# Content Review — Round 2 (April 2026)

**Scope:** Re-audit of user-facing content after the first round of improvements (documented in `CONTENT_REVIEW_AND_IMPROVEMENTS.md` and `CONTENT_IMPROVEMENTS_IMPLEMENTED.md`). This document lists **what is still broken, placeholder, or weak** — not feature requests.

**Read order:** Fix P0 first (these show to real users today), then P1, then P2.

---

## P0 — Broken or Embarrassing (ship-blocking)

### 1. Footer links to legal pages that don't exist
- **Where:** `src/templates/footer.php:204,206`
  ```php
  <a href="<?= url('privacy.php') ?>">Privacy Policy</a>
  <a href="<?= url('terms.php') ?>">Terms of Service</a>
  ```
- **Problem:** Both `public/privacy.php` and `public/terms.php` are **missing from the repo**. Every page footer links to two 404s. This is a compliance and trust issue (especially under Zambia's Data Protection Act, 2021).
- **Fix:** Create both pages with real content. Minimum viable:
  - `privacy.php` — What data is collected (name, email, NRC, payment info), purpose, third parties (Lenco, Google OAuth), retention, user rights, contact for data requests.
  - `terms.php` — Enrolment terms, fee/refund policy (align with FAQ answer), intellectual property, code of conduct, termination, governing law (Zambia).

### 2. Dummy JS `alert()` shown to users
- **Where:** `public/campus.php:361`
  ```js
  alert('This would load more photos via AJAX in production');
  ```
- **Problem:** Clicking "Load More Photos" on the live site pops a developer debug alert. Users will report this as "site is broken".
- **Fix:** Either (a) wire the button to `InstitutionPhoto::getAll()` with an offset/limit AJAX endpoint, or (b) remove the button entirely when photo count ≤ shown count.

### 3. "Coming Soon" placeholders still live on published pages
| File | Line | Text |
|---|---|---|
| `public/testimonials.php` | 83, 106 | Entire "Featured Video Section" showing *"Video coming soon"* |
| `public/campus.php` | 287 | *"Interactive Map Coming Soon"* (a grey box where a map should be) |
| `public/campus.php` | 169 | *"Photos Coming Soon"* — acceptable as an empty state, but only if the DB is genuinely empty |

- **Fix options:**
  - Testimonials video → remove the placeholder section until a real video exists. A blank promise erodes trust.
  - Map → embed a Google Maps iframe (`<iframe src="https://www.google.com/maps/embed?...">`) pointing at the Kalomo campus. Takes 5 minutes.

### 4. Broken/duplicated page title casing
- **Where:** `public/index.php:10`
  ```php
  $page_title = "Home - Edutrack computer training college";
  ```
- **Problem:** "computer training college" is lowercase. All other page titles use title case. Browser tab and OG preview look amateurish.
- **Fix:** `"Edutrack Computer Training College | TEVETA-Certified Tech Training in Zambia"` (and drop the "Home - " prefix on the root page — redundant).

### 5. Static OG image for every share
- **Where:** `src/templates/header.php:35`
  ```html
  <meta property="og:image" content="<?= asset('images/logo.png') ?>">
  ```
- **Problem:** Every Facebook/WhatsApp share of any page — course pages, events, testimonials — uses the plain logo. Course and event pages should share their own hero image.
- **Fix:** Let pages override via `$og_image` variable, and fall back to a branded default (1200×630px, not the logo square).

---

## P1 — Inconsistent or Misleading (fix within sprint)

### 6. Hardcoded statistics duplicated across 5+ files
The same numbers appear embedded in HTML in multiple places with no single source of truth:

| Stat | Files |
|---|---|
| `5,000+` graduates | `index.php:22,216`, `testimonials-section.php`, `faq.php` |
| `85%` placement | `index.php:41,223`, `course.php:445`, `testimonials.php:72`, `faq.php:92,96` |
| `4.8/5` rating | `index.php:230`, `testimonials-section.php` |
| `50+` partners | `campus.php:64`, `course.php:453` |

- **Problem:** When marketing updates one, the others drift. Three of these numbers are claims that need to be *defensible* (regulator, media) — so every page must agree.
- **Fix:** Move to `config/app.php` as `STATS_GRADUATES`, `STATS_PLACEMENT_RATE`, etc., and reference from a single `src/includes/stats.php` helper. OR pull from a `site_stats` DB table editable from admin.

### 7. Course count hardcoded as "12+"
- **Where:** `public/courses.php` hero ("Explore 12+ TEVETA-certified courses...")
- **Problem:** Number drifts as admins add/archive courses.
- **Fix:** `<?= count($publishedCourses) ?>+` or just drop the number ("Explore our catalog of TEVETA-certified courses").

### 8. Hero fallback text duplicates DB-editable content
- **Where:** `public/index.php:16-50`
- **Problem:** Hero slides can be edited through `HeroSlide::getActive()`, but when the DB table is empty there are **3 hardcoded fallback slides** in PHP. Any copy edit has to happen in two places.
- **Fix:** Seed the `hero_slides` table via migration and remove the PHP fallback. If the table is empty, render a single static "Explore courses" CTA — not a fake carousel.

### 9. Contact info has no clear primary/secondary
- **Where:** `public/contact.php`, `public/faq.php`, `public/campus.php`
- **Problem:** `SITE_PHONE`, `SITE_PHONE2`, `SITE_EMAIL`, `SITE_ALT_EMAIL` are all rendered as a list with no label. Users don't know which to call first.
- **Fix:** Label them:
  ```
  Admissions:  +260 97X XXX XXX      admissions@edutrackzambia.com
  Support:     +260 96X XXX XXX      support@edutrackzambia.com
  ```

### 10. Generic career-outcome copy on every course
- **Where:** `public/course.php:440-470` (Career tab)
- **Problem:** The "job titles" and "skills gained" list is the same boilerplate for every course — "IT Support Specialist", "Network Administrator" regardless of whether the course is Web Development or Digital Marketing.
- **Fix:** Add two new fields to the `courses` table:
  - `career_outcomes` (TEXT) — JSON array of job titles
  - `key_skills` (TEXT) — JSON array of skills
  Then render per-course instead of a fixed list.

### 11. Newsletter signup has no handler / confirmation
- **Where:** `public/events.php:269-275`
- **Problem:** The form submits but there is no visible POST handler and no success UX. Submitting likely produces a blank page or silent failure.
- **Fix:** Either wire it to a `newsletter_subscribers` table with a success flash message, or remove the form until mailing infrastructure is ready.

### 12. Social links fall back to `#` placeholders
- **Where:** `public/contact.php:204` ("Static Social Placeholders if config is missing")
- **Problem:** If config values are empty, the site renders `href="#"` — clicking does nothing. The site should hide the whole icon instead.
- **Fix:** `<?php if (!empty(SITE_FACEBOOK)): ?>...<?php endif; ?>` pattern for each platform.

---

## P2 — Polish (fix when convenient)

### 13. Email templates use emoji and hardcoded colour
- **Where:** `src/mail/welcome.php:30` uses `👋` in a plain `<h2>`. Not all mail clients render emoji in headings (Outlook 2016 shows a box).
- **Fix:** Drop the emoji from headings; keep them in body text where they degrade gracefully.

### 14. Welcome email promises features the user hasn't enabled yet
- **Where:** `src/mail/welcome.php:70-75` ("Complete your profile to get personalized recommendations")
- **Problem:** There are no personalised recommendations on the dashboard. Don't promise what the product doesn't do.
- **Fix:** Change to: "Enrol in your first course", "Set up your profile", "Join our student community on WhatsApp".

### 15. Testimonial consent is not captured or displayed
- **Where:** `public/testimonials.php`, `src/templates/testimonials-section.php`
- **Problem:** Real names, photos, and current employers are shown. There's no visible "used with permission" note, and no record of consent in the DB.
- **Fix:** Add `consent_given_at` timestamp to `testimonials` table; add small "Shared with permission" caption under each testimonial.

### 16. Form error UX is an alert box above the form
- **Where:** `public/contact.php:240-246`, `register.php`, `login.php`
- **Problem:** Errors render in a red panel at the top, not next to the field that failed. On mobile the user has to scroll up, fix, scroll down.
- **Fix:** Render errors inline under each field; keep the summary panel only for non-field errors (CSRF, rate limit).

### 17. Register page has no password strength feedback
- **Where:** `public/register.php` password field
- **Problem:** Policy is enforced server-side with an error after submit. Users get frustrated typing a weak password three times.
- **Fix:** Add a simple client-side meter (weak/fair/strong) with the rules visible: "8+ characters, one number, one letter".

### 18. CTA wording inconsistent across the site
- `index.php`: "Explore Courses", "Apply Now", "Take a Tour"
- `courses.php`: "View Details"
- `course.php`: "Enrol Now" (or "Enroll"? — check spelling — Zambian English is usually "Enrol")
- `faq.php`: "Contact Admissions"

- **Fix:** Pick one enrolment verb — **"Enrol"** (British/Zambian) — and use it consistently. Audit all buttons.

### 19. Empty states are missing on several listings
- **Where:** `courses.php` category filter with no matches, `events.php` when no events, student `my-courses.php` when unenrolled.
- **Problem:** A blank grid with no message confuses users — is it loading? broken? Do they have access?
- **Fix:** Each listing needs an empty state with:
  - A short explanation ("No events scheduled right now.")
  - A next action ("Browse courses" / "Follow us on Facebook for updates")

### 20. Search has no "no results" messaging
- **Where:** `public/search.php`
- **Problem:** A zero-result search returns an empty grid.
- **Fix:** "No courses match **"{query}"**. Try a broader search or browse all courses."

### 21. Meta description is the same for every page
- **Where:** `src/templates/header.php:26` — one global string regardless of page.
- **Problem:** Google shows the same snippet for every result from the site; course-level and event-level pages lose their CTR potential.
- **Fix:** Allow pages to set `$page_description` before including the header, falling back to the global string.

### 22. Name/brand capitalisation is mixed
- Header logo: "Edutrack"
- Some body copy: "EduTrack"
- Some email subject lines: "edutrack"
- **Fix:** Canonical form is **Edutrack** (one word, capital E). Grep and normalise.

---

## Quick Wins (< 30 min each)

These are trivial content edits — no DB changes, no new pages:

1. **Homepage hero subtitle** — change *"85% Job Placement Rate"* → *"85% of graduates hired within 6 months"* (specificity builds trust).
2. **Contact success message** — after form submission change *"We'll get back to you within 24 hours"* → *"We typically reply within 4 business hours (Mon–Fri, 08:00–17:00 CAT). Check your inbox — including spam."*
3. **FAQ contact section** — add the `**Admissions:**` / `**Support:**` labels (Finding #9 above).
4. **Campus page** — replace "Interactive Map Coming Soon" with a Google Maps iframe.
5. **Courses hero** — remove hardcoded "12+".
6. **Welcome email** — remove the `👋` from the `<h2>`.
7. **`index.php` page title** — fix casing (Finding #4).

---

## What's Already Good — Don't Touch

The previous review round landed solid wins; do not regress these:

- Hero copy now outcome-focused ("Launch Your Tech Career…")
- TEVETA registration code (TVA/2064) is consistent across FAQ, footer, about
- Currency formatting is consistent (ZMW)
- Course card info architecture is clean
- Navigation includes Events, FAQ, Campus, Testimonials
- Email templates follow consistent visual design system
- Kalomo location is consistently referenced
- Mobile navigation is present and labelled

---

## Owner Checklist

- [ ] Create `privacy.php` and `terms.php` (P0 #1)
- [ ] Remove/fix `alert()` on campus.php (P0 #2)
- [ ] Replace "Coming Soon" placeholders on testimonials and campus (P0 #3)
- [ ] Fix homepage `$page_title` casing (P0 #4)
- [ ] Add per-page `$og_image` override (P0 #5)
- [ ] Centralise hardcoded stats into config or DB (P1 #6)
- [ ] Make courses count dynamic (P1 #7)
- [ ] Seed `hero_slides` table and remove PHP fallback (P1 #8)
- [ ] Label primary vs secondary contacts (P1 #9)
- [ ] Add `career_outcomes` + `key_skills` to courses table (P1 #10)
- [ ] Wire or remove newsletter form on events.php (P1 #11)
- [ ] Conditional-render social icons (P1 #12)
- [ ] Normalise "Edutrack" capitalisation site-wide (P2 #22)

---

*Prepared: 18 April 2026. Supersedes the P0/P1 sections of `CONTENT_REVIEW_AND_IMPROVEMENTS.md` — the broader roadmap there (blog, course finder quiz, exit-intent popup) still stands as the strategic plan.*
