<?php
require_once 'functions.php';
require_role('patient');

$appId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user = get_logged_in_user();
$patient = fetch_patient_by_user_id($user['id']);

if (!$appId || !$patient) {
    header('Location: dashboard_patient.php');
    exit;
}

// Fetch appointment to ensure it belongs to the user
$conn = get_db_connection();
$stmt = $conn->prepare(
    "SELECT a.*, d.consultation_fee, u.last_name AS doc_last 
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

$message = '';
$payment = fetch_payment_for_appointment($appId);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!$payment || $payment['status'] !== 'paid')) {
    if (csrf_verify($_POST['csrf_token'] ?? '')) {
        $amount = (float)$appointment['consultation_fee'];
        $ref = 'TXN' . rand(100000, 999999); // Generate secure mock ref

        if (create_or_update_payment($appId, $amount, 'Credit Card', $ref)) {
            update_appointment_status($appId, 'confirmed');
            add_notification($user['id'], "Payment of LKR $amount received for Dr. {$appointment['doc_last']}. Ref: $ref");
            $message = "<div class='success-banner'>Payment Successfully Processed. Reference: $ref</div>";
            $payment = fetch_payment_for_appointment($appId); // Refresh
        } else {
            $message = "<div class='error-banner'>Payment processing failed. Please try again.</div>";
        }
    }
}

include 'header.php';
?>

<style>
    .billing-container {
        max-width: 600px;
        margin: 4rem auto;
        font-family: Arial, sans-serif;
    }

    .invoice-card {
        background: #fff;
        border: 1px solid #ccc;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .invoice-header {
        text-align: center;
        border-bottom: 2px solid #0056b3;
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }

    .invoice-header h2 {
        margin: 0;
        color: #333;
    }

    .invoice-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid #eee;
    }

    .invoice-total {
        display: flex;
        justify-content: space-between;
        padding: 1rem 0;
        font-size: 1.2rem;
        font-weight: bold;
        color: #0056b3;
    }

    .secure-form {
        margin-top: 2rem;
        background: #f8f9fa;
        padding: 1.5rem;
        border: 1px solid #ddd;
    }

    .form-control {
        width: 100%;
        padding: 0.6rem;
        margin-bottom: 1rem;
        border: 1px solid #ccc;
        box-sizing: border-box;
    }

    .btn-pay {
        background: #28a745;
        color: white;
        width: 100%;
        padding: 0.8rem;
        border: none;
        font-size: 1.1rem;
        cursor: pointer;
    }

    .btn-pay:hover {
        background: #218838;
    }

    .success-banner {
        background: #d4edda;
        color: #155724;
        padding: 1rem;
        border: 1px solid #c3e6cb;
        margin-bottom: 1rem;
        text-align: center;
    }

    .error-banner {
        background: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border: 1px solid #f5c6cb;
        margin-bottom: 1rem;
        text-align: center;
    }
</style>

<div class="billing-container">
    <?= $message ?>
    <div class="invoice-card">
        <div class="invoice-header">
            <h2>Payment Authorization</h2>
            <p style="color: #666;">MedicarePlus Billing System</p>
        </div>

        <div class="invoice-row">
            <span>Patient Name:</span>
            <span><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
        </div>
        <div class="invoice-row">
            <span>Provider:</span>
            <span>Dr. <?= htmlspecialchars($appointment['doc_last']) ?></span>
        </div>
        <div class="invoice-row">
            <span>Appointment Date:</span>
            <span><?= format_date($appointment['appointment_dt']) ?></span>
        </div>

        <div class="invoice-total">
            <span>Total Amount Due:</span>
            <span>LKR <?= number_format($appointment['consultation_fee'], 2) ?></span>
        </div>

        <?php if ($payment && $payment['status'] === 'paid'): ?>
            <div style="text-align:center; margin-top: 2rem; color: #28a745;">
                <h3 style="margin-bottom:0;">Paid in Full</h3>
                <p>Transaction Ref: <?= htmlspecialchars($payment['transaction_ref']) ?></p>
                <a href="dashboard_patient.php" style="color:#0056b3;">Return to Portal</a>
            </div>
        <?php else: ?>
            <div class="secure-form">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <label>Card Number</label>
                    <input type="text" class="form-control" placeholder="XXXX XXXX XXXX XXXX" required>

                    <div style="display: flex; gap: 1rem;">
                        <div style="flex: 1;">
                            <label>Expiry (MM/YY)</label>
                            <input type="text" class="form-control" placeholder="12/28" required>
                        </div>
                        <div style="flex: 1;">
                            <label>Security Code (CVV)</label>
                            <input type="text" class="form-control" placeholder="123" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-pay">Authorize LKR <?= number_format($appointment['consultation_fee'], 2) ?></button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>