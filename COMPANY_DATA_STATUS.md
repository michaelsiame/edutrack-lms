# Edutrack Company Data Status Dashboard

## ✅ Properly Configured

| Data Point | Current Value | Status |
|------------|---------------|--------|
| **Company Name** | Edutrack Computer Training College | ✅ Active |
| **Trading Name** | EduTrack Zambia | ✅ Active |
| **Website URL** | https://edutrackzambia.com | ✅ Active |
| **Primary Email** | info@edutrackzambia.com | ✅ Active |
| **Alternate Email** | edutrackcomputertrainingschool@gmail.com | ✅ Active |
| **Primary Phone** | +260770666937 | ✅ Active |
| **Secondary Phone** | +260965992967 | ✅ Active |
| **Physical Address** | Kalomo, Zambia | ✅ Active |
| **Country** | Zambia | ✅ Active |
| **Primary Color** | #2E70DA (Blue) | ✅ Active |
| **Secondary Color** | #F6B745 (Gold) | ✅ Active |
| **WhatsApp Group** | https://chat.whatsapp.com/HkqCis0yejbJybxyTbsG2e | ✅ Active |
| **Payment Account** | Edutrack computer training college | ✅ Active |
| **Jitsi Room Prefix** | edutrack_zm | ✅ Active |

---

## ⚠️ Needs Update

| Data Point | Current Value | Recommended Action |
|------------|---------------|-------------------|
| **TEVETA Code** | `TEVETA/XXX/2024` | 🔴 **PLACEHOLDER** - Replace with actual TEVETA registration number |
| **Secondary Phone** | +260965992967 | ✅ Active |
| **Facebook URL** | *(empty)* | 🟡 Add Facebook page link |
| **Twitter URL** | *(empty)* | 🟢 Optional |
| **Instagram URL** | *(empty)* | 🟢 Optional |
| **LinkedIn URL** | *(empty)* | 🟡 Recommended for professional presence |
| **YouTube URL** | *(empty)* | 🟢 Optional |
| **Google Analytics** | *(empty)* | 🟡 Add for visitor tracking |
| **TEVETA Verified** | `false` | 🟢 Update after official verification |

---

## 📍 Where Data is Displayed

### Header (Top Bar)
```
✅ TEVETA Registered: TEVETA/XXX/2024
✅ Phone: +260771216339
✅ Email: info@edutrackzambia.com
```

### Footer
```
✅ Address: Kalomo, Zambia
✅ Phone 1: +260771216339
⚠️  Phone 2: (hidden - not set)
✅ Email: info@edutrackzambia.com
✅ TEVETA: TEVETA/XXX/2024
⚠️  Social Icons: (hidden - not configured)
```

### Homepage Hero
```
✅ "TEVETA Registered Institution" badge
✅ "Edutrack computer training college" heading
```

### Contact Page
```
✅ Form sends to: info@edutrackzambia.com
✅ Address displayed
```

### Email Templates
```
✅ All use info@edutrackzambia.com
⚠️  All show TEVETA/XXX/2024
```

---

## 🔧 How to Update

### Option 1: Environment Variables (.env) - RECOMMENDED
Add to `.env` file:
```bash
# Critical Update
TEVETA_INSTITUTION_CODE="TEVETA/ACTUAL/CODE/2024"

# Optional Updates
SITE_PHONE2="+260XXXXXXXX"
FACEBOOK_URL="https://facebook.com/edutrackzambia"
LINKEDIN_URL="https://linkedin.com/company/edutrack"
GOOGLE_ANALYTICS_ID="G-XXXXXXXXXX"
```

### Option 2: Config File
Edit `config/app.php`:
```php
'teveta' => [
    'institution_code' => getenv('TEVETA_INSTITUTION_CODE') ?: 'TEVETA/ACTUAL/CODE/2024',
],
'site' => [
    'phone2' => getenv('SITE_PHONE2') ?: '+260XXXXXXXX',
],
'social' => [
    'facebook' => getenv('FACEBOOK_URL') ?: 'https://facebook.com/edutrackzambia',
    'linkedin' => getenv('LINKEDIN_URL') ?: 'https://linkedin.com/company/edutrack',
],
```

---

## 📊 Quick Stats

| Category | Configured | Missing | Percentage |
|----------|------------|---------|------------|
| **Critical Data** | 12/12 | 0 | 100% |
| **Social Media** | 0/5 | 5 | 0% |
| **Optional Data** | 4/5 | 1 | 80% |
| **Overall** | 16/22 | 6 | 73% |

---

## 🎯 Priority Actions

### 🔴 CRITICAL (Do First)
1. **Get actual TEVETA registration code** and update:
   - `.env` file: `TEVETA_INSTITUTION_CODE`
   - Or `config/app.php` line 42

### 🟡 HIGH (Do This Week)
2. **Set up Facebook page** and add URL
3. **Add secondary phone** if available
4. **Set up Google Analytics** for tracking

### 🟢 LOW (When Convenient)
5. Create LinkedIn company page
6. Set up Instagram account
7. Create YouTube channel for course previews
8. Apply for TEVETA verified status

---

## 📞 Current Contact Methods

| Method | Status | Link/Number |
|--------|--------|-------------|
| Phone Call 1 | ✅ Active | +260770666937 |
| Phone Call 2 | ✅ Active | +260965992967 |
| Email 1 | ✅ Active | info@edutrackzambia.com |
| Email 2 | ✅ Active | edutrackcomputertrainingschool@gmail.com |
| WhatsApp Group | ✅ Active | Join community group |
| Facebook | ❌ Not set | - |
| Physical Address | ✅ Active | Kalomo, Zambia |

---

*Last updated: April 18, 2024*
