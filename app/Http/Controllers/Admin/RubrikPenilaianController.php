<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RubrikKategori;
use App\Models\RubrikSkala;
use App\Services\GradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class RubrikPenilaianController extends Controller
{
    public function __construct(
        protected GradingService $gradingService
    ) {}

    /**
     * Tampilkan matriks rubrik penilaian dinamis sesuai spesifikasi Excel.
     */
    public function index(): View
    {
        $categories = RubrikKategori::with(['skalas' => function ($q) {
            $q->orderBy('urutan');
        }])->orderBy('urutan')->get();

        $tiers = [
            ['grade' => 'A',  'range' => '80 – 100', 'min' => 80.0, 'max' => 100.0, 'badge' => 'badge-success'],
            ['grade' => 'B+', 'range' => '75 – 79',  'min' => 75.0, 'max' => 79.99, 'badge' => 'badge-primary'],
            ['grade' => 'B',  'range' => '70 – 74',  'min' => 70.0, 'max' => 74.99, 'badge' => 'badge-info'],
            ['grade' => 'C+', 'range' => '65 – 69',  'min' => 65.0, 'max' => 69.99, 'badge' => 'badge-warning'],
            ['grade' => 'C',  'range' => '60 – 64',  'min' => 60.0, 'max' => 64.99, 'badge' => 'badge-warning'],
            ['grade' => 'D+', 'range' => '50 – 59',  'min' => 50.0, 'max' => 59.99, 'badge' => 'badge-danger'],
            ['grade' => 'D',  'range' => '40 – 49',  'min' => 40.0, 'max' => 49.99, 'badge' => 'badge-danger'],
            ['grade' => 'E',  'range' => '0 – 39',   'min' => 0.0,  'max' => 39.99, 'badge' => 'badge-secondary'],
        ];

        // Format data matrix: rows = tiers, columns = categories
        $matrix = [];
        foreach ($tiers as $tier) {
            $row = [
                'grade' => $tier['grade'],
                'range' => $tier['range'],
                'badge' => $tier['badge'],
                'descriptors' => [],
            ];

            foreach ($categories as $cat) {
                $skala = $cat->skalas->firstWhere('nilai_huruf', $tier['grade']);
                $row['descriptors'][$cat->kode] = [
                    'id' => $skala?->id,
                    'kategori_nama' => $cat->nama_kategori,
                    'text' => $skala?->deskripsi ?? '-',
                ];
            }

            $matrix[] = $row;
        }

        return view('admin.rubrik.index', compact('categories', 'tiers', 'matrix'));
    }

    /**
     * API JSON untuk memuat rubrik dinamis pada modal penilaian.
     */
    public function apiData(): JsonResponse
    {
        $categories = RubrikKategori::with(['skalas' => function ($q) {
            $q->orderBy('urutan');
        }])->orderBy('urutan')->get();

        $scale = $this->gradingService->getRubricGradeScale();

        $formatted = $categories->map(function ($cat) {
            return [
                'id' => $cat->id,
                'kode' => $cat->kode,
                'nama' => $cat->nama_kategori,
                'bobot' => (float) $cat->bobot,
                'deskripsi' => $cat->deskripsi,
                'levels' => $cat->skalas->map(function ($skala) {
                    return [
                        'id' => $skala->id,
                        'grade' => $skala->nilai_huruf,
                        'range' => $skala->rentang_nilai,
                        'min' => (float) $skala->nilai_min,
                        'max' => (float) $skala->nilai_max,
                        'text' => $skala->deskripsi,
                        'badge' => $skala->badge_class,
                    ];
                })->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'categories' => $formatted,
            'scale' => $scale,
        ]);
    }

    /**
     * Perbarui deskriptor skala rubrik.
     */
    public function update(Request $request, RubrikSkala $skala): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'deskripsi' => ['required', 'string', 'max:1000'],
        ]);

        $skala->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Deskripsi rubrik berhasil diperbarui.',
                'skala' => $skala,
            ]);
        }

        Alert::toast('Deskripsi rubrik berhasil diperbarui.', 'success');

        return back();
    }
}
