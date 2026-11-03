<?php

namespace App\Support;

/**
 * The TEVETA Zambia grading scale. Marks are percentages (0-100); a student
 * below the pass mark has failed and cannot be awarded a certificate.
 */
class TevetaGrade
{
    public const PASS_MARK = 50;

    /** Lowest mark for each grade, best first. */
    public const BANDS = [
        'Distinction' => 85,
        'Merit' => 70,
        'Credit' => 60,
        'Pass' => 50,
        'Fail' => 0,
    ];

    public static function for(float $mark): string
    {
        foreach (self::BANDS as $grade => $lowest) {
            if ($mark >= $lowest) {
                return $grade;
            }
        }

        return 'Fail';
    }

    public static function passed(float $mark): bool
    {
        return $mark >= self::PASS_MARK;
    }

    /** Rows for a printed scale: [grade, "85% - 100%"]. */
    public static function scale(): array
    {
        $rows = [];
        $top = 100;
        foreach (self::BANDS as $grade => $lowest) {
            $rows[] = [$grade, $lowest . '% - ' . $top . '%'];
            $top = $lowest - 1;
        }

        return $rows;
    }
}
