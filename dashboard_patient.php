<?php
require_once 'auth.php';
require_role('patient');

$pageTitle = 'Patient Dashboard — Medicare Plus';
$user      = get_logged_in_user();
$patient   = fetch_patient_by_user_id($user['id']);

// Auto-create patient profile if missing
if (!$patient) {
    create_patient_profile($user['id']);
    $patient = fetch_patient_by_user_id($user['id']);
}

// Fetch data using your new functions.php
$allAppointments = fetch_appointments_for_patient($patient['id']);
$upcomingAppointments = [];
$completedAppointments = [];

foreach ($allAppointments as $appt) {
    if ($appt['status'] === 'completed') {
        $completedAppointments[] = $appt;
    } else {
        $upcomingAppointments[] = $appt;
    }
}

// Notifications logic instead of old messaging system
$unreadCount = get_unread_count($user['id']);
$notifications = fetch_notifications($user['id'], 5);

include 'header.php';
?>

<div class="dash-layout">
    <aside class="dash-sidebar">
        <div class="dash-user">
            <div class="dash-avatar">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
            <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
            <span>Patient</span>
        </div>
        <nav class="dash-nav">
            <a href="dashboard_patient.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
            <a href="medical_reports.php"><i class="fas fa-file-medical"></i> Medical Records</a>
            <div class="dash-nav-divider"></div>
            <a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
            <a href="logout.php" style="color:#e74c3c"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Welcome back, <?= htmlspecialchars($user['first_name']) ?>! 👋</h1>
                <p>Manage your appointments, medical records, and notifications from one secure place.</p>
            </div>
            <a href="book_appointment.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Appointment
            </a>
        </div>

        <div class="stat-grid">
            <div class="stat-widget">
                <div class="stat-icon teal"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <strong><?= count($upcomingAppointments) ?></strong>
                    <span>Upcoming</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon amber"><i class="fas fa-bell"></i></div>
                <div class="stat-info">
                    <strong><?= $unreadCount ?></strong>
                    <span>Unread Alerts</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon green"><i class="fas fa-file-medical"></i></div>
                <div class="stat-info">
                    <strong style="font-size: 1.2rem;"><a href="medical_reports.php" style="color: inherit;">View</a></strong>
                    <span>Medical Reports</span>
                </div>
            </div>
            <div class="stat-widget">
                <div class="stat-icon red"><i class="fas fa-history"></i></div>
                <div class="stat-info">
                    <strong><?= count($completedAppointments) ?></strong>
                    <span>Past Visits</span>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-calendar-alt" style="color:var(--teal)"></i> Upcoming Appointments</h2>
            </div>
            <div class="table-wrap">
                <?php if (empty($upcomingAppointments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-plus"></i>
                        <h3>No upcoming appointments</h3>
                        <p>You haven't booked any future consultations yet.</p>
                        <a href="book_appointment.php" class="btn btn-primary btn-sm">Book Now</a>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Specialty</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($upcomingAppointments, 0, 5) as $app): ?>
                                <tr>
                                    <td><strong>Dr. <?= htmlspecialchars($app['doc_first'] . ' ' . $app['doc_last']) ?></strong></td>
                                    <td><?= htmlspecialchars($app['specialization']) ?></td>
                                    <td><?= format_date($app['appointment_dt']) ?></td>
                                    <td><?= status_badge($app['status']) ?></td>
                                    <td>
                                        <?php if ($app['status'] === 'confirmed'): ?>
                                            <a href="payment.php?id=<?= $app['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-credit-card"></i> Pay Now</a>
                                        <?php else: ?>
                                            <span style="color:var(--muted);font-size:0.85rem">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-check-circle" style="color:var(--leaf)"></i> Completed</h2>
                </div>
                <div class="table-wrap">
                    <?php if (empty($completedAppointments)): ?>
                        <p style="color:var(--muted); font-size:0.9rem; padding: 10px;">No past appointments.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($completedAppointments, 0, 4) as $app): ?>
                                    <tr>
                                        <td><strong>Dr. <?= htmlspecialchars($app['doc_last']) ?></strong></td>
                                        <td><?= format_date($app['appointment_dt']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-bell" style="color:var(--accent-dark)"></i> Recent Alerts</h2>
                </div>
                <div class="table-wrap">
                    <?php if (empty($notifications)): ?>
                        <p style="color:var(--muted); font-size:0.9rem; padding: 10px;">No new notifications.</p>
                    <?php else: ?>
                        <table>
                            <tbody>
                                <?php foreach ($notifications as $notif): ?>
                                    <tr>
                                        <td style="font-size:0.9rem; <?= $notif['is_read'] ? 'color:var(--muted);' : 'font-weight:600; color:var(--dark);' ?>">
                                            <?= htmlspecialchars($notif['message']) ?>
                                        </td>
                                        <td style="font-size:0.75rem; color:var(--muted); text-align:right;">
                                            <?= date('M j', strtotime($notif['created_at'])) ?>
                                        </td>
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