<?php

namespace App\Http\Controllers;

use App\Models\Sppd;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Support\DispositionStatus;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $suratMasuk = SuratMasuk::query()->latest('updated_at')->get();
        $suratKeluar = SuratKeluar::query()->latest('updated_at')->get();
        $sppd = Sppd::query()->latest('updated_at')->get();

        $letters = $suratMasuk->concat($suratKeluar);
        $allDocuments = $letters->concat($sppd);

        $stats = [
            'surat_masuk' => $suratMasuk->count(),
            'surat_keluar' => $suratKeluar->count(),
            'sppd' => $sppd->count(),
            'draft' => $this->countByStatus($allDocuments, 'draft'),
            'pending' => $this->countByStatus($allDocuments, 'pending_approval'),
            'approved_letters' => $this->countByStatus($letters, 'approved'),
            'rejected_letters' => $this->countByStatus($letters, 'rejected'),
            'revision' => $this->countByStatus($allDocuments, 'revision'),
            'reviewed' => $allDocuments->filter(fn ($item) => $this->hasReviewMarker($item->status, $item->notes))->count(),
        ];

        $statusDistribution = [
            'Draft' => $stats['draft'],
            'Menunggu' => $stats['pending'],
            'Revisi' => $stats['revision'],
            'Disetujui' => $this->countByStatus($allDocuments, 'approved'),
            'Ditolak' => $this->countByStatus($allDocuments, 'rejected'),
        ];

        $recentActivities = $this->buildActivities($suratMasuk, $suratKeluar, $sppd)
            ->sortByDesc('sort_time')
            ->take(6)
            ->values();

        return view('dashboard', [
            'stats' => $stats,
            'statusDistribution' => $statusDistribution,
            'recentActivities' => $recentActivities,
        ]);
    }

    private function countByStatus(Collection $items, string $normalizedStatus): int
    {
        return $items->filter(function ($item) use ($normalizedStatus) {
            return DispositionStatus::normalize($item->status) === $normalizedStatus;
        })->count();
    }

    private function hasReviewMarker(?string $status, ?string $notes): bool
    {
        return filled($notes) || DispositionStatus::normalize($status) !== DispositionStatus::DEFAULT;
    }

    private function buildActivities(Collection $suratMasuk, Collection $suratKeluar, Collection $sppd): Collection
    {
        $incoming = $suratMasuk->map(function (SuratMasuk $item) {
            return [
                'title' => 'Surat masuk diperbarui',
                'description' => $item->origin . ' - ' . $item->subject,
                'badge' => DispositionStatus::label($item->status),
                'type' => 'Surat Masuk',
                'icon' => 'Masuk',
                'sort_time' => optional($item->updated_at)->timestamp ?? 0,
                'time_label' => optional($item->updated_at)?->diffForHumans() ?? '-',
            ];
        });

        $outgoing = $suratKeluar->map(function (SuratKeluar $item) {
            return [
                'title' => 'Surat keluar diperbarui',
                'description' => $item->destination . ' - ' . $item->subject,
                'badge' => DispositionStatus::label($item->status),
                'type' => 'Surat Keluar',
                'icon' => 'Keluar',
                'sort_time' => optional($item->updated_at)->timestamp ?? 0,
                'time_label' => optional($item->updated_at)?->diffForHumans() ?? '-',
            ];
        });

        $travel = $sppd->map(function (Sppd $item) {
            return [
                'title' => 'SPPD diperbarui',
                'description' => ($item->employee_name ?: 'Pegawai') . ' - ' . $item->destination,
                'badge' => DispositionStatus::label($item->status),
                'type' => 'SPPD',
                'icon' => 'SPPD',
                'sort_time' => optional($item->updated_at)->timestamp ?? 0,
                'time_label' => optional($item->updated_at)?->diffForHumans() ?? '-',
            ];
        });

        return $incoming->concat($outgoing)->concat($travel);
    }
}
