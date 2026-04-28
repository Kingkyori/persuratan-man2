<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Services\ArchiveFileService;
use App\Services\GoogleDriveUploadService;
use App\Support\DispositionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuratKeluarController extends Controller
{
    public function __construct(
        private GoogleDriveUploadService $googleDriveUploadService,
        private ArchiveFileService $archiveFileService
    ) {
    }

    public function index()
    {
        $surat_keluar = SuratKeluar::orderBy('letter_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('surat-keluar', compact('surat_keluar'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|string|max:255',
            'letter_date' => 'required|date',
            'letter_number' => 'required|string|max:255|unique:surat_keluar,letter_number',
            'subject' => 'required|string|max:500',
            'status' => ['required', Rule::in(DispositionStatus::keys())],
            'notes' => 'nullable|string',
            'letter_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        try {
            $file = $request->file('letter_file');
            $originalName = $file->getClientOriginalName();
            $safeOriginalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $fileName = 'surat_keluar_' . now()->format('Ymd_His') . '_' . $safeOriginalName;
            $fallbackDriveUrl = config('services.google_apps_script.folders.surat_keluar.url');
            $localFilePath = $this->archiveFileService->storeLocalBackup($file, 'surat_keluar', $fileName);

            $surat = SuratKeluar::create([
                'destination' => $validated['destination'],
                'letter_date' => $validated['letter_date'],
                'letter_number' => $validated['letter_number'],
                'subject' => $validated['subject'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'google_drive_link' => null,
                'local_file_path' => $localFilePath,
                'file_name' => $originalName,
                'user_id' => $this->resolveExistingUserId(),
            ]);

            $driveSynced = false;
            $message = 'Surat keluar berhasil disimpan ke database.';

            try {
                $uploadResult = $this->googleDriveUploadService->upload(
                    $file,
                    'surat_keluar',
                    'surat_keluar',
                    $fileName
                );

                $resolvedDriveUrl = $this->archiveFileService->resolveDriveFileUrl(
                    $uploadResult['url'] ?? null,
                    $fileName
                );

                if ($resolvedDriveUrl) {
                    $surat->update([
                        'google_drive_link' => $resolvedDriveUrl,
                    ]);
                }

                $driveSynced = (bool) $resolvedDriveUrl;
                $message = $resolvedDriveUrl
                    ? 'Surat keluar berhasil disimpan dan file terhubung ke Drive.'
                    : 'Surat keluar berhasil disimpan. File cadangan tetap bisa dibuka dari sistem meski tautan Drive belum tervalidasi.';
            } catch (\Throwable $uploadException) {
                $resolvedDriveUrl = $fallbackDriveUrl
                    ? $this->archiveFileService->resolveDriveFileUrl($fallbackDriveUrl, $fileName)
                    : null;

                if ($resolvedDriveUrl) {
                    $surat->update([
                        'google_drive_link' => $resolvedDriveUrl,
                    ]);
                    $driveSynced = true;
                }

                Log::warning('Upload Google Drive surat keluar gagal setelah data database tersimpan.', [
                    'surat_keluar_id' => $surat->id,
                    'message' => $uploadException->getMessage(),
                ]);

                $message = $resolvedDriveUrl
                    ? 'Surat keluar berhasil disimpan. File ditemukan di folder Drive dan sekarang bisa dibuka dari sistem.'
                    : 'Surat keluar berhasil disimpan. File tetap aman dan bisa dibuka dari sistem, tetapi tautan Drive belum berhasil diverifikasi.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'drive_synced' => $driveSynced,
                'data' => [
                    'id' => $surat->id,
                    'tanggal' => optional($surat->letter_date)->format('d M Y'),
                    'tujuan' => $surat->destination,
                    'perihal' => $surat->subject,
                    'nomor' => $surat->letter_number,
                    'file' => $surat->file_name,
                    'status' => $surat->status,
                    'google_drive_link' => route('archive.open', ['type' => 'surat-keluar', 'id' => $surat->id]),
                    'download_url' => route('archive.download', ['type' => 'surat-keluar', 'id' => $surat->id]),
                    'catatan' => $surat->notes ?: '-',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error Simpan Surat Keluar', [
                'message' => $e->getMessage(),
                'trace' => Str::limit($e->getTraceAsString(), 1000),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => ['required', Rule::in(DispositionStatus::keys())],
            ]);

            $surat = SuratKeluar::find($id);
            if (!$surat) {
                return response()->json(['success' => false, 'message' => 'Surat tidak ditemukan'], 404);
            }

            $surat->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
                'data' => [
                    'status' => DispositionStatus::normalize($surat->status),
                    'status_label' => DispositionStatus::label($surat->status),
                    'status_class' => DispositionStatus::meta($surat->status)['class'],
                    'notes' => $surat->notes ?: '-',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $surat = SuratKeluar::find($id);
            if (!$surat) {
                return response()->json(['success' => false, 'message' => 'Surat keluar tidak ditemukan'], 404);
            }

            $surat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Surat keluar berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function resolveExistingUserId(): ?int
    {
        $sessionUserId = data_get(session('user'), 'id');
        $authenticatedUserId = auth()->id();

        if ($authenticatedUserId && User::whereKey($authenticatedUserId)->exists()) {
            return (int) $authenticatedUserId;
        }

        if ($sessionUserId && User::whereKey($sessionUserId)->exists()) {
            return (int) $sessionUserId;
        }

        return null;
    }
}
