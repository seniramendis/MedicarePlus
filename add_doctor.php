<?php
require_once 'functions.php';
require_role('admin');

$pageTitle = 'Add New Doctor — Medicare Plus';
$user      = get_logged_in_user();
$conn      = get_db_connection();
$msg       = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_verify($_POST['csrf_token'] ?? '')) {
        $first = trim($_POST['first_name']);
        $last  = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $pass  = $_POST['password'];
        $phone = trim($_POST['phone']);
        $city  = trim($_POST['city']);

        $spec  = trim($_POST['specialization']);
        $qual  = trim($_POST['qualification']);
        $hosp  = trim($_POST['hospital']);
        $loc   = trim($_POST['location']);
        $fee   = (float)$_POST['consultation_fee'];
        $exp   = (int)$_POST['experience_years'];
        $avail = trim($_POST['availability']);
        $bio   = trim($_POST['bio']);

        if (fetch_user_by_email($email)) {
            $msg = "A user with this email already exists.";
        } else {
            $conn->begin_transaction();
            try {
                $userId = create_user($first, $last, $email, $pass, 'doctor', $phone, $city);
                if (!$userId) throw new Exception("Failed to create user account.");

                $stmt = $conn->prepare("INSERT INTO doctors (user_id, specialization, qualification, hospital, location, consultation_fee, experience_years, availability, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isssssiss', $userId, $spec, $qual, $hosp, $loc, $fee, $exp, $avail, $bio);
                $stmt->execute();

                $conn->commit();
                header("Location: manage_doctors.php");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                $msg = "Error creating doctor profile: " . $e->getMessage();
            }
        }
    }
}

include 'header.php';
?>

<div class="dash-layout">
    <aside class="dash-sidebar">
        <div class="dash-user">
            <div class="dash-avatar" style="background: rgba(232,168,56,0.2); color: var(--secondary);"><i class="fas fa-shield-alt"></i></div>
            <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
            <span>System Administrator</span>
        </div>
        <nav class="dash-nav">
            <a href="dashboard_admin.php"><i class="fas fa-users-cog"></i> User Management</a>
            <a href="manage_doctors.php" class="active"><i class="fas fa-user-md"></i> Doctor Profiles</a>
            <a href="admin_reports.php"><i class="fas fa-chart-line"></i> System Reports</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Add New Doctor</h1>
                <p>Create a new specialist account and public profile.</p>
            </div>
            <a href="manage_doctors.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?php if ($msg): ?>
            <div class="card" style="margin-bottom: 24px; padding: 15px; border-left: 4px solid #dc3545;">
                <strong><?= htmlspecialchars($msg) ?></strong>
            </div>
        <?php endif; ?>

        <div class="card" style="padding: 24px;">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                <h3 style="margin-bottom: 16px; color: var(--primary);">1. Account Information</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>First Name</label><input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label><input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label><input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Initial Password</label><input type="text" name="password" class="form-control" value="password123" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label><input type="text" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>City</label><input type="text" name="city" class="form-control" required>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border); margin: 30px 0;">

                <h3 style="margin-bottom: 16px; color: var(--primary);">2. Public Doctor Profile</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Specialization (e.g., Cardiology)</label><input type="text" name="specialization" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Qualifications (e.g., MBBS, MD)</label><input type="text" name="qualification" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Primary Hospital</label><input type="text" name="hospital" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Clinic Location</label><input type="text" name="location" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Consultation Fee (LKR)</label><input type="number" name="consultation_fee" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Years of Experience</label><input type="number" name="experience_years" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Availability Schedule</label><input type="text" name="availability" class="form-control" placeholder="e.g., Mon-Fri 9AM - 2PM" required>
                </div>
                <div class="form-group">
                    <label>Professional Bio</label><textarea name="bio" class="form-control" rows="4" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Create Doctor Profile</button>
            </form>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>