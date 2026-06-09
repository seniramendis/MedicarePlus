<?php
require_once 'functions.php';

$user = get_logged_in_user();
$role = $_SESSION['role'];

if (!$user) {
    header('Location: Login.php');
    exit;
}

$pageTitle = 'Medical Reports — Medicare Plus';
$reports = [];
$uploadMsg = '';
$msgType = '';

// Role-based data fetching
if ($role === 'patient') {
    $patient = fetch_patient_by_user_id($user['id']);
    if ($patient) {
        $reports = fetch_reports_for_patient($patient['id']);
    }
} elseif ($role === 'doctor') {
    $doctor = fetch_doctor_by_user_id($user['id']);

    // Logic: Fetch patients this doctor has seen for the dropdown
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
            $desc  = trim($_POST['description'] ?? 'Clinical documentation');

            $file     = $_FILES['report_file'];
            $fileName = basename($file['name']);
            $targetDir = __DIR__ . '/uploads/reports/';

            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $targetPath = $targetDir . time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName);
            $dbPath     = 'uploads/reports/' . basename($targetPath);

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                if (save_report($pId, null, $user['id'], $fileName, $dbPath, $desc)) {
                    $uploadMsg = 'Report successfully uploaded and attached to the patient.';
                    $msgType = 'success';
                } else {
                    $uploadMsg = 'Database error. Could not save record.';
                    $msgType = 'error';
                }
            } else {
                $uploadMsg = 'Failed to upload the file to the server.';
                $msgType = 'error';
            }
        }
    }
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
                <a href="medical_reports.php" class="active"><i class="fas fa-file-medical"></i> Medical Records</a>
            <?php else: ?>
                <a href="appointments.php"><i class="fas fa-calendar-alt"></i> My Schedule</a>
                <a href="patients.php"><i class="fas fa-users"></i> My Patients</a>
            <?php endif; ?>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Medical Records & Reports</h1>
                <p><?= $role === 'patient' ? 'View and download your digital medical records.' : 'Upload clinical reports directly to patient charts.' ?></p>
            </div>
        </div>

        <?php if ($uploadMsg): ?>
            <div class="alert alert-<?= $msgType ?>">
                <i class="fas <?= $msgType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>" style="margin-top:2px;"></i>
                <div><?= htmlspecialchars($uploadMsg) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($role === 'doctor'): ?>
            <div class="card" style="margin-bottom: 24px; border-left: 4px solid var(--teal);">
                <div class="card-header">
                    <h2 class="card-title">Upload New Record</h2>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label>Select Patient</label>
                            <select name="target_patient_id" class="form-control" required>
                                <option value="">-- Choose Patient --</option>
                                <?php foreach ($patientsList as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Document File (PDF, JPG, PNG)</label>
                            <input type="file" name="report_file" class="form-control" required style="padding: 8px;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Clinical Notes / Diagnosis</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Blood test results, X-ray analysis..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Save to Records</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($role === 'patient'): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Your File History</h2>
                </div>
                <div class="table-wrap">
                    <?php if (empty($reports)): ?>
                        <div class="empty-state">
                            <i class="fas fa-folder-open"></i>
                            <h3>No Records Found</h3>
                            <p>Your doctors have not uploaded any medical reports yet.</p>
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
                                        <td style="white-space: nowrap;"><?= format_date($rep['created_at']) ?></td>
                                        <td><i class="fas fa-file-pdf" style="color:#e74c3c; margin-right:6px;"></i> <strong><?= htmlspecialchars($rep['file_name']) ?></strong></td>
                                        <td style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($rep['description']) ?>">
                                            <?= htmlspecialchars($rep['description'] ?: 'No description') ?>
                                        </td>
                                        <td>
                                            <a href="<?= htmlspecialchars($rep['file_path']) ?>" download class="btn btn-ghost btn-sm">
                                                <i class="fas fa-download"></i> Download
                                            </a>
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