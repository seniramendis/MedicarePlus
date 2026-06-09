<?php
require_once 'functions.php';
require_role('doctor');

$pageTitle = 'Doctor Dashboard — Medicare Plus';
$user      = get_logged_in_user();
$doctor    = fetch_doctor_by_user_id($user['id']);

if (!$doctor) {
    header('Location: profile_doctor.php');
    exit;
}

$successMsg = '';

// Handle Appointment Acceptance Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['appointment_id'])) {
    if (csrf_verify($_POST['csrf_token'] ?? '')) {
        $appId = (int)$_POST['appointment_id'];
        if ($_POST['action'] === 'accept') {
            update_appointment_status($appId, 'confirmed');
            $successMsg = 'Appointment confirmed. The patient can now proceed to payment.';
        }
    }
}

// Fetch Logic
$appointments = fetch_appointments_for_doctor($doctor['id']);
$upcoming = [];
$completedCount = 0;

foreach ($appointments as $a) {
    if ($a['status'] === 'completed') {
        $completedCount++;
    } elseif ($a['appointment_dt'] >= date('Y-m-d 00:00:00') && $a['status'] !== 'cancelled') {
        $upcoming[] = $a;
    }
}

// Calculate Revenue directly from Payments table
$conn = get_db_connection();
$totalRevenue = 0;
$transactions = [];

$stmt = $conn->prepare("SELECT amount, payment_method, paid_at FROM payments p JOIN appointments a ON p.appointment_id = a.id WHERE a.doctor_id = ? AND p.status = 'paid' ORDER BY p.paid_at DESC");
$stmt->bind_param('i', $doctor['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $totalRevenue += (float)$row['amount'];
    if (count($transactions) < 5) $transactions[] = $row;
}
$stmt->close();

$unreadCount = get_unread_count($user['id']);

include 'header.php';
?>

<div class="dash-layout">
    <!-- Sidebar -->
    <aside class="dash-sidebar">
        <div class="dash-user">
            <div class="dash-avatar" style="background: rgba(13,115,119,.3); color: var(--teal-light);">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
            <h4>Dr. <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
            <span><?= htmlspecialchars($doctor['specialization']) ?></span>
        </div>

        <!-- Updated Navigation with Secure Inbox -->
        <nav class="dash-nav">
            <a href="dashboard_doctor.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="appointments.php"><i class="fas fa-calendar-alt"></i> My Schedule</a>
            <a href="patients.php"><i class="fas fa-users"></i> My Patients</a>
            <a href="chat_engine.php"><i class="fas fa-comments"></i> Secure Inbox</a>
            <div class="dash-nav-divider"></div>
            <a href="profile_doctor.php"><i class="fas fa-user-md"></i> Edit Profile</a>
            <a href="logout.php" style="color:#e74c3c"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Good day, Dr. <?= htmlspecialchars($user['last_name']) ?>! 🩺</h1>
                <p>Review your schedule, recent transactions, and patient requests.</p>
            </div>
            <a href="medical_reports.php" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Report</a>
        </div>

        <?php if ($successMsg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle" style="margin-top:2px;"></i>
                <div><?= htmlspecialchars($successMsg) ?></div>
            </div>
        <?php endif; ?>

        <div class="stat-grid">
            <div class="stat-widget">
                <div class="stat-icon red"><i class="fas fa-bell"></i></div>
                <div class="stat-info">
                    <strong><?= $unreadCount ?></strong>
                    <span>Pending Alerts</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon teal"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <strong><?= count($upcoming) ?></strong>
                    <span>Upcoming Visits</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon green"><i class="fas fa-check-double"></i></div>
                <div class="stat-info">
                    <strong><?= $completedCount ?></strong>
                    <span>Completed</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon amber"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <strong style="font-size: 1.2rem;">LKR <?= number_format($totalRevenue, 0) ?></strong>
                    <span>Total Revenue</span>
                </div>
            </div>
        </div>

        <div class="grid-2">
            <!-- Action Required: Appointments -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-calendar-alt" style="color:var(--teal)"></i> Action Required</h2>
                </div>
                <div class="table-wrap">
                    <?php if (empty($upcoming)): ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-check"></i>
                            <h3>All clear!</h3>
                            <p>No active appointment requests.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Date & Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($upcoming, 0, 5) as $app): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($app['pat_first'] . ' ' . $app['pat_last']) ?></strong></td>
                                        <td style="white-space: nowrap;"><?= format_date($app['appointment_dt']) ?></td>
                                        <td>
                                            <?php if ($app['status'] === 'pending'): ?>
                                                <form method="POST" style="margin:0;">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                    <input type="hidden" name="appointment_id" value="<?= $app['id'] ?>">
                                                    <button type="submit" name="action" value="accept" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Accept</button>
                                                </form>
                                            <?php else: ?>
                                                <?= status_badge($app['status']) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-receipt" style="color:var(--leaf)"></i> Recent Financials</h2>
                </div>
                <div class="table-wrap">
                    <?php if (empty($transactions)): ?>
                        <div class="empty-state">
                            <i class="fas fa-coins"></i>
                            <h3>No data</h3>
                            <p>No payment transactions yet.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($transactions as $tx): ?>
                                    <tr>
                                        <td style="color:var(--leaf); font-weight:700;">LKR <?= number_format($tx['amount'], 0) ?></td>
                                        <td><?= htmlspecialchars($tx['payment_method']) ?></td>
                                        <td style="font-size:0.85rem; color:var(--muted);"><?= format_date($tx['paid_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>