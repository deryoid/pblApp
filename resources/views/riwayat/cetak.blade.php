<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Portofolio Riwayat PBL - {{ $mahasiswa->nama_mahasiswa }} ({{ $mahasiswa->nim }})</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="icon" href="{{ asset('sbadmin2/img/logo-campus.png') }}" type="image/png">
    <link href="{{ asset('sbadmin2/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('sbadmin2/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <style>
        body {
            background-color: #f1f5f9;
            color: #0f172a;
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
        }

        .paper-sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm 20mm;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }

        .header-kop {
            display: flex;
            align-items: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header-kop img {
            width: 80px;
            height: auto;
            margin-right: 20px;
        }

        .header-kop .kop-text {
            text-align: center;
            flex-grow: 1;
        }

        .header-kop h3 {
            font-size: 13pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }

        .header-kop h2 {
            font-size: 15pt;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .header-kop p {
            margin: 0;
            font-size: 9.5pt;
            font-style: italic;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .doc-title h4 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 4px;
        }

        .doc-title p {
            font-size: 10pt;
            margin: 0;
        }

        .table-data th, .table-data td {
            border: 1px solid #333 !important;
            padding: 6px 8px !important;
            font-size: 10pt;
        }

        .table-identity td {
            padding: 3px 6px;
            font-size: 10.5pt;
        }

        .signature-block {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        @media print {
            body {
                background: #ffffff !important;
                margin: 0;
                padding: 0;
            }
            .paper-sheet {
                width: 100% !important;
                margin: 0 !important;
                box-shadow: none !important;
                padding: 10mm 15mm !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    {{-- Action Bar --}}
    <div class="no-print py-3 bg-dark text-white text-center shadow-sm sticky-top">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ $backRoute ?? url()->previous() }}" class="btn btn-outline-light btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            <span class="font-weight-bold">Lembar Resmi Portofolio Riwayat PBL TRKJ Politala</span>
            <button onclick="window.print()" class="btn btn-primary btn-sm font-weight-bold px-3">
                <i class="fas fa-print mr-1"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    {{-- Kertas Dokumen Resmi --}}
    <div class="paper-sheet">
        {{-- KOP SURAT POLITALA --}}
        <div class="header-kop">
            <img src="{{ asset('sbadmin2/img/logo-campus.png') }}" alt="Logo Politala">
            <div class="kop-text">
                <h3>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
                <h2>POLITEKNIK NEGERI TANAH LAUT</h2>
                <h3 style="font-size: 11.5pt;">JURUSAN TEKNIK INFORMATIKA</h3>
                <h3 style="font-size: 11pt;">PROGRAM STUDI TEKNOLOGI REKAYASA KOMPUTER JARINGAN</h3>
                <p>Jl. A. Yani Km. 06 Desa Panggung, Kec. Pelaihari, Kab. Tanah Laut, Kalimantan Selatan 70815</p>
                <p>Website: www.politala.ac.id | Email: pbl.trkj@politala.ac.id</p>
            </div>
        </div>

        {{-- JUDUL DOKUMEN --}}
        <div class="doc-title">
            <h4>LEMBAR PORTOFOLIO & RIWAYAT PROJECT BASED LEARNING (PBL)</h4>
            <p>Nomor Registrasi: PBL-TRKJ/{{ date('Y') }}/{{ str_pad($mahasiswa->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>

        {{-- IDENTITAS MAHASISWA --}}
        <table class="table-identity mb-3" style="width: 100%;">
            <tr>
                <td style="width: 160px; font-weight: bold;">Nama Mahasiswa</td>
                <td style="width: 10px;">:</td>
                <td style="font-weight: bold; text-transform: uppercase;">{{ $mahasiswa->nama_mahasiswa }}</td>
                <td style="width: 130px; font-weight: bold;">Program Studi</td>
                <td style="width: 10px;">:</td>
                <td>TRKJ Politala</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">NIM</td>
                <td>:</td>
                <td><strong>{{ $mahasiswa->nim }}</strong></td>
                <td style="font-weight: bold;">Kelas / Semester</td>
                <td>:</td>
                <td>{{ $mahasiswa->kelas->kelas ?? '-' }} / Semester {{ $mahasiswa->semester ?? ($mahasiswa->kelas->semester ?? '-') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Total Proyek PBL</td>
                <td>:</td>
                <td>{{ $summary['total_proyek'] }} Proyek</td>
                <td style="font-weight: bold;">Predikat Mutu Akhir</td>
                <td>:</td>
                <td>
                    <strong>Grade {{ $summary['overall_grade'] ?? '-' }}</strong>
                    @if($summary['avg_nilai_akhir'])
                        ({{ number_format($summary['avg_nilai_akhir'], 2) }})
                    @endif
                </td>
            </tr>
        </table>

        {{-- TABEL RIWAYAT PBL --}}
        <h5 style="font-size: 11pt; font-weight: bold; margin-bottom: 6px; text-transform: uppercase;">
            I. Rincian Pelaksanaan Proyek Based Learning (PBL)
        </h5>

        @foreach($riwayat as $idx => $r)
            @php
                $card = $r['card'];
                $kelompok = $r['kelompok'];
                $tempat = $r['tempat'];
                $pekerjaan = $r['pekerjaan'];
                $penilaian = $r['penilaian'];
                $kesimpulan = $r['kesimpulan'];
            @endphp

            <div style="margin-bottom: 20px; page-break-inside: avoid;">
                <table class="table table-bordered table-data" style="width: 100%;">
                    <tbody>
                        <tr style="background-color: #f1f5f9;">
                            <th colspan="4" style="font-size: 10.5pt; font-weight: bold;">
                                PROYEK #{{ $idx + 1 }}: {{ strtoupper($pekerjaan['judul']) }}
                            </th>
                        </tr>
                        <tr>
                            <th style="width: 25%; background-color: #f8fafc;">Tempat PBL (Dimana)</th>
                            <td colspan="3">
                                <strong>{{ $tempat['nama'] }}</strong> (Skema: {{ $tempat['skema'] }})<br>
                                <span style="font-size: 9pt; color: #475569;">Alamat: {{ $tempat['alamat'] }} | Kontak: {{ $tempat['kontak'] }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8fafc;">Peran & Kelompok</th>
                            <td style="width: 35%;">
                                <strong>{{ $r['peran'] }}</strong> (Kelompok: {{ $kelompok->nama_kelompok }})
                            </td>
                            <th style="width: 20%; background-color: #f8fafc;">Periode & Progres</th>
                            <td style="width: 20%;">
                                {{ $r['periode']->periode ?? '-' }} (Progres: {{ $pekerjaan['progress'] }}%)
                            </td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8fafc;">Uraian Pekerjaan Teknis</th>
                            <td colspan="3">
                                {{ $pekerjaan['deskripsi'] ?: 'Pengerjaan modul sistem terintegrasi sesuai standar kurikulum PBL TRKJ.' }}
                                <br>
                                <span style="font-size: 9pt; font-style: italic;">
                                    Total Logbook Kegiatan: {{ $pekerjaan['total_aktivitas'] }} aktivitas tervalidasi.
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8fafc;">Penilaian 4 Pilar (25% per Pilar)</th>
                            <td colspan="3">
                                <div style="display: flex; justify-content: space-around; text-align: center;">
                                    <div><strong>Relevansi (25%):</strong> {{ $penilaian['relevansi'] ?? '-' }}</div>
                                    <div><strong>Kualitas (25%):</strong> {{ $penilaian['kualitas'] ?? '-' }}</div>
                                    <div><strong>Kreativitas (25%):</strong> {{ $penilaian['kreativitas'] ?? '-' }}</div>
                                    <div><strong>Estetika (25%):</strong> {{ $penilaian['estetika'] ?? '-' }}</div>
                                    <div><strong>Nilai Akhir:</strong> {{ $penilaian['nilai_akhir'] ? number_format($penilaian['nilai_akhir'], 2) : '-' }} ({{ $penilaian['grade'] ?? '-' }})</div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th style="background-color: #f8fafc;">Kesimpulan Proyek</th>
                            <td colspan="3" style="font-size: 9.5pt;">
                                <strong>Status:</strong> {{ $kesimpulan['status'] }}<br>
                                <strong>Analisis:</strong> {{ $kesimpulan['analisis_pilar'] }}<br>
                                <strong>Pernyataan SKPI:</strong> "{{ $kesimpulan['narasi_portofolio'] }}"
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach

        {{-- KESIMPULAN PORTOFOLIO KESELURUHAN --}}
        <div style="margin-top: 15px; page-break-inside: avoid;">
            <h5 style="font-size: 11pt; font-weight: bold; margin-bottom: 6px; text-transform: uppercase;">
                II. Kesimpulan Portofolio & Kelayakan
            </h5>
            <div style="border: 1px solid #333; padding: 10px 14px; font-size: 10pt; line-height: 1.5; background: #fafafa;">
                {{ $summary['overall_kesimpulan'] }}
            </div>
        </div>

        {{-- TANDA TANGAN PENGESAHAN --}}
        <div class="signature-block">
            <div class="signature-box">
                <p style="margin-bottom: 5px;">Mengetahui,<br>Koordinator PBL Program Studi TRKJ</p>
                <div style="height: 60px;"></div>
                <p style="font-weight: bold; text-decoration: underline; margin-bottom: 0;">
                    Dery Yuswanto Jaya, M.Kom
                </p>
                <p style="font-size: 9pt; margin: 0;">NIP. 19890101 202203 1 001</p>
            </div>

            <div class="signature-box">
                <p style="margin-bottom: 5px;">Pelaihari, {{ date('d F Y') }}<br>Mahasiswa Yang Bersangkutan</p>
                <div style="height: 60px;"></div>
                <p style="font-weight: bold; text-decoration: underline; margin-bottom: 0;">
                    {{ $mahasiswa->nama_mahasiswa }}
                </p>
                <p style="font-size: 9pt; margin: 0;">NIM. {{ $mahasiswa->nim }}</p>
            </div>
        </div>

    </div>

</body>
</html>
