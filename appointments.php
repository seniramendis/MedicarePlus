<?php
require_once 'auth.php';

$user = get_logged_in_user();
$role = $_SESSION['role'];
$pageTitle = 'Appointments — Medicare Plus';

$appointments = [];

if ($role === 'patient') {
    $patient = fetch_patient_by_user_id($user['id']);
    if ($patient) {
        $appointments = fetch_appointments_for_patient($patient['id']);
    }
} elseif ($role === 'doctor') {
    $doctor = fetch_doctor_by_user_id($user['id']);
    if ($doctor) {
        $appointments = fetch_appointments_for_doctor($doctor['id']);
    }
}

// Basic Filtering
$statusFilter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS);
if ($statusFilter && $statusFilter !== 'all') {
    $appointments = array_filter($appointments, function ($a) use ($statusFilter) {
        return $a['status'] === $statusFilter;
    });
}

include 'header.php';
?>

<div class="dash-layout">
    <aside class="dash-sidebar">
        <div class="dash-user">
            <div class="dash-avatar">
                <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
            </div>
            <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
            <span><?= ucfirst($role) ?></span>
        </div>
        <nav class="dash-nav">
            <a href="dashboard_<?= $role ?>.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <?php if ($role === 'patient'): ?>
                <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
                <a href="appointments.php" class="active"><i class="fas fa-history"></i> My Appointments</a>
                <a href="medical_reports.php"><i class="fas fa-file-medical"></i> Medical Records</a>
            <?php else: ?>
                <a href="appointments.php" class="active"><i class="fas fa-calendar-alt"></i> My Schedule</a>
                <a href="patients.php"><i class="fas fa-users"></i> My Patients</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1><?= $role === 'doctor' ? 'Schedule History' : 'Appointment History' ?></h1>
                <p>Complete log of your past and upcoming consultations.</p>
            </div>
            <?php if ($role === 'patient'): ?>
                <a href="book_appointment.php" class="btn btn-primary"><i class="fas fa-plus"></i> Book New</a>
            <?php endif; ?>
        </div>

        <div class="search-bar" style="padding: 12px 24px; margin-bottom: 24px;">
            <span style="font-weight: 600; font-size: 0.9rem; color: var(--dark);">Filter by Status:</span>
            <div style="display:flex; gap: 8px;">
                <?php
                $filters = ['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
                $currentFilter = $statusFilter ?: 'all';
                foreach ($filters as $val => $label):
                    $btnClass = $val === $currentFilter ? 'btn-primary' : 'btn-outline';
                ?>
                    <a href="?status=<?= $val ?>" class="btn <?= $btnClass ?> btn-sm" style="padding: 6px 14px;"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="table-wrap">
                <?php if (empty($appointments)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No records found</h3>
                        <p>There are no appointments matching the current filter.</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th><?= $role === 'patient' ? 'Doctor Name' : 'Patient Name' ?></th>
                                <th>Date & Time</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $app): ?>
                                <tr>
                                    <td>
                                        <?php if ($role === 'patient'): ?>
                                            <strong>Dr. <?= htmlspecialchars($app['doc_first'] . ' ' . $app['doc_last']) ?></strong><br>
                                            <span style="font-size:0.75rem; color:var(--muted);"><?= htmlspecialchars($app['specialization']) ?></span>
                                        <?php else: ?>
                                            <strong><?= htmlspecialchars($app['pat_first'] . ' ' . $app['pat_last']) ?></strong><br>
                                            <span style="font-size:0.75rem; color:var(--muted);"><i class="fas fa-phone"></i> <?= htmlspecialchars($app['pat_phone']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="white-space: nowrap;"><?= format_date($app['appointment_dt']) ?></td>
                                    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($app['notes'] ?? '') ?>">
                                        <?= htmlspecialchars($app['notes'] ?: '-') ?>
                                    </td>
                                    <td><?= status_badge($app['status']) ?></td>
                                    <td>
                                        <?php if ($role === 'patient' && in_array($app['status'], ['pending', 'confirmed'])): ?>
                                            <a href="payment.php?id=<?= $app['id'] ?>" class="btn btn-outline btn-sm">Pay</a>
                                        <?php elseif ($role === 'doctor' && $app['status'] === 'pending'): ?>
                                            <a href="dashboard_doctor.php" class="btn btn-primary btn-sm">Manage</a>
                                        <?php else: ?>
                                            <a href="#" class="btn btn-ghost btn-sm"><i class="fas fa-eye"></i> View</a>
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