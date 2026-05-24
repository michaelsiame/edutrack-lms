<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use TCPDF;

class LessonExportService
{
    public function generatePdf(Course $course, Lesson $lesson): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4');
        $pdf->SetCreator('Edutrack LMS');
        $pdf->SetAuthor('Edutrack Computer Training College');
        $pdf->SetTitle($lesson->title . ' - ' . $course->title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->SetFont('dejavuserif', '', 10);
        $pdf->AddPage();

        $html = view('exports.lesson-pdf', [
            'course' => $course,
            'lesson' => $lesson,
            'content' => HtmlSanitizer::clean($lesson->content),
        ])->render();

        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output('', 'S');
    }
}
