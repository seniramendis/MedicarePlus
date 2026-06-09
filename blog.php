<?php
// blog.php — Medicare Plus
// Enhanced: Unsplash cover images, hero background, category filter, scroll animations
session_start();
require_once 'db_connect.php';
include 'header.php';

// ── Pagination & filter ───────────────────────────────
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset  = ($page - 1) * $perPage;
$catFilter = trim($_GET['cat'] ?? '');
$search    = trim($_GET['q'] ?? '');

$whereArr = ['b.published = 1'];
$params   = [];
$types    = '';
if ($catFilter) {
    $whereArr[] = 'b.category = ?';
    $params[] = $catFilter;
    $types .= 's';
}
if ($search) {
    $whereArr[] = '(b.title LIKE ? OR b.excerpt LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}
$where = 'WHERE ' . implode(' AND ', $whereArr);

// Total count
$countSql = "SELECT COUNT(*) AS total FROM blog_posts b $where";
$stmt = $conn->prepare($countSql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$totalPages = max(1, (int)ceil($total / $perPage));

// Posts
$sql = "SELECT b.id, b.title, b.excerpt, b.created_at, b.category, b.read_time,
               u.first_name, u.last_name, d.specialization
        FROM blog_posts b
        LEFT JOIN users u ON u.id = b.author_id
        LEFT JOIN doctors d ON d.user_id = u.id
        $where
        ORDER BY b.created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$allParams = [...$params, $perPage, $offset];
$allTypes  = $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Categories
$catRes  = $conn->query("SELECT category, COUNT(*) AS cnt FROM blog_posts WHERE published=1 AND category != '' GROUP BY category ORDER BY cnt DESC");
$categories = $catRes ? $catRes->fetch_all(MYSQLI_ASSOC) : [];

// Curated Unsplash images mapped by category keyword
$categoryImages = [
    'Cardiology'     => 'https://images.unsplash.com/photo-1628348068343-c6a848d2b6dd?w=600&q=75&auto=format&fit=crop',
    'Neurology'      => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=600&q=75&auto=format&fit=crop',
    'Paediatrics'    => 'https://images.unsplash.com/photo-1503454537195-1dcabb73ffb9?w=600&q=75&auto=format&fit=crop',
    'Dermatology'    => 'https://images.unsplash.com/photo-1576671414221-4baf8a6b87f3?w=600&q=75&auto=format&fit=crop',
    'Orthopaedics'   => 'https://images.unsplash.com/photo-1530497610245-94d3c16cda28?w=600&q=75&auto=format&fit=crop',
    'Gynaecology'    => 'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?w=600&q=75&auto=format&fit=crop',
    'ENT'            => 'https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?w=600&q=75&auto=format&fit=crop',
    'Endocrinology'  => 'https://images.unsplash.com/photo-1576671414221-4baf8a6b87f3?w=600&q=75&auto=format&fit=crop',
    'Pulmonology'    => 'https://images.unsplash.com/photo-1584467735871-8e548e28c0d9?w=600&q=75&auto=format&fit=crop',
    'General'        => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=600&q=75&auto=format&fit=crop',
    'Mental Health'  => 'https://images.unsplash.com/photo-1456406644174-8ddd4cd52a06?w=600&q=75&auto=format&fit=crop',
    'Nutrition'      => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=600&q=75&auto=format&fit=crop',
    'Fitness'        => 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=600&q=75&auto=format&fit=crop',
];
$fallbackImages = [
    'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=600&q=75&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=600&q=75&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=600&q=75&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1532938911079-1b06ac7ceec7?w=600&q=75&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1584467735871-8e548e28c0d9?w=600&q=75&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1628348068343-c6a848d2b6dd?w=600&q=75&auto=format&fit=crop',
];

function getBlogImage($category, $index, $categoryImages, $fallbackImages)
{
    foreach ($categoryImages as $key => $url) {
        if (stripos($category, $key) !== false) return $url;
    }
    return $fallbackImages[$index % count($fallbackImages)];
}

// Featured post (latest)
$featuredSql = "SELECT b.id, b.title, b.excerpt, b.created_at, b.category, b.read_time,
                       u.first_name, u.last_name
                FROM blog_posts b
                LEFT JOIN users u ON u.id = b.author_id
                WHERE b.published = 1
                ORDER BY b.created_at DESC LIMIT 1";
$featuredPost = $conn->query($featuredSql)?->fetch_assoc();
?>

<!-- ═══════════════════════════════════════════════════════
     HERO — blog header with layered background
═══════════════════════════════════════════════════════════ -->
<section class="blog-hero">
    <div class="blog-hero__bg"
        style="background-image:url('https://images.unsplash.com/photo-1505751172876-fa1923c5c528?w=1600&q=80&auto=format&fit=crop')">
    </div>
    <div class="blog-hero__overlay"></div>
    <div class="container blog-hero__content">
        <span class="mp-eyebrow mp-eyebrow--light mp-reveal" data-delay="0">Health Insights</span>
        <h1 class="blog-hero__title mp-reveal" data-delay="80">Medicare Plus Blog</h1>
        <p class="blog-hero__sub mp-reveal" data-delay="160">
            Expert health advice and clinical insights written by our board-certified specialists.
        </p>
        <!-- Inline search -->
        <form class="blog-hero__search mp-reveal" data-delay="240" method="GET" action="blog.php">
            <input type="search" name="q" placeholder="Search articles…" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-accent">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     FEATURED ARTICLE
═══════════════════════════════════════════════════════════ -->
<?php if ($featuredPost && $page === 1 && !$catFilter && !$search): ?>
    <section class="mp-section mp-section--white" style="padding-bottom:0">
        <div class="container">
            <div class="mp-eyebrow mp-reveal" data-delay="0" style="margin-bottom:16px">Featured Article</div>
            <article class="blog-featured mp-reveal" data-delay="80">
                <a href="blog_post.php?id=<?= (int)$featuredPost['id'] ?>" class="blog-featured__img">
                    <img src="<?= getBlogImage($featuredPost['category'] ?? '', 0, $categoryImages, $fallbackImages) ?>"
                        alt="<?= htmlspecialchars($featuredPost['title']) ?>" loading="lazy">
                    <?php if (!empty($featuredPost['category'])): ?>
                        <span class="mp-blog-card__cat"><?= htmlspecialchars($featuredPost['category']) ?></span>
                    <?php endif; ?>
                </a>
                <div class="blog-featured__body">
                    <div class="mp-blog-card__meta">
                        <span><i class="fas fa-user-pen"></i>
                            Dr. <?= htmlspecialchars($featuredPost['first_name'] . ' ' . $featuredPost['last_name']) ?>
                        </span>
                        <span><i class="fas fa-calendar"></i>
                            <?= date('F j, Y', strtotime($featuredPost['created_at'])) ?>
                        </span>
                        <?php if (!empty($featuredPost['read_time'])): ?>
                            <span><i class="fas fa-clock"></i> <?= (int)$featuredPost['read_time'] ?> min read</span>
                        <?php endif; ?>
                    </div>
                    <h2 class="blog-featured__title">
                        <a href="blog_post.php?id=<?= (int)$featuredPost['id'] ?>">
                            <?= htmlspecialchars($featuredPost['title']) ?>
                        </a>
                    </h2>
                    <p class="blog-featured__excerpt"><?= htmlspecialchars($featuredPost['excerpt'] ?? '') ?></p>
                    <a href="blog_post.php?id=<?= (int)$featuredPost['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-book-open"></i> Read Full Article
                    </a>
                </div>
            </article>
        </div>
    </section>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════
     MAIN CONTENT: FILTERS + GRID
═══════════════════════════════════════════════════════════ -->
<section class="mp-section mp-section--light">
    <div class="container">
        <div class="blog-layout">

            <!-- Posts grid -->
            <main class="blog-main">
                <!-- Active filter bar -->
                <?php if ($catFilter || $search): ?>
                    <div class="blog-filter-bar mp-reveal" data-delay="0">
                        <?php if ($catFilter): ?>
                            <span class="blog-filter-bar__tag">
                                Category: <strong><?= htmlspecialchars($catFilter) ?></strong>
                                <a href="blog.php<?= $search ? '?q=' . urlencode($search) : '' ?>"><i class="fas fa-times"></i></a>
                            </span>
                        <?php endif; ?>
                        <?php if ($search): ?>
                            <span class="blog-filter-bar__tag">
                                Search: <strong><?= htmlspecialchars($search) ?></strong>
                                <a href="blog.php<?= $catFilter ? '?cat=' . urlencode($catFilter) : '' ?>"><i class="fas fa-times"></i></a>
                            </span>
                        <?php endif; ?>
                        <span class="blog-filter-bar__count"><?= $total ?> article<?= $total !== 1 ? 's' : '' ?> found</span>
                    </div>
                <?php endif; ?>

                <?php if (count($posts) > 0): ?>
                    <div class="mp-blog-grid" id="blogGrid">
                        <?php foreach ($posts as $i => $post):
                            $imgUrl = getBlogImage($post['category'] ?? '', $i, $categoryImages, $fallbackImages);
                        ?>
                            <article class="mp-blog-card mp-reveal" data-delay="<?= ($i % 3) * 70 ?>">
                                <a href="blog_post.php?id=<?= (int)$post['id'] ?>" class="mp-blog-card__img-wrap">
                                    <img src="<?= $imgUrl ?>"
                                        alt="<?= htmlspecialchars($post['title']) ?>"
                                        loading="lazy">
                                    <?php if (!empty($post['category'])): ?>
                                        <span class="mp-blog-card__cat"><?= htmlspecialchars($post['category']) ?></span>
                                    <?php endif; ?>
                                </a>
                                <div class="mp-blog-card__body">
                                    <div class="mp-blog-card__meta">
                                        <span>
                                            <i class="fas fa-user-pen"></i>
                                            Dr. <?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            <?= date('M j, Y', strtotime($post['created_at'])) ?>
                                        </span>
                                        <?php if (!empty($post['read_time'])): ?>
                                            <span><i class="fas fa-clock"></i> <?= (int)$post['read_time'] ?> min</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 class="mp-blog-card__title">
                                        <a href="blog_post.php?id=<?= (int)$post['id'] ?>">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h3>
                                    <p class="mp-blog-card__excerpt"><?= htmlspecialchars($post['excerpt'] ?? '') ?></p>
                                    <a href="blog_post.php?id=<?= (int)$post['id'] ?>" class="mp-blog-card__link">
                                        Read article <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="blog-pagination mp-reveal" data-delay="100" aria-label="Blog pagination">
                            <?php
                            $qBase = http_build_query(array_filter(['cat' => $catFilter, 'q' => $search]));
                            $qBase = $qBase ? '&' . $qBase : '';
                            ?>
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?><?= $qBase ?>" class="blog-pagination__btn">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
                                <a href="?page=<?= $p ?><?= $qBase ?>"
                                    class="blog-pagination__btn <?= $p === $page ? 'is-active' : '' ?>">
                                    <?= $p ?>
                                </a>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?><?= $qBase ?>" class="blog-pagination__btn">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="blog-empty mp-reveal" data-delay="0">
                        <i class="fas fa-newspaper"></i>
                        <h3>No articles found</h3>
                        <p>Try a different category or search term.</p>
                        <a href="blog.php" class="btn btn-primary">Clear filters</a>
                    </div>
                <?php endif; ?>
            </main>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <!-- Category filter -->
                <?php if (count($categories) > 0): ?>
                    <div class="blog-widget mp-reveal" data-delay="0">
                        <h4 class="blog-widget__title"><i class="fas fa-tag"></i> Browse by Category</h4>
                        <ul class="blog-widget__cats">
                            <li>
                                <a href="blog.php<?= $search ? '?q=' . urlencode($search) : '' ?>"
                                    class="<?= !$catFilter ? 'is-active' : '' ?>">
                                    All Articles
                                    <span><?= $total ?></span>
                                </a>
                            </li>
                            <?php foreach ($categories as $cat):
                                $catQ = http_build_query(array_filter(['cat' => $cat['category'], 'q' => $search]));
                            ?>
                                <li>
                                    <a href="blog.php?<?= $catQ ?>"
                                        class="<?= $catFilter === $cat['category'] ? 'is-active' : '' ?>">
                                        <?= htmlspecialchars($cat['category']) ?>
                                        <span><?= (int)$cat['cnt'] ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Newsletter widget -->
                <div class="blog-widget blog-widget--teal mp-reveal" data-delay="80">
                    <i class="fas fa-envelope-open-text blog-widget__icon"></i>
                    <h4>Health Tips in Your Inbox</h4>
                    <p>Get our weekly digest of expert health articles — free.</p>
                    <form class="blog-newsletter" onsubmit="handleNewsletterSubmit(event, this)">
                        <input type="email" placeholder="you@example.com" required>
                        <button type="submit" class="btn btn-accent" style="width:100%;justify-content:center">
                            Subscribe
                        </button>
                    </form>
                    <p class="blog-widget__fine">No spam. Unsubscribe any time.</p>
                </div>

                <!-- Book appointment prompt -->
                <div class="blog-widget blog-widget--dark mp-reveal" data-delay="140">
                    <i class="fas fa-stethoscope blog-widget__icon"></i>
                    <h4>Talk to a Specialist</h4>
                    <p>Have a health concern? Book a same-day consultation.</p>
                    <a href="book_appointment.php" class="btn btn-accent" style="width:100%;justify-content:center;margin-top:8px">
                        <i class="fas fa-calendar-check"></i> Book Now
                    </a>
                </div>
            </aside>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════
     STYLES
═══════════════════════════════════════════════════════════ -->
<style>
    /* ── HERO ─────────────────────────────────────────────── */
    .blog-hero {
        position: relative;
        padding: 120px 0 80px;
        overflow: hidden;
        text-align: center;
    }

    .blog-hero__bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: center;
    }

    .blog-hero__overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(160deg, rgba(6, 60, 62, .92) 0%, rgba(13, 115, 119, .82) 60%, rgba(24, 90, 40, .7) 100%);
    }

    .blog-hero__content {
        position: relative;
        z-index: 1;
        max-width: 640px;
        margin: 0 auto;
    }

    .blog-hero__title {
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 800;
        color: #fff;
        margin: 10px 0 14px;
    }

    .blog-hero__sub {
        font-size: 1.05rem;
        color: rgba(255, 255, 255, .75);
        line-height: 1.7;
        margin: 0 0 32px;
    }

    .blog-hero__search {
        display: flex;
        gap: 8px;
        max-width: 460px;
        margin: 0 auto;
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 12px;
        padding: 6px 6px 6px 14px;
        backdrop-filter: blur(8px);
    }

    .blog-hero__search input {
        flex: 1;
        background: transparent;
        border: none;
        outline: none;
        color: #fff;
        font-size: .95rem;
        min-width: 0;
    }

    .blog-hero__search input::placeholder {
        color: rgba(255, 255, 255, .5);
    }

    /* ── FEATURED ─────────────────────────────────────────── */
    .blog-featured {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 32px;
        align-items: center;
        background: #fff;
        border-radius: 20px;
        border: 1.5px solid var(--border, #e4eaec);
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0, 0, 0, .07);
        margin-bottom: 0;
    }

    .blog-featured__img {
        display: block;
        height: 360px;
        overflow: hidden;
        position: relative;
    }

    .blog-featured__img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .5s ease;
    }

    .blog-featured:hover .blog-featured__img img {
        transform: scale(1.05);
    }

    .blog-featured__body {
        padding: 36px 36px 36px 0;
    }

    .blog-featured__title {
        font-family: var(--font-display, 'Poppins', sans-serif);
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--dark, #0b1d1e);
        margin: 12px 0 14px;
        line-height: 1.3;
    }

    .blog-featured__title a {
        color: inherit;
        text-decoration: none;
    }

    .blog-featured__title a:hover {
        color: var(--teal, #0d7373);
    }

    .blog-featured__excerpt {
        color: var(--mid, #4a6163);
        line-height: 1.7;
        margin: 0 0 24px;
        font-size: .93rem;
    }

    /* ── LAYOUT ───────────────────────────────────────────── */
    .blog-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 36px;
        align-items: start;
    }

    .mp-blog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 22px;
    }

    /* ── FILTER BAR ───────────────────────────────────────── */
    .blog-filter-bar {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 24px;
        padding: 12px 18px;
        background: var(--sand, #f7f3ed);
        border-radius: 10px;
        border: 1px solid var(--border, #e4eaec);
    }

    .blog-filter-bar__tag {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--teal, #0d7373);
        color: #fff;
        font-size: .8rem;
        padding: 5px 12px;
        border-radius: 50px;
    }

    .blog-filter-bar__tag a {
        color: rgba(255, 255, 255, .7);
        text-decoration: none;
    }

    .blog-filter-bar__tag a:hover {
        color: #fff;
    }

    .blog-filter-bar__count {
        margin-left: auto;
        font-size: .82rem;
        color: var(--muted, #6b7c80);
    }

    /* ── PAGINATION ───────────────────────────────────────── */
    .blog-pagination {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-top: 40px;
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

    /* ── EMPTY STATE ──────────────────────────────────────── */
    .blog-empty {
        text-align: center;
        padding: 64px 20px;
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

    /* ── SIDEBAR ──────────────────────────────────────────── */
    .blog-sidebar {
        display: flex;
        flex-direction: column;
        gap: 20px;
        position: sticky;
        top: 90px;
    }

    .blog-widget {
        background: #fff;
        border: 1.5px solid var(--border, #e4eaec);
        border-radius: 16px;
        padding: 24px;
    }

    .blog-widget--teal {
        background: linear-gradient(135deg, var(--teal-dark, #063c3e), var(--teal, #0d7373));
        border-color: transparent;
        color: #fff;
    }

    .blog-widget--teal h4 {
        color: #fff;
    }

    .blog-widget--teal p {
        color: rgba(255, 255, 255, .72);
        font-size: .85rem;
    }

    .blog-widget--dark {
        background: var(--dark, #0b1d1e);
        border-color: transparent;
        color: #fff;
    }

    .blog-widget--dark h4 {
        color: #fff;
    }

    .blog-widget--dark p {
        color: rgba(255, 255, 255, .65);
        font-size: .85rem;
    }

    .blog-widget__title {
        font-size: .85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--dark, #0b1d1e);
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .blog-widget__title i {
        color: var(--teal, #0d7373);
    }

    .blog-widget__icon {
        font-size: 1.8rem;
        margin-bottom: 12px;
        display: block;
        color: var(--accent, #f5c518);
    }

    .blog-widget h4 {
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 8px;
    }

    .blog-widget p {
        font-size: .85rem;
        color: var(--muted, #6b7c80);
        margin: 0 0 14px;
    }

    .blog-widget__cats {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .blog-widget__cats li a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        border-radius: 8px;
        text-decoration: none;
        color: var(--dark, #0b1d1e);
        font-size: .88rem;
        transition: background .18s, color .18s;
    }

    .blog-widget__cats li a:hover,
    .blog-widget__cats li a.is-active {
        background: rgba(13, 115, 119, .1);
        color: var(--teal, #0d7373);
        font-weight: 600;
    }

    .blog-widget__cats li a span {
        background: var(--border, #e4eaec);
        color: var(--muted, #6b7c80);
        font-size: .72rem;
        padding: 2px 8px;
        border-radius: 50px;
        font-weight: 600;
    }

    .blog-newsletter {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .blog-newsletter input {
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 8px;
        padding: 10px 14px;
        color: #fff;
        font-size: .9rem;
        outline: none;
    }

    .blog-newsletter input::placeholder {
        color: rgba(255, 255, 255, .45);
    }

    .blog-newsletter input:focus {
        border-color: var(--accent, #f5c518);
    }

    .blog-widget__fine {
        font-size: .72rem !important;
        color: rgba(255, 255, 255, .45) !important;
        margin-top: 8px !important;
    }

    /* ── RESPONSIVE ───────────────────────────────────────── */
    @media (max-width: 900px) {
        .blog-layout {
            grid-template-columns: 1fr;
        }

        .blog-sidebar {
            position: static;
        }

        .blog-featured {
            grid-template-columns: 1fr;
        }

        .blog-featured__img {
            height: 240px;
        }

        .blog-featured__body {
            padding: 24px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .mp-reveal {
            opacity: 1 !important;
            transform: none !important;
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
        threshold: 0.1
    });
    document.querySelectorAll('.mp-reveal').forEach(el => io.observe(el));

    // Newsletter submit
    function handleNewsletterSubmit(e, form) {
        e.preventDefault();
        const btn = form.querySelector('button');
        btn.textContent = '✓ Subscribed!';
        btn.disabled = true;
        btn.style.background = '#27ae60';
        form.querySelector('input').disabled = true;
    }
<?php include 'footer.php'; ?>
