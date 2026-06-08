<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'functions.php';

// Already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard_' . strtolower($_SESSION['role']) . '.php');
    exit;
}

$pageTitle = 'Login — Medicare Plus';
$errors    = [];
$email     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW) ?? '';
    if (!csrf_verify($token)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $email    = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL) ?? '');
        $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW) ?? '';

        if (!$email || !$password) {
            $errors[] = 'Please enter both your email and password.';
        } else {
            $user = fetch_user_by_email($email);
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['name']    = $user['first_name'];
                session_regenerate_id(true);

                $redirect = filter_input(INPUT_GET, 'redirect', FILTER_SANITIZE_URL);
                header('Location: ' . ($redirect ?: 'dashboard_' . $user['role'] . '.php'));
                exit;
            } else {
                $errors[] = 'The email or password you entered is incorrect.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>
<body>

<div class="auth-page">
    <div class="auth-card">

        <!-- Logo -->
        <div class="auth-logo">
            <div class="logo-icon" style="margin:0 auto 12px"><i class="fas fa-leaf"></i></div>
            <h2>Welcome back</h2>
            <p>Sign in to your Medicare Plus account</p>
        </div>

        <!-- Errors -->
        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle" style="flex-shrink:0"></i>
            <div>
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="Login.php<?= isset($_GET['redirect']) ? '?redirect='.urlencode($_GET['redirect']) : '' ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div class="form-group">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="you@example.lk"
                    required
                    autocomplete="email"
                >
            </div>

            <div class="form-group">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:7px">
                    <label for="password" style="margin-bottom:0">Password</label>
                    <a href="forgot_password.php" style="font-size:.8rem;color:var(--teal)">Forgot password?</a>
                </div>
                <div style="position:relative">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                        style="padding-right:44px"
                    >
                    <button type="button" onclick="togglePwd(this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:.9rem">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="auth-divider">or</div>

        <div class="auth-switch">
            Don't have an account?
            <a href="register.php">Create one free</a>
        </div>

        <div style="text-align:center;margin-top:20px">
            <a href="Home.php" style="font-size:.82rem;color:var(--muted)">
                <i class="fas fa-arrow-left" style="font-size:.7rem"></i> Back to Medicare Plus
            </a>
        </div>

    </div>
</div>

<script>
function togglePwd(btn) {
    var input = btn.previousElementSibling;
    var icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}
</script>
</body>
</html>
