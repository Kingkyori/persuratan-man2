<?php

namespace App\Http\Controllers;

use App\Models\Sppd;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Support\DepartmentReceiptStatus;
use App\Support\DispositionStatus;
use Illuminate\Support\Collection;

class ManajemenSuratController extends Controller
{
    public function index()
    {
        $userRole = session('user.role', 'admin');
        $items = $this->buildItems();

        $counts = [
            'all' => $items->count(),
            'surat-masuk' => $items->where('category', 'surat-masuk')->count(),
            'surat-keluar' => $items->where('category', 'surat-keluar')->count(),
            'sppd' => $items->where('category', 'sppd')->count(),
        ];

        $stats = [
            'total' => $items->count(),
            'approved' => $items->where('headmaster_status', 'approved')->count(),
            'pending' => $items->whereIn('headmaster_status', ['draft', 'pending_approval'])->count(),
            'department_received' => $items->where('department_status', 'received')->count(),
        ];

        return view('manajemen-surat', [
            'items' => $items->values(),
            'counts' => $counts,
            'stats' => $stats,
            'statusConfig' => DispositionStatus::options(),
            'departmentStatusConfig' => DepartmentReceiptStatus::options(),
            'userRole' => $userRole,
        ]);
    }

    private function buildItems(): Collection
    {
        $suratMasuk = SuratMasuk::query()
            ->orderBy('reception_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (SuratMasuk $item) {
                $headmasterStatus = DispositionStatus::normalize($item->status);
                $departmentStatus = DepartmentReceiptStatus::normalize($item->department_status);

                return [
                    'id' => $item->id,
                    'key' => "surat-masuk-{$item->id}",
                    'category' => 'surat-masuk',
                    'type_label' => 'Surat Masuk',
                    'primary_name' => $item->origin,
                    'secondary_name' => $item->subject,
                    'destination' => $item->department_destination ?: '-',
                    'number' => $item->reference_number ?: $item->letter_number,
                    'date_label' => optional($item->reception_date)->format('d M Y') ?? '-',
                    'date_sort' => optional($item->reception_date)->format('Y-m-d') ?? '',
                    'updated_label' => optional($item->updated_at)?->diffForHumans() ?? '-',
                    'updated_sort' => optional($item->updated_at)->timestamp ?? 0,
                    'headmaster_status' => $headmasterStatus,
                    'headmaster_status_label' => DispositionStatus::label($item->status),
                    'headmaster_status_class' => DispositionStatus::meta($item->status)['class'],
                    'department_status' => $departmentStatus,
                    'department_status_label' => DepartmentReceiptStatus::label($item->department_status),
                    'department_status_class' => DepartmentReceiptStatus::meta($item->department_status)['class'],
                    'notes' => $item->notes ?: '-',
                    'department_notes' => $item->department_notes ?: '-',
                    'file_name' => $item->file_name ?: 'File belum tersedia',
                    'file_url' => filled($item->google_drive_link) || filled($item->local_file_path)
                        ? route('archive.open', ['type' => 'surat-masuk', 'id' => $item->id])
                        : null,
                    'preview_url' => filled($item->google_drive_link) || filled($item->local_file_path)
                        ? route('archive.preview', ['type' => 'surat-masuk', 'id' => $item->id])
                        : null,
                    'source_url' => $this->resolveSourceUrl('surat-masuk'),
                    'source_label' => $this->resolveSourceLabel('surat-masuk'),
                    'share_url' => $item->share_token ? route('surat-masuk.share', $item->share_token) : null,
                    'search' => strtolower(implode(' ', [
                        'surat masuk',
                        $item->origin,
                        $item->subject,
                        $item->letter_number,
                        $item->reference_number,
                        $item->department_destination,
                        $item->notes,
                        $item->department_notes,
                    ])),
                ];
            });

        $suratKeluar = SuratKeluar::query()
            ->orderBy('letter_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (SuratKeluar $item) {
                $headmasterStatus = DispositionStatus::normalize($item->status);

                return [
                    'id' => $item->id,
                    'key' => "surat-keluar-{$item->id}",
                    'category' => 'surat-keluar',
                    'type_label' => 'Surat Keluar',
                    'primary_name' => $item->destination,
                    'secondary_name' => $item->subject,
                    'destination' => $item->destination,
                    'number' => $item->letter_number,
                    'date_label' => optional($item->letter_date)->format('d M Y') ?? '-',
                    'date_sort' => optional($item->letter_date)->format('Y-m-d') ?? '',
                    'updated_label' => optional($item->updated_at)?->diffForHumans() ?? '-',
                    'updated_sort' => optional($item->updated_at)->timestamp ?? 0,
                    'headmaster_status' => $headmasterStatus,
                    'headmaster_status_label' => DispositionStatus::label($item->status),
                    'headmaster_status_class' => DispositionStatus::meta($item->status)['class'],
                    'department_status' => 'not_applicable',
                    'department_status_label' => 'Tidak Berlaku',
                    'department_status_class' => 'badge-neutral',
                    'notes' => $item->notes ?: '-',
                    'department_notes' => '-',
                    'file_name' => $item->file_name ?: 'File belum tersedia',
                    'file_url' => filled($item->google_drive_link) || filled($item->local_file_path)
                        ? route('archive.open', ['type' => 'surat-keluar', 'id' => $item->id])
                        : null,
                    'preview_url' => filled($item->google_drive_link) || filled($item->local_file_path)
                        ? route('archive.preview', ['type' => 'surat-keluar', 'id' => $item->id])
                        : null,
                    'source_url' => $this->resolveSourceUrl('surat-keluar'),
                    'source_label' => $this->resolveSourceLabel('surat-keluar'),
                    'share_url' => null,
                    'search' => strtolower(implode(' ', [
                        'surat keluar',
                        $item->destination,
                        $item->subject,
                        $item->letter_number,
                        $item->notes,
                    ])),
                ];
            });

        $sppd = Sppd::query()
            ->orderBy('departure_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (Sppd $item) {
                $headmasterStatus = DispositionStatus::normalize($item->status);

                return [
                    'id' => $item->id,
                    'key' => "sppd-{$item->id}",
                    'category' => 'sppd',
                    'type_label' => 'SPPD',
                    'primary_name' => $item->employee_name ?: 'Pegawai belum diisi',
                    'secondary_name' => $item->purpose,
                    'destination' => $item->destination,
                    'number' => $item->purpose,
                    'date_label' => optional($item->departure_date)->format('d M Y') ?? '-',
                    'date_sort' => optional($item->departure_date)->format('Y-m-d') ?? '',
                    'updated_label' => optional($item->updated_at)?->diffForHumans() ?? '-',
                    'updated_sort' => optional($item->updated_at)->timestamp ?? 0,
                    'headmaster_status' => $headmasterStatus,
                    'headmaster_status_label' => DispositionStatus::label($item->status),
                    'headmaster_status_class' => DispositionStatus::meta($item->status)['class'],
                    'department_status' => 'not_applicable',
                    'department_status_label' => 'Tidak Berlaku',
                    'department_status_class' => 'badge-neutral',
                    'notes' => $item->notes ?: '-',
                    'department_notes' => '-',
                    'file_name' => $item->file_name ?: 'File belum tersedia',
                    'file_url' => filled($item->google_drive_link) || filled($item->local_file_path)
                        ? route('archive.open', ['type' => 'sppd', 'id' => $item->id])
                        : null,
                    'preview_url' => filled($item->google_drive_link) || filled($item->local_file_path)
                        ? route('archive.preview', ['type' => 'sppd', 'id' => $item->id])
                        : null,
                    'source_url' => $this->resolveSourceUrl('sppd'),
                    'source_label' => $this->resolveSourceLabel('sppd'),
                    'share_url' => null,
                    'search' => strtolower(implode(' ', [
                        'sppd',
                        $item->employee_name,
                        $item->destination,
                        $item->purpose,
                        $item->notes,
                    ])),
                ];
            });

        return $suratMasuk
            ->concat($suratKeluar)
            ->concat($sppd)
            ->sortByDesc('updated_sort')
            ->values();
    }

    private function resolveSourceUrl(string $category): string
    {
        if (session('user.role') === 'kepala_sekolah') {
            return route('disposisi');
        }

        return match ($category) {
            'surat-masuk' => route('surat-masuk'),
            'surat-keluar' => route('surat-keluar'),
            'sppd' => route('sppd'),
            default => route('manajemen-surat'),
        };
    }

    private function resolveSourceLabel(string $category): string
    {
        if (session('user.role') === 'kepala_sekolah') {
            return 'Buka Disposisi';
        }

        return 'Buka Halaman Asal';
    }
}
