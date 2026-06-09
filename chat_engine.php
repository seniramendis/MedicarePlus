<?php
require_once 'functions.php';
require_role('patient', 'doctor', 'admin');

$user      = get_logged_in_user();
$role      = $_SESSION['role'];
$pageTitle = 'Secure Messages — Medicare Plus';
$conn      = get_db_connection();

// ── Handle reply POST (from conversation thread) ──────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_body'], $_POST['receiver_id'])) {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }
    $receiverId = (int) $_POST['receiver_id'];
    $body       = trim($_POST['reply_body']);
    if ($body !== '' && $receiverId > 0) {
        send_message($user['id'], $receiverId, $body);
    }
    header('Location: chat_engine.php?view_user=' . $receiverId);
    exit;
}

// ── Handle new message POST (inline compose panel) ────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_body'], $_POST['new_receiver_id'])) {
    if (!csrf_verify($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token.');
    }
    $receiverId = (int) $_POST['new_receiver_id'];
    $body       = trim($_POST['new_body']);
    if ($body !== '' && $receiverId > 0) {
        send_message($user['id'], $receiverId, $body);
        header('Location: chat_engine.php?view_user=' . $receiverId);
        exit;
    }
}

// ── Fetch inbox (list of conversation partners) ───────────
$inbox = fetch_inbox($user['id']);

// ── Fetch open conversation if ?view_user= is set ─────────
$viewUserId   = isset($_GET['view_user']) ? (int) $_GET['view_user'] : 0;
$otherUser    = null;
$conversation = [];

if ($viewUserId > 0) {
    $otherUser = fetch_user_by_id($viewUserId);
    if ($otherUser) {
        mark_conversation_read($user['id'], $viewUserId);
        $conversation = fetch_conversation($user['id'], $viewUserId);
    }
}

// ── Recipients for inline "New Message" compose ───────────
$recipients = fetch_message_recipients($user['id'], $role);

// ── Admins quick-contact bar (patients & doctors only) ────
$admins = [];
if ($role !== 'admin') {
    $res = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'admin' LIMIT 5");
    while ($row = $res->fetch_assoc()) $admins[] = $row;
}

include 'header.php';
?>
<style>
    .chat-wrap {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 20px;
    }

    .inbox-list {
        padding: 0;
    }

    .inbox-item {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 13px 15px;
        border-bottom: 1px solid var(--border, #eee);
        text-decoration: none;
        color: inherit;
        transition: background .15s;
    }

    .inbox-item:hover,
    .inbox-item.active {
        background: rgba(13, 115, 119, .08);
    }

    .av {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(13, 115, 119, .18);
        color: var(--teal, #0d7377);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .85rem;
        flex-shrink: 0;
    }

    .av.adm {
        background: rgba(232, 168, 56, .2);
        color: #c98a00;
    }

    .inbox-info {
        flex: 1;
        min-width: 0;
    }

    .inbox-info strong {
        display: block;
        font-size: .88rem;
    }

    .inbox-info span {
        display: block;
        font-size: .75rem;
        color: var(--muted, #888);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .badge-dot {
        background: var(--teal, #0d7377);
        color: #fff;
        border-radius: 10px;
        padding: 1px 6px;
        font-size: .72rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .msg-feed {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 20px;
        max-height: 400px;
        overflow-y: auto;
    }

    .bubble {
        max-width: 68%;
        padding: 10px 14px;
        border-radius: 12px;
        font-size: .88rem;
        line-height: 1.5;
    }

    .bubble.out {
        background: var(--teal, #0d7377);
        color: #fff;
        align-self: flex-end;
        border-bottom-right-radius: 3px;
    }

    .bubble.in {
        background: #f0f4f5;
        color: #333;
        align-self: flex-start;
        border-bottom-left-radius: 3px;
    }

    .btime {
        font-size: .7rem;
        opacity: .6;
        margin-top: 3px;
    }

    .reply-bar {
        border-top: 1px solid var(--border, #eee);
        padding: 14px;
        display: flex;
        gap: 10px;
    }

    .reply-bar textarea {
        flex: 1;
        border: 1px solid var(--border, #ddd);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: .88rem;
        resize: none;
        font-family: inherit;
    }

    .role-chip {
        display: inline-block;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 2px 6px;
        border-radius: 4px;
        background: #e8f4fd;
        color: #0077b6;
    }

    .role-chip.adm {
        background: rgba(232, 168, 56, .2);
        color: #c98a00;
    }

    .admin-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
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
            <div class="dash-nav-divider"></div>
            <a href="logout.php" style="color:#dc3545"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
        </nav>
    </aside>

    <main class="dash-main">
        <div class="dash-header">
            <div>
                <h1>Secure Messaging</h1>
                <p>Your private conversations with doctors, patients and admin.</p>
            </div>
            <button onclick="toggleCompose()" class="btn btn-primary"><i class="fas fa-edit"></i> New Message</button>
        </div>

        <?php if ($role !== 'admin' && !empty($admins)): ?>
            <div class="card" style="padding:13px 18px; margin-bottom:18px; border-left:4px solid #e8a838;">
                <strong style="font-size:.85rem; color:#c98a00;"><i class="fas fa-shield-alt"></i> Contact System Admin</strong>
                <div class="admin-bar">
                    <?php foreach ($admins as $adm): ?>
                        <a href="chat_engine.php?view_user=<?= $adm['id'] ?>" class="btn btn-outline btn-sm" style="border-color:#e8a838;color:#c98a00;">
                            <i class="fas fa-user-shield"></i> <?= htmlspecialchars($adm['first_name'] . ' ' . $adm['last_name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Inline Compose Panel -->
        <div id="composePanel" style="display:none; margin-bottom:20px;">
            <div class="card" style="padding:22px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <strong><i class="fas fa-paper-plane" style="color:var(--teal)"></i> New Message</strong>
                    <button onclick="toggleCompose()" style="background:none;border:none;cursor:pointer;font-size:1.2rem;color:var(--muted)">&times;</button>
                </div>
                <form method="POST" action="chat_engine.php">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div style="margin-bottom:12px;">
                        <label style="display:block;margin-bottom:5px;font-weight:600;font-size:.86rem;">To</label>
                        <select name="new_receiver_id" required style="width:100%;padding:9px 12px;border:1px solid var(--border,#ddd);border-radius:8px;font-size:.88rem;font-family:inherit;">
                            <option value="">— Select recipient —</option>
                            <?php foreach ($recipients as $r): ?>
                                <option value="<?= (int)$r['id'] ?>">
                                    <?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?>
                                    (<?= htmlspecialchars(ucfirst($r['role'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="margin-bottom:12px;">
                        <label style="display:block;margin-bottom:5px;font-weight:600;font-size:.86rem;">Message</label>
                        <textarea name="new_body" rows="4" required placeholder="Write your secure message…"
                            style="width:100%;padding:9px 12px;border:1px solid var(--border,#ddd);border-radius:8px;font-size:.88rem;font-family:inherit;resize:vertical;box-sizing:border-box;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-lock"></i> Send Message</button>
                </form>
            </div>
        </div>

        <div class="chat-wrap">
            <!-- Left: Inbox list -->
            <div class="card" style="padding:0;overflow:hidden;">
                <div style="padding:13px 15px;border-bottom:1px solid var(--border,#eee);">
                    <strong style="font-size:.88rem;">Conversations</strong>
                </div>
                <div class="inbox-list">
                    <?php if (empty($inbox)): ?>
                        <div style="padding:30px 15px;text-align:center;color:var(--muted);">
                            <i class="fas fa-comments" style="font-size:1.8rem;display:block;margin-bottom:8px;"></i>
                            <p style="font-size:.85rem;">No conversations yet.</p>
                            <button onclick="toggleCompose()" class="btn btn-primary btn-sm" style="margin-top:6px;">Start a Chat</button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($inbox as $c):
                            $isActive = ($viewUserId === (int)$c['id']);
                            $isAdm    = ($c['role'] === 'admin');
                            $initials = strtoupper(substr($c['first_name'], 0, 1) . substr($c['last_name'], 0, 1));
                        ?>
                            <a href="chat_engine.php?view_user=<?= (int)$c['id'] ?>"
                                class="inbox-item <?= $isActive ? 'active' : '' ?>">
                                <div class="av <?= $isAdm ? 'adm' : '' ?>">
                                    <?= $isAdm ? '<i class="fas fa-shield-alt"></i>' : $initials ?>
                                </div>
                                <div class="inbox-info">
                                    <strong><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></strong>
                                    <span>
                                        <span class="role-chip <?= $isAdm ? 'adm' : '' ?>"><?= ucfirst($c['role']) ?></span>
                                        &nbsp;<?= htmlspecialchars(mb_strimwidth($c['last_message'] ?? '', 0, 28, '…')) ?>
                                    </span>
                                </div>
                                <?php if ((int)$c['unread'] > 0): ?>
                                    <span class="badge-dot"><?= (int)$c['unread'] ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Conversation thread -->
            <div class="card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;">
                <?php if ($otherUser): ?>
                    <!-- Thread header -->
                    <div style="padding:13px 18px;border-bottom:1px solid var(--border,#eee);display:flex;align-items:center;gap:11px;">
                        <div class="av <?= $otherUser['role'] === 'admin' ? 'adm' : '' ?>" style="width:34px;height:34px;font-size:.78rem;">
                            <?= $otherUser['role'] === 'admin'
                                ? '<i class="fas fa-shield-alt"></i>'
                                : strtoupper(substr($otherUser['first_name'], 0, 1) . substr($otherUser['last_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <strong style="font-size:.92rem;">
                                <?= htmlspecialchars($otherUser['first_name'] . ' ' . $otherUser['last_name']) ?>
                            </strong>
                            <span class="role-chip <?= $otherUser['role'] === 'admin' ? 'adm' : '' ?>" style="margin-left:6px;">
                                <?= ucfirst($otherUser['role']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Message bubbles -->
                    <div class="msg-feed" id="msgFeed">
                        <?php if (empty($conversation)): ?>
                            <div style="text-align:center;color:var(--muted);padding:30px;font-size:.85rem;">
                                No messages yet — send the first one below.
                            </div>
                        <?php else: ?>
                            <?php foreach ($conversation as $m):
                                $mine = ((int)$m['sender_id'] === (int)$user['id']);
                            ?>
                                <div style="display:flex;justify-content:<?= $mine ? 'flex-end' : 'flex-start' ?>">
                                    <div class="bubble <?= $mine ? 'out' : 'in' ?>">
                                        <?= nl2br(htmlspecialchars($m['body'])) ?>
                                        <div class="btime" style="text-align:<?= $mine ? 'right' : 'left' ?>">
                                            <?= date('d M, g:i A', strtotime($m['sent_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Reply bar -->
                    <div class="reply-bar">
                        <form method="POST" action="chat_engine.php?view_user=<?= $viewUserId ?>"
                            style="display:flex;gap:10px;width:100%;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <input type="hidden" name="receiver_id" value="<?= $viewUserId ?>">
                            <textarea name="reply_body" rows="2"
                                placeholder="Type your reply…" required></textarea>
                            <button type="submit" class="btn btn-primary" style="align-self:flex-end;">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- No conversation selected -->
                    <div style="padding:60px 40px;text-align:center;">
                        <i class="fas fa-lock" style="font-size:2.4rem;color:var(--teal);display:block;margin-bottom:14px;"></i>
                        <h3>Your Secure Inbox</h3>
                        <p style="color:var(--muted);max-width:300px;margin:10px auto;font-size:.88rem;">
                            Select a conversation on the left, or start a new one.
                        </p>
                        <button onclick="toggleCompose()" class="btn btn-primary" style="margin-top:12px;">
                            <i class="fas fa-edit"></i> New Message
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
    // Scroll chat to bottom on load
    const feed = document.getElementById('msgFeed');
    if (feed) feed.scrollTop = feed.scrollHeight;

    // Toggle inline compose panel
    function toggleCompose() {
        const p = document.getElementById('composePanel');
        p.style.display = p.style.display === 'none' ? 'block' : 'none';
        if (p.style.display === 'block') p.querySelector('select').focus();
    }

    // Auto-open compose if URL has ?compose=1
    if (new URLSearchParams(location.search).get('compose') === '1') toggleCompose();
</script>

<?php include 'footer.php'; ?>