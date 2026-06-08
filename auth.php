<?php
// ─────────────────────────────────────────────────────────────
// MedicarePlus – auth.php
// Session init + auth guard included on every protected page
// ─────────────────────────────────────────────────────────────

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/functions.php';

$isLoggedIn    = isset($_SESSION['user_id']);
$userRole      = $_SESSION['role'] ?? 'guest';
$dashboardLink = $isLoggedIn ? 'dashboard_' . strtolower($userRole) . '.php' : 'Login.php';
$currentPage   = basename($_SERVER['PHP_SELF']);
