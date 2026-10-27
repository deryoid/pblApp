<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Periode;
use App\Models\ProjectCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShowcaseController extends Controller
{
    /**
     * Tampilkan halaman utama SPA Showcase Proyek Selesai.
     */
    public function index(Request $request): View
    {
        $data = $this->fetchShowcaseData($request);

        return view('index', $data);
    }

    /**
     * Endpoint API JSON untuk interaksi SPA real-time.
     */
    public function getData(Request $request): JsonResponse
    {
        $data = $this->fetchShowcaseData($request);

        return response()->json([
            'status' => 'success',
            'projects' => $data['projects'],
            'stats' => $data['stats'],
        ]);
    }

    /**
     * Ambil data proyek yang sudah selesai beserta agregasi metrik.
     */
    protected function fetchShowcaseData(Request $request): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('project_cards')) {
            return [
                'projects' => collect(),
                'stats' => [
                    'total_completed' => 0,
                    'total_mitra' => 0,
                    'total_mahasiswa' => 0,
                    'total_matkul' => 0,
                ],
                'periodes' => collect(),
                'skemas' => ['Internal', 'External', 'Hibah/Penelitian', 'Lomba'],
                'semesters' => range(1, 8),
            ];
        }

        $query = ProjectCard::query()
            ->with([
                'kelompok.mahasiswas.user',
                'periode',
                'mataKuliahs',
                'dosenPenentu',
                'dosenPenilai.user',
                'evaluasiMitra',
                'penilaianDosen',
            ]);

        // Prioritaskan proyek selesai atau progress 100%
        $query->where(function ($q) {
            $q->where('status_proyek', 'Selesai')
                ->orWhere('progress', '>=', 100)
                ->orWhere('status', 'Selesai');
        });

        // Filter: Pencarian teks
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('topik_proyek', 'like', "%{$search}%")
                    ->orWhere('nama_mitra', 'like', "%{$search}%")
                    ->orWhereHas('kelompok', function ($kq) use ($search) {
                        $kq->where('nama_kelompok', 'like', "%{$search}%")
                            ->orWhereHas('mahasiswas', function ($mq) use ($search) {
                                $mq->where('nama_mahasiswa', 'like', "%{$search}%")
                                    ->orWhere('nim', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // Filter: Skema PBL
        if ($request->filled('skema') && $request->input('skema') !== 'all') {
            $query->where('skema_pbl', $request->input('skema'));
        }

        // Filter: Jenis Proyek (internal / external)
        if ($request->filled('jenis') && $request->input('jenis') !== 'all') {
            $query->where('jenis_proyek', $request->input('jenis'));
        }

        // Filter: Periode
        if ($request->filled('periode_id') && $request->input('periode_id') !== 'all') {
            $query->where('periode_id', $request->input('periode_id'));
        }

        // Filter: Semester
        if ($request->filled('semester') && $request->input('semester') !== 'all') {
            $semester = (int) $request->input('semester');
            $query->whereHas('kelompok', function ($kq) use ($semester) {
                $kq->where('semester', $semester);
            });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        if ($sort === 'title_asc') {
            $query->orderBy('title', 'asc');
        } elseif ($sort === 'title_desc') {
            $query->orderBy('title', 'desc');
        } elseif ($sort === 'progress_desc') {
            $query->orderByDesc('progress');
        } else {
            $query->latest('updated_at');
        }

        $rawProjects = $query->get();

        // Transformasikan data proyek agar siap pakai dan aman untuk JSON SPA
        $projects = $rawProjects->map(function (ProjectCard $card) {
            $members = $card->kelompok?->mahasiswas?->map(function ($mhs) {
                return [
                    'id' => $mhs->id,
                    'nim' => $mhs->nim,
                    'nama' => $mhs->nama_mahasiswa,
                    'role' => $mhs->pivot->role ?? 'Anggota',
                    'avatar' => $mhs->user?->profile_photo_data_url ?? null,
                ];
            }) ?? collect();

            $courses = $card->mataKuliahs?->map(function ($mk) {
                return [
                    'kode' => $mk->kode_matkul,
                    'nama' => $mk->nama_matkul,
                    'sks' => $mk->sks,
                    'semester' => $mk->semester,
                ];
            }) ?? collect();

            // Rata-rata skor evaluasi mitra jika ada
            $mitraScore = null;
            if ($card->evaluasiMitra && $card->evaluasiMitra->isNotEmpty()) {
                $mitraScore = round($card->evaluasiMitra->avg('nilai_akhir'), 1);
            }

            // Dosen penilai
            $evaluators = $card->dosenPenilai?->map(function ($dp) {
                return $dp->user?->nama_user ?? 'Dosen Penilai';
            })->filter()->values() ?? collect();

            return [
                'id' => $card->id,
                'uuid' => $card->uuid,
                'title' => $card->title ?: 'Proyek PBL Tanpa Judul',
                'description' => $card->description ?: 'Proyek inovasi berbasis pemecahan masalah nyata yang diselesaikan secara komprehensif oleh tim mahasiswa.',
                'topik' => $card->topik_proyek ?: 'Inovasi Rekayasa & Teknologi Informasi',
                'jenis_proyek' => $card->jenis_proyek ?: 'internal',
                'skema_pbl' => $card->skema_pbl ?: 'Internal',
                'status_proyek' => $card->status_proyek ?: 'Selesai',
                'progress' => $card->progress > 0 ? $card->progress : 100,
                'nama_mitra' => $card->nama_mitra,
                'kontak_mitra' => $card->kontak_mitra,
                'alamat_mitra' => $card->alamat_mitra,
                'link_drive' => $card->link_drive_proyek ?: ($card->kelompok?->link_drive ?: null),
                'tanggal_mulai' => $card->tanggal_mulai ? $card->tanggal_mulai->format('d M Y') : null,
                'tanggal_selesai' => $card->tanggal_selesai ? $card->tanggal_selesai->format('d M Y') : null,
                'kelompok' => [
                    'nama' => $card->kelompok?->nama_kelompok ?? 'Kelompok PBL',
                    'semester' => $card->kelompok?->semester ?? 1,
                ],
                'periode' => $card->periode?->periode ?? 'Aktif',
                'members' => $members,
                'courses' => $courses,
                'evaluators' => $evaluators,
                'mitra_score' => $mitraScore,
                'labels' => $card->labels ?: [],
            ];
        });

        // Hitung statistik dashboard
        $totalCompleted = ProjectCard::where('status_proyek', 'Selesai')
            ->orWhere('progress', '>=', 100)
            ->count();

        $totalMitra = ProjectCard::whereNotNull('nama_mitra')
            ->where('nama_mitra', '!=', '')
            ->distinct('nama_mitra')
            ->count('nama_mitra');

        $totalMahasiswa = Mahasiswa::count();
        $totalMatkul = MataKuliah::count();

        $periodes = Periode::orderByDesc('id')->get(['id', 'periode', 'status_periode']);
        $skemas = ['Internal', 'External', 'Hibah/Penelitian', 'Lomba'];
        $semesters = range(1, 8);

        return [
            'projects' => $projects,
            'stats' => [
                'total_completed' => max($totalCompleted, $projects->count()),
                'total_mitra' => max($totalMitra, $projects->whereNotNull('nama_mitra')->count()),
                'total_mahasiswa' => $totalMahasiswa,
                'total_matkul' => $totalMatkul,
            ],
            'periodes' => $periodes,
            'skemas' => $skemas,
            'semesters' => $semesters,
        ];
    }
}
