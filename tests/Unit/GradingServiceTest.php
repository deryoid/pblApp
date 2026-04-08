<?php

namespace Tests\Unit;

use App\Services\GradingService;
use PHPUnit\Framework\TestCase;

class GradingServiceTest extends TestCase
{
    private GradingService $gradingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gradingService = new GradingService;
    }

    public function test_calculate_grade_returns_a_for_score_above_85(): void
    {
        $this->assertSame('A', $this->gradingService->calculateGrade(85));
        $this->assertSame('A', $this->gradingService->calculateGrade(90));
        $this->assertSame('A', $this->gradingService->calculateGrade(100));
    }

    public function test_calculate_grade_returns_b_for_score_between_75_and_84(): void
    {
        $this->assertSame('B', $this->gradingService->calculateGrade(75));
        $this->assertSame('B', $this->gradingService->calculateGrade(80));
        $this->assertSame('B', $this->gradingService->calculateGrade(84.9));
    }

    public function test_calculate_grade_returns_c_for_score_between_65_and_74(): void
    {
        $this->assertSame('C', $this->gradingService->calculateGrade(65));
        $this->assertSame('C', $this->gradingService->calculateGrade(70));
        $this->assertSame('C', $this->gradingService->calculateGrade(74.9));
    }

    public function test_calculate_grade_returns_d_for_score_between_55_and_64(): void
    {
        $this->assertSame('D', $this->gradingService->calculateGrade(55));
        $this->assertSame('D', $this->gradingService->calculateGrade(60));
        $this->assertSame('D', $this->gradingService->calculateGrade(64.9));
    }

    public function test_calculate_grade_returns_e_for_score_below_55(): void
    {
        $this->assertSame('E', $this->gradingService->calculateGrade(0));
        $this->assertSame('E', $this->gradingService->calculateGrade(54.9));
    }

    public function test_calculate_grade_returns_null_for_null_score(): void
    {
        $this->assertNull($this->gradingService->calculateGrade(null));
    }

    public function test_convert_attendance_to_value(): void
    {
        $this->assertSame(100, $this->gradingService->convertAttendanceToValue('Hadir'));
        $this->assertSame(70, $this->gradingService->convertAttendanceToValue('Izin'));
        $this->assertSame(60, $this->gradingService->convertAttendanceToValue('Sakit'));
        $this->assertSame(50, $this->gradingService->convertAttendanceToValue('Terlambat'));
        $this->assertSame(0, $this->gradingService->convertAttendanceToValue('Tanpa Keterangan'));
        $this->assertSame(0, $this->gradingService->convertAttendanceToValue('Invalid Status'));
    }

    public function test_calculate_final_score_with_default_weights(): void
    {
        // 70% project + 30% activity
        $result = $this->gradingService->calculateFinalScore(80, 60);
        $this->assertSame(74.0, $result); // (80 * 0.7) + (60 * 0.3) = 56 + 18 = 74
    }

    public function test_calculate_final_score_with_custom_weights(): void
    {
        // 50% project + 50% activity
        $result = $this->gradingService->calculateFinalScore(80, 60, 0.5, 0.5);
        $this->assertSame(70.0, $result); // (80 * 0.5) + (60 * 0.5) = 40 + 30 = 70
    }

    public function test_calculate_project_score_with_default_weights(): void
    {
        // 80% dosen + 20% mitra
        $result = $this->gradingService->calculateProjectScore(85, 75);
        $this->assertSame(83.0, $result); // (85 * 0.8) + (75 * 0.2) = 68 + 15 = 83
    }

    public function test_calculate_project_score_with_custom_weights(): void
    {
        // 50% dosen + 50% mitra
        $result = $this->gradingService->calculateProjectScore(85, 75, 0.5, 0.5);
        $this->assertSame(80.0, $result); // (85 * 0.5) + (75 * 0.5) = 42.5 + 37.5 = 80
    }

    public function test_calculate_activity_score_with_default_weights(): void
    {
        // 50% attendance + 50% presentation
        $result = $this->gradingService->calculateActivityScore(100, 80);
        $this->assertSame(90.0, $result); // (100 * 0.5) + (80 * 0.5) = 50 + 40 = 90
    }

    public function test_calculate_activity_score_with_custom_weights(): void
    {
        // 70% attendance + 30% presentation
        $result = $this->gradingService->calculateActivityScore(100, 80, 0.7, 0.3);
        $this->assertSame(94.0, $result); // (100 * 0.7) + (80 * 0.3) = 70 + 24 = 94
    }

    public function test_format_score_with_default_decimals(): void
    {
        $this->assertSame(85.67, $this->gradingService->formatScore(85.666666));
        $this->assertSame(85.57, $this->gradingService->formatScore(85.567));
    }

    public function test_format_score_with_custom_decimals(): void
    {
        $this->assertSame(85.7, $this->gradingService->formatScore(85.666666, 1));
        $this->assertSame(86.0, $this->gradingService->formatScore(85.666666, 0));
    }

    public function test_complete_grading_calculation_flow(): void
    {
        // Scenario: Student with various scores
        $avgDosen = 85.0;
        $avgMitra = 75.0;
        $avgAP = 80.0;

        // Calculate project score: 80% dosen + 20% mitra
        $projectScore = $this->gradingService->calculateProjectScore($avgDosen, $avgMitra);

        // Calculate final score: 70% project + 30% AP
        $finalScore = $this->gradingService->calculateFinalScore($projectScore, $avgAP);

        // Get grade
        $grade = $this->gradingService->calculateGrade($finalScore);

        $this->assertSame(83.0, $projectScore); // (85 * 0.8) + (75 * 0.2) = 68 + 15 = 83
        $this->assertSame(82.1, $finalScore);   // (83 * 0.7) + (80 * 0.3) = 58.1 + 24 = 82.1
        $this->assertSame('B', $grade);        // 82.1 is between 75-84.99, so grade is B
    }

    public function test_calculate_grade_boundary_values(): void
    {
        // Test exact boundary values
        $this->assertSame('A', $this->gradingService->calculateGrade(85.0));
        $this->assertSame('B', $this->gradingService->calculateGrade(84.99));
        $this->assertSame('B', $this->gradingService->calculateGrade(75.0));
        $this->assertSame('C', $this->gradingService->calculateGrade(74.99));
        $this->assertSame('C', $this->gradingService->calculateGrade(65.0));
        $this->assertSame('D', $this->gradingService->calculateGrade(64.99));
        $this->assertSame('D', $this->gradingService->calculateGrade(55.0));
        $this->assertSame('E', $this->gradingService->calculateGrade(54.99));
    }
}
