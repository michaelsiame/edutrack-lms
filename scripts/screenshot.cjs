#!/usr/bin/env node
/**
 * Mobile-screenshot helper for Edutrack LMS.
 *
 * Usage:
 *   node scripts/screenshot.js [name]
 *
 * Env:
 *   BASE_URL  - app root (default: http://127.0.0.1:8000)
 *   EMAIL     - user to dev-login as   (default: testuser@edutrack.edu)
 *   OUT_DIR   - where to save PNGs     (default: screenshots)
 */

const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

const baseUrl = (process.env.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const email = process.env.EMAIL || 'testuser@edutrack.edu';
const outDir = process.env.OUT_DIR || path.join(__dirname, '..', 'screenshots');

if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
}

async function capture(page, name, url, viewport = { width: 375, height: 812 }) {
    await page.setViewport(viewport);
    await page.goto(`${baseUrl}${url}`, { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 500));
    const file = path.join(outDir, `${name}.png`);
    await page.screenshot({ path: file, fullPage: true });
    console.log(`📸 ${file}`);
}

(async () => {
    const browser = await puppeteer.launch({ headless: 'new' });
    const page = await browser.newPage();

    // Dev login
    await page.goto(`${baseUrl}/dev-login?email=${encodeURIComponent(email)}`, {
        waitUntil: 'networkidle2',
    });

    // Capture the pages mentioned in the bug report
    await capture(page, 'notes-mobile', '/student/notes');
    await capture(page, 'dashboard-mobile', '/student/dashboard');

    // Capture an accessible lesson page (course 1, lesson 29 for the default test user)
    await capture(page, 'learning-mobile', '/student/courses/1/lessons/29');

    await browser.close();
    console.log('Done.');
})().catch(err => {
    console.error(err);
    process.exit(1);
});
