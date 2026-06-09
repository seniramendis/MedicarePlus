<?php
require_once 'functions.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$doctorId = filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT);
$doctor   = $doctorId && function_exists('fetch_doctor_by_id') ? fetch_doctor_by_id($doctorId) : null;

// Handle form submission
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: Login.php?redirect=book_appointment.php' . ($doctorId ? '?doctor_id=' . $doctorId : ''));
        exit;
    }
    // Basic validation
    $required = ['doctor_id', 'appt_date', 'appt_time', 'reason'];
    $missing  = array_filter($required, fn($k) => empty($_POST[$k]));
    if ($missing) {
        $error = 'Please fill in all required fields.';
    } else {
        // Insert appointment
        if (function_exists('create_appointment')) {
            // Resolve patient_id from session user
            $patientRow = function_exists('fetch_patient_by_user_id')
                ? fetch_patient_by_user_id((int)$_SESSION['user_id'])
                : null;
            $patientId = $patientRow['id'] ?? 0;
            $doctorId2  = (int)$_POST['doctor_id'];
            $dt        = $_POST['appt_date'] . ' ' . $_POST['appt_time'] . ':00';
            $notes     = trim($_POST['reason'] ?? '');
            $res = ($patientId > 0)
                ? create_appointment($patientId, $doctorId2, $dt, $notes)
                : false;
            $success = $res ? 'Appointment booked successfully! You will receive a confirmation shortly.' : 'Booking failed — please try again.';
        } elseif (isset($conn)) {
            $stmt = $conn->prepare("INSERT INTO appointments (user_id, doctor_id, appointment_date, appointment_time, reason, status, created_at) VALUES (?,?,?,?,?,'pending',NOW())");
            if ($stmt) {
                $stmt->bind_param('iisss', $_SESSION['user_id'], $_POST['doctor_id'], $_POST['appt_date'], $_POST['appt_time'], $_POST['reason']);
                $success = $stmt->execute() ? 'Appointment booked successfully!' : 'Booking failed — please try again.';
                $stmt->close();
            }
        } else {
            $success = 'Appointment request submitted (offline mode).';
        }
    }
}

// Fetch all doctors for dropdown
$allDoctors = [];
if (function_exists('fetch_all_doctors')) $allDoctors = fetch_all_doctors() ?: [];
elseif (isset($conn)) {
    $r = $conn->query("SELECT * FROM doctors ORDER BY last_name");
    if ($r) while ($row = $r->fetch_assoc()) $allDoctors[] = $row;
}

include 'header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --teal: #0d7377;
            --teal-dark: #0a5658;
            --teal-light: #14a5aa;
            --accent: #f0c850;
            --accent-dark: #d4a017;
            --white: #ffffff;
            --sand: #f7f8fa;
            --dark: #131c23;
            --mid: #4a5568;
            --muted: #8492a6;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 4px rgba(0, 0, 0, .06);
            --shadow-md: 0 6px 28px rgba(0, 0, 0, .10);
            --shadow-lg: 0 16px 56px rgba(0, 0, 0, .14);
            --radius: 12px;
            --radius-lg: 20px;
            --font-display: 'Sora', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-body);
            color: var(--dark);
            background: var(--sand);
            min-height: 100vh;
        }

        /* HERO */
        .book-hero {
            background: linear-gradient(120deg, var(--teal-dark), var(--teal) 60%, #1a9fa5);
            padding: 48px 0 80px;
            position: relative;
            overflow: hidden;
        }

        .book-hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 80% 50%, rgba(255, 255, 255, .05), transparent 55%);
        }

        .hero-inner {
            max-width: 960px;
            margin: 0 auto;
            padding: 0 24px;
            position: relative;
            z-index: 1;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .8rem;
            color: rgba(255, 255, 255, .6);
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: rgba(255, 255, 255, .6);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            color: var(--accent);
        }

        .breadcrumb i {
            font-size: .6rem;
        }

        .book-hero h1 {
            font-family: var(--font-display);
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }

        .book-hero p {
            color: rgba(255, 255, 255, .7);
            font-size: .97rem;
        }

        /* MAIN LAYOUT */
        .book-layout {
            max-width: 960px;
            margin: -48px auto 80px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 28px;
            align-items: start;
        }

        /* DOCTOR PREVIEW CARD (right) */
        .doctor-preview {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            position: sticky;
            top: 90px;
        }

        .preview-header {
            background: linear-gradient(135deg, var(--teal-dark), var(--teal));
            padding: 28px 24px;
            text-align: center;
        }

        .preview-avatar {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .18);
            border: 3px solid rgba(255, 255, 255, .3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-display);
            font-size: 1.8rem;
            font-weight: 800;
            color: #fff;
            margin: 0 auto 14px;
        }

        .preview-name {
            font-family: var(--font-display);
            font-size: 1.1rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }

        .preview-spec {
            font-size: .8rem;
            color: rgba(255, 255, 255, .7);
        }

        .preview-body {
            padding: 20px 22px;
        }

        .preview-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: .87rem;
            color: var(--mid);
        }

        .preview-row:last-child {
            border-bottom: none;
        }

        .preview-row i {
            color: var(--teal);
            width: 16px;
            flex-shrink: 0;
        }

        .preview-fee {
            margin: 18px 0 0;
            background: rgba(13, 115, 119, .06);
            border: 1px solid rgba(13, 115, 119, .15);
            border-radius: var(--radius);
            padding: 16px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .fee-label {
            font-size: .75rem;
            color: var(--muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .fee-val {
            font-family: var(--font-display);
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--teal);
        }

        /* FORM CARD */
        .form-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        /* PROGRESS STEPS */
        .progress-bar {
            display: flex;
            align-items: center;
            background: var(--sand);
            padding: 20px 28px;
            border-bottom: 1px solid var(--border);
            gap: 0;
        }

        .progress-step {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        .step-num {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--border);
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .85rem;
            flex-shrink: 0;
            transition: all .3s;
        }

        .progress-step.active .step-num {
            background: var(--teal);
            color: #fff;
        }

        .progress-step.complete .step-num {
            background: #27ae60;
            color: #fff;
        }

        .step-label {
            font-size: .8rem;
            font-weight: 600;
            color: var(--muted);
        }

        .progress-step.active .step-label {
            color: var(--teal);
        }

        .progress-step.complete .step-label {
            color: #27ae60;
        }

        .step-connector {
            height: 2px;
            flex: 1;
            background: var(--border);
            margin: 0 8px;
        }

        /* FORM SECTIONS */
        .form-section {
            padding: 32px 32px 0;
        }

        .form-section:last-of-type {
            padding-bottom: 32px;
        }

        .section-heading {
            font-family: var(--font-display);
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .section-heading i {
            color: var(--teal);
        }

        /* FORM FIELDS */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1/-1;
        }

        .form-label {
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            color: var(--mid);
        }

        .form-label .required {
            color: #e74c3c;
        }

        .form-control {
            height: 48px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 0 16px;
            font-size: .92rem;
            font-family: var(--font-body);
            color: var(--dark);
            background: var(--sand);
            outline: none;
            transition: border-color .2s, background .2s;
        }

        .form-control:focus {
            border-color: var(--teal);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(13, 115, 119, .08);
        }

        textarea.form-control {
            height: 110px;
            padding: 14px 16px;
            resize: vertical;
        }

        select.form-control {
            cursor: pointer;
        }

        /* TIME SLOTS */
        .time-slots {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 4px;
        }

        .slot-btn {
            padding: 10px 8px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: var(--sand);
            font-size: .82rem;
            font-weight: 600;
            color: var(--mid);
            cursor: pointer;
            text-align: center;
            transition: all .2s;
        }

        .slot-btn:hover {
            border-color: var(--teal);
            color: var(--teal);
            background: rgba(13, 115, 119, .04);
        }

        .slot-btn.selected {
            background: var(--teal);
            border-color: var(--teal);
            color: #fff;
            box-shadow: 0 4px 12px rgba(13, 115, 119, .3);
        }

        .slot-btn.unavailable {
            opacity: .4;
            cursor: not-allowed;
            text-decoration: line-through;
        }

        #apptTimeHidden {
            display: none;
        }

        /* ALERTS */
        .alert {
            border-radius: 10px;
            padding: 16px 20px;
            margin: 24px 32px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: .9rem;
        }

        .alert-success {
            background: #eafaf1;
            border: 1px solid #27ae60;
            color: #1e8449;
        }

        .alert-error {
            background: #fdecea;
            border: 1px solid #e74c3c;
            color: #c0392b;
        }

        .alert i {
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* SUBMIT */
        .form-footer {
            padding: 24px 32px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            background: var(--sand);
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--teal);
            color: #fff;
            padding: 14px 36px;
            border-radius: 12px;
            border: none;
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all .2s;
            box-shadow: 0 4px 16px rgba(13, 115, 119, .3);
        }

        .btn-submit:hover {
            background: var(--teal-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(13, 115, 119, .35);
        }

        .secure-note {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .78rem;
            color: var(--muted);
        }

        .secure-note i {
            color: #27ae60;
        }

        /* NOTICE BOXES */
        .info-box {
            background: rgba(13, 115, 119, .05);
            border: 1px solid rgba(13, 115, 119, .15);
            border-radius: var(--radius);
            padding: 14px 18px;
            font-size: .84rem;
            color: var(--mid);
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .info-box i {
            color: var(--teal);
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* RESPONSIVE */
        @media(max-width: 860px) {
            .book-layout {
                grid-template-columns: 1fr;
            }

            .doctor-preview {
                position: static;
                order: -1;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .time-slots {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media(max-width: 480px) {
            .time-slots {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>


    <!-- Hero -->
    <section class="book-hero">
        <div class="hero-inner">
            <div class="breadcrumb">
                <a href="Home.php">Home</a>
                <i class="fas fa-chevron-right"></i>
                <?php if ($doctor): ?>
                    <a href="doctors.php">Doctors</a>
                    <i class="fas fa-chevron-right"></i>
                <?php endif; ?>
                <span>Book Appointment</span>
            </div>
            <h1><i class="fas fa-calendar-check" style="color:var(--accent);font-size:.85em"></i> Book an Appointment</h1>
            <p>Fill in the form below and we'll confirm your slot within minutes.</p>
        </div>
    </section>

    <div class="book-layout">

        <!-- LEFT: FORM -->
        <div class="form-card">

            <!-- Progress -->
            <div class="progress-bar">
                <div class="progress-step active">
                    <div class="step-num">1</div>
                    <div class="step-label">Choose Doctor</div>
                </div>
                <div class="step-connector"></div>
                <div class="progress-step active">
                    <div class="step-num">2</div>
                    <div class="step-label">Pick Date & Time</div>
                </div>
                <div class="step-connector"></div>
                <div class="progress-step">
                    <div class="step-num">3</div>
                    <div class="step-label">Confirm</div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-circle-check"></i>
                    <div><?= htmlspecialchars($success) ?></div>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-error"><i class="fas fa-triangle-exclamation"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="book_appointment.php<?= $doctorId ? '?doctor_id=' . $doctorId : '' ?>" id="appointmentForm" novalidate>

                <!-- Section 1: Doctor -->
                <div class="form-section">
                    <div class="section-heading"><i class="fas fa-user-doctor"></i> Select a Doctor</div>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label class="form-label">Doctor <span class="required">*</span></label>
                            <select name="doctor_id" id="doctorSelect" class="form-control" required onchange="updatePreview(this)">
                                <option value="">— Choose a specialist —</option>
                                <?php foreach ($allDoctors as $d):
                                    $sel = ($doctorId && $d['id'] == $doctorId) ? 'selected' : '';
                                    $fee = number_format((float)($d['consultation_fee'] ?? 0), 0);
                                ?>
                                    <option value="<?= (int)$d['id'] ?>" <?= $sel ?>
                                        data-name="Dr. <?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?>"
                                        data-spec="<?= htmlspecialchars($d['specialization'] ?? '') ?>"
                                        data-fee="LKR <?= $fee ?>"
                                        data-hosp="<?= htmlspecialchars($d['hospital'] ?? '') ?>"
                                        data-city="<?= htmlspecialchars($d['location'] ?? $d['city'] ?? '') ?>"
                                        data-avail="<?= htmlspecialchars($d['availability'] ?? 'Mon–Fri') ?>"
                                        data-initials="<?= strtoupper(substr($d['first_name'], 0, 1) . substr($d['last_name'], 0, 1)) ?>">
                                        Dr. <?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?> — <?= htmlspecialchars($d['specialization'] ?? 'General') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Date & Time -->
                <div class="form-section">
                    <div class="section-heading"><i class="fas fa-calendar-days"></i> Choose Date & Time</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Appointment Date <span class="required">*</span></label>
                            <input type="date" name="appt_date" id="apptDate" class="form-control"
                                min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                max="<?= date('Y-m-d', strtotime('+60 days')) ?>"
                                value="<?= htmlspecialchars($_POST['appt_date'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Preferred Time <span class="required">*</span></label>
                            <div class="time-slots" id="timeSlots">
                                <?php
                                $slots = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'];
                                $selTime = $_POST['appt_time'] ?? '';
                                foreach ($slots as $t):
                                    $disp = date('g:i A', strtotime($t));
                                ?>
                                    <button type="button" class="slot-btn <?= $selTime === $t ? 'selected' : '' ?>"
                                        onclick="selectSlot('<?= $t ?>','<?= $disp ?>', this)">
                                        <?= $disp ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="appt_time" id="apptTimeHidden" value="<?= htmlspecialchars($selTime) ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Details -->
                <div class="form-section">
                    <div class="section-heading"><i class="fas fa-notes-medical"></i> Appointment Details</div>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label class="form-label">Reason for Visit <span class="required">*</span></label>
                            <textarea name="reason" id="reasonField" class="form-control" placeholder="Briefly describe your symptoms or reason for the visit…" required><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Visit Type</label>
                            <select name="visit_type" class="form-control">
                                <option value="in_person">In-Person Visit</option>
                                <option value="video">Video Consultation</option>
                                <option value="follow_up">Follow-up</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control">
                                <option value="routine">Routine</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control" style="height:80px" placeholder="Allergies, medications, special requests…"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="info-box" style="margin-top:18px">
                        <i class="fas fa-circle-info"></i>
                        <span>Please arrive 10 minutes early. Bring your National ID and any previous reports or prescriptions.</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="form-footer">
                    <div class="secure-note">
                        <i class="fas fa-lock"></i> Your information is encrypted and secure
                    </div>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="Login.php?redirect=book_appointment.php<?= $doctorId ? '?doctor_id=' . $doctorId : '' ?>"
                            style="display:inline-flex;align-items:center;gap:8px;background:var(--teal);color:#fff;padding:13px 28px;border-radius:12px;text-decoration:none;font-weight:700;font-size:.95rem">
                            <i class="fas fa-sign-in-alt"></i> Login to Book
                        </a>
                    <?php else: ?>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-calendar-check"></i> Confirm Appointment
                        </button>
                    <?php endif; ?>
                </div>

            </form>
        </div>

        <!-- RIGHT: DOCTOR PREVIEW -->
        <div class="doctor-preview" id="doctorPreview" style="<?= !$doctor ? 'display:none' : '' ?>">
            <?php if ($doctor):
                $initials = strtoupper(substr($doctor['first_name'], 0, 1) . substr($doctor['last_name'], 0, 1));
            ?>
                <div class="preview-header">
                    <div class="preview-avatar" id="previewInitials"><?= $initials ?></div>
                    <div class="preview-name" id="previewName">Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?></div>
                    <div class="preview-spec" id="previewSpec"><?= htmlspecialchars($doctor['specialization'] ?? 'Specialist') ?></div>
                </div>
                <div class="preview-body">
                    <div class="preview-row"><i class="fas fa-hospital-alt"></i><span id="previewHosp"><?= htmlspecialchars($doctor['hospital'] ?? '') ?></span></div>
                    <div class="preview-row"><i class="fas fa-map-pin"></i><span id="previewCity"><?= htmlspecialchars($doctor['location'] ?? $doctor['city'] ?? 'Sri Lanka') ?></span></div>
                    <div class="preview-row"><i class="fas fa-clock"></i><span id="previewAvail"><?= htmlspecialchars($doctor['availability'] ?? 'Mon–Fri') ?></span></div>
                    <?php if (($doctor['rating'] ?? 0) > 0): ?>
                        <div class="preview-row"><i class="fas fa-star" style="color:#f59e0b"></i><span><?= number_format((float)$doctor['rating'], 1) ?> / 5.0 rating</span></div>
                    <?php endif; ?>
                    <div class="preview-fee">
                        <div>
                            <div class="fee-label">Consultation Fee</div>
                        </div>
                        <div class="fee-val" id="previewFee">LKR <?= number_format((float)($doctor['consultation_fee'] ?? 0), 0) ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="preview-header">
                    <div class="preview-avatar" id="previewInitials">?</div>
                    <div class="preview-name" id="previewName">Select a Doctor</div>
                    <div class="preview-spec" id="previewSpec">Choose from the form</div>
                </div>
                <div class="preview-body">
                    <div class="preview-row"><i class="fas fa-hospital-alt"></i><span id="previewHosp">—</span></div>
                    <div class="preview-row"><i class="fas fa-map-pin"></i><span id="previewCity">—</span></div>
                    <div class="preview-row"><i class="fas fa-clock"></i><span id="previewAvail">—</span></div>
                    <div class="preview-fee">
                        <div>
                            <div class="fee-label">Consultation Fee</div>
                        </div>
                        <div class="fee-val" id="previewFee">—</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        function selectSlot(val, disp, el) {
            document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('apptTimeHidden').value = val;
        }

        function updatePreview(sel) {
            const opt = sel.options[sel.selectedIndex];
            if (!opt.value) return;
            document.getElementById('doctorPreview').style.display = '';
            document.getElementById('previewInitials').textContent = opt.dataset.initials || '?';
            document.getElementById('previewName').textContent = opt.dataset.name || '—';
            document.getElementById('previewSpec').textContent = opt.dataset.spec || '—';
            document.getElementById('previewHosp').textContent = opt.dataset.hosp || '—';
            document.getElementById('previewCity').textContent = opt.dataset.city || '—';
            document.getElementById('previewAvail').textContent = opt.dataset.avail || '—';
            document.getElementById('previewFee').textContent = opt.dataset.fee || '—';
        }

        // Form validation
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            const time = document.getElementById('apptTimeHidden').value;
            if (!time) {
                e.preventDefault();
                alert('Please select an appointment time slot.');
            }
        });

        // Highlight today's date-restricted min
        document.getElementById('apptDate').addEventListener('change', function() {
            // Could regenerate slots based on doctor's available days here
        });
    </script>

<?php include 'footer.php'; ?>
