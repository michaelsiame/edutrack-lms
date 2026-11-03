<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\CertificateService;
use App\Support\TevetaGrade;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * TEVETA grading: Distinction 85-100, Merit 70-84, Credit 60-69, Pass 50-59,
 * Fail 0-49. The certificate carries the grade ("With Merit"), a fail gets no
 * certificate, and a certificate is dated the day it is issued.
 */
class CertificateGradingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_marks_follow_the_teveta_scale(): void
    {
        $this->assertSame('Distinction', TevetaGrade::for(85));
        $this->assertSame('Merit', TevetaGrade::for(84.9));
        $this->assertSame('Merit', TevetaGrade::for(70));
        $this->assertSame('Credit', TevetaGrade::for(69));
        $this->assertSame('Credit', TevetaGrade::for(60));
        $this->assertSame('Pass', TevetaGrade::for(59));
        $this->assertSame('Pass', TevetaGrade::for(50));
        $this->assertSame('Fail', TevetaGrade::for(49.9));
        $this->assertSame('Fail', TevetaGrade::for(0));
        $this->assertSame([
            ['Distinction', '85% - 100%'], ['Merit', '70% - 84%'], ['Credit', '60% - 69%'],
            ['Pass', '50% - 59%'], ['Fail', '0% - 49%'],
        ], TevetaGrade::scale());
    }

    public function test_a_fail_gets_no_certificate(): void
    {
        $enrollment = $this->completedEnrollment(45);

        $this->assertNull((new CertificateService())->issueCertificate($enrollment));
        $this->assertFalse((bool) $enrollment->fresh()->certificate_issued);
    }

    public function test_the_grade_on_the_certificate_follows_the_mark(): void
    {
        $service = new CertificateService();
        foreach ([[72, 'Merit'], [64, 'Credit'], [55, 'Pass']] as [$mark, $grade]) {
            $certificate = $service->issueCertificate($this->completedEnrollment($mark));
            $this->assertSame($grade, $service->getCertificateData($certificate)['classification']);
        }
    }

    public function test_a_pass_carries_its_teveta_grade_and_is_dated_the_day_it_is_issued(): void
    {
        $this->travelTo('2026-11-03 09:00');
        $enrollment = $this->completedEnrollment(88);
        $service = new CertificateService();

        $certificate = $service->issueCertificate($enrollment);
        $this->assertNotNull($certificate);

        // Even with an earlier ceremony date on record, the print uses the issue date.
        // (Not every schema has the column, so it is set on the model only.)
        $certificate->graduation_ceremony_date = \Carbon\Carbon::parse('2026-08-07');
        $data = $service->getCertificateData($certificate);

        $this->assertSame(['3', 'rd', 'November', '2026'], [
            $data['graduation_day'], $data['graduation_suffix'], $data['graduation_month'], $data['graduation_year'],
        ]);
        $this->assertSame('Distinction', $data['classification']);
    }

    public function test_a_common_issue_date_dates_new_certificates_but_never_backdates(): void
    {
        $this->travelTo('2026-11-03 09:00');
        $service = new CertificateService();

        SystemSetting::set(CertificateService::COMMON_ISSUE_DATE, '2026-11-20');
        $this->assertSame('2026-11-20', $service->issueCertificate($this->completedEnrollment(75))->issued_date->toDateString());

        // Once that day has passed, a new certificate is dated the day it is issued.
        $this->travelTo('2026-11-25 09:00');
        $this->assertSame('2026-11-25', $service->issueCertificate($this->completedEnrollment(75))->issued_date->toDateString());
    }

    public function test_an_admin_cannot_set_a_past_issue_date(): void
    {
        $this->travelTo('2026-11-03 09:00');
        $this->withoutMiddleware();

        $this->post(route('admin.certificates.issue-date'), ['issue_date' => '2026-11-02'])
            ->assertSessionHasErrors('issue_date');
        $this->flushSession();
        $this->post(route('admin.certificates.issue-date'), ['issue_date' => '2026-11-03'])
            ->assertSessionHasNoErrors();
        $this->assertSame('2026-11-03', SystemSetting::get(CertificateService::COMMON_ISSUE_DATE));

        $this->post(route('admin.certificates.issue-date'), ['clear' => 1]);
        $this->assertNull(SystemSetting::get(CertificateService::COMMON_ISSUE_DATE));
    }

    private function completedEnrollment(float $grade): Enrollment
    {
        $user = User::create([
            'username' => 'certgrade_' . uniqid(),
            'email' => 'certgrade+' . uniqid() . '@example.com',
            'password_hash' => bcrypt('secret-password'),
            'first_name' => 'Test',
            'last_name' => 'Graduate',
            'status' => 'active',
        ]);
        $student = Student::create(['user_id' => $user->id, 'enrollment_date' => now()]);
        $category = CourseCategory::create(['name' => 'Test ' . uniqid()]);
        $course = Course::create([
            'category_id' => $category->id,
            'title' => 'Test Course ' . uniqid(),
            'slug' => 'test-course-' . uniqid(),
            'description' => 'Test',
            'status' => 'published',
        ]);

        return Enrollment::create([
            'user_id' => $user->id,
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'enrollment_status' => 'Completed',
            'final_grade' => $grade,
            'certificate_blocked' => false,
        ]);
    }
}
