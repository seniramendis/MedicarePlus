<?php
$pageTitle = 'Medicare Plus — Sri Lanka\'s Trusted Health Platform';
require_once 'functions.php';
$featuredDoctors = array_slice(fetch_all_doctors(), 0, 3);
include 'header.php';
?>

<!-- ══════════════════════ HERO ══════════════════════════ -->
<section class="hero">
    <div class="hero-deco"></div>
    <div class="hero-deco-2"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-tag">
                <i class="fas fa-circle-check" style="font-size:.7rem"></i>
                Trusted by 50,000+ Sri Lankans
            </div>

            <h1>
                Quality Healthcare,<br>
                <span>Right Here</span> in Sri Lanka
            </h1>

            <p>
                Book specialist consultations at Colombo, Kandy, Galle and beyond.
                Secure medical records, easy payments, and real-time appointment tracking —
                all in one place.
            </p>

            <div class="hero-actions">
                <a href="doctors.php" class="btn btn-accent btn-lg">
                    <i class="fas fa-search"></i> Find a Doctor
                </a>
                <a href="register.php" class="btn btn-lg" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3);backdrop-filter:blur(8px)">
                    <i class="fas fa-user-plus"></i> Create Free Account
                </a>
            </div>

            <div class="hero-stats">
                <div class="hero-stat">
                    <strong>120+</strong>
                    <span>Specialist Doctors</span>
                </div>
                <div class="hero-stat">
                    <strong>22</strong>
                    <span>Hospitals & Clinics</span>
                </div>
                <div class="hero-stat">
                    <strong>4.8★</strong>
                    <span>Average Rating</span>
                </div>
                <div class="hero-stat">
                    <strong>9 Prov.</strong>
                    <span>Island-wide</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════ HOW IT WORKS ═════════════════ -->
<section class="section" style="background:var(--white)">
    <div class="container">
        <div style="text-align:center;margin-bottom:56px">
            <div class="section-label" style="justify-content:center">How it works</div>
            <h2 class="section-title">Book an appointment in 3 steps</h2>
            <p class="section-sub" style="margin:0 auto">No waiting rooms, no confusion — just fast, simple healthcare booking.</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:32px;position:relative">
            <!-- connector line -->
            <div style="position:absolute;top:36px;left:calc(16.67% + 12px);right:calc(16.67% + 12px);height:2px;background:linear-gradient(90deg,var(--teal),var(--teal-light));z-index:0"></div>

            <?php
            $steps = [
                ['icon'=>'fas fa-user-circle',    'num'=>'01', 'title'=>'Create your profile',    'desc'=>'Sign up for free and complete your patient profile in under 2 minutes.'],
                ['icon'=>'fas fa-stethoscope',     'num'=>'02', 'title'=>'Choose a specialist',    'desc'=>'Browse doctors by speciality, location, experience and patient rating.'],
                ['icon'=>'fas fa-calendar-check',  'num'=>'03', 'title'=>'Confirm & attend',       'desc'=>'Pick your slot, pay the consultation fee online, and attend your appointment.'],
            ];
            foreach ($steps as $step): ?>
            <div style="text-align:center;position:relative;z-index:1">
                <div style="width:72px;height:72px;background:linear-gradient(135deg,var(--teal),var(--teal-light));border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(13,115,119,.3)">
                    <i class="<?= $step['icon'] ?>" style="font-size:1.5rem;color:#fff"></i>
                </div>
                <div style="font-family:var(--font-display);font-size:2.5rem;font-weight:700;color:rgba(13,115,119,.08);line-height:1;margin-bottom:4px"><?= $step['num'] ?></div>
                <h3 style="font-size:1.05rem;color:var(--dark);margin-bottom:10px"><?= $step['title'] ?></h3>
                <p style="font-size:.88rem;max-width:220px;margin:0 auto"><?= $step['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════ SERVICES ═════════════════════ -->
<section class="section">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:48px;flex-wrap:wrap;gap:20px">
            <div>
                <div class="section-label">What we offer</div>
                <h2 class="section-title" style="margin-bottom:0">Comprehensive health services</h2>
            </div>
            <a href="services.php" class="btn btn-ghost">View all services <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="grid-3">
            <?php
            $services = [
                ['icon'=>'fas fa-heartbeat',       'title'=>'Cardiology',        'desc'=>'Expert heart care from nationally recognised cardiologists in Colombo and Kandy.'],
                ['icon'=>'fas fa-brain',            'title'=>'Neurology',         'desc'=>'Advanced neurological assessments and treatment at teaching hospitals island-wide.'],
                ['icon'=>'fas fa-baby',             'title'=>'Paediatrics',       'desc'=>'Compassionate child health services from Lady Ridgeway and other leading hospitals.'],
                ['icon'=>'fas fa-bone',             'title'=>'Orthopaedics',      'desc'=>'Joint replacement, sports injuries and fracture management by experienced surgeons.'],
                ['icon'=>'fas fa-venus',            'title'=>'Gynaecology',       'desc'=>'Women\'s health specialists serving patients across the Western and Southern provinces.'],
                ['icon'=>'fas fa-lungs',            'title'=>'Pulmonology',       'desc'=>'Respiratory care and lung health at accredited Sri Lankan hospitals and clinics.'],
            ];
            foreach ($services as $svc): ?>
            <div class="service-card">
                <div class="service-icon"><i class="<?= $svc['icon'] ?>"></i></div>
                <h3><?= $svc['title'] ?></h3>
                <p><?= $svc['desc'] ?></p>
                <a href="doctors.php?spec=<?= urlencode($svc['title']) ?>" style="display:inline-flex;align-items:center;gap:6px;margin-top:14px;font-size:.85rem;font-weight:600;color:var(--teal)">
                    Browse doctors <i class="fas fa-chevron-right" style="font-size:.65rem"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════ FEATURED DOCTORS ════════════ -->
<?php if (!empty($featuredDoctors)): ?>
<section class="section" style="background:var(--white)">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:48px;flex-wrap:wrap;gap:20px">
            <div>
                <div class="section-label">Our team</div>
                <h2 class="section-title" style="margin-bottom:0">Highest-rated specialists</h2>
            </div>
            <a href="doctors.php" class="btn btn-outline">All doctors <i class="fas fa-arrow-right"></i></a>
        </div>

        <div class="grid-3">
            <?php foreach ($featuredDoctors as $doc):
                $initials = strtoupper(substr($doc['first_name'],0,1) . substr($doc['last_name'],0,1));
            ?>
            <div class="doctor-card">
                <div class="doctor-card-top">
                    <div class="doctor-avatar"><?= $initials ?></div>
                    <h3>Dr. <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></h3>
                    <div class="doctor-specialty"><?= htmlspecialchars($doc['specialization']) ?></div>
                </div>
                <div class="doctor-card-body">
                    <div class="doctor-meta">
                        <div class="doctor-meta-item">
                            <i class="fas fa-hospital-alt"></i>
                            <?= htmlspecialchars($doc['hospital'] ?? 'N/A') ?>
                        </div>
                        <div class="doctor-meta-item">
                            <i class="fas fa-map-pin"></i>
                            <?= htmlspecialchars($doc['location'] ?? $doc['city'] ?? 'Sri Lanka') ?>
                        </div>
                        <div class="doctor-meta-item">
                            <i class="fas fa-award"></i>
                            <?= (int)$doc['experience_years'] ?> years experience
                        </div>
                        <div class="doctor-meta-item doctor-rating">
                            <i class="fas fa-star" style="color:var(--accent)"></i>
                            <?= number_format($doc['rating'],1) ?> rating
                        </div>
                    </div>
                    <div class="doctor-fee">
                        LKR <?= number_format($doc['consultation_fee'],0) ?>
                        <small> / consultation</small>
                    </div>
                    <a href="book_appointment.php?doctor_id=<?= $doc['id'] ?>" class="btn btn-primary btn-sm btn-block">
                        <i class="fas fa-calendar-plus"></i> Book Appointment
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════ LOCATIONS ════════════════════ -->
<section class="section">
    <div class="container">
        <div style="text-align:center;margin-bottom:48px">
            <div class="section-label" style="justify-content:center">Coverage</div>
            <h2 class="section-title">Available across Sri Lanka</h2>
            <p class="section-sub" style="margin:0 auto">From Jaffna to Galle — we connect you with doctors in your region.</p>
        </div>

        <div class="grid-4">
            <?php
            $locations = [
                ['city'=>'Colombo',    'count'=>'48 doctors', 'icon'=>'fas fa-city'],
                ['city'=>'Kandy',      'count'=>'24 doctors', 'icon'=>'fas fa-mountain'],
                ['city'=>'Galle',      'count'=>'18 doctors', 'icon'=>'fas fa-anchor'],
                ['city'=>'Negombo',    'count'=>'12 doctors', 'icon'=>'fas fa-water'],
                ['city'=>'Matara',     'count'=>'10 doctors', 'icon'=>'fas fa-umbrella-beach'],
                ['city'=>'Kurunegala', 'count'=>'9 doctors',  'icon'=>'fas fa-tree'],
                ['city'=>'Jaffna',     'count'=>'8 doctors',  'icon'=>'fas fa-sun'],
                ['city'=>'Trincomalee','count'=>'6 doctors',  'icon'=>'fas fa-fish'],
            ];
            foreach ($locations as $loc): ?>
            <a href="doctors.php?city=<?= urlencode($loc['city']) ?>" style="background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:22px 18px;text-align:center;transition:all .25s;text-decoration:none;display:block" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow-md)';this.style.borderColor='var(--teal-light)'" onmouseout="this.style.transform='';this.style.boxShadow='';this.style.borderColor='var(--border)'">
                <div style="width:44px;height:44px;background:rgba(13,115,119,.08);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:var(--teal)">
                    <i class="<?= $loc['icon'] ?>"></i>
                </div>
                <div style="font-family:var(--font-display);font-size:1rem;color:var(--dark);margin-bottom:4px;font-weight:600"><?= $loc['city'] ?></div>
                <div style="font-size:.78rem;color:var(--muted)"><?= $loc['count'] ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════ TESTIMONIALS ════════════════ -->
<section class="testimonial-section">
    <div class="container">
        <div style="text-align:center;margin-bottom:48px">
            <div class="section-label" style="justify-content:center;color:rgba(255,255,255,.7)">
                <span style="background:rgba(255,255,255,.3);width:24px;height:2px;display:inline-block;border-radius:2px"></span>
                Patient stories
            </div>
            <h2 class="section-title" style="color:#fff;margin-bottom:10px">What our patients say</h2>
            <p style="color:rgba(255,255,255,.65)">Thousands of families across Sri Lanka trust Medicare Plus for their healthcare needs.</p>
        </div>

        <div class="grid-3">
            <?php
            $testimonials = [
                ['text'=>'I booked with Dr. Perera at National Hospital in minutes. The whole experience — from choosing a slot to receiving my report — was seamless. Highly recommend Medicare Plus.', 'name'=>'Kavindu Silva', 'loc'=>'Colombo'],
                ['text'=>'As someone from Matara, accessing a good gynaecologist used to mean a trip to Colombo. Medicare Plus helped me find Dr. Rathnayake locally. What a relief!', 'name'=>'Amaya Disanayake', 'loc'=>'Matara'],
                ['text'=>'My son needed a paediatrician urgently. Medicare Plus showed me availability in real time and I got an appointment the same afternoon. Fantastic service!', 'name'=>'Tharanga Perera', 'loc'=>'Kandy'],
            ];
            foreach ($testimonials as $t):
                $initials = implode('', array_map(fn($p)=>strtoupper($p[0]), explode(' ', $t['name'])));
            ?>
            <div class="testimonial-card">
                <div style="color:var(--accent);font-size:1.2rem;margin-bottom:12px">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
                <p>"<?= $t['text'] ?>"</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar"><?= $initials ?></div>
                    <div>
                        <strong><?= $t['name'] ?></strong>
                        <span><i class="fas fa-map-pin" style="font-size:.65rem;margin-right:4px"></i><?= $t['loc'] ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════ CTA ═══════════════════════════ -->
<section class="section" style="background:var(--white)">
    <div class="container">
        <div style="background:linear-gradient(135deg,var(--teal-dark),var(--teal));border-radius:24px;padding:60px 48px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:32px;position:relative;overflow:hidden">
            <div style="position:absolute;right:-40px;top:-40px;width:200px;height:200px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none"></div>
            <div style="position:relative">
                <h2 style="font-size:clamp(1.6rem,3vw,2rem);color:#fff;margin-bottom:12px">Ready to take control of your health?</h2>
                <p style="color:rgba(255,255,255,.7);font-size:.95rem;max-width:480px">Join over 50,000 Sri Lankans who manage their healthcare digitally with Medicare Plus. Free to register.</p>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;position:relative">
                <a href="register.php" class="btn btn-accent btn-lg">
                    <i class="fas fa-user-plus"></i> Get started free
                </a>
                <a href="doctors.php" class="btn btn-lg" style="background:rgba(255,255,255,.15);color:#fff;border:1.5px solid rgba(255,255,255,.3)">
                    <i class="fas fa-search"></i> Browse doctors
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
