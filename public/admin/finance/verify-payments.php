<?php
/**
 * Verify Course Payments
 * Redirect to existing payments verify page (reuse existing functionality)
 */

require_once '../../../src/middleware/finance-only.php';

// Redirect to existing verify page which handles course payments
redirect(url('admin/payments/verify.php'));
