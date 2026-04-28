<?php

namespace App\Http\Controllers;

use App\Models\Sppd;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Services\ArchiveFileService;
use App\Support\DispositionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ArchiveFileController extends Controller
{
    public function __construct(
        private ArchiveFileService $archiveFileService
    ) {
    }

    public function open(string $type, int $id): Response|RedirectResponse
    {
        return $this->serveRecordFile($this->resolveRecord($type, $id), 'open');
    }

    public function preview(string $type, int $id): Response|RedirectResponse
    {
        return $this->serveRecordFile($this->resolveRecord($type, $id), 'preview');
    }

    public function download(string $type, int $id): Response|RedirectResponse
    {
        return $this->serveRecordFile($this->resolveRecord($type, $id), 'download');
    }

    public function shareOpen(string $token): Response|RedirectResponse
    {
        return $this->serveRecordFile($this->resolveSharedIncomingRecord($token), 'open');
    }

    public function sharePreview(string $token): Response|RedirectResponse
    {
        return $this->serveRecordFile($this->resolveSharedIncomingRecord($token), 'preview');
    }

    public function shareDownload(string $token): Response|RedirectResponse
    {
        return $this->serveRecordFile($this->resolveSharedIncomingRecord($token), 'download');
    }

    private function serveRecordFile(Model $record, string $mode): Response|RedirectResponse
    {
        $driveUrl = $this->archiveFileService->resolveDriveFileUrl(
            $record->google_drive_link,
            $record->file_name
        );

        if ($driveUrl && $driveUrl !== $record->google_drive_link) {
            $record->forceFill(['google_drive_link' => $driveUrl])->saveQuietly();
        }

        if ($driveUrl) {
            return match ($mode) {
                'preview' => redirect()->away(DispositionStatus::previewUrl($driveUrl) ?? $driveUrl),
                'download' => redirect()->away(DispositionStatus::downloadUrl($driveUrl) ?? $driveUrl),
                default => redirect()->away($driveUrl),
            };
        }

        if ($this->archiveFileService->hasLocalFile($record->local_file_path)) {
            $absolutePath = $this->archiveFileService->localAbsolutePath($record->local_file_path);
            $mimeType = $this->archiveFileService->localMimeType($record->local_file_path);

            if ($mode === 'download') {
                return Storage::disk('local')->download(
                    $record->local_file_path,
                    $record->file_name ?: basename($record->local_file_path)
                );
            }

            if ($mode === 'preview' && !$this->archiveFileService->canPreview($record->file_name, $record->local_file_path)) {
                return response($this->previewUnavailableMarkup(), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
            }

            return response()->file($absolutePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . ($record->file_name ?: basename($absolutePath)) . '"',
            ]);
        }

        abort(404, 'File arsip belum tersedia.');
    }

    private function resolveRecord(string $type, int $id): Model
    {
        return match ($type) {
            'surat-masuk' => SuratMasuk::findOrFail($id),
            'surat-keluar' => SuratKeluar::findOrFail($id),
            'sppd' => Sppd::findOrFail($id),
            default => abort(404),
        };
    }

    private function resolveSharedIncomingRecord(string $token): SuratMasuk
    {
        return SuratMasuk::where('share_token', $token)->firstOrFail();
    }

    private function previewUnavailableMarkup(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Tidak Tersedia</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f5f8f3;
            color: #243126;
            font-family: Arial, sans-serif;
        }
        .box {
            max-width: 460px;
            padding: 24px;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(24, 57, 30, 0.08);
            text-align: center;
        }
        h1 {
            margin: 0 0 10px;
            font-size: 20px;
        }
        p {
            margin: 0;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>Preview belum tersedia</h1>
        <p>File ini belum bisa ditampilkan langsung di preview. Gunakan tombol buka atau download file dari halaman sebelumnya.</p>
    </div>
</body>
</html>
HTML;
    }
}
