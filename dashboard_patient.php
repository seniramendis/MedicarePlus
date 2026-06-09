<?php
require_once 'functions.php';
require_role('patient');

$user = get_logged_in_user();
$patient = fetch_patient_by_user_id($user['id']);

// Auto-create patient profile if it doesn't exist
if (!$patient) {
    create_patient_profile($user['id']);
    $patient = fetch_patient_by_user_id($user['id']);
}

// Fetch data using your functions
$appointments = fetch_appointments_for_patient($patient['id']);
$unreadCount = get_unread_count($user['id']);
$notifications = fetch_notifications($user['id'], 5);

include 'header.php';
?>

<!-- Fresh Clinical UI Styles -->
<style>
    .clinical-portal {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .portal-header {
        border-bottom: 2px solid #0056b3;
        padding-bottom: 1rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .portal-header h1 {
        color: #0056b3;
        margin: 0;
        font-size: 1.8rem;
    }

    .portal-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 2rem;
    }

    .clinical-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .clinical-card-header {
        background: #f8f9fa;
        padding: 1rem;
        border-bottom: 1px solid #e0e0e0;
        font-weight: bold;
        color: #333;
        display: flex;
        justify-content: space-between;
    }

    .clinical-card-body {
        padding: 1rem;
    }

    .clinical-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .clinical-table th,
    .clinical-table td {
        padding: 0.75rem;
        border-bottom: 1px solid #eee;
    }

    .clinical-table th {
        background: #f1f5f9;
        color: #495057;
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .btn-action {
        background: #0056b3;
        color: white;
        padding: 0.5rem 1rem;
        text-decoration: none;
        border-radius: 3px;
        font-size: 0.9rem;
    }

    .btn-action:hover {
        background: #004494;
    }

    .btn-outline {
        border: 1px solid #0056b3;
        color: #0056b3;
        padding: 0.5rem 1rem;
        text-decoration: none;
        border-radius: 3px;
    }

    .alert-box {
        padding: 0.75rem;
        background: #e2e3e5;
        border-left: 4px solid #6c757d;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }
</style>

<div class="clinical-portal">
    <div class="portal-header">
        <div>
            <h1>Patient Portal</h1>
            <p style="color: #6c757d; margin-top: 0.5rem;">Welcome, <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
        </div>
        <div>
            <a href="book_appointment.php" class="btn-action">+ Schedule Visit</a>
        </div>
    </div>

    <div class="portal-grid">
        <!-- Main Content Area -->
        <div class="portal-main">
            <div class="clinical-card">
                <div class="clinical-card-header">
                    <span>Active Appointments</span>
                </div>
                <div class="clinical-card-body" style="padding: 0;">
                    <?php if (empty($appointments)): ?>
                        <p style="padding: 1rem; color: #666;">No appointments on record.</p>
                    <?php else: ?>
                        <table class="clinical-table">
                            <thead>
                                <tr>
                                    <th>Provider</th>
                                    <th>Date/Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $app): ?>
                                    <tr>
                                        <td>
                                            <strong>Dr. <?= htmlspecialchars($app['doc_last']) ?></strong><br>
                                            <small style="color:#666;"><?= htmlspecialchars($app['specialization']) ?></small>
                                        </td>
                                        <td><?= format_date($app['appointment_dt']) ?></td>
                                        <td><?= status_badge($app['status']) ?></td>
                                        <td>
                                            <?php if ($app['status'] === 'confirmed'): ?>
                                                <a href="payment.php?id=<?= $app['id'] ?>" class="btn-outline">Pay Fee</a>
                                            <?php elseif ($app['status'] === 'completed'): ?>
                                                <a href="medical_reports.php?app_id=<?= $app['id'] ?>" style="color:#0056b3; text-decoration:none;">View Report</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Area -->
        <div class="portal-side">
            <div class="clinical-card">
                <div class="clinical-card-header">
                    <span>Notifications (<?= $unreadCount ?>)</span>
                </div>
                <div class="clinical-card-body">
                    <?php if (empty($notifications)): ?>
                        <p style="color:#666; font-size:0.9rem;">No new alerts.</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="alert-box" style="<?= $notif['is_read'] ? 'opacity: 0.6;' : 'border-color: #0056b3; background: #e6f2ff;' ?>">
                                <?= htmlspecialchars($notif['message']) ?>
                                <div style="font-size: 0.75rem; color: #666; margin-top: 4px;">
                                    <?= date('M d, Y', strtotime($notif['created_at'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="clinical-card">
                <div class="clinical-card-header">
                    <span>Quick Links</span>
                </div>
                <div class="clinical-card-body" style="display:flex; flex-direction:column; gap:0.5rem;">
                    <a href="medical_reports.php" style="color:#0056b3; text-decoration:none;">📂 My Medical Records</a>
                    <a href="profile.php" style="color:#0056b3; text-decoration:none;">⚙️ Account Settings</a>
                    <a href="logout.php" style="color:#dc3545; text-decoration:none;">🚪 Secure Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>