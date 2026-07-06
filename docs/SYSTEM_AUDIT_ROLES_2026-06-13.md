# Edutrack LMS — Role & Workflow Audit (2026-06-13)

A second audit, this time through each role's eyes: *can they do what they need, is it discoverable/guided, and are there safeguards against data drift and siloing?* Findings are grounded in the code and ordered by severity within each section. The system now supports online **and** in-person students sharing one record set (offline marks, delivery mode, instructor/admin completion, weighted grading) — this audit checks whether the surrounding workflows and guardrails keep up.

Severity: 🔴 fix soon · 🟠 should fix · 🟢 polish.

---

## 1. Data integrity & consistency (cross-cutting)

### 🔴 1.1 Intake / course `enrollment_count` drifts — never decremented
`enrollment_count` on `intakes` and `courses` is maintained by **manual increment** only:
- `EnrollmentController::store` increments intake + course on create, and increments **intake again** on re-enrolment of a dropped student ([EnrollmentController.php:73,120-121](app/Http/Controllers/EnrollmentController.php)).
- Dropping a student (admin sets status `Dropped`) and **deleting** an enrolment (`Admin\EnrollmentController::destroy`) do **not** decrement either counter.

Consequences:
- An intake can show **"30/30 full"** when it isn't, **blocking new enrolments** (the capacity check reads `enrollment_count`).
- Re-enrolling a dropped student **double-counts** them.
- Course "students enrolled" stats inflate over time.

**Fix:** stop trusting the stored counter — recompute it (like `students.total_*` already does via observers). Either an `EnrollmentObserver` that recalculates `intake.enrollment_count = enrollments where status != Dropped` on save/delete, or decrement on drop/delete and don't re-increment on reactivation. Recompute is the safer pattern. *(Contrast: `students.total_courses_*` and `total_certificates` are correctly recomputed in `StudentObserver::syncCounters` — copy that approach.)*

### 🟢 1.2 What's already solid
- `students.total_courses_enrolled / completed / certificates` — recomputed from source on every enrolment/certificate change (`StudentObserver`). Robust.
- `enrollments.amount_paid` — recomputed as the sum of completed payments in the payment services, not hand-incremented. Good.
- `final_grade` — now recomputed across all assessments on every mark change. Good.
- Soft deletes everywhere, so destructive actions are recoverable.

---

## 2. Student perspective

### 🟠 2.1 The "registration fee first" rule is a wall, not a guided step
A student can browse courses, pick one, click Enrol — and only **then** get bounced to the K150 registration-fee page. The rule is mentioned on some pages but the flow itself doesn't *lead* them: there's no "Step 1: pay registration → Step 2: enrol" onboarding. First-time users hit a redirect they didn't expect.
**Fix:** a short onboarding strip on the student dashboard ("Welcome — 1. Pay your K150 registration 2. Enrol 3. Start learning"), and show the fee status up front rather than at the enrol gate.

### 🟠 2.2 In-person students get an online-shaped experience
Mode now exists, but the student UI doesn't adapt: an in-person student still sees "Mark as Complete" buttons, lesson-progress bars, and "Take the Quiz" prompts for work they do in class. Nothing's broken, but it can confuse ("the system says I'm 0% but I've attended every class").
**Fix:** for `in_person` enrolments, soften the self-service prompts (e.g. "Your instructor records your progress") and lean on the grade/transcript as the source of truth.

### 🟢 2.3 Discoverability gaps
No in-app help/FAQ for students on *how* to take a quiz, submit an assignment, or read their transcript. Empty-state components exist on ~10 views (good) but coverage is partial. A one-page "How learning works at Edutrack" help page would help the school-leaver audience.

---

## 3. Instructor perspective

### 🟠 3.1 New powerful actions have no guidance
Instructors can now **Record offline mark**, **Record offline score**, and **Mark Complete & Issue Cert** — but there's no instruction anywhere explaining *when/how* to use them, the difference between online vs offline marks, or that marking complete issues a certificate. For a small Zambian college where instructors may not be power users, this matters.
**Fix:** short inline helper text on each action ("Use this for work done on paper / in class") and a one-page instructor guide.

### 🟠 3.2 No "withdraw / drop a student" instructor action, and drop doesn't tidy up
Instructors can complete students but can't cleanly **drop/withdraw** one; that lives only in admin. And when a drop does happen, it doesn't free the intake seat (see 1.1).
**Fix:** decide who owns withdrawal (admin is fine) but make it consistent with counts.

### 🟢 3.3 Grading transparency
Now that the final grade is a 40/60 weighted total, instructors should see the same breakdown students see, so they can explain a grade. Currently the weighting is invisible in the instructor UI.

---

## 4. Admin perspective

### 🔴 4.1 Admin can't enrol a student — onboarding is incomplete
Admin can **create users** and **record payments**, and (now) grade/complete on any course — but there is **no admin "enrol a student into a course"** action (`Admin\EnrollmentController` has only index/update/destroy, no create/store). So an admin **cannot fully onboard an in-person student**: they'd create the account, then the *student* must log in and self-enrol before the admin can set mode/record marks. That breaks the in-person workflow we just built.
**Fix:** add an admin "Enrol student" form (pick user + course + intake + mode), reusing the same transaction/intake logic as `EnrollmentController::store`. This is the missing piece that makes admin-driven in-person onboarding actually work end-to-end.

### 🟠 4.2 Admin enrolment edit can set `final_grade` by hand, bypassing aggregation
`Admin\EnrollmentController::update` lets an admin type any `final_grade`. The next time any mark is recorded, the GradeAggregationService **overwrites** it. So a hand-set grade silently reverts — inconsistent and confusing.
**Fix:** either make `final_grade` read-only in admin (computed), or recompute on save so admin and the service never disagree.

### 🟢 4.3 No audit trail surfaced
There's an `activity_logs` table, but admins have no screen to see "who issued this certificate / changed this grade / recorded this offline mark." For money + certificates, an admin-visible audit log is worth having.

---

## 5. Finance perspective

### 🟢 5.1 Mostly coherent
Finance can verify payments, view transactions/invoices. Two gaps worth noting:
- Cash/in-person payments: an admin can record a payment, but the **finance** role's path for "student paid cash at the desk" should be explicit and reconcile with `amount_paid` + certificate unblocking (it does flow through the same payment record, which is good).
- No guidance distinguishing "verify a Lenco/online payment" from "record a manual cash payment."

---

## 6. Safeguards against data isolation (siloing)

The unification work (one record set for online + in-person; offline marks feeding the same grades/transcripts/certificates) is the right architecture and largely closes the silo risk. Remaining edges:
- 🟠 **Assessments not linked to a lesson** were invisible on the transcript until today's fix — a reminder that *any* new "record type" must be wired into the transcript/grade aggregation or it silently siloes. Keep that checklist when adding features.
- 🟢 **Certificate issuance has two paths** (auto at 100% lesson progress for online; instructor/admin mark-complete for in-person) — both converge on the same `CertificateService`, so no divergence. Good.
- 🟢 **Authorisation** is consistent post-admin-access change (admins bypass ownership, instructors scoped to owned courses, students to their own enrolments; one IDOR fixed earlier).

---

## 7. Prioritised action list

| # | Item | Severity | Effort |
|---|------|----------|--------|
| 1 | Recompute `intake`/`course` enrollment_count (stop drift / false "full") | 🔴 | ~half day |
| 2 | Admin "Enrol a student" action (completes in-person onboarding) | 🔴 | ~half day |
| 3 | Admin `final_grade` edit conflicts with aggregation — make computed/recompute | 🟠 | small |
| 4 | Inline helper text + one-page guides (instructor actions, student "how learning works") | 🟠 | 1 day |
| 5 | Student onboarding strip + in-person UI softening | 🟠 | 1 day |
| 6 | Admin-visible audit log (certificates/grades/payments) | 🟢 | 1–2 days |
| 7 | Instructor-visible grade breakdown/weighting | 🟢 | small |

**Top two (🔴) are the ones that block real workflows or corrupt data:** the enrollment_count drift (can wrongly block enrolments) and the missing admin enrol action (in-person onboarding can't be completed by staff). I'd do those first.
