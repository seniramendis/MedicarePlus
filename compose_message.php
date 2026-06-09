<?php
require_once 'functions.php';

$user = get_logged_in_user();
if (!$user) {
    header('Location: Login.php');
    exit;
}

$role = $_SESSION['role'];
$pageTitle = 'Compose Message — Medicare Plus';
$conn = get_db_connection();
$msgAlert = '';

// Form Submission Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_verify($_POST['csrf_token'] ?? '')) {
        $receiverId = (int)$_POST['receiver_id'];
        $messageBody = trim($_POST['message_body']);

        if (!empty($messageBody) && $receiverId > 0) {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param('iis', $user['id'], $receiverId, $messageBody);
            if ($stmt->execute()) {
                $stmt->close();
                // Redirect immediately after sending
                header("Location: chat_engine.php?chat_with=" . $receiverId);
                exit;
            } else {
                $msgAlert = "Message failed to send. Please try again.";
            }
            $stmt->close();
        }
    }
}

// Fetch Allowed Recipients (Includes Admins)
$recipients = [];
if ($role === 'patient') {
    $stmt = $conn->prepare("
        SELECT DISTINCT u.id, u.first_name, u.last_name, d.specialization as sub_text
        FROM users u
        JOIN doctors d ON u.id = d.user_id
        JOIN appointments a ON d.id = a.doctor_id
        JOIN patients p ON a.patient_id = p.id
        WHERE p.user_id = ?
        UNION
        SELECT id, first_name, last_name, 'System Admin' as sub_text
        FROM users WHERE role = 'admin'
    ");
    $stmt->bind_param('i', $user['id']);
} elseif ($role === 'doctor') {
    $stmt = $conn->prepare("
        SELECT DISTINCT u.id, u.first_name, u.last_name, u.phone as sub_text
        FROM users u
        JOIN patients p ON u.id = p.user_id
        JOIN appointments a ON p.id = a.patient_id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE d.user_id = ?
        UNION
        SELECT id, first_name, last_name, 'System Admin' as sub_text
        FROM users WHERE role = 'admin'
    ");
    $stmt->bind_param('i', $user['id']);
} else {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, role as sub_text FROM users WHERE id != ?");
    $stmt->bind_param('i', $user['id']);
}

$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $recipients[] = $row;
}
$stmt->close();

include 'header.php';
?>

<div class="dash-layout">
    <aside class="dash-sidebar">
        <div class="dash-user">
            <div class="dash-avatar"><?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?></div>
            <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
            <span><?= ucfirst($role) ?> Portal</span>
        </div>
        <nav class="dash-nav">
            <a href="dashboard_<?= $role === 'admin' ? 'admin' : $role ?>.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="chat_engine.php"><i class="fas fa-comments"></i> Secure Inbox</a>
            <a href="compose_message.php" class="active"><i class="fas fa-paper-plane"></i> Compose Message</a>
            <div class="dash-nav-divider"></div>
            <a href="logout.php" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Compose Secure Message</h1>
            </div>
            <a href="chat_engine.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Inbox</a>
        </div>

        <?php if ($msgAlert): ?><div class="card" style="padding: 15px; border-left: 4px solid #dc3545;"><strong><?= htmlspecialchars($msgAlert) ?></strong></div><?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">New Message</h2>
            </div>
            <div style="padding: 25px;">
                <form method="POST" action="compose_message.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom: 8px; font-weight: 600;">Select Recipient</label>
                        <select name="receiver_id" class="form-control" required style="width: 100%; padding: 12px;">
                            <option value="">-- Choose a Contact --</option>
                            <?php foreach ($recipients as $r): ?>
                                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?> (<?= htmlspecialchars($r['sub_text']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <label style="display:block; margin-bottom: 8px; font-weight: 600;">Secure Message</label>
                        <textarea name="message_body" class="form-control" rows="8" required style="width: 100%; padding: 15px;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-lock"></i> Send Secure Message</button>
                </form>
            </div>
        </div>
    </main>
</div>
<?php include 'footer.php'; ?>