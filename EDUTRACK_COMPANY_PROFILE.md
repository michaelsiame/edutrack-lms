# Edutrack Computer Training College - Company Profile

## Overview
**Legal Name:** Edutrack Computer Training College  
**Trading Name:** EduTrack Zambia  
**Website:** https://edutrackzambia.com  
**Status:** TEVETA Registered Institution

---

## Contact Information

| Type | Value | Location Used |
|------|-------|---------------|
| **Primary Email** | info@edutrackzambia.com | Header, Footer, Contact Page |
| **Alternate Email** | edutrackcomputertrainingschool@gmail.com | Contact Page |
| **Primary Phone** | +260770666937 | Header, Footer, Contact Page |
| **Secondary Phone** | +260965992967 | Footer, Contact Page |
| **Physical Address** | Kalomo, Zambia | Footer, Contact Page |
| **Country** | Zambia | All pages |

---

## Brand Identity

### Colors
| Color | Hex | Usage |
|-------|-----|-------|
| **Primary Blue** | #2E70DA | Headers, buttons, links |
| **Secondary Gold** | #F6B745 | Accents, highlights, CTAs |
| **Dark Blue** | #1E4A8A | Hover states |
| **Light Blue** | #EBF4FF | Backgrounds |

### Logo & Assets
- Logo file: `public/assets/images/logo.png`
- Favicon: `public/assets/images/favicon.ico`
- WhatsApp button: `public/assets/css/whatsapp-button.css`

---

## TEVETA Registration

| Field | Value | Notes |
|-------|-------|-------|
| **Institution Code** | TEVETA/XXX/2024 | ⚠️ PLACEHOLDER - Needs actual TEVETA code |
| **Institution Name** | Edutrack Computer Training College | As registered |
| **Registration URL** | https://www.teveta.org.zm | TEVETA official website |
| **Verified Status** | false | Pending verification |

---

## Social Media Links

| Platform | URL | Status |
|----------|-----|--------|
| Facebook | (empty) | ⚠️ Not configured |
| Twitter | (empty) | ⚠️ Not configured |
| Instagram | (empty) | ⚠️ Not configured |
| LinkedIn | (empty) | ⚠️ Not configured |
| YouTube | (empty) | ⚠️ Not configured |

---

## Configuration Files

### Primary Config: `config/app.php`
Contains all company settings with environment fallbacks:
```php
'name' => 'Edutrack Computer Training College',
'url' => 'https://edutrackzambia.com',
'site' => [
    'email' => 'info@edutrackzambia.com',
    'phone' => '+260771216339',
    'address' => 'Kalomo, Zambia',
],
```

### Constants: `src/includes/config.php`
Defines global constants used throughout the site:
- `SITE_EMAIL`
- `SITE_ALT_EMAIL`
- `SITE_PHONE`
- `SITE_PHONE2`
- `SITE_ADDRESS`
- `TEVETA_CODE`

---

## Pages Displaying Company Data

### Header (`src/templates/header.php`)
- ✅ TEVETA Registration number (top bar)
- ✅ Phone number (top bar)
- ✅ Email address (top bar)

### Footer (`src/templates/footer.php`)
- ✅ Physical address
- ✅ Phone numbers (primary & secondary)
- ✅ Email address
- ✅ TEVETA registration number
- ✅ Social media links (conditional)

### Homepage (`public/index.php`)
- ✅ "TEVETA Registered Institution" badge
- ✅ Company name in hero section

### About Page (`public/about.php`)
- ✅ Mission statement
- ✅ Vision statement
- ✅ Team members (from database)

### Contact Page (`public/contact.php`)
- ✅ Contact form sends to SITE_EMAIL
- ✅ Address display

---

## Environment Variables (.env)

The following can be overridden via environment variables:

```bash
APP_NAME="Edutrack Computer Training College"
APP_URL="https://edutrackzambia.com"
SITE_EMAIL="info@edutrackzambia.com"
SITE_ALT_EMAIL="edutrackcomputertrainingschool@gmail.com"
SITE_PHONE="+260770666937"
SITE_PHONE2="+260965992967"
SITE_ADDRESS="Kalomo, Zambia"
TEVETA_INSTITUTION_CODE="TEVETA/XXX/2024"
TEVETA_INSTITUTION_NAME="Edutrack Computer Training College"
FACEBOOK_URL=""
TWITTER_URL=""
INSTAGRAM_URL=""
LINKEDIN_URL=""
YOUTUBE_URL=""
```

---

## Action Items

### 🔴 Critical (Update Immediately)
1. **TEVETA Institution Code** - Currently "TEVETA/XXX/2024" (placeholder)
   - File: `config/app.php` line 42
   - Or set in `.env`: `TEVETA_INSTITUTION_CODE`

### 🟡 Important (Update Soon)
2. **Secondary Phone** - Currently empty
   - Add alternate contact number if available

3. **Social Media Links** - All platforms empty
   - Add Facebook page URL
   - Add WhatsApp business link
   - Consider LinkedIn for professional presence

### 🟢 Optional (Nice to Have)
4. **TEVETA Verified Status** - Currently false
   - Update after official verification

5. **Google Analytics ID** - Currently empty
   - Add tracking ID for visitor analytics

---

## WhatsApp Integration

Current WhatsApp button links to:
- **URL:** https://chat.whatsapp.com/HkqCis0yejbJybxyTbsG2e
- **Type:** WhatsApp Group
- **Purpose:** Community group for students

Alternative options:
- WhatsApp Business API for official support
- WhatsApp Click-to-Chat for individual inquiries

---

## Payment Information

### Bank Details (from `config/payment.php`)
**Account Name:** Edutrack computer training college

Payment methods supported:
- Lenco Payment Gateway
- MTN Mobile Money
- Airtel Money
- Manual bank transfer

---

## Email Templates

Company branding used in:
- `src/mail/enrollment-confirm.php`
- `src/mail/payment-received.php`
- `src/mail/certificate-issued.php`
- `src/mail/reset-password.php`
- `src/mail/verify-email.php`
- `src/mail/welcome.php`
- `src/mail/announcement-notification.php`
- `src/mail/password-reset-by-admin.php`

All templates use:
- Company colors (blue/gold)
- SITE_EMAIL for replies
- TEVETA_CODE for registration display

---

## Last Updated
Generated: April 18, 2024
