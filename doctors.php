<?php
// doctors.php — Medicare Plus
// Enhanced: rich profile cards, specialty filter, search, smooth animations
session_start();
require_once 'db_connect.php';
include 'header.php';

// ── Filters & pagination ──────────────────────────────
$specFilter = trim($_GET['spec'] ?? '');
$search     = trim($_GET['q'] ?? '');
$sortBy     = $_GET['sort'] ?? 'rating';
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 12;
$offset     = ($page - 1) * $perPage;

$allowed_sort = ['rating' => 'd.rating DESC', 'fee_asc' => 'd.consultation_fee ASC', 'fee_desc' => 'd.consultation_fee DESC', 'experience' => 'd.experience_years DESC'];
$orderBy = $allowed_sort[$sortBy] ?? 'd.rating DESC';

$whereArr = ['1=1'];
$params   = [];
$types    = '';
if ($specFilter) {
    $whereArr[] = 'd.specialization = ?';
    $params[] = $specFilter;
    $types .= 's';
}
if ($search) {
    $whereArr[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR d.specialization LIKE ? OR d.hospital LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
    $types .= 'ssss';
}
$where = 'WHERE ' . implode(' AND ', $whereArr);

// Count
$countSql = "SELECT COUNT(*) AS total FROM doctors d JOIN users u ON u.id = d.user_id $where";
$stmt = $conn->prepare($countSql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$totalPages = max(1, (int)ceil($total / $perPage));

// Doctors
$sql = "SELECT d.id, u.first_name, u.last_name, d.specialization,
               d.consultation_fee, d.experience_years, d.rating,
               d.hospital, d.location, d.bio, d.qualification, d.availability
        FROM doctors d
        JOIN users u ON u.id = d.user_id
        $where
        ORDER BY $orderBy
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$allP = [...$params, $perPage, $offset];
$allT = $types . 'ii';
$stmt->bind_param($allT, ...$allP);
$stmt->execute();
$doctors = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Specialties for filter
$specRes = $conn->query("SELECT d.specialization, COUNT(*) AS cnt FROM doctors d JOIN users u ON u.id = d.user_id GROUP BY d.specialization ORDER BY cnt DESC");
$specialties = $specRes ? $specRes->fetch_all(MYSQLI_ASSOC) : [];

// Colour map
$specColours = [
    'Cardiology'           => ['#c0392b', '#fdecea'],
    'Neurology'            => ['#6c3483', '#f3e8f9'],
    'Paediatrics'          => ['#1565c0', '#e3f0fc'],
    'Pediatrics'           => ['#1565c0', '#e3f0fc'],
    'Dermatology'          => ['#b7950b', '#fef9e7'],
    'Orthopaedics'         => ['#1a5276', '#eaf2fc'],
    'Orthopedics'          => ['#1a5276', '#eaf2fc'],
    'General Practitioner' => ['#1e8449', '#e9f7ef'],
    'Gynaecology'          => ['#b03a8a', '#fce4f4'],
    'ENT'                  => ['#ca6f1e', '#fef3e2'],
    'Endocrinology'        => ['#1abc9c', '#e8faf5'],
    'Pulmonology'          => ['#2980b9', '#eaf4fb'],
    'Ophthalmology'        => ['#0d7373', '#e0f5f5'],
    'Oncology'             => ['#7d3c98', '#f4ecfa'],
];

// Unsplash doctor photos by speciality (gender-neutral, professional)
$specPhotos = [
    'Cardiology'     => ['photo-1612349317150-e413f6a5b16d', 'photo-1559839734-2b71ea197ec2'],
    'Neurology'      => ['photo-1537368910025-700350fe46c7', 'photo-1526256262350-7da7584cf5eb'],
    'Paediatrics'    => ['photo-1594824476967-48c8b964273f', 'photo-1576091160550-2173dba999ef'],
    'Dermatology'    => ['photo-1559839734-2b71ea197ec2', 'photo-1612349317150-e413f6a5b16d'],
    'Orthopaedics'   => ['photo-1584467735871-8e548e28c0d9', 'photo-1530497610245-94d3c16cda28'],
    'Gynaecology'    => ['photo-1594824476967-48c8b964273f', 'photo-1607990281513-2c110a25bd8c'],
    'General Practitioner' => ['photo-1612349317150-e413f6a5b16d', 'photo-1537368910025-700350fe46c7'],
    'ENT'            => ['photo-1559839734-2b71ea197ec2', 'photo-1526256262350-7da7584cf5eb'],
    'Endocrinology'  => ['photo-1576091160550-2173dba999ef', 'photo-1594824476967-48c8b964273f'],
    'Pulmonology'    => ['photo-1584467735871-8e548e28c0d9', 'photo-1612349317150-e413f6a5b16d'],
    'Neurology'      => ['photo-1537368910025-700350fe46c7', 'photo-1559839734-2b71ea197ec2'],
];
$fallbackPhotos = [
    'photo-1612349317150-e413f6a5b16d',
    'photo-1537368910025-700350fe46c7',
    'photo-1559839734-2b71ea197ec2',
    'photo-1594824476967-48c8b964273f',
    'photo-1576091160550-2173dba999ef',
    'photo-1584467735871-8e548e28c0d9',
];

function getDoctorPhoto($spec, $docId, $specPhotos, $fallbackPhotos)
{
    $photos = $specPhotos[$spec] ?? $fallbackPhotos;
    $slug   = $photos[$docId % count($photos)];
    return "https://images.unsplash.com/{$slug}?w=300&h=300&q=80&auto=format&fit=crop&crop=face";
}
?>

<!-- ═══════════════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════════════════ -->
<section class="doc-hero">
    <div class="doc-hero__bg"
        style="background-image:url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=1600&q=80&auto=format&fit=crop')">
    </div>
    <div class="doc-hero__overlay"></div>
    <div class="container doc-hero__content">
        <span class="mp-eyebrow mp-eyebrow--light mp-reveal" data-delay="0">Our Team</span>
        <h1 class="doc-hero__title mp-reveal" data-delay="80">Find Your Specialist</h1>
        <p class="doc-hero__sub mp-reveal" data-delay="160">
            <?= $total ?> board-certified doctors across <?= count($specialties) ?> specialties.
            Search, filter, and book — all in one place.
        </p>

        <!-- Inline search -->
        <form class="doc-hero__search mp-reveal" data-delay="240" method="GET" action="doctors.php">
            <?php if ($specFilter): ?>
                <input type="hidden" name="spec" value="<?= htmlspecialchars($specFilter) ?>">
            <?php endif; ?>
            <i class="fas fa-search"></i>
            <input type="search" name="q" placeholder="Search by name, specialty, or hospital…" value="<?= htmlspecialchars($search) ?>">
            <?php if ($search): ?>
                <a href="doctors.php<?= $specFilter ? '?spec=' . urlencode($specFilter) : '' ?>" class="doc-hero__search-clear" title="Clear search">
                    <i class="fas fa-times"></i>
                </a>
            <?php endif; ?>
            <button type="submit" class="btn btn-accent">Search</button>
        </form>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     SPECIALTY TABS
═══════════════════════════════════════════════════════════ -->
<div class="doc-spec-bar">
    <div class="container doc-spec-bar__inner">
        <a href="doctors.php<?= $search ? '?q=' . urlencode($search) : '' ?>"
            class="doc-spec-pill <?= !$specFilter ? 'is-active' : '' ?>">
            All
        </a>
        <?php foreach ($specialties as $sp):
            $spColour = $specColours[$sp['specialization']] ?? ['#0d7373', '#e0f5f5'];
            $spQ = http_build_query(array_filter(['spec' => $sp['specialization'], 'q' => $search]));
        ?>
            <a href="doctors.php?<?= $spQ ?>"
                class="doc-spec-pill <?= $specFilter === $sp['specialization'] ? 'is-active' : '' ?>"
                style="--pill-color:<?= $spColour[0] ?>;--pill-bg:<?= $spColour[1] ?>">
                <?= htmlspecialchars($sp['specialization']) ?>
                <span><?= (int)$sp['cnt'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     RESULTS TOOLBAR + GRID
═══════════════════════════════════════════════════════════ -->
<section class="mp-section mp-section--light">
    <div class="container">

        <!-- Toolbar -->
        <div class="doc-toolbar mp-reveal" data-delay="0">
            <p class="doc-toolbar__count">
                <?php if ($specFilter || $search): ?>
                    Showing <strong><?= $total ?></strong> result<?= $total !== 1 ? 's' : '' ?>
                    <?php if ($specFilter): ?> in <strong><?= htmlspecialchars($specFilter) ?></strong><?php endif; ?>
                    <?php if ($search): ?> for "<strong><?= htmlspecialchars($search) ?></strong>"<?php endif; ?>
                    <?php else: ?>
                        <strong><?= $total ?></strong> specialist<?= $total !== 1 ? 's' : '' ?> available
                    <?php endif; ?>
            </p>
            <form class="doc-toolbar__sort" method="GET" id="sortForm">
                <?php if ($specFilter): ?><input type="hidden" name="spec" value="<?= htmlspecialchars($specFilter) ?>"><?php endif; ?>
                <?php if ($search): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>"><?php endif; ?>
                <label for="sortSelect"><i class="fas fa-sort"></i> Sort:</label>
                <select name="sort" id="sortSelect" onchange="document.getElementById('sortForm').submit()">
                    <option value="rating" <?= $sortBy === 'rating'     ? 'selected' : '' ?>>Top Rated</option>
                    <option value="experience" <?= $sortBy === 'experience' ? 'selected' : '' ?>>Most Experienced</option>
                    <option value="fee_asc" <?= $sortBy === 'fee_asc'    ? 'selected' : '' ?>>Lowest Fee</option>
                    <option value="fee_desc" <?= $sortBy === 'fee_desc'   ? 'selected' : '' ?>>Highest Fee</option>
                </select>
            </form>
        </div>

        <?php if (count($doctors) > 0): ?>
            <!-- Doctor cards grid -->
            <div class="doc-grid" id="doctorGrid">
                <?php foreach ($doctors as $i => $doc):
                    $spec     = htmlspecialchars($doc['specialization']);
                    $name     = htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']);
                    $initials = strtoupper(substr($doc['first_name'], 0, 1) . substr($doc['last_name'], 0, 1));
                    $colours  = $specColours[$doc['specialization']] ?? ['#0d7373', '#e0f5f5'];
                    $rating   = (float)$doc['rating'];
                    $exp      = (int)$doc['experience_years'];
                    $fee      = (float)$doc['consultation_fee'];
                    $hospital = htmlspecialchars($doc['hospital'] ?? '');
                    $location = htmlspecialchars($doc['location'] ?? 'Sri Lanka');
                    $avail    = htmlspecialchars($doc['availability'] ?? 'Mon – Fri');
                    $photoUrl = getDoctorPhoto($doc['specialization'], $doc['id'], $specPhotos, $fallbackPhotos);
                    $bioSnip  = $doc['bio'] ? mb_strimwidth(strip_tags($doc['bio']), 0, 100, '…') : '';
                ?>
                    <article class="doc-card mp-reveal" data-delay="<?= ($i % 4) * 60 ?>">

                        <!-- Card top: photo + accent bar -->
                        <div class="doc-card__header" style="--doc-color:<?= $colours[0] ?>;--doc-bg:<?= $colours[1] ?>">
                            <div class="doc-card__photo-wrap">
                                <!-- Real photo from Unsplash -->
                                <img class="doc-card__photo"
                                    src="<?= $photoUrl ?>"
                                    alt="Dr. <?= $name ?>"
                                    loading="lazy"
                                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <!-- Fallback initials avatar -->
                                <div class="doc-card__initials" style="display:none;background:<?= $colours[1] ?>;color:<?= $colours[0] ?>">
                                    <?= $initials ?>
                                </div>
                            </div>

                            <!-- Speciality badge -->
                            <div class="doc-card__spec-badge" style="background:<?= $colours[1] ?>;color:<?= $colours[0] ?>">
                                <?= $spec ?>
                            </div>
                        </div>

                        <!-- Card body -->
                        <div class="doc-card__body">
                            <h3 class="doc-card__name">Dr. <?= $name ?></h3>

                            <?php if ($hospital): ?>
                                <p class="doc-card__hospital">
                                    <i class="fas fa-hospital-alt"></i> <?= $hospital ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($bioSnip): ?>
                                <p class="doc-card__bio"><?= htmlspecialchars($bioSnip) ?></p>
                            <?php endif; ?>

                            <!-- Stats row -->
                            <div class="doc-card__stats">
                                <?php if ($rating > 0): ?>
                                    <div class="doc-card__stat">
                                        <div class="doc-card__stat-val doc-card__stat-val--star">
                                            <?= number_format($rating, 1) ?>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="doc-card__stat-label">Rating</div>
                                    </div>
                                <?php endif; ?>
                                <?php if ($exp > 0): ?>
                                    <div class="doc-card__stat">
                                        <div class="doc-card__stat-val"><?= $exp ?></div>
                                        <div class="doc-card__stat-label">Years Exp.</div>
                                    </div>
                                <?php endif; ?>
                                <div class="doc-card__stat">
                                    <div class="doc-card__stat-val">LKR <?= number_format($fee, 0) ?></div>
                                    <div class="doc-card__stat-label">Per Visit</div>
                                </div>
                            </div>

                            <!-- Availability pill -->
                            <div class="doc-card__avail">
                                <span class="doc-card__avail-dot"></span>
                                <?= $avail ?>
                            </div>
                        </div>

                        <!-- Card footer actions -->
                        <div class="doc-card__footer">
                            <a href="doctor_profile.php?id=<?= (int)$doc['id'] ?>" class="btn btn-outline-teal">
                                <i class="fas fa-id-card"></i> Profile
                            </a>
                            <a href="book_appointment.php?doctor_id=<?= (int)$doc['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-calendar-check"></i> Book
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="blog-pagination mp-reveal" data-delay="100" style="margin-top:48px">
                    <?php
                    $qBase = http_build_query(array_filter(['spec' => $specFilter, 'q' => $search, 'sort' => $sortBy]));
                    $qBase = $qBase ? '&' . $qBase : '';
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $qBase ?>" class="blog-pagination__btn"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>
                    <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                        <a href="?page=<?= $p ?><?= $qBase ?>" class="blog-pagination__btn <?= $p === $page ? 'is-active' : '' ?>"><?= $p ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $qBase ?>" class="blog-pagination__btn"><i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="blog-empty mp-reveal" data-delay="0" style="grid-column:1/-1">
                <i class="fas fa-user-doctor"></i>
                <h3>No doctors found</h3>
                <p>Try clearing your filters or searching for a different specialty.</p>
                <a href="doctors.php" class="btn btn-primary">View All Doctors</a>
            </div>
        <?php endif; ?>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     STYLES
═══════════════════════════════════════════════════════════ -->
<style>
    /* ── HERO ─────────────────────────────────────────────── */
    .doc-hero {
        position: relative;
        padding: 120px 0 80px;
        overflow: hidden;
        text-align: center;
    }

    .doc-hero__bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center top;
    }

    .doc-hero__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(6, 60, 62, .93) 0%, rgba(13, 115, 119, .84) 60%, rgba(26, 84, 118, .7) 100%);
    }

    .doc-hero__content {
        position: relative;
        z-index: 1;
        max-width: 660px;
        margin: 0 auto;
    }

    .doc-hero__title {
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        color: #fff;
        margin: 10px 0 14px;
    }

    .doc-hero__sub {
        font-size: 1.05rem;
        color: rgba(255, 255, 255, .75);
        line-height: 1.7;
        margin: 0 0 32px;
    }

    .doc-hero__search {
        display: flex;
        align-items: center;
        gap: 8px;
        max-width: 520px;
        margin: 0 auto;
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 12px;
        padding: 6px 6px 6px 16px;
        backdrop-filter: blur(8px);
    }

    .doc-hero__search i {
        color: rgba(255, 255, 255, .5);
        flex-shrink: 0;
    }

    .doc-hero__search input {
        flex: 1;
        background: transparent;
        border: none;
        outline: none;
        color: #fff;
        font-size: .95rem;
        min-width: 0;
    }

    .doc-hero__search input::placeholder {
        color: rgba(255, 255, 255, .45);
    }

    .doc-hero__search-clear {
        color: rgba(255, 255, 255, .55);
        text-decoration: none;
        font-size: .85rem;
        padding: 2px 4px;
    }

    .doc-hero__search-clear:hover {
        color: #fff;
    }

    /* ── SPEC TABS BAR ───────────────────────────────────── */
    .doc-spec-bar {
        background: #fff;
        border-bottom: 1.5px solid var(--border, #e4eaec);
        position: sticky;
        top: 0;
        z-index: 40;
        box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
    }

    .doc-spec-bar__inner {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding: 12px 0;
        scrollbar-width: none;
    }

    .doc-spec-bar__inner::-webkit-scrollbar {
        display: none;
    }

    .doc-spec-pill {
        flex-shrink: 0;
        padding: 7px 16px;
        border-radius: 50px;
        font-size: .8rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--mid, #4a6163);
        background: var(--sand, #f7f3ed);
        border: 1.5px solid transparent;
        transition: all .2s;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .doc-spec-pill span {
        background: rgba(0, 0, 0, .08);
        border-radius: 50px;
        padding: 1px 7px;
        font-size: .7rem;
    }

    .doc-spec-pill:hover {
        background: var(--pill-bg, #e0f5f5);
        color: var(--pill-color, #0d7373);
        border-color: var(--pill-color, #0d7373);
    }

    .doc-spec-pill.is-active {
        background: var(--pill-bg, rgba(13, 115, 119, .1));
        color: var(--pill-color, #0d7373);
        border-color: var(--pill-color, #0d7373);
        font-weight: 700;
    }

    /* ── TOOLBAR ─────────────────────────────────────────── */
    .doc-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 28px;
    }

    .doc-toolbar__count {
        font-size: .9rem;
        color: var(--mid, #4a6163);
        margin: 0;
    }

    .doc-toolbar__sort {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .85rem;
        color: var(--muted, #6b7c80);
    }

    .doc-toolbar__sort select {
        border: 1.5px solid var(--border, #e4eaec);
        border-radius: 8px;
        padding: 6px 12px;
        font-size: .85rem;
        outline: none;
        cursor: pointer;
        color: var(--dark, #0b1d1e);
        background: #fff;
    }

    .doc-toolbar__sort select:focus {
        border-color: var(--teal, #0d7373);
    }

    /* ── DOCTOR CARD GRID ────────────────────────────────── */
    .doc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 22px;
    }

    .doc-card {
        background: #fff;
        border: 1.5px solid var(--border, #e4eaec);
        border-radius: 20px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform .24s, box-shadow .24s, border-color .24s;
    }

    .doc-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 48px rgba(13, 115, 119, .12);
        border-color: var(--doc-color, #0d7373);
    }

    /* ── CARD HEADER (photo zone) ──────────────────────── */
    .doc-card__header {
        position: relative;
        background: var(--doc-bg, #e0f5f5);
        padding: 28px 22px 60px;
        display: flex;
        justify-content: center;
    }

    /* Decorative wavy divider between header and body */
    .doc-card__header::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 36px;
        background: #fff;
        clip-path: ellipse(58% 100% at 50% 100%);
    }

    .doc-card__photo-wrap {
        width: 96px;
        height: 96px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid rgba(255, 255, 255, .9);
        box-shadow: 0 6px 20px rgba(0, 0, 0, .12);
        position: relative;
        z-index: 1;
    }

    .doc-card__photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .doc-card__initials {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: 1.8rem;
        font-weight: 800;
    }

    .doc-card__spec-badge {
        position: absolute;
        bottom: 14px;
        left: 50%;
        transform: translateX(-50%);
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        padding: 4px 14px;
        border-radius: 50px;
        white-space: nowrap;
        z-index: 2;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
    }

    /* ── CARD BODY ──────────────────────────────────────── */
    .doc-card__body {
        padding: 4px 22px 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .doc-card__name {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--dark, #0b1d1e);
        margin: 0 0 6px;
    }

    .doc-card__hospital {
        font-size: .78rem;
        color: var(--muted, #6b7c80);
        margin: 0 0 10px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .doc-card__bio {
        font-size: .8rem;
        color: var(--mid, #4a6163);
        line-height: 1.55;
        margin: 0 0 14px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .doc-card__stats {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
        margin-bottom: 14px;
        width: 100%;
        padding: 12px 0;
        border-top: 1px solid var(--border, #e4eaec);
        border-bottom: 1px solid var(--border, #e4eaec);
    }

    .doc-card__stat {
        text-align: center;
    }

    .doc-card__stat-val {
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: .95rem;
        font-weight: 800;
        color: var(--dark, #0b1d1e);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        white-space: nowrap;
    }

    .doc-card__stat-val--star i {
        color: #f39c12;
        font-size: .8rem;
    }

    .doc-card__stat-label {
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--muted, #6b7c80);
        margin-top: 2px;
        font-weight: 600;
    }

    .doc-card__avail {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: .78rem;
        color: var(--mid, #4a6163);
        margin-top: 2px;
    }

    .doc-card__avail-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #27ae60;
        flex-shrink: 0;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            box-shadow: 0 0 0 0 rgba(39, 174, 96, .4);
        }

        50% {
            box-shadow: 0 0 0 6px rgba(39, 174, 96, 0);
        }
    }

    /* ── CARD FOOTER ───────────────────────────────────── */
    .doc-card__footer {
        display: flex;
        gap: 8px;
        padding: 0 18px 18px;
    }

    .doc-card__footer .btn {
        flex: 1;
        justify-content: center;
        font-size: .83rem;
        padding: 9px 10px;
    }

    /* ── REUSE FROM Home.php ─────────────────────────────── */
    .mp-reveal {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity .5s ease, transform .5s ease;
    }

    .mp-reveal.is-visible {
        opacity: 1;
        transform: translateY(0);
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

    .mp-section {
        padding: 72px 0;
    }

    .mp-section--light {
        background: #f7f9fc;
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

    .blog-pagination {
        display: flex;
        gap: 8px;
        justify-content: center;
    }

    .blog-pagination__btn {
        width: 40px;
        height: 40px;
        border: 1.5px solid var(--border, #e4eaec);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: var(--dark, #0b1d1e);
        font-weight: 600;
        font-size: .9rem;
        transition: all .2s;
    }

    .blog-pagination__btn:hover {
        border-color: var(--teal, #0d7373);
        color: var(--teal, #0d7373);
    }

    .blog-pagination__btn.is-active {
        background: var(--teal, #0d7373);
        border-color: var(--teal, #0d7373);
        color: #fff;
    }

    .blog-empty {
        text-align: center;
        padding: 72px 20px;
    }

    .blog-empty i {
        font-size: 3rem;
        color: var(--muted, #6b7c80);
        margin-bottom: 16px;
        display: block;
    }

    .blog-empty h3 {
        color: var(--dark, #0b1d1e);
        margin: 0 0 8px;
    }

    .blog-empty p {
        color: var(--muted, #6b7c80);
        margin: 0 0 24px;
    }

    /* ── RESPONSIVE ──────────────────────────────────────── */
    @media (max-width: 600px) {
        .doc-grid {
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .doc-card__header {
            padding: 22px 16px 56px;
        }

        .doc-card__photo-wrap {
            width: 76px;
            height: 76px;
        }

        .doc-card__body {
            padding: 4px 14px 12px;
        }

        .doc-card__footer {
            padding: 0 12px 14px;
            gap: 6px;
        }
    }

    @media (max-width: 400px) {
        .doc-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .mp-reveal {
            opacity: 1 !important;
            transform: none !important;
        }

        .doc-card__avail-dot {
            animation: none;
        }
    }
</style>

<script>
    // Scroll reveal
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            setTimeout(() => e.target.classList.add('is-visible'), parseInt(e.target.dataset.delay || 0));
            io.unobserve(e.target);
        });
    }, {
        threshold: 0.08
    });
    document.querySelectorAll('.mp-reveal').forEach(el => io.observe(el));

    // Sticky spec bar active indicator scroll
    const specBar = document.querySelector('.doc-spec-bar__inner');
    const activePill = specBar?.querySelector('.doc-spec-pill.is-active');
    if (activePill) activePill.scrollIntoView({
        behavior: 'smooth',
        block: 'nearest',
        inline: 'center'
    });
</script>

<?php include 'footer.php'; ?>
