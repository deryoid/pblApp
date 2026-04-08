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
     * Convert attendance status to numeric value.
     */
    public function convertAttendanceToValue(string $status): int
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
