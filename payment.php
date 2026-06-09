<?php
require_once 'auth.php';
require_role('patient');

$pageTitle = 'Secure Checkout — Medicare Plus';
$appId     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user      = get_logged_in_user();
$patient   = fetch_patient_by_user_id($user['id']);

if (!$appId || !$patient) {
    header('Location: dashboard_patient.php');
    exit;
}

// Verify this appointment belongs to the patient
$conn = get_db_connection();
$stmt = $conn->prepare(
    "SELECT a.*, d.consultation_fee, u.first_name AS doc_first, u.last_name AS doc_last, d.specialization 
     FROM appointments a 
     JOIN doctors d ON a.doctor_id = d.id 
     JOIN users u ON d.user_id = u.id 
     WHERE a.id = ? AND a.patient_id = ? LIMIT 1"
);
$stmt->bind_param('ii', $appId, $patient['id']);
$stmt->execute();
$appointment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$appointment) {
    header('Location: dashboard_patient.php');
    exit;
}

$existingPayment = fetch_payment_for_appointment($appId);
if ($existingPayment && $existingPayment['status'] === 'paid') {
    $success = 'This appointment has already been paid for.';
}

$successMsg = '';
$errorMsg   = '';

// Mock Payment Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($success)) {
    if (csrf_verify($_POST['csrf_token'] ?? '')) {
        $amount = (float)$appointment['consultation_fee'];
        $method = $_POST['payment_method'] ?? 'Card';
        $ref    = 'TXN-' . strtoupper(uniqid()); // Mock transaction ID

        if (create_or_update_payment($appId, $amount, $method, $ref)) {
            // Auto-confirm the appointment if it was pending
            if ($appointment['status'] === 'pending') {
                update_appointment_status($appId, 'confirmed');
            }
            $successMsg = 'Payment successful! Your transaction reference is ' . $ref;
            $existingPayment = fetch_payment_for_appointment($appId); // Refresh state
        } else {
            $errorMsg = 'Payment failed to process. Please try again.';
        }
    } else {
        $errorMsg = 'Invalid session. Please refresh and try again.';
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
            <span>Patient</span>
        </div>
        <nav class="dash-nav">
            <a href="dashboard_patient.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="book_appointment.php"><i class="fas fa-calendar-plus"></i> Book Appointment</a>
            <a href="medical_reports.php"><i class="fas fa-file-medical"></i> Medical Records</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Checkout</h1>
                <p>Complete your payment to confirm the consultation.</p>
            </div>
            <a href="dashboard_patient.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?php if ($successMsg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle" style="margin-top:2px;"></i>
                <div><?= htmlspecialchars($successMsg) ?></div>
            </div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle" style="margin-top:2px;"></i>
                <div><?= htmlspecialchars($errorMsg) ?></div>
            </div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns: 1fr 380px; gap: 28px;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-credit-card" style="color:var(--teal)"></i> Payment Details</h2>
                </div>

                <?php if (isset($success) || ($existingPayment && $existingPayment['status'] === 'paid')): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle" style="color: #27ae60;"></i>
                        <h3>Payment Completed</h3>
                        <p>Your consultation fee of LKR <?= number_format($appointment['consultation_fee'], 2) ?> has been paid.</p>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                        <div class="form-group">
                            <label>Cardholder Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Card Number</label>
                            <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" required>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div class="form-group">
                                <label>Expiry Date</label>
                                <input type="text" class="form-control" placeholder="MM/YY" maxlength="5" required>
                            </div>
                            <div class="form-group">
                                <label>CVV</label>
                                <input type="text" class="form-control" placeholder="123" maxlength="3" required>
                            </div>
                        </div>

                        <div style="background: rgba(13,115,119,.05); border-radius: var(--radius); padding: 16px; margin-bottom: 20px;">
                            <div style="display:flex; align-items:center; gap: 10px; font-size: 0.85rem; color: var(--mid);">
                                <i class="fas fa-lock" style="color: var(--teal);"></i>
                                Your payment information is encrypted and securely processed.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">
                            Pay LKR <?= number_format($appointment['consultation_fee'], 2) ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="card" style="align-self: start;">
                <div class="card-header">
                    <h2 class="card-title">Order Summary</h2>
                </div>
                <div style="display:flex; flex-direction:column; gap: 16px;">
                    <div style="display:flex; align-items:center; gap: 12px; border-bottom: 1px solid var(--border); padding-bottom: 16px;">
                        <div class="dash-avatar" style="width:48px; height:48px; font-size:1rem; margin:0; background:rgba(13,115,119,.1); color:var(--teal); border:none;">
                            <?= strtoupper(substr($appointment['doc_first'], 0, 1) . substr($appointment['doc_last'], 0, 1)) ?>
                        </div>
                        <div>
                            <strong style="color:var(--dark); display:block;">Dr. <?= htmlspecialchars($appointment['doc_first'] . ' ' . $appointment['doc_last']) ?></strong>
                            <span style="font-size:0.8rem; color:var(--muted);"><?= htmlspecialchars($appointment['specialization']) ?></span>
                        </div>
                    </div>

                    <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                        <span style="color:var(--mid);">Date & Time:</span>
                        <strong style="color:var(--dark);"><?= format_date($appointment['appointment_dt']) ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:0.9rem;">
                        <span style="color:var(--mid);">Status:</span>
                        <?= status_badge($appointment['status']) ?>
                    </div>

                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px dashed var(--border); display:flex; justify-content:space-between; align-items:center;">
                        <span style="color:var(--mid); font-weight:600;">Total Due</span>
                        <strong style="font-family:var(--font-display); font-size:1.4rem; color:var(--teal-dark);">LKR <?= number_format($appointment['consultation_fee'], 0) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>