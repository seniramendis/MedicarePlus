<?php
require_once 'auth.php';

$pageTitle = 'Medical Reports — Medicare Plus';
$user      = get_logged_in_user();
$role      = $_SESSION['role'];

$reports = [];
$uploadSuccess = '';
$uploadError   = '';

if ($role === 'patient') {
    $patient = fetch_patient_by_user_id($user['id']);
    if ($patient) {
        $reports = fetch_reports_for_patient($patient['id']);
    }
} elseif ($role === 'doctor') {
    // Doctors can view reports if a patient ID is passed
    $patientId = filter_input(INPUT_GET, 'patient_id', FILTER_VALIDATE_INT);
    if ($patientId) {
        $reports = fetch_reports_for_patient($patientId);
    }

    // Handle Upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['report_file'])) {
        if (csrf_verify($_POST['csrf_token'] ?? '')) {
            $pId   = (int)$_POST['target_patient_id'];
            $appId = !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null;
            $desc  = trim($_POST['description'] ?? '');

            $file     = $_FILES['report_file'];
            $fileName = basename($file['name']);
            $targetDir = __DIR__ . '/uploads/';

            // Ensure uploads directory exists
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

            $targetPath = $targetDir . time() . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName);
            $dbPath     = 'uploads/' . basename($targetPath);

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                if (save_report($pId, $appId, $user['id'], $fileName, $dbPath, $desc)) {
                    $uploadSuccess = 'Report uploaded successfully.';
                    $reports = fetch_reports_for_patient($pId); // Refresh
                } else {
                    $uploadError = 'Database error. Could not save report record.';
                }
            } else {
                $uploadError = 'Failed to upload the file to the server.';
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
                <p><?= $role === 'patient' ? 'View and download your digital medical records.' : 'Manage reports for your patients.' ?></p>
            </div>
            <?php if ($role === 'doctor' && isset($patientId)): ?>
                <button class="btn btn-primary" onclick="document.getElementById('uploadForm').style.display='block'">
                    <i class="fas fa-upload"></i> Upload Report
                </button>
            <?php endif; ?>
        </div>

        <?php if ($uploadSuccess): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle" style="margin-top:2px;"></i>
                <div><?= htmlspecialchars($uploadSuccess) ?></div>
            </div>
        <?php endif; ?>
        <?php if ($uploadError): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle" style="margin-top:2px;"></i>
                <div><?= htmlspecialchars($uploadError) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($role === 'doctor' && isset($patientId)): ?>
            <div id="uploadForm" class="card" style="display:none; margin-bottom: 24px; border-left: 4px solid var(--teal);">
                <div class="card-header">
                    <h2 class="card-title">Upload New Record</h2>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="target_patient_id" value="<?= htmlspecialchars($patientId) ?>">

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label>Select File (PDF, JPG, PNG)</label>
                            <input type="file" name="report_file" class="form-control" required style="padding: 8px;">
                        </div>
                        <div class="form-group">
                            <label>Linked Appointment ID (Optional)</label>
                            <input type="number" name="appointment_id" class="form-control" placeholder="e.g. 1042">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description / Diagnosis Notes</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Blood test results, X-ray analysis..."></textarea>
                    </div>
                    <div style="display:flex; gap: 10px;">
                        <button type="submit" class="btn btn-primary">Save Record</button>
                        <button type="button" class="btn btn-outline" onclick="document.getElementById('uploadForm').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="table-wrap">
                <?php if (empty($reports)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No Records Found</h3>
                        <p><?= $role === 'patient' ? 'Your doctors have not uploaded any medical reports yet.' : 'Please select a patient from the My Patients tab to view or upload records.' ?></p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date Added</th>
                                <th>File Name</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $rep): ?>
                                <tr>
                                    <td style="white-space: nowrap;"><?= format_date($rep['created_at']) ?></td>
                                    <td><i class="fas fa-file-pdf" style="color:#e74c3c; margin-right:6px;"></i> <?= htmlspecialchars($rep['file_name']) ?></td>
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
    </main>
</div>

<?php include 'footer.php'; ?>