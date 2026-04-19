# System & Logic Review (April 2026)

Comprehensive audit of business logic, authentication, payment flows, data integrity, and grading systems. Findings are grouped by domain and prioritised by severity.

---

## CRITICAL — Data loss, financial fraud, or credential bypass

### 1. Payment webhook has no signature verification
**Files:** `public/api/payment-callback.php`, `src/classes/Lenco.php:309-317`
- Mobile money callbacks (MTN, Airtel, Zamtel) accept requests with zero cryptographic validation.
- `Lenco.verifyWebhookSignature()` exists but is never called from webhook handlers.
- **Impact:** Attacker POSTs fake payment confirmation → enrollment activates → free course access.
- **Fix:** Call `verifyWebhookSignature()` on every incoming webhook. Reject unverified payloads.

### 2. Course price not verified server-side at checkout
**File:** `public/checkout.php:63,89`
- Payment amount comes from a hidden form field originating from client-side JS.
- No server-side check that the submitted price matches the current course price in the DB.
- **Impact:** Student edits hidden field in dev tools → pays K1 instead of K5,000.
- **Fix:** Always read price from `courses` table at submission time. Reject mismatches.

### 3. Lesson progress API skips payment check
**File:** `public/api/lesson-progress.php:27-40`
- Verifies enrollment status but does NOT call `canAccessContent()`.
- The 30% payment rule is enforced only in `course.php`, not in the API.
- **Impact:** Student with 0% paid hits the API to mark lessons complete → course progress fakes through.
- **Fix:** Add `$enrollment->canAccessContent()` check in the API before recording progress.

### 4. True/false questions always scored as incorrect
**File:** `public/actions/submit-quiz.php:132-141`
- `is_correct` and `points_earned` are hardcoded to `0` for true/false questions.
- **Impact:** Quizzes with T/F questions are mathematically impossible to pass at full marks.
- **Fix:** Compare student answer against the correct answer for T/F questions, same as multiple choice.

### 5. Short answer questions always scored as zero
**File:** `public/actions/submit-quiz.php:142-150`
- Same hardcoded `0` issue as T/F.
- **Fix:** Either auto-grade with case-insensitive string match against expected answer, or flag as "pending manual grading" (not "wrong").

### 6. Quiz time limit only enforced in JavaScript
**File:** `public/student/take-quiz.php:282-310`, `public/actions/submit-quiz.php:160`
- Timer is client-side only. Disabling JS or holding the form open bypasses the limit.
- **Impact:** A 15-minute quiz becomes a take-home exam.
- **Fix:** Record `started_at` in DB when quiz begins. On submission, reject if `NOW() - started_at > time_limit + grace`.

### 7. Password reset race condition (TOCTOU)
**File:** `src/includes/auth.php:517-558`
- Token is fetched at line 522, then cleared at line 537-541. Two concurrent requests can both pass.
- **Impact:** Attacker can use the same reset token twice in a timing window.
- **Fix:** Use atomic `UPDATE ... WHERE password_reset_token = ? RETURNING id` or wrap in a transaction with `FOR UPDATE`.

### 8. Course completion ignores quiz results
**File:** `public/api/lesson-progress.php:179-216`
- Course marked "completed" when `progress_percentage >= 100` based ONLY on lessons.
- No check for quiz pass/fail.
- **Impact:** Student marks all lessons done, fails every quiz → still gets certificate.
- **Fix:** Add quiz pass check: `SELECT COUNT(*) FROM quiz_attempts WHERE course_id = ? AND user_id = ? AND status = 'Failed'`.

---

## HIGH — Access control gaps, data corruption

### 9. Email verification not enforced at login
**File:** `src/includes/auth.php:132-260`
- `email_verified` flag exists but `loginUser()` never checks it.
- **Impact:** Users register with fake emails, immediately access the system.
- **Fix:** Add early return in `loginUser()` when `email_verified === 0`.

### 10. API registration bypasses password strength rules
**File:** `public/api/auth.php:277`
- API only checks `strlen($password) < 8`. Form registration requires uppercase + number + special char.
- **Fix:** Call `validatePasswordStrength()` in the API path too.

### 11. Payment webhook replay increments total_paid
**File:** `public/api/payment-callback.php:162-181`
- `UPDATE SET total_paid = total_paid + ?` runs on every webhook call. No idempotency check.
- **Impact:** Replaying the same webhook 5x increases `total_paid` by 5x the actual amount.
- **Fix:** Check `WHERE transaction_id = ? AND payment_status = 'Pending'` to only process once.

### 12. Refund doesn't downgrade enrollment access
**File:** `src/classes/Lenco.php:439-443`
- Payment marked "Refunded" but enrollment status is not re-evaluated.
- **Impact:** Student gets refund, keeps full course access and downloadable certificate.
- **Fix:** After refund, check remaining `total_paid` vs 30% threshold and downgrade if needed.

### 13. Assignment deadline not enforced server-side
**File:** `public/actions/submit-assignment.php`
- No `due_date` check before accepting submission. `late_penalty_percent` DB field never used.
- **Fix:** Compare `NOW()` against `assignments.due_date`. Flag as late or reject.

### 14. Unanswered quiz questions not recorded
**File:** `public/actions/submit-quiz.php:116`
- `if ($userAnswer)` — blank answers create no `quiz_responses` row.
- **Impact:** Instructor can't see which questions were skipped. Analytics undercount.
- **Fix:** Always insert a response row; set `is_correct = 0, points_earned = 0` for blanks.

### 15. Instructor can manually set 100% progress
**File:** `public/instructor/quick-actions.php:152-173`
- `bulkUpdateProgress()` sets progress to 100% and triggers certificate without verifying lesson completion.
- **Fix:** Add audit log entry and require confirmation if setting progress above actual completion.

---

## MEDIUM — Data integrity, schema issues

### 16. Enum value mismatches across tables
- `enrollments.payment_status`: `'pending','completed','failed','refunded'` (lowercase)
- `payments.payment_status`: `'Pending','Completed','Failed','Refunded'` (PascalCase)
- `Progress.php:292` sets `'completed'` but enrollment enum expects `'Completed'`
- **Fix:** Standardise all enums to lowercase across schema and PHP code.

### 17. Missing UNIQUE constraint on enrollments (user_id, course_id)
- No DB-level prevention of double-enrollment.
- `ON DUPLICATE KEY UPDATE` in progress tracking relies on a unique key that may not exist.
- **Fix:** `ALTER TABLE enrollments ADD UNIQUE KEY uk_enrollment (student_id, course_id);`

### 18. Missing UNIQUE on lesson_progress (enrollment_id, lesson_id)
- Same lesson can be marked complete multiple times, inflating progress percentage.
- **Fix:** `ALTER TABLE lesson_progress ADD UNIQUE KEY uk_progress (enrollment_id, lesson_id);`

### 19. CASCADE DELETE on courses destroys enrollment history
**File:** `migrations/004_add_unique_constraints.sql:67`
- Deleting a course cascades to enrollments, payments, progress, certificates.
- `Course::delete()` has no safeguard or soft-delete.
- **Fix:** Change to `ON DELETE RESTRICT`. Implement soft-delete (`status = 'archived'`).

### 20. No foreign key from enrollments.student_id → students table
- Orphan enrollments possible if student record is deleted.
- **Fix:** Add FK constraint with appropriate ON DELETE behavior.

### 21. Missing foreign keys on payments table
- `enrollment_id` and `payment_plan_id` are nullable with no FK constraints.
- Payments can become orphaned and break financial audit trails.

### 22. No concurrent session limit
**File:** `src/includes/auth.php:303-348`
- Users can have unlimited active sessions. No "logout all devices" feature.
- If password is compromised, attacker's session persists after password change.
- **Fix:** Add session invalidation on password change.

### 23. Refresh tokens not bound to client
**File:** `public/api/auth.php:351-388`
- 30-day refresh tokens usable from any IP/device.
- **Fix:** Store IP hash with token, validate on use.

### 24. Reset tokens stored in plaintext
**File:** `src/includes/auth.php:466-508`
- If DB is compromised, all pending reset tokens are exposed.
- **Fix:** Store `hash('sha256', $token)`, compare with hashed input.

### 25. Empty courses can auto-complete
**File:** `public/api/lesson-progress.php:155-156`
- If `$totalLessons === 0`, function returns early but progress may retain old 100% value.
- **Fix:** Set `progress_percentage = 0` when total lessons is 0.

---

## Owner Checklist

**Week 1 (Critical):**
- [ ] Add webhook signature verification to payment-callback.php (#1)
- [ ] Verify course price from DB in checkout.php (#2)
- [ ] Add payment check in lesson-progress API (#3)
- [ ] Fix T/F and short-answer quiz scoring (#4, #5)
- [ ] Add server-side quiz time limit (#6)
- [ ] Fix password reset race condition (#7)
- [ ] Add quiz pass requirement for course completion (#8)

**Week 2 (High):**
- [ ] Enforce email verification at login (#9)
- [ ] Add password strength to API registration (#10)
- [ ] Add webhook idempotency (#11)
- [ ] Downgrade enrollment on refund (#12)
- [ ] Enforce assignment deadlines (#13)
- [ ] Record blank quiz answers (#14)
- [ ] Add audit logging for manual progress override (#15)

**Week 3 (Medium):**
- [ ] Standardise enum casing across schema (#16)
- [ ] Add missing UNIQUE constraints (#17, #18)
- [ ] Change CASCADE to RESTRICT on courses FK (#19)
- [ ] Add missing foreign keys (#20, #21)
- [ ] Invalidate sessions on password change (#22)
- [ ] Hash tokens before storage (#24)

---

*Prepared: 18 April 2026*
