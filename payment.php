<?php
require_once 'functions.php';
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
        $ref    = 'TXN-' . strtoupper(uniqid());

        if (create_or_update_payment($appId, $amount, $method, $ref)) {
            if ($appointment['status'] === 'pending') {
                update_appointment_status($appId, 'confirmed');
            }
            $successMsg = 'Payment successful! Your transaction reference is ' . $ref;
            $existingPayment = fetch_payment_for_appointment($appId);
        } else {
            $errorMsg = 'Payment failed to process. Please try again.';
        }
    } else {
        $errorMsg = 'Invalid session. Please refresh and try again.';
    }
}

include 'header.php';
?>

<style>
    /* Checkout UI Styles */
    body {
        background-color: #f8f9fa;
    }

    .checkout-wrapper {
        max-width: 1000px;
        margin: 60px auto;
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 40px;
        font-family: 'Segoe UI', system-ui, sans-serif;
    }

    .checkout-form-container {
        background: #fff;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .checkout-header {
        margin-bottom: 30px;
    }

    .checkout-header h2 {
        margin: 0 0 10px 0;
        color: #2c3e50;
        font-size: 28px;
    }

    .checkout-header p {
        color: #6c757d;
        margin: 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        color: #495057;
        font-weight: 600;
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid #ced4da;
        border-radius: 8px;
        font-size: 15px;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .form-control:focus {
        outline: none;
        border-color: #0d7377;
        box-shadow: 0 0 0 3px rgba(13, 115, 119, 0.1);
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .btn-pay {
        width: 100%;
        background: #0d7377;
        color: white;
        border: none;
        padding: 16px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }

    .btn-pay:hover {
        background: #095053;
    }

    .summary-container {
        background: linear-gradient(145deg, #2c3e50, #1a252f);
        color: white;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        height: fit-content;
    }

    .summary-title {
        font-size: 18px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 0;
        margin-bottom: 30px;
        color: #adb5bd;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 15px;
    }

    .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        font-size: 15px;
        color: #e9ecef;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 24px;
        font-weight: bold;
        color: #fff;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .secure-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: #6c757d;
        font-size: 13px;
        margin-top: 20px;
    }
</style>

<div class="checkout-wrapper">
    <!-- Left Column: Payment Form -->
    <div class="checkout-form-container">
        <div class="checkout-header">
            <h2>Payment Details</h2>
            <p>Complete your booking securely via Stripe-mock gateway.</p>
        </div>

        <?php if ($successMsg): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                <div><?= htmlspecialchars($successMsg) ?></div>
            </div>
            <a href="dashboard_patient.php" class="btn-pay" style="text-decoration:none; text-align:center; display:block;">Return to Dashboard</a>
        <?php elseif ($errorMsg): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle" style="font-size: 20px;"></i>
                <div><?= htmlspecialchars($errorMsg) ?></div>
            </div>
        <?php endif; ?>

        <?php if (!isset($success) && (!$existingPayment || $existingPayment['status'] !== 'paid') && !$successMsg): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                <div class="form-group">
                    <label class="form-label">Name on Card</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Card Number</label>
                    <div style="position: relative;">
                        <input type="text" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" style="padding-left: 45px;" required>
                        <i class="far fa-credit-card" style="position: absolute; left: 15px; top: 16px; color: #adb5bd; font-size: 18px;"></i>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Expiry Date</label>
                        <input type="text" class="form-control" placeholder="MM/YY" maxlength="5" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">CVC</label>
                        <input type="text" class="form-control" placeholder="123" maxlength="4" required>
                    </div>
                </div>

                <button type="submit" class="btn-pay">
                    <i class="fas fa-lock"></i> Pay LKR <?= number_format($appointment['consultation_fee'], 2) ?>
                </button>

                <div class="secure-badge">
                    <i class="fas fa-shield-alt"></i> Payments are 256-bit encrypted and secure
                </div>
            </form>
        <?php elseif (isset($success) || ($existingPayment && $existingPayment['status'] === 'paid')): ?>
            <div style="text-align: center; padding: 40px 0;">
                <i class="fas fa-check-circle" style="font-size: 60px; color: #28a745; margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 10px; color: #2c3e50;">Payment Already Completed</h3>
                <p style="color: #6c757d; margin-bottom: 30px;">This appointment has been fully paid and confirmed.</p>
                <a href="dashboard_patient.php" class="btn-outline" style="padding: 10px 20px; border-radius: 6px; text-decoration:none; font-weight:600;">Back to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Order Summary -->
    <div class="summary-container">
        <h3 class="summary-title">Consultation Summary</h3>

        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px;">
            <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold;">
                <?= strtoupper(substr($appointment['doc_first'], 0, 1) . substr($appointment['doc_last'], 0, 1)) ?>
            </div>
            <div>
                <strong style="display: block; font-size: 18px;">Dr. <?= htmlspecialchars($appointment['doc_first'] . ' ' . $appointment['doc_last']) ?></strong>
                <span style="color: #adb5bd; font-size: 14px;"><?= htmlspecialchars($appointment['specialization']) ?></span>
            </div>
        </div>

        <div class="summary-item">
            <span>Date & Time</span>
            <span style="font-weight: 500;"><?= format_date($appointment['appointment_dt']) ?></span>
        </div>
        <div class="summary-item">
            <span>Status</span>
            <span style="font-weight: 500; text-transform: capitalize; color: #e8a838;"><?= htmlspecialchars($appointment['status']) ?></span>
        </div>

        <div class="summary-total">
            <span>Total</span>
            <span>LKR <?= number_format($appointment['consultation_fee'], 2) ?></span>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>