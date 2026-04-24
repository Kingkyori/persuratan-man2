<?php

namespace App\Http\Controllers;

use App\Models\Sppd;
use App\Models\User;
use App\Services\GoogleDriveUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SppdController extends Controller
{
    public function __construct(
        private GoogleDriveUploadService $googleDriveUploadService
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
            'status' => 'required|in:draft,review,aktif,selesai',
            'notes' => 'nullable|string',
            'attachment_file' => 'required|file|mimes:pdf,png,jpg,jpeg|max:10240',
        ]);

        try {
            $file = $request->file('attachment_file');
            $originalName = $file->getClientOriginalName();
            $safeOriginalName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
            $fileName = 'sppd_' . now()->format('Ymd_His') . '_' . $safeOriginalName;

            $uploadResult = $this->googleDriveUploadService->upload(
                $file,
                'sppd',
                'sppd',
                $fileName
            );

            $record = Sppd::create([
                'employee_id' => null,
                'employee_name' => $validated['employee_name'],
                'destination' => $validated['destination'],
                'departure_date' => $validated['departure_date'],
                'duration_days' => $validated['duration_days'],
                'purpose' => $validated['purpose'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'google_drive_link' => $uploadResult['url'],
                'file_name' => $originalName,
                'user_id' => $this->resolveExistingUserId(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data SPPD berhasil disimpan dan file terunggah ke Drive.',
                'data' => [
                    'id' => $record->id,
                    'pegawai' => $record->employee_name ?: '-',
                    'tujuan' => $record->destination,
                    'tanggal' => optional($record->departure_date)->format('d M Y'),
                    'durasi' => $record->duration_days . ' hari',
                    'kepentingan' => $record->purpose,
                    'status' => $record->status,
                    'file' => $record->file_name,
                    'google_drive_link' => $record->google_drive_link,
                    'catatan' => $record->notes ?: '-',
                ],
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
