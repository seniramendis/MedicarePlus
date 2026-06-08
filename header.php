<?php
// header.php — included at the top of every public page
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
</head>
<body class="page-body">

<header class="main-header">
    <div class="header-inner">
        <a href="Home.php" class="logo">
            <div class="logo-icon"><i class="fas fa-leaf"></i></div>
            Medicare Plus
        </a>

        <nav class="nav-links" id="navLinks">
            <a href="Home.php"     class="<?= navActive('Home',     $currentPage) ?>">Home</a>
            <a href="services.php" class="<?= navActive('services', $currentPage) ?>">Services</a>
            <a href="doctors.php"  class="<?= navActive('doctors',  $currentPage) ?>">Doctors</a>
            <a href="blog.php"     class="<?= navActive('blog',     $currentPage) ?>">Blog</a>

            <?php if ($isLoggedIn): ?>
                <a href="<?= htmlspecialchars($dashboardLink) ?>" class="btn-dashboard">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                <a href="Login.php"    class="btn-login">Login</a>
                <a href="register.php" class="btn-signup">Sign Up</a>
            <?php endif; ?>
        </nav>

        <button class="hamburger" id="hamburger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</header>

<script>
(function(){
    var ham = document.getElementById('hamburger');
    var nav = document.getElementById('navLinks');
    if (!ham || !nav) return;
    ham.addEventListener('click', function(){
        ham.classList.toggle('open');
        nav.classList.toggle('open');
    });
    nav.querySelectorAll('a').forEach(function(a){
        a.addEventListener('click', function(){
            ham.classList.remove('open');
            nav.classList.remove('open');
        });
    });
})();
</script>
