# Edutrack LMS — Experiential UX Walkthrough by Role (2026-06-14)

Not a code audit. This is "walk a mile in each user's shoes": log in as each role, try to do the real job, and note where it feels confusing, where you get stuck, and the edge cases that break the illusion. Friction is rated: 🔴 blocks the job · 🟠 confusing/clunky · 🟢 fine.

---

## ADMIN (the person running the college)

**First impression:** A long left sidebar — Dashboard, Users, Courses, Intakes, Enrolments, Payments, Announcements, Events, Photos, Team, Testimonials, Email Templates, Badges, Newsletter, Promotions, Certificates, Teaching (Course Builder/Grade/Quizzes/Class Progress), Reports, Settings. It's comprehensive but **long and flat** — 20+ items with no grouping beyond the new "Teaching" header. A new admin doesn't know where to start.

**🔴 Can't read course content.** The #1 job of an admin doing quality control — "let me read what we're actually teaching" — has no path. The student reading view is gated to students; the Course Builder only exposes content through Edit forms; "Courses" shows counts. To review a course you'd have to enrol yourself. This is the clearest "nobody designed for the admin" gap.

**🟠 "Courses" vs "Course Builder" — two doors, unclear difference.** Admin → Courses (metadata/approve) and Admin → Teaching → Course Builder (modules/lessons) are different screens for the same thing. An admin won't know which to use to "edit a course."

**🟠 Editing a student is split across screens.** To fully set up an in-person student: create the user (Users), then enrol them (Enrolments → Enrol a Student), then record payment (Payments), then maybe set grade/completion (Class Progress). Four screens, no guided "onboard a student" flow. Each works; the journey isn't connected.

**🟠 Grade field shows but the story is thin.** Admin sees a computed final grade on an enrolment, but no breakdown of how it was reached without opening the student's transcript separately.

**🟢 Working well:** enrolment management, payment recording, the new Enrol-a-Student form, certificates list, the Teaching section now reaching grading.

---

## INSTRUCTOR (the person teaching)

**First impression:** Cleaner sidebar — Dashboard, My Courses, Assignments, Quizzes, Submissions, Class Progress, Analytics. Reasonable.

**🔴 Can't preview their own course as a student sees it.** An instructor builds modules/lessons in the Course Builder (edit forms) but has **no "view as student"** to check how the lesson actually reads, whether the video embeds, whether resources download. They're authoring blind. Big confidence gap.

**🟠 Grading is scattered across three screens.** Offline assignment marks live on Assignments; offline quiz scores live on a quiz's Attempts (reached by drilling Quizzes → a quiz → Attempts); completion/certificate lives on Class Progress. There's **no single gradebook** for "show me this student and everything they've done." Recording a full set of marks means hopping around.

**🟠 "Record offline mark/score" has no guidance.** Powerful, but nothing explains when to use it vs online submission, or that Mark Complete issues a certificate. A non-power-user instructor will hesitate.

**🟢 Working well:** course builder itself, class progress list, mark-complete for in-person students, the new Attempts link.

---

## STUDENT (the learner — the most important experience)

**First impression:** Good sidebar — Dashboard, My Courses, Assignments, Notes, Schedule, Quizzes, Progress, Certificates, Transcript, Payments, Achievements, Profile. This role got the most design love.

**🟠 The first-time wall: registration fee.** A new student browses, picks a course, clicks Enrol — and is bounced to "pay K150 first." It works, but it's a surprise, not a guided step. There's no "Welcome — here's how to get started (1 pay, 2 enrol, 3 learn)."

**🟠 Paid → now what?** After paying, the path to actually *start learning* (My Courses → course → first lesson) isn't signposted. A student can land on a course page and not see the obvious "Start" entry point.

**🟠 In-person students see an online-shaped UI.** A physical student logs in to read notes, but still sees "Mark as Complete" buttons and a 0% progress bar for work they did in class — implying they're behind when they're not.

**🟢 Working well:** the lesson reading view with the sticky module sidebar, quizzes grouped by course/module with lock states, the module-quiz prompt after lessons, transcript, certificates.

---

## FINANCE (the person handling money)

**First impression:** Tight, focused sidebar — Dashboard, Transactions, Verify Payments, Invoices. Appropriately narrow.

**🟠 "Verify a Lenco payment" vs "record a cash payment" isn't distinguished.** A desk clerk taking cash and an online mobile-money payment flow through similar screens with no guidance on which is which.

**🟢 Working well:** verification, transactions, invoices are coherent for the role.

---

## Cross-cutting feelings

- **No content-reading view for staff** (admin/instructor) — the single biggest "this wasn't designed for me" gap. Affects QA, teaching confidence, and support.
- **No in-app guidance anywhere** — no first-run tips, no "how this works" for any role. Everyone learns by poking.
- **Powerful new actions are undocumented** (offline marks, mark-complete, modes) — they exist but don't explain themselves.
- **Journeys aren't connected** — admin onboarding a student, student getting from payment to first lesson, instructor authoring-then-checking — each is a set of correct screens that don't hand off to each other.
- **Orphaned/edge data shows through** in staff views (e.g., deleted-user enrolments caused a 500 until guarded) — admins see the messy underside.

---

## What would make it *feel* right (priority order)

1. **🔴 Staff "Read / Preview course" view** — let admins and instructors open any course and read every lesson + download resources exactly as a student would (read-only). Fixes the admin's core question and lets instructors check their own work. *Highest impact, moderate effort.*
2. **🟠 Connect the journeys** — student onboarding strip (pay → enrol → start); admin "Onboard a student" wizard that chains user→enrol→payment; a "Start learning" CTA after payment.
3. **🟠 One instructor gradebook** — a per-course screen: students down the side, assessments across, marks in the grid, with the offline-entry inline.
4. **🟠 Light inline guidance** — one-line helper text on the powerful actions; a short "how it works" per role.
5. **🟠 In-person mode UX** — soften online-only prompts for in_person enrolments; lean on grades/transcript as the source of truth.
6. **🟠 Merge "Courses" and "Course Builder"** for admins so there's one obvious door to a course.
