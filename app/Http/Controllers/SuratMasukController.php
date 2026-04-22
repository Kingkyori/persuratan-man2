<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client as HttpClient;

class SuratMasukController extends Controller
{
    /**
     * Display surat masuk page with data
     */
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

    /**
     * Store new surat masuk entry with file upload to Google Drive
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'origin' => 'required|string|max:255',
                'reception_date' => 'required|date',
                'letter_number' => 'required|string|max:255|unique:surat_masuk',
                'subject' => 'required|string|max:500',
                'reference_number' => 'nullable|string|max:255',
                'status' => 'required|in:pending,done,disposed',
                'notes' => 'nullable|string',
                'letter_scan' => 'nullable|file|mimes:pdf,jpg,jpeg,png,img|max:10240', // Max 10MB
            ]);

            $validated['user_id'] = session('user_id') ?? auth()->id();
            $validated['google_drive_link'] = null;
            $validated['file_name'] = null;
            
            // Upload file ke Google Drive jika ada
            if ($request->hasFile('letter_scan')) {
                $file = $request->file('letter_scan');
                $validated['file_name'] = $file->getClientOriginalName();
                
                // Upload ke Google Drive
                $drive_link = $this->uploadToGoogleDrive($file);
                if ($drive_link) {
                    $validated['google_drive_link'] = $drive_link;
                }
            }

            $surat = SuratMasuk::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Surat masuk berhasil ditambahkan!',
                'data' => $surat
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Surat Masuk Store Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload file to Google Drive menggunakan Google Apps Script dengan base64 encoding
     * Endpoint: https://script.google.com/macros/s/AKfycbw2lIpT8tgHQ-q_qSRLX5kb5v01V-q1CKrhm8FDePaK8bpjFWtEFYz2QRO2gdsM7ERl/exec
     */
    private function uploadToGoogleDrive($file)
    {
        try {
            // Bersihkan nama file dari karakter aneh
            $cleanFilename = preg_replace('/[^A-Za-z0-9\-_\.]/', '_', $file->getClientOriginalName());
            $filename = 'surat_masuk_' . time() . '_' . $cleanFilename;

            // Encode file ke base64
            $fileContent = base64_encode(file_get_contents($file->getRealPath()));

            // URL Google Apps Script Web App
            $scriptUrl = 'https://script.google.com/macros/s/AKfycbw2lIpT8tgHQ-q_qSRLX5kb5v01V-q1CKrhm8FDePaK8bpjFWtEFYz2QRO2gdsM7ERl/exec';

            // Kirim ke Google Apps Script menggunakan Laravel Http
            $response = Http::asForm()
                ->timeout(60)
                ->post($scriptUrl, [
                    'type' => 'surat_masuk',
                    'filename' => $filename,
                    'file' => $fileContent,
                ]);

            $result = $response->json();

            // Cek apakah upload berhasil
            if (!isset($result['success']) || !$result['success']) {
                \Log::warning('Google Drive Upload Failed: ' . ($result['error'] ?? 'Unknown error'));
                return null;
            }

            // Return file URL dari Google Drive
            if (isset($result['url'])) {
                return $result['url'];
            } elseif (isset($result['fileLink'])) {
                return $result['fileLink'];
            }

            \Log::warning('No file URL returned from Google Apps Script');
            return null;

        } catch (\Exception $e) {
            \Log::error('Google Apps Script Upload Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update surat masuk status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $surat = SuratMasuk::findOrFail($id);
            
            $validated = $request->validate([
                'status' => 'required|in:pending,done,disposed'
            ]);

            $surat->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Status surat berhasil diubah!',
                'data' => $surat
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get surat masuk data for table (AJAX)
     */
    public function getData(Request $request)
    {
        $query = SuratMasuk::query();

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('reception_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('reception_date', '<=', $request->to_date);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('origin', 'like', "%$search%")
                  ->orWhere('subject', 'like', "%$search%")
                  ->orWhere('letter_number', 'like', "%$search%");
            });
        }

        $surat_masuk = $query->orderBy('reception_date', 'desc')->paginate(10);

        return response()->json($surat_masuk);
    }

    /**
     * Delete surat masuk
     */
    public function destroy($id)
    {
        try {
            $surat = SuratMasuk::findOrFail($id);
            $surat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Surat masuk berhasil dihapus!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
