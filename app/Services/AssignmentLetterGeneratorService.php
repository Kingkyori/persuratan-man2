<?php

namespace App\Services;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class AssignmentLetterGeneratorService
{
    public function generate(array $payload): array
    {
        $letter = $this->buildLetterData($payload);

        Storage::disk('local')->makeDirectory('archives/sppd/generated');

        $baseFileName = $this->makeBaseFileName($letter['document_number']);
        $pdfFileName = $baseFileName . '.pdf';
        $docxFileName = $baseFileName . '.docx';
        $pdfRelativePath = 'archives/sppd/generated/' . $pdfFileName;
        $docxRelativePath = 'archives/sppd/generated/' . $docxFileName;

        $this->generatePdf($letter, Storage::disk('local')->path($pdfRelativePath));
        $this->generateDocx($letter, Storage::disk('local')->path($docxRelativePath));

        return [
            'pdf_relative_path' => $pdfRelativePath,
            'pdf_file_name' => $pdfFileName,
            'docx_relative_path' => $docxRelativePath,
            'docx_file_name' => $docxFileName,
            'payload' => $letter['payload'],
            'employee_summary' => $letter['employee_summary'],
        ];
    }

    private function buildLetterData(array $payload): array
    {
        $documentDate = Carbon::parse($payload['document_date']);
        $activityDate = Carbon::parse($payload['activity_date']);
        $referenceDate = filled($payload['reference_date'] ?? null)
            ? Carbon::parse($payload['reference_date'])
            : null;

        $assignees = collect($payload['assignees'] ?? [])
            ->map(fn (array $assignee) => [
                'name' => trim((string) ($assignee['name'] ?? '')),
                'nip' => trim((string) ($assignee['nip'] ?? '')),
                'rank' => trim((string) ($assignee['rank'] ?? '')),
                'position' => trim((string) ($assignee['position'] ?? '')),
            ])
            ->filter(fn (array $assignee) => filled($assignee['name']))
            ->values()
            ->all();

        $activityDateLabel = $this->formatDayAndDate($activityDate);
        $documentDateLabel = $this->formatDate($documentDate);
        $activityTime = $this->formatTime((string) $payload['activity_time']);
        $documentCity = trim((string) ($payload['city'] ?? 'Surakarta'));
        $employeeSummary = collect($assignees)->pluck('name')->implode(', ');
        $referenceFrom = trim((string) ($payload['reference_from'] ?? ''));
        $referenceNumber = trim((string) ($payload['reference_number'] ?? ''));
        $referenceSubject = trim((string) ($payload['reference_subject'] ?? ''));
        $hasReferenceBasis = $referenceFrom !== ''
            || $referenceNumber !== ''
            || $referenceSubject !== ''
            || $referenceDate !== null;
        $basisItems = [
            '1. Peraturan Menteri Agama Nomor 19 Tahun 2019 tentang Organisasi dan Tata Kerja Instansi Vertikal Kementerian Agama.',
        ];

        if ($hasReferenceBasis) {
            $basisItems[] = '2. ' . $this->buildReferenceLine(
                $referenceFrom,
                $referenceNumber,
                $referenceDate ? $this->formatDate($referenceDate) : '-',
                $referenceSubject
            );
        }

        $instructionPoints = [
            'Melaksanakan tugas dan fungsi organisasi di lingkungan MAN 2 Surakarta.',
            trim((string) $payload['assignment_agenda']) . ', pada Hari/Tanggal : ' . $activityDateLabel . ', pukul ' . $activityTime . ' WIB s.d. selesai, Tempat: ' . trim((string) $payload['activity_location']) . '.',
            'Melaporkan kepada pimpinan setelah selesai melaksanakan tugas.',
        ];

        $payloadForDatabase = [
            'reference_from' => $referenceFrom,
            'reference_number' => $referenceNumber,
            'reference_date' => $referenceDate?->toDateString(),
            'reference_subject' => $referenceSubject,
            'has_reference_basis' => $hasReferenceBasis,
            'assignees' => $assignees,
            'assignment_agenda' => trim((string) $payload['assignment_agenda']),
            'activity_date' => $activityDate->toDateString(),
            'activity_time' => trim((string) $payload['activity_time']),
            'activity_location' => trim((string) $payload['activity_location']),
            'city' => $documentCity,
            'signer_title' => trim((string) $payload['signer_title']),
            'signer_name' => trim((string) $payload['signer_name']),
            'signer_nip' => trim((string) ($payload['signer_nip'] ?? '')),
            'basis_items' => $basisItems,
            'instruction_points' => $instructionPoints,
        ];

        return [
            'document_number' => trim((string) $payload['document_number']),
            'document_date' => $documentDateLabel,
            'document_city_and_date' => $documentCity . ', ' . $documentDateLabel,
            'basis_items' => $basisItems,
            'assignees' => $assignees,
            'instruction_points' => $instructionPoints,
            'signer_title' => trim((string) $payload['signer_title']),
            'signer_name' => trim((string) $payload['signer_name']),
            'signer_nip' => trim((string) ($payload['signer_nip'] ?? '')),
            'logo_path' => public_path('images/man2.png'),
            'logo_data_uri' => extension_loaded('gd') ? $this->logoDataUri(public_path('images/man2.png')) : null,
            'payload' => $payloadForDatabase,
            'employee_summary' => $employeeSummary,
        ];
    }

    private function buildReferenceLine(string $from, string $number, string $date, string $subject): string
    {
        $resolvedFrom = $from !== '' ? $from : '-';
        $resolvedNumber = $number !== '' ? $number : '-';
        $resolvedSubject = $subject !== '' ? $subject : '-';

        return "Surat dari {$resolvedFrom}, Nomor {$resolvedNumber}, tanggal {$date}, Perihal {$resolvedSubject}.";
    }

    private function generatePdf(array $letter, string $absolutePath): void
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->setChroot(base_path());

        $dompdf = new Dompdf($options);
        $html = view('documents.surat-penugasan-pdf', ['letter' => $letter])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        file_put_contents($absolutePath, $dompdf->output());
    }

    private function generateDocx(array $letter, string $absolutePath): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);

        $section = $phpWord->addSection([
            'marginTop' => 900,
            'marginBottom' => 900,
            'marginLeft' => 1100,
            'marginRight' => 900,
        ]);

        $this->addWordHeader($section, $letter);

        $section->addText('SURAT TUGAS', ['bold' => true, 'size' => 18], ['alignment' => 'center', 'spaceAfter' => 0]);
        $section->addText('Nomor: ' . $letter['document_number'], ['size' => 12], ['alignment' => 'center', 'spaceAfter' => 260]);

        $introTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'alignment' => 'left']);
        $this->addInfoRow(
            $introTable,
            'Menimbang',
            ['Bahwa sehubungan dengan pelaksanaan tugas dan fungsi organisasi di lingkungan Kementerian Agama, dipandang perlu membuat surat tugas dinas pada MAN 2 Surakarta;']
        );
        $this->addInfoRow(
            $introTable,
            'Dasar',
            $letter['basis_items']
        );

        $section->addText('MEMBERI TUGAS', ['bold' => true, 'size' => 14], ['alignment' => 'center', 'spaceBefore' => 220, 'spaceAfter' => 220]);

        $assigneeTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'alignment' => 'left']);
        $this->addAssigneesRow($assigneeTable, 'Kepada', $letter['assignees']);
        $this->addInfoRow($assigneeTable, 'Untuk', $letter['instruction_points']);

        $section->addTextBreak(1);

        $signatureTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'alignment' => 'left']);
        $signatureTable->addRow();
        $noteCell = $signatureTable->addCell(5200, ['borderSize' => 10, 'borderColor' => '555555', 'valign' => 'top']);
        $noteCell->addText('Keterangan :', ['bold' => true], ['spaceAfter' => 120]);
        $noteCell->addText('Yang bersangkutan telah melaksanakan tugas dengan baik pada :', [], ['spaceAfter' => 140]);
        $noteCell->addText('Tanggal : ____________________', [], ['spaceAfter' => 140]);
        $noteCell->addText('Mengetahui', [], ['spaceAfter' => 700, 'alignment' => 'center']);
        $noteCell->addText('NIP. ____________________', [], ['spaceBefore' => 280]);

        $signatureCell = $signatureTable->addCell(3200, ['borderSize' => 0, 'valign' => 'top']);
        $signatureCell->addText($letter['document_city_and_date'], [], ['alignment' => 'right', 'spaceAfter' => 120]);
        $signatureCell->addText($letter['signer_title'], ['bold' => true], ['alignment' => 'right', 'spaceAfter' => 700]);
        $signatureCell->addText($letter['signer_name'], ['bold' => true, 'underline' => 'single'], ['alignment' => 'right', 'spaceAfter' => 80]);

        if ($letter['signer_nip'] !== '') {
            $signatureCell->addText('NIP. ' . $letter['signer_nip'], [], ['alignment' => 'right']);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($absolutePath);
    }

    private function addWordHeader($section, array $letter): void
    {
        $table = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'alignment' => 'center',
        ]);

        $table->addRow();
        $logoCell = $table->addCell(1500, ['borderSize' => 0, 'valign' => 'center']);
        if (is_file($letter['logo_path'])) {
            $logoCell->addImage($letter['logo_path'], [
                'width' => 70,
                'height' => 70,
                'alignment' => 'center',
            ]);
        }

        $textCell = $table->addCell(7600, ['borderSize' => 0, 'valign' => 'center']);
        $textCell->addText('KEMENTERIAN AGAMA REPUBLIK INDONESIA', ['bold' => true, 'size' => 14], ['alignment' => 'center', 'spaceAfter' => 0]);
        $textCell->addText('KANTOR KEMENTERIAN AGAMA KOTA SURAKARTA', ['size' => 11], ['alignment' => 'center', 'spaceAfter' => 0]);
        $textCell->addText('MADRASAH ALIYAH NEGERI 2', ['size' => 12, 'bold' => true], ['alignment' => 'center', 'spaceAfter' => 0]);
        $textCell->addText('Jalan Slamet Riyadi Nomor 308 Surakarta, Telepon: (0271)716387', ['size' => 10], ['alignment' => 'center', 'spaceAfter' => 0]);
        $textCell->addText('Web : www.man2ska.sch.id  Email : man2surakarta@kemenag.go.id', ['size' => 10], ['alignment' => 'center']);

        $section->addLine(['weight' => 1.2, 'width' => 460, 'height' => 0, 'color' => '222222']);
        $section->addTextBreak(1);
    }

    private function addInfoRow($table, string $label, array $lines): void
    {
        $table->addRow();
        $table->addCell(1500, ['borderSize' => 0, 'valign' => 'top'])->addText($label);
        $table->addCell(250, ['borderSize' => 0, 'valign' => 'top'])->addText(':');
        $contentCell = $table->addCell(7050, ['borderSize' => 0, 'valign' => 'top']);

        foreach ($lines as $index => $line) {
            $contentCell->addText($line, [], ['spaceAfter' => $index === count($lines) - 1 ? 0 : 80]);
        }
    }

    private function addAssigneesRow($table, string $label, array $assignees): void
    {
        $table->addRow();
        $table->addCell(1500, ['borderSize' => 0, 'valign' => 'top'])->addText($label);
        $table->addCell(250, ['borderSize' => 0, 'valign' => 'top'])->addText(':');
        $contentCell = $table->addCell(7050, ['borderSize' => 0, 'valign' => 'top']);

        foreach ($assignees as $index => $assignee) {
            $assigneeTable = $contentCell->addTable(['borderSize' => 0, 'cellMargin' => 0, 'alignment' => 'left']);
            $assigneeTable->addRow();
            $assigneeTable->addCell(250, ['borderSize' => 0, 'valign' => 'top'])->addText(($index + 1) . '.');
            $detailCell = $assigneeTable->addCell(6600, ['borderSize' => 0, 'valign' => 'top']);
            $detailCell->addText('Nama : ' . $assignee['name'], ['bold' => true], ['spaceAfter' => 40]);
            $detailCell->addText('NIP : ' . ($assignee['nip'] !== '' ? $assignee['nip'] : '-'), [], ['spaceAfter' => 40]);
            $detailCell->addText('Pangkat : ' . ($assignee['rank'] !== '' ? $assignee['rank'] : '-'), [], ['spaceAfter' => 40]);
            $detailCell->addText('Jabatan : ' . ($assignee['position'] !== '' ? $assignee['position'] : '-'));

            if ($index !== count($assignees) - 1) {
                $contentCell->addTextBreak(1);
            }
        }
    }

    private function logoDataUri(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        $encoded = base64_encode(file_get_contents($path));

        return 'data:' . $mime . ';base64,' . $encoded;
    }

    private function makeBaseFileName(string $documentNumber): string
    {
        $numberSlug = Str::slug($documentNumber, '_');

        return 'surat_penugasan_' . now()->format('Ymd_His') . '_' . ($numberSlug !== '' ? $numberSlug : Str::random(6));
    }

    private function formatDate(Carbon $date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $date->day . ' ' . $months[$date->month] . ' ' . $date->year;
    }

    private function formatDayAndDate(Carbon $date): string
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        return ($days[$date->englishDayOfWeek] ?? $date->englishDayOfWeek) . ', ' . $this->formatDate($date);
    }

    private function formatTime(string $time): string
    {
        return str_replace(':', '.', substr($time, 0, 5));
    }
}
