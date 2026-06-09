<?php
// Home.php — Medicare Plus
// Enhanced: hero background image, scroll animations, section transitions
session_start();
include 'header.php';
?>

<!-- ═══════════════════════════════════════════════════════
     HERO SECTION — full-screen with parallax background
═══════════════════════════════════════════════════════════ -->
<section id="hero" class="mp-hero">

    <!-- Layered background: image + gradient overlay -->
    <div class="mp-hero__bg">
        <div class="mp-hero__bg-img" id="heroParallax"
            style="background-image: url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?w=1600&q=80&auto=format&fit=crop');">
        </div>
        <div class="mp-hero__overlay"></div>
    </div>

    <!-- Animated floating badge cards (paediatrics, cardiology etc.) -->
    <div class="mp-hero__floaters" aria-hidden="true">
        <div class="mp-floater mp-floater--1">
            <i class="fas fa-heartbeat"></i>
            <span>Cardiology</span>
        </div>
        <div class="mp-floater mp-floater--2">
            <i class="fas fa-brain"></i>
            <span>Neurology</span>
        </div>
        <div class="mp-floater mp-floater--3">
            <i class="fas fa-baby"></i>
            <span>Paediatrics</span>
        </div>
        <div class="mp-floater mp-floater--4">
            <i class="fas fa-bone"></i>
            <span>Orthopaedics</span>
        </div>
    </div>

    <div class="container mp-hero__content">
        <div class="mp-hero__badge mp-reveal" data-delay="0">
            <i class="fas fa-location-dot"></i>
            SRI LANKA'S TRUSTED HEALTH NETWORK
        </div>

        <h1 class="mp-hero__heading mp-reveal" data-delay="100">
            Expert Care,<br>
            <span class="mp-hero__heading-accent">When You Need</span><br>
            It Most
        </h1>

        <p class="mp-hero__sub mp-reveal" data-delay="200">
            Board-certified specialists across 12 disciplines. Same-day appointments,
            digital reports, and a dedicated care team — all in one place.
        </p>

        <div class="mp-hero__actions mp-reveal" data-delay="300">
            <a href="book_appointment.php" class="btn btn-accent btn-lg">
                <i class="fas fa-calendar-check"></i> Book an Appointment
            </a>
            <a href="doctors.php" class="btn btn-ghost-white btn-lg">
                <i class="fas fa-user-doctor"></i> Find a Doctor
            </a>
        </div>

        <!-- Quick stats strip -->
        <div class="mp-hero__stats mp-reveal" data-delay="400">
            <div class="mp-stat">
                <span class="mp-stat__num" data-count="120">0</span><span class="mp-stat__unit">+</span>
                <span class="mp-stat__label">Specialists</span>
            </div>
            <div class="mp-stat__divider" aria-hidden="true"></div>
            <div class="mp-stat">
                <span class="mp-stat__num" data-count="50000">0</span><span class="mp-stat__unit">+</span>
                <span class="mp-stat__label">Patients Served</span>
            </div>
            <div class="mp-stat__divider" aria-hidden="true"></div>
            <div class="mp-stat">
                <span class="mp-stat__num" data-count="12">0</span><span class="mp-stat__unit"></span>
                <span class="mp-stat__label">Specialties</span>
            </div>
            <div class="mp-stat__divider" aria-hidden="true"></div>
            <div class="mp-stat">
                <span class="mp-stat__num" data-count="98">0</span><span class="mp-stat__unit">%</span>
                <span class="mp-stat__label">Satisfaction</span>
            </div>
        </div>
    </div>

    <!-- Scroll cue -->
    <a href="#specialties" class="mp-hero__scroll-cue" aria-label="Scroll down">
        <i class="fas fa-chevron-down"></i>
    </a>
</section>

<!-- ═══════════════════════════════════════════════════════
     EMERGENCY BANNER (moved below hero for better rhythm)
═══════════════════════════════════════════════════════════ -->
<div class="mp-emergency-bar">
    <div class="container mp-emergency-bar__inner">
        <span class="mp-emergency-bar__item">
            <i class="fas fa-phone-volume"></i>
            <strong>24/7 Emergency Hotline:</strong>
            <a href="tel:+94112140000">+94 11 214 0000</a>
        </span>
        <span class="mp-emergency-bar__sep" aria-hidden="true">|</span>
        <span class="mp-emergency-bar__item">
            <i class="fas fa-truck-medical"></i>
            <strong>Ambulance:</strong>
            <a href="tel:1990">1990</a>
        </span>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SPECIALTIES SECTION
═══════════════════════════════════════════════════════════ -->
<section id="specialties" class="mp-section mp-section--light">
    <div class="container">
        <div class="mp-section-head mp-reveal" data-delay="0">
            <span class="mp-eyebrow">What We Treat</span>
            <h2 class="mp-section-head__title">Our Specialties</h2>
            <p class="mp-section-head__sub">World-class expertise across the most critical medical disciplines.</p>
        </div>

        <div class="mp-specialty-grid">
            <?php
            $specialties = [
                ['icon' => 'fa-heart-pulse',   'name' => 'Cardiology',          'desc' => 'Heart & vascular care',         'color' => '#c0392b', 'bg' => '#fdecea'],
                ['icon' => 'fa-brain',          'name' => 'Neurology',           'desc' => 'Brain & nervous system',        'color' => '#6c3483', 'bg' => '#f3e8f9'],
                ['icon' => 'fa-baby',           'name' => 'Paediatrics',         'desc' => 'Child health & development',    'color' => '#1565c0', 'bg' => '#e3f0fc'],
                ['icon' => 'fa-lungs',          'name' => 'Pulmonology',         'desc' => 'Lung & respiratory health',     'color' => '#2980b9', 'bg' => '#eaf4fb'],
                ['icon' => 'fa-bone',           'name' => 'Orthopaedics',        'desc' => 'Bones, joints & muscles',      'color' => '#1a5276', 'bg' => '#eaf2fc'],
                ['icon' => 'fa-venus',          'name' => 'Gynaecology',         'desc' => "Women's health & obstetrics",  'color' => '#b03a8a', 'bg' => '#fce4f4'],
                ['icon' => 'fa-ear-listen',     'name' => 'ENT',                 'desc' => 'Ear, nose & throat',           'color' => '#ca6f1e', 'bg' => '#fef3e2'],
                ['icon' => 'fa-microscope',     'name' => 'Dermatology',         'desc' => 'Skin, hair & nails',           'color' => '#b7950b', 'bg' => '#fef9e7'],
                ['icon' => 'fa-dna',            'name' => 'Endocrinology',       'desc' => 'Hormones & metabolism',        'color' => '#1abc9c', 'bg' => '#e8faf5'],
                ['icon' => 'fa-user-doctor',    'name' => 'General Practice',    'desc' => 'Primary & preventive care',    'color' => '#1e8449', 'bg' => '#e9f7ef'],
                ['icon' => 'fa-eye',            'name' => 'Ophthalmology',       'desc' => 'Eyes & vision',                'color' => '#0d7373', 'bg' => '#e0f5f5'],
                ['icon' => 'fa-syringe',        'name' => 'Oncology',            'desc' => 'Cancer diagnosis & treatment', 'color' => '#7d3c98', 'bg' => '#f4ecfa'],
            ];
            foreach ($specialties as $i => $s):
            ?>
                <a href="doctors.php?spec=<?= urlencode($s['name']) ?>" class="mp-specialty-card mp-reveal" data-delay="<?= ($i % 4) * 60 ?>"
                    style="--sp-color:<?= $s['color'] ?>;--sp-bg:<?= $s['bg'] ?>">
                    <div class="mp-specialty-card__icon">
                        <i class="fas <?= $s['icon'] ?>"></i>
                    </div>
                    <div class="mp-specialty-card__body">
                        <h3><?= $s['name'] ?></h3>
                        <p><?= $s['desc'] ?></p>
                    </div>
                    <i class="fas fa-arrow-right mp-specialty-card__arrow"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     WHY CHOOSE US — split layout with image
═══════════════════════════════════════════════════════════ -->
<section class="mp-section mp-section--teal">
    <div class="container mp-split">
        <div class="mp-split__img mp-reveal" data-delay="0">
            <img src="https://images.unsplash.com/photo-1579684385127-1ef15d508118?w=700&q=80&auto=format&fit=crop"
                alt="Doctor with patient" loading="lazy">
            <div class="mp-split__img-badge">
                <i class="fas fa-shield-heart"></i>
                <div>
                    <strong>NABH Accredited</strong>
                    <span>Quality healthcare standards</span>
                </div>
            </div>
        </div>
        <div class="mp-split__copy">
            <span class="mp-eyebrow mp-eyebrow--light mp-reveal" data-delay="0">Why Medicare Plus</span>
            <h2 class="mp-split__title mp-reveal" data-delay="80">Care you can count on,<br>every single time</h2>
            <div class="mp-why-list">
                <?php
                $whys = [
                    ['fas fa-clock',           'Same-Day Appointments',        'Book in minutes and see a specialist the same day — no long waitlists.'],
                    ['fas fa-file-medical-alt', 'Digital Medical Records',       'Your full health history, lab results, and prescriptions — secure and always accessible.'],
                    ['fas fa-comments',        'Teleconsultation',              'See a doctor from home via video call whenever coming in isn\'t possible.'],
                    ['fas fa-award',           'Board-Certified Specialists',   'Every doctor on our platform is credentialed, reviewed, and continuously evaluated.'],
                ];
                foreach ($whys as $idx => $w):
                ?>
                    <div class="mp-why-item mp-reveal" data-delay="<?= 100 + $idx * 80 ?>">
                        <div class="mp-why-item__icon"><i class="fas <?= $w[0] ?>"></i></div>
                        <div>
                            <h4><?= $w[1] ?></h4>
                            <p><?= $w[2] ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <a href="services.php" class="btn btn-accent mp-reveal" data-delay="500" style="margin-top:8px">
                <i class="fas fa-stethoscope"></i> Explore Our Services
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FEATURED DOCTORS
═══════════════════════════════════════════════════════════ -->
<section class="mp-section mp-section--light">
    <div class="container">
        <div class="mp-section-head mp-reveal" data-delay="0">
            <span class="mp-eyebrow">Our Team</span>
            <h2 class="mp-section-head__title">Featured Specialists</h2>
            <p class="mp-section-head__sub">Meet some of our top-rated doctors — trusted by thousands of patients.</p>
        </div>

        <div class="mp-doctor-cards" id="homeDoctorGrid">
            <?php
            // Fetch top 4 doctors from DB
            require_once 'db_connect.php';
            $specColours = [
                'Cardiology'           => ['#c0392b', '#fdecea'],
                'Neurology'            => ['#6c3483', '#f3e8f9'],
                'Paediatrics'          => ['#1565c0', '#e3f0fc'],
                'Dermatology'          => ['#b7950b', '#fef9e7'],
                'Orthopaedics'         => ['#1a5276', '#eaf2fc'],
                'General Practitioner' => ['#1e8449', '#e9f7ef'],
                'Gynaecology'          => ['#b03a8a', '#fce4f4'],
                'ENT'                  => ['#ca6f1e', '#fef3e2'],
                'Endocrinology'        => ['#1abc9c', '#e8faf5'],
                'Pulmonology'          => ['#2980b9', '#eaf4fb'],
            ];
            $sql = "SELECT d.id, u.first_name, u.last_name, d.specialization,
                           d.consultation_fee, d.experience_years, d.rating, d.hospital
                    FROM doctors d
                    JOIN users u ON u.id = d.user_id
                    ORDER BY d.rating DESC
                    LIMIT 4";
            $result = $conn->query($sql);
            $idx = 0;
            if ($result && $result->num_rows > 0):
                while ($doc = $result->fetch_assoc()):
                    $spec = htmlspecialchars($doc['specialization']);
                    $name = htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']);
                    $initials = strtoupper(substr($doc['first_name'], 0, 1) . substr($doc['last_name'], 0, 1));
                    $colours = $specColours[$doc['specialization']] ?? ['#0d7373', '#e0f5f5'];
            ?>
                    <div class="mp-doc-card mp-reveal" data-delay="<?= $idx * 80 ?>">
                        <div class="mp-doc-card__avatar" style="background:<?= $colours[1] ?>;color:<?= $colours[0] ?>">
                            <?= $initials ?>
                        </div>
                        <div class="mp-doc-card__spec" style="color:<?= $colours[0] ?>;background:<?= $colours[1] ?>">
                            <?= $spec ?>
                        </div>
                        <h3 class="mp-doc-card__name">Dr. <?= $name ?></h3>
                        <?php if (!empty($doc['hospital'])): ?>
                            <p class="mp-doc-card__hosp"><i class="fas fa-hospital-alt"></i> <?= htmlspecialchars($doc['hospital']) ?></p>
                        <?php endif; ?>
                        <div class="mp-doc-card__meta">
                            <?php if ($doc['experience_years'] > 0): ?>
                                <span><i class="fas fa-award"></i> <?= (int)$doc['experience_years'] ?> yrs</span>
                            <?php endif; ?>
                            <?php if ($doc['rating'] > 0): ?>
                                <span><i class="fas fa-star"></i> <?= number_format($doc['rating'], 1) ?></span>
                            <?php endif; ?>
                            <span><i class="fas fa-tag"></i> LKR <?= number_format($doc['consultation_fee'], 0) ?></span>
                        </div>
                        <div class="mp-doc-card__actions">
                            <a href="doctor_profile.php?id=<?= (int)$doc['id'] ?>" class="btn btn-outline-teal btn-sm">View Profile</a>
                            <a href="book_appointment.php?doctor_id=<?= (int)$doc['id'] ?>" class="btn btn-primary btn-sm">Book Now</a>
                        </div>
                    </div>
                <?php $idx++;
                endwhile;
            else: ?>
                <!-- Skeleton placeholders if DB is empty -->
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="mp-doc-card mp-doc-card--skeleton"></div>
            <?php endfor;
            endif; ?>
        </div>

        <div class="mp-section-cta mp-reveal" data-delay="200">
            <a href="doctors.php" class="btn btn-primary btn-lg">
                <i class="fas fa-user-doctor"></i> View All Doctors
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     BLOG PREVIEW — with Unsplash images
═══════════════════════════════════════════════════════════ -->
<section class="mp-section mp-section--white">
    <div class="container">
        <div class="mp-section-head mp-reveal" data-delay="0">
            <span class="mp-eyebrow">Health Insights</span>
            <h2 class="mp-section-head__title">From Our Blog</h2>
            <p class="mp-section-head__sub">Evidence-based articles written by our specialists, for you.</p>
        </div>

        <div class="mp-blog-preview-grid" id="homeBlogGrid">
            <?php
            // Fetch latest 3 blog posts
            $blogImages = [
                'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=600&q=75&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=600&q=75&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=600&q=75&auto=format&fit=crop',
            ];
            $blogSql = "SELECT b.id, b.title, b.excerpt, b.created_at, b.category,
                               u.first_name, u.last_name
                        FROM blog_posts b
                        LEFT JOIN users u ON u.id = b.author_id
                        WHERE b.published = 1
                        ORDER BY b.created_at DESC
                        LIMIT 3";
            $bResult = $conn->query($blogSql);
            $bi = 0;
            if ($bResult && $bResult->num_rows > 0):
                while ($post = $bResult->fetch_assoc()):
                    $img = $blogImages[$bi % count($blogImages)];
            ?>
                    <article class="mp-blog-card mp-reveal" data-delay="<?= $bi * 80 ?>">
                        <a href="blog_post.php?id=<?= (int)$post['id'] ?>" class="mp-blog-card__img-wrap">
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                            <?php if (!empty($post['category'])): ?>
                                <span class="mp-blog-card__cat"><?= htmlspecialchars($post['category']) ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="mp-blog-card__body">
                            <div class="mp-blog-card__meta">
                                <span><i class="fas fa-user-pen"></i>
                                    Dr. <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>
                                </span>
                                <span><i class="fas fa-calendar"></i>
                                    <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                </span>
                            </div>
                            <h3 class="mp-blog-card__title">
                                <a href="blog_post.php?id=<?= (int)$post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                            </h3>
                            <p class="mp-blog-card__excerpt"><?= htmlspecialchars($post['excerpt'] ?? '') ?></p>
                            <a href="blog_post.php?id=<?= (int)$post['id'] ?>" class="mp-blog-card__link">
                                Read article <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                <?php $bi++;
                endwhile;
            else:
                // Fallback blog cards using static content + Unsplash
                $fallbackPosts = [
                    ['title' => 'Understanding Heart Health: Warning Signs You Shouldn\'t Ignore', 'cat' => 'Cardiology', 'author' => 'Dr. Perera', 'date' => 'Jun 5, 2025', 'img' => $blogImages[0], 'excerpt' => 'Early detection of heart disease can save your life. Here\'s what our cardiologists want you to know about the symptoms that demand immediate attention.'],
                    ['title' => 'Children\'s Vaccinations: A Complete Schedule for Sri Lankan Parents', 'cat' => 'Paediatrics', 'author' => 'Dr. Fernando', 'date' => 'May 28, 2025', 'img' => $blogImages[1], 'excerpt' => 'Keeping up with your child\'s vaccination calendar is one of the most important things you can do. Our paediatricians break down each milestone.'],
                    ['title' => 'Managing Diabetes Through Diet: A Practical Guide', 'cat' => 'Endocrinology', 'author' => 'Dr. Wijesekara', 'date' => 'May 15, 2025', 'img' => $blogImages[2], 'excerpt' => 'Dietary changes remain the single most effective tool in managing type 2 diabetes. Our endocrinologist shares an evidence-based approach.'],
                ];
                foreach ($fallbackPosts as $bi => $p):
                ?>
                    <article class="mp-blog-card mp-reveal" data-delay="<?= $bi * 80 ?>">
                        <a href="blog.php" class="mp-blog-card__img-wrap">
                            <img src="<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
                            <span class="mp-blog-card__cat"><?= $p['cat'] ?></span>
                        </a>
                        <div class="mp-blog-card__body">
                            <div class="mp-blog-card__meta">
                                <span><i class="fas fa-user-pen"></i> <?= $p['author'] ?></span>
                                <span><i class="fas fa-calendar"></i> <?= $p['date'] ?></span>
                            </div>
                            <h3 class="mp-blog-card__title">
                                <a href="blog.php"><?= htmlspecialchars($p['title']) ?></a>
                            </h3>
                            <p class="mp-blog-card__excerpt"><?= htmlspecialchars($p['excerpt']) ?></p>
                            <a href="blog.php" class="mp-blog-card__link">Read article <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </article>
            <?php endforeach;
            endif; ?>
        </div>

        <div class="mp-section-cta mp-reveal" data-delay="200">
            <a href="blog.php" class="btn btn-primary btn-lg">
                <i class="fas fa-newspaper"></i> Visit the Blog
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     CTA BANNER
═══════════════════════════════════════════════════════════ -->
<section class="mp-cta-banner mp-reveal" data-delay="0">
    <div class="mp-cta-banner__bg"
        style="background-image:url('https://images.unsplash.com/photo-1581056771107-24ca5f033842?w=1600&q=80&auto=format&fit=crop')">
    </div>
    <div class="mp-cta-banner__overlay"></div>
    <div class="container mp-cta-banner__content">
        <h2>Your health can't wait.</h2>
        <p>Book a same-day consultation with a specialist right now.</p>
        <a href="book_appointment.php" class="btn btn-accent btn-xl">
            <i class="fas fa-calendar-check"></i> Book Now — It's Free to Register
        </a>
    </div>
</section>


<style>
    /* ── HERO ─────────────────────────────────────────────── */
    .mp-hero {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        overflow: hidden;
    }

    .mp-hero__bg {
        position: absolute;
        inset: 0;
        z-index: 0;
    }

    .mp-hero__bg-img {
        position: absolute;
        inset: -10%;
        background-size: cover;
        background-position: center;
        will-change: transform;
        transition: transform .1s linear;
    }

    .mp-hero__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg,
                rgba(6, 60, 62, .88) 0%,
                rgba(13, 115, 119, .75) 55%,
                rgba(24, 90, 40, .55) 100%);
    }

    .mp-hero__content {
        position: relative;
        z-index: 1;
        padding: 140px 0 100px;
        max-width: 700px;
    }

    .mp-hero__badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .28);
        border-radius: 50px;
        padding: 6px 18px;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: 1.2px;
        color: rgba(255, 255, 255, .9);
        margin-bottom: 24px;
        backdrop-filter: blur(8px);
    }

    .mp-hero__heading {
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: clamp(2.4rem, 5.5vw, 4rem);
        font-weight: 800;
        color: #fff;
        line-height: 1.15;
        margin: 0 0 20px;
    }

    .mp-hero__heading-accent {
        color: var(--accent, #f5c518);
    }

    .mp-hero__sub {
        font-size: 1.05rem;
        color: rgba(255, 255, 255, .78);
        line-height: 1.7;
        margin: 0 0 36px;
        max-width: 540px;
    }

    .mp-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 48px;
    }

    .mp-hero__stats {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0;
        background: rgba(255, 255, 255, .1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 16px;
        padding: 20px 28px;
        width: fit-content;
    }

    .mp-stat {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0 24px;
    }

    .mp-stat__num {
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: 1.8rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }

    .mp-stat__unit {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--accent, #f5c518);
    }

    .mp-stat__label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: rgba(255, 255, 255, .6);
        margin-top: 4px;
        font-weight: 600;
    }

    .mp-stat__divider {
        width: 1px;
        height: 40px;
        background: rgba(255, 255, 255, .2);
    }

    .mp-hero__scroll-cue {
        position: absolute;
        bottom: 32px;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 40px;
        border: 2px solid rgba(255, 255, 255, .4);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, .7);
        text-decoration: none;
        animation: scrollBounce 2s ease-in-out infinite;
        z-index: 2;
    }

    @keyframes scrollBounce {

        0%,
        100% {
            transform: translateX(-50%) translateY(0);
        }

        50% {
            transform: translateX(-50%) translateY(8px);
        }
    }

    /* Floating speciality badges */
    .mp-hero__floaters {
        position: absolute;
        inset: 0;
        z-index: 1;
        pointer-events: none;
    }

    .mp-floater {
        position: absolute;
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, .12);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 50px;
        padding: 8px 16px;
        font-size: .8rem;
        font-weight: 600;
        color: rgba(255, 255, 255, .85);
        animation: floatBob 5s ease-in-out infinite;
    }

    .mp-floater i {
        color: var(--accent, #f5c518);
    }

    .mp-floater--1 {
        top: 22%;
        right: 12%;
        animation-delay: 0s;
    }

    .mp-floater--2 {
        top: 42%;
        right: 6%;
        animation-delay: 1.2s;
    }

    .mp-floater--3 {
        top: 62%;
        right: 15%;
        animation-delay: .6s;
    }

    .mp-floater--4 {
        top: 75%;
        right: 8%;
        animation-delay: 1.8s;
    }

    @keyframes floatBob {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    @media (max-width: 900px) {
        .mp-hero__floaters {
            display: none;
        }
    }

    /* ── EMERGENCY BAR ───────────────────────────────────── */
    .mp-emergency-bar {
        background: var(--danger, #c0392b);
        color: #fff;
        padding: 12px 0;
    }

    .mp-emergency-bar__inner {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px 24px;
        font-size: .88rem;
    }

    .mp-emergency-bar__item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .mp-emergency-bar__item a {
        color: #fff;
        font-weight: 700;
        text-decoration: none;
    }

    .mp-emergency-bar__item a:hover {
        text-decoration: underline;
    }

    .mp-emergency-bar__sep {
        color: rgba(255, 255, 255, .4);
    }

    /* ── SECTIONS COMMON ─────────────────────────────────── */
    .mp-section {
        padding: 88px 0;
    }

    .mp-section--light {
        background: #f7f9fc;
    }

    .mp-section--white {
        background: #fff;
    }

    .mp-section--teal {
        background: linear-gradient(135deg, var(--teal-dark, #063c3e) 0%, var(--teal, #0d7373) 100%);
    }

    .mp-section-head {
        text-align: center;
        margin-bottom: 52px;
    }

    .mp-section-head__title {
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: clamp(1.6rem, 3vw, 2.3rem);
        font-weight: 800;
        color: var(--dark, #0b1d1e);
        margin: 8px 0 12px;
    }

    .mp-section-head__sub {
        color: var(--muted, #6b7c80);
        font-size: 1rem;
        max-width: 500px;
        margin: 0 auto;
    }

    .mp-eyebrow {
        font-size: .73rem;
        font-weight: 700;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        color: var(--teal, #0d7373);
    }

    .mp-eyebrow--light {
        color: var(--accent, #f5c518);
    }

    .mp-section-cta {
        text-align: center;
        margin-top: 40px;
    }

    /* ── SPECIALTY GRID ──────────────────────────────────── */
    .mp-specialty-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 18px;
    }

    .mp-specialty-card {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #fff;
        border: 1.5px solid var(--border, #e4eaec);
        border-radius: 14px;
        padding: 18px 20px;
        text-decoration: none;
        color: var(--dark, #0b1d1e);
        transition: transform .22s, box-shadow .22s, border-color .22s;
        position: relative;
        overflow: hidden;
    }

    .mp-specialty-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, .1);
        border-color: var(--sp-color, #0d7373);
    }

    .mp-specialty-card__icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        background: var(--sp-bg, #e0f5f5);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: var(--sp-color, #0d7373);
        flex-shrink: 0;
        transition: background .22s;
    }

    .mp-specialty-card:hover .mp-specialty-card__icon {
        background: var(--sp-color, #0d7373);
        color: #fff;
    }

    .mp-specialty-card__body h3 {
        font-size: .93rem;
        font-weight: 700;
        margin: 0 0 2px;
    }

    .mp-specialty-card__body p {
        font-size: .78rem;
        color: var(--muted, #6b7c80);
        margin: 0;
    }

    .mp-specialty-card__arrow {
        margin-left: auto;
        color: var(--sp-color, #0d7373);
        opacity: 0;
        transform: translateX(-6px);
        transition: opacity .22s, transform .22s;
        flex-shrink: 0;
        font-size: .85rem;
    }

    .mp-specialty-card:hover .mp-specialty-card__arrow {
        opacity: 1;
        transform: translateX(0);
    }

    /* ── WHY SPLIT ───────────────────────────────────────── */
    .mp-split {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 56px;
        align-items: center;
    }

    .mp-split__img {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 24px 60px rgba(0, 0, 0, .25);
    }

    .mp-split__img img {
        width: 100%;
        height: 480px;
        object-fit: cover;
        display: block;
        transition: transform .5s ease;
    }

    .mp-split__img:hover img {
        transform: scale(1.04);
    }

    .mp-split__img-badge {
        position: absolute;
        bottom: 24px;
        left: 24px;
        background: rgba(255, 255, 255, .95);
        border-radius: 12px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
        font-size: .85rem;
    }

    .mp-split__img-badge i {
        font-size: 1.6rem;
        color: var(--teal, #0d7373);
    }

    .mp-split__img-badge strong {
        display: block;
        font-weight: 700;
        color: var(--dark, #0b1d1e);
    }

    .mp-split__img-badge span {
        color: var(--muted, #6b7c80);
        font-size: .75rem;
    }

    .mp-split__title {
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: clamp(1.5rem, 2.5vw, 2rem);
        font-weight: 800;
        color: #fff;
        margin: 10px 0 28px;
    }

    .mp-why-list {
        display: flex;
        flex-direction: column;
        gap: 22px;
    }

    .mp-why-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }

    .mp-why-item__icon {
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, .12);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: var(--accent, #f5c518);
        flex-shrink: 0;
    }

    .mp-why-item h4 {
        color: #fff;
        font-weight: 700;
        margin: 0 0 4px;
        font-size: .95rem;
    }

    .mp-why-item p {
        color: rgba(255, 255, 255, .68);
        font-size: .85rem;
        margin: 0;
        line-height: 1.6;
    }

    /* ── DOCTOR CARDS ────────────────────────────────────── */
    .mp-doctor-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
    }

    .mp-doc-card {
        background: #fff;
        border: 1.5px solid var(--border, #e4eaec);
        border-radius: 16px;
        padding: 28px 22px 22px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform .22s, box-shadow .22s;
        position: relative;
    }

    .mp-doc-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 40px rgba(13, 115, 119, .12);
        border-color: var(--teal, #0d7373);
    }

    .mp-doc-card__avatar {
        width: 72px;
        height: 72px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: 1.6rem;
        font-weight: 800;
        margin-bottom: 14px;
        border: 2px solid rgba(255, 255, 255, .6);
        box-shadow: 0 4px 14px rgba(0, 0, 0, .08);
    }

    .mp-doc-card__spec {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        border-radius: 50px;
        padding: 4px 12px;
        margin-bottom: 10px;
    }

    .mp-doc-card__name {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark, #0b1d1e);
        margin: 0 0 6px;
    }

    .mp-doc-card__hosp {
        font-size: .78rem;
        color: var(--muted, #6b7c80);
        margin: 0 0 12px;
        display: flex;
        align-items: center;
        gap: 5px;
        justify-content: center;
    }

    .mp-doc-card__meta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
        font-size: .78rem;
        color: var(--mid, #4a6163);
        margin-bottom: 18px;
    }

    .mp-doc-card__meta span {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .mp-doc-card__actions {
        display: flex;
        gap: 8px;
        width: 100%;
    }

    .mp-doc-card__actions .btn {
        flex: 1;
        justify-content: center;
        font-size: .82rem;
        padding: 8px 10px;
    }

    /* ── BLOG PREVIEW ────────────────────────────────────── */
    .mp-blog-preview-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }

    .mp-blog-card {
        background: #fff;
        border: 1.5px solid var(--border, #e4eaec);
        border-radius: 16px;
        overflow: hidden;
        transition: transform .22s, box-shadow .22s;
        display: flex;
        flex-direction: column;
    }

    .mp-blog-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 40px rgba(0, 0, 0, .09);
    }

    .mp-blog-card__img-wrap {
        display: block;
        position: relative;
        overflow: hidden;
        height: 200px;
    }

    .mp-blog-card__img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .4s ease;
    }

    .mp-blog-card:hover .mp-blog-card__img-wrap img {
        transform: scale(1.06);
    }

    .mp-blog-card__cat {
        position: absolute;
        top: 14px;
        left: 14px;
        background: var(--teal, #0d7373);
        color: #fff;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 50px;
    }

    .mp-blog-card__body {
        padding: 22px 22px 20px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .mp-blog-card__meta {
        display: flex;
        gap: 14px;
        font-size: .75rem;
        color: var(--muted, #6b7c80);
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .mp-blog-card__meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .mp-blog-card__title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--dark, #0b1d1e);
        margin: 0 0 8px;
        line-height: 1.4;
    }

    .mp-blog-card__title a {
        color: inherit;
        text-decoration: none;
    }

    .mp-blog-card__title a:hover {
        color: var(--teal, #0d7373);
    }

    .mp-blog-card__excerpt {
        font-size: .85rem;
        color: var(--mid, #4a6163);
        line-height: 1.6;
        margin: 0 0 16px;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .mp-blog-card__link {
        font-size: .83rem;
        font-weight: 700;
        color: var(--teal, #0d7373);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: gap .2s;
        margin-top: auto;
    }

    .mp-blog-card__link:hover {
        gap: 10px;
    }

    /* ── CTA BANNER ──────────────────────────────────────── */
    .mp-cta-banner {
        position: relative;
        padding: 100px 0;
        text-align: center;
        overflow: hidden;
    }

    .mp-cta-banner__bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
    }

    .mp-cta-banner__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(6, 60, 62, .9), rgba(13, 115, 119, .8));
    }

    .mp-cta-banner__content {
        position: relative;
        z-index: 1;
    }

    .mp-cta-banner__content h2 {
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: clamp(1.8rem, 3.5vw, 2.8rem);
        font-weight: 800;
        color: #fff;
        margin: 0 0 12px;
    }

    .mp-cta-banner__content p {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, .75);
        margin: 0 0 32px;
    }

    /* ── REVEAL ANIMATIONS ───────────────────────────────── */
    .mp-reveal {
        opacity: 0;
        transform: translateY(28px);
        transition: opacity .55s ease, transform .55s ease;
    }

    .mp-reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* ── BUTTON ADDITIONS ────────────────────────────────── */
    .btn-ghost-white {
        background: transparent;
        border: 2px solid rgba(255, 255, 255, .5);
        color: #fff;
    }

    .btn-ghost-white:hover {
        background: rgba(255, 255, 255, .12);
        border-color: rgba(255, 255, 255, .8);
    }

    .btn-outline-teal {
        background: transparent;
        border: 1.5px solid var(--teal, #0d7373);
        color: var(--teal, #0d7373);
    }

    .btn-outline-teal:hover {
        background: var(--teal, #0d7373);
        color: #fff;
    }

    .btn-lg {
        padding: 13px 28px;
        font-size: 1rem;
    }

    .btn-xl {
        padding: 16px 36px;
        font-size: 1.1rem;
    }

    .btn-sm {
        padding: 8px 14px;
        font-size: .82rem;
    }

    /* ── RESPONSIVE ──────────────────────────────────────── */
    @media (max-width: 800px) {
        .mp-split {
            grid-template-columns: 1fr;
        }

        .mp-split__img {
            order: -1;
        }

        .mp-split__img img {
            height: 280px;
        }

        .mp-hero__stats {
            padding: 14px 18px;
        }

        .mp-stat {
            padding: 0 14px;
        }

        .mp-stat__num {
            font-size: 1.4rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .mp-reveal {
            opacity: 1 !important;
            transform: none !important;
        }

        .mp-hero__bg-img {
            transition: none;
        }

        .mp-floater {
            animation: none;
        }

        .mp-hero__scroll-cue {
            animation: none;
        }
    }
</style>

<!-- ═══════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════ -->
<script>
    (function() {
        // ── 1. Scroll-reveal observer ──────────────────────
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const delay = parseInt(el.dataset.delay || 0);
                setTimeout(() => el.classList.add('is-visible'), delay);
                revealObserver.unobserve(el);
            });
        }, {
            threshold: 0.12
        });
        document.querySelectorAll('.mp-reveal').forEach(el => revealObserver.observe(el));

        // ── 2. Parallax hero on scroll ────────────────────
        const heroBg = document.getElementById('heroParallax');
        if (heroBg && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            window.addEventListener('scroll', () => {
                const y = window.scrollY;
                heroBg.style.transform = `translateY(${y * 0.3}px)`;
            }, {
                passive: true
            });
        }

        // ── 3. Count-up numbers on hero stats ─────────────
        function countUp(el, target, duration) {
            const start = performance.now();
            const update = (now) => {
                const elapsed = now - start;
                const progress = Math.min(elapsed / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * target).toLocaleString();
                if (progress < 1) requestAnimationFrame(update);
            };
            requestAnimationFrame(update);
        }

        const statsEl = document.querySelector('.mp-hero__stats');
        if (statsEl) {
            const io = new IntersectionObserver(([entry]) => {
                if (!entry.isIntersecting) return;
                document.querySelectorAll('.mp-stat__num[data-count]').forEach(el => {
                    countUp(el, parseInt(el.dataset.count), 1800);
                });
                io.disconnect();
            }, {
                threshold: 0.5
            });
            io.observe(statsEl);
        }
    })();
</script>
<?php include 'footer.php'; ?>