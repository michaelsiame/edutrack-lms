# Google Drive Integration Setup Guide

This guide will help you set up automatic Google Drive uploads for course resources.

## 📋 Overview

When enabled, instructors can upload files (PDFs, Excel, PowerPoint, etc.) and they'll be **automatically uploaded to Google Drive** instead of your server. The shareable link is stored in the database, saving server storage space.

---

## 🎯 Benefits

✅ **No Server Storage Used** - Files stored on Google Drive, not your server
✅ **Unlimited Storage** - Google Drive provides generous free storage
✅ **Fast Downloads** - Students download directly from Google's CDN
✅ **Automatic Sharing** - Links are automatically made public
✅ **Easy Management** - Manage files in Google Drive dashboard
✅ **Backup** - Files are backed up by Google

---

## 🔧 Setup Instructions

### Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **"Create Project"**
3. Name it: `EduTrack LMS` (or your preferred name)
4. Click **"Create"**

### Step 2: Enable Google Drive API

1. In your project, go to **"APIs & Services" → "Library"**
2. Search for **"Google Drive API"**
3. Click on it and press **"Enable"**

### Step 3: Create Service Account

1. Go to **"APIs & Services" → "Credentials"**
2. Click **"Create Credentials" → "Service Account"**
3. Fill in details:
   - **Service account name:** `edutrack-drive-uploader`
   - **Service account ID:** (auto-generated)
   - **Description:** `Uploads course resources to Google Drive`
4. Click **"Create and Continue"**
5. **Role:** Select `Editor` or `Owner` (for full permissions)
6. Click **"Continue"** then **"Done"**

### Step 4: Create Service Account Key

1. Click on the service account you just created
2. Go to **"Keys"** tab
3. Click **"Add Key" → "Create new key"**
4. Choose **JSON** format
5. Click **"Create"**
6. A JSON file will download automatically - **KEEP THIS SAFE!**

### Step 5: Set Up Google Drive Folder (Optional)

1. Go to [Google Drive](https://drive.google.com/)
2. Create a new folder: `EduTrack Course Materials`
3. Right-click the folder → **"Share"**
4. Add the service account email (found in the JSON file as `client_email`)
   - Example: `edutrack-drive-uploader@your-project.iam.gserviceaccount.com`
5. Give it **"Editor"** permissions
6. Copy the **folder ID** from the URL:
   - URL: `https://drive.google.com/drive/folders/1abc123XYZ456`
   - Folder ID: `1abc123XYZ456`

---

## 📁 Installation

### 1. Install Google API PHP Client

```bash
cd /home/user/edutrack-lms
composer require google/apiclient
```

Or if composer isn't working:

```bash
composer install
```

### 2. Upload Credentials File

1. Rename the downloaded JSON file to: `google-credentials.json`
2. Upload it to: `/home/user/edutrack-lms/config/google-credentials.json`

**Important:** Make sure the file path is correct:
```
/home/user/edutrack-lms/
├── config/
│   └── google-credentials.json  ← Here
└── ...
```

### 3. Update Configuration

Edit `/home/user/edutrack-lms/config/config.php` and add:

```php
// Google Drive Integration
define('GOOGLE_DRIVE_ENABLED', true);
define('GOOGLE_DRIVE_FOLDER_ID', '1abc123XYZ456'); // Optional: Your folder ID from Step 5

// If you don't want to use a specific folder, just set it to null:
// define('GOOGLE_DRIVE_FOLDER_ID', null);
```

### 4. Set File Permissions

```bash
chmod 600 /home/user/edutrack-lms/config/google-credentials.json
```

This ensures only the web server can read the credentials file.

---

## ✅ Verify Setup

Create a test file to check if Google Drive is configured:

**File:** `/home/user/edutrack-lms/test-google-drive.php`

```php
<?php
require_once 'config/config.php';
require_once 'src/classes/Database.php';
require_once 'src/classes/GoogleDriveService.php';

// Check configuration
$check = GoogleDriveService::isConfigured();

if ($check['configured']) {
    echo "✅ Google Drive is properly configured!\n\n";

    // Try to create a test folder
    try {
        $service = new GoogleDriveService();
        $result = $service->createFolder('EduTrack Test Folder');

        if ($result['success']) {
            echo "✅ Successfully created test folder!\n";
            echo "Folder ID: " . $result['folder_id'] . "\n\n";
            echo "Check your Google Drive to see the folder.\n";
        } else {
            echo "❌ Failed to create folder: " . $result['error'] . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ Google Drive is NOT configured\n";
    echo "Error: " . $check['error'] . "\n";
}
```

Run it:
```bash
php test-google-drive.php
```

---

## 🎨 How It Works

### For Instructors:

1. Go to **Instructor Dashboard → My Courses → Manage Content**
2. Click a lesson, then click **"Manage Resources"** (📥 button)
3. Click **"Upload File"**
4. Choose between:
   - **Local Server** - Stores on your server
   - **Google Drive** - Automatically uploads to Google Drive ✨
5. Upload the file

### What Happens Behind the Scenes:

1. File is uploaded to a temp location on your server
2. System uploads it to Google Drive using the API
3. Google Drive returns a shareable link
4. Link is stored in database
5. Temp file is deleted from server
6. Students download directly from Google Drive

### For Students:

- They see "Downloadable Resources" on lesson pages
- Click download button
- File downloads from Google Drive (fast and reliable!)

---

## 🔐 Security Notes

**✅ Service Account Credentials:**
- Never commit `google-credentials.json` to Git
- Keep file permissions restrictive (`chmod 600`)
- Only store in `/config/` directory

**✅ Public Access:**
- Files are automatically made public (anyone with link)
- Perfect for course materials
- If you need private files, modify the code to not share publicly

**✅ Folder Access:**
- Service account has access to the specific folder
- Files are organized under your Google account
- You can manage them in Google Drive dashboard

---

## 📊 Storage Limits

**Google Drive Free Tier:**
- 15 GB free storage
- Shared across Gmail, Drive, and Photos

**If you need more:**
- Google Workspace: 30 GB - 2 TB per user
- Google One: Up to 2 TB personal storage
- Or create multiple service accounts with different Google accounts

---

## 🛠️ Troubleshooting

### Error: "Google Drive credentials file not found"

**Solution:**
- Check file path: `/home/user/edutrack-lms/config/google-credentials.json`
- Make sure filename is exact: `google-credentials.json`
- Verify file permissions: `chmod 600`

### Error: "Permission denied"

**Solution:**
- Make sure service account email is added to the Google Drive folder
- Check that the role is "Editor" or "Owner"
- Wait a few minutes for permissions to propagate

### Error: "Invalid credentials file format"

**Solution:**
- Re-download the JSON key from Google Cloud Console
- Make sure it's the service account key, not OAuth credentials
- Check that the JSON is valid (use `cat google-credentials.json`)

### Files upload but students can't download

**Solution:**
- The file might not be publicly shared
- Check Google Drive sharing settings
- The API should automatically set permissions to "anyone with link"

### Uploads are slow

**Solution:**
- Google Drive uploads can take time for large files
- Consider limiting file sizes
- Use local storage for very large videos (or link to YouTube)

---

## 🔄 Switching Between Local and Google Drive

**After enabling Google Drive, instructors can choose:**

1. **Upload File Tab:**
   - Radio buttons appear: "Local Server" or "Google Drive"
   - Select which storage to use
   - Default: Local Server

2. **Existing Files:**
   - Old files on local server stay there
   - New uploads go to selected storage
   - You can manually migrate files if needed

---

## 📝 Example Configuration

**config/config.php:**
```php
// Google Drive Integration
define('GOOGLE_DRIVE_ENABLED', true);
define('GOOGLE_DRIVE_FOLDER_ID', '1abc123XYZ456');
```

**google-credentials.json:**
```json
{
  "type": "service_account",
  "project_id": "edutrack-lms",
  "private_key_id": "abc123...",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",
  "client_email": "edutrack-drive-uploader@edutrack-lms.iam.gserviceaccount.com",
  "client_id": "123456789",
  ...
}
```

---

## 🎉 You're Done!

Once configured, instructors will see a beautiful storage option selector when uploading files:

```
┌────────────────────────────────────┐
│  Storage Location                  │
├────────────────────────────────────┤
│  [🖥️ Local Server]  [☁️ Google Drive]  │
│                                    │
│  ℹ️ Google Drive: Files are        │
│  automatically uploaded to Google  │
│  Drive and students get a          │
│  shareable link. No server         │
│  storage used!                     │
└────────────────────────────────────┘
```

Files upload automatically to Google Drive and students download directly from Google's servers!

---

## 📚 Additional Resources

- [Google Drive API Documentation](https://developers.google.com/drive/api/guides/about-sdk)
- [Service Account Guide](https://cloud.google.com/iam/docs/service-accounts)
- [PHP Client Library](https://github.com/googleapis/google-api-php-client)

---

## 🆘 Need Help?

If you encounter issues:

1. Check the troubleshooting section above
2. Verify all setup steps were followed
3. Check server error logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
4. Run the test script: `php test-google-drive.php`
5. Check Google Cloud Console for API errors

---

**Happy uploading! 🚀**
