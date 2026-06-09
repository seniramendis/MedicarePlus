<?php
require_once 'functions.php';
require_role('admin');

$pageTitle = 'Edit Doctor — Medicare Plus';
$user      = get_logged_in_user();
$conn      = get_db_connection();
$docId     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$msg       = '';

$doctor = fetch_doctor_by_id($docId);
if (!$doctor) {
    header("Location: manage_doctors.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_verify($_POST['csrf_token'] ?? '')) {
        // Update Users Table
        $first = trim($_POST['first_name']);
        $last  = trim($_POST['last_name']);
        $phone = trim($_POST['phone']);
        $city  = trim($_POST['city']);

        // Update Doctors Table
        $spec  = trim($_POST['specialization']);
        $qual  = trim($_POST['qualification']);
        $hosp  = trim($_POST['hospital']);
        $loc   = trim($_POST['location']);
        $fee   = (float)$_POST['consultation_fee'];
        $exp   = (int)$_POST['experience_years'];
        $avail = trim($_POST['availability']);
        $bio   = trim($_POST['bio']);

        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, city=? WHERE id=?");
            $stmt1->bind_param('ssssi', $first, $last, $phone, $city, $doctor['user_id']);
            $stmt1->execute();

            $stmt2 = $conn->prepare("UPDATE doctors SET specialization=?, qualification=?, hospital=?, location=?, consultation_fee=?, experience_years=?, availability=?, bio=? WHERE id=?");
            $stmt2->bind_param('ssssiissi', $spec, $qual, $hosp, $loc, $fee, $exp, $avail, $bio, $docId);
            $stmt2->execute();

            $conn->commit();
            $msg = "Doctor profile updated successfully.";
            $doctor = fetch_doctor_by_id($docId); // Refresh data
        } catch (Exception $e) {
            $conn->rollback();
            $msg = "Error updating profile.";
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
                <h1>Edit Doctor Profile</h1>
                <p>Updating records for Dr. <?= htmlspecialchars($doctor['last_name']) ?></p>
            </div>
            <a href="manage_doctors.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?php if ($msg): ?>
            <div class="card" style="margin-bottom: 24px; padding: 15px; border-left: 4px solid var(--primary);">
                <strong><?= htmlspecialchars($msg) ?></strong>
            </div>
        <?php endif; ?>

        <div class="card" style="padding: 24px;">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                <h3 style="margin-bottom: 16px; color: var(--primary);">Account Information</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($doctor['first_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($doctor['last_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($doctor['phone']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($doctor['city']) ?>" required>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border); margin: 30px 0;">

                <h3 style="margin-bottom: 16px; color: var(--primary);">Public Profile Details</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Specialization</label>
                        <input type="text" name="specialization" class="form-control" value="<?= htmlspecialchars($doctor['specialization']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Qualifications</label>
                        <input type="text" name="qualification" class="form-control" value="<?= htmlspecialchars($doctor['qualification']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Primary Hospital</label>
                        <input type="text" name="hospital" class="form-control" value="<?= htmlspecialchars($doctor['hospital']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Clinic Location</label>
                        <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($doctor['location']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Consultation Fee (LKR)</label>
                        <input type="number" name="consultation_fee" class="form-control" value="<?= htmlspecialchars($doctor['consultation_fee']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Years of Experience</label>
                        <input type="number" name="experience_years" class="form-control" value="<?= htmlspecialchars($doctor['experience_years']) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Availability Schedule</label>
                    <input type="text" name="availability" class="form-control" value="<?= htmlspecialchars($doctor['availability']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Professional Bio</label>
                    <textarea name="bio" class="form-control" rows="4" required><?= htmlspecialchars($doctor['bio']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Save Changes</button>
            </form>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>