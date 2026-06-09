<?php
require_once 'functions.php';
require_role('admin');

$pageTitle = 'Admin Control Panel — Medicare Plus';
$user      = get_logged_in_user();
$conn      = get_db_connection();
$msg       = '';

// Handle User Deletion (System Admin Action)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['target_user_id'])) {
    if (csrf_verify($_POST['csrf_token'] ?? '')) {
        $targetId = (int)$_POST['target_user_id'];

        // Ensure admin cannot delete themselves
        if ($_POST['action'] === 'delete' && $targetId !== $user['id']) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $targetId);
            if ($stmt->execute()) {
                $msg = "User successfully removed from the system. Associated records (appointments/reports) were cascaded.";
            } else {
                $msg = "Error deleting user.";
            }
            $stmt->close();
        }
    }
}

// Aggregate System Statistics
$stats = [
    'patients' => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'patient'")->fetch_row()[0],
    'doctors'  => $conn->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'")->fetch_row()[0],
    'appointments' => $conn->query("SELECT COUNT(*) FROM appointments")->fetch_row()[0],
    'revenue'  => $conn->query("SELECT SUM(amount) FROM payments WHERE status = 'paid'")->fetch_row()[0] ?? 0
];

// Fetch Recent Users for Management
$recentUsers = [];
$res = $conn->query("SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 15");
while ($row = $res->fetch_assoc()) {
    $recentUsers[] = $row;
}

include 'header.php';
?>

<div class="dash-layout">
    <!-- Sidebar -->
    <aside class="dash-sidebar">
        <div class="dash-user">
            <div class="dash-avatar" style="background: rgba(232,168,56,0.2); color: var(--secondary);">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
            <span>System Administrator</span>
        </div>

        <!-- Updated Navigation with Secure Inbox -->
        <nav class="dash-nav">
            <a href="dashboard_admin.php" class="active"><i class="fas fa-users-cog"></i> User Management</a>
            <a href="manage_doctors.php"><i class="fas fa-user-md"></i> Doctor Profiles</a>
            <a href="admin_reports.php"><i class="fas fa-chart-line"></i> System Reports</a>
            <a href="chat_engine.php"><i class="fas fa-comments"></i> Secure Inbox</a>
            <div class="dash-nav-divider"></div>
            <a href="logout.php" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Admin Control Panel</h1>
                <p>Manage platform users, roles, and monitor system health.</p>
            </div>
            <a href="add_doctor.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Doctor</a>
        </div>

        <?php if ($msg): ?>
            <div class="card" style="margin-bottom: 20px; padding: 15px; border-left: 4px solid var(--primary);">
                <strong><i class="fas fa-info-circle"></i> <?= htmlspecialchars($msg) ?></strong>
            </div>
        <?php endif; ?>

        <!-- Top-level Analytics -->
        <div class="stat-grid">
            <div class="stat-widget">
                <div class="stat-icon teal"><i class="fas fa-user-injured"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['patients'] ?></strong>
                    <span>Total Patients</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon teal"><i class="fas fa-user-md"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['doctors'] ?></strong>
                    <span>Total Doctors</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon amber"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-info">
                    <strong><?= $stats['appointments'] ?></strong>
                    <span>All Appointments</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon green"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <strong style="font-size: 1.1rem;">LKR <?= number_format($stats['revenue'], 0) ?></strong>
                    <span>Gross Revenue</span>
                </div>
            </div>
        </div>

        <!-- User Management Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-users"></i> Registered System Users</h2>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Registration Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td style="color: var(--muted);">#<?= $u['id'] ?></td>
                                <td><strong><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-pending';
                                    if ($u['role'] === 'admin') $badgeClass = 'badge-completed';
                                    if ($u['role'] === 'doctor') $badgeClass = 'badge-confirmed';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>"><?= strtoupper($u['role']) ?></span>
                                </td>
                                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <?php if ($u['id'] !== $user['id']): ?>
                                        <form method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to permanently delete this user?');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-outline btn-sm" style="color: #dc3545; border-color: #dc3545;">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 12px; color: var(--muted);">You</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>