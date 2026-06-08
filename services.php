<?php
$pageTitle = 'Our Services — Medicare Plus';
require_once 'functions.php';
include 'header.php';

$services = [];
$conn = get_db_connection();
if ($conn) {
    try {
        $r = $conn->query("SELECT * FROM services ORDER BY category, name");
        if ($r) $services = $r->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {}
}

// Static services for when DB has no data — Sri Lankan context
$staticServices = [
    [
        'category' => 'Heart & Vascular',
        'icon'     => 'fas fa-heartbeat',
        'colour'   => '#c0392b',
        'light'    => '#fdecea',
        'name'     => 'Cardiology',
        'desc'     => 'Nationally recognised cardiologists at Colombo National Hospital and Lanka Hospital offering ECG, Echo, angioplasty consultations and heart failure management.',
        'price'    => 3500,
    ],
    [
        'category' => 'Brain & Nerves',
        'icon'     => 'fas fa-brain',
        'colour'   => '#6c3483',
        'light'    => '#f3e8f9',
        'name'     => 'Neurology',
        'desc'     => 'Advanced neurological assessments, epilepsy management, stroke rehabilitation and headache clinics at Teaching Hospitals in Colombo, Kandy and Galle.',
        'price'    => 4200,
    ],
    [
        'category' => "Children's Health",
        'icon'     => 'fas fa-baby',
        'colour'   => '#1565c0',
        'light'    => '#e3f0fc',
        'name'     => 'Paediatrics',
        'desc'     => 'Compassionate child health services from Lady Ridgeway Hospital specialists. Growth monitoring, vaccinations, neonatal care and developmental assessments.',
        'price'    => 2800,
    ],
    [
        'category' => 'Bone & Joint',
        'icon'     => 'fas fa-bone',
        'colour'   => '#1a5276',
        'light'    => '#eaf2fc',
        'name'     => 'Orthopaedics',
        'desc'     => 'Joint replacement surgery, sports injury management, spinal care and fracture treatment by experienced surgeons at Colombo and Peradeniya hospitals.',
        'price'    => 3800,
    ],
    [
        "category" => "Women's Health",
        'icon'     => 'fas fa-venus',
        'colour'   => '#b03a8a',
        'light'    => '#fce4f4',
        'name'     => 'Gynaecology',
        'desc'     => 'Comprehensive women\'s health services including maternity care, gynaecological oncology, family planning and minimally-invasive surgery at Castle Street Hospital.',
        'price'    => 3000,
    ],
    [
        'category' => 'Skin Care',
        'icon'     => 'fas fa-leaf',
        'colour'   => '#b7950b',
        'light'    => '#fef9e7',
        'name'     => 'Dermatology',
        'desc'     => 'Diagnosis and treatment of skin, hair and nail conditions. Acne, eczema, psoriasis, skin cancer screening and cosmetic dermatology at Colombo clinics.',
        'price'    => 2500,
    ],
    [
        'category' => 'Respiratory',
        'icon'     => 'fas fa-lungs',
        'colour'   => '#2980b9',
        'light'    => '#eaf4fb',
        'name'     => 'Pulmonology',
        'desc'     => 'Expert respiratory care for asthma, COPD, lung infections and sleep apnoea. Spirometry and bronchoscopy available at Karapitiya and Colombo South hospitals.',
        'price'    => 3200,
    ],
    [
        'category' => 'Hormones & Metabolism',
        'icon'     => 'fas fa-flask',
        'colour'   => '#1abc9c',
        'light'    => '#e8faf5',
        'name'     => 'Endocrinology',
        'desc'     => 'Specialist management of diabetes, thyroid disorders, obesity, PCOS and adrenal conditions at National Hospital and Nawaloka Colombo.',
        'price'    => 2900,
    ],
    [
        'category' => 'Ear Nose Throat',
        'icon'     => 'fas fa-ear-listen',
        'colour'   => '#ca6f1e',
        'light'    => '#fef3e2',
        'name'     => 'ENT',
        'desc'     => 'Hearing loss assessment, sinus surgery, tonsillitis, voice disorders and head & neck tumour management at accredited Sri Lankan ENT centres.',
        'price'    => 2200,
    ],
    [
        'category' => 'Diagnostics',
        'icon'     => 'fas fa-x-ray',
        'colour'   => '#4a5568',
        'light'    => '#f0f4f8',
        'name'     => 'Radiology & Imaging',
        'desc'     => 'Full-body MRI, CT, X-ray, ultrasound and mammography services with same-day report turnaround. Available at 8 locations island-wide.',
        'price'    => 1500,
    ],
    [
        'category' => 'Emergency',
        'icon'     => 'fas fa-truck-medical',
        'colour'   => '#e74c3c',
        'light'    => '#fdecea',
        'name'     => 'Emergency Care',
        'desc'     => '24/7 emergency consultations available through Medicare Plus partner hospitals in Colombo, Kandy and Galle. Triage and stabilisation by experienced ER specialists.',
        'price'    => 0,
    ],
    [
        'category' => 'Mental Health',
        'icon'     => 'fas fa-hand-holding-heart',
        'colour'   => '#8e44ad',
        'light'    => '#f5eef8',
        'name'     => 'Psychiatry',
        'desc'     => 'Confidential mental health support including therapy, mood disorder management, anxiety treatment and addiction counselling at NIMH-affiliated clinics.',
        'price'    => 2500,
    ],
];
?>

<!-- ══════════════════════ HERO ══════════════════════════ -->
<section style="background:linear-gradient(135deg,var(--teal-dark),var(--teal) 60%,var(--leaf) 100%);padding:72px 0 60px;position:relative;overflow:hidden">
    <div style="position:absolute;inset:0;background:radial-gradient(circle at 80% 10%,rgba(255,255,255,.07) 0%,transparent 45%);pointer-events:none"></div>
    <div class="container" style="position:relative;text-align:center">
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:50px;padding:5px 18px;font-size:.78rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.9);margin-bottom:18px">
            <i class="fas fa-hospital"></i> What We Offer
        </div>
        <h1 style="font-family:var(--font-display);font-size:clamp(2rem,4vw,2.8rem);color:#fff;margin:0 0 16px">
            Comprehensive Health Services
        </h1>
        <p style="color:rgba(255,255,255,.72);font-size:1.05rem;max-width:540px;margin:0 auto 32px">
            From routine consultations to specialist care — Medicare Plus connects you with the right doctors across Sri Lanka's top hospitals.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center">
            <?php
            $highlights = [
                ['fas fa-user-doctor', '120+ Specialists'],
                ['fas fa-hospital-alt','22 Hospitals'],
                ['fas fa-map-pin',     '9 Provinces'],
                ['fas fa-star',        'SLMC Verified'],
            ];
            foreach ($highlights as $h): ?>
            <span style="display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:.83rem;font-weight:500;padding:7px 18px;border-radius:30px">
                <i class="<?= $h[0] ?>" style="color:var(--accent)"></i> <?= $h[1] ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════ SERVICES GRID ════════════════ -->
<section class="section">
    <div class="container">
        <div style="text-align:center;margin-bottom:56px">
            <div class="section-label" style="justify-content:center">Available specialities</div>
            <h2 class="section-title">Specialist care across every department</h2>
            <p class="section-sub" style="margin:0 auto">Choose a service below to find the right specialist and book your consultation.</p>
        </div>

        <?php
        // Use DB services if available, otherwise static
        $displayServices = !empty($services) ? $services : $staticServices;
        $specIconMap = [
            'Cardiology'    => ['fas fa-heartbeat',   '#c0392b','#fdecea'],
            'Neurology'     => ['fas fa-brain',        '#6c3483','#f3e8f9'],
            'Paediatrics'   => ['fas fa-baby',         '#1565c0','#e3f0fc'],
            'Orthopaedics'  => ['fas fa-bone',         '#1a5276','#eaf2fc'],
            'Gynaecology'   => ['fas fa-venus',        '#b03a8a','#fce4f4'],
            'Dermatology'   => ['fas fa-leaf',         '#b7950b','#fef9e7'],
            'Pulmonology'   => ['fas fa-lungs',        '#2980b9','#eaf4fb'],
            'Endocrinology' => ['fas fa-flask',        '#1abc9c','#e8faf5'],
            'ENT'           => ['fas fa-ear-listen',   '#ca6f1e','#fef3e2'],
            'Radiology'     => ['fas fa-x-ray',        '#4a5568','#f0f4f8'],
            'Emergency'     => ['fas fa-truck-medical','#e74c3c','#fdecea'],
            'Psychiatry'    => ['fas fa-hand-holding-heart','#8e44ad','#f5eef8'],
        ];
        ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px">
            <?php foreach ($displayServices as $svc):
                // For DB records
                if (isset($svc['name']) && !isset($svc['icon'])) {
                    $matched = null;
                    foreach ($specIconMap as $k => $v) {
                        if (stripos($svc['name'],$k)!==false || stripos($svc['category']??'',$k)!==false) {
                            $matched = $v; break;
                        }
                    }
                    $ico  = $matched ? $matched[0] : 'fas fa-notes-medical';
                    $col  = $matched ? $matched[1] : 'var(--teal)';
                    $lCol = $matched ? $matched[2] : 'rgba(13,115,119,.08)';
                } else {
                    $ico  = $svc['icon'] ?? 'fas fa-notes-medical';
                    $col  = $svc['colour'] ?? 'var(--teal)';
                    $lCol = $svc['light'] ?? 'rgba(13,115,119,.08)';
                }
                $svcName  = htmlspecialchars($svc['name']);
                $svcDesc  = htmlspecialchars($svc['desc'] ?? $svc['description'] ?? '');
                $svcCat   = htmlspecialchars($svc['category'] ?? '');
                $svcPrice = (float)($svc['price'] ?? $svc['price'] ?? 0);
            ?>
            <div style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);padding:28px;display:flex;flex-direction:column;gap:16px;transition:transform .25s,box-shadow .25s"
                onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--shadow-md)';this.style.borderColor='<?= $col ?>44'"
                onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='var(--border)'">

                <!-- Icon & category -->
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div style="width:52px;height:52px;background:<?= $lCol ?>;border-radius:14px;display:flex;align-items:center;justify-content:center;color:<?= $col ?>;font-size:1.3rem">
                        <i class="<?= $ico ?>"></i>
                    </div>
                    <?php if ($svcCat): ?>
                    <span style="font-size:.72rem;font-weight:700;color:<?= $col ?>;background:<?= $lCol ?>;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.6px"><?= $svcCat ?></span>
                    <?php endif; ?>
                </div>

                <!-- Name & desc -->
                <div>
                    <h3 style="font-size:1.05rem;color:var(--dark);margin-bottom:8px"><?= $svcName ?></h3>
                    <p style="font-size:.87rem;color:var(--muted);line-height:1.65;margin:0"><?= $svcDesc ?></p>
                </div>

                <!-- Price & CTA -->
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:14px;border-top:1px solid var(--border)">
                    <div>
                        <?php if ($svcPrice > 0): ?>
                        <div style="font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;font-weight:600">From</div>
                        <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:var(--teal-dark)">LKR <?= number_format($svcPrice,0) ?></div>
                        <?php else: ?>
                        <div style="font-size:.82rem;font-weight:600;color:#e74c3c"><i class="fas fa-bolt" style="margin-right:4px"></i>24/7 Emergency</div>
                        <?php endif; ?>
                    </div>
                    <a href="doctors.php?spec=<?= urlencode($svc['name']) ?>"
                        style="display:inline-flex;align-items:center;gap:6px;font-size:.83rem;font-weight:700;color:<?= $col ?>;text-decoration:none;padding:7px 14px;border:1.5px solid <?= $col ?>33;border-radius:9px;transition:all .2s"
                        onmouseover="this.style.background='<?= $col ?>11'" onmouseout="this.style.background='transparent'">
                        Find doctors <i class="fas fa-arrow-right" style="font-size:.68rem"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════ EMERGENCY BANNER ═════════════ -->
<section style="background:linear-gradient(135deg,#c0392b,#e74c3c);padding:56px 0">
    <div class="container" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:28px">
        <div>
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                <div style="width:42px;height:42px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem">
                    <i class="fas fa-truck-medical"></i>
                </div>
                <h2 style="font-family:var(--font-display);font-size:1.5rem;color:#fff;margin:0">Medical Emergency?</h2>
            </div>
            <p style="color:rgba(255,255,255,.8);font-size:.95rem;margin:0;max-width:480px">
                Call the national health emergency line or visit the nearest A&E. Do not use this platform for life-threatening emergencies.
            </p>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <a href="tel:1990" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#c0392b;padding:12px 24px;border-radius:var(--radius);font-weight:700;font-size:.95rem;text-decoration:none">
                <i class="fas fa-phone-alt"></i> Call 1990 (Suwa Seriya)
            </a>
            <a href="Emergency Care.php" style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.4);padding:12px 24px;border-radius:var(--radius);font-weight:600;font-size:.95rem;text-decoration:none">
                <i class="fas fa-hospital"></i> Emergency Info
            </a>
        </div>
    </div>
</section>

<!-- ══════════════════════ CTA ═══════════════════════════ -->
<section class="section" style="background:var(--white)">
    <div class="container" style="text-align:center">
        <div class="section-label" style="justify-content:center">Get started today</div>
        <h2 class="section-title">Ready to book your consultation?</h2>
        <p class="section-sub" style="margin:0 auto 32px">Browse our verified specialists and find the right doctor for your needs — free to sign up.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
            <a href="doctors.php" class="btn btn-primary btn-lg"><i class="fas fa-search"></i> Find a Doctor</a>
            <a href="register.php" class="btn btn-outline btn-lg"><i class="fas fa-user-plus"></i> Create Free Account</a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
