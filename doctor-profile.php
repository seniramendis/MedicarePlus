<?php
require_once 'functions.php';

$docId  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$doctor = $docId ? fetch_doctor_by_id($docId) : null;

$pageTitle = $doctor
    ? 'Dr. ' . $doctor['first_name'] . ' ' . $doctor['last_name'] . ' — Medicare Plus'
    : 'Doctor Not Found — Medicare Plus';

include 'header.php';

$specColours = [
    'Cardiology'           => ['#c0392b','#fdecea'],
    'Neurology'            => ['#6c3483','#f3e8f9'],
    'Paediatrics'          => ['#1565c0','#e3f0fc'],
    'Pediatrics'           => ['#1565c0','#e3f0fc'],
    'Dermatology'          => ['#b7950b','#fef9e7'],
    'Orthopaedics'         => ['#1a5276','#eaf2fc'],
    'Orthopedics'          => ['#1a5276','#eaf2fc'],
    'General Practitioner' => ['#1e8449','#e9f7ef'],
    'Gynaecology'          => ['#b03a8a','#fce4f4'],
    'ENT'                  => ['#ca6f1e','#fef3e2'],
    'Endocrinology'        => ['#1abc9c','#e8faf5'],
    'Pulmonology'          => ['#2980b9','#eaf4fb'],
];
?>

<?php if ($doctor):
    $spec     = htmlspecialchars($doctor['specialization']);
    $name     = htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']);
    $initials = strtoupper(substr($doctor['first_name'],0,1) . substr($doctor['last_name'],0,1));
    $rating   = (float)$doctor['rating'];
    $fee      = (float)$doctor['consultation_fee'];
    $exp      = (int)$doctor['experience_years'];
    $hosp     = htmlspecialchars($doctor['hospital'] ?? '');
    $city     = htmlspecialchars($doctor['location'] ?? $doctor['city'] ?? 'Sri Lanka');
    $avail    = htmlspecialchars($doctor['availability'] ?? 'Mon – Fri, 9am – 5pm');
    $bio      = nl2br(htmlspecialchars($doctor['bio'] ?: 'No biography provided for this specialist.'));
    $qual     = htmlspecialchars($doctor['qualification'] ?? '');
    $colours  = $specColours[$doctor['specialization']] ?? ['var(--teal)','rgba(13,115,119,.08)'];
    $accentC  = $colours[0];
    $lightC   = $colours[1];
?>

<!-- ══════════════════════ PROFILE HERO ═════════════════ -->
<section style="background:linear-gradient(135deg,var(--teal-dark) 0%,var(--teal) 60%,var(--leaf) 100%);padding:64px 0 56px;position:relative;overflow:hidden">
    <div style="position:absolute;inset:0;background:radial-gradient(circle at 90% 10%,rgba(255,255,255,.07) 0%,transparent 45%);pointer-events:none"></div>
    <div class="container" style="position:relative">

        <!-- Breadcrumb -->
        <a href="doctors.php" style="display:inline-flex;align-items:center;gap:6px;color:rgba(255,255,255,.65);font-size:.84rem;text-decoration:none;margin-bottom:28px;transition:color .2s"
            onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.65)'">
            <i class="fas fa-arrow-left" style="font-size:.72rem"></i> Back to all doctors
        </a>

        <div style="display:flex;align-items:center;gap:32px;flex-wrap:wrap">
            <!-- Avatar -->
            <div style="width:110px;height:110px;border-radius:24px;background:<?= $lightC ?>;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:2.4rem;font-weight:700;color:<?= $accentC ?>;border:3px solid rgba(255,255,255,.25);flex-shrink:0">
                <?= $initials ?>
            </div>

            <div>
                <div style="display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);border-radius:50px;padding:5px 16px;font-size:.78rem;font-weight:600;color:rgba(255,255,255,.9);margin-bottom:12px">
                    <i class="fas fa-stethoscope"></i> <?= $spec ?>
                </div>
                <h1 style="font-family:var(--font-display);font-size:clamp(1.6rem,3vw,2.2rem);color:#fff;margin:0 0 10px">Dr. <?= $name ?></h1>
                <div style="display:flex;flex-wrap:wrap;gap:14px;font-size:.88rem;color:rgba(255,255,255,.75)">
                    <?php if ($hosp): ?>
                    <span><i class="fas fa-hospital-alt" style="margin-right:5px;color:var(--accent)"></i><?= $hosp ?></span>
                    <?php endif; ?>
                    <span><i class="fas fa-map-pin" style="margin-right:5px;color:var(--accent)"></i><?= $city ?></span>
                    <?php if ($exp > 0): ?>
                    <span><i class="fas fa-award" style="margin-right:5px;color:var(--accent)"></i><?= $exp ?> yrs experience</span>
                    <?php endif; ?>
                    <?php if ($rating > 0): ?>
                    <span><i class="fas fa-star" style="margin-right:5px;color:var(--accent)"></i><?= number_format($rating,1) ?> rating</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════ PROFILE BODY ═════════════════ -->
<section class="section">
    <div class="container">
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:28px;align-items:start">

            <!-- LEFT: About & Bio -->
            <div style="display:flex;flex-direction:column;gap:24px">

                <!-- About card -->
                <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:32px;box-shadow:var(--shadow-sm)">
                    <h2 style="font-family:var(--font-display);font-size:1.2rem;color:var(--dark);margin:0 0 18px;padding-bottom:14px;border-bottom:2px solid var(--border);display:flex;align-items:center;gap:9px">
                        <i class="fas fa-user-doctor" style="color:var(--teal)"></i> About Dr. <?= $name ?>
                    </h2>
                    <p style="color:var(--mid);line-height:1.8;margin:0"><?= $bio ?></p>

                    <?php if ($qual): ?>
                    <div style="margin-top:20px;padding:14px 18px;background:var(--sand);border-radius:var(--radius);border-left:3px solid var(--teal)">
                        <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);margin-bottom:4px;font-weight:600">Qualifications</div>
                        <div style="font-size:.9rem;color:var(--dark)"><?= $qual ?></div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Availability card -->
                <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:28px;box-shadow:var(--shadow-sm)">
                    <h2 style="font-family:var(--font-display);font-size:1.1rem;color:var(--dark);margin:0 0 18px;padding-bottom:12px;border-bottom:2px solid var(--border);display:flex;align-items:center;gap:9px">
                        <i class="fas fa-calendar-alt" style="color:var(--teal)"></i> Schedule &amp; Availability
                    </h2>
                    <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:rgba(13,115,119,.06);border-radius:10px;border:1px solid rgba(13,115,119,.12)">
                        <div style="width:10px;height:10px;border-radius:50%;background:#27ae60;flex-shrink:0"></div>
                        <span style="font-size:.9rem;color:var(--dark);font-weight:500"><?= $avail ?></span>
                    </div>
                    <p style="margin:14px 0 0;font-size:.85rem;color:var(--muted)">
                        <i class="fas fa-info-circle" style="margin-right:5px"></i>
                        Slots are subject to availability. Book online to confirm your preferred time.
                    </p>
                </div>
            </div>

            <!-- RIGHT: Details & Book CTA -->
            <div style="display:flex;flex-direction:column;gap:20px">

                <!-- Details card -->
                <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:28px;box-shadow:var(--shadow-sm)">
                    <h2 style="font-family:var(--font-display);font-size:1.1rem;color:var(--dark);margin:0 0 18px;padding-bottom:12px;border-bottom:2px solid var(--border);display:flex;align-items:center;gap:9px">
                        <i class="fas fa-circle-info" style="color:var(--teal)"></i> Details
                    </h2>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:0">
                        <?php
                        $details = [
                            ['fas fa-briefcase-medical', 'Specialisation', $spec],
                            ['fas fa-map-pin',            'Location',       $city],
                        ];
                        if ($hosp) $details[] = ['fas fa-hospital-alt','Hospital',$hosp];
                        if ($exp>0) $details[] = ['fas fa-award','Experience',$exp.' years'];
                        if ($rating>0) $details[] = ['fas fa-star','Rating',number_format($rating,1).' / 5.0'];
                        foreach ($details as $d):
                        ?>
                        <li style="display:flex;align-items:flex-start;gap:12px;padding:11px 0;border-bottom:1px solid var(--border);font-size:.88rem">
                            <i class="<?= $d[0] ?>" style="color:var(--teal);width:16px;flex-shrink:0;margin-top:2px"></i>
                            <div>
                                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.6px;color:var(--muted);margin-bottom:2px;font-weight:600"><?= $d[1] ?></div>
                                <div style="color:var(--dark);font-weight:500"><?= $d[2] ?></div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Booking CTA card -->
                <div style="background:linear-gradient(135deg,var(--teal-dark),var(--teal));border-radius:var(--radius-lg);padding:28px;box-shadow:var(--shadow-md);position:relative;overflow:hidden">
                    <div style="position:absolute;right:-20px;top:-20px;width:100px;height:100px;background:rgba(255,255,255,.07);border-radius:50%;pointer-events:none"></div>
                    <div style="position:relative">
                        <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.8px;color:rgba(255,255,255,.65);margin-bottom:6px;font-weight:600">Consultation Fee</div>
                        <div style="font-family:var(--font-display);font-size:1.8rem;font-weight:700;color:#fff;margin-bottom:4px">
                            LKR <?= number_format($fee,0) ?>
                        </div>
                        <div style="font-size:.8rem;color:rgba(255,255,255,.6);margin-bottom:20px">per consultation</div>
                        <a href="book_appointment.php?doctor_id=<?= (int)$doctor['id'] ?>"
                            style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px;background:var(--accent);color:var(--dark);border-radius:var(--radius);font-weight:700;font-size:.95rem;text-decoration:none;transition:all .2s;box-shadow:0 4px 14px rgba(0,0,0,.2)"
                            onmouseover="this.style.background='var(--accent-dark)';this.style.transform='translateY(-1px)'"
                            onmouseout="this.style.background='var(--accent)';this.style.transform=''">
                            <i class="fas fa-calendar-check"></i> Book Appointment
                        </a>
                        <div style="display:flex;align-items:center;gap:6px;justify-content:center;margin-top:12px;font-size:.78rem;color:rgba(255,255,255,.55)">
                            <i class="fas fa-shield-check"></i> Secure &amp; Instant Confirmation
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php else: ?>

<!-- ══════════════════════ NOT FOUND ════════════════════ -->
<section class="section">
    <div class="container" style="text-align:center;padding:80px 20px">
        <div style="width:80px;height:80px;background:rgba(13,115,119,.08);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px">
            <i class="fas fa-user-doctor" style="font-size:2rem;color:var(--teal)"></i>
        </div>
        <h2 style="color:var(--dark);margin-bottom:10px">Doctor Not Found</h2>
        <p style="color:var(--muted);margin-bottom:28px">The doctor profile you're looking for could not be found.</p>
        <a href="doctors.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Browse All Doctors
        </a>
    </div>
</section>

<?php endif; ?>

<?php include 'footer.php'; ?>

<style>
@media (max-width: 700px) {
    section div[style*="grid-template-columns:2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
