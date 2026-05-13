<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SystemSetting;
use Carbon\Carbon;

class TranscriptController extends Controller
{
    public function download()
    {
        $user = auth()->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->whereIn('enrollment_status', ['Completed', 'In Progress'])
            ->with(['course', 'certificate'])
            ->orderBy('enrolled_at', 'desc')
            ->get();

        $totalCredits = $enrollments->sum(function ($e) {
            return $e->course->duration_weeks ?? 0;
        });

        $completedCourses = $enrollments->where('enrollment_status', 'Completed')->count();
        $gpa = $this->calculateGPA($enrollments);

        $institutionName = SystemSetting::get('institution_name', 'Edutrack Computer Training College');
        $institutionAddress = SystemSetting::get('site_address', 'Kalomo, Zambia');
        $tevetaReg = SystemSetting::get('teveta_registration_number', '');

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor($institutionName);
        $pdf->SetTitle('Academic Transcript - ' . $user->full_name);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $institutionName, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $institutionAddress, 0, 1, 'C');
        if ($tevetaReg) {
            $pdf->Cell(0, 6, 'TEVETA Reg: ' . $tevetaReg, 0, 1, 'C');
        }
        $pdf->Ln(5);

        $pdf->SetDrawColor(46, 112, 218);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(5);

        // Title
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'ACADEMIC TRANSCRIPT', 0, 1, 'C');
        $pdf->Ln(3);

        // Student Info
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(30, 7, 'Student:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(80, 7, $user->full_name, 0, 0);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(30, 7, 'Date:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, Carbon::now()->format('F d, Y'), 0, 1);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(30, 7, 'Email:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(80, 7, $user->email, 0, 0);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(30, 7, 'Phone:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 7, $user->phone ?? 'N/A', 0, 1);
        $pdf->Ln(5);

        // Summary Box
        $pdf->SetFillColor(240, 248, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(55, 8, 'Completed Courses: ' . $completedCourses, 1, 0, 'C', true);
        $pdf->Cell(55, 8, 'Total Credits: ' . $totalCredits, 1, 0, 'C', true);
        $pdf->Cell(55, 8, 'GPA: ' . number_format($gpa, 2) . '/5.0', 1, 1, 'C', true);
        $pdf->Ln(5);

        // Course Table
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(46, 112, 218);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(70, 8, 'Course', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Enrolled', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Status', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Grade', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Credits', 1, 0, 'C', true);
        $pdf->Cell(20, 8, 'Cert', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($enrollments as $enrollment) {
            $grade = $this->calculateGrade($enrollment);
            $hasCertificate = $enrollment->certificate ? 'Yes' : 'No';
            $credits = $enrollment->course->duration_weeks ?? 0;

            $pdf->Cell(70, 7, $enrollment->course->title, 1, 0, 'L');
            $pdf->Cell(30, 7, $enrollment->enrolled_at->format('M Y'), 1, 0, 'C');
            $pdf->Cell(25, 7, $enrollment->enrollment_status, 1, 0, 'C');
            $pdf->Cell(20, 7, $grade, 1, 0, 'C');
            $pdf->Cell(20, 7, $credits, 1, 0, 'C');
            $pdf->Cell(20, 7, $hasCertificate, 1, 1, 'C');
        }

        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 5, 'This transcript is generated electronically and is valid without signature.', 0, 1, 'C');
        $pdf->Cell(0, 5, 'Verify at: ' . url('/certificates/verify'), 0, 1, 'C');

        $filename = 'Transcript_' . str_replace(' ', '_', $user->full_name) . '_' . Carbon::now()->format('Ymd') . '.pdf';
        $pdf->Output($filename, 'D');
    }

    private function calculateGrade(Enrollment $enrollment): string
    {
        if ($enrollment->enrollment_status !== 'Completed') {
            return 'In Progress';
        }

        $progress = $enrollment->progress ?? 0;
        return match (true) {
            $progress >= 90 => 'A',
            $progress >= 80 => 'B+',
            $progress >= 70 => 'B',
            $progress >= 60 => 'C+',
            $progress >= 50 => 'C',
            default => 'D',
        };
    }

    private function calculateGPA($enrollments): float
    {
        $completed = $enrollments->where('enrollment_status', 'Completed');
        if ($completed->isEmpty()) {
            return 0;
        }

        $totalPoints = 0;
        $count = 0;

        foreach ($completed as $enrollment) {
            $progress = $enrollment->progress ?? 0;
            $points = match (true) {
                $progress >= 90 => 5.0,
                $progress >= 80 => 4.5,
                $progress >= 70 => 4.0,
                $progress >= 60 => 3.5,
                $progress >= 50 => 3.0,
                default => 2.0,
            };
            $totalPoints += $points;
            $count++;
        }

        return $count > 0 ? round($totalPoints / $count, 2) : 0;
    }
}
