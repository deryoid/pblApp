@extends('layout.public')

@section('title', 'Penilaian Mitra - ' . ($card->title ?? 'Proyek'))

@section('content')
<style>
    :root {
        --primary-color: #4e73df;
        --secondary-color: #858796;
        --success-color: #1cc88a;
        --info-color: #36b9cc;
        --warning-color: #f6c23e;
        --danger-color: #e74a3b;
        --light-color: #f8f9fc;
        --dark-color: #2e3744;
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    }

    .evaluation-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .project-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, #6f8ce9 100%);
        color: white;
        border-radius: 20px 20px 0 0;
        padding: 2rem;
    }

    .mahasiswa-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid #e3e6f0;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .mahasiswa-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .mahasiswa-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-color);
        box-shadow: 0 2px 8px rgba(78, 115, 223, 0.2);
    }

    .mahasiswa-info {
        flex: 1;
        min-width: 0; /* Prevents text overflow */
    }

    .mahasiswa-name {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 0.25rem;
        word-break: break-word;
    }

    .mahasiswa-nim {
        font-size: 0.9rem;
        color: var(--secondary-color);
        margin-bottom: 0;
    }

    
    .student-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .student-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 2px solid #e3e6f0;
        padding: 0.75rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, #6f8ce9 100%);
        border: none;
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
    }

    .btn-outline-primary {
        border: 2px solid var(--primary-color);
        color: var(--primary-color);
        border-radius: 10px;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-primary:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        transform: translateY(-2px);
    }

    .badge-status {
        font-size: 0.8rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .score-display {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--success-color) 0%, #26c89f 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .grade-badge {
        font-size: 1.5rem;
        font-weight: 800;
        padding: 0.5rem 1rem;
        border-radius: 15px;
    }

    .grade-A { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
    .grade-B { background: linear-gradient(135deg, #17a2b8, #20c997); color: white; }
    .grade-C { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; }
    .grade-D { background: linear-gradient(135deg, #fd7e14, #dc3545); color: white; }
    .grade-E { background: linear-gradient(135deg, #dc3545, #6f42c1); color: white; }

    .info-card {
        background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
        border-left: 5px solid var(--primary-color);
        border-radius: 10px;
        padding: 1.5rem;
    }

    .share-link-section {
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        border-radius: 10px;
        padding: 1rem;
        border: 2px dashed #adb5bd;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
        border-width: 0.3rem;
    }

    .alert-custom {
        border-radius: 10px;
        border: none;
        padding: 1rem 1.5rem;
    }

    @media (max-width: 768px) {
        .evaluation-container {
            margin: 0.5rem;
            border-radius: 15px;
        }

        .project-header {
            padding: 1.5rem;
            text-align: center;
        }

        .project-header h2 {
            font-size: 1.5rem;
        }

        .mahasiswa-avatar {
            width: 48px;
            height: 48px;
            border-width: 2px;
        }

        .mahasiswa-name {
            font-size: 1rem;
        }

        .mahasiswa-nim {
            font-size: 0.8rem;
        }

        .card-body {
            padding: 1.5rem !important;
        }

        .form-control, .form-select {
            font-size: 16px; /* Prevents zoom on iOS */
        }

        .btn-primary {
            padding: 0.6rem 1.5rem;
            font-size: 0.9rem;
        }

        .grade-badge {
            font-size: 1.2rem;
            padding: 0.4rem 0.8rem;
        }

        .score-display {
            font-size: 1.5rem;
        }

        .mahasiswa-card {
            margin-bottom: 1rem;
        }

            }

    @media (max-width: 480px) {
        .project-header {
            padding: 1rem;
        }

        .project-header h2 {
            font-size: 1.3rem;
        }

        .mahasiswa-avatar {
            width: 40px;
            height: 40px;
            border-width: 1px;
        }

        .mahasiswa-name {
            font-size: 0.95rem;
        }

        .card-body {
            padding: 1rem !important;
        }

        .student-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .row {
            margin: 0;
        }

        .col-md-6, .col-md-4, .col-md-8, .col-md-12 {
            padding: 0.25rem;
        }

        .d-flex.flex-row {
            flex-direction: column;
            text-align: center;
        }

        .text-right.ml-auto {
            text-align: center;
            margin-left: 0;
            margin-top: 1rem;
        }

        .evaluation-form .row {
            gap: 0.5rem;
        }
    }
</style>

<div class="container py-4">
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center text-white">
            <div class="spinner-border text-light mb-3" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <h5>Menyimpan penilaian...</h5>
        </div>
    </div>

    <!-- Main Container -->
    <div class="evaluation-container">
        <!-- Project Header -->
        <div class="project-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-building fa-2x mr-3"></i>
                        <div>
                            <h2 class="mb-1 font-weight-bold">{{ $card->title ?? 'Proyek' }}</h2>
                            <p class="mb-0 opacity-75">
                                <i class="fas fa-users mr-2"></i>{{ $card->list->kelompok->nama_kelompok ?? 'Kelompok' }}
                                @if($card->nama_mitra)
                                    <span class="ml-3"><i class="fas fa-handshake mr-2"></i>{{ $card->nama_mitra }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-right">
                    <div class="mb-2">
                        <span class="badge badge-status badge-light">
                            <i class="fas fa-chart-line mr-2"></i>{{ $card->status_proyek ?? 'Proses' }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <small class="opacity-75">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            @if($card->tanggal_mulai && $card->tanggal_selesai)
                                {{ $card->tanggal_mulai->format('d M Y') }} - {{ $card->tanggal_selesai->format('d M Y') }}
                            @else
                                Belum ada tanggal
                            @endif
                        </small>
                    </div>
                    @if(isset($existingEvaluations) && $existingEvaluations->count() > 0)
                        <div>
                            <span class="badge badge-status badge-success">
                                <i class="fas fa-check-circle mr-2"></i>{{ $existingEvaluations->count() }} Penilaian
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Project Details -->
        <div class="p-4">
            <!-- Company Information -->
            <div class="info-card mb-4">
                <h6 class="font-weight-bold text-primary mb-3">
                    <i class="fas fa-building mr-2"></i>Informasi Perusahaan & Kontak Mitra
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Nama Perusahaan:</strong><br>
                            {{ $card->nama_mitra ?? 'Tidak tersedia' }}
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2">
                            <strong>Kontak Mitra:</strong><br>
                            {{ $card->kontak_mitra ?? 'Tidak tersedia' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Project Description -->
            @if($card->description)
                <div class="info-card mb-4">
                    <h6 class="font-weight-bold text-primary mb-3">
                        <i class="fas fa-info-circle mr-2"></i>Deskripsi Proyek
                    </h6>
                    <p class="mb-0">{{ $card->description }}</p>
                </div>
            @endif

            <!-- Form untuk penilaian -->
            <form id="evaluationForm">
                @csrf

            <!-- Students Evaluation -->
            <div class="mb-4">
                <h5 class="font-weight-bold mb-3">
                    <i class="fas fa-users mr-2"></i>Penilaian Mahasiswa
                </h5>
                <div id="studentsContainer" class="student-grid">
                    <!-- Students will be loaded here via AJAX -->
                    <div class="col-12 text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat data mahasiswa...</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="button" class="btn btn-primary btn-lg px-5" onclick="submitEvaluation()">
                    <i class="fas fa-save mr-2"></i>Simpan Penilaian
                </button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle mr-2"></i>Penilaian Berhasil Disimpan!
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                <h4 class="mt-3 mb-2">Terima Kasih!</h4>
                <p class="text-muted">Penilaian Anda telah berhasil disimpan dan akan digunakan dalam perhitungan nilai akhir mahasiswa.</p>
                <div class="mt-3">
                    <button type="button" class="btn btn-success" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let studentsData = [];
let cardData = {};

// Load data when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadStudentsData();
});

// Load students data via AJAX
function loadStudentsData() {
    const url = '{{ route("public.penilaian-mitra.data", $card->uuid) }}';

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cardData = data.project;
                studentsData = data.mahasiswas;
                renderStudents();
            } else {
                showError('Gagal memuat data mahasiswa');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Terjadi kesalahan saat memuat data');
        });
}


// Render students cards
function renderStudents() {
    const container = document.getElementById('studentsContainer');

    if (studentsData.length === 0) {
        container.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Tidak ada mahasiswa dalam kelompok ini
            </div>
        `;
        return;
    }

    let html = '';
    studentsData.forEach((student, index) => {
        html += createStudentCard(student, index);
    });

    container.innerHTML = html;

    // Add event listeners for score inputs
    document.querySelectorAll('.score-input').forEach(input => {
        input.addEventListener('input', function() {
            calculateStudentScore(this.dataset.studentId);
        });
    });
}

// Create student card HTML
function createStudentCard(student, index) {
    const avatar = `https://ui-avatars.com/api/?name=${encodeURIComponent(student.nama)}&background=4e73df&color=ffffff&size=64&rounded=true&bold=true`;
    const existingEval = student.existing_evaluation;

    return `
        <div class="mahasiswa-card h-100">
            <div class="card-body p-4">
                    <!-- Student Header -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-center mr-3">
                            <img src="${avatar}" alt="${student.nama}" class="mahasiswa-avatar">
                        </div>
                        <div class="mahasiswa-info">
                            <h6 class="mahasiswa-name">${student.nama}</h6>
                            <p class="mahasiswa-nim">${student.nim}</p>
                        </div>
                        ${existingEval ? `
                            <div class="text-right ml-auto">
                                <div class="grade-badge grade-${existingEval.grade.toLowerCase()}">
                                    ${existingEval.grade}
                                </div>
                                <div class="score-display small">${existingEval.nilai_akhir}</div>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Evaluation Form -->
                    <div class="evaluation-form">
                        <input type="hidden" name="mahasiswa_id" value="${student.id}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small">
                                        <i class="fas fa-comments mr-1"></i>Komunikasi & Sikap (50%)
                                    </label>
                                    <input type="number"
                                           class="form-control score-input"
                                           data-student-id="${student.id}"
                                           data-type="komunikasi_sikap"
                                           min="0" max="100"
                                           value="${existingEval ? existingEval.komunikasi_sikap : ''}"
                                           placeholder="0-100"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold small">
                                        <i class="fas fa-briefcase mr-1"></i>Hasil Pekerjaan (50%)
                                    </label>
                                    <input type="number"
                                           class="form-control score-input"
                                           data-student-id="${student.id}"
                                           data-type="hasil_pekerjaan"
                                           min="0" max="100"
                                           value="${existingEval ? existingEval.hasil_pekerjaan : ''}"
                                           placeholder="0-100"
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold small">
                                <i class="fas fa-comment-alt mr-1"></i>Catatan (Opsional)
                            </label>
                            <textarea class="form-control"
                                      name="catatan"
                                      rows="2"
                                      placeholder="Masukkan catatan atau feedback...">${existingEval ? existingEval.catatan : ''}</textarea>
                        </div>

                        <!-- Score Display -->
                        <div class="score-display-container mt-3 p-3 bg-light rounded text-center" id="score-display-${student.id}" style="display: none;">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <small class="text-muted">Nilai Akhir</small>
                                    <div class="h4 font-weight-bold text-primary" id="final-score-${student.id}">-</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">Grade</small>
                                    <div class="h3 font-weight-bold" id="grade-${student.id}">-</div>
                                </div>
                            </div>
                        </div>

                        ${existingEval ? `
                            <div class="mt-2 text-center">
                                <small class="text-muted">
                                    <i class="fas fa-clock mr-1"></i>
                                    Dinilai oleh: ${existingEval.evaluator_name}
                                    pada ${existingEval.created_at}
                                </small>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Calculate student score
function calculateStudentScore(studentId) {
    const komunikasiInput = document.querySelector(`input[data-student-id="${studentId}"][data-type="komunikasi_sikap"]`);
    const hasilInput = document.querySelector(`input[data-student-id="${studentId}"][data-type="hasil_pekerjaan"]`);

    const komunikasi = parseFloat(komunikasiInput.value) || 0;
    const hasil = parseFloat(hasilInput.value) || 0;

    if (komunikasi > 0 || hasil > 0) {
        const finalScore = (komunikasi * 0.5) + (hasil * 0.5);
        const grade = calculateGrade(finalScore);

        // Update display
        const scoreDisplay = document.getElementById(`score-display-${studentId}`);
        const finalScoreElement = document.getElementById(`final-score-${studentId}`);
        const gradeElement = document.getElementById(`grade-${studentId}`);

        scoreDisplay.style.display = 'block';
        finalScoreElement.textContent = finalScore.toFixed(1);
        gradeElement.textContent = grade;
        gradeElement.className = `h3 font-weight-bold grade-${grade.toLowerCase()}`;
    } else {
        document.getElementById(`score-display-${studentId}`).style.display = 'none';
    }
}

// Calculate grade
function calculateGrade(score) {
    if (score >= 85) return 'A';
    if (score >= 75) return 'B';
    if (score >= 65) return 'C';
    if (score >= 55) return 'D';
    return 'E';
}


// Submit evaluation
function submitEvaluation() {
    // Collect evaluations
    const evaluations = [];
    let hasErrors = false;

    studentsData.forEach(student => {
        const komunikasiInput = document.querySelector(`input[data-student-id="${student.id}"][data-type="komunikasi_sikap"]`);
        const hasilInput = document.querySelector(`input[data-student-id="${student.id}"][data-type="hasil_pekerjaan"]`);
        const catatanInput = document.querySelector(`textarea[name="catatan"]`);

        const komunikasi = parseFloat(komunikasiInput.value) || 0;
        const hasil = parseFloat(hasilInput.value) || 0;

        if (komunikasi === 0 && hasil === 0) {
            // Skip if no evaluation provided
            return;
        }

        if (komunikasi < 0 || komunikasi > 100 || hasil < 0 || hasil > 100) {
            showError('Nilai harus antara 0-100 untuk ' + student.nama);
            hasErrors = true;
            return;
        }

        evaluations.push({
            mahasiswa_id: student.id,
            komunikasi_sikap: komunikasi,
            hasil_pekerjaan: hasil,
            catatan: catatanInput.value.trim()
        });
    });

    if (hasErrors) {
        return;
    }

    if (evaluations.length === 0) {
        showError('Setidaknya satu mahasiswa harus dinilai');
        return;
    }

    // Show loading
    document.getElementById('loadingOverlay').style.display = 'flex';

    // Submit data
    const formData = {
        evaluations: evaluations,
        _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    };

    const url = '{{ route("public.penilaian-mitra.submit", $card->uuid) }}';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': formData._token
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loadingOverlay').style.display = 'none';

        if (data.success) {
            $('#successModal').modal('show');

            // Reload data after 2 seconds
            setTimeout(() => {
                loadStudentsData();
            }, 2000);
        } else {
            showError(data.message || 'Gagal menyimpan penilaian');
        }
    })
    .catch(error => {
        document.getElementById('loadingOverlay').style.display = 'none';
        console.error('Error:', error);
        showError('Terjadi kesalahan saat menyimpan penilaian');
    });
}

// Show error message
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#4e73df'
    });
}
</script>
@endpush