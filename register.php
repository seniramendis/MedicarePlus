<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard_' . strtolower($_SESSION['role']) . '.php');
    exit;
}

$pageTitle = 'Create Account — Medicare Plus';
$errors    = [];
$success   = '';
$data      = ['first_name'=>'','last_name'=>'','email'=>'','phone'=>'','city'=>'','role'=>'patient'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW) ?? '';
    if (!csrf_verify($token)) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $data['first_name'] = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $data['last_name']  = trim(filter_input(INPUT_POST, 'last_name',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $data['email']      = trim(filter_input(INPUT_POST, 'email',      FILTER_SANITIZE_EMAIL) ?? '');
        $data['phone']      = trim(filter_input(INPUT_POST, 'phone',      FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $data['city']       = trim(filter_input(INPUT_POST, 'city',       FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
        $data['role']       = in_array($_POST['role'] ?? '', ['patient','doctor']) ? $_POST['role'] : 'patient';
        $password           = filter_input(INPUT_POST, 'password',  FILTER_UNSAFE_RAW) ?? '';
        $confirm            = filter_input(INPUT_POST, 'confirm',   FILTER_UNSAFE_RAW) ?? '';

        if (!$data['first_name']) $errors[] = 'First name is required.';
        if (!$data['last_name'])  $errors[] = 'Last name is required.';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';
        if (fetch_user_by_email($data['email'])) $errors[] = 'An account with that email already exists.';

        if (empty($errors)) {
            $userId = create_user($data['first_name'], $data['last_name'], $data['email'], $password, $data['role'], $data['phone'], $data['city']);
            if ($userId) {
                if ($data['role'] === 'patient') {
                    create_patient_profile($userId);
                }
                // Auto-login
                $_SESSION['user_id'] = $userId;
                $_SESSION['role']    = $data['role'];
                $_SESSION['name']    = $data['first_name'];
                session_regenerate_id(true);
                header('Location: dashboard_' . $data['role'] . '.php');
                exit;
            } else {
                $errors[] = 'Could not create your account. Please try again.';
            }
        }
    }
}

$sriLankaCities = ['Colombo','Kandy','Galle','Negombo','Matara','Kurunegala','Jaffna','Trincomalee','Batticaloa','Anuradhapura','Polonnaruwa','Ratnapura','Badulla','Nuwara Eliya','Hambantota'];
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

<div class="auth-page" style="align-items:flex-start;padding:40px 20px">
    <div class="auth-card" style="max-width:520px;margin:auto">

        <div class="auth-logo">
            <div class="logo-icon" style="margin:0 auto 12px"><i class="fas fa-leaf"></i></div>
            <h2>Join Medicare Plus</h2>
            <p>Create your free account today</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:2px"></i>
            <ul style="margin:0;padding-left:16px;list-style:disc">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

            <!-- Account type -->
            <div class="form-group">
                <label>I am registering as</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <label for="role_patient" style="cursor:pointer">
                        <input type="radio" id="role_patient" name="role" value="patient" <?= $data['role']==='patient'?'checked':'' ?> style="display:none" onchange="highlightRole()">
                        <div class="role-tile" id="tile_patient" style="border:2px solid <?= $data['role']==='patient'?'var(--teal)':'var(--border)' ?>;background:<?= $data['role']==='patient'?'rgba(13,115,119,.06)':'var(--white)' ?>;border-radius:10px;padding:16px;text-align:center;transition:all .2s">
                            <i class="fas fa-user-injured" style="font-size:1.5rem;color:var(--teal);margin-bottom:8px;display:block"></i>
                            <strong style="font-size:.9rem;color:var(--dark)">Patient</strong>
                            <p style="font-size:.75rem;margin-top:4px">Book appointments</p>
                        </div>
                    </label>
                    <label for="role_doctor" style="cursor:pointer">
                        <input type="radio" id="role_doctor" name="role" value="doctor" <?= $data['role']==='doctor'?'checked':'' ?> style="display:none" onchange="highlightRole()">
                        <div class="role-tile" id="tile_doctor" style="border:2px solid <?= $data['role']==='doctor'?'var(--teal)':'var(--border)' ?>;background:<?= $data['role']==='doctor'?'rgba(13,115,119,.06)':'var(--white)' ?>;border-radius:10px;padding:16px;text-align:center;transition:all .2s">
                            <i class="fas fa-user-md" style="font-size:1.5rem;color:var(--teal);margin-bottom:8px;display:block"></i>
                            <strong style="font-size:.9rem;color:var(--dark)">Doctor</strong>
                            <p style="font-size:.75rem;margin-top:4px">Manage consultations</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Name row -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label for="first_name">First name</label>
                    <input type="text" id="first_name" name="first_name" class="form-control"
                        value="<?= htmlspecialchars($data['first_name']) ?>"
                        placeholder="Nuwan" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control"
                        value="<?= htmlspecialchars($data['last_name']) ?>"
                        placeholder="Perera" required>
                </div>
            </div>

            <div class="form-group">
                <label for="reg_email">Email address</label>
                <input type="email" id="reg_email" name="email" class="form-control"
                    value="<?= htmlspecialchars($data['email']) ?>"
                    placeholder="you@example.lk" required autocomplete="email">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                <div class="form-group">
                    <label for="phone">Phone number</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                        value="<?= htmlspecialchars($data['phone']) ?>"
                        placeholder="077 123 4567">
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <select id="city" name="city" class="form-control">
                        <option value="">Select city</option>
                        <?php foreach ($sriLankaCities as $c): ?>
                        <option value="<?= $c ?>" <?= $data['city']===$c?'selected':'' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="reg_password">Password</label>
                <div style="position:relative">
                    <input type="password" id="reg_password" name="password" class="form-control"
                        placeholder="Minimum 8 characters" required
                        style="padding-right:44px" autocomplete="new-password">
                    <button type="button" onclick="togglePwd(this)" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:.9rem">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm">Confirm password</label>
                <input type="password" id="confirm" name="confirm" class="form-control"
                    placeholder="Re-enter password" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:4px">
                <i class="fas fa-user-plus"></i> Create my account
            </button>
        </form>

        <div class="auth-switch" style="margin-top:20px">
            Already have an account? <a href="Login.php">Sign in</a>
        </div>

        <div style="text-align:center;margin-top:12px">
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
    if (input.type === 'password') { input.type = 'text'; icon.className = 'fas fa-eye-slash'; }
    else { input.type = 'password'; icon.className = 'fas fa-eye'; }
}

function highlightRole() {
    var pat = document.getElementById('role_patient').checked;
    var tp  = document.getElementById('tile_patient');
    var td  = document.getElementById('tile_doctor');
    var sel = 'border:2px solid var(--teal);background:rgba(13,115,119,.06)';
    var uns = 'border:2px solid var(--border);background:var(--white)';
    tp.style.cssText = pat ? sel : uns;
    td.style.cssText = pat ? uns : sel;
}

// Make tiles clickable
['tile_patient','tile_doctor'].forEach(function(id) {
    document.getElementById(id).addEventListener('click', function() {
        var radio = this.parentElement.querySelector('input[type=radio]');
        radio.checked = true;
        highlightRole();
    });
});
</script>
</body>
</html>
