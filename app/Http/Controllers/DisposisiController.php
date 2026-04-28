<?php

namespace App\Http\Controllers;

use App\Models\Sppd;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Support\DepartmentReceiptStatus;
use App\Support\DispositionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class DisposisiController extends Controller
{
    public function index()
    {
        $statusConfig = DispositionStatus::options();
        $items = $this->buildDispositionItems();
        $counts = [
            'surat-masuk' => $items->where('category', 'surat-masuk')->count(),
            'surat-keluar' => $items->where('category', 'surat-keluar')->count(),
            'sppd' => $items->where('category', 'sppd')->count(),
        ];

        return view('disposisi', [
            'statusConfig' => $statusConfig,
            'departmentStatusConfig' => DepartmentReceiptStatus::options(),
            'items' => $items->values(),
            'counts' => $counts,
        ]);
    }

    public function updateStatus(Request $request, string $type, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(DispositionStatus::keys())],
            'notes' => ['nullable', 'string'],
        ]);

        [$modelClass] = $this->resolveSource($type);
        $record = $modelClass::findOrFail($id);
        $record->update([
            'status' => $validated['status'],
            'notes' => trim((string) ($validated['notes'] ?? '')) !== ''
                ? trim((string) $validated['notes'])
                : null,
        ]);

        $statusMeta = DispositionStatus::meta($record->status);

        return response()->json([
            'success' => true,
            'message' => 'Status disposisi berhasil diperbarui.',
            'data' => [
                'id' => $record->id,
                'type' => $type,
                'status' => DispositionStatus::normalize($record->status),
                'status_label' => $statusMeta['label'],
                'status_class' => $statusMeta['class'],
                'status_description' => $statusMeta['description'],
                'notes' => $record->notes ?: '-',
            ],
        ]);
    }

    private function buildDispositionItems(): Collection
    {
        $suratMasuk = SuratMasuk::query()
            ->orderBy('reception_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (SuratMasuk $item) {
                return $this->makeItem(
                    category: 'surat-masuk',
                    typeLabel: 'Surat Masuk',
                    id: $item->id,
                    primaryName: $item->origin,
                    destination: $item->department_destination ?: 'Belum ditentukan',
                    letterNumber: $item->reference_number ?: $item->letter_number,
                    dateValue: optional($item->reception_date),
                    status: $item->status,
                    notes: $item->notes,
                    fileName: $item->file_name,
                    fileUrl: $item->google_drive_link,
                    extra: [
                        'subject' => $item->subject,
                        'department_destination' => $item->department_destination ?: '-',
                        'department_status' => DepartmentReceiptStatus::normalize($item->department_status),
                        'department_status_label' => DepartmentReceiptStatus::label($item->department_status),
                        'department_status_class' => DepartmentReceiptStatus::meta($item->department_status)['class'],
                        'department_notes' => $item->department_notes ?: '-',
                        'share_url' => $item->share_token ? route('surat-masuk.share', $item->share_token) : null,
                    ]
                );
            });

        $suratKeluar = SuratKeluar::query()
            ->orderBy('letter_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (SuratKeluar $item) {
                return $this->makeItem(
                    category: 'surat-keluar',
                    typeLabel: 'Surat Keluar',
                    id: $item->id,
                    primaryName: $item->subject,
                    destination: $item->destination,
                    letterNumber: $item->letter_number,
                    dateValue: optional($item->letter_date),
                    status: $item->status,
                    notes: $item->notes,
                    fileName: $item->file_name,
                    fileUrl: $item->google_drive_link
                );
            });

        $sppd = Sppd::query()
            ->orderBy('departure_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (Sppd $item) {
                return $this->makeItem(
                    category: 'sppd',
                    typeLabel: 'SPPD',
                    id: $item->id,
                    primaryName: $item->employee_name ?: 'Pegawai belum diisi',
                    destination: $item->destination,
                    letterNumber: $item->purpose,
                    dateValue: optional($item->departure_date),
                    status: $item->status,
                    notes: $item->notes,
                    fileName: $item->file_name,
                    fileUrl: $item->google_drive_link
                );
            });

        return $suratMasuk
            ->concat($suratKeluar)
            ->concat($sppd)
            ->sortByDesc('date_sort')
            ->values();
    }

    private function makeItem(
        string $category,
        string $typeLabel,
        int $id,
        string $primaryName,
        string $destination,
        string $letterNumber,
        $dateValue,
        ?string $status,
        ?string $notes,
        ?string $fileName,
        ?string $fileUrl,
        array $extra = []
    ): array {
        $normalizedStatus = DispositionStatus::normalize($status);
        $statusMeta = DispositionStatus::meta($status);

        return array_merge([
            'id' => $id,
            'key' => "{$category}-{$id}",
            'category' => $category,
            'type_label' => $typeLabel,
            'primary_name' => $primaryName,
            'destination' => $destination,
            'letter_number' => $letterNumber,
            'date_label' => $dateValue?->format('d M Y') ?? '-',
            'date_sort' => $dateValue?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'status' => $normalizedStatus,
            'status_label' => $statusMeta['label'],
            'status_class' => $statusMeta['class'],
            'status_description' => $statusMeta['description'],
            'notes' => $notes ?: '-',
            'notes_excerpt' => str($notes ?: 'Belum ada catatan disposisi.')->limit(56)->toString(),
            'file_name' => $fileName ?: 'File belum tersedia',
            'file_url' => $fileUrl,
            'preview_url' => DispositionStatus::previewUrl($fileUrl),
            'search' => strtolower(implode(' ', [
                $typeLabel,
                $primaryName,
                $destination,
                $letterNumber,
                $notes ?: '',
                $dateValue?->format('d M Y') ?? '',
            ])),
        ], $extra);
    }

    private function resolveSource(string $type): array
    {
        return match ($type) {
            'surat-masuk' => [SuratMasuk::class],
            'surat-keluar' => [SuratKeluar::class],
            'sppd' => [Sppd::class],
            default => abort(404),
        };
    }
}
