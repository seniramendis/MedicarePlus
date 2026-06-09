<?php
require_once 'functions.php';

$user = get_logged_in_user();
if (!$user) {
    header('Location: Login.php');
    exit;
}

$role = $_SESSION['role'];
$pageTitle = 'Secure Messages — Medicare Plus';
$conn = get_db_connection();

$activeChatId = filter_input(INPUT_GET, 'chat_with', FILTER_VALIDATE_INT);
$msg = '';

// Handle Sending a Quick Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'], $_POST['receiver_id'])) {
    if (csrf_verify($_POST['csrf_token'] ?? '')) {
        $receiverId = (int)$_POST['receiver_id'];
        $replyBody  = trim($_POST['reply_message']);

        if (!empty($replyBody)) {
            $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
            $stmt->bind_param('iis', $user['id'], $receiverId, $replyBody);
            $stmt->execute();
            $stmt->close();

            // Auto-refresh the page to show the new message
            header("Location: chat_engine.php?chat_with=" . $receiverId);
            exit;
        }
    }
}

// Fetch all distinct users the logged-in user has conversed with
$contacts = [];
$stmt = $conn->prepare("
    SELECT DISTINCT u.id, u.first_name, u.last_name, u.role 
    FROM users u 
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id) 
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
");
$stmt->bind_param('iii', $user['id'], $user['id'], $user['id']);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $contacts[] = $row;
}
$stmt->close();

// Fetch active conversation if a chat is selected
$conversation = [];
$activeContact = null;
if ($activeChatId) {
    // Mark messages as read
    $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $stmt->bind_param('ii', $activeChatId, $user['id']);
    $stmt->execute();
    $stmt->close();

    // Fetch the contact's details
    $stmt = $conn->prepare("SELECT first_name, last_name, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $activeChatId);
    $stmt->execute();
    $activeContact = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch messages
    $stmt = $conn->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    $stmt->bind_param('iiii', $user['id'], $activeChatId, $activeChatId, $user['id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $conversation[] = $row;
    }
    $stmt->close();
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
            <span><?= ucfirst($role) ?> Portal</span>
        </div>
        <nav class="dash-nav">
            <a href="dashboard_<?= $role === 'admin' ? 'admin' : $role ?>.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="chat_engine.php" class="active"><i class="fas fa-comments"></i> Secure Inbox</a>
            <a href="compose_message.php"><i class="fas fa-paper-plane"></i> Compose Message</a>
            <div class="dash-nav-divider"></div>
            <a href="logout.php" style="color: #dc3545;"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Secure Messaging</h1>
                <p>Direct communication portal for patients and healthcare providers.</p>
            </div>
            <a href="compose_message.php" class="btn btn-primary"><i class="fas fa-edit"></i> New Message</a>
        </div>

        <div class="grid-2" style="grid-template-columns: 1fr 2fr;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><i class="fas fa-inbox"></i> Active Conversations</h2>
                </div>
                <div style="padding: 10px 0;">
                    <?php if (empty($contacts)): ?>
                        <div style="padding: 20px; text-align: center; color: var(--muted);">
                            <p>No messages yet.</p>
                        </div>
                    <?php else: ?>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach ($contacts as $c): ?>
                                <li>
                                    <a href="chat_engine.php?chat_with=<?= $c['id'] ?>" style="display: block; padding: 15px 20px; text-decoration: none; border-bottom: 1px solid var(--border); background: <?= $activeChatId === (int)$c['id'] ? 'rgba(13,115,119,0.05)' : 'transparent' ?>; color: var(--dark); transition: 0.2s;">
                                        <strong style="display: block; font-size: 1.1rem;">
                                            <?= $c['role'] === 'doctor' ? 'Dr. ' : '' ?><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
                                        </strong>
                                        <span style="font-size: 0.85rem; color: var(--muted); text-transform: capitalize;"><?= htmlspecialchars($c['role']) ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card" style="display: flex; flex-direction: column; height: 600px;">
                <?php if ($activeChatId && $activeContact): ?>
                    <div class="card-header" style="background: rgba(13,115,119,0.05); border-bottom: 1px solid var(--border);">
                        <h2 class="card-title">
                            Conversation with <?= $activeContact['role'] === 'doctor' ? 'Dr. ' : '' ?><?= htmlspecialchars($activeContact['first_name'] . ' ' . $activeContact['last_name']) ?>
                        </h2>
                    </div>

                    <div style="flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; background: #fafbfc;">
                        <?php foreach ($conversation as $msg):
                            $isMe = $msg['sender_id'] === $user['id'];
                        ?>
                            <div style="max-width: 75%; padding: 12px 18px; border-radius: 12px; font-size: 0.95rem; line-height: 1.5; align-self: <?= $isMe ? 'flex-end' : 'flex-start' ?>; background: <?= $isMe ? 'var(--primary)' : '#fff' ?>; color: <?= $isMe ? '#fff' : 'var(--dark)' ?>; border: <?= $isMe ? 'none' : '1px solid var(--border)' ?>; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                                <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                <div style="font-size: 0.7rem; margin-top: 5px; text-align: right; color: <?= $isMe ? 'rgba(255,255,255,0.7)' : 'var(--muted)' ?>;">
                                    <?= date('M d, g:i A', strtotime($msg['created_at'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="padding: 15px; border-top: 1px solid var(--border); background: #fff;">
                        <form method="POST" style="display: flex; gap: 10px; margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="receiver_id" value="<?= $activeChatId ?>">
                            <input type="text" name="reply_message" placeholder="Type your secure message here..." required style="flex: 1; padding: 12px 15px; border: 1px solid var(--border); border-radius: 20px; outline: none; font-family: inherit;">
                            <button type="submit" class="btn btn-primary" style="border-radius: 20px; padding: 0 20px;"><i class="fas fa-paper-plane"></i> Send</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div style="flex: 1; display: flex; align-items: center; justify-content: center; flex-direction: column; color: var(--muted); padding: 40px; text-align: center;">
                        <i class="fas fa-comments" style="font-size: 4rem; opacity: 0.2; margin-bottom: 20px;"></i>
                        <h3>No Conversation Selected</h3>
                        <p>Select a contact from the list on the left to view your message history, or compose a new message.</p>
                        <a href="compose_message.php" class="btn btn-primary" style="margin-top: 15px;">Compose New</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>