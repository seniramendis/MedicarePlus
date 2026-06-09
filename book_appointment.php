<?php
require_once 'auth.php';
require_role('patient');

$pageTitle = 'Book Appointment — Medicare Plus';
$errors    = [];
$success   = '';

$selectedDoctorId = filter_input(INPUT_GET, 'doctor_id', FILTER_VALIDATE_INT);
$doctor           = $selectedDoctorId ? fetch_doctor_by_id($selectedDoctorId) : null;
$patient          = fetch_patient_by_user_id($_SESSION['user_id']);

if (!$patient) {
    create_patient_profile($_SESSION['user_id']);
    $patient = fetch_patient_by_user_id($_SESSION['user_id']);
}

$availableDoctors = fetch_all_doctors();
$appointmentDate  = '';
$notes            = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = filter_input(INPUT_POST, 'csrf_token', FILTER_UNSAFE_RAW) ?? '';
    if (!csrf_verify($token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }

    $selectedDoctorId = filter_input(INPUT_POST, 'doctor_id', FILTER_VALIDATE_INT);
    $appointmentDate  = trim(filter_input(INPUT_POST, 'appointment_date', FILTER_UNSAFE_RAW) ?: '');
    $notes            = trim(filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?: '');

    if (!$selectedDoctorId)  $errors[] = 'Please select a doctor.';
    if (!$appointmentDate)   $errors[] = 'Please choose a date and time.';

    $doctor = $selectedDoctorId ? fetch_doctor_by_id($selectedDoctorId) : null;
    if (!$doctor) $errors[] = 'Doctor not found. Please choose another.';

    $appointmentDateTime = null;
    if (empty($errors)) {
        $appointmentDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $appointmentDate);
        if (!$appointmentDateTime) {
            $errors[] = 'Invalid date format. Please use the date picker.';
        } elseif ($appointmentDateTime < new DateTime()) {
            $errors[] = 'Appointment date must be in the future.';
        }
    }

    if (empty($errors) && doctor_has_conflict($selectedDoctorId, $appointmentDateTime->format('Y-m-d H:i:s'))) {
        $errors[] = 'The doctor is already booked at that time. Please choose another slot.';
    }

    if (empty($errors)) {
        $created = create_appointment($patient['id'], $selectedDoctorId, $appointmentDateTime->format('Y-m-d H:i:s'), $notes);
        if ($created) {
            $success         = 'Your appointment request has been submitted! The doctor will confirm shortly.';
            $appointmentDate = '';
            $notes           = '';
        } else {
            $errors[] = 'Unable to schedule the appointment. Please try again.';
        }
    }
}

include 'header.php';
?>

<!-- ══════════════════════ PAGE HEADER ══════════════════ -->
<section style="background:linear-gradient(135deg,var(--teal-dark),var(--teal));padding:56px 0 48px;position:relative;overflow:hidden">
    <div style="position:absolute;inset:0;background:radial-gradient(circle at 85% 15%,rgba(255,255,255,.07) 0%,transparent 50%);pointer-events:none"></div>
    <div class="container" style="position:relative;text-align:center">
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:50px;padding:5px 18px;font-size:.78rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.9);margin-bottom:18px">
            <i class="fas fa-calendar-check"></i> Appointment Booking
        </div>
        <h1 style="font-family:var(--font-display);font-size:clamp(1.8rem,3.5vw,2.5rem);color:#fff;margin:0 0 12px">
            Book Your Consultation
        </h1>
        <p style="color:rgba(255,255,255,.72);font-size:1rem;max-width:480px;margin:0 auto">
            Select your doctor, choose a convenient slot and confirm your booking in minutes.
        </p>

        <!-- Progress steps -->
        <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-top:32px">
            <?php
            $steps = ['Choose Doctor', 'Pick Date & Time', 'Confirm'];
            foreach ($steps as $i => $s):
                $stepN = $i + 1;
                $isDone = $success && $stepN < 3;
                $isActive = !$success && $stepN === 1;
                $circleBg = $isDone ? '#27ae60' : ($isActive ? 'var(--accent)' : 'rgba(255,255,255,.2)');
                $circleColor = $isDone || $isActive ? 'var(--dark)' : 'rgba(255,255,255,.6)';
                $labelColor  = $isDone || $isActive ? '#fff' : 'rgba(255,255,255,.55)';
            ?>
                <div style="display:flex;align-items:center;gap:8px">
                    <div style="width:32px;height:32px;border-radius:50%;background:<?= $circleBg ?>;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:700;color:<?= $circleColor ?>">
                        <?= $isDone ? '<i class="fas fa-check" style="font-size:.7rem"></i>' : $stepN ?>
                    </div>
                    <span style="font-size:.82rem;font-weight:600;color:<?= $labelColor ?>"><?= $s ?></span>
                </div>
                <?php if ($i < count($steps) - 1): ?>
                    <div style="width:50px;height:2px;background:rgba(255,255,255,.2);margin:0 6px"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════ FORM BODY ════════════════════ -->
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start">

            <!-- Main form -->
            <div>
                <?php if ($success): ?>
                    <!-- Success state -->
                    <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid rgba(39,174,96,.3);padding:48px 36px;text-align:center;box-shadow:var(--shadow-sm)">
                        <div style="width:72px;height:72px;background:rgba(39,174,96,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
                            <i class="fas fa-check-circle" style="font-size:2rem;color:#27ae60"></i>
                        </div>
                        <h2 style="color:var(--dark);margin-bottom:10px">Appointment Requested!</h2>
                        <p style="color:var(--mid);margin-bottom:28px;max-width:400px;margin-left:auto;margin-right:auto"><?= htmlspecialchars($success) ?></p>
                        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
                            <a href="dashboard_patient.php" class="btn btn-primary"><i class="fas fa-th-large"></i> Go to Dashboard</a>
                            <a href="book_appointment.php" class="btn btn-outline"><i class="fas fa-calendar-plus"></i> Book Another</a>
                        </div>
                    </div>
                <?php else: ?>

                    <!-- Errors -->
                    <?php if (!empty($errors)): ?>
                        <div style="background:#fdecea;border:1px solid #f1a7a0;border-radius:var(--radius);padding:14px 18px;margin-bottom:24px;display:flex;gap:10px;align-items:flex-start">
                            <i class="fas fa-exclamation-circle" style="color:#c0392b;margin-top:2px;flex-shrink:0"></i>
                            <ul style="margin:0;padding-left:16px;list-style:disc">
                                <?php foreach ($errors as $e): ?>
                                    <li style="color:#922b21;font-size:.88rem"><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="book_appointment.php<?= $selectedDoctorId ? '?doctor_id=' . (int)$selectedDoctorId : '' ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                        <!-- STEP 1: Choose doctor -->
                        <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);overflow:hidden;margin-bottom:20px;box-shadow:var(--shadow-sm)">
                            <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
                                <div style="width:38px;height:38px;background:rgba(13,115,119,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:.95rem;flex-shrink:0">
                                    <i class="fas fa-user-doctor"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;color:var(--dark);font-size:.98rem">Select a Doctor</div>
                                    <div style="font-size:.78rem;color:var(--muted)">Choose the specialist you'd like to consult</div>
                                </div>
                            </div>
                            <div style="padding:24px">
                                <?php if ($doctor): ?>
                                    <!-- Pre-selected doctor -->
                                    <input type="hidden" name="doctor_id" value="<?= (int)$doctor['id'] ?>">
                                    <div style="display:flex;align-items:center;gap:14px;padding:16px;background:rgba(13,115,119,.05);border:1.5px solid rgba(13,115,119,.25);border-radius:var(--radius)">
                                        <div style="width:48px;height:48px;border-radius:12px;background:rgba(13,115,119,.12);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:1.1rem;font-weight:700;color:var(--teal);flex-shrink:0">
                                            <?= strtoupper(substr($doctor['first_name'], 0, 1) . substr($doctor['last_name'], 0, 1)) ?>
                                        </div>
                                        <div style="flex:1">
                                            <div style="font-weight:700;color:var(--dark)">Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?></div>
                                            <div style="font-size:.82rem;color:var(--muted)"><?= htmlspecialchars($doctor['specialization']) ?> · <?= htmlspecialchars($doctor['hospital'] ?? '') ?></div>
                                        </div>
                                        <a href="book_appointment.php" style="font-size:.78rem;color:var(--teal);text-decoration:none">Change</a>
                                    </div>
                                <?php else: ?>
                                    <!-- Doctor selector -->
                                    <div class="form-group" style="margin-bottom:0">
                                        <label for="doctor_id" style="display:block;font-weight:600;color:var(--dark);margin-bottom:7px;font-size:.88rem">Choose your doctor</label>
                                        <select name="doctor_id" id="doctor_id" class="form-control" required>
                                            <option value="">— Select a specialist —</option>
                                            <?php foreach ($availableDoctors as $d): ?>
                                                <option value="<?= (int)$d['id'] ?>" <?= ((int)($selectedDoctorId ?? 0) === (int)$d['id']) ? 'selected' : '' ?>>
                                                    Dr. <?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?> — <?= htmlspecialchars($d['specialization']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- STEP 2: Date & Time -->
                        <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);overflow:hidden;margin-bottom:20px;box-shadow:var(--shadow-sm)">
                            <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
                                <div style="width:38px;height:38px;background:rgba(232,168,56,.12);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:.95rem;flex-shrink:0">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;color:var(--dark);font-size:.98rem">Date &amp; Time</div>
                                    <div style="font-size:.78rem;color:var(--muted)">Pick your preferred appointment slot</div>
                                </div>
                            </div>
                            <div style="padding:24px">
                                <div class="form-group" style="margin-bottom:0">
                                    <label for="appointment_date" style="display:block;font-weight:600;color:var(--dark);margin-bottom:7px;font-size:.88rem">Appointment date &amp; time</label>
                                    <input type="datetime-local" id="appointment_date" name="appointment_date" class="form-control"
                                        value="<?= htmlspecialchars($appointmentDate) ?>"
                                        min="<?= date('Y-m-d\TH:i', strtotime('+1 hour')) ?>"
                                        required>
                                    <p style="margin:8px 0 0;font-size:.78rem;color:var(--muted)">
                                        <i class="fas fa-info-circle" style="margin-right:4px"></i>Appointments must be at least 1 hour from now. Working hours: 8am – 6pm.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 3: Notes -->
                        <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);overflow:hidden;margin-bottom:28px;box-shadow:var(--shadow-sm)">
                            <div style="padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
                                <div style="width:38px;height:38px;background:rgba(58,125,68,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--leaf);font-size:.95rem;flex-shrink:0">
                                    <i class="fas fa-notes-medical"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;color:var(--dark);font-size:.98rem">Symptoms / Notes</div>
                                    <div style="font-size:.78rem;color:var(--muted)">Optional — helps the doctor prepare</div>
                                </div>
                            </div>
                            <div style="padding:24px">
                                <div class="form-group" style="margin-bottom:0">
                                    <label for="notes" style="display:block;font-weight:600;color:var(--dark);margin-bottom:7px;font-size:.88rem">Describe your symptoms or reason for visit</label>
                                    <textarea id="notes" name="notes" class="form-control" rows="4"
                                        placeholder="e.g. Persistent chest pain for 3 days, shortness of breath when climbing stairs…"
                                        style="resize:vertical"><?= htmlspecialchars($notes) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-calendar-check"></i> Confirm Appointment Request
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Right sidebar -->
            <div style="display:flex;flex-direction:column;gap:20px">

                <!-- Selected doctor summary -->
                <?php if ($doctor): ?>
                    <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:24px;box-shadow:var(--shadow-sm)">
                        <h3 style="font-family:var(--font-display);font-size:1rem;color:var(--dark);margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
                            Your Specialist
                        </h3>
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
                            <div style="width:52px;height:52px;border-radius:14px;background:rgba(13,115,119,.1);display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:1.2rem;font-weight:700;color:var(--teal);flex-shrink:0">
                                <?= strtoupper(substr($doctor['first_name'], 0, 1) . substr($doctor['last_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:700;color:var(--dark);font-size:.95rem">Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?></div>
                                <div style="font-size:.8rem;color:var(--muted)"><?= htmlspecialchars($doctor['specialization']) ?></div>
                            </div>
                        </div>
                        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;font-size:.85rem;color:var(--muted)">
                            <?php if ($doctor['hospital'] ?? ''): ?>
                                <li style="display:flex;align-items:center;gap:8px"><i class="fas fa-hospital-alt" style="color:var(--teal);width:14px"></i><?= htmlspecialchars($doctor['hospital']) ?></li>
                            <?php endif; ?>
                            <li style="display:flex;align-items:center;gap:8px"><i class="fas fa-map-pin" style="color:var(--teal);width:14px"></i><?= htmlspecialchars($doctor['location'] ?? $doctor['city'] ?? 'Sri Lanka') ?></li>
                            <?php if ((float)$doctor['rating'] > 0): ?>
                                <li style="display:flex;align-items:center;gap:8px"><i class="fas fa-star" style="color:var(--accent);width:14px"></i><?= number_format((float)$doctor['rating'], 1) ?> rating</li>
                            <?php endif; ?>
                        </ul>
                        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border)">
                            <div style="font-size:.75rem;color:var(--muted);text-transform:uppercase;letter-spacing:.6px;font-weight:600;margin-bottom:4px">Consultation Fee</div>
                            <div style="font-family:var(--font-display);font-size:1.4rem;font-weight:700;color:var(--teal-dark)">LKR <?= number_format((float)$doctor['consultation_fee'], 0) ?></div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Trust badges -->
                <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:22px;box-shadow:var(--shadow-sm)">
                    <h3 style="font-family:var(--font-display);font-size:.92rem;color:var(--dark);margin:0 0 14px">Why book with Medicare Plus?</h3>
                    <?php
                    $perks = [
                        ['fas fa-shield-check', 'Verified Specialists', 'All doctors are SLMC registered'],
                        ['fas fa-clock',         'Fast Confirmation',    'Typically confirmed within 2 hrs'],
                        ['fas fa-file-medical',  'Digital Records',      'Reports saved securely online'],
                        ['fas fa-undo-alt',      'Easy Rescheduling',    'Change or cancel anytime'],
                    ];
                    foreach ($perks as $p): ?>
                        <div style="display:flex;align-items:flex-start;gap:11px;padding:9px 0;border-bottom:1px solid var(--border)">
                            <div style="width:32px;height:32px;background:rgba(13,115,119,.08);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--teal);flex-shrink:0;font-size:.82rem">
                                <i class="<?= $p[0] ?>"></i>
                            </div>
                            <div>
                                <div style="font-size:.85rem;font-weight:600;color:var(--dark)"><?= $p[1] ?></div>
                                <div style="font-size:.78rem;color:var(--muted)"><?= $p[2] ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Help box -->
                <div style="background:rgba(13,115,119,.05);border:1px solid rgba(13,115,119,.15);border-radius:var(--radius-lg);padding:20px">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-weight:600;color:var(--teal);font-size:.88rem">
                        <i class="fas fa-headset"></i> Need Help?
                    </div>
                    <p style="font-size:.82rem;color:var(--muted);margin:0 0 10px">Our support team is available Mon–Fri, 8am–6pm.</p>
                    <a href="contact.php" style="font-size:.82rem;font-weight:600;color:var(--teal);text-decoration:none">
                        Contact us <i class="fas fa-arrow-right" style="font-size:.68rem"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<style>
    @media (max-width: 880px) {
        section div[style*="grid-template-columns:1fr 360px"] {
            grid-template-columns: 1fr !important;
        }
    }

    .btn-block {
        width: 100%;
        justify-content: center;
    }
</style>