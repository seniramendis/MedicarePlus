<?php
require_once 'functions.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: Login.php');
    exit;
}

$user = get_logged_in_user();
$role = $_SESSION['role'];
$pageTitle = 'Medical Reports — Medicare Plus';

$reports = [];
$uploadMsg = '';

// Role-based data fetching
if ($role === 'patient') {
    $patient = fetch_patient_by_user_id($user['id']);
    if ($patient) {
        $reports = fetch_reports_for_patient($patient['id']);
    }
} elseif ($role === 'doctor') {
    $doctor = fetch_doctor_by_user_id($user['id']);

    // Fetch patients this doctor has seen for the dropdown
    $conn = get_db_connection();
    $patientsList = [];
    $stmt = $conn->prepare("SELECT DISTINCT p.id, u.first_name, u.last_name FROM patients p JOIN appointments a ON p.id = a.patient_id JOIN users u ON u.id = p.user_id WHERE a.doctor_id = ?");
    $stmt->bind_param('i', $doctor['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $patientsList[] = $row;
    }
    $stmt->close();

    // Handle Upload Logic
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['report_file'])) {
        if (csrf_verify($_POST['csrf_token'] ?? '')) {
            $pId   = (int)$_POST['target_patient_id'];
            $desc  = trim($_POST['description'] ?? '');

            $file     = $_FILES['report_file'];
            $fileName = basename($file['name']);
            $targetDir = __DIR__ . '/uploads/reports/';

            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $targetPath = $targetDir . time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName);
            $dbPath     = 'uploads/reports/' . basename($targetPath);

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                if (save_report($pId, null, $user['id'], $fileName, $dbPath, $desc)) {
                    $uploadMsg = "Report successfully uploaded.";
                } else {
                    $uploadMsg = "Database error. Could not save record.";
                }
            } else {
                $uploadMsg = "Failed to upload the file to the server.";
            }
        }
    }
}

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
            <span><?= ucfirst($role) ?> Portal</span>
        </div>
        <nav class="dash-nav">
            <a href="dashboard_<?= $role ?>.php"><i class="fas fa-home"></i> Dashboard Overview</a>
            <?php if ($role === 'patient'): ?>
                <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
                <a href="medical_reports.php" class="active"><i class="fas fa-folder-open"></i> Medical Records</a>
            <?php else: ?>
                <a href="appointments.php"><i class="fas fa-calendar-alt"></i> My Schedule</a>
                <a href="medical_reports.php" class="active"><i class="fas fa-file-medical"></i> Patient Reports</a>
            <?php endif; ?>
            <div class="dash-nav-divider"></div>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Medical Records</h1>
                <p><?= $role === 'patient' ? 'View and download your clinical documents.' : 'Upload clinical reports to patient charts.' ?></p>
            </div>
        </div>

        <?php if ($uploadMsg): ?>
            <div class="card" style="margin-bottom: 20px; padding: 15px; border-left: 4px solid var(--primary);">
                <strong><?= htmlspecialchars($uploadMsg) ?></strong>
            </div>
        <?php endif; ?>

        <?php if ($role === 'doctor'): ?>
            <div class="card" style="margin-bottom: 30px;">
                <div class="card-header">
                    <h2 class="card-title">Upload New Record</h2>
                </div>
                <div style="padding: 20px;">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom: 5px;">Select Patient</label>
                            <select name="target_patient_id" class="form-control" required style="width: 100%; padding: 10px;">
                                <option value="">-- Choose Patient --</option>
                                <?php foreach ($patientsList as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom: 5px;">Document File (PDF, JPG)</label>
                            <input type="file" name="report_file" class="form-control" required style="width: 100%; padding: 10px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display:block; margin-bottom: 5px;">Clinical Notes</label>
                            <textarea name="description" class="form-control" rows="3" style="width: 100%; padding: 10px;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Upload Record</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($role === 'patient'): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Your File History</h2>
                </div>
                <div class="table-wrap">
                    <?php if (empty($reports)): ?>
                        <div style="padding: 30px; text-align: center;">
                            <p>No medical records have been uploaded yet.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date Logged</th>
                                    <th>Document Title</th>
                                    <th>Clinical Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $rep): ?>
                                    <tr>
                                        <td><?= format_date($rep['created_at']) ?></td>
                                        <td><strong><?= htmlspecialchars($rep['file_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($rep['description']) ?></td>
                                        <td>
                                            <a href="<?= htmlspecialchars($rep['file_path']) ?>" download class="btn btn-primary">Download</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php include 'footer.php'; ?>