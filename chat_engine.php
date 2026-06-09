<?php
require_once 'functions.php';
require_role('patient', 'doctor', 'admin'); // Ensure all roles have access

$user = get_logged_in_user();
if (!$user) {
    header('Location: Login.php');
    exit;
}

$role = $_SESSION['role'];
$pageTitle = 'Secure Messages — Medicare Plus';
$conn = get_db_connection();

// 1. DATABASE CHECK
$table_exists = $conn->query("SHOW TABLES LIKE 'messages'");
if ($table_exists->num_rows == 0) {
    die("<h1>Database Error</h1><p>The 'messages' table does not exist. Please run the SQL command to create it.</p>");
}

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
            header("Location: chat_engine.php?chat_with=" . $receiverId);
            exit;
        }
    }
}

// Fetch Contacts (people we have exchanged messages with)
$contacts = [];
$stmt = $conn->prepare("
    SELECT DISTINCT u.id, u.first_name, u.last_name, u.role,
        (SELECT COUNT(*) FROM messages m2
         WHERE m2.sender_id = u.id AND m2.receiver_id = ? AND m2.is_read = 0) AS unread_count,
        (SELECT m3.message FROM messages m3
         WHERE (m3.sender_id = u.id AND m3.receiver_id = ?)
            OR (m3.sender_id = ? AND m3.receiver_id = u.id)
         ORDER BY m3.created_at DESC LIMIT 1) AS last_message,
        (SELECT m4.created_at FROM messages m4
         WHERE (m4.sender_id = u.id AND m4.receiver_id = ?)
            OR (m4.sender_id = ? AND m4.receiver_id = u.id)
         ORDER BY m4.created_at DESC LIMIT 1) AS last_at
    FROM users u
    JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    WHERE (m.sender_id = ? OR m.receiver_id = ?) AND u.id != ?
    ORDER BY last_at DESC
");
$uid = $user['id'];
$stmt->bind_param('iiiiiiii', $uid, $uid, $uid, $uid, $uid, $uid, $uid, $uid);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $contacts[] = $row;
}
$stmt->close();

// Fetch Conversation Thread (if chat_with is set)
$chatWith = isset($_GET['chat_with']) ? (int)$_GET['chat_with'] : 0;
$chatUser = null;
$messages = [];

if ($chatWith > 0) {
    // Get the other user's details
    $stmt = $conn->prepare("SELECT id, first_name, last_name, role FROM users WHERE id = ?");
    $stmt->bind_param('i', $chatWith);
    $stmt->execute();
    $chatUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($chatUser) {
        // Mark messages from them as read
        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
        $stmt->bind_param('ii', $chatWith, $user['id']);
        $stmt->execute();
        $stmt->close();

        // Fetch full conversation
        $stmt = $conn->prepare("
            SELECT m.*, u.first_name, u.last_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE (m.sender_id = ? AND m.receiver_id = ?)
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->bind_param('iiii', $user['id'], $chatWith, $chatWith, $user['id']);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $messages[] = $row;
        }
        $stmt->close();
    }
}

// Fetch all admins for "Message Admin" quick-action (for patients & doctors)
$admins = [];
if ($role !== 'admin') {
    $res = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'admin' LIMIT 5");
    while ($row = $res->fetch_assoc()) {
        $admins[] = $row;
    }
}

include 'header.php';
?>

<style>
    .chat-layout {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 20px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid var(--border, #eee);
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        transition: background 0.15s;
    }

    .contact-item:hover,
    .contact-item.active {
        background: rgba(13, 115, 119, 0.08);
    }

    .contact-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(13, 115, 119, 0.2);
        color: var(--teal, #0d7377);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .contact-avatar.admin-av {
        background: rgba(232, 168, 56, 0.2);
        color: #c98a00;
    }

    .contact-info {
        flex: 1;
        min-width: 0;
    }

    .contact-info strong {
        display: block;
        font-size: 0.92rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .contact-info span {
        font-size: 0.78rem;
        color: var(--muted, #888);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }

    .unread-badge {
        background: var(--teal, #0d7377);
        color: #fff;
        border-radius: 12px;
        padding: 1px 7px;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .msg-bubble {
        max-width: 70%;
        padding: 10px 14px;
        border-radius: 12px;
        margin-bottom: 10px;
        line-height: 1.5;
        font-size: 0.9rem;
    }

    .msg-bubble.sent {
        background: var(--teal, #0d7377);
        color: #fff;
        align-self: flex-end;
        border-bottom-right-radius: 3px;
    }

    .msg-bubble.recv {
        background: #f0f4f5;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 3px;
    }

    .msg-time {
        font-size: 0.72rem;
        opacity: 0.65;
        margin-top: 4px;
    }

    .msg-thread {
        display: flex;
        flex-direction: column;
        padding: 20px;
        max-height: 420px;
        overflow-y: auto;
    }

    .reply-box {
        border-top: 1px solid var(--border, #eee);
        padding: 15px;
    }

    .reply-box textarea {
        flex: 1;
        border: 1px solid var(--border, #ddd);
        border-radius: 8px;
        padding: 10px;
        font-size: 0.9rem;
        resize: none;
        font-family: inherit;
    }

    .role-tag {
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 4px;
        background: #e8f4fd;
        color: #0077b6;
        font-weight: 600;
        text-transform: uppercase;
    }

    .role-tag.admin {
        background: rgba(232, 168, 56, 0.2);
        color: #c98a00;
    }

    .admin-quick {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 12px 16px;
        background: rgba(232, 168, 56, 0.06);
        border-radius: 8px;
        margin-top: 10px;
    }
</style>

<div class="dash-layout">
    <aside class="dash-sidebar">
        <div class="dash-user">
            <div class="dash-avatar"><?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?></div>
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
                <p>Your private, encrypted conversations.</p>
            </div>
            <a href="compose_message.php" class="btn btn-primary"><i class="fas fa-edit"></i> New Message</a>
        </div>

        <?php if ($role !== 'admin' && !empty($admins)): ?>
            <div class="card" style="padding: 14px 18px; margin-bottom: 18px; border-left: 4px solid #e8a838;">
                <strong style="font-size: 0.88rem; color: #c98a00;"><i class="fas fa-shield-alt"></i> Contact System Admin</strong>
                <div class="admin-quick">
                    <?php foreach ($admins as $adm): ?>
                        <a href="chat_engine.php?chat_with=<?= $adm['id'] ?>" class="btn btn-outline btn-sm" style="border-color:#e8a838; color:#c98a00;">
                            <i class="fas fa-user-shield"></i> <?= htmlspecialchars($adm['first_name'] . ' ' . $adm['last_name']) ?>
                        </a>
                    <?php endforeach; ?>
                    <a href="compose_message.php" class="btn btn-sm" style="background:rgba(232,168,56,0.15); color:#c98a00; border:1px solid #e8a838;">
                        <i class="fas fa-pen"></i> Write to Admin
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="chat-layout">
            <!-- Contacts Column -->
            <div class="card" style="padding: 0; overflow: hidden;">
                <div class="card-header" style="padding: 14px 16px;">
                    <h2 class="card-title" style="font-size: 0.95rem;">Conversations</h2>
                </div>
                <?php if (empty($contacts)): ?>
                    <div style="padding: 30px 20px; text-align: center;">
                        <i class="fas fa-comments" style="font-size: 2rem; color: var(--muted); margin-bottom: 10px; display: block;"></i>
                        <p style="color: var(--muted); font-size: 0.88rem;">No conversations yet.</p>
                        <a href="compose_message.php" class="btn btn-primary btn-sm" style="margin-top: 8px;">Start a Chat</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($contacts as $c): ?>
                        <?php
                        $initials = strtoupper(substr($c['first_name'], 0, 1) . substr($c['last_name'], 0, 1));
                        $isActive = ($chatWith === (int)$c['id']) ? 'active' : '';
                        $isAdmin  = ($c['role'] === 'admin');
                        ?>
                        <a href="chat_engine.php?chat_with=<?= $c['id'] ?>" class="contact-item <?= $isActive ?>">
                            <div class="contact-avatar <?= $isAdmin ? 'admin-av' : '' ?>">
                                <?= $isAdmin ? '<i class="fas fa-shield-alt"></i>' : $initials ?>
                            </div>
                            <div class="contact-info">
                                <strong><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                                <span>
                                    <span class="role-tag <?= $isAdmin ? 'admin' : '' ?>"><?= ucfirst($c['role']) ?></span>
                                    <?php if ($c['last_message']): ?>
                                        &nbsp;<?= htmlspecialchars(mb_strimwidth($c['last_message'], 0, 30, '…')) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if ($c['unread_count'] > 0): ?>
                                <span class="unread-badge"><?= $c['unread_count'] ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Conversation Thread Column -->
            <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                <?php if ($chatUser): ?>
                    <div class="card-header" style="padding: 14px 18px; display: flex; align-items: center; gap: 12px;">
                        <div class="contact-avatar <?= ($chatUser['role'] === 'admin') ? 'admin-av' : '' ?>" style="width:36px; height:36px; font-size:0.8rem;">
                            <?= ($chatUser['role'] === 'admin') ? '<i class="fas fa-shield-alt"></i>' : strtoupper(substr($chatUser['first_name'], 0, 1) . substr($chatUser['last_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <strong style="font-size:0.95rem;"><?= htmlspecialchars($chatUser['first_name'] . ' ' . $chatUser['last_name']) ?></strong>
                            <span class="role-tag <?= ($chatUser['role'] === 'admin') ? 'admin' : '' ?>" style="margin-left:6px;"><?= ucfirst($chatUser['role']) ?></span>
                        </div>
                    </div>

                    <div class="msg-thread" id="msgThread">
                        <?php if (empty($messages)): ?>
                            <div style="text-align:center; color:var(--muted); padding: 30px; font-size: 0.9rem;">
                                No messages yet. Send the first message below!
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <?php $isMine = ((int)$msg['sender_id'] === $user['id']); ?>
                                <div style="display:flex; justify-content:<?= $isMine ? 'flex-end' : 'flex-start' ?>;">
                                    <div class="msg-bubble <?= $isMine ? 'sent' : 'recv' ?>">
                                        <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                        <div class="msg-time" style="text-align:<?= $isMine ? 'right' : 'left' ?>;">
                                            <?= date('d M, g:i A', strtotime($msg['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="reply-box">
                        <form method="POST" action="chat_engine.php?chat_with=<?= $chatWith ?>" style="display:flex; gap:10px; width:100%;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="receiver_id" value="<?= $chatWith ?>">
                            <textarea name="reply_message" rows="2" placeholder="Type your secure message…" required style="flex:1; border:1px solid var(--border,#ddd); border-radius:8px; padding:10px; font-size:0.9rem; resize:none; font-family:inherit;"></textarea>
                            <button type="submit" class="btn btn-primary" style="align-self: flex-end;"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>

                <?php else: ?>
                    <div style="padding: 60px 40px; text-align: center;">
                        <i class="fas fa-lock" style="font-size: 2.5rem; color: var(--teal); margin-bottom: 15px; display: block;"></i>
                        <h3>Your Secure Inbox</h3>
                        <p style="color:var(--muted); max-width: 320px; margin: 10px auto;">
                            Select a conversation from the left, or compose a new message to a doctor, patient, or admin.
                        </p>
                        <a href="compose_message.php" class="btn btn-primary" style="margin-top: 12px;"><i class="fas fa-edit"></i> Compose Message</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
    // Auto-scroll chat thread to the bottom
    const thread = document.getElementById('msgThread');
    if (thread) thread.scrollTop = thread.scrollHeight;
</script>

<?php include 'footer.php'; ?>