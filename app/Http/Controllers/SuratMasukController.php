<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SuratMasukController extends Controller
{
    public function index()
    {
        $surat_masuk = SuratMasuk::orderBy('reception_date', 'desc')->paginate(10);
        $stats = [
            'total_received' => SuratMasuk::count(),
            'disposed' => SuratMasuk::where('status', 'disposed')->count(),
            'pending' => SuratMasuk::where('status', 'pending')->count(),
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
            'reference_number' => 'nullable|string|max:255',
            'status' => 'required|in:pending,done,disposed',
            'notes' => 'nullable|string',
            'letter_scan' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        try {
            $file = $request->file('letter_scan');
            $originalName = $file->getClientOriginalName();
            $safeOriginalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $fileName = 'surat_' . now()->format('Ymd_His') . '_' . $safeOriginalName;

            $uploadResult = $this->uploadFileToGoogleDrive($file, $fileName);
            $sessionUserId = data_get(session('user'), 'id');
            $authenticatedUserId = auth()->id();
            $resolvedUserId = null;

            if ($authenticatedUserId && User::whereKey($authenticatedUserId)->exists()) {
                $resolvedUserId = $authenticatedUserId;
            } elseif ($sessionUserId && User::whereKey($sessionUserId)->exists()) {
                $resolvedUserId = $sessionUserId;
            }

            $surat = SuratMasuk::create([
                'origin' => $validated['origin'],
                'reception_date' => $validated['reception_date'],
                'letter_number' => $validated['letter_number'],
                'subject' => $validated['subject'],
                'reference_number' => $validated['reference_number'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'google_drive_link' => $uploadResult['url'],
                'file_name' => $originalName,
                'user_id' => $resolvedUserId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Surat berhasil disimpan dan file terunggah ke Drive.',
                'data' => $surat,
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
            SuratMasuk::orderBy('reception_date', 'desc')->get()
        );
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $surat = SuratMasuk::find($id);
            if (!$surat) {
                return response()->json(['success' => false, 'message' => 'Surat tidak ditemukan'], 404);
            }

            $surat->update(['status' => $request->status]);

            return response()->json(['success' => true, 'message' => 'Status berhasil diubah']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

    private function uploadFileToGoogleDrive($file, string $fileName): array
    {
        $webAppUrl = config('services.google_apps_script.web_app_url');

        if (!$webAppUrl) {
            throw new \RuntimeException('URL Google Apps Script belum dikonfigurasi.');
        }

        $fileContents = file_get_contents($file->getRealPath());

        if ($fileContents === false) {
            throw new \RuntimeException('File upload tidak dapat dibaca.');
        }

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $base64Contents = base64_encode($fileContents);
        $timeout = (int) config('services.google_apps_script.timeout', 120);

        $jsonPayload = [
            'action' => 'upload',
            'type' => 'surat_masuk',
            'folder' => 'surat_masuk',
            'folderName' => 'surat_masuk',
            'filename' => $fileName,
            'original_filename' => $file->getClientOriginalName(),
            'originalFilename' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'mimeType' => $mimeType,
            'encoding' => 'base64',
            'file' => [
                'contents' => $base64Contents,
                'filename' => $fileName,
                'originalFilename' => $file->getClientOriginalName(),
                'mimeType' => $mimeType,
            ],
            // Beberapa versi Apps Script membaca field flat seperti ini.
            'contents' => $base64Contents,
            'fileData' => $base64Contents,
        ];

        $jsonResponse = Http::acceptJson()
            ->timeout($timeout)
            ->post($webAppUrl, $jsonPayload);

        $parsedJsonResponse = $this->normalizeAppsScriptResponse($jsonResponse);

        if ($parsedJsonResponse['success']) {
            return $parsedJsonResponse;
        }

        Log::warning('Google Apps Script JSON upload failed, trying form fallback.', [
            'status' => $jsonResponse->status(),
            'response' => $parsedJsonResponse,
        ]);

        $formResponse = Http::acceptJson()
            ->asForm()
            ->timeout($timeout)
            ->post($webAppUrl, [
                'action' => 'upload',
                'type' => 'surat_masuk',
                'folder' => 'surat_masuk',
                'filename' => $fileName,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'encoding' => 'base64',
                'file' => $base64Contents,
            ]);

        $parsedFormResponse = $this->normalizeAppsScriptResponse($formResponse);

        if ($parsedFormResponse['success']) {
            return $parsedFormResponse;
        }

        Log::warning('Google Apps Script form upload failed, trying multipart fallback.', [
            'status' => $formResponse->status(),
            'response' => $parsedFormResponse,
        ]);

        $multipartResponse = Http::acceptJson()
            ->timeout($timeout)
            ->attach('file', $fileContents, $fileName, ['Content-Type' => $mimeType])
            ->post($webAppUrl, [
                'action' => 'upload',
                'type' => 'surat_masuk',
                'folder' => 'surat_masuk',
                'filename' => $fileName,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
            ]);

        $parsedMultipartResponse = $this->normalizeAppsScriptResponse($multipartResponse);

        if ($parsedMultipartResponse['success']) {
            return $parsedMultipartResponse;
        }

        Log::warning('Google Drive Upload Error', [
            'json_response' => $parsedJsonResponse,
            'form_response' => $parsedFormResponse,
            'multipart_response' => $parsedMultipartResponse,
        ]);

        throw new \RuntimeException(
            'Gagal upload ke Google Drive. '
            . ($parsedJsonResponse['message']
                ?: $parsedFormResponse['message']
                ?: $parsedMultipartResponse['message']
                ?: 'Periksa deployment Apps Script dan izin folder Drive.')
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
