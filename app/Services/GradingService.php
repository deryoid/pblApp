<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

class GradingService
{
    /**
     * Calculate grade based on score.
     */
    public function calculateGrade(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

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

    /**
     * Hitung nilai huruf dinamis berdasarkan Rubrik Excel:
     * A  : 80 – 100
     * B+ : 75 – 79.99
     * B  : 70 – 74.99
     * C+ : 65 – 69.99
     * C  : 60 – 64.99
     * D+ : 50 – 59.99
     * D  : 40 – 49.99
     * E  : < 40
     */
    public function calculateRubricGrade(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        if ($score >= 80) {
            return 'A';
        }

        if ($score >= 75) {
            return 'B+';
        }

        if ($score >= 70) {
            return 'B';
        }

        if ($score >= 65) {
            return 'C+';
        }

        if ($score >= 60) {
            return 'C';
        }

        if ($score >= 50) {
            return 'D+';
        }

        if ($score >= 40) {
            return 'D';
        }

        return 'E';
    }

    /**
     * Dapatkan daftar skala rubrik lengkap beserta rentang dan warna badge.
     */
    public function getRubricGradeScale(): array
    {
        return [
            ['grade' => 'A',  'min' => 80.0, 'max' => 100.0, 'range' => '80 – 100', 'badge' => 'badge-success', 'color' => '#28a745'],
            ['grade' => 'B+', 'min' => 75.0, 'max' => 79.99, 'range' => '75 – 79',  'badge' => 'badge-primary', 'color' => '#007bff'],
            ['grade' => 'B',  'min' => 70.0, 'max' => 74.99, 'range' => '70 – 74',  'badge' => 'badge-info',    'color' => '#17a2b8'],
            ['grade' => 'C+', 'min' => 65.0, 'max' => 69.99, 'range' => '65 – 69',  'badge' => 'badge-warning', 'color' => '#ffc107'],
            ['grade' => 'C',  'min' => 60.0, 'max' => 64.99, 'range' => '60 – 64',  'badge' => 'badge-warning', 'color' => '#ffc107'],
            ['grade' => 'D+', 'min' => 50.0, 'max' => 59.99, 'range' => '50 – 59',  'badge' => 'badge-danger',  'color' => '#e65100'],
            ['grade' => 'D',  'min' => 40.0, 'max' => 49.99, 'range' => '40 – 49',  'badge' => 'badge-danger',  'color' => '#dc3545'],
            ['grade' => 'E',  'min' => 0.0,  'max' => 39.99, 'range' => '0 – 39',   'badge' => 'badge-secondary', 'color' => '#6c757d'],
        ];
    }

    /**
     * Dapatkan kelas badge bootstrap untuk nilai huruf rubrik.
     */
    public function getRubricBadgeClass(?string $grade): string
    {
        return match ($grade) {
            'A' => 'badge-success',
            'B+' => 'badge-primary',
            'B' => 'badge-info',
            'C+', 'C' => 'badge-warning',
            'D+', 'D' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    /**
     * Convert attendance status to numeric value.
     */
    public function convertAttendanceToValue(?string $status): int
    {
        return match ($status) {
            'Hadir' => 100,
            'Izin' => 70,
            'Sakit' => 60,
            'Terlambat' => 50,
            'Tanpa Keterangan' => 0,
            default => 0,
        };
    }

    /**
     * Calculate final score from components.
     *
     * @param  float  $projectWeight  Default 0.7 (70%)
     * @param  float  $activityWeight  Default 0.3 (30%)
     */
    public function calculateFinalScore(
        float $projectScore,
        float $activityScore,
        float $projectWeight = 0.7,
        float $activityWeight = 0.3
    ): float {
        return ($projectScore * $projectWeight) + ($activityScore * $activityWeight);
    }

    /**
     * Calculate project score (dosen + mitra).
     *
     * @param  float  $lecturerWeight  Default 0.8 (80%)
     * @param  float  $partnerWeight  Default 0.2 (20%)
     */
    public function calculateProjectScore(
        float $lecturerScore,
        float $partnerScore,
        float $lecturerWeight = 0.8,
        float $partnerWeight = 0.2
    ): float {
        return ($lecturerScore * $lecturerWeight) + ($partnerScore * $partnerWeight);
    }

    /**
     * Calculate activity score (attendance + presentation).
     *
     * @param  float  $attendanceWeight  Default 0.5 (50%)
     * @param  float  $presentationWeight  Default 0.5 (50%)
     */
    public function calculateActivityScore(
        float $attendanceScore,
        float $presentationScore,
        float $attendanceWeight = 0.5,
        float $presentationWeight = 0.5
    ): float {
        return ($attendanceScore * $attendanceWeight) + ($presentationScore * $presentationWeight);
    }

    /**
     * Get evaluation setting with fallback to config.
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function getEvaluationSetting(string $key, $default = null)
    {
        try {
            if (Schema::hasTable((new \App\Models\EvaluationSetting)->getTable())) {
                $val = \App\Models\EvaluationSetting::get($key);
                if ($val !== null) {
                    return $val;
                }
            }
        } catch (\Throwable $e) {
            // Ignore and fallback
        }

        $cfg = config('evaluasi.defaults.'.$key);

        return $cfg !== null ? $cfg : $default;
    }

    /**
     * Format score for display.
     */
    public function formatScore(float $score, int $decimals = 2): float
    {
        return round($score, $decimals);
    }
}
