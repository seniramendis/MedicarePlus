<?php
$pageTitle = 'Find a Doctor — Medicare Plus';
require_once 'functions.php';
$allDoctors = fetch_all_doctors();

// Filter by specialisation or city from URL params
$filterSpec = trim(filter_input(INPUT_GET, 'spec', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$filterCity = trim(filter_input(INPUT_GET, 'city', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

include 'header.php';
?>

<!-- ══════════════════════ PAGE HERO ════════════════════ -->
<section style="background:linear-gradient(135deg,var(--teal-dark) 0%,var(--teal) 55%,var(--leaf) 100%);padding:72px 0 64px;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background:radial-gradient(circle at 80% 20%,rgba(255,255,255,.06) 0%,transparent 50%),radial-gradient(circle at 10% 80%,rgba(0,0,0,.08) 0%,transparent 40%);pointer-events:none"></div>
    <div class="container" style="position:relative">
        <div style="max-width:620px">
            <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:50px;padding:5px 16px;font-size:.78rem;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.9);margin-bottom:20px">
                <i class="fas fa-stethoscope"></i> Our Medical Team
            </div>
            <h1 style="font-family:var(--font-display);font-size:clamp(2rem,4.5vw,3rem);color:#fff;margin:0 0 14px;line-height:1.15">
                Find Your <span style="color:var(--accent)">Specialist</span>
            </h1>
            <p style="color:rgba(255,255,255,.75);font-size:1.05rem;line-height:1.7;margin:0 0 28px">
                Browse Sri Lanka's top-rated doctors by speciality or location. All consultants are verified and available for online booking.
            </p>
            <div style="display:flex;flex-wrap:wrap;gap:10px">
                <?php
                $chips = [
                    ['fas fa-user-doctor', count($allDoctors) . ' Specialists'],
                    ['fas fa-hospital-alt', '9 Departments'],
                    ['fas fa-star', 'Verified &amp; Rated'],
                    ['fas fa-calendar-check', 'Online Booking'],
                ];
                foreach ($chips as $ch): ?>
                <span style="display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:#fff;font-size:.83rem;font-weight:500;padding:7px 16px;border-radius:30px">
                    <i class="<?= $ch[0] ?>" style="color:var(--accent)"></i> <?= $ch[1] ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════ SEARCH BAR ═══════════════════ -->
<div style="background:var(--white);border-bottom:1px solid var(--border);padding:24px 0;position:sticky;top:68px;z-index:99;box-shadow:var(--shadow-sm)">
    <div class="container">
        <div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap">
            <!-- Name search -->
            <div style="position:relative;flex:1;min-width:220px">
                <i class="fas fa-search" style="position:absolute;left:15px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:.9rem"></i>
                <input type="text" id="searchName" placeholder="Search by doctor's name…"
                    style="width:100%;height:48px;padding:0 16px 0 42px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:var(--font-body);font-size:.93rem;background:var(--sand);transition:border .2s,box-shadow .2s;outline:none"
                    onfocus="this.style.borderColor='var(--teal)';this.style.boxShadow='0 0 0 3px rgba(13,115,119,.1)'"
                    onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'"
                    value="<?= htmlspecialchars(filter_input(INPUT_GET,'q',FILTER_SANITIZE_SPECIAL_CHARS)??'') ?>">
            </div>
            <!-- Specialty filter -->
            <select id="filterSpec" style="height:48px;padding:0 36px 0 14px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:var(--font-body);font-size:.9rem;color:var(--dark);background:var(--sand) url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 20 20%22 fill=%22%237a9394%22%3E%3Cpath d=%22M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z%22/%3E%3C/svg%3E') no-repeat right 10px center/16px;appearance:none;cursor:pointer;min-width:200px">
                <option value="all">All Specialities</option>
                <?php
                $specs = ['Cardiology','Dermatology','Endocrinology','ENT','General Practitioner','Gynaecology','Neurology','Orthopaedics','Paediatrics','Pulmonology'];
                foreach ($specs as $s):
                    $sel = ($filterSpec === $s) ? 'selected' : '';
                ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= $sel ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <!-- City filter -->
            <select id="filterCity" style="height:48px;padding:0 36px 0 14px;border:1.5px solid var(--border);border-radius:var(--radius);font-family:var(--font-body);font-size:.9rem;color:var(--dark);background:var(--sand) url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 20 20%22 fill=%22%237a9394%22%3E%3Cpath d=%22M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z%22/%3E%3C/svg%3E') no-repeat right 10px center/16px;appearance:none;cursor:pointer;min-width:160px">
                <option value="all">All Cities</option>
                <?php
                $cities = ['Colombo','Kandy','Galle','Negombo','Matara','Kurunegala','Jaffna','Trincomalee'];
                foreach ($cities as $c):
                    $sel = ($filterCity === $c) ? 'selected' : '';
                ?>
                <option value="<?= htmlspecialchars($c) ?>" <?= $sel ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- Specialty quick-filter pills -->
<div style="background:var(--white);border-bottom:1px solid var(--border);padding:14px 0">
    <div class="container">
        <div style="display:flex;flex-wrap:wrap;gap:8px" id="pillRow">
            <?php
            $pills = [
                ['all',                 'fas fa-th-large',     'All Doctors'],
                ['Cardiology',          'fas fa-heartbeat',    'Cardiology'],
                ['Paediatrics',         'fas fa-baby',         'Paediatrics'],
                ['Orthopaedics',        'fas fa-bone',         'Orthopaedics'],
                ['Dermatology',         'fas fa-leaf',         'Dermatology'],
                ['Neurology',           'fas fa-brain',        'Neurology'],
                ['General Practitioner','fas fa-stethoscope',  'General'],
                ['Gynaecology',         'fas fa-venus',        'Gynaecology'],
                ['ENT',                 'fas fa-ear-listen',   'ENT'],
                ['Pulmonology',         'fas fa-lungs',        'Pulmonology'],
            ];
            foreach ($pills as [$val, $ico, $label]):
                $active = ($filterSpec === $val || ($val === 'all' && !$filterSpec)) ? 'background:rgba(13,115,119,.1);border-color:var(--teal);color:var(--teal)' : 'background:var(--white);border-color:var(--border);color:var(--muted)';
            ?>
            <button class="spec-pill-btn" data-spec="<?= $val ?>"
                style="display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:30px;border:1.5px solid;font-family:var(--font-body);font-size:.82rem;font-weight:500;cursor:pointer;transition:all .2s;white-space:nowrap;<?= $active ?>">
                <i class="<?= $ico ?>"></i> <?= $label ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ══════════════════════ DOCTOR GRID ══════════════════ -->
<section class="section">
    <div class="container">
        <!-- Results meta -->
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;flex-wrap:wrap;gap:12px">
            <p style="font-size:.9rem;color:var(--muted)">
                Showing <strong id="resultCount" style="color:var(--dark)">0</strong> doctors
            </p>
            <select id="sortSelect" style="height:38px;padding:0 32px 0 12px;border:1.5px solid var(--border);border-radius:10px;font-family:var(--font-body);font-size:.86rem;color:var(--dark);background:var(--white) url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 20 20%22 fill=%22%237a9394%22%3E%3Cpath d=%22M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z%22/%3E%3C/svg%3E') no-repeat right 8px center/16px;appearance:none;cursor:pointer">
                <option value="default">Sort: Default</option>
                <option value="name">Sort: Name A–Z</option>
                <option value="rating">Sort: Highest Rated</option>
                <option value="fee_asc">Sort: Fee Low–High</option>
            </select>
        </div>

        <!-- Cards grid -->
        <div id="doctorGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:28px">
            <?php
            // Sri Lankan specialty colour map
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

            if (!empty($allDoctors)):
                foreach ($allDoctors as $doc):
                    $id      = (int)$doc['id'];
                    $spec    = htmlspecialchars($doc['specialization']);
                    $city    = htmlspecialchars($doc['location'] ?? $doc['city'] ?? 'Sri Lanka');
                    $hosp    = htmlspecialchars($doc['hospital'] ?? '');
                    $rating  = (float)$doc['rating'];
                    $fee     = (float)$doc['consultation_fee'];
                    $exp     = (int)$doc['experience_years'];
                    $name    = htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']);
                    $initials = strtoupper(substr($doc['first_name'],0,1) . substr($doc['last_name'],0,1));
                    $colours  = $specColours[$doc['specialization']] ?? ['var(--teal)','rgba(13,115,119,.08)'];
                    $accentC  = $colours[0];
                    $lightC   = $colours[1];
            ?>
            <div class="doc-card"
                data-name="<?= strtolower($name) ?>"
                data-spec="<?= htmlspecialchars($doc['specialization']) ?>"
                data-city="<?= htmlspecialchars($doc['location'] ?? $doc['city'] ?? '') ?>"
                data-rating="<?= $rating ?>"
                data-fee="<?= $fee ?>"
                style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);overflow:hidden;display:flex;flex-direction:column;transition:transform .25s,box-shadow .25s;position:relative">

                <!-- Specialty accent bar -->
                <div style="height:4px;background:<?= $accentC ?>"></div>

                <!-- Card top -->
                <div style="padding:24px 24px 16px;display:flex;align-items:flex-start;gap:16px">
                    <!-- Avatar -->
                    <div style="width:64px;height:64px;border-radius:16px;background:<?= $lightC ?>;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:1.3rem;font-weight:700;color:<?= $accentC ?>;flex-shrink:0">
                        <?= $initials ?>
                    </div>
                    <div style="flex:1;min-width:0">
                        <h3 style="font-size:1rem;color:var(--dark);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">Dr. <?= $name ?></h3>
                        <div style="display:inline-block;background:<?= $lightC ?>;color:<?= $accentC ?>;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px"><?= $spec ?></div>
                        <?php if ($rating > 0): ?>
                        <div style="display:flex;align-items:center;gap:5px;font-size:.82rem;color:var(--muted)">
                            <span style="color:var(--accent)"><i class="fas fa-star" style="font-size:.7rem"></i></span>
                            <strong style="color:var(--dark)"><?= number_format($rating,1) ?></strong> rating
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card details -->
                <div style="padding:0 24px 16px;flex:1">
                    <div style="display:flex;flex-direction:column;gap:7px;font-size:.85rem;color:var(--muted)">
                        <?php if ($hosp): ?>
                        <div style="display:flex;align-items:center;gap:8px">
                            <i class="fas fa-hospital-alt" style="width:14px;color:var(--teal);flex-shrink:0"></i>
                            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= $hosp ?></span>
                        </div>
                        <?php endif; ?>
                        <div style="display:flex;align-items:center;gap:8px">
                            <i class="fas fa-map-pin" style="width:14px;color:var(--teal);flex-shrink:0"></i>
                            <?= $city ?>
                        </div>
                        <?php if ($exp > 0): ?>
                        <div style="display:flex;align-items:center;gap:8px">
                            <i class="fas fa-award" style="width:14px;color:var(--teal);flex-shrink:0"></i>
                            <?= $exp ?> years experience
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Card footer -->
                <div style="padding:16px 24px 22px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px">
                    <div>
                        <div style="font-size:.75rem;color:var(--muted);margin-bottom:1px">Consultation</div>
                        <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:var(--teal-dark)">LKR <?= number_format($fee,0) ?></div>
                    </div>
                    <div style="display:flex;gap:8px">
                        <a href="doctor-profile.php?id=<?= $id ?>"
                            style="height:36px;padding:0 14px;border-radius:9px;border:1.5px solid var(--border);background:var(--white);color:var(--dark);font-size:.82rem;font-weight:600;display:inline-flex;align-items:center;gap:5px;transition:all .2s;text-decoration:none"
                            onmouseover="this.style.borderColor='var(--teal)';this.style.color='var(--teal)'"
                            onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--dark)'">
                            Profile
                        </a>
                        <a href="book_appointment.php?doctor_id=<?= $id ?>"
                            style="height:36px;padding:0 14px;border-radius:9px;background:linear-gradient(135deg,var(--teal),var(--teal-light));color:#fff;font-size:.82rem;font-weight:600;display:inline-flex;align-items:center;gap:5px;transition:all .2s;text-decoration:none;box-shadow:0 3px 10px rgba(13,115,119,.25)"
                            onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 5px 16px rgba(13,115,119,.35)'"
                            onmouseout="this.style.transform='';this.style.boxShadow='0 3px 10px rgba(13,115,119,.25)'">
                            <i class="fas fa-calendar-plus" style="font-size:.72rem"></i> Book
                        </a>
                    </div>
                </div>
            </div>
            <?php
                endforeach;
            else:
                // Demo fallback with Sri Lankan names
                $demoDoctors = [
                    ['Nimal','Perera','Cardiology','Colombo Teaching Hospital','Colombo',14,4.9,3500],
                    ['Kumudini','Jayasinghe','Paediatrics','Lady Ridgeway Hospital','Colombo',10,4.8,2800],
                    ['Asela','Wickramasinghe','Neurology','National Hospital','Colombo',18,4.7,4200],
                    ['Chamari','Dissanayake','Dermatology','Kandy General Hospital','Kandy',7,4.6,2500],
                    ['Pradeep','Ranasinghe','Orthopaedics','Sirimavo Bandaranaike Hospital','Peradeniya',12,4.8,3800],
                    ['Nilmini','Fernando','Gynaecology','Castle Street Hospital','Colombo',9,4.7,3000],
                    ['Roshan','Jayawardena','General Practitioner','Nawaloka Hospital','Colombo',5,4.5,1800],
                    ['Sumudu','Seneviratne','ENT','Colombo South Teaching Hospital','Kalutara',11,4.6,2200],
                    ['Buddhika','Rathnayake','Pulmonology','Karapitiya Teaching Hospital','Galle',15,4.8,3200],
                    ['Hasini','Marasinghe','Endocrinology','District General Hospital','Kurunegala',8,4.5,2900],
                    ['Tharaka','Amarasinghe','Cardiology','Lanka Hospital','Colombo',20,4.9,5000],
                    ['Dilini','Bandara','Neurology','Teaching Hospital','Kandy',13,4.7,3800],
                ];
                foreach ($demoDoctors as [$first,$last,$spec,$hosp,$city,$exp,$rating,$fee]):
                    $initials = strtoupper($first[0].$last[0]);
                    $colours  = $specColours[$spec] ?? ['var(--teal)','rgba(13,115,119,.08)'];
                    $accentC  = $colours[0];
                    $lightC   = $colours[1];
            ?>
            <div class="doc-card"
                data-name="<?= strtolower("$first $last") ?>"
                data-spec="<?= $spec ?>"
                data-city="<?= $city ?>"
                data-rating="<?= $rating ?>"
                data-fee="<?= $fee ?>"
                style="background:var(--white);border-radius:var(--radius-lg);border:1px solid var(--border);overflow:hidden;display:flex;flex-direction:column;transition:transform .25s,box-shadow .25s;position:relative">
                <div style="height:4px;background:<?= $accentC ?>"></div>
                <div style="padding:24px 24px 16px;display:flex;align-items:flex-start;gap:16px">
                    <div style="width:64px;height:64px;border-radius:16px;background:<?= $lightC ?>;display:flex;align-items:center;justify-content:center;font-family:var(--font-display);font-size:1.3rem;font-weight:700;color:<?= $accentC ?>;flex-shrink:0"><?= $initials ?></div>
                    <div style="flex:1;min-width:0">
                        <h3 style="font-size:1rem;color:var(--dark);margin-bottom:3px">Dr. <?= $first ?> <?= $last ?></h3>
                        <div style="display:inline-block;background:<?= $lightC ?>;color:<?= $accentC ?>;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px"><?= $spec ?></div>
                        <div style="display:flex;align-items:center;gap:5px;font-size:.82rem;color:var(--muted)">
                            <span style="color:var(--accent)"><i class="fas fa-star" style="font-size:.7rem"></i></span>
                            <strong style="color:var(--dark)"><?= $rating ?></strong> rating
                        </div>
                    </div>
                </div>
                <div style="padding:0 24px 16px;flex:1">
                    <div style="display:flex;flex-direction:column;gap:7px;font-size:.85rem;color:var(--muted)">
                        <div style="display:flex;align-items:center;gap:8px"><i class="fas fa-hospital-alt" style="width:14px;color:var(--teal);flex-shrink:0"></i><span><?= $hosp ?></span></div>
                        <div style="display:flex;align-items:center;gap:8px"><i class="fas fa-map-pin" style="width:14px;color:var(--teal);flex-shrink:0"></i><?= $city ?></div>
                        <div style="display:flex;align-items:center;gap:8px"><i class="fas fa-award" style="width:14px;color:var(--teal);flex-shrink:0"></i><?= $exp ?> years experience</div>
                    </div>
                </div>
                <div style="padding:16px 24px 22px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px">
                    <div>
                        <div style="font-size:.75rem;color:var(--muted);margin-bottom:1px">Consultation</div>
                        <div style="font-family:var(--font-display);font-size:1rem;font-weight:700;color:var(--teal-dark)">LKR <?= number_format($fee,0) ?></div>
                    </div>
                    <div style="display:flex;gap:8px">
                        <a href="doctor-profile.php?id=0"
                            style="height:36px;padding:0 14px;border-radius:9px;border:1.5px solid var(--border);background:var(--white);color:var(--dark);font-size:.82rem;font-weight:600;display:inline-flex;align-items:center;text-decoration:none">Profile</a>
                        <a href="book_appointment.php"
                            style="height:36px;padding:0 14px;border-radius:9px;background:linear-gradient(135deg,var(--teal),var(--teal-light));color:#fff;font-size:.82rem;font-weight:600;display:inline-flex;align-items:center;gap:5px;text-decoration:none;box-shadow:0 3px 10px rgba(13,115,119,.25)">
                            <i class="fas fa-calendar-plus" style="font-size:.72rem"></i> Book
                        </a>
                    </div>
                </div>
            </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <!-- No results -->
        <div id="noResults" style="display:none;text-align:center;padding:72px 20px;grid-column:1/-1">
            <i class="fas fa-user-doctor" style="font-size:3rem;color:var(--border);display:block;margin-bottom:16px"></i>
            <h3 style="color:var(--dark);margin-bottom:8px">No doctors found</h3>
            <p style="color:var(--muted)">Try adjusting your search or filter criteria.</p>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
(function(){
    var nameInput  = document.getElementById('searchName');
    var specSelect = document.getElementById('filterSpec');
    var citySelect = document.getElementById('filterCity');
    var sortSelect = document.getElementById('sortSelect');
    var grid       = document.getElementById('doctorGrid');
    var noRes      = document.getElementById('noResults');
    var countEl    = document.getElementById('resultCount');

    function cards() { return Array.from(grid.querySelectorAll('.doc-card')); }

    function filterAll() {
        var nq  = nameInput.value.toLowerCase();
        var sq  = specSelect.value;
        var cq  = citySelect.value;
        var vis = 0;
        cards().forEach(function(c){
            var nm = (c.dataset.name||'').includes(nq);
            var sm = sq === 'all' || c.dataset.spec === sq;
            var cm = cq === 'all' || (c.dataset.city||'').toLowerCase().includes(cq.toLowerCase());
            var show = nm && sm && cm;
            c.style.display = show ? '' : 'none';
            if (show) vis++;
        });
        countEl.textContent = vis;
        noRes.style.display = vis ? 'none' : 'block';
    }

    function sortAll(val) {
        var cs = cards().filter(function(c){ return c.style.display !== 'none'; });
        cs.sort(function(a,b){
            if (val === 'name')    return (a.dataset.name||'').localeCompare(b.dataset.name||'');
            if (val === 'rating')  return parseFloat(b.dataset.rating||0) - parseFloat(a.dataset.rating||0);
            if (val === 'fee_asc') return parseFloat(a.dataset.fee||0)    - parseFloat(b.dataset.fee||0);
            return 0;
        });
        cs.forEach(function(c){ grid.appendChild(c); });
    }

    // Pill buttons
    document.querySelectorAll('.spec-pill-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('.spec-pill-btn').forEach(function(b){
                b.style.cssText = b.style.cssText.replace(/background:[^;]+;/,'background:var(--white);').replace(/border-color:[^;]+;/,'border-color:var(--border);').replace(/color:[^;]+;/,'color:var(--muted);');
                b.style.background='var(--white)'; b.style.borderColor='var(--border)'; b.style.color='var(--muted)';
            });
            this.style.background='rgba(13,115,119,.1)'; this.style.borderColor='var(--teal)'; this.style.color='var(--teal)';
            specSelect.value = this.dataset.spec;
            filterAll();
        });
    });

    nameInput.addEventListener('input', filterAll);
    specSelect.addEventListener('change', filterAll);
    citySelect.addEventListener('change', filterAll);
    sortSelect.addEventListener('change', function(){ sortAll(this.value); });

    // Hover effect
    cards().forEach(function(c){
        c.addEventListener('mouseenter', function(){ this.style.transform='translateY(-5px)'; this.style.boxShadow='var(--shadow-md)'; });
        c.addEventListener('mouseleave', function(){ this.style.transform=''; this.style.boxShadow=''; });
    });

    // Pre-apply URL filters
    <?php if ($filterSpec): ?>
    specSelect.value = '<?= addslashes($filterSpec) ?>';
    <?php endif; ?>
    <?php if ($filterCity): ?>
    citySelect.value = '<?= addslashes($filterCity) ?>';
    <?php endif; ?>

    filterAll();
})();
</script>
