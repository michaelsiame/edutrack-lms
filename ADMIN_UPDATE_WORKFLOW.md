# Admin Panel Update Workflow

## 🔄 **Proper Workflow for Updates**

### What to Keep Where:

✅ **On Your Computer (Git Repository):**
- Keep ALL source files (.tsx, .ts, package.json, etc.)
- This is your "source of truth"
- Make all changes here

✅ **On Hostinger (Production Server):**
- Only upload BUILT files (dist/ contents)
- Delete source files to save space (optional)
- Keep only what's needed to run

---

## 📝 **When You Need to Update Admin Panel:**

### Step-by-Step:

1. **Make Changes Locally:**
   ```bash
   # On your computer
   cd C:\xampp\htdocs\edutrack-lms\public\admin

   # Edit your .tsx files in VS Code
   # Test with: npm run dev
   ```

2. **Build Updated Version:**
   ```bash
   npm run build
   ```

3. **Upload to Hostinger:**
   - Upload new `dist/` contents
   - Replaces old files

4. **Commit to Git:**
   ```bash
   git add .
   git commit -m "Updated admin panel"
   git push
   ```

---

## 💾 **Recommended Setup:**

### Keep Two Copies:

1. **Development Copy** (Your Computer):
   ```
   C:\xampp\htdocs\edutrack-lms\
   ├── public/admin/
   │   ├── src/          ← Edit here
   │   ├── dist/         ← Build output
   │   └── package.json
   ```

2. **Production Copy** (Hostinger):
   ```
   /public_html/public/admin/
   ├── index.html        ← Built files only
   └── assets/
   ```

---

## 🔧 **Best Practice:**

**Never delete source files from Git!** Only delete from Hostinger to save space.

Your workflow:
```
Edit locally → Build → Upload dist/ → Keep source in Git
```

This way you can always make updates!
