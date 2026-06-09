<?php
// ─────────────────────────────────────────────────────────────
// MedicarePlus – functions.php
// All shared helper & DB functions
// ─────────────────────────────────────────────────────────────

require_once __DIR__ . '/db_connect.php';

// ── Auth helpers ───────────────────────────────────────────

function get_logged_in_user(): ?array
{
    if (!isset($_SESSION['user_id'])) return null;
    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT id, first_name, last_name, email, role, phone, city FROM users WHERE id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ?: null;
}

function require_role(string ...$roles): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: Login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    $userRole = $_SESSION['role'] ?? '';
    if (!in_array($userRole, $roles, true)) {
        http_response_code(403);
        die('<p style="font-family:sans-serif;padding:2rem">Access denied. <a href="Home.php">Go home</a></p>');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ── User functions ─────────────────────────────────────────

function fetch_user_by_id(int $id): ?array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT id, first_name, last_name, email, role FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function fetch_user_by_email(string $email): ?array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function create_user(string $first, string $last, string $email, string $password, string $role = 'patient', string $phone = '', string $city = ''): int|false
{
    $conn = get_db_connection();
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $conn->prepare('INSERT INTO users (first_name, last_name, email, password_hash, role, phone, city) VALUES (?,?,?,?,?,?,?)');
    $stmt->bind_param('sssssss', $first, $last, $email, $hash, $role, $phone, $city);
    if ($stmt->execute()) {
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }
    $stmt->close();
    return false;
}

// ── Patient functions ──────────────────────────────────────

function create_patient_profile(int $userId): bool
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('INSERT IGNORE INTO patients (user_id) VALUES (?)');
    $stmt->bind_param('i', $userId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function fetch_patient_by_user_id(int $userId): ?array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT p.*, u.first_name, u.last_name, u.email, u.phone, u.city FROM patients p JOIN users u ON u.id = p.user_id WHERE p.user_id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

// ── Doctor functions ───────────────────────────────────────

function fetch_all_doctors(): array
{
    $conn = get_db_connection();
    $result = $conn->query(
        'SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.city
         FROM doctors d
         JOIN users u ON u.id = d.user_id
         ORDER BY d.rating DESC'
    );
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function fetch_doctor_by_id(int $id): ?array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare(
        'SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.city
         FROM doctors d JOIN users u ON u.id = d.user_id WHERE d.id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function fetch_doctor_by_user_id(int $userId): ?array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare(
        'SELECT d.*, u.first_name, u.last_name, u.email, u.phone, u.city
         FROM doctors d JOIN users u ON u.id = d.user_id WHERE d.user_id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

// ── Appointment functions ──────────────────────────────────

function create_appointment(int $patientId, int $doctorId, string $dt, string $notes): bool
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('INSERT INTO appointments (patient_id, doctor_id, appointment_dt, notes) VALUES (?,?,?,?)');
    $stmt->bind_param('iiss', $patientId, $doctorId, $dt, $notes);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

function doctor_has_conflict(int $doctorId, string $dt): bool
{
    $conn = get_db_connection();
    $stmt = $conn->prepare(
        "SELECT id FROM appointments
         WHERE doctor_id = ? AND status NOT IN ('cancelled')
         AND ABS(TIMESTAMPDIFF(MINUTE, appointment_dt, ?)) < 30 LIMIT 1"
    );
    $stmt->bind_param('is', $doctorId, $dt);
    $stmt->execute();
    $has = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $has;
}

function fetch_appointments_for_patient(int $patientId): array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare(
        "SELECT a.*, d.specialization, d.consultation_fee, u.first_name AS doc_first, u.last_name AS doc_last
         FROM appointments a
         JOIN doctors d ON d.id = a.doctor_id
         JOIN users u ON u.id = d.user_id
         WHERE a.patient_id = ?
         ORDER BY a.appointment_dt DESC"
    );
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function fetch_appointments_for_doctor(int $doctorId): array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare(
        "SELECT a.*, u.first_name AS pat_first, u.last_name AS pat_last, u.phone AS pat_phone
         FROM appointments a
         JOIN patients p ON p.id = a.patient_id
         JOIN users u ON u.id = p.user_id
         WHERE a.doctor_id = ?
         ORDER BY a.appointment_dt DESC"
    );
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function update_appointment_status(int $appointmentId, string $status): bool
{
    $allowed = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($status, $allowed, true)) return false;
    $conn = get_db_connection();
    $stmt = $conn->prepare('UPDATE appointments SET status = ? WHERE id = ?');
    $stmt->bind_param('si', $status, $appointmentId);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// ── Medical reports ────────────────────────────────────────

function fetch_reports_for_patient(int $patientId): array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT * FROM medical_reports WHERE patient_id = ? ORDER BY created_at DESC');
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function save_report(int $patientId, ?int $appointmentId, int $uploadedBy, string $fileName, string $filePath, string $description): bool
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('INSERT INTO medical_reports (patient_id, appointment_id, uploaded_by, file_name, file_path, description) VALUES (?,?,?,?,?,?)');
    $stmt->bind_param('iiisss', $patientId, $appointmentId, $uploadedBy, $fileName, $filePath, $description);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// ── Payments ───────────────────────────────────────────────

function fetch_payment_for_appointment(int $appointmentId): ?array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT * FROM payments WHERE appointment_id = ? LIMIT 1');
    $stmt->bind_param('i', $appointmentId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function create_or_update_payment(int $appointmentId, float $amount, string $method, string $ref): bool
{
    $conn = get_db_connection();
    $stmt = $conn->prepare(
        'INSERT INTO payments (appointment_id, amount, status, payment_method, transaction_ref, paid_at)
         VALUES (?,?,\'paid\',?,?,NOW())
         ON DUPLICATE KEY UPDATE status=\'paid\', payment_method=?, transaction_ref=?, paid_at=NOW()'
    );
    $stmt->bind_param('idssss', $appointmentId, $amount, $method, $ref, $method, $ref);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// ── Messaging (modelled on MediCare_Plus reference) ────────

// Fetch all users available to message (for recipient dropdown)
function fetch_message_recipients(int $currentUserId, string $role): array
{
    $conn = get_db_connection();
    if ($role === 'patient') {
        // Patients can message their appointed doctors + any admin
        $stmt = $conn->prepare("
            SELECT DISTINCT u.id, u.first_name, u.last_name, u.role,
                   d.specialization AS sub_text
            FROM users u
            JOIN doctors d ON u.id = d.user_id
            JOIN appointments a ON d.id = a.doctor_id
            JOIN patients p ON a.patient_id = p.id
            WHERE p.user_id = ?
            UNION
            SELECT id, first_name, last_name, role, 'System Admin' AS sub_text
            FROM users WHERE role = 'admin'
        ");
        $stmt->bind_param('i', $currentUserId);
    } elseif ($role === 'doctor') {
        // Doctors can message their patients + any admin
        $stmt = $conn->prepare("
            SELECT DISTINCT u.id, u.first_name, u.last_name, u.role,
                   COALESCE(u.phone, 'Patient') AS sub_text
            FROM users u
            JOIN patients p ON u.id = p.user_id
            JOIN appointments a ON p.id = a.patient_id
            JOIN doctors d ON a.doctor_id = d.id
            WHERE d.user_id = ?
            UNION
            SELECT id, first_name, last_name, role, 'System Admin' AS sub_text
            FROM users WHERE role = 'admin'
        ");
        $stmt->bind_param('i', $currentUserId);
    } else {
        // Admin can message everyone
        $stmt = $conn->prepare("
            SELECT id, first_name, last_name, role, role AS sub_text
            FROM users WHERE id != ?
            ORDER BY role, first_name
        ");
        $stmt->bind_param('i', $currentUserId);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// Send a message — returns true on success
function send_message(int $senderId, int $receiverId, string $body): bool
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())');
    $stmt->bind_param('iis', $senderId, $receiverId, $body);
    $result = $stmt->execute();
    $stmt->close();
    if ($result) {
        // Notify the receiver
        $userStmt = $conn->prepare('SELECT first_name, last_name FROM users WHERE id = ? LIMIT 1');
        $userStmt->bind_param('i', $senderId);
        $userStmt->execute();
        $sender = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
        if ($sender) {
            add_notification($receiverId, "New message from {$sender['first_name']} {$sender['last_name']}.");
        }
    }
    return $result;
}

// Fetch inbox: one row per conversation partner, most recent first
function fetch_inbox(int $userId): array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare("
        SELECT u.id, u.first_name, u.last_name, u.role,
               latest.last_message, latest.last_at,
               (SELECT COUNT(*) FROM messages
                WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) AS unread
        FROM users u
        JOIN (
            SELECT
                CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS partner_id,
                message AS last_message,
                created_at AS last_at
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?
            ORDER BY created_at DESC
        ) AS latest ON u.id = latest.partner_id
        WHERE u.id != ?
        GROUP BY u.id
        ORDER BY latest.last_at DESC
    ");
    $stmt->bind_param('iiiii', $userId, $userId, $userId, $userId, $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// Fetch full conversation between two users, oldest first
function fetch_conversation(int $userId, int $otherId): array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare("
        SELECT m.id, m.sender_id, m.receiver_id, m.message AS body,
               m.is_read, m.created_at AS sent_at,
               u.first_name AS sender_first, u.last_name AS sender_last
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->bind_param('iiii', $userId, $otherId, $otherId, $userId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

// Mark all messages from a sender to current user as read
function mark_conversation_read(int $readerId, int $senderId): void
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ? AND is_read = 0');
    $stmt->bind_param('ii', $readerId, $senderId);
    $stmt->execute();
    $stmt->close();
}

// Count unread messages (used for nav badge on dashboards)
function get_unread_messages(int $userId): int
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int) $count;
}

// ── Notifications ──────────────────────────────────────────

function get_unread_count(int $userId): int
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return (int) $count;
}

function add_notification(int $userId, string $message): void
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('INSERT INTO notifications (user_id, message) VALUES (?,?)');
    $stmt->bind_param('is', $userId, $message);
    $stmt->execute();
    $stmt->close();
}

function fetch_notifications(int $userId, int $limit = 20): array
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function mark_notifications_read(int $userId): void
{
    $conn = get_db_connection();
    $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
}

// ── Utilities ──────────────────────────────────────────────

function navActive(string $page, string $current): string
{
    return strpos($current, $page) !== false ? 'active' : '';
}

function format_date(string $dt): string
{
    return date('d M Y, g:i A', strtotime($dt));
}

function status_badge(string $status): string
{
    $map = [
        'pending'   => 'badge-pending',
        'confirmed' => 'badge-confirmed',
        'completed' => 'badge-completed',
        'cancelled' => 'badge-cancelled',
        'paid'      => 'badge-paid',
        'refunded'  => 'badge-refunded',
    ];
    $cls = $map[$status] ?? 'badge-pending';
    return '<span class="badge ' . $cls . '">' . htmlspecialchars(ucfirst($status)) . '</span>';
}
