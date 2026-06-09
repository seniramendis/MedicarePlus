<?php
require_once 'functions.php';
require_role('patient');

$pageTitle = 'Patient Dashboard — Medicare Plus';
$user      = get_logged_in_user();
$patient   = fetch_patient_by_user_id($user['id']);

// Auto-create patient profile if missing
if (!$patient) {
    create_patient_profile($user['id']);
    $patient = fetch_patient_by_user_id($user['id']);
}

// Fetch data using your functions.php
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

// Notifications logic
$unreadCount = get_unread_count($user['id']);
$notifications = fetch_notifications($user['id'], 5);

include 'header.php';
?>

<div class="dash-layout">
    <!-- Sidebar -->
    <aside class="dash-sidebar">
        <div class="dash-user">
            <div class="dash-avatar">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
            <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
            <span>Patient Portal</span>
        </div>

        <!-- Updated Navigation with Secure Inbox -->
        <nav class="dash-nav">
            <a href="dashboard_patient.php" class="active"><i class="fas fa-home"></i> Dashboard Overview</a>
            <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
            <a href="medical_reports.php"><i class="fas fa-folder-open"></i> Medical Records</a>
            <a href="chat_engine.php"><i class="fas fa-comments"></i> Secure Inbox</a>
            <div class="dash-nav-divider"></div>
            <a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
            <a href="logout.php" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Welcome back, <?= htmlspecialchars($user['first_name']) ?>! 👋</h1>
                <p>Here is what's happening with your health profile today.</p>
            </div>
            <a href="book_appointment.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Consultation
            </a>
        </div>

        <div class="stat-grid">
            <div class="stat-widget">
                <div class="stat-icon teal"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-info">
                    <strong><?= count($upcomingAppointments) ?></strong>
                    <span>Upcoming Visits</span>
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
                <div class="stat-icon red"><i class="fas fa-history"></i></div>
                <div class="stat-info">
                    <strong><?= count($completedAppointments) ?></strong>
                    <span>Past Consultations</span>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h2 class="card-title">Upcoming Appointments</h2>
            </div>
            <div class="table-wrap">
                <?php if (empty($upcomingAppointments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No upcoming appointments</h3>
                        <p>You are all caught up! Book a new visit if you need to see a doctor.</p>
                        <a href="book_appointment.php" class="btn btn-primary btn-sm">Book Now</a>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($upcomingAppointments, 0, 5) as $app): ?>
                                <tr>
                                    <td>
                                        <strong>Dr. <?= htmlspecialchars($app['doc_first'] . ' ' . $app['doc_last']) ?></strong><br>
                                        <span style="font-size: 13px; color: var(--muted);"><?= htmlspecialchars($app['specialization']) ?></span>
                                    </td>
                                    <td><strong><?= format_date($app['appointment_dt']) ?></strong></td>
                                    <td><?= status_badge($app['status']) ?></td>
                                    <td>
                                        <?php if ($app['status'] === 'confirmed'): ?>
                                            <a href="payment.php?id=<?= $app['id'] ?>" class="btn btn-primary btn-sm">Pay Fee</a>
                                        <?php else: ?>
                                            <span style="color: var(--muted);">—</span>
                                        <?php endif; ?>
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