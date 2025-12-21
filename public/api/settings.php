<?php
/**
 * Settings API Endpoint
 * Handles system settings management
 */

require_once '../../src/bootstrap.php';
require_once '../../src/middleware/admin-only.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            // Get all system settings
            $settings = $db->fetchAll("SELECT * FROM system_settings");

            // Convert to key-value structure
            $settingsObj = [
                'general' => [
                    'siteName' => '',
                    'siteEmail' => '',
                    'sitePhone' => '',
                    'siteAddress' => '',
                    'timezone' => 'UTC'
                ],
                'email' => [
                    'smtpHost' => '',
                    'smtpPort' => 587,
                    'smtpUser' => '',
                    'smtpPassword' => '',
                    'fromEmail' => '',
                    'fromName' => ''
                ],
                'payments' => [
                    'currency' => 'ZMW',
                    'taxRate' => 0,
                    'enablePayments' => true
                ],
                'courses' => [
                    'requireApproval' => false,
                    'autoEnroll' => false,
                    'certificateEnabled' => true
                ],
                'notifications' => [
                    'emailNotifications' => true,
                    'smsNotifications' => false
                ]
            ];

            foreach ($settings as $setting) {
                $key = $setting['setting_key'];
                $value = $setting['setting_value'];

                // Try to detect the category from the key
                if (strpos($key, 'site_') === 0) {
                    $settingsObj['general'][str_replace('site_', '', $key)] = $value;
                } elseif (strpos($key, 'smtp_') === 0 || strpos($key, 'email_') === 0) {
                    $settingsObj['email'][str_replace(['smtp_', 'email_'], '', $key)] = $value;
                } elseif (strpos($key, 'payment_') === 0) {
                    $settingsObj['payments'][str_replace('payment_', '', $key)] = $value;
                } elseif (strpos($key, 'course_') === 0) {
                    $settingsObj['courses'][str_replace('course_', '', $key)] = $value;
                } elseif (strpos($key, 'notification_') === 0) {
                    $settingsObj['notifications'][str_replace('notification_', '', $key)] = $value;
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $settingsObj
            ]);
            break;

        case 'POST':
        case 'PUT':
            // Update settings
            if (empty($input)) {
                throw new Exception('Settings data is required');
            }

            $db->beginTransaction();

            // Flatten the settings object and save to database
            foreach ($input as $category => $categorySettings) {
                if (!is_array($categorySettings)) continue;

                foreach ($categorySettings as $key => $value) {
                    $settingKey = '';

                    // Build the setting key based on category
                    switch ($category) {
                        case 'general':
                            $settingKey = 'site_' . $key;
                            break;
                        case 'email':
                            $settingKey = (strpos($key, 'smtp') === 0 ? '' : 'email_') . $key;
                            if (strpos($key, 'smtp') === 0) {
                                $settingKey = $key;
                            }
                            break;
                        case 'payments':
                            $settingKey = 'payment_' . $key;
                            break;
                        case 'courses':
                            $settingKey = 'course_' . $key;
                            break;
                        case 'notifications':
                            $settingKey = 'notification_' . $key;
                            break;
                        default:
                            $settingKey = $key;
                    }

                    // Convert boolean to string
                    if (is_bool($value)) {
                        $value = $value ? '1' : '0';
                    }

                    // Check if setting exists
                    $exists = $db->exists('system_settings', 'setting_key = ?', [$settingKey]);

                    if ($exists) {
                        $db->update(
                            'system_settings',
                            ['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
                            'setting_key = ?',
                            [$settingKey]
                        );
                    } else {
                        $db->insert('system_settings', [
                            'setting_key' => $settingKey,
                            'setting_value' => $value,
                            'setting_type' => 'text',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
