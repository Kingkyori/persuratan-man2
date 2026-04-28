<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ArchiveFileService
{
    public function storeLocalBackup(UploadedFile $file, string $folderKey, string $fileName): string
    {
        return $file->storeAs("archives/{$folderKey}", $fileName);
    }

    public function hasLocalFile(?string $path): bool
    {
        return filled($path) && Storage::disk('local')->exists($path);
    }

    public function canPreview(?string $fileName, ?string $localPath = null): bool
    {
        $extension = strtolower(pathinfo($fileName ?: $localPath ?: '', PATHINFO_EXTENSION));

        return in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp'], true);
    }

    public function resolveDriveFileUrl(?string $url, ?string $expectedFileName = null): ?string
    {
        if (!$url) {
            return null;
        }

        $fileId = $this->extractDriveFileId($url);

        if ($fileId) {
            return "https://drive.google.com/file/d/{$fileId}/view?usp=sharing";
        }

        if (!$this->isDriveFolderUrl($url) || !$expectedFileName) {
            return null;
        }

        return Cache::remember(
            'drive-folder-file:' . md5($url . '|' . $expectedFileName),
            now()->addMinutes(10),
            fn () => $this->findFileUrlInSharedFolder($url, $expectedFileName)
        );
    }

    public function localMimeType(string $path): string
    {
        return Storage::disk('local')->mimeType($path) ?: 'application/octet-stream';
    }

    public function localAbsolutePath(string $path): string
    {
        return Storage::disk('local')->path($path);
    }

    private function findFileUrlInSharedFolder(string $folderUrl, string $expectedFileName): ?string
    {
        try {
            $response = Http::timeout(20)->get($folderUrl);
        } catch (ConnectionException $exception) {
            Log::warning('Gagal mengakses folder Google Drive untuk pencarian file.', [
                'folder_url' => $folderUrl,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $files = $this->extractFilesFromFolderHtml($response->body());

        if ($files === []) {
            return null;
        }

        $expected = $this->normalizeFilename($expectedFileName);

        foreach ($files as $file) {
            if ($this->normalizeFilename($file['name']) === $expected) {
                return $file['url'];
            }
        }

        foreach ($files as $file) {
            $candidate = $this->normalizeFilename($file['name']);

            if (str_ends_with($candidate, $expected) || str_contains($candidate, $expected)) {
                return $file['url'];
            }
        }

        return null;
    }

    private function extractFilesFromFolderHtml(string $html): array
    {
        if (!preg_match("~window\\['_DRIVE_ivd'\\]\\s*=\\s*'(.*?)';if~s", $html, $matches)) {
            return [];
        }

        $decoded = $this->decodeDrivePayload($matches[1]);

        if (!$decoded) {
            return [];
        }

        preg_match_all(
            '~\\["(?P<id>[A-Za-z0-9_-]{20,})",\\["(?P<folder>[A-Za-z0-9_-]+)"\\],"(?P<name>[^"]+)","(?P<mime>[^"]+)"~',
            $decoded,
            $files,
            PREG_SET_ORDER
        );

        return collect($files)
            ->map(fn (array $file) => [
                'id' => $file['id'],
                'name' => $file['name'],
                'mime' => $file['mime'],
                'url' => "https://drive.google.com/file/d/{$file['id']}/view?usp=sharing",
            ])
            ->unique('id')
            ->values()
            ->all();
    }

    private function decodeDrivePayload(string $payload): string
    {
        $decoded = preg_replace_callback(
            '/\\\\x([0-9A-Fa-f]{2})/',
            fn (array $matches) => chr(hexdec($matches[1])),
            $payload
        );

        $decoded = preg_replace_callback(
            '/\\\\u([0-9A-Fa-f]{4})/',
            function (array $matches) {
                $code = hexdec($matches[1]);

                return mb_convert_encoding(pack('n', $code), 'UTF-8', 'UTF-16BE');
            },
            $decoded
        );

        return str_replace(['\\/', '\\\\'], ['/', '\\'], $decoded);
    }

    private function normalizeFilename(string $fileName): string
    {
        $normalized = strtolower($fileName);
        $normalized = preg_replace('/^(surat|surat_keluar|sppd)_\d{8}_\d{6}_/i', '', $normalized);
        $normalized = preg_replace('/[^a-z0-9]+/i', '_', $normalized);

        return trim((string) $normalized, '_');
    }

    private function extractDriveFileId(string $url): ?string
    {
        if (preg_match('~drive\.google\.com/file/d/([^/]+)~', $url, $matches)) {
            return $matches[1];
        }

        if (str_contains($url, 'drive.google.com') && preg_match('~[?&]id=([^&]+)~', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function isDriveFolderUrl(string $url): bool
    {
        return (bool) preg_match('~drive\.google\.com/drive/folders/~', $url);
    }
}
