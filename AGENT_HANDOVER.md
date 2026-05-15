# Edutrack LMS — Agent Handover

**Date:** 2026-05-14  
**Branch:** `main` (custom PHP, NOT Laravel)  
**Last Agent Work:** Admin Dashboard Revamp + Certificate System + Bug Fixes

---

## 1. What We Were Building

### Primary Goal
Revamp the admin dashboard and fix production errors on the custom PHP LMS running at `https://edutrackzambia.com`.

### Secondary Goals
- Redesign the certificate PDF to match a provided sample design
- Compress image assets for faster loading
- Fix SQL errors appearing in production logs

---

## 2. What Got Completed

### ✅ Admin Dashboard Revamp (COMPLETE)
**Location:** `public/admin/`

Created **21 new admin pages** in `public/admin/pages/`:
- People: `instructors.php`, `students.php`
- Academics: `quizzes.php`, `assignments.php`, `certificates.php`, `badges.php`, `live-sessions.php`
- Content: `events.php`, `hero-slides.php`, `institution-photos.php`, `email-templates.php`
- Community: `discussions.php`, `reviews.php`, `contacts.php`
- Finance: `payment-methods.php`, `registration-fees.php`
- Communication: `email-queue.php`, `notifications.php`, `newsletter-subscribers.php`
- System: `reports.php`, `activity-logs.php`

Updated existing pages:
- `public/admin/index.php` — Added all pages to `$validPages`, expanded sidebar with 8 nav groups, added dashboard stats
- `settings.php` — Expanded to 25+ settings keys across 8 sections
- `announcements.php` — Added course targeting, modal form
- `enrollments.php` — Pagination, filters, progress bars
- `company-profile.php` — Consistent styling

### ✅ Production Bug Fixes (COMPLETE)

| Issue | Fix | File(s) |
|-------|-----|---------|
| Missing `testimonials` table | Added table to schema + migration | `database/complete_lms_schema.sql`, `migrations/005_add_missing_tables.sql` |
| Missing `rate_limits` table | Added table to schema + migration | Same as above |
| `l.course_id` column not found | Fixed JOIN to go through `modules` table | `public/admin/index.php`, `public/admin/pages/live-sessions.php` |
| `$registrationPaid` undefined | Added `RegistrationFee::hasPaid()` call | `public/dashboard.php` |
| MySQL "gone away" spam | Added persistent connections + reduced log noise | `config/database.php`, `src/includes/database.php` |

### ✅ Image Compression (COMPLETE)
Compressed PNG logos using PHP GD:
- `logo.png`: 914 KB → 40 KB (95.6% saved)
- `teveta-logo.png`: 2.1 MB → 31 KB (98.5% saved)
- JPG hero images were already optimized (~100-280 KB each)

Script kept at: `scripts/compress-images.php`

---

## 3. What Is Partially Done / Has Issues

### ⚠️ Certificate PDF Redesign (IN PROGRESS — NEEDS ATTENTION)

**Current State:**
- Switched from **TCPDF** to **Dompdf** via Composer (`composer require dompdf/dompdf`)
- Dompdf is installed and generates PDFs successfully (~60KB, renders in <1 second)
- All "Download Certificate" buttons across the site now include `&action=download` and trigger direct PDF download
- Certificate generates **on-demand** (not pre-saved to disk) per user request

**Files Involved:**
- `src/templates/certificate-dompdf.php` — Dompdf-optimized HTML template
- `src/templates/certificate-pdf.php` — Old TCPDF template (kept as fallback)
- `src/classes/Certificate.php` — Auto-detects Dompdf → TCPDF fallback
- `public/download-certificate.php` — Streams generated PDF bytes directly

**Current Visual Issue:**
The certificate generates but the **styling doesn't match the user's reference design** exactly. Specifically:
- White space at the bottom of the portrait A4 page
- Some CSS features don't render as expected in Dompdf (corner decorations, flexbox, absolute positioning quirks)

**What Was Tried:**
1. TCPDF template with complex nested tables — caused 504 timeouts
2. TCPDF with simplified template — worked but `???` characters appeared (Unicode diamonds not supported by core PDF fonts)
3. Switched to Dompdf for better CSS support
4. Rewrote Dompdf template 4 times with different approaches (absolute positioning, table-based wrappers, float-based layout)

**What's Needed:**
The user has a specific reference image they want matched. Dompdf supports:
- ✅ Tables (very reliable)
- ✅ Basic borders and colors
- ✅ Absolute positioning (moderately reliable)
- ✅ Standard fonts (DejaVu Sans, serif, helvetica)
- ❌ Flexbox (partial/poor)
- ❌ `clip-path` (not supported)
- ❌ `inset` CSS property
- ❌ Complex `transform` or `rotate`
- ❌ Many Unicode symbols without DejaVu font

**Recommended Next Steps:**
1. Ask the user for **specific feedback** on what's wrong (font size? spacing? alignment? missing elements?)
2. Or switch to a **simpler, classic certificate design** that Dompdf can render reliably
3. Or generate a **static HTML preview page** instead of PDF, letting the user print from browser

### ⚠️ Laravel Folder Deletion (COMPLETE LOCALLY)

The `laravel/` folder was deleted from the repo. It existed as a separate Laravel migration experiment and is **NOT needed** for production. The live site runs the custom PHP app in `public/`.

---

## 4. Architecture & Key Decisions

### Framework
- **Custom PHP** (not Laravel/Symfony)
- PHP 8.0+ required
- Composer for dependencies (Dompdf, PHPMailer, TCPDF, Google API)

### Database
- MySQL/MariaDB on Hostinger shared hosting
- 56+ tables
- Singleton `Database` class in `src/includes/database.php`

### Certificate System Flow
```
User clicks "Download Certificate"
  → download-certificate.php?id=X&action=download
    → Certificate::generatePDF()
      → Detects Dompdf (or falls back to TCPDF)
      → Builds HTML from template with placeholders replaced
      → Renders to PDF binary string
    → Sends PDF headers + echoes content
```

### Admin Router
`public/admin/index.php` acts as the main router:
- Reads `?page=` parameter
- Validates against `$validPages` array
- Includes the matching file from `public/admin/pages/`

### New Placeholders in Certificate
The Certificate class now provides these placeholders:
- `{{student_name}}`, `{{course_title}}`, `{{completion_date}}`, `{{formal_date}}`
- `{{certificate_number}}`, `{{verify_url}}`, `{{student_number}}`
- `{{merit_text}}`, `{{graduate_id}}`, `{{principal_name}}`
- `{{director_name}}`, `{{instructor_name}}`, `{{director_signature}}`, etc.

### Styling Approach
- Tailwind CSS for frontend
- Inline CSS for PDF templates (TCPDF/Dompdf limitation)

---

## 5. Files Changed in This Session

### New Files
- `public/admin/pages/*` (21 new pages)
- `src/templates/certificate-dompdf.php`
- `migrations/005_add_missing_tables.sql`
- `scripts/compress-images.php`

### Modified Files
- `public/admin/index.php`
- `public/admin/pages/settings.php`
- `public/admin/pages/announcements.php`
- `public/admin/pages/enrollments.php`
- `public/admin/pages/company-profile.php`
- `public/dashboard.php`
- `public/download-certificate.php`
- `public/my-certificates.php`
- `public/student/achievements.php`
- `src/classes/Certificate.php`
- `src/templates/certificate-pdf.php`
- `src/includes/database.php`
- `config/database.php`
- `database/complete_lms_schema.sql`
- `dbschema.sql`
- `composer.json` / `composer.lock` (added Dompdf)

### Deleted
- `laravel/` directory (entire Laravel experiment removed)

---

## 6. How to Test Certificate Generation Locally

```php
// Quick test script
require_once 'vendor/autoload.php';
require_once 'src/bootstrap.php';
require_once 'src/classes/Certificate.php';

$cert = Certificate::find(18); // or any valid certificate_id
$pdf = $cert->generatePDF();
if ($pdf) {
    file_put_contents('test.pdf', $pdf);
    echo "Generated: " . strlen($pdf) . " bytes\n";
}
```

For HTML preview without PDF:
```php
$html = $cert->getDebugHtml();
echo $html; // View in browser
```

---

## 7. Production Deployment Checklist

When deploying to Hostinger (`u605780771`):

1. **Upload changed PHP files**
2. **Upload `vendor/` folder** (contains Dompdf + dependencies — run `composer install --no-dev` locally then upload)
3. **Run migration** if needed: `mysql -u u605780771_root -p u605780771_edutrack_lms < migrations/005_add_missing_tables.sql`
4. **Verify `.htaccess`** in `public/` has `php_value max_execution_time 300`
5. **Delete** `public/install.php` if it exists
6. **Check** `storage/` and `public/uploads/` are writable (755)

---

## 8. Contact & Context

- **Organization:** Edutrack Computer Training College
- **Location:** Kalomo, Zambia
- **Email:** edutrackzambia@gmail.com
- **Phone:** +260 770 666 937
- **Currency:** ZMW (Zambian Kwacha)
- **Timezone:** Africa/Lusaka (CAT, UTC+2)
- **TEVETA Code:** TVA/2064 (on certificate PDFs only)

---

## 9. Notes for Next Agent

1. **Certificate styling is the #1 open issue.** The user has a specific reference image. Dompdf is installed and working, but the CSS needs tuning to match the design. Consider asking the user to mark up the screenshot with exact changes needed.

2. **If Dompdf continues to be problematic**, the fallback is TCPDF (still installed via `tecnickcom/tcpdf`). The old template is at `src/templates/certificate-pdf.php`. TCPDF is reliable but has limited CSS — best for simple, text-heavy certificates.

3. **No automated tests** exist. Test manually by walking through user flows.

4. **The `laravel-migration` branch** contains a separate Laravel experiment. Do NOT merge it to `main`. It is preserved for future reference only.

5. **Database schema** is the source of truth: `database/complete_lms_schema.sql`
