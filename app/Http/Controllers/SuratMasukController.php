<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\User;
use App\Services\ArchiveFileService;
use App\Support\DepartmentReceiptStatus;
use App\Services\GoogleDriveUploadService;
use App\Support\DispositionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuratMasukController extends Controller
{
    public function __construct(
        private GoogleDriveUploadService $googleDriveUploadService,
        private ArchiveFileService $archiveFileService
    ) {
    }

    public function index()
    {
        $surat_masuk = SuratMasuk::orderBy('reception_date', 'desc')->paginate(10);
        $stats = [
            'total_received' => SuratMasuk::count(),
            'approved' => SuratMasuk::whereIn('status', DispositionStatus::databaseValuesFor('approved'))->count(),
            'pending_approval' => SuratMasuk::whereIn('status', [
                ...DispositionStatus::databaseValuesFor('draft'),
                ...DispositionStatus::databaseValuesFor('pending_approval'),
            ])->count(),
            'department_received' => SuratMasuk::where('department_status', 'received')->count(),
        ];

        return view('surat-masuk', compact('surat_masuk', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'origin' => 'required|string|max:255',
            'reception_date' => 'required|date',
            'letter_number' => 'required|string|max:255|unique:surat_masuk,letter_number',
            'subject' => 'required|string|max:500',
            'department_destination' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'letter_scan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        try {
            $file = $request->file('letter_scan');
            $originalName = $file->getClientOriginalName();
            $safeOriginalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $fileName = 'surat_' . now()->format('Ymd_His') . '_' . $safeOriginalName;
            $fallbackDriveUrl = config('services.google_apps_script.folders.surat_masuk.url');
            $localFilePath = $this->archiveFileService->storeLocalBackup($file, 'surat_masuk', $fileName);

            $surat = SuratMasuk::create([
                'origin' => $validated['origin'],
                'reception_date' => $validated['reception_date'],
                'letter_number' => $validated['letter_number'],
                'subject' => $validated['subject'],
                'department_destination' => $validated['department_destination'],
                'reference_number' => $validated['reference_number'],
                'status' => 'pending_approval',
                'department_status' => DepartmentReceiptStatus::DEFAULT,
                'notes' => $validated['notes'],
                'department_notes' => null,
                'google_drive_link' => null,
                'local_file_path' => $localFilePath,
                'file_name' => $originalName,
                'share_token' => (string) Str::uuid(),
                'user_id' => $this->resolveExistingUserId(),
            ]);

            $driveSynced = false;
            $message = 'Surat berhasil disimpan ke database.';

            try {
                $uploadResult = $this->googleDriveUploadService->upload(
                    $file,
                    'surat_masuk',
                    'surat_masuk',
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
                    ? 'Surat berhasil disimpan dan file terhubung ke Drive.'
                    : 'Surat berhasil disimpan. File cadangan tetap bisa dibuka dari sistem meski tautan Drive belum tervalidasi.';
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

                Log::warning('Upload Google Drive surat masuk gagal setelah data database tersimpan.', [
                    'surat_masuk_id' => $surat->id,
                    'message' => $uploadException->getMessage(),
                ]);

                $message = $resolvedDriveUrl
                    ? 'Surat berhasil disimpan. File ditemukan di folder Drive dan sekarang bisa dibuka dari sistem.'
                    : 'Surat berhasil disimpan. File tetap aman dan bisa dibuka dari sistem, tetapi tautan Drive belum berhasil diverifikasi.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'drive_synced' => $driveSynced,
                'data' => [
                    'id' => $surat->id,
                    'origin' => $surat->origin,
                    'subject' => $surat->subject,
                    'department_destination' => $surat->department_destination,
                    'status' => $surat->status,
                    'department_status' => $surat->department_status,
                    'notes' => $surat->notes,
                    'department_notes' => $surat->department_notes,
                    'file_url' => route('archive.open', ['type' => 'surat-masuk', 'id' => $surat->id]),
                    'download_url' => route('archive.download', ['type' => 'surat-masuk', 'id' => $surat->id]),
                    'share_url' => $this->shareUrl($surat),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error Simpan Surat', [
                'message' => $e->getMessage(),
                'trace' => Str::limit($e->getTraceAsString(), 1000),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function simpan(Request $request)
    {
        return $this->store($request);
    }

    public function getData()
    {
        return response()->json(
            SuratMasuk::orderBy('reception_date', 'desc')
                ->get()
                ->map(fn (SuratMasuk $surat) => [
                    'id' => $surat->id,
                    'origin' => $surat->origin,
                    'subject' => $surat->subject,
                    'department_destination' => $surat->department_destination,
                    'reference_number' => $surat->reference_number,
                    'status' => $surat->status,
                    'department_status' => $surat->department_status,
                    'notes' => $surat->notes,
                    'department_notes' => $surat->department_notes,
                    'share_url' => $this->shareUrl($surat),
                ])
        );
    }

    public function updateStatus(Request $request, $id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Status kepala sekolah untuk surat masuk hanya dapat diubah melalui halaman disposisi.',
        ], 403);
    }

    public function showDepartmentPortal(string $token)
    {
        $surat = SuratMasuk::where('share_token', $token)->firstOrFail();

        return view('surat-masuk-share', [
            'surat' => $surat,
            'statusMeta' => DispositionStatus::meta($surat->status),
            'departmentStatusMeta' => DepartmentReceiptStatus::meta($surat->department_status),
            'departmentStatusConfig' => DepartmentReceiptStatus::options(),
            'previewUrl' => route('surat-masuk.share.preview', $surat->share_token),
            'openUrl' => route('surat-masuk.share.file', $surat->share_token),
            'downloadUrl' => route('surat-masuk.share.download', $surat->share_token),
        ]);
    }

    public function updateDepartmentReceipt(Request $request, string $token)
    {
        $validated = $request->validate([
            'department_status' => ['required', Rule::in(DepartmentReceiptStatus::keys())],
            'department_notes' => ['nullable', 'string'],
        ]);

        $surat = SuratMasuk::where('share_token', $token)->firstOrFail();

        $surat->update([
            'department_status' => $validated['department_status'],
            'department_notes' => trim((string) ($validated['department_notes'] ?? '')) !== ''
                ? trim((string) $validated['department_notes'])
                : null,
        ]);

        $statusMeta = DepartmentReceiptStatus::meta($surat->department_status);

        return response()->json([
            'success' => true,
            'message' => 'Status departemen berhasil diperbarui.',
            'data' => [
                'department_status' => DepartmentReceiptStatus::normalize($surat->department_status),
                'department_status_label' => $statusMeta['label'],
                'department_status_class' => $statusMeta['class'],
                'department_status_description' => $statusMeta['description'],
                'department_notes' => $surat->department_notes ?: '-',
            ],
        ]);
    }

    public function destroy($id)
    {
        try {
            $surat = SuratMasuk::find($id);
            if (!$surat) {
                return response()->json(['success' => false, 'message' => 'Surat tidak ditemukan'], 404);
            }

            $surat->delete();

            return response()->json(['success' => true, 'message' => 'Surat berhasil dihapus']);
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

    private function shareUrl(SuratMasuk $surat): ?string
    {
        if (!$surat->share_token) {
            return null;
        }

        return route('surat-masuk.share', $surat->share_token);
    }
}
