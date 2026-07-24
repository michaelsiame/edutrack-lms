<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Intake;
use App\Models\Student;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BatchGenerate2026Certificates extends Command
{
    protected $signature = 'certificates:batch-2026
        {--dry : List what would be done without writing anything}
        {--no-pdf : Create records only, skip PDF files}
        {--out=certificates/2026 : storage/app path to write PDFs into}';

    protected $description = 'Enrol, graduate and generate printable certificates for the 2026 graduation list';

    public function handle(CertificateService $certs): int
    {
        $data = require database_path('data/graduates-2026.php');
        $durations = $data['default_durations'] ?? [];
        $rows = $data['graduates'] ?? [];
        $dry = (bool) $this->option('dry');
        $out = trim((string) $this->option('out'), '/');

        $dir = storage_path('app/' . $out);
        if (!$dry && !$this->option('no-pdf') && !is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $ceremony = \Carbon\Carbon::create(2026, 7, 1);
        $ok = 0;
        $fail = 0;

        foreach ($rows as $i => [$studentId, $name, $courseTitle, $grade]) {
            try {
                $result = DB::transaction(function () use ($studentId, $name, $courseTitle, $grade, $durations, $ceremony, $dry) {
                    return $this->processOne($studentId, $name, $courseTitle, $grade, $durations, $ceremony, $dry);
                });

                if ($dry) {
                    $this->line(sprintf('  DRY  %-26s  %-34s  %s', $name, $courseTitle, $grade));
                    $ok++;
                    continue;
                }

                $data = $result;

                if (!$this->option('no-pdf')) {
                    $pdf = $certs->renderPdf($data);
                    $file = $dir . '/' . $this->safeName($name, $courseTitle, $data['certificate_number']) . '.pdf';
                    file_put_contents($file, $pdf);
                }

                $this->info(sprintf('  OK   %-26s  %-34s  %s  [%s]', $name, $courseTitle, $grade, $data['certificate_number']));
                $ok++;
            } catch (\Throwable $e) {
                $this->error(sprintf('  FAIL %-26s  %-34s  %s', $name, $courseTitle, $e->getMessage()));
                $fail++;
            }
        }

        $this->newLine();
        $this->info("Done. {$ok} processed, {$fail} failed." . ($dry ? ' (dry run — nothing written)' : " PDFs in storage/app/{$out}"));

        return $fail === 0 ? self::SUCCESS : self::FAILURE;
    }

    protected function processOne(string $studentId, string $name, string $courseTitle, string $grade, array $durations, \Carbon\Carbon $ceremony, bool $dry): ?array
    {
        // --- Course (match by title, create if missing) ---
        $course = Course::where('title', 'like', '%' . $courseTitle . '%')
            ->orWhere('title', 'like', '%' . str_replace(' & ', ' and ', $courseTitle) . '%')
            ->first();

        if (!$course && !$dry) {
            $attrs = [
                'title' => 'Certificate in ' . $courseTitle,
                'slug' => Str::slug('certificate-in-' . $courseTitle),
                'description' => 'Certificate in ' . $courseTitle,
                'duration_weeks' => $durations[$courseTitle] ?? 12,
                'price' => 0,
                'status' => 'published',
                'level' => 'Beginner',
            ];
            // category_id only exists / is needed on some schemas; and the
            // categories table may be absent entirely. Guard both.
            if (Schema::hasColumn('courses', 'category_id')) {
                $catId = null;
                if (Schema::hasTable('categories')) {
                    $catId = DB::table('categories')->value('id');
                }
                // No categories table on this schema — borrow a category_id
                // already used by an existing course so the insert satisfies
                // the NOT NULL column.
                $catId = $catId ?? Course::whereNotNull('category_id')->value('category_id');
                if ($catId) {
                    $attrs['category_id'] = $catId;
                }
            }
            $course = Course::create($attrs);
        }
        if ($dry) {
            return null;
        }

        // --- User + Student (match by student_number, else create) ---
        $student = Student::where('student_number', $studentId)->first();
        $user = $student?->user;

        if (!$user) {
            $parts = preg_split('/\s+/', trim($name));
            $first = $parts[0] ?? $name;
            $last = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
            $email = Str::slug($name, '.') . '.' . strtolower(Str::random(4)) . '@graduates.edutrackzambia.com';

            $pwd = bcrypt(Str::random(24));
            $user = new User();
            $user->forceFill(array_filter([
                'first_name' => ucwords(strtolower($first)),
                'last_name' => ucwords(strtolower($last)),
                'username' => Str::slug($name) . '-' . strtolower(Str::random(5)),
                'email' => $email,
                'email_verified_at' => Schema::hasColumn('users', 'email_verified_at') ? now() : null,
                'password' => Schema::hasColumn('users', 'password') ? $pwd : null,
                'password_hash' => Schema::hasColumn('users', 'password_hash') ? $pwd : null,
            ], fn ($v) => $v !== null))->save();
        }

        if (!$student) {
            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $studentId,
                'enrollment_date' => $ceremony,
            ]);
        }

        // --- Intake + completed Enrolment ---
        $intake = Intake::firstOrCreate(
            ['course_id' => $course->id, 'is_default' => true],
            ['name' => 'Open Enrollment', 'status' => 'open', 'max_students' => 0, 'enrollment_count' => 0, 'display_order' => 0]
        );

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            [
                'student_id' => $student->id,
                'intake_id' => $intake->id,
                'enrolled_at' => $ceremony,
                'enrollment_status' => 'Completed',
                'payment_status' => 'completed',
                'amount_paid' => 0,
                'certificate_blocked' => false,
                'mode' => 'online',
            ]
        );
        $enrollment->update([
            'enrollment_status' => 'Completed',
            'certificate_blocked' => false,
            'completion_date' => $enrollment->completion_date ?? $ceremony,
        ]);

        // --- Certificate (classification straight from the list grade) ---
        $classification = ucfirst(strtolower($grade));
        $score = match (strtoupper($grade)) {
            'DISTINCTION' => 85,
            'MERIT' => 70,
            'CREDIT' => 60,
            default => 50,
        };

        $certificate = Certificate::where('enrollment_id', $enrollment->id)->first();

        if (!$certificate) {
            $certificate = new Certificate();
            $certificate->user_id = $user->id;
            $certificate->course_id = $course->id;
            $certificate->enrollment_id = $enrollment->id;
            $certificate->certificate_number = $this->nextCertNumber();
            $certificate->verification_code = Str::random(32);
        }

        // Only set columns that actually exist on this (non-standard) schema.
        $certificate->issued_date = $ceremony;
        foreach ([
            'classification' => $classification,
            'final_score' => $score,
            'issued_at' => now(),
            'graduation_ceremony_date' => $ceremony,
            'is_verified' => true,
        ] as $col => $val) {
            if (Schema::hasColumn('certificates', $col)) {
                $certificate->{$col} = $val;
            }
        }
        $certificate->save();

        $enrollment->update(['certificate_issued' => true]);

        // Return everything the PDF needs, built from the list (NOT read back
        // from DB columns that may not exist, e.g. classification).
        return [
            'certificate' => $certificate,
            'student_name' => ucwords(strtolower($name)),
            'course_title' => $course->title ?? ('Certificate in ' . $courseTitle),
            'duration' => $this->durationString($courseTitle, $durations, $course),
            'classification' => $classification,
            'certificate_number' => $certificate->certificate_number,
            'verify_url' => route('certificates.verify', $certificate->verification_code),
            'graduation_day' => $ceremony->format('j'),
            'graduation_suffix' => $this->daySuffix((int) $ceremony->format('j')),
            'graduation_month' => $ceremony->format('F'),
            'graduation_year' => $ceremony->format('Y'),
            'student_number' => $studentId,
            'national_id' => '',
        ];
    }

    /** Sequential ECTC number, race-safe within the batch transaction. */
    protected function nextCertNumber(): string
    {
        $prefix = 'ECTC' . date('y');
        $last = Certificate::where('certificate_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(certificate_number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED)) as seq')
            ->value('seq');

        return $prefix . str_pad((string) (((int) $last) + 1), 3, '0', STR_PAD_LEFT);
    }

    protected function safeName(string $name, string $course, string $certNo): string
    {
        return $certNo . '-' . Str::slug($name) . '-' . Str::slug(Str::limit($course, 20, ''));
    }

    protected function durationString(string $title, array $durations, ?Course $course): string
    {
        $weeks = ($course && $course->duration_weeks) ? (int) $course->duration_weeks : ($durations[$title] ?? 12);
        if ($weeks % 4 === 0) {
            $m = intdiv($weeks, 4);
            return $m . ' Month' . ($m > 1 ? 's' : '');
        }
        return $weeks . ' Week' . ($weeks > 1 ? 's' : '');
    }

    protected function daySuffix(int $day): string
    {
        return match (true) {
            $day >= 11 && $day <= 13 => 'th',
            $day % 10 === 1 => 'st',
            $day % 10 === 2 => 'nd',
            $day % 10 === 3 => 'rd',
            default => 'th',
        };
    }
}
