<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Services\ArchiveFileService;
use App\Services\GoogleDriveUploadService;
use App\Services\OutgoingLetterGeneratorService;
use App\Support\DispositionStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuratKeluarController extends Controller
{
    public function __construct(
        private GoogleDriveUploadService $googleDriveUploadService,
        private ArchiveFileService $archiveFileService,
        private OutgoingLetterGeneratorService $outgoingLetterGeneratorService
    ) {
    }

    public function index()
    {
        $surat_keluar = SuratKeluar::orderBy('letter_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        $generatedLetterTypes = OutgoingLetterGeneratorService::TYPES;
        $nextLetterNumbers = collect($generatedLetterTypes)
            ->mapWithKeys(fn (array $meta, string $type) => [
                $type => $this->makeGeneratedLetterNumber($type, now()),
            ])
            ->all();

        return view('surat-keluar', compact('surat_keluar', 'generatedLetterTypes', 'nextLetterNumbers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|string|max:255',
            'letter_date' => 'required|date',
            'letter_number' => 'required|string|max:255|unique:surat_keluar,letter_number',
            'subject' => 'required|string|max:500',
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
                'status' => DispositionStatus::DEFAULT,
                'notes' => $validated['notes'],
                'google_drive_link' => null,
                'local_file_path' => $localFilePath,
                'file_name' => $originalName,
                'share_token' => (string) Str::uuid(),
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
                    'google_drive_link' => route('archive.open', ['type' => 'surat-keluar', 'id' => $surat->id]),
                    'download_url' => route('archive.download', ['type' => 'surat-keluar', 'id' => $surat->id]),
                    'share_url' => $this->shareUrl($surat),
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

    public function storeGenerated(Request $request)
    {
        $validated = $request->validate($this->generatedLetterValidationRules($request));
        $validated['document_number'] = $this->makeGeneratedLetterNumber(
            $validated['document_type'],
            Carbon::parse($validated['document_date'])
        );

        try {
            $generatedFiles = $this->outgoingLetterGeneratorService->generate($validated);

            $surat = SuratKeluar::create([
                'destination' => $validated['recipient_name'],
                'entry_type' => 'generated_letter',
                'document_type' => $validated['document_type'],
                'letter_date' => $validated['document_date'],
                'letter_number' => $validated['document_number'],
                'subject' => $generatedFiles['subject'],
                'status' => DispositionStatus::DEFAULT,
                'notes' => $validated['notes'] ?? null,
                'generated_payload' => $generatedFiles['payload'],
                'google_drive_link' => null,
                'local_file_path' => $generatedFiles['pdf_relative_path'],
                'generated_docx_path' => $generatedFiles['docx_relative_path'],
                'generated_docx_name' => $generatedFiles['docx_file_name'],
                'file_name' => $generatedFiles['pdf_file_name'],
                'share_token' => (string) Str::uuid(),
                'user_id' => $this->resolveExistingUserId(),
            ]);

            return response()->json([
                'success' => true,
                'message' => OutgoingLetterGeneratorService::TYPES[$validated['document_type']]['label'] . ' berhasil dibuat dalam format PDF dan DOCX.',
                'drive_synced' => false,
                'data' => $this->formatRecordData($surat),
                'next_letter_number' => $this->makeGeneratedLetterNumber(
                    $validated['document_type'],
                    Carbon::parse($validated['document_date'])
                ),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error Generate Surat Keluar', [
                'message' => $e->getMessage(),
                'trace' => Str::limit($e->getTraceAsString(), 1000),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat surat keluar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function showSharePortal(string $token)
    {
        $surat = SuratKeluar::where('share_token', $token)->firstOrFail();
        $hasFile = filled($surat->google_drive_link) || filled($surat->local_file_path);

        return view('surat-keluar-share', [
            'surat' => $surat,
            'documentTypeLabel' => $surat->document_type
                ? (OutgoingLetterGeneratorService::TYPES[$surat->document_type]['label'] ?? 'Surat Keluar')
                : 'Surat Keluar',
            'previewUrl' => $hasFile ? route('surat-keluar.share.preview', $surat->share_token) : null,
            'openUrl' => $hasFile ? route('surat-keluar.share.file', $surat->share_token) : null,
            'downloadUrl' => $hasFile ? route('surat-keluar.share.download', $surat->share_token) : null,
            'docxUrl' => $surat->generated_docx_path ? route('surat-keluar.downloadDocx', $surat->id) : null,
        ]);
    }

    public function downloadDocx(int $id)
    {
        $surat = SuratKeluar::findOrFail($id);

        if (!$surat->generated_docx_path || !Storage::disk('local')->exists($surat->generated_docx_path)) {
            abort(404, 'File DOCX belum tersedia.');
        }

        return Storage::disk('local')->download(
            $surat->generated_docx_path,
            $surat->generated_docx_name ?: basename($surat->generated_docx_path)
        );
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

    private function generatedLetterValidationRules(Request $request): array
    {
        $baseRules = [
            'document_type' => ['required', Rule::in(array_keys(OutgoingLetterGeneratorService::TYPES))],
            'document_date' => 'required|date',
            'recipient_name' => 'required|string|max:255',
            'recipient_address' => 'nullable|string|max:500',
            'subject' => 'nullable|string|max:500',
            'city' => 'required|string|max:255',
            'signer_title' => 'required|string|max:255',
            'signer_name' => 'required|string|max:255',
            'signer_nip' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];

        return match ($request->input('document_type')) {
            'undangan' => [
                ...$baseRules,
                'agenda' => 'required|string|max:500',
                'activity_date' => 'required|date',
                'activity_time' => 'required|date_format:H:i',
                'activity_place' => 'required|string|max:500',
            ],
            'keterangan' => [
                ...$baseRules,
                'described_person' => 'required|string|max:255',
                'person_identifier' => 'nullable|string|max:255',
                'statement' => 'required|string',
            ],
            'panggilan' => [
                ...$baseRules,
                'called_person' => 'required|string|max:255',
                'call_reason' => 'required|string|max:500',
                'call_date' => 'required|date',
                'call_time' => 'required|date_format:H:i',
                'call_place' => 'required|string|max:500',
            ],
            'perjanjian' => [
                ...$baseRules,
                'first_party' => 'required|string|max:255',
                'second_party' => 'required|string|max:255',
                'agreement_subject' => 'required|string|max:500',
                'agreement_points' => 'required|string',
            ],
            'izin' => [
                ...$baseRules,
                'permitted_person' => 'required|string|max:255',
                'permission_activity' => 'required|string|max:500',
                'permission_start_date' => 'required|date',
                'permission_end_date' => 'required|date|after_or_equal:permission_start_date',
                'permission_place' => 'required|string|max:500',
            ],
            default => $baseRules,
        };
    }

    private function makeGeneratedLetterNumber(string $type, Carbon $date): string
    {
        $meta = OutgoingLetterGeneratorService::TYPES[$type] ?? OutgoingLetterGeneratorService::TYPES['undangan'];
        $year = $date->format('Y');
        $lastSequence = SuratKeluar::query()
            ->where('document_type', $type)
            ->whereYear('letter_date', $year)
            ->pluck('letter_number')
            ->map(function (?string $letterNumber) {
                if (!$letterNumber || !preg_match('/^(\d+)/', $letterNumber, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        return sprintf(
            '%03d/Ma.11.31.02/%s/%s/%s',
            $lastSequence + 1,
            $meta['code'],
            $date->format('m'),
            $year
        );
    }

    private function formatRecordData(SuratKeluar $surat): array
    {
        return [
            'id' => $surat->id,
            'tanggal' => optional($surat->letter_date)->format('d M Y'),
            'tujuan' => $surat->destination,
            'perihal' => $surat->subject,
            'nomor' => $surat->letter_number,
            'file' => $surat->file_name,
            'google_drive_link' => route('archive.open', ['type' => 'surat-keluar', 'id' => $surat->id]),
            'download_url' => route('archive.download', ['type' => 'surat-keluar', 'id' => $surat->id]),
            'docx_download_url' => $surat->generated_docx_path ? route('surat-keluar.downloadDocx', $surat->id) : null,
            'share_url' => $this->shareUrl($surat),
            'catatan' => $surat->notes ?: '-',
            'entry_type' => $surat->entry_type ?: 'upload',
            'document_type_label' => $surat->document_type
                ? (OutgoingLetterGeneratorService::TYPES[$surat->document_type]['label'] ?? 'Surat Otomatis')
                : 'Upload Surat Keluar',
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

    private function shareUrl(SuratKeluar $surat): ?string
    {
        if (!$surat->share_token) {
            return null;
        }

        return route('surat-keluar.share', $surat->share_token);
    }
}
