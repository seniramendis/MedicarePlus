<?php
require_once 'functions.php';
require_role('admin');

$pageTitle = 'Manage Doctors — Medicare Plus';
$user      = get_logged_in_user();
$conn      = get_db_connection();
$msg       = '';
$msgType   = '';

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['target_user_id'])) {
    if (csrf_verify($_POST['csrf_token'] ?? '')) {
        if ($_POST['action'] === 'delete') {
            $targetUserId = (int)$_POST['target_user_id'];
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
            $stmt->bind_param('i', $targetUserId);
            if ($stmt->execute()) {
                $msg = "Doctor profile and all associated records deleted successfully.";
                $msgType = "success";
            } else {
                $msg = "Error deleting doctor profile.";
                $msgType = "error";
            }
            $stmt->close();
        }
    }
}

$doctors = fetch_all_doctors();

include 'header.php';
?>

<div class="dash-layout">
    <aside class="dash-sidebar">
        <div class="dash-user">
            <div class="dash-avatar" style="background: rgba(232,168,56,0.2); color: var(--secondary);">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
            <span>System Administrator</span>
        </div>
        <nav class="dash-nav">
            <a href="dashboard_admin.php"><i class="fas fa-users-cog"></i> User Management</a>
            <a href="manage_doctors.php" class="active"><i class="fas fa-user-md"></i> Doctor Profiles</a>
            <a href="admin_reports.php"><i class="fas fa-chart-line"></i> System Reports</a>
            <div class="dash-nav-divider"></div>
            <a href="logout.php" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Doctor Management</h1>
                <p>Add, update, or remove specialists from the platform.</p>
            </div>
            <a href="add_doctor.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Doctor</a>
        </div>

        <?php if ($msg): ?>
            <div class="card" style="margin-bottom: 24px; padding: 15px; border-left: 4px solid <?= $msgType === 'success' ? 'var(--primary)' : '#dc3545' ?>;">
                <strong><?= htmlspecialchars($msg) ?></strong>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="table-wrap">
                <?php if (empty($doctors)): ?>
                    <div class="empty-state">
                        <i class="fas fa-user-md"></i>
                        <h3>No Doctors Found</h3>
                        <p>There are currently no doctors registered in the system.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Doctor Details</th>
                                <th>Specialization</th>
                                <th>Consultation Fee</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doc): ?>
                                <tr>
                                    <td>
                                        <strong>Dr. <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></strong><br>
                                        <span style="font-size: 13px; color: var(--muted);"><?= htmlspecialchars($doc['email']) ?></span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($doc['specialization']) ?><br>
                                        <span style="font-size: 13px; color: var(--muted);"><?= htmlspecialchars($doc['hospital']) ?></span>
                                    </td>
                                    <td>LKR <?= number_format($doc['consultation_fee'], 0) ?></td>
                                    <td><i class="fas fa-star" style="color:var(--secondary)"></i> <?= number_format($doc['rating'], 1) ?></td>
                                    <td style="display: flex; gap: 8px;">
                                        <a href="edit_doctor.php?id=<?= $doc['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                        <form method="POST" style="margin: 0;" onsubmit="return confirm('WARNING: This will permanently delete the doctor, their profile, and all their appointments. Continue?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <input type="hidden" name="target_user_id" value="<?= $doc['user_id'] ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-outline btn-sm" style="color: #dc3545; border-color: #dc3545;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>