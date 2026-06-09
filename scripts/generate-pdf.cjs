#!/usr/bin/env node

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

(async () => {
    const inputFile = process.argv[2];
    const outputFile = process.argv[3];

    if (!inputFile || !outputFile) {
        console.error('Usage: node generate-pdf.js <input-html> <output-pdf>');
        process.exit(1);
    }

    if (!fs.existsSync(inputFile)) {
        console.error('Input file not found:', inputFile);
        process.exit(1);
    }

    const browser = await puppeteer.launch({
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    });

    try {
        const page = await browser.newPage();

        const fileUrl = 'file://' + path.resolve(inputFile);
        await page.goto(fileUrl, { waitUntil: 'networkidle0' });

        // Wait for fonts to load
        await page.evaluateHandle('document.fonts.ready');

        // Apply print media styles
        await page.emulateMediaType('print');

        await page.pdf({
            path: outputFile,
            width: '210mm',
            height: '297mm',
            printBackground: true,
            preferCSSPageSize: true,
        });

        console.log('PDF generated:', outputFile);
    } catch (err) {
        console.error('PDF generation failed:', err.message);
        process.exit(1);
    } finally {
        await browser.close();
    }
})();
