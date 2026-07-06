# Edutrack LMS — Capabilities, What Works, and Limitations (2026-06-14)

A grounded inventory of what the system does, what's working, and the real limitations/ceilings — especially the third-party constraints (Gmail send limits, Jitsi, Lenco, shared hosting) that affect scaling.

---

## 1. Authentication & roles
**What's there:** Email/password login (with throttling + account-status enforcement), Google OAuth (Socialite), email verification, password reset, 5 roles (Super Admin, Admin, Instructor, Finance, Student) with middleware + a CoursePolicy. Admins now have full teaching access.
**Working:** Yes — login, registration→verification, role gating, admin teaching access all verified.
**Limitations:**
- **No MFA/2FA** for Admin/Finance — money-handling and certificate-issuing accounts are password-only.
- Google OAuth depends on the exact redirect URI being registered in Google Cloud Console (was just fixed; the old PHP callback URL was still registered).
- One enrolment per (user, course) — repeating a whole course as a fresh record isn't supported (only re-activating a dropped enrolment).

## 2. Courses & content
**What's there:** Courses → modules → lessons (Reading/Video/Quiz/Assignment), lesson resources, versioning, HTML lesson content (HTMLPurifier-sanitised), a course builder (instructor/admin), templates. 23 courses fully seeded with real Zambian-context content.
**Working:** Yes — content renders, builder works, admins can edit any course.
**Limitations:**
- Video lessons rely on external embeds (YouTube/Vimeo URLs); no native video hosting.
- HTMLPurifier needs a writable cache dir (was a 500 source; now self-heals).

## 3. Enrolment & intakes
**What's there:** Self-enrolment (students), admin "Enrol a Student", intakes (cohorts) with capacity, delivery mode (online/in-person/hybrid), re-enrolment of dropped students, intake lifecycle auto-advance (cron).
**Working:** Yes — counts now self-heal via observer (no more false "full").
**Limitations:**
- **Registration-fee gate (K150)** is a hard prerequisite for student self-enrolment; admin enrolment bypasses it.
- 28 orphaned enrolments exist on production (users deleted, rows remained) — hidden from UI but worth cleaning.

## 4. Assessments & grading
**What's there:** Quizzes (MC/True-False/Short Answer/Essay, timed, attempts, auto-grade), assignments (file/text submission), **offline mark/score entry** for in-person work, weighted final grade (40% quizzes / 60% assignments, best attempt), per-module quiz unlock.
**Working:** Yes — grading verified, weighted total reconciles with the transcript.
**Limitations:**
- Essay questions need manual grading.
- **Grading notifications are unreliable** — see §7; an offline mark recorded today did not produce an email or in-app notification even though the mark itself saved correctly.

## 5. Certificates & transcripts
**What's there:** Auto-issue at 100% (online) or instructor/admin "Mark Complete" (in-person); PDF certificate stamped onto the authentic artwork (FPDI); verification page; transcript PDF listing every assessment + weighted total.
**Working:** Yes — both paths converge on one CertificateService; transcript reconciles.
**Limitations:**
- Certificate fonts are embedded from Office/macOS licences (fine for the institution's own docs).
- Student NRC on the certificate comes from their profile; blank if not filled in.

## 6. Payments
**What's there:** **Lenco** (Zambian gateway) — mobile money (Airtel/MTN) + bank transfer, webhook + 10-min polling fallback, invoices, finance verification, manual/cash payment recording.
**Working:** Mobile money + bank transfer via Lenco; finance dashboard.
**Limitations:**
- **Lenco-only**; card payments aren't wired into checkout. No Stripe/PayPal.
- Webhook signature secret needs confirming (HMAC key source).
- ZMW-only.

## 7. Email & notifications  ⚠️ biggest scaling ceiling
**What's there:** Custom `email_queue` table + **PHPMailer over Gmail SMTP** (`smtp.gmail.com`, `edutrackzambia@gmail.com`), processed by `queue:work --stop-when-empty` **every 5 minutes**; in-app notifications table; templated Blade emails.
**Working:** Email delivers when SMTP auth is valid (verified — preview emails sent).
**Limitations (important):**
- **Gmail daily send cap.** A **free @gmail.com account sends ~500 emails/day** over SMTP (Google Workspace paid is ~2,000/day) — and Google throttles aggressively, temporarily blocking the account if you burst. As students + notifications grow, this **will** hit the ceiling. (The "100/day" figure applies to some Gmail API/relay contexts; the practical SMTP ceiling for free Gmail is ~500 recipients/day.) **This is not a transactional-email setup** — for scale you want SendGrid / Amazon SES / Mailgun / Postmark (thousands/day, proper deliverability, SPF/DKIM).
- **Up to 5-minute delay** on every email (queue runs every 5 min) — verification links, receipts, grade notifications are not instant.
- **Misleading "sent" status:** if SMTP fails, the queue silently falls back to PHP `mail()` (poor deliverability on shared hosting) and still marks the row "sent."
- **App-password fragility:** depends on a Gmail App Password that can be revoked/expire (already caused an outage this week).
- **Grading notifications currently not firing reliably** — a recorded mark didn't queue its email/notification today; needs fixing.
- Everything depends on **Hostinger cron** invoking `schedule:run` every minute; if cron stops, no emails, no payment polling, no intake lifecycle.

## 8. Live sessions  ⚠️ depends on a free public service
**What's there:** Sessions scheduled per lesson/module; the student join page **embeds Jitsi Meet from the public `meet.jit.si` instance** (`external_api.js`, room = `meeting_room_id`); attendance tracking; reminders.
**Working:** The embed loads and tracks attendance.
**Limitations (important):**
- **Public meet.jit.si — no SLA, no guarantees.** It's free and shared; Jitsi has progressively added **moderator authentication** ("waiting for a moderator" / Google login prompts) on the public instance, which can block your sessions unexpectedly.
- **No server-side recording** under your control (recording fields exist in the schema but the public instance doesn't give you the files).
- **Room-name squatting / weak privacy** — rooms are just names on a public server; anyone with the room name can join unless passwords are enforced (password fields exist but aren't clearly wired).
- For reliability at scale you'd self-host Jitsi (a VPS, not shared hosting) or use a paid provider (Zoom/Daily/Whereby API).

## 9. Other
- **PDFs:** TCPDF + FPDI (certificates), mpdf (invoices/transcripts) — pure PHP, shared-hosting safe.
- **File storage:** local disk on Hostinger; private files (submissions, resources) served through authorised controllers. Limit: shared-hosting disk quota, no CDN, manual backups.
- **Discussions, notes, schedule, achievements/badges, announcements, events, testimonials, newsletter, promotions** — all present and functional.
- **Search:** basic keyword search.
- **No automated tests / CI** — every change is manually verified.

---

## Hard ceilings to plan around (summary)
| Area | Current | Ceiling / risk | When it bites |
|------|---------|----------------|---------------|
| Email | Free Gmail SMTP | ~500 emails/day, throttling, app-password breakage, 5-min delay | Growing student base + notifications |
| Live sessions | Public meet.jit.si | No SLA, moderator-auth prompts, no recording, weak privacy | Any serious class reliance |
| Payments | Lenco only, ZMW | No cards/international | International or card-paying students |
| Hosting | Hostinger shared | Disk quota, single server, cron-dependent, no queue worker daemon | Traffic/storage growth |
| Auth | No MFA | Admin/finance password-only | Security incident |

## Recommended next moves (not yet done)
1. **Move email to a transactional provider** (SES/SendGrid/Mailgun) — removes the Gmail cap, the 5-min delay, and the app-password fragility; fixes deliverability. Highest-leverage.
2. **Fix grade-notification firing** (a recorded mark isn't notifying) and stop the misleading "sent" fallback.
3. **Decide live-session strategy** — self-hosted Jitsi or a paid API — before relying on it for real classes.
4. MFA for admin/finance; clean the 28 orphaned enrolments; add automated tests + CI.
