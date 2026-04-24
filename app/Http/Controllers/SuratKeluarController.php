<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\User;
use App\Services\GoogleDriveUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SuratKeluarController extends Controller
{
    public function __construct(
        private GoogleDriveUploadService $googleDriveUploadService
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
            'status' => 'required|in:draft,review,revisi,final',
            'notes' => 'nullable|string',
            'letter_file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        try {
            $file = $request->file('letter_file');
            $originalName = $file->getClientOriginalName();
            $safeOriginalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $fileName = 'surat_keluar_' . now()->format('Ymd_His') . '_' . $safeOriginalName;

            $uploadResult = $this->googleDriveUploadService->upload(
                $file,
                'surat_keluar',
                'surat_keluar',
                $fileName
            );

            $surat = SuratKeluar::create([
                'destination' => $validated['destination'],
                'letter_date' => $validated['letter_date'],
                'letter_number' => $validated['letter_number'],
                'subject' => $validated['subject'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'google_drive_link' => $uploadResult['url'],
                'file_name' => $originalName,
                'user_id' => $this->resolveExistingUserId(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Surat keluar berhasil disimpan dan file terunggah ke Drive.',
                'data' => [
                    'id' => $surat->id,
                    'tanggal' => optional($surat->letter_date)->format('d M Y'),
                    'tujuan' => $surat->destination,
                    'perihal' => $surat->subject,
                    'nomor' => $surat->letter_number,
                    'file' => $surat->file_name,
                    'status' => $surat->status,
                    'google_drive_link' => $surat->google_drive_link,
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
