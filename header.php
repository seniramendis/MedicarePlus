<?php
// ─────────────────────────────────────────────────────────────
// MedicarePlus – header.php
// Included at the top of every public page
// ─────────────────────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';

$isLoggedIn    = isset($_SESSION['user_id']);
$userRole      = $_SESSION['role'] ?? 'guest';
$dashboardLink = $isLoggedIn ? 'dashboard_' . strtolower($userRole) . '.php' : 'Login.php';
$currentPage   = basename($_SERVER['PHP_SELF']);

if (!isset($pageTitle)) $pageTitle = 'Medicare Plus — Sri Lanka\'s Trusted Health Platform';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="Medicare Plus — Book specialist consultations, manage health records and payments across Sri Lanka's top hospitals.">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="page-body">

    <!-- ══════════════════════════════════════
     TOP INFO BAR
══════════════════════════════════════ -->
    <div class="mp-topbar">
        <div class="mp-topbar-inner">
            <div class="mp-topbar-item">
                <i class="fas fa-map-marker-alt"></i>
                Serving 12 cities across Sri Lanka
            </div>
            <div class="mp-topbar-divider">|</div>
            <div class="mp-topbar-item">
                <i class="fas fa-phone-alt"></i>
                24/7 Helpline: <a href="tel:+94112345678">+94 11 234 5678</a>
            </div>
            <div class="mp-topbar-divider">|</div>
            <div class="mp-topbar-item">
                <i class="fas fa-clock"></i>
                Mon–Sat, 8 AM – 9 PM
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════
     MAIN HEADER
══════════════════════════════════════ -->
    <header class="mp-header">
        <div class="mp-header-inner">

            <!-- Logo -->
            <a href="Home.php" class="mp-logo">
                <div class="mp-logo-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="mp-logo-text">
                    <strong>Medicare Plus</strong>
                    <span>Health Network</span>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="mp-nav" id="mpNav">
                <a href="Home.php" class="<?= navActive('Home',     $currentPage) ?>">Home</a>
                <a href="services.php" class="<?= navActive('services', $currentPage) ?>">Services</a>
                <a href="doctors.php" class="<?= navActive('doctors',  $currentPage) ?>">Doctors</a>
                <a href="blog.php" class="<?= navActive('blog',     $currentPage) ?>">Blog</a>
            </nav>

            <!-- Right Side -->
            <div class="mp-right">

                <!-- Search pill -->
                <div class="mp-search" id="mpSearchTrigger">
                    <i class="fas fa-search"></i>
                    <span class="mp-search-text">Search doctors...</span>
                    <span class="mp-search-kbd">⌘K</span>
                </div>

                <div class="mp-nav-divider"></div>

                <?php if ($isLoggedIn): ?>
                    <a href="<?= htmlspecialchars($dashboardLink) ?>" class="mp-btn-dashboard">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                    <a href="logout.php" class="mp-btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="Login.php" class="mp-btn-login">Login</a>
                    <a href="register.php" class="mp-btn-signup">
                        <i class="fas fa-user-plus"></i> Sign Up
                    </a>
                <?php endif; ?>
            </div>

            <!-- Hamburger (mobile) -->
            <button class="mp-hamburger" id="mpHamburger" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

        </div>
    </header>

    <script>
        (function() {
            var ham = document.getElementById('mpHamburger');
            var nav = document.getElementById('mpNav');
            if (!ham || !nav) return;

            ham.addEventListener('click', function() {
                ham.classList.toggle('open');
                nav.classList.toggle('open');
            });

            nav.querySelectorAll('a').forEach(function(a) {
                a.addEventListener('click', function() {
                    ham.classList.remove('open');
                    nav.classList.remove('open');
                });
            });
        })();
    </script>