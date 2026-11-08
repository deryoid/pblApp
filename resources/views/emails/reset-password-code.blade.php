<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Reset Kata Sandi</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #334155;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" max-width="540" style="max-width: 540px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.08); border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 30px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -0.02em;">PBL TRKJ POLITALA</h1>
                            <p style="color: #94a3b8; margin: 6px 0 0; font-size: 13px;">Sistem Informasi Manajemen Evaluasi & Proyek</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 35px 30px;">
                            <h2 style="color: #0f172a; font-size: 18px; font-weight: 700; margin: 0 0 14px;">Halo, {{ $user->nama_user }}</h2>
                            <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 20px;">
                                Kami menerima permintaan untuk mengatur ulang kata sandi akun PBL TRKJ Anda. Gunakan kode verifikasi 6 digit di bawah ini untuk melanjutkan:
                            </p>

                            <!-- Kode Box -->
                            <div style="background-color: #f8fafc; border: 2px dashed #93c5fd; border-radius: 12px; padding: 20px; text-align: center; margin: 25px 0;">
                                <span style="font-size: 12px; font-weight: 700; color: #2563eb; text-transform: uppercase; letter-spacing: 0.08em; display: block; margin-bottom: 8px;">KODE VERIFIKASI ANDA</span>
                                <span style="font-family: 'SF Mono', Consolas, Monaco, monospace; font-size: 36px; font-weight: 800; color: #1e3a8a; letter-spacing: 0.25em; display: inline-block;">{{ $code }}</span>
                            </div>

                            <p style="color: #64748b; font-size: 13px; line-height: 1.5; margin: 0 0 16px;">
                                <strong style="color: #0f172a;">Perhatian:</strong> Kode ini hanya berlaku selama <strong>15 menit</strong>. Jangan bagikan kode ini kepada siapa pun demi keamanan akun Anda.
                            </p>

                            <p style="color: #94a3b8; font-size: 12px; line-height: 1.5; margin: 20px 0 0; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                                Jika Anda tidak meminta pengaturan ulang kata sandi, abaikan email ini. Kata sandi akun Anda akan tetap aman.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="color: #94a3b8; font-size: 11px; margin: 0;">
                                &copy; {{ date('Y') }} Program Studi Teknologi Rekayasa Komputer Jaringan (TRKJ)<br>
                                Politeknik Negeri Tanah Laut
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
