<?php
/**
 * Verify Course Payments - Finance Module Entry Point
 *
 * This file acts as a finance-specific entry point for payment verification.
 * It redirects to the main payment verification page (admin/payments/verify.php)
 * to avoid code duplication while maintaining a clear navigation structure
 * for finance staff within their module.
 *
 * Why this exists:
 * - Finance staff access verification through admin/finance/ navigation
 * - Admin staff access through admin/payments/ navigation
 * - Both lead to the same underlying functionality
 * - This prevents maintaining duplicate code
 *
 * Access Control:
 * - This route: finance staff + admins (via finance-only middleware)
 * - Direct route: admin staff only (via admin-only middleware)
 *
 * @see admin/payments/verify.php - Main payment verification implementation
 */

require_once '../../../src/middleware/finance-only.php';

// Redirect to main verify page which handles all course payment verification
redirect(url('admin/payments/verify.php'));
