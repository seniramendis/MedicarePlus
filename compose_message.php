<?php
require_once 'functions.php';
require_role('patient', 'doctor', 'admin');

$user      = get_logged_in_user();
$role      = $_SESSION['role'];
$pageTitle = 'Compose Message — Medicare Plus';
$errors    = [];
$success   = '';

// Pre-select recipient if ?to= is in the URL (e.g. from "Reply" links)
$preselect = filter_input(INPUT_GET, 'to', FILTER_VALIDATE_INT) ?: 0;

// Fetch recipients using the same centralised function
$recipients = fetch_message_recipients($user['id'], $role);

// ── Handle form submission ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check (exactly like the reference project)
    $submitted = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW) ?? '';
    if (!hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        die('Invalid CSRF token. Please go back and try again.');
    }

    $receiverId = filter_input(INPUT_POST, 'receiver_id', FILTER_VALIDATE_INT);
    $body       = trim(filter_input(INPUT_POST, 'message_body', FILTER_DEFAULT) ?: '');

    if (!$receiverId) $errors[] = 'Please select a recipient.';
    if ($body === '')  $errors[] = 'Message cannot be empty.';

    if (empty($errors)) {
        if (send_message($user['id'], $receiverId, $body)) {
            // Redirect straight to the conversation thread
            header('Location: chat_engine.php?view_user=' . $receiverId);
            exit;
        } else {
            $errors[] = 'Could not send message. Please try again.';
        }
    }
}

include 'header.php';
?>
<div class="dash-layout">
  <aside class="dash-sidebar">
    <div class="dash-user">
      <div class="dash-avatar"><?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?></div>
      <h4><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></h4>
      <span><?= ucfirst($role) ?> Portal</span>
    </div>
    <nav class="dash-nav">
      <a href="dashboard_<?= $role==='admin'?'admin':$role ?>.php"><i class="fas fa-home"></i> Dashboard</a>
      <a href="chat_engine.php"><i class="fas fa-comments"></i> Secure Inbox</a>
      <a href="compose_message.php" class="active"><i class="fas fa-paper-plane"></i> Compose</a>
      <div class="dash-nav-divider"></div>
      <a href="logout.php" style="color:#dc3545"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
    </nav>
  </aside>

  <main class="dash-main">
    <div class="dash-header">
      <div><h1>Compose Secure Message</h1></div>
      <a href="chat_engine.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Inbox</a>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="card" style="padding:14px 18px;margin-bottom:18px;border-left:4px solid #dc3545;">
        <?php foreach ($errors as $e): ?>
          <p style="margin:4px 0;color:#dc3545;"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header"><h2 class="card-title">New Message</h2></div>
      <div style="padding:24px;">
        <form method="POST" action="compose_message.php">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

          <div style="margin-bottom:18px;">
            <label style="display:block;margin-bottom:7px;font-weight:600;">Recipient</label>
            <select name="receiver_id" required class="form-control" style="width:100%;padding:11px;">
              <option value="">— Select recipient —</option>
              <?php foreach ($recipients as $r): ?>
                <option value="<?= (int)$r['id'] ?>"
                  <?= ($preselect === (int)$r['id'] || (isset($_POST['receiver_id']) && (int)$_POST['receiver_id'] === (int)$r['id'])) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($r['first_name'].' '.$r['last_name'].' ('.ucfirst($r['role']).')') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div style="margin-bottom:18px;">
            <label style="display:block;margin-bottom:7px;font-weight:600;">Message</label>
            <textarea name="message_body" rows="7" required class="form-control"
              style="width:100%;padding:12px;"
              placeholder="Write your secure message here…"><?= htmlspecialchars($_POST['message_body'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="fas fa-lock"></i> Send Secure Message
          </button>
        </form>
      </div>
    </div>
  </main>
</div>
<?php include 'footer.php'; ?>
