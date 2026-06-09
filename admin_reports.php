<?php
require_once 'functions.php';
require_role('admin');

$pageTitle = 'System Reports — Medicare Plus';
$user      = get_logged_in_user();
$conn      = get_db_connection();

$appStats = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
$res = $conn->query("SELECT status, COUNT(*) as count FROM appointments GROUP BY status");
while ($row = $res->fetch_assoc()) {
    $appStats[$row['status']] = $row['count'];
}

$payments = [];
$res2 = $conn->query("
    SELECT p.amount, p.payment_method, p.paid_at, p.transaction_ref, 
           u_pat.first_name as pat_first, u_pat.last_name as pat_last,
           u_doc.last_name as doc_last
    FROM payments p
    JOIN appointments a ON p.appointment_id = a.id
    JOIN patients pat ON a.patient_id = pat.id
    JOIN users u_pat ON pat.user_id = u_pat.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users u_doc ON d.user_id = u_doc.id
    WHERE p.status = 'paid'
    ORDER BY p.paid_at DESC 
    LIMIT 20
");
while ($row = $res2->fetch_assoc()) {
    $payments[] = $row;
}

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
            <a href="manage_doctors.php"><i class="fas fa-user-md"></i> Doctor Profiles</a>
            <a href="admin_reports.php" class="active"><i class="fas fa-chart-line"></i> System Reports</a>
            <div class="dash-nav-divider"></div>
            <a href="logout.php" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Financial & Operations Reports</h1>
                <p>View transaction logs and operational statistics.</p>
            </div>
        </div>

        <div class="stat-grid">
            <div class="stat-widget" style="border-bottom: 3px solid #e8a838;">
                <div class="stat-info">
                    <strong><?= $appStats['pending'] ?></strong>
                    <span>Pending Approval</span>
                </div>
            </div>
            <div class="stat-widget" style="border-bottom: 3px solid #0056b3;">
                <div class="stat-info">
                    <strong><?= $appStats['confirmed'] ?></strong>
                    <span>Confirmed & Paid</span>
                </div>
            </div>
            <div class="stat-widget" style="border-bottom: 3px solid #28a745;">
                <div class="stat-info">
                    <strong><?= $appStats['completed'] ?></strong>
                    <span>Consultations Completed</span>
                </div>
            </div>
            <div class="stat-widget" style="border-bottom: 3px solid #dc3545;">
                <div class="stat-info">
                    <strong><?= $appStats['cancelled'] ?></strong>
                    <span>Cancelled</span>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Recent Transactions Log</h2>
            </div>
            <div class="table-wrap">
                <?php if (empty($payments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <p>No financial transactions recorded yet.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Transaction Ref</th>
                                <th>Date & Time</th>
                                <th>Patient Name</th>
                                <th>Provider</th>
                                <th>Payment Method</th>
                                <th>Amount Logged</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><code style="background: #f1f3f5; padding: 4px 8px; border-radius: 4px;"><?= htmlspecialchars($p['transaction_ref']) ?></code></td>
                                    <td><?= format_date($p['paid_at']) ?></td>
                                    <td><?= htmlspecialchars($p['pat_first'] . ' ' . $p['pat_last']) ?></td>
                                    <td>Dr. <?= htmlspecialchars($p['doc_last']) ?></td>
                                    <td><?= htmlspecialchars($p['payment_method']) ?></td>
                                    <td style="color: #28a745; font-weight: 700;">LKR <?= number_format($p['amount'], 2) ?></td>
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