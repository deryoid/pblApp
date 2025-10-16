<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\KunjunganMitra;
use Illuminate\Http\Request;

class KunjunganMitraController extends Controller
{
    /**
     * Display public kunjungan mitra page
     * Menampilkan semua data kunjungan mitra tanpa login
     */
    public function index()
    {
        return view('public.kunjungan_mitra.index');
    }

    /**
     * Get all kunjungan data for public display
     * Menampilkan SELURUH data kunjungan dari database tanpa filter apapun
     * Menggunakan pagination bawaan Laravel untuk efisiensi memory
     * Optimized for memory usage with chunked queries
     */
    public function getData(Request $request)
    {
        try {
            // Free up memory before processing
            gc_collect_cycles();

            // Build query dengan join untuk mendapatkan nama periode, kelompok, dan user
            // Using select without bukti_kunjungan to reduce memory usage
            $query = KunjunganMitra::select(
                'kunjungan_mitra.id',
                'kunjungan_mitra.uuid',
                'kunjungan_mitra.tanggal_kunjungan',
                'kunjungan_mitra.perusahaan',
                'kunjungan_mitra.alamat',
                'kunjungan_mitra.status_kunjungan',
                // 'kunjungan_mitra.bukti_kunjungan', // Exclude to save memory
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

            // Apply pagination dengan 10 data per halaman untuk efisiensi memory
            $kunjungans = $query->paginate(10);

            // Free memory after query
            unset($query);
            gc_collect_cycles();

            // Return view dengan data pagination
            $html = view('public.kunjungan_mitra.partials.kunjungan_table', compact('kunjungans'))->render();

            // Free memory after render
            unset($kunjungans);
            gc_collect_cycles();

            return $html;

        } catch (\Exception $e) {
            \Log::error('Error in getData', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            // Force garbage collection on error
            gc_collect_cycles();

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

            // Allow access to all kunjungan for public view
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
}
