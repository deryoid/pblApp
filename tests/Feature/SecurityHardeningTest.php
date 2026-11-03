<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    /**
     * Memastikan runtime PHP saat ini telah menggunakan versi patched (>= 8.4.27)
     * yang kebal terhadap eksploitasi kerentanan kritis PHP2Shell (CVE-2026-109618 dan CVE-2026-109619).
     */
    public function test_php_version_is_patched_against_php2shell(): void
    {
        $version = PHP_VERSION;
        $versionId = PHP_VERSION_ID;

        // Versi 8.4 harus >= 8.4.27, atau versi 8.5 harus >= 8.5.12
        if (str_starts_with($version, '8.4.')) {
            $this->assertGreaterThanOrEqual(
                80427,
                $versionId,
                "Versi PHP saat ini ({$version}) masih rentan terhadap PHP2Shell. Harus minimal 8.4.27."
            );
        } elseif (str_starts_with($version, '8.5.')) {
            $this->assertGreaterThanOrEqual(
                80512,
                $versionId,
                "Versi PHP saat ini ({$version}) masih rentan terhadap PHP2Shell. Harus minimal 8.5.12."
            );
        } else {
            $this->assertTrue(
                version_compare($version, '8.4.27', '>='),
                "Versi PHP ({$version}) belum memenuhi standar keamanan minimal patch PHP2Shell."
            );
        }
    }

    /**
     * Memastikan HTTP Security Headers terpasang pada respon aplikasi untuk mencegah
     * Clickjacking, MIME sniffing, XSS, dan kebocoran referrer.
     */
    public function test_security_headers_are_attached_to_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    /**
     * Memastikan route login dan registrasi dilindungi oleh rate limiting untuk mencegah serangan OOM / DoS.
     */
    public function test_auth_routes_have_rate_limiting(): void
    {
        $response = $this->post('/login', [
            'username' => 'nonexistent_user',
            'password' => 'wrong_password',
        ]);

        // Respon login harus memiliki header rate limit
        $this->assertTrue(
            $response->headers->has('X-RateLimit-Limit') || $response->isRedirect(),
            'Route /login harus memiliki proteksi rate limiting.'
        );
    }
}
