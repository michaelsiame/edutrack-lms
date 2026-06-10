<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'google_id',
        'password_hash',
        'first_name',
        'last_name',
        'phone',
        'avatar_url',
        'status',
        'email_verified',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'email_verification_token',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified' => 'boolean',
        'email_verification_expires' => 'datetime',
        'last_login' => 'datetime',
        'account_locked_until' => 'datetime',
        'failed_login_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Role Checks
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->roles()->whereIn('role_id', [1, 2])->exists();
    }

    public function isInstructor(): bool
    {
        return $this->roles()->where('role_id', 3)->exists();
    }

    public function isFinance(): bool
    {
        return $this->roles()->where('role_id', 6)->exists();
    }

    public function isStudent(): bool
    {
        return $this->roles()->where('role_id', 4)->exists();
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function hasRole(int $roleId): bool
    {
        return $this->roles()->where('role_id', $roleId)->exists();
    }

    public function isEnrolledIn(int $courseId): bool
    {
        return $this->enrollments()->where('course_id', $courseId)->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function instructor()
    {
        return $this->hasOne(Instructor::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'student_id')
            ->whereIn('student_id', function ($query) {
                $query->select('id')->from('students')->where('user_id', $this->id);
            });
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'student_id');
    }

    public function assignmentSubmissions()
    {
        return $this->hasMany(AssignmentSubmission::class, 'student_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'posted_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function courseReviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    public function liveSessionAttendance()
    {
        return $this->hasMany(LiveSessionAttendance::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getNationalIdAttribute(): ?string
    {
        return $this->profile?->nrc_number;
    }

    public function getDateOfBirthAttribute(): ?string
    {
        return $this->profile?->date_of_birth?->format('F d, Y');
    }

    public function getPasswordAttribute(): string
    {
        return $this->password_hash;
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password_hash'] = bcrypt($value);
    }
}
