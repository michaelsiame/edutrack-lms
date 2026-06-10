<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\SystemSetting;
use App\Services\CertificateService;
use Carbon\Carbon;

class TranscriptController extends Controller
{
    /**
     * Preview the transcript in the browser.
     */
    public function preview()
    {
        $user = auth()->user();
        $data = $this->buildTranscriptData($user);
        return view('transcripts.preview', $data);
    }

    /**
     * Download the transcript as a PDF.
     */
    public function download()
    {
        $user = auth()->user();
        $data = $this->buildTranscriptData($user);

        $pdf = $this->generateTcpdf($data);

        $filename = 'Transcript_' . str_replace(' ', '_', $user->full_name) . '_' . Carbon::now()->format('Ymd') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Build all transcript data from the database.
     */
    protected function buildTranscriptData($user): array
    {
        $student = $user->student;
        $studentId = $student?->id;

        $enrollments = Enrollment::where('user_id', $user->id)
            ->whereIn('enrollment_status', ['Completed', 'In Progress'])
            ->with(['course', 'certificate', 'course.modules', 'course.modules.lessons', 'course.modules.lessons.assignments', 'course.modules.lessons.quizzes'])
            ->orderBy('enrolled_at', 'desc')
            ->get();

        $enrollmentData = [];
        $totalCredits = 0;
        $completedCount = 0;
        $gpaSum = 0;

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (!$course) continue;

            $credits = $course->duration_weeks ?? 0;
            $totalCredits += $credits;

            $isCompleted = $enrollment->enrollment_status === 'Completed';
            if ($isCompleted) {
                $completedCount++;
            }

            $finalScore = $enrollment->final_grade ?? $enrollment->progress ?? 0;
            if ($isCompleted) {
                $grade = $this->scoreToGrade($finalScore);
                $points = $this->scoreToPoints($finalScore);
                $gpaSum += $points;
            } else {
                $grade = 'In Progress';
                $points = 0;
            }

            $modules = [];
            $courseModules = $course->modules->sortBy('display_order');
            $moduleCount = $courseModules->count();
            $moduleCredits = $moduleCount > 0 ? round($credits / $moduleCount, 1) : $credits;
            $modIndex = 1;

            foreach ($courseModules as $module) {
                $modCode = 'M' . str_pad((string) $modIndex, 2, '0', STR_PAD_LEFT);
                $assessments = [];

                foreach ($module->lessons->sortBy('display_order') as $lesson) {
                    // Assignments
                    foreach ($lesson->assignments as $assignment) {
                        $submission = $studentId
                            ? $assignment->submissions()
                                ->where('student_id', $studentId)
                                ->orderBy('graded_at', 'desc')
                                ->first()
                            : null;
                        $score = $submission?->points_earned ?? 0;
                        $max = $assignment->max_points;
                        $pct = $max > 0 ? round(($score / $max) * 100, 1) : 0;
                        $assessments[] = [
                            'code' => $modCode,
                            'title' => $lesson->title . ' — ' . $assignment->title,
                            'type' => 'Assignment',
                            'score' => round($score, 1),
                            'max' => $max,
                            'percentage' => $pct . '%',
                            'grade' => $this->scoreToGrade($pct),
                            'credits' => '-',
                        ];
                    }

                    // Quizzes
                    foreach ($lesson->quizzes as $quiz) {
                        $attempt = $studentId
                            ? $quiz->attempts()
                                ->where('student_id', $studentId)
                                ->whereIn('status', ['Graded', 'Submitted'])
                                ->orderBy('score', 'desc')
                                ->first()
                            : null;
                        $score = $attempt?->score ?? 0;
                        $max = 100; // quizzes store percentage
                        $pct = $score;
                        $assessments[] = [
                            'code' => $modCode,
                            'title' => $lesson->title . ' — ' . $quiz->title,
                            'type' => $quiz->quiz_type,
                            'score' => round($score, 1),
                            'max' => $max,
                            'percentage' => round($pct, 1) . '%',
                            'grade' => $this->scoreToGrade($pct),
                            'credits' => '-',
                        ];
                    }
                }

                // If no assessments, show module as completed/in progress based on lesson progress
                if (empty($assessments)) {
                    $lessonProgress = $studentId
                        ? \App\Models\LessonProgress::where('enrollment_id', $enrollment->id)
                            ->whereIn('lesson_id', $module->lessons->pluck('id'))
                            ->get()
                        : collect();
                    // De-duplicate in case multiple progress records exist for the same lesson
                    $completedLessons = $lessonProgress->where('status', 'Completed')->unique('lesson_id')->count();
                    $totalLessons = $module->lessons->count();
                    $progress = $totalLessons > 0 ? min(100, round(($completedLessons / $totalLessons) * 100, 1)) : 0;
                    $assessments[] = [
                        'code' => $modCode,
                        'title' => $module->title,
                        'type' => 'Module',
                        'score' => $progress,
                        'max' => 100,
                        'percentage' => $progress . '%',
                        'grade' => $this->scoreToGrade($progress),
                        'credits' => $moduleCredits,
                    ];
                }

                $modules = array_merge($modules, $assessments);
                $modIndex++;
            }

            $enrollmentData[] = [
                'course_code' => $this->generateCourseCode($course->id),
                'course_title' => $course->title,
                'level' => $course->level,
                'credits' => $credits,
                'enrolled_at' => $enrollment->enrolled_at?->format('F Y') ?? 'N/A',
                'completion_date' => $enrollment->completion_date?->format('F Y') ?? 'In Progress',
                'status' => $enrollment->enrollment_status,
                'final_grade' => $grade,
                'final_score' => round($finalScore, 1) . '%',
                'classification' => $enrollment->certificate?->classification ?? null,
                'certificate_number' => $enrollment->certificate?->certificate_number ?? null,
                'assessments' => $modules,
            ];
        }

        $overallGpa = $completedCount > 0 ? round($gpaSum / $completedCount, 2) : 0;
        $overallClass = $this->pointsToClassification($overallGpa);

        return [
            'user' => $user,
            'student_name' => $user->full_name,
            'student_number' => $this->generateStudentNumber($user),
            'national_id' => $user->student?->national_id ?? $user->national_id ?? 'Not on record',
            'date_of_birth' => $user->student?->date_of_birth?->format('F d, Y') ?? 'Not on record',
            'email' => $user->email,
            'phone' => $user->phone ?? 'N/A',
            'issue_date' => Carbon::now()->format('F d, Y'),
            'transcript_ref' => 'TRX-' . Carbon::now()->format('Ymd') . '-' . str_pad((string) $user->id, 4, '0', STR_PAD_LEFT),
            'total_courses' => $enrollments->count(),
            'completed_courses' => $completedCount,
            'total_credits' => $totalCredits,
            'gpa' => number_format($overallGpa, 2),
            'classification' => $overallClass,
            'enrollments' => $enrollmentData,
            'institution_name' => SystemSetting::get('institution_name', 'EduTrack Computer Training College'),
            'institution_address' => SystemSetting::get('site_address', 'Kalomo, Zambia'),
            'teveta_reg' => SystemSetting::get('teveta_registration_number', 'TVA/2064'),
        ];
    }

    /**
     * Generate a course code from the course ID.
     */
    protected function generateCourseCode(int $courseId): string
    {
        return 'ECTC-' . str_pad((string) $courseId, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a student number.
     */
    protected function generateStudentNumber($user): string
    {
        $certService = new CertificateService();
        // Re-use the logic or create a simple one
        $yearSuffix = substr(date('Y'), -2);
        if ($user->national_id) {
            $numberPart = preg_replace('/[^0-9]/', '', $user->national_id);
            if (strlen($numberPart) > 6) $numberPart = substr($numberPart, -6);
        } else {
            $numberPart = str_pad((string) $user->id, 6, '0', STR_PAD_LEFT);
        }
        return $yearSuffix . 'Edu' . $numberPart;
    }

    /**
     * Convert a score (0-100) to a letter grade.
     */
    protected function scoreToGrade(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B+',
            $score >= 70 => 'B',
            $score >= 60 => 'C+',
            $score >= 50 => 'C',
            default => 'D',
        };
    }

    /**
     * Convert a score (0-100) to GPA points.
     */
    protected function scoreToPoints(float $score): float
    {
        return match (true) {
            $score >= 90 => 5.0,
            $score >= 80 => 4.5,
            $score >= 70 => 4.0,
            $score >= 60 => 3.5,
            $score >= 50 => 3.0,
            default => 2.0,
        };
    }

    /**
     * Convert GPA points to classification.
     */
    protected function pointsToClassification(float $points): string
    {
        return match (true) {
            $points >= 4.5 => 'Distinction',
            $points >= 4.0 => 'Merit',
            $points >= 3.5 => 'Credit',
            $points >= 3.0 => 'Pass',
            default => 'Fail',
        };
    }

    /**
     * Generate a TCPDF transcript.
     */
    protected function generateTcpdf(array $data): string
    {
        $pdf = new class('P', 'mm', 'A4') extends \TCPDF {
            public function Header() {}
            public function Footer() {}
        };
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor($data['institution_name']);
        $pdf->SetTitle('Academic Transcript - ' . $data['student_name']);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->AddPage();

        $navy = [27, 58, 107];
        $lightGray = [247, 250, 252];
        $borderGray = [226, 232, 240];

        // ===== HEADER =====
        $logoPath = public_path('assets/images/logo.png');
        $hasLogo = is_file($logoPath);
        $headerY = $pdf->GetY();

        if ($hasLogo) {
            // Logo on the left
            $pdf->Image($logoPath, 12, $headerY, 0, 18);
            $pdf->SetXY(38, $headerY + 1);
        } else {
            $pdf->SetXY(12, $headerY);
        }

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(...$navy);
        $pdf->Cell(0, 8, $data['institution_name'], 0, 1, 'L');
        $pdf->SetX($hasLogo ? 38 : 12);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(0, 5, $data['institution_address'], 0, 1, 'L');
        $pdf->SetX($hasLogo ? 38 : 12);
        $pdf->Cell(0, 5, 'TEVETA Reg: ' . $data['teveta_reg'], 0, 1, 'L');

        $pdf->SetDrawColor(...$navy);
        $pdf->SetLineWidth(0.8);
        $lineY = max($pdf->GetY(), $headerY + 20) + 2;
        $pdf->Line(12, $lineY, 198, $lineY);
        $pdf->SetY($lineY + 4);

        // ===== TITLE =====
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(...$navy);
        $pdf->Cell(0, 8, 'OFFICIAL ACADEMIC TRANSCRIPT', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 4, 'This document certifies the academic record of the named student', 0, 1, 'C');
        $pdf->Ln(4);

        // ===== STUDENT INFO =====
        $pdf->SetFillColor(...$lightGray);
        $pdf->SetDrawColor(...$borderGray);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(...$navy);

        $infoRows = [
            ['Student Name:', $data['student_name'], 'Student Number:', $data['student_number']],
            ['NRC Number:', $data['national_id'], 'Date of Birth:', $data['date_of_birth']],
            ['Email:', $data['email'], 'Phone:', $data['phone']],
            ['Date of Issue:', $data['issue_date'], 'Transcript Ref:', $data['transcript_ref']],
        ];

        foreach ($infoRows as $row) {
            $pdf->Cell(30, 6, $row[0], 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(30, 30, 30);
            $pdf->Cell(65, 6, $row[1], 0, 0, 'L');
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetTextColor(...$navy);
            $pdf->Cell(30, 6, $row[2], 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(30, 30, 30);
            $pdf->Cell(0, 6, $row[3], 0, 1, 'L');
        }
        $pdf->Ln(3);

        // ===== SUMMARY BAR =====
        $pdf->SetFillColor(...$navy);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 10);
        $summaryItems = [
            'Total Courses: ' . $data['total_courses'],
            'Completed: ' . $data['completed_courses'],
            'Credits: ' . $data['total_credits'],
            'GPA: ' . $data['gpa'] . '/5.0',
            'Class: ' . $data['classification'],
        ];
        $cellW = 176 / count($summaryItems);
        foreach ($summaryItems as $item) {
            $pdf->Cell($cellW, 8, $item, 0, 0, 'C', true);
        }
        $pdf->Ln(10);

        // ===== COURSE SECTIONS =====
        $pdf->SetTextColor(0, 0, 0);
        foreach ($data['enrollments'] as $enrollment) {
            // Course header
            $pdf->SetFillColor(42, 74, 122);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(140, 7, $enrollment['course_code'] . ' — ' . $enrollment['course_title'], 0, 0, 'L', true);
            $pdf->Cell(36, 7, 'Level: ' . $enrollment['level'] . ' | Credits: ' . $enrollment['credits'], 0, 1, 'R', true);

            // Meta bar
            $pdf->SetFillColor(237, 242, 247);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetFont('helvetica', '', 8);
            $meta = 'Enrolled: ' . $enrollment['enrolled_at'] . ' | Completed: ' . $enrollment['completion_date'] . ' | Status: ' . $enrollment['status'];
            if ($enrollment['certificate_number']) {
                $meta .= ' | Cert: ' . $enrollment['certificate_number'];
            }
            $pdf->Cell(176, 5, $meta, 'LR', 1, 'L', true);

            // Assessment table
            $pdf->SetFillColor(...$navy);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('helvetica', 'B', 8);
            $cols = [18, 70, 22, 18, 18, 16, 14];
            $headers = ['Code', 'Module / Assessment', 'Type', 'Score', 'Max', '%', 'Grade'];
            foreach ($headers as $i => $h) {
                $pdf->Cell($cols[$i], 6, $h, 1, 0, 'C', true);
            }
            $pdf->Ln();

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 8);
            $fill = false;
            foreach ($enrollment['assessments'] as $assessment) {
                if ($fill) {
                    $pdf->SetFillColor(250, 251, 252);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }
                $pdf->Cell($cols[0], 5.5, $assessment['code'], 1, 0, 'C', true);
                $pdf->Cell($cols[1], 5.5, $assessment['title'], 1, 0, 'L', true);
                $pdf->Cell($cols[2], 5.5, $assessment['type'], 1, 0, 'L', true);
                $pdf->Cell($cols[3], 5.5, $assessment['score'], 1, 0, 'C', true);
                $pdf->Cell($cols[4], 5.5, $assessment['max'], 1, 0, 'C', true);
                $pdf->Cell($cols[5], 5.5, $assessment['percentage'], 1, 0, 'C', true);

                $grade = $assessment['grade'];
                $gradeColor = match ($grade[0] ?? 'D') {
                    'A' => [39, 103, 73],
                    'B' => [47, 133, 90],
                    'C' => [192, 86, 33],
                    default => [197, 48, 48],
                };
                $pdf->SetTextColor(...$gradeColor);
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->Cell($cols[6], 5.5, $grade, 1, 0, 'C', true);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Ln();
                $fill = !$fill;
            }

            // Course footer
            $pdf->SetFillColor(...$lightGray);
            $pdf->SetFont('helvetica', 'B', 9);
            $footerText = $enrollment['status'] === 'Completed'
                ? 'Final Grade: ' . $enrollment['final_grade'] . ' (' . $enrollment['final_score'] . ')'
                : 'Status: ' . $enrollment['status'] . ' (' . $enrollment['final_score'] . ')';
            $pdf->Cell(120, 6, $footerText, 1, 0, 'L', true);
            if ($enrollment['classification'] && $enrollment['status'] === 'Completed') {
                $pdf->SetFillColor(212, 149, 42);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(56, 6, $enrollment['classification'], 1, 0, 'C', true);
            } else {
                $pdf->Cell(56, 6, '', 1, 0, 'C', true);
            }
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(10);
        }

        // ===== GRADING SCALE =====
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(...$navy);
        $pdf->Cell(0, 6, 'GRADING SCALE', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $scale = [
            ['A', '90-100%', '5.0', 'Distinction'],
            ['B+', '80-89%', '4.5', 'Merit'],
            ['B', '70-79%', '4.0', 'Merit'],
            ['C+', '60-69%', '3.5', 'Credit'],
            ['C', '50-59%', '3.0', 'Pass'],
            ['D', '<50%', '2.0', 'Fail'],
        ];
        foreach ($scale as $s) {
            $pdf->Cell(20, 5, $s[0], 1, 0, 'C');
            $pdf->Cell(35, 5, $s[1], 1, 0, 'C');
            $pdf->Cell(25, 5, $s[2] . ' pts', 1, 0, 'C');
            $pdf->Cell(40, 5, $s[3], 1, 1, 'C');
        }
        $pdf->Ln(6);

        // ===== FOOTER =====
        $pdf->SetDrawColor(...$navy);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
        $pdf->Ln(3);
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->MultiCell(120, 4, "This transcript is an official record of academic achievement issued by {$data['institution_name']}. It certifies that the above-named student has completed the listed modules and assessments to the standard indicated.", 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Cell(0, 4, 'Verify at: ' . url('/certificates/verify') . ' | Ref: ' . $data['transcript_ref'], 0, 1, 'L');

        return $pdf->Output('', 'S');
    }
}
