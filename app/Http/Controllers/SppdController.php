<?php

namespace App\Http\Controllers;

use App\Models\Sppd;
use App\Models\User;
use App\Services\AssignmentLetterGeneratorService;
use App\Services\ArchiveFileService;
use App\Services\GoogleDriveUploadService;
use App\Support\DispositionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SppdController extends Controller
{
    public function __construct(
        private GoogleDriveUploadService $googleDriveUploadService,
        private ArchiveFileService $archiveFileService,
        private AssignmentLetterGeneratorService $assignmentLetterGeneratorService
    ) {
    }

    public function index()
    {
        $sppd = Sppd::query()
            ->orderBy('departure_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('sppd', compact('sppd'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departure_date' => 'required|date',
            'duration_days' => 'required|integer|min:1|max:365',
            'purpose' => 'required|string',
            'status' => ['required', Rule::in(DispositionStatus::keys())],
            'notes' => 'nullable|string',
            'attachment_file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
        ]);

        try {
            $file = $request->file('attachment_file');
            $originalName = $file->getClientOriginalName();
            $safeOriginalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $fileName = 'sppd_' . now()->format('Ymd_His') . '_' . $safeOriginalName;
            $fallbackDriveUrl = config('services.google_apps_script.folders.sppd.url');
            $localFilePath = $this->archiveFileService->storeLocalBackup($file, 'sppd', $fileName);

            $record = Sppd::create([
                'employee_id' => null,
                'employee_name' => $validated['employee_name'],
                'destination' => $validated['destination'],
                'departure_date' => $validated['departure_date'],
                'duration_days' => $validated['duration_days'],
                'purpose' => $validated['purpose'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'google_drive_link' => null,
                'local_file_path' => $localFilePath,
                'file_name' => $originalName,
                'user_id' => $this->resolveExistingUserId(),
            ]);

            $driveSynced = false;
            $message = 'Data SPPD berhasil disimpan ke database.';

            try {
                $uploadResult = $this->googleDriveUploadService->upload(
                    $file,
                    'sppd',
                    'sppd',
                    $fileName
                );

                $resolvedDriveUrl = $this->archiveFileService->resolveDriveFileUrl(
                    $uploadResult['url'] ?? null,
                    $fileName
                );

                if ($resolvedDriveUrl) {
                    $record->update([
                        'google_drive_link' => $resolvedDriveUrl,
                    ]);
                }

                $driveSynced = (bool) $resolvedDriveUrl;
                $message = $resolvedDriveUrl
                    ? 'Data SPPD berhasil disimpan dan file terhubung ke Drive.'
                    : 'Data SPPD berhasil disimpan. File cadangan tetap bisa dibuka dari sistem meski tautan Drive belum tervalidasi.';
            } catch (\Throwable $uploadException) {
                $resolvedDriveUrl = $fallbackDriveUrl
                    ? $this->archiveFileService->resolveDriveFileUrl($fallbackDriveUrl, $fileName)
                    : null;

                if ($resolvedDriveUrl) {
                    $record->update([
                        'google_drive_link' => $resolvedDriveUrl,
                    ]);
                    $driveSynced = true;
                }

                Log::warning('Upload Google Drive SPPD gagal setelah data database tersimpan.', [
                    'sppd_id' => $record->id,
                    'message' => $uploadException->getMessage(),
                ]);

                $message = $resolvedDriveUrl
                    ? 'Data SPPD berhasil disimpan. File ditemukan di folder Drive dan sekarang bisa dibuka dari sistem.'
                    : 'Data SPPD berhasil disimpan. File tetap aman dan bisa dibuka dari sistem, tetapi tautan Drive belum berhasil diverifikasi.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'drive_synced' => $driveSynced,
                'data' => $this->formatRecordData($record),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error Simpan SPPD', [
                'message' => $e->getMessage(),
                'trace' => Str::limit($e->getTraceAsString(), 1000),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function storeGenerated(Request $request)
    {
        $validated = $request->validate([
            'document_number' => 'required|string|max:255',
            'document_date' => 'required|date',
            'reference_from' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'reference_date' => 'nullable|date',
            'reference_subject' => 'nullable|string|max:255',
            'assignees' => 'required|array|min:1|max:10',
            'assignees.*.name' => 'required|string|max:255',
            'assignees.*.nip' => 'nullable|string|max:100',
            'assignees.*.rank' => 'nullable|string|max:255',
            'assignees.*.position' => 'nullable|string|max:255',
            'assignment_agenda' => 'required|string',
            'activity_date' => 'required|date',
            'activity_time' => 'required|date_format:H:i',
            'activity_location' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1|max:365',
            'city' => 'required|string|max:255',
            'signer_title' => 'required|string|max:255',
            'signer_name' => 'required|string|max:255',
            'signer_nip' => 'nullable|string|max:100',
            'status' => ['required', Rule::in(DispositionStatus::keys())],
            'notes' => 'nullable|string',
        ]);

        try {
            $generatedFiles = $this->assignmentLetterGeneratorService->generate($validated);

            $record = Sppd::create([
                'employee_id' => null,
                'employee_name' => $generatedFiles['employee_summary'],
                'entry_type' => 'generated_assignment',
                'document_number' => $validated['document_number'],
                'destination' => $validated['activity_location'],
                'departure_date' => $validated['activity_date'],
                'document_date' => $validated['document_date'],
                'duration_days' => $validated['duration_days'],
                'purpose' => $validated['assignment_agenda'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'assignment_payload' => $generatedFiles['payload'],
                'google_drive_link' => null,
                'local_file_path' => $generatedFiles['pdf_relative_path'],
                'generated_docx_path' => $generatedFiles['docx_relative_path'],
                'generated_docx_name' => $generatedFiles['docx_file_name'],
                'file_name' => $generatedFiles['pdf_file_name'],
                'user_id' => $this->resolveExistingUserId(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Surat penugasan berhasil dibuat dalam format PDF dan DOCX.',
                'drive_synced' => false,
                'data' => $this->formatRecordData($record),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error Generate Surat Penugasan', [
                'message' => $e->getMessage(),
                'trace' => Str::limit($e->getTraceAsString(), 1000),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat surat penugasan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => ['required', Rule::in(DispositionStatus::keys())],
            ]);

            $record = Sppd::find($id);
            if (!$record) {
                return response()->json(['success' => false, 'message' => 'Data SPPD tidak ditemukan'], 404);
            }

            $record->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah',
                'data' => [
                    'status' => DispositionStatus::normalize($record->status),
                    'status_label' => DispositionStatus::label($record->status),
                    'status_class' => DispositionStatus::meta($record->status)['class'],
                    'notes' => $record->notes ?: '-',
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $record = Sppd::find($id);
            if (!$record) {
                return response()->json(['success' => false, 'message' => 'Data SPPD tidak ditemukan'], 404);
            }

            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data SPPD berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function downloadDocx(int $id)
    {
        $record = Sppd::findOrFail($id);

        if (!$record->generated_docx_path || !Storage::disk('local')->exists($record->generated_docx_path)) {
            abort(404, 'File DOCX belum tersedia.');
        }

        return Storage::disk('local')->download(
            $record->generated_docx_path,
            $record->generated_docx_name ?: basename($record->generated_docx_path)
        );
    }

    private function formatRecordData(Sppd $record): array
    {
        return [
            'id' => $record->id,
            'pegawai' => $record->employee_name ?: '-',
            'tujuan' => $record->destination,
            'tanggal' => optional($record->departure_date)->format('d M Y'),
            'durasi' => $record->duration_days . ' hari',
            'kepentingan' => $record->purpose,
            'status' => DispositionStatus::normalize($record->status),
            'file' => $record->file_name,
            'google_drive_link' => route('archive.open', ['type' => 'sppd', 'id' => $record->id]),
            'download_url' => route('archive.download', ['type' => 'sppd', 'id' => $record->id]),
            'docx_download_url' => $record->generated_docx_path ? route('sppd.downloadDocx', $record->id) : null,
            'catatan' => $record->notes ?: '-',
            'entry_type' => $record->entry_type ?: 'upload',
            'record_type' => $record->entry_type === 'generated_assignment' ? 'Surat Penugasan' : 'Upload Lampiran',
            'document_number' => $record->document_number ?: '-',
        ];
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
