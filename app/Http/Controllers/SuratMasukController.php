<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\User;
use App\Services\GoogleDriveUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SuratMasukController extends Controller
{
    public function __construct(
        private GoogleDriveUploadService $googleDriveUploadService
    ) {
    }

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

            $uploadResult = $this->googleDriveUploadService->upload(
                $file,
                'surat_masuk',
                'surat_masuk',
                $fileName
            );

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
                'user_id' => $this->resolveExistingUserId(),
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
