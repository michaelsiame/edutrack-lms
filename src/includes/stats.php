<?php
/**
 * Site Statistics
 * Centralized statistics used across the site
 * Edit these values to update all references site-wide
 */

// Student & Graduate Statistics
define('STATS_GRADUATES_TOTAL', '5,000+');
define('STATS_GRADUATES_NUMBER', 5000);
define('STATS_PLACEMENT_RATE', '85%');
define('STATS_PLACEMENT_NUMBER', 85);
define('STATS_AVG_RATING', '4.8');
define('STATS_PARTNER_COMPANIES', '50+');
define('STATS_PARTNER_NUMBER', 50);

// Facility Statistics
define('STATS_WORKSTATIONS', '50+');
define('STATS_CLASSROOMS', '8');
define('STATS_LABS', '3');

// Course Statistics (dynamically fetched when possible)
function getPublishedCoursesCount() {
    try {
        $db = Database::getInstance();
        return $db->fetchColumn("SELECT COUNT(*) FROM courses WHERE status = 'published'");
    } catch (Exception $e) {
        return 12; // Fallback
    }
}

// Helper function to get stats with fallback
function getStat($key, $fallback = '') {
    $stats = [
        'graduates' => STATS_GRADUATES_TOTAL,
        'placement_rate' => STATS_PLACEMENT_RATE,
        'avg_rating' => STATS_AVG_RATING,
        'partner_companies' => STATS_PARTNER_COMPANIES,
        'workstations' => STATS_WORKSTATIONS,
        'classrooms' => STATS_CLASSROOMS,
    ];
    
    return $stats[$key] ?? $fallback;
}
