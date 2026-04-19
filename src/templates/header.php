<?php
/**
 * Edutrack computer training college
 * Main Site Header Template
 */

// This template should be included after bootstrap.php is loaded
// If bootstrap is not loaded, load it
if (!defined('APP_BOOTSTRAPPED')) {
    require_once dirname(__DIR__) . '/bootstrap.php';
}

// Set page title
$page_title = $page_title ?? 'Edutrack computer training college - TEVETA REGISTERED';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= sanitize($page_title) ?></title>
    
    <!-- Meta Tags -->
    <meta name="description" content="<?= sanitize($page_description ?? 'Edutrack Computer Training College - TEVETA registered institution offering quality computer training in Zambia. Transform your future with industry-recognized certification programs.') ?>">
    <meta name="keywords" content="computer training, TEVETA, Zambia, courses, certification, web development, digital marketing, Lusaka">
    <meta name="author" content="Edutrack computer training college">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= url() ?>">
    <meta property="og:title" content="<?= sanitize($page_title) ?>">
    <meta property="og:description" content="<?= sanitize($page_description ?? 'TEVETA registered computer training institution in Zambia') ?>">
    <meta property="og:image" content="<?= $og_image ?? asset('images/logo.png') ?>">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= url() ?>">
    <meta property="twitter:title" content="<?= sanitize($page_title) ?>">
    <meta property="twitter:description" content="<?= sanitize($page_description ?? 'TEVETA registered computer training institution in Zambia') ?>">
    <meta property="twitter:image" content="<?= $og_image ?? asset('images/logo.png') ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= asset('images/favicon.ico') ?>">
    
    <!-- Tailwind CSS CDN (v3.4.1) -->
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= asset('css/whatsapp-button.css') ?>">
    
    <!-- Shared Tailwind Config -->
    <script src="<?= asset('js/tailwind-config.js') ?>"></script>
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #2E70DA;
            --secondary-color: #F6B745;
            --primary-dark: #1E4A8A;
            --secondary-dark: #D89E2E;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(46, 112, 218, 0.4);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: #111827;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(246, 183, 69, 0.4);
        }
        
        .nav-link {
            position: relative;
            transition: color 0.3s ease;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--secondary-color);
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
    </style>
    
    <?php if (isset($extra_css)): ?>
        <?= $extra_css ?>
    <?php endif; ?>
</head>
<body class="bg-gray-50">
    
    <!-- Top Bar -->
    <div class="bg-primary-600 text-white py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center text-xs sm:text-sm gap-2 sm:gap-0">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-4 gap-y-1">
                    <span class="flex items-center">
                        <i class="fas fa-certificate mr-1 text-secondary-500"></i>
                        <strong>TEVETA Registered:</strong> <?= TEVETA_CODE ?>
                    </span>
                    <span class="hidden md:flex items-center">
                        <i class="fas fa-phone mr-1"></i>
                        <?= SITE_PHONE ?>
                    </span>
                </div>
                <div class="flex items-center gap-3 sm:gap-4">
                    <span class="hidden sm:flex items-center">
                        <i class="fas fa-envelope mr-1"></i>
                        <?= SITE_EMAIL ?>
                    </span>
                    <div class="flex items-center space-x-2">
                        <?php if (config('social.facebook')): ?>
                            <a href="<?= config('social.facebook') ?>" target="_blank" class="hover:text-secondary-400 transition">
                                <i class="fab fa-facebook"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (config('social.twitter')): ?>
                            <a href="<?= config('social.twitter') ?>" target="_blank" class="hover:text-secondary-400 transition">
                                <i class="fab fa-twitter"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (config('social.linkedin')): ?>
                            <a href="<?= config('social.linkedin') ?>" target="_blank" class="hover:text-secondary-400 transition">
                                <i class="fab fa-linkedin"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Navigation -->
    <?php include dirname(__FILE__) . '/navigation.php'; ?>
    
    <!-- Flash Messages -->
    <div id="flash-container" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <?= getFlash('message') ?>
        <?= getFlash('success') ?>
        <?= getFlash('error') ?>
        <?= getFlash('warning') ?>
    </div>
    <script>
    document.querySelectorAll('#flash-container > div').forEach(function(el) {
        setTimeout(function() {
            el.style.transition = 'opacity 0.5s ease';
            el.style.opacity = '0';
            setTimeout(function() { el.remove(); }, 500);
        }, 5000);
    });
    </script>
