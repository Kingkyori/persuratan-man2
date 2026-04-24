<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoogleDriveUploadService
{
    public function upload(UploadedFile $file, string $folderKey, string $type, ?string $fileName = null): array
    {
        $webAppUrl = config('services.google_apps_script.web_app_url');
        $folderConfig = config("services.google_apps_script.folders.{$folderKey}", []);
        $folderId = $folderConfig['id'] ?? null;
        $folderUrl = $folderConfig['url'] ?? null;

        if (!$webAppUrl) {
            throw new \RuntimeException(
                'URL Google Apps Script belum dikonfigurasi. Isi GOOGLE_APPS_SCRIPT_WEB_APP_URL pada file .env.'
            );
        }

        $fileContents = file_get_contents($file->getRealPath());

        if ($fileContents === false) {
            throw new \RuntimeException('File upload tidak dapat dibaca.');
        }

        $originalName = $file->getClientOriginalName();
        $safeOriginalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
        $finalFileName = $fileName ?: 'surat_' . now()->format('Ymd_His') . '_' . $safeOriginalName;
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $base64Contents = base64_encode($fileContents);
        $timeout = (int) config('services.google_apps_script.timeout', 120);
        $connectTimeout = (int) config('services.google_apps_script.connect_timeout', 30);

        $commonPayload = [
            'action' => 'upload',
            'type' => $type,
            'folder' => $folderKey,
            'folderName' => $folderKey,
            'folder_key' => $folderKey,
            'folderKey' => $folderKey,
            'filename' => $finalFileName,
            'original_filename' => $originalName,
            'originalFilename' => $originalName,
            'mime_type' => $mimeType,
            'mimeType' => $mimeType,
        ];

        if ($folderId) {
            $commonPayload['folder_id'] = $folderId;
            $commonPayload['folderId'] = $folderId;
        }

        if ($folderUrl) {
            $commonPayload['folder_url'] = $folderUrl;
            $commonPayload['folderUrl'] = $folderUrl;
        }

        $attempts = [
            'json' => function () use ($webAppUrl, $timeout, $connectTimeout, $commonPayload, $base64Contents) {
                return Http::acceptJson()
                    ->connectTimeout($connectTimeout)
                    ->timeout($timeout)
                    ->post($webAppUrl, array_merge($commonPayload, [
                        'encoding' => 'base64',
                        'file' => [
                            'contents' => $base64Contents,
                            'filename' => $commonPayload['filename'],
                            'originalFilename' => $commonPayload['originalFilename'],
                            'mimeType' => $commonPayload['mimeType'],
                        ],
                        'contents' => $base64Contents,
                        'fileData' => $base64Contents,
                    ]));
            },
            'form' => function () use ($webAppUrl, $timeout, $connectTimeout, $commonPayload, $base64Contents) {
                return Http::acceptJson()
                    ->asForm()
                    ->connectTimeout($connectTimeout)
                    ->timeout($timeout)
                    ->post($webAppUrl, array_merge($commonPayload, [
                        'encoding' => 'base64',
                        'file' => $base64Contents,
                    ]));
            },
            'multipart' => function () use ($webAppUrl, $timeout, $connectTimeout, $commonPayload, $fileContents, $mimeType) {
                return Http::acceptJson()
                    ->connectTimeout($connectTimeout)
                    ->timeout($timeout)
                    ->attach('file', $fileContents, $commonPayload['filename'], ['Content-Type' => $mimeType])
                    ->post($webAppUrl, $commonPayload);
            },
        ];

        $results = [];
        $connectionErrors = [];

        foreach ($attempts as $attemptName => $attempt) {
            try {
                $response = $attempt();
                $parsed = $this->normalizeAppsScriptResponse($response);
                $results[$attemptName] = $parsed;

                if ($parsed['success']) {
                    return array_merge($parsed, [
                        'file_name' => $finalFileName,
                        'original_name' => $originalName,
                    ]);
                }

                Log::warning('Google Apps Script upload attempt failed.', [
                    'attempt' => $attemptName,
                    'response' => $parsed,
                ]);
            } catch (ConnectionException $exception) {
                $connectionErrors[$attemptName] = $exception->getMessage();

                Log::warning('Google Apps Script connection failed.', [
                    'attempt' => $attemptName,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if (!empty($connectionErrors)) {
            throw new \RuntimeException(
                'Gagal terhubung ke Google Apps Script. Periksa URL deployment, koneksi internet, dan setelan firewall. '
                . 'Detail: ' . reset($connectionErrors)
            );
        }

        Log::warning('Google Drive Upload Error', $results);

        $message = collect($results)
            ->pluck('message')
            ->filter()
            ->first();

        throw new \RuntimeException(
            'Gagal upload ke Google Drive. '
            . ($message ?: 'Periksa deployment Apps Script dan izin folder Drive.')
        );
    }

    private function normalizeAppsScriptResponse(Response $response): array
    {
        $body = trim($response->body());
        $decoded = $response->json();

        if (!is_array($decoded)) {
            $decoded = json_decode($body, true);
        }

        if (!is_array($decoded) && preg_match('/\{.*\}/s', $body, $matches)) {
            $decoded = json_decode($matches[0], true);
        }

        if (!is_array($decoded)) {
            $decoded = [];
        }

        $url = $decoded['url']
            ?? $decoded['link']
            ?? $decoded['fileUrl']
            ?? $decoded['google_drive_link']
            ?? $decoded['webViewLink']
            ?? null;

        if (!$url && preg_match('~https?://drive\.google\.com/[^\s"\'<]+~i', $body, $matches)) {
            $url = $matches[0];
        }

        $message = $decoded['message']
            ?? $decoded['error']
            ?? $decoded['msg']
            ?? null;

        $success = $decoded['success'] ?? null;

        if (is_string($success)) {
            $success = filter_var($success, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        }

        if ($success === null) {
            $status = strtolower((string) ($decoded['status'] ?? ''));
            $success = in_array($status, ['success', 'ok'], true);
        }

        if ($success === null) {
            $success = $response->successful() && !empty($url);
        }

        return [
            'success' => (bool) $success,
            'url' => $url,
            'message' => $message,
            'status_code' => $response->status(),
            'body_preview' => Str::limit($body, 500),
        ];
    }
}
