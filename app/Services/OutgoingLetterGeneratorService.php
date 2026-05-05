<?php

namespace App\Services;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;

class OutgoingLetterGeneratorService
{
    public const TYPES = [
        'undangan' => [
            'label' => 'Surat Undangan',
            'code' => 'HM.01',
            'subject_prefix' => 'Undangan',
        ],
        'keterangan' => [
            'label' => 'Surat Keterangan',
            'code' => 'KP.02',
            'subject_prefix' => 'Keterangan',
        ],
        'panggilan' => [
            'label' => 'Surat Panggilan',
            'code' => 'KP.03',
            'subject_prefix' => 'Panggilan',
        ],
        'perjanjian' => [
            'label' => 'Surat Perjanjian',
            'code' => 'HK.00',
            'subject_prefix' => 'Perjanjian',
        ],
        'izin' => [
            'label' => 'Surat Izin',
            'code' => 'KP.05',
            'subject_prefix' => 'Izin',
        ],
    ];

    public function generate(array $payload): array
    {
        $letter = $this->buildLetterData($payload);

        Storage::disk('local')->makeDirectory('archives/surat_keluar/generated');

        $baseFileName = $this->makeBaseFileName($letter['document_number']);
        $pdfFileName = $baseFileName . '.pdf';
        $docxFileName = $baseFileName . '.docx';
        $pdfRelativePath = 'archives/surat_keluar/generated/' . $pdfFileName;
        $docxRelativePath = 'archives/surat_keluar/generated/' . $docxFileName;

        $this->generatePdf($letter, Storage::disk('local')->path($pdfRelativePath));
        $this->generateDocx($letter, Storage::disk('local')->path($docxRelativePath));

        return [
            'pdf_relative_path' => $pdfRelativePath,
            'pdf_file_name' => $pdfFileName,
            'docx_relative_path' => $docxRelativePath,
            'docx_file_name' => $docxFileName,
            'payload' => $letter['payload'],
            'subject' => $letter['subject'],
            'recipient_summary' => $letter['recipient_summary'],
        ];
    }

    private function buildLetterData(array $payload): array
    {
        $type = (string) $payload['document_type'];
        $typeMeta = self::TYPES[$type];
        $documentDate = Carbon::parse($payload['document_date']);
        $city = trim((string) ($payload['city'] ?? 'Surakarta'));
        $subject = trim((string) ($payload['subject'] ?? ''));

        if ($subject === '') {
            $subject = $typeMeta['subject_prefix'];
        }

        $letter = [
            'document_type' => $type,
            'document_type_label' => $typeMeta['label'],
            'document_number' => trim((string) $payload['document_number']),
            'document_date' => $documentDate->toDateString(),
            'document_date_label' => $this->formatDate($documentDate),
            'document_city_and_date' => $city . ', ' . $this->formatDate($documentDate),
            'subject' => $subject,
            'recipient_name' => trim((string) $payload['recipient_name']),
            'recipient_address' => trim((string) ($payload['recipient_address'] ?? 'Tempat')),
            'city' => $city,
            'signer_title' => trim((string) ($payload['signer_title'] ?? 'Kepala MAN 2 Surakarta')),
            'signer_name' => trim((string) ($payload['signer_name'] ?? 'Sita Kurniasari')),
            'signer_nip' => trim((string) ($payload['signer_nip'] ?? '')),
            'logo_path' => public_path('images/man2.png'),
        ];

        $body = $this->bodyForType($type, $payload);
        $letter['opening'] = $body['opening'];
        $letter['details'] = $body['details'];
        $letter['closing'] = $body['closing'];
        $letter['payload'] = [
            ...$letter,
            'details' => $body['details'],
            'specific' => $body['specific'],
        ];
        unset($letter['payload']['logo_path']);

        $letter['recipient_summary'] = $letter['recipient_name'];

        return $letter;
    }

    private function bodyForType(string $type, array $payload): array
    {
        return match ($type) {
            'undangan' => $this->invitationBody($payload),
            'keterangan' => $this->certificateBody($payload),
            'panggilan' => $this->summonsBody($payload),
            'perjanjian' => $this->agreementBody($payload),
            'izin' => $this->permitBody($payload),
        };
    }

    private function invitationBody(array $payload): array
    {
        $activityDate = Carbon::parse($payload['activity_date']);

        return [
            'opening' => 'Dengan hormat, kami mengundang Bapak/Ibu/Saudara untuk hadir pada kegiatan berikut:',
            'details' => [
                'Hari/Tanggal' => $this->formatDayAndDate($activityDate),
                'Waktu' => $this->formatTime((string) $payload['activity_time']) . ' WIB',
                'Tempat' => trim((string) $payload['activity_place']),
                'Agenda' => trim((string) $payload['agenda']),
            ],
            'closing' => 'Demikian undangan ini kami sampaikan. Atas perhatian dan kehadirannya kami ucapkan terima kasih.',
            'specific' => [
                'agenda' => trim((string) $payload['agenda']),
                'activity_date' => $activityDate->toDateString(),
                'activity_time' => trim((string) $payload['activity_time']),
                'activity_place' => trim((string) $payload['activity_place']),
            ],
        ];
    }

    private function certificateBody(array $payload): array
    {
        return [
            'opening' => 'Yang bertanda tangan di bawah ini menerangkan bahwa:',
            'details' => [
                'Nama' => trim((string) $payload['described_person']),
                'Identitas' => trim((string) ($payload['person_identifier'] ?? '-')),
                'Keterangan' => trim((string) $payload['statement']),
            ],
            'closing' => 'Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana mestinya.',
            'specific' => [
                'described_person' => trim((string) $payload['described_person']),
                'person_identifier' => trim((string) ($payload['person_identifier'] ?? '')),
                'statement' => trim((string) $payload['statement']),
            ],
        ];
    }

    private function summonsBody(array $payload): array
    {
        $callDate = Carbon::parse($payload['call_date']);

        return [
            'opening' => 'Sehubungan dengan keperluan administrasi dan koordinasi madrasah, kami memanggil pihak berikut:',
            'details' => [
                'Nama' => trim((string) $payload['called_person']),
                'Keperluan' => trim((string) $payload['call_reason']),
                'Hari/Tanggal' => $this->formatDayAndDate($callDate),
                'Waktu' => $this->formatTime((string) $payload['call_time']) . ' WIB',
                'Tempat' => trim((string) $payload['call_place']),
            ],
            'closing' => 'Demikian surat panggilan ini kami sampaikan untuk menjadi perhatian.',
            'specific' => [
                'called_person' => trim((string) $payload['called_person']),
                'call_reason' => trim((string) $payload['call_reason']),
                'call_date' => $callDate->toDateString(),
                'call_time' => trim((string) $payload['call_time']),
                'call_place' => trim((string) $payload['call_place']),
            ],
        ];
    }

    private function agreementBody(array $payload): array
    {
        $points = collect(preg_split("/\r\n|\n|\r/", (string) $payload['agreement_points']))
            ->map(fn (string $point) => trim($point))
            ->filter()
            ->values()
            ->implode("\n");

        return [
            'opening' => 'Pada prinsipnya para pihak menyepakati perjanjian dengan informasi sebagai berikut:',
            'details' => [
                'Pihak Pertama' => trim((string) $payload['first_party']),
                'Pihak Kedua' => trim((string) $payload['second_party']),
                'Objek Perjanjian' => trim((string) $payload['agreement_subject']),
                'Pokok Kesepakatan' => $points,
            ],
            'closing' => 'Demikian surat perjanjian ini dibuat sebagai dasar pelaksanaan bersama.',
            'specific' => [
                'first_party' => trim((string) $payload['first_party']),
                'second_party' => trim((string) $payload['second_party']),
                'agreement_subject' => trim((string) $payload['agreement_subject']),
                'agreement_points' => $points,
            ],
        ];
    }

    private function permitBody(array $payload): array
    {
        $startDate = Carbon::parse($payload['permission_start_date']);
        $endDate = Carbon::parse($payload['permission_end_date']);

        return [
            'opening' => 'Berdasarkan pertimbangan administrasi madrasah, kami memberikan izin kepada:',
            'details' => [
                'Nama' => trim((string) $payload['permitted_person']),
                'Kegiatan' => trim((string) $payload['permission_activity']),
                'Tanggal Mulai' => $this->formatDate($startDate),
                'Tanggal Selesai' => $this->formatDate($endDate),
                'Tempat' => trim((string) $payload['permission_place']),
            ],
            'closing' => 'Demikian surat izin ini diterbitkan untuk dipergunakan sebagaimana mestinya.',
            'specific' => [
                'permitted_person' => trim((string) $payload['permitted_person']),
                'permission_activity' => trim((string) $payload['permission_activity']),
                'permission_start_date' => $startDate->toDateString(),
                'permission_end_date' => $endDate->toDateString(),
                'permission_place' => trim((string) $payload['permission_place']),
            ],
        ];
    }

    private function generatePdf(array $letter, string $absolutePath): void
    {
        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', true);
        $options->setChroot(base_path());

        $dompdf = new Dompdf($options);
        $html = view('documents.surat-keluar-generated-pdf', ['letter' => $letter])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();

        file_put_contents($absolutePath, $dompdf->output());
    }

    private function generateDocx(array $letter, string $absolutePath): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        $section = $phpWord->addSection([
            'marginTop' => 850,
            'marginBottom' => 850,
            'marginLeft' => 1100,
            'marginRight' => 900,
        ]);

        $this->addDocxHeader($section, $letter);
        $section->addTextBreak(1);

        $metaTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $this->addMetaRow($metaTable, 'Nomor', $letter['document_number']);
        $this->addMetaRow($metaTable, 'Lamp', '-');
        $this->addMetaRow($metaTable, 'Hal', $letter['subject']);

        $section->addTextBreak(1);
        $section->addText('Kepada', [], ['spaceAfter' => 0]);
        $section->addText($letter['recipient_name'], [], ['spaceAfter' => 0]);
        $section->addText('Di', [], ['spaceAfter' => 0]);
        $section->addText($letter['recipient_address'] ?: 'Tempat', [], ['spaceAfter' => 220]);

        $section->addText($letter['opening'], [], ['alignment' => 'both', 'spaceAfter' => 180]);

        $detailTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        foreach ($letter['details'] as $label => $value) {
            $this->addMetaRow($detailTable, $label, $value);
        }

        $section->addTextBreak(1);
        $section->addText($letter['closing'], [], ['alignment' => 'both', 'spaceAfter' => 420]);

        $signatureTable = $section->addTable(['borderSize' => 0, 'cellMargin' => 0]);
        $signatureTable->addRow();
        $signatureTable->addCell(5200)->addText('');
        $signatureCell = $signatureTable->addCell(3400);
        $signatureCell->addText($letter['document_city_and_date'], [], ['spaceAfter' => 120]);
        $signatureCell->addText($letter['signer_title'], [], ['spaceAfter' => 760]);
        $signatureCell->addText($letter['signer_name'], ['bold' => true, 'underline' => 'single'], ['spaceAfter' => 80]);

        if ($letter['signer_nip'] !== '') {
            $signatureCell->addText('NIP. ' . $letter['signer_nip']);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($absolutePath);
    }

    private function addDocxHeader($section, array $letter): void
    {
        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 0, 'alignment' => 'center']);
        $table->addRow();
        $logoCell = $table->addCell(1300, ['valign' => 'center']);

        if (is_file($letter['logo_path'])) {
            $logoCell->addImage($letter['logo_path'], ['width' => 70, 'height' => 70]);
        }

        $titleCell = $table->addCell(7200, ['valign' => 'center']);
        $titleCell->addText('KEMENTERIAN AGAMA REPUBLIK INDONESIA', ['bold' => true, 'size' => 13], ['alignment' => 'center', 'spaceAfter' => 0]);
        $titleCell->addText('KANTOR KEMENTERIAN AGAMA KOTA SURAKARTA', ['bold' => true, 'size' => 12], ['alignment' => 'center', 'spaceAfter' => 0]);
        $titleCell->addText('MADRASAH ALIYAH NEGERI 2 SURAKARTA', ['bold' => true, 'size' => 12], ['alignment' => 'center', 'spaceAfter' => 0]);
        $titleCell->addText('Jl. Slamet Riyadi No. 308 Surakarta', ['size' => 10], ['alignment' => 'center', 'spaceAfter' => 0]);
        $titleCell->addText('Telepon (0271) 717510', ['size' => 10], ['alignment' => 'center', 'spaceAfter' => 0]);

        $section->addLine(['weight' => 1.5, 'width' => 500, 'height' => 0]);
    }

    private function addMetaRow($table, string $label, string $value): void
    {
        $table->addRow();
        $table->addCell(1650)->addText($label);
        $table->addCell(180)->addText(':');
        $table->addCell(6500)->addText($value, [], ['alignment' => 'both']);
    }

    private function makeBaseFileName(string $documentNumber): string
    {
        return 'surat_keluar_' . Str::slug(str_replace('/', '-', $documentNumber), '_');
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

        return $date->format('j') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
    }

    private function formatDayAndDate(Carbon $date): string
    {
        $days = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return $days[(int) $date->format('w')] . ', ' . $this->formatDate($date);
    }

    private function formatTime(string $time): string
    {
        return str_replace(':', '.', substr($time, 0, 5));
    }
}
