<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KunjunganMitra;
use App\Models\Mahasiswa;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class KunjunganMitraController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $mhs = Mahasiswa::where('user_id', $user->id)->first();

        // Get all user IDs from the same kelompok as the current mahasiswa
        $groupMemberIds = collect();
        if ($mhs) {
            $groupMemberIds = DB::table('kelompok_mahasiswa')
                ->where('mahasiswa_id', $mhs->id)
                ->pluck('kelompok_id', 'periode_id')
                ->flatMap(function ($kelompokId, $periodeId) {
                    return DB::table('kelompok_mahasiswa')
                        ->where('kelompok_id', $kelompokId)
                        ->where('periode_id', $periodeId)
                        ->pluck('mahasiswa_id');
                })
                ->unique();
        }

        // Get all user IDs from group members
        $userIds = DB::table('mahasiswa')
            ->whereIn('id', $groupMemberIds)
            ->pluck('user_id')
            ->push($user->id) // Include current user as fallback
            ->unique();

        $items = KunjunganMitra::with(['periode', 'kelompok', 'user'])
            ->whereIn('user_id', $userIds)
            ->latest()->get();

        return view('mahasiswa.kunjungan_mitra.index', compact('items'));
    }

    public function create()
    {
        $user = Auth::user();

        $mhs = Mahasiswa::where('user_id', $user->id)->first();
        // Hanya periode Aktif
        $periodes = Periode::where('status_periode', 'Aktif')->orderBy('created_at', 'desc')->get();

        // Ambil pasangan (kelompok_id, periode_id) yang dimiliki mahasiswa
        $kelompokOptions = collect();
        if ($mhs) {
            $kelompokOptions = DB::table('kelompok_mahasiswa as km')
                ->join('kelompok as k', 'k.id', '=', 'km.kelompok_id')
                ->join('periode as p', 'p.id', '=', 'km.periode_id')
                ->where('km.mahasiswa_id', $mhs->id)
                ->select('k.id as kelompok_id', 'k.nama_kelompok', 'p.id as periode_id', 'p.periode as nama_periode')
                ->orderBy('p.created_at', 'desc')
                ->get();
        }

        return view('mahasiswa.kunjungan_mitra.create', compact('periodes', 'kelompokOptions'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $mhs = Mahasiswa::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'periode_id' => ['required', Rule::exists('periode', 'id')->where('status_periode', 'Aktif')],
            'kelompok_id' => ['required', 'exists:kelompok,id'],
            'perusahaan' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:255'],
            'tanggal_kunjungan' => ['required', 'date'],
            'status_kunjungan' => ['required', Rule::in(['Sudah dikunjungi', 'Proses Pembicaraan', 'Tidak ada tanggapan', 'Ditolak'])],
            'bukti' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:500'],
        ]);

        // Validasi bahwa mahasiswa tsb memang anggota kelompok di periode tsb
        if ($mhs) {
            $exists = DB::table('kelompok_mahasiswa')
                ->where('mahasiswa_id', $mhs->id)
                ->where('kelompok_id', $validated['kelompok_id'])
                ->where('periode_id', $validated['periode_id'])
                ->exists();
            if (! $exists) {
                Alert::toast('Anda bukan anggota kelompok tersebut pada periode terpilih.', 'error');

                return back()->withErrors(['kelompok_id' => 'Anda bukan anggota kelompok tersebut pada periode terpilih.'])->withInput();
            }
        }

        $payload = [
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'periode_id' => $validated['periode_id'],
            'kelompok_id' => $validated['kelompok_id'],
            'user_id' => $user->id,
            'perusahaan' => $validated['perusahaan'],
            'alamat' => $validated['alamat'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'status_kunjungan' => $validated['status_kunjungan'],
        ];

        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $compressedImage = $this->compressImage($file);
            $payload['bukti_kunjungan'] = $compressedImage['data'];
            $payload['bukti_kunjungan_mime'] = $compressedImage['mime'];
        }

        KunjunganMitra::create($payload);
        Alert::toast('Data kunjungan berhasil disimpan.', 'success');

        return redirect()->route('mahasiswa.kunjungan.index');
    }

    public function edit(KunjunganMitra $kunjungan)
    {
        $this->authorizeView($kunjungan);
        // Hanya periode Aktif
        $periodes = Periode::where('status_periode', 'Aktif')->orderBy('created_at', 'desc')->get();
        // kelompok options dibatasi ke milik mahasiswa pembuat
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        $kelompokOptions = collect();
        if ($mhs) {
            $kelompokOptions = DB::table('kelompok_mahasiswa as km')
                ->join('kelompok as k', 'k.id', '=', 'km.kelompok_id')
                ->join('periode as p', 'p.id', '=', 'km.periode_id')
                ->where('km.mahasiswa_id', $mhs->id)
                ->select('k.id as kelompok_id', 'k.nama_kelompok', 'p.id as periode_id', 'p.periode as nama_periode')
                ->orderBy('p.created_at', 'desc')
                ->get();
        }

        return view('mahasiswa.kunjungan_mitra.edit', compact('kunjungan', 'periodes', 'kelompokOptions'));
    }

    public function update(Request $request, KunjunganMitra $kunjungan)
    {
        $this->authorizeView($kunjungan);
        $user = $request->user();
        $mhs = Mahasiswa::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'periode_id' => ['required', Rule::exists('periode', 'id')->where('status_periode', 'Aktif')],
            'kelompok_id' => ['required', 'exists:kelompok,id'],
            'perusahaan' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:255'],
            'tanggal_kunjungan' => ['required', 'date'],
            'status_kunjungan' => ['required', Rule::in(['Sudah dikunjungi', 'Proses Pembicaraan', 'Tidak ada tanggapan', 'Ditolak'])],
            'bukti' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:500'],
            'remove_bukti' => ['sometimes', 'boolean'],
        ]);

        if ($mhs) {
            $exists = DB::table('kelompok_mahasiswa')
                ->where('mahasiswa_id', $mhs->id)
                ->where('kelompok_id', $validated['kelompok_id'])
                ->where('periode_id', $validated['periode_id'])
                ->exists();
            if (! $exists) {
                Alert::toast('Anda bukan anggota kelompok tersebut pada periode terpilih.', 'error');

                return back()->withErrors(['kelompok_id' => 'Anda bukan anggota kelompok tersebut pada periode terpilih.'])->withInput();
            }
        }

        $kunjungan->fill([
            'periode_id' => $validated['periode_id'],
            'kelompok_id' => $validated['kelompok_id'],
            'perusahaan' => $validated['perusahaan'],
            'alamat' => $validated['alamat'],
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'status_kunjungan' => $validated['status_kunjungan'],
        ]);

        if ($request->boolean('remove_bukti')) {
            $kunjungan->bukti_kunjungan = null;
            $kunjungan->bukti_kunjungan_mime = null;
        }
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $compressedImage = $this->compressImage($file);
            $kunjungan->bukti_kunjungan = $compressedImage['data'];
            $kunjungan->bukti_kunjungan_mime = $compressedImage['mime'];
        }

        $kunjungan->save();
        Alert::toast('Data kunjungan berhasil diperbarui.', 'success');

        return redirect()->route('mahasiswa.kunjungan.index');
    }

    public function destroy(KunjunganMitra $kunjungan)
    {
        $this->authorizeView($kunjungan);
        $kunjungan->delete();
        Alert::toast('Data kunjungan dihapus.', 'success');

        return back();
    }

    /**
     * Get all kunjungan data for pagination (dashboard)
     * Menampilkan SELURUH data kunjungan dari database tanpa filter apapun
     * Menggunakan pagination bawaan Laravel untuk efisiensi memory
     */
    public function getDataForDashboard(Request $request)
    {
        try {
            // Build query dengan join untuk mendapatkan nama periode, kelompok, dan user
            $query = KunjunganMitra::select(
                'kunjungan_mitra.id',
                'kunjungan_mitra.tanggal_kunjungan',
                'kunjungan_mitra.perusahaan',
                'kunjungan_mitra.alamat',
                'kunjungan_mitra.status_kunjungan',
                'kunjungan_mitra.bukti_kunjungan',
                'p.periode as periode_nama',
                'k.nama_kelompok as kelompok_nama',
                'u.nama_user as user_nama'
            )
                ->leftJoin('periode as p', 'kunjungan_mitra.periode_id', '=', 'p.id')
                ->leftJoin('kelompok as k', 'kunjungan_mitra.kelompok_id', '=', 'k.id')
                ->leftJoin('users as u', 'kunjungan_mitra.user_id', '=', 'u.id')
                ->orderBy('kunjungan_mitra.tanggal_kunjungan', 'desc')
                ->orderBy('kunjungan_mitra.created_at', 'desc');

            // Apply search if exists
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('kunjungan_mitra.perusahaan', 'like', "%{$search}%")
                        ->orWhere('kunjungan_mitra.alamat', 'like', "%{$search}%")
                        ->orWhere('kunjungan_mitra.status_kunjungan', 'like', "%{$search}%")
                        ->orWhere('p.periode', 'like', "%{$search}%")
                        ->orWhere('k.nama_kelompok', 'like', "%{$search}%")
                        ->orWhere('u.nama_user', 'like', "%{$search}%");
                });
            }

            // Apply pagination dengan 10 data per halaman
            $kunjungans = $query->paginate(10);

            // Return view dengan data pagination
            return view('mahasiswa.partials.kunjungan_table', compact('kunjungans'))->render();

        } catch (\Exception $e) {
            \Log::error('Error in getDataForDashboard', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => true,
                'message' => 'Server error: '.$e->getMessage(),
                'html' => '<div class="alert alert-danger">Gagal memuat data. Silakan refresh halaman.</div>',
            ], 500);
        }
    }

    /**
     * Get bukti kunjungan for AJAX request
     */
    public function getBukti($id)
    {
        try {
            $kunjungan = KunjunganMitra::findOrFail($id);

            // Allow access to all kunjungan for dashboard view
            // No authorization check needed for read-only access

            if (! $kunjungan->bukti_kunjungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bukti kunjungan tidak tersedia',
                ]);
            }

            return response()->json([
                'success' => true,
                'bukti_data_url' => $kunjungan->bukti_data_url,
                'mime_type' => $kunjungan->bukti_kunjungan_mime,
                'perusahaan' => $kunjungan->perusahaan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Kunjungan tidak ditemukan',
            ], 404);
        }
    }

    private function authorizeView(KunjunganMitra $item): void
    {
        if ($item->user_id !== Auth::id()) {
            abort(403, 'Tidak diizinkan.');
        }
    }

    /**
     * Compress image to reduce file size while maintaining quality
     */
    private function compressImage($file): array
    {
        $imageData = file_get_contents($file->getRealPath());
        $mimeType = $file->getMimeType();
        $maxSize = 500 * 1024; // 500KB

        // If already under 500KB, return as-is
        if (strlen($imageData) <= $maxSize) {
            return [
                'data' => $imageData,
                'mime' => $mimeType,
            ];
        }

        // Create image resource based on mime type
        $image = null;
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file->getRealPath());
                break;
            case 'image/png':
                $image = imagecreatefrompng($file->getRealPath());
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file->getRealPath());
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($file->getRealPath());
                break;
        }

        if (! $image) {
            // Fallback: return original image if compression fails
            return [
                'data' => $imageData,
                'mime' => $mimeType,
            ];
        }

        // Get original dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Calculate new dimensions if needed (max 1200px width/height)
        $maxDimension = 1200;
        if ($width > $maxDimension || $height > $maxDimension) {
            $ratio = min($maxDimension / $width, $maxDimension / $height);
            $newWidth = intval($width * $ratio);
            $newHeight = intval($height * $ratio);

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency for PNG
            if ($mimeType == 'image/png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $newImage;
        }

        // Start with high quality and reduce if still too large
        $quality = 85;
        $compressedData = null;

        while ($quality >= 30) { // Don't go below 30% quality
            ob_start();

            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($image, null, $quality);
                    break;
                case 'image/png':
                    // PNG quality is 0-9 (0 = no compression, 9 = max compression)
                    $pngQuality = 9 - (($quality / 100) * 9);
                    imagepng($image, null, intval($pngQuality));
                    break;
                case 'image/gif':
                    imagegif($image);
                    break;
                case 'image/webp':
                    imagewebp($image, null, $quality);
                    break;
            }

            $compressedData = ob_get_contents();
            ob_end_clean();

            if (strlen($compressedData) <= $maxSize) {
                break;
            }

            $quality -= 10; // Reduce quality by 10% and try again
        }

        imagedestroy($image);

        // If compression still fails to reduce size, use original
        if (! $compressedData || strlen($compressedData) > strlen($imageData)) {
            return [
                'data' => $imageData,
                'mime' => $mimeType,
            ];
        }

        return [
            'data' => $compressedData,
            'mime' => $mimeType,
        ];
    }
}
