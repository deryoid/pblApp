<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EvaluasiMitra;
use App\Models\Mahasiswa;
use App\Models\ProjectCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PenilaianMitraController extends Controller
{
    /**
     * Display all penilaian mitra
     * Menampilkan semua penilaian mitra yang ada
     */
    public function all()
    {
        try {
            $evaluations = EvaluasiMitra::with(['mahasiswa.user', 'projectCard', 'kelompok'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('public.penilaian_mitra.all', compact('evaluations'));

        } catch (\Exception $e) {
            Log::error('Error in penilaian mitra all', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(500, 'Terjadi kesalahan saat memuat data penilaian: '.$e->getMessage());
        }
    }

    /**
     * Display public penilaian mitra page
     * Menampilkan form penilaian mitra tanpa login
     */
    public function index(ProjectCard $card)
    {
        try {
            // Debug: Log card info
            Log::info('Penilaian mitra index called', [
                'card_id' => $card->id,
                'card_uuid' => $card->uuid,
                'card_title' => $card->title,
            ]);

            // Load project dengan relasi yang diperlukan
            $card->load([
                'list.kelompok.mahasiswas' => function ($query) {
                    $query->with(['user' => function ($query) {
                        $query->select('id', 'uuid', 'nama_user', 'email', 'username', 'profile_photo', 'profile_photo_mime');
                    }]);
                },
                'evaluasiMitra.mahasiswa',
            ]);

            // Check apakah project ditemukan
            if (! $card) {
                abort(404, 'Proyek tidak ditemukan');
            }

            // Check if required relations exist
            if (! $card->list) {
                abort(404, 'Proyek tidak terkait dengan list');
            }

            if (! $card->list->kelompok) {
                abort(404, 'Proyek tidak terkait dengan kelompok');
            }

            // Generate shareable link
            $shareableLink = route('public.penilaian-mitra.index', $card->uuid);

            // Get existing evaluations
            $existingEvaluations = $card->evaluasiMitra ?? collect([]);

            Log::info('Penilaian mitra index success', [
                'card_uuid' => $card->uuid,
                'mahasiswas_count' => $card->list->kelompok->mahasiswas->count(),
                'evaluations_count' => $existingEvaluations->count(),
            ]);

            return view('public.penilaian_mitra.index', compact('card', 'shareableLink', 'existingEvaluations'));

        } catch (\Exception $e) {
            Log::error('Error in penilaian mitra index', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'card_uuid' => $card->uuid ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            abort(500, 'Terjadi kesalahan saat memuat data proyek: '.$e->getMessage());
        }
    }

    /**
     * Get project data for AJAX request
     */
    public function getData(ProjectCard $card)
    {
        try {
            Log::info('Penilaian mitra getData called', [
                'card_uuid' => $card->uuid,
                'card_id' => $card->id,
            ]);

            $card->load([
                'list.kelompok.mahasiswas' => function ($query) {
                    $query->with(['user' => function ($query) {
                        $query->select('id', 'uuid', 'nama_user', 'email', 'username', 'profile_photo', 'profile_photo_mime');
                    }]);
                },
                'evaluasiMitra.mahasiswa',
            ]);

            // Check if required relations exist
            if (! $card->list || ! $card->list->kelompok) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kelompok tidak ditemukan',
                ], 404);
            }

            // Prepare mahasiswa data
            $mahasiswas = $card->list->kelompok->mahasiswas->map(function ($mahasiswa) use ($card) {
                $existingEval = $card->evaluasiMitra->firstWhere('mahasiswa_id', $mahasiswa->id);

                // Prepare profile photo data
                $profilePhoto = null;
                if ($mahasiswa->user && $mahasiswa->user->profile_photo) {
                    $profilePhoto = 'data:'.$mahasiswa->user->profile_photo_mime.';base64,'.base64_encode($mahasiswa->user->profile_photo);
                }

                return [
                    'id' => $mahasiswa->id,
                    'uuid' => $mahasiswa->uuid,
                    'nim' => $mahasiswa->nim,
                    'nama' => $mahasiswa->nama_mahasiswa,
                    'nama_mahasiswa' => $mahasiswa->nama_mahasiswa,
                    'profile_photo' => $profilePhoto,
                    'existing_evaluation' => $existingEval ? [
                        'id' => $existingEval->id,
                        'komunikasi_sikap' => $existingEval->m_kehadiran, // m_kehadiran -> komunikasi_sikap
                        'hasil_pekerjaan' => $existingEval->m_presentasi, // m_presentasi -> hasil_pekerjaan
                        'nilai_akhir' => $existingEval->nilai_akhir,
                        'grade' => $existingEval->grade,
                        'catatan' => $existingEval->catatan,
                        'created_at' => $existingEval->created_at->format('d M Y H:i'),
                    ] : null,
                ];
            });

            // Project info
            $projectInfo = [
                'id' => $card->id,
                'uuid' => $card->uuid,
                'title' => $card->title,
                'description' => $card->description,
                'nama_mitra' => $card->nama_mitra,
                'kontak_mitra' => $card->kontak_mitra,
                'skema_pbl' => $card->skema_pbl,
                'tanggal_mulai' => $card->tanggal_mulai ? $card->tanggal_mulai->format('d M Y') : null,
                'tanggal_selesai' => $card->tanggal_selesai ? $card->tanggal_selesai->format('d M Y') : null,
                'status_proyek' => $card->status_proyek ?? 'Proses',
                'progress' => (int) ($card->progress ?? 0),
                'labels' => $card->labels ?? [],
                'link_drive_proyek' => $card->link_drive_proyek,
                'kelompok' => [
                    'nama_kelompok' => $card->list->kelompok->nama_kelompok,
                ],
            ];

            Log::info('Penilaian mitra getData success', [
                'card_uuid' => $card->uuid,
                'mahasiswas_count' => $mahasiswas->count(),
            ]);

            return response()->json([
                'success' => true,
                'project' => $projectInfo,
                'mahasiswas' => $mahasiswas,
                'evaluations_count' => $card->evaluasiMitra->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in penilaian mitra getData', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'card_uuid' => $card->uuid ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data proyek: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit penilaian mitra
     */
    public function submit(Request $request, ProjectCard $card)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'evaluations' => 'required|array|min:1',
                'evaluations.*.mahasiswa_id' => 'required|integer|exists:mahasiswa,id',
                'evaluations.*.komunikasi_sikap' => 'required|integer|min:0|max:100',
                'evaluations.*.hasil_pekerjaan' => 'required|integer|min:0|max:100',
                'evaluations.*.catatan' => 'nullable|string|max:1000',
            ], [
                'evaluations.required' => 'Data penilaian mahasiswa wajib diisi',
                'evaluations.*.komunikasi_sikap.required' => 'Nilai komunikasi & sikap wajib diisi',
                'evaluations.*.hasil_pekerjaan.required' => 'Nilai hasil pekerjaan wajib diisi',
                'evaluations.*.komunikasi_sikap.max' => 'Nilai maksimal adalah 100',
                'evaluations.*.hasil_pekerjaan.max' => 'Nilai maksimal adalah 100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Process each evaluation
            $savedEvaluations = [];
            $evaluationSettings = $this->getEvaluationSettings();

            foreach ($request->evaluations as $evalData) {
                $mahasiswaId = $evalData['mahasiswa_id'];
                $komunikasiSikap = (int) $evalData['komunikasi_sikap'];
                $hasilPekerjaan = (int) $evalData['hasil_pekerjaan'];

                // Calculate final score (50% komunikasi + 50% hasil pekerjaan)
                $nilaiAkhir = ($komunikasiSikap * 0.5) + ($hasilPekerjaan * 0.5);

                // Determine grade
                $grade = $this->calculateGrade($nilaiAkhir);

                // Check if evaluation already exists
                $existingEval = EvaluasiMitra::where('project_card_id', $card->id)
                    ->where('mahasiswa_id', $mahasiswaId)
                    ->first();

                if ($existingEval) {
                    // Update existing evaluation
                    $existingEval->update([
                        'm_kehadiran' => $komunikasiSikap, // komunikasi & sikap -> m_kehadiran
                        'm_presentasi' => $hasilPekerjaan, // hasil pekerjaan -> m_presentasi
                        'catatan' => $evalData['catatan'] ?? null,
                        'status' => 'submitted', // Mark as submitted
                    ]);

                    $savedEvaluations[] = $existingEval;
                } else {
                    // Create new evaluation
                    $newEval = EvaluasiMitra::create([
                        'project_card_id' => $card->id,
                        'mahasiswa_id' => $mahasiswaId,
                        'm_kehadiran' => $komunikasiSikap, // komunikasi & sikap -> m_kehadiran
                        'm_presentasi' => $hasilPekerjaan, // hasil pekerjaan -> m_presentasi
                        'catatan' => $evalData['catatan'] ?? null,
                        'status' => 'submitted', // Mark as submitted
                        'uuid' => \Str::uuid(),
                        'periode_id' => 1, // Default periode ID
                        'kelompok_id' => $card->list->kelompok->id ?? 1, // Get from project
                        'evaluasi_master_id' => null, // Not required for public evaluation
                    ]);

                    $savedEvaluations[] = $newEval;
                }
            }

            // Log the submission
            Log::info('Penilaian mitra submitted', [
                'card_id' => $card->id,
                'card_uuid' => $card->uuid,
                'evaluator_name' => $request->evaluator_name,
                'evaluator_email' => $request->evaluator_email,
                'evaluations_count' => count($savedEvaluations),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Penilaian mitra berhasil disimpan!',
                'evaluations' => array_map(function ($eval) {
                    return [
                        'mahasiswa_id' => $eval->mahasiswa_id,
                        'nilai_akhir' => $eval->nilai_akhir,
                        'grade' => $eval->grade,
                    ];
                }, $savedEvaluations),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in penilaian mitra submit', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'card_uuid' => $card->uuid ?? null,
                'request_data' => $request->except(['evaluations']), // Don't log sensitive evaluation data
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan penilaian. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Get evaluation settings
     */
    private function getEvaluationSettings()
    {
        // Default settings - bisa diambil dari database jika perlu
        return [
            'komunikasi_sikap_weight' => 0.5, // 50%
            'hasil_pekerjaan_weight' => 0.5, // 50%
        ];
    }

    /**
     * Calculate grade based on score
     */
    private function calculateGrade($score)
    {
        if ($score >= 85) {
            return 'A';
        }
        if ($score >= 75) {
            return 'B';
        }
        if ($score >= 65) {
            return 'C';
        }
        if ($score >= 55) {
            return 'D';
        }

        return 'E';
    }
}
