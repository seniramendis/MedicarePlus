<?php
$pageTitle = 'Our Services — Medicare Plus';
require_once 'functions.php';
include 'header.php';

$services = [];
$conn = get_db_connection();
if ($conn) {
    try {
        $r = $conn->query("SELECT * FROM services ORDER BY name");
        if ($r) $services = $r->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {}
}

// Enriched static services — each with an Unsplash image
$staticServices = [
    [
        'category' => 'Heart & Vascular',
        'icon'     => 'fas fa-heartbeat',
        'colour'   => '#c0392b',
        'light'    => '#fdecea',
        'name'     => 'Cardiology',
        'img'      => 'https://images.unsplash.com/photo-1628348068343-c6a848d2b6dd?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Nationally recognised cardiologists at Colombo National Hospital and Lanka Hospital offering ECG, Echo, angioplasty consultations and heart failure management.',
        'price'    => 3500,
    ],
    [
        'category' => 'Brain & Nerves',
        'icon'     => 'fas fa-brain',
        'colour'   => '#6c3483',
        'light'    => '#f3e8f9',
        'name'     => 'Neurology',
        'img'      => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Advanced neurological assessments, epilepsy management, stroke rehabilitation and headache clinics at Teaching Hospitals in Colombo, Kandy and Galle.',
        'price'    => 4200,
    ],
    [
        'category' => "Children's Health",
        'icon'     => 'fas fa-baby',
        'colour'   => '#1565c0',
        'light'    => '#e3f0fc',
        'name'     => 'Paediatrics',
        'img'      => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Compassionate child health services from Lady Ridgeway Hospital specialists. Growth monitoring, vaccinations, neonatal care and developmental assessments.',
        'price'    => 2800,
    ],
    [
        'category' => 'Bone & Joint',
        'icon'     => 'fas fa-bone',
        'colour'   => '#1a5276',
        'light'    => '#eaf2fc',
        'name'     => 'Orthopaedics',
        'img'      => 'https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Joint replacement surgery, sports injury management, spinal care and fracture treatment by experienced surgeons at Colombo and Peradeniya hospitals.',
        'price'    => 3800,
    ],
    [
        'category' => "Women's Health",
        'icon'     => 'fas fa-venus',
        'colour'   => '#b03a8a',
        'light'    => '#fce4f4',
        'name'     => 'Gynaecology',
        'img'      => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Comprehensive women\'s health services including maternity care, gynaecological oncology, family planning and minimally-invasive surgery at Castle Street Hospital.',
        'price'    => 3000,
    ],
    [
        'category' => 'Skin Care',
        'icon'     => 'fas fa-leaf',
        'colour'   => '#b7950b',
        'light'    => '#fef9e7',
        'name'     => 'Dermatology',
        'img'      => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Diagnosis and treatment of skin, hair and nail conditions. Acne, eczema, psoriasis, skin cancer screening and cosmetic dermatology at Colombo clinics.',
        'price'    => 2500,
    ],
    [
        'category' => 'Respiratory',
        'icon'     => 'fas fa-lungs',
        'colour'   => '#2980b9',
        'light'    => '#eaf4fb',
        'name'     => 'Pulmonology',
        'img'      => 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Expert respiratory care for asthma, COPD, lung infections and sleep apnoea. Spirometry and bronchoscopy available at Karapitiya and Colombo South hospitals.',
        'price'    => 3200,
    ],
    [
        'category' => 'Hormones & Metabolism',
        'icon'     => 'fas fa-flask',
        'colour'   => '#1abc9c',
        'light'    => '#e8faf5',
        'name'     => 'Endocrinology',
        'img'      => 'https://images.unsplash.com/photo-1581595219315-a187dd40c322?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Specialist management of diabetes, thyroid disorders, obesity, PCOS and adrenal conditions at National Hospital and Nawaloka Colombo.',
        'price'    => 2900,
    ],
    [
        'category' => 'Ear Nose Throat',
        'icon'     => 'fas fa-ear-listen',
        'colour'   => '#ca6f1e',
        'light'    => '#fef3e2',
        'name'     => 'ENT',
        'img'      => 'https://images.unsplash.com/photo-1666214280557-f1b5022eb634?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Hearing loss assessment, sinus surgery, tonsillitis, voice disorders and head & neck tumour management at accredited Sri Lankan ENT centres.',
        'price'    => 2200,
    ],
    [
        'category' => 'Diagnostics',
        'icon'     => 'fas fa-x-ray',
        'colour'   => '#4a5568',
        'light'    => '#f0f4f8',
        'name'     => 'Radiology & Imaging',
        'img'      => 'https://images.unsplash.com/photo-1516069677018-378515003435?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Full-body MRI, CT, X-ray, ultrasound and mammography services with same-day report turnaround. Available at 8 locations island-wide.',
        'price'    => 1500,
    ],
    [
        'category' => 'Emergency',
        'icon'     => 'fas fa-truck-medical',
        'colour'   => '#e74c3c',
        'light'    => '#fdecea',
        'name'     => 'Emergency Care',
        'img'      => 'https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=700&q=75&auto=format&fit=crop',
        'desc'     => '24/7 emergency consultations available through Medicare Plus partner hospitals in Colombo, Kandy and Galle. Triage and stabilisation by experienced ER specialists.',
        'price'    => 0,
    ],
    [
        'category' => 'Mental Health',
        'icon'     => 'fas fa-hand-holding-heart',
        'colour'   => '#8e44ad',
        'light'    => '#f5eef8',
        'name'     => 'Psychiatry',
        'img'      => 'https://images.unsplash.com/photo-1544027993-37dbfe43562a?w=700&q=75&auto=format&fit=crop',
        'desc'     => 'Confidential mental health support including therapy, mood disorder management, anxiety treatment and addiction counselling at NIMH-affiliated clinics.',
        'price'    => 2500,
    ],
];
?>

<!-- ══════════════════════ HERO with image ═══════════════ -->
<div class="svc-page-hero">
    <div class="svc-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1504813184591-01572f98c85f?w=1400&q=80&auto=format&fit=crop')"></div>
    <div class="svc-hero-overlay"></div>
    <div class="container svc-hero-body">
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:50px;padding:5px 18px;font-size:.78rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.9);margin-bottom:18px">
            <i class="fas fa-hospital"></i> What We Offer
        </div>
        <h1 style="font-family:var(--font-display);font-size:clamp(2rem,4vw,3rem);color:#fff;margin:0 0 16px;line-height:1.15">
            Comprehensive Health<br>Services Across Sri Lanka
        </h1>
        <p style="color:rgba(255,255,255,.78);font-size:1.05rem;max-width:560px;margin:0 0 32px;line-height:1.7">
            From routine consultations to specialist care — Medicare Plus connects you with the right doctors across Sri Lanka's top hospitals.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:12px">
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
</div>

<!-- ══════════════════════ SERVICES GRID ════════════════ -->
<section class="section">
    <div class="container">
        <div style="text-align:center;margin-bottom:56px">
            <div class="section-label" style="justify-content:center">Available specialities</div>
            <h2 class="section-title">Specialist care across every department</h2>
            <p class="section-sub" style="margin:0 auto">Choose a service below to find the right specialist and book your consultation.</p>
        </div>

        <?php
        $displayServices = !empty($services) ? $services : $staticServices;
        $specIconMap = [
            'Cardiology'    => ['fas fa-heartbeat',        '#c0392b','#fdecea','https://images.unsplash.com/photo-1628348068343-c6a848d2b6dd?w=700&q=75&auto=format&fit=crop'],
            'Neurology'     => ['fas fa-brain',             '#6c3483','#f3e8f9','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=700&q=75&auto=format&fit=crop'],
            'Paediatrics'   => ['fas fa-baby',              '#1565c0','#e3f0fc','https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=700&q=75&auto=format&fit=crop'],
            'Orthopaedics'  => ['fas fa-bone',              '#1a5276','#eaf2fc','https://images.unsplash.com/photo-1538108149393-fbbd81895907?w=700&q=75&auto=format&fit=crop'],
            'Gynaecology'   => ['fas fa-venus',             '#b03a8a','#fce4f4','https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=700&q=75&auto=format&fit=crop'],
            'Dermatology'   => ['fas fa-leaf',              '#b7950b','#fef9e7','https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?w=700&q=75&auto=format&fit=crop'],
            'Pulmonology'   => ['fas fa-lungs',             '#2980b9','#eaf4fb','https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=700&q=75&auto=format&fit=crop'],
            'Endocrinology' => ['fas fa-flask',             '#1abc9c','#e8faf5','https://images.unsplash.com/photo-1581595219315-a187dd40c322?w=700&q=75&auto=format&fit=crop'],
            'ENT'           => ['fas fa-ear-listen',        '#ca6f1e','#fef3e2','https://images.unsplash.com/photo-1666214280557-f1b5022eb634?w=700&q=75&auto=format&fit=crop'],
            'Radiology'     => ['fas fa-x-ray',             '#4a5568','#f0f4f8','https://images.unsplash.com/photo-1516069677018-378515003435?w=700&q=75&auto=format&fit=crop'],
            'Emergency'     => ['fas fa-truck-medical',     '#e74c3c','#fdecea','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=700&q=75&auto=format&fit=crop'],
            'Psychiatry'    => ['fas fa-hand-holding-heart','#8e44ad','#f5eef8','https://images.unsplash.com/photo-1544027993-37dbfe43562a?w=700&q=75&auto=format&fit=crop'],
        ];
        ?>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:28px">
            <?php foreach ($displayServices as $svc):
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
                    $img  = $matched ? $matched[3] : 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=700&q=75&auto=format&fit=crop';
                } else {
                    $ico  = $svc['icon']   ?? 'fas fa-notes-medical';
                    $col  = $svc['colour'] ?? 'var(--teal)';
                    $lCol = $svc['light']  ?? 'rgba(13,115,119,.08)';
                    $img  = $svc['img']    ?? 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=700&q=75&auto=format&fit=crop';
                }
                $svcName  = htmlspecialchars($svc['name']);
                $svcDesc  = htmlspecialchars($svc['desc'] ?? $svc['description'] ?? '');
                $svcCat   = htmlspecialchars($svc['category'] ?? '');
                $svcPrice = (float)($svc['price'] ?? 0);
            ?>
            <div class="svc-full-card">
                <!-- Photo header -->
                <div class="svc-full-img">
                    <img src="<?= $img ?>" alt="<?= $svcName ?>" loading="lazy">
                    <div class="svc-full-img-overlay" style="--c:<?= $col ?>"></div>
                    <!-- Category pill on image -->
                    <?php if ($svcCat): ?>
                    <span class="svc-cat-pill" style="background:<?= $col ?>"><?= $svcCat ?></span>
                    <?php endif; ?>
                    <!-- Icon circle -->
                    <div class="svc-full-icon" style="background:<?= $lCol ?>;color:<?= $col ?>">
                        <i class="<?= $ico ?>"></i>
                    </div>
                </div>

                <!-- Body -->
                <div class="svc-full-body">
                    <h3><?= $svcName ?></h3>
                    <p><?= $svcDesc ?></p>

                    <div class="svc-full-footer">
                        <div>
                            <?php if ($svcPrice > 0): ?>
                            <div style="font-size:.7rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;font-weight:600">From</div>
                            <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:var(--teal-dark)">LKR <?= number_format($svcPrice,0) ?></div>
                            <?php else: ?>
                            <div style="font-size:.82rem;font-weight:600;color:#e74c3c"><i class="fas fa-bolt" style="margin-right:4px"></i>24/7 Emergency</div>
                            <?php endif; ?>
                        </div>
                        <a href="doctors.php?spec=<?= urlencode($svc['name']) ?>"
                            class="svc-btn-find" style="--c:<?= $col ?>">
                            Find doctors <i class="fas fa-arrow-right" style="font-size:.68rem"></i>
                        </a>
                    </div>
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
                Call the national health emergency line or visit the nearest A&amp;E. Do not use this platform for life-threatening emergencies.
            </p>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <a href="tel:1990" style="display:inline-flex;align-items:center;gap:8px;background:#fff;color:#c0392b;padding:12px 24px;border-radius:var(--radius);font-weight:700;font-size:.95rem;text-decoration:none">
                <i class="fas fa-phone-alt"></i> Call 1990 (Suwa Seriya)
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
