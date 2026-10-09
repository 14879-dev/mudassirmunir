<?php
/**
 * Portfolio OS — Single-Page App (Nova Glass Theme)
 * Matches reference design exactly.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';

startSecureSession();

sendSecurityHeaders();

// ── FETCH DATA ──
Database::ensureCertificatesTable();
$hero       = Database::selectOne("SELECT * FROM hero_content WHERE id = 1");
$taglines   = $hero ? json_decode($hero['taglines'] ?? '[]', true) : ['Game Developer', 'Web Developer', 'Trading Systems Builder'];
$about      = Database::selectOne("SELECT * FROM about_content WHERE id = 1");
$timeline   = $about ? json_decode($about['timeline_items'] ?? '[]', true) : [];
$skills     = Database::select("SELECT * FROM skills ORDER BY category ASC, sort_order ASC");
$groupedSkills = [];
foreach ($skills as $s) { $groupedSkills[$s['category']][] = $s; }
$spokenLangs = Database::select("SELECT * FROM languages WHERE lang_type='spoken' ORDER BY sort_order ASC");
$progLangs   = Database::select("SELECT * FROM languages WHERE lang_type='programming' ORDER BY sort_order ASC");
$projects    = Database::select("SELECT * FROM projects WHERE is_published=1 ORDER BY sort_order ASC, created_at DESC");
$featuredProjects = array_slice($projects, 0, 3);
$certCount   = (int)(Database::selectOne("SELECT COUNT(*) as c FROM certificates WHERE is_published=1")['c'] ?? 0);
$allTags = [];
foreach ($projects as $p) { foreach ((json_decode($p['tags'] ?? '[]', true) ?: []) as $t) { $allTags[$t] = true; } }
$allTags = array_keys($allTags); sort($allTags);
$recentBlogs = Database::select("SELECT id, title, slug, excerpt, cover_image, views, created_at FROM blogs WHERE is_published=1 ORDER BY created_at DESC LIMIT 2");

$pageTitle  = ($hero['full_name'] ?? 'Mudassir') . " — Portfolio";
$pageDesc   = "Software Engineering student, game developer, web developer & co-founder of Hexspire Solutions.";
$activePage = 'home';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════ -->
<!--  HERO SECTION (Editorial Magazine Style)            -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="home" class="hero-section">
  <!-- Dynamic animated ambient mesh background (Original Nova Theme) -->
  <div class="hero-bg-mesh" aria-hidden="true"></div>

  <div class="container hero-container">
    
    <!-- Top Tagline: Just SOFTWARE ENGINEER (Shifted Upwards) -->
    <div class="hero-top-tagline reveal">
      <span class="hero-pill-badge">
        <span class="hero-pill-dot"></span>
        SOFTWARE ENGINEER
      </span>
    </div>

    <!-- Center Stage: Giant Bold Name BEHIND, Crisp Enlarged Portrait in Center, Descriptions on Left, Buttons on Right -->
    <div class="hero-stage reveal delay-100">
      
      <!-- Backdrop Name with Animated Glow (BEHIND portrait, Even Bolder, z-index: 1) -->
      <div class="hero-backdrop-name" aria-hidden="true">
        <span class="name-part name-part--first">MUDASSIR</span>
        <span class="name-part name-part--last">MUNIR</span>
      </div>

      <!-- Main Flanking Wings Content Grid (z-index: 2, No Box Containers) -->
      <div class="hero-flank-grid">

        <!-- LEFT SIDE: Title & Bio Description (Desktop Only, Pushed Down) -->
        <div class="hero-flank-left reveal delay-150">
          <div class="hero-flank-text">
            <h2 class="hero-title-text">
              <?= e($hero['title'] ?? 'Software Engineering Student & Developer') ?>
            </h2>
            <p class="hero-bio-text">
              <?= e($about['bio'] ?? 'I design and build futuristic interfaces and digital experiences that blend clarity, functionality, and innovation. Passionate about clean design, smooth interactions, and details that make a difference.') ?>
            </p>

            <!-- Buttons on Left Side directly under text (Aligned Next to Each Other) -->
            <div class="hero-cta-actions hero-cta-actions--desktop">
              <a href="#projects" class="btn btn--primary btn--lg hero-btn-main">
                <i class="bi bi-grid-fill"></i>
                <span><?= e($hero['cta_primary'] ?? 'VIEW WORK') ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
              <a href="<?= APP_URL ?>/cv-download.php" class="btn btn--ghost btn--lg hero-btn-sub">
                <i class="bi bi-download"></i>
                <span>DOWNLOAD CV</span>
              </a>
            </div>
          </div>
        </div>

        <!-- CENTER: Maximum Enlarged Portrait (Head reaches top of name, Soft Bottom Blend) -->
        <div class="hero-portrait-wrap reveal">
          <?php if (!empty($hero['profile_photo']) && file_exists(UPLOAD_PATH . '/hero/' . $hero['profile_photo'])): ?>
            <img src="<?= APP_URL ?>/uploads/hero/<?= e($hero['profile_photo']) ?>"
                 alt="<?= e($hero['full_name'] ?? 'Mudassir Munir') ?>"
                 class="hero-portrait-img"
                 loading="eager">
          <?php elseif (file_exists(UPLOAD_PATH . '/hero/cf74b767e0ff7d2ab2db01d281e59da7.webp')): ?>
            <img src="<?= APP_URL ?>/uploads/hero/cf74b767e0ff7d2ab2db01d281e59da7.webp"
                 alt="Mudassir Munir"
                 class="hero-portrait-img"
                 loading="eager">
          <?php else: ?>
            <div class="hero-portrait-placeholder">🧑‍💻</div>
          <?php endif; ?>
        </div>

        <!-- RIGHT SIDE: Rich Descriptive Editorial Content (Shown on Mobile before buttons) -->
        <div class="hero-flank-right reveal delay-200">
          <div class="hero-right-text">
            <div class="hero-subheading-badge">
              <span class="hero-bullet"></span> ENGINEERING &amp; VISION
            </div>
            <h3 class="hero-right-title">
              CRAFTING RESILIENT DIGITAL SYSTEMS &amp; INTERFACES
            </h3>
            <p class="hero-right-desc">
              Bridging software architecture with clean aesthetics — developing robust web platforms, real-time systems, and memorable user experiences built for scale and performance.
            </p>
            <div class="hero-right-tags">
              <span class="hero-tag-pill"><i class="bi bi-code-slash"></i> Full-Stack</span>
              <span class="hero-tag-pill"><i class="bi bi-cpu"></i> Systems</span>
              <span class="hero-tag-pill"><i class="bi bi-shield-check"></i> Security</span>
            </div>

            <!-- Mobile Buttons (Placed under right description on mobile) -->
            <div class="hero-cta-actions hero-cta-actions--mobile">
              <a href="#projects" class="btn btn--primary btn--lg hero-btn-main">
                <i class="bi bi-grid-fill"></i>
                <span><?= e($hero['cta_primary'] ?? 'VIEW WORK') ?></span>
                <i class="bi bi-arrow-right"></i>
              </a>
              <a href="<?= APP_URL ?>/cv-download.php" class="btn btn--ghost btn--lg hero-btn-sub">
                <i class="bi bi-download"></i>
                <span>DOWNLOAD CV</span>
              </a>
            </div>
          </div>
        </div>

      </div><!-- /.hero-flank-grid -->

    </div><!-- /.hero-stage -->

  </div><!-- /.hero-container -->
</section>

<!-- ═══════════════════════════════════════════════════ -->
<!--  STATS BAR                                          -->
<!-- ═══════════════════════════════════════════════════ -->
<section class="page-section" style="padding: var(--space-8) 0;" aria-label="Statistics">
  <div class="container">
    <div class="stats-bar reveal">
      <div class="stat-item">
        <div class="stat-icon"><i class="bi bi-briefcase"></i></div>
        <div class="stat-number">2+</div>
        <div class="stat-label">Years of<br>Experience</div>
      </div>
      <div class="stat-item">
        <div class="stat-icon"><i class="bi bi-folder-check"></i></div>
        <div class="stat-number"><?= count($projects) > 0 ? count($projects) . '+' : '7+' ?></div>
        <div class="stat-label">Projects<br>Completed</div>
      </div>
      <div class="stat-item">
        <div class="stat-icon"><i class="bi bi-people"></i></div>
        <div class="stat-number">5+</div>
        <div class="stat-label">Happy<br>Clients</div>
      </div>
      <a href="<?= APP_URL ?>/certificates.php" class="stat-item stat-item--link" title="Click to view all Awards & Certificates">
        <div class="stat-icon"><i class="bi bi-award-fill" style="color:#f59e0b;"></i></div>
        <div class="stat-number"><?= $certCount ?></div>
        <div class="stat-label">Awards &amp;<br>Certificates <i class="bi bi-arrow-up-right text-xs" style="opacity:0.8;"></i></div>
      </a>
      <div class="stat-quote">
        <blockquote>
          "Design is not just what it looks like and feels like. Design is how it works."
        </blockquote>
        <cite>— Steve Jobs</cite>
      </div>
    </div>
  </div>
</section>


<!-- ═══════════════════════════════════════════════════ -->
<!--  ABOUT / EDUCATION + SKILLS + SERVICES              -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="about" class="page-section" aria-labelledby="about-heading">
  <div class="container">
    <div class="skills-pane">

      <!-- COL 1: Education & Skills -->
      <div class="reveal from-left">
        <div class="sec-head mb-6">
          <div class="sec-head__left">
            <div class="sec-head__icon"><i class="bi bi-mortarboard-fill"></i></div>
            <span class="sec-head__title">Education &amp; Skills</span>
          </div>
        </div>

        <!-- Education & Timeline -->
        <div style="margin-bottom:var(--space-6);">
          <div class="text-xs font-semibold uppercase text-accent mb-3" style="letter-spacing:.1em;">Education &amp; Experience</div>
          <?php if (!empty($timeline)): ?>
            <div class="timeline">
              <?php foreach ($timeline as $tItem): ?>
                <div class="timeline-item">
                  <div class="timeline-year"><?= e($tItem['year'] ?? '') ?></div>
                  <div class="timeline-title"><?= e($tItem['event'] ?? '') ?></div>
                  <?php if (!empty($tItem['institution'])): ?>
                    <div class="timeline-sub"><?= e($tItem['institution']) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($tItem['description'])): ?>
                    <div class="text-xs text-muted mt-1"><?= e($tItem['description']) ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php elseif (!empty($about['education_degree'])): ?>
            <div class="timeline">
              <div class="timeline-item">
                <div class="timeline-year"><?= e($about['education_years'] ?? '2022 – Present') ?></div>
                <div class="timeline-title"><?= e($about['education_degree']) ?></div>
                <div class="timeline-sub"><?= e($about['education_institution'] ?? '') ?></div>
              </div>
            </div>
          <?php else: ?>
            <div class="timeline">
              <div class="timeline-item">
                <div class="timeline-year">2023 – Present</div>
                <div class="timeline-title">BS Software Engineering</div>
                <div class="timeline-sub">CUSIT Peshawar</div>
              </div>
              <div class="timeline-item">
                <div class="timeline-year">2023</div>
                <div class="timeline-title">Co-Founder, Hexspire Solutions</div>
                <div class="timeline-sub">Web Studio</div>
              </div>
              <div class="timeline-item">
                <div class="timeline-year">2021 – 2023</div>
                <div class="timeline-title">F.Sc Pre-medical</div>
                <div class="timeline-sub">Government College Peshawar</div>
              </div>
              <div class="timeline-item">
                <div class="timeline-year">2021</div>
                <div class="timeline-title">Matriculation</div>
                <div class="timeline-sub">PMS Branch Boys V</div>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <!-- Skills pills (tags) -->
        <div class="text-xs font-semibold uppercase text-accent mb-3" style="letter-spacing:.1em;">Skills</div>
        <div style="display:flex;flex-wrap:wrap;gap:var(--space-2);">
          <?php if (!empty($skills)): ?>
            <?php foreach ($skills as $sk): ?>
              <span class="tag tag--neutral"><?= e($sk['name']) ?></span>
            <?php endforeach; ?>
          <?php else: ?>
            <?php foreach (['Unity','C#','PHP','MySQL','React','Web Design','Game Dev','Python','Git','Figma','JavaScript','HTML/CSS'] as $sk): ?>
              <span class="tag tag--neutral"><?= $sk ?></span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- COL 2: Skill Bars + Tech Stack -->
      <div class="reveal delay-100">
        <div class="sec-head mb-6">
          <div class="sec-head__left">
            <div class="sec-head__icon"><i class="bi bi-cpu-fill"></i></div>
            <span class="sec-head__title">Tech Stack</span>
          </div>
        </div>

       

        <!-- Tech icons grid -->
        <div class="tech-grid mt-6">
          <?php
          $techItems = [
            
            ['icon'=>'devicon-cplusplus-plain',      'label'=>'C++'],
            ['icon'=>'devicon-javascript-plain',   'label'=>'JS'],
            ['icon'=>'devicon-react-original',    'label'=>'React'],
            ['icon'=>'devicon-nodejs-plain',       'label'=>'Node.js'],
            ['icon'=>'devicon-flutter-plain',       'label'=>'Flutter'],
            ['icon'=>'devicon-python-plain',       'label'=>'Python'],
            ['icon'=>'devicon-html5-plain',          'label'=>'HTML'],
            ['icon'=>'devicon-css3-plain',          'label'=>'CSS'],
              ['icon'=>'devicon-bootstrap-plain',          'label'=>'Bootstrap'],
            ['icon'=>'devicon-php-plain',          'label'=>'PHP'],
            ['icon'=>'devicon-mysql-plain',        'label'=>'MySQL'],
            ['icon'=>'devicon-unity-original',   'label'=>'Unity'],
            ['icon'=>'devicon-csharp-plain',      'label'=>'C#'],
            ['icon'=>'devicon-git-plain',          'label'=>'Git'],
          ];
          foreach ($techItems as $ti): ?>
            <div class="tech-item">
              <i class="<?= $ti['icon'] ?> colored" style="font-size:2rem;"></i>
              <span><?= $ti['label'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- COL 3: Design Philosophy / Services -->
      <div id="services" class="reveal delay-200">
        <div class="sec-head mb-6">
          <div class="sec-head__left">
            <div class="sec-head__icon"><i class="bi bi-compass"></i></div>
            <span class="sec-head__title">Design Philosophy</span>
          </div>
        </div>

        <!-- Philosophy wheel -->
        <div class="philosophy-wheel mb-6" style="height:220px; position:relative; display:flex; align-items:center; justify-content:center;">
          <div class="philosophy-ring" style="width:200px;height:200px;"></div>
          <div class="philosophy-ring" style="width:140px;height:140px;"></div>
          <div class="philosophy-center">💎</div>
          <div style="position:absolute;top:8px;left:50%;transform:translateX(-50%);font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);white-space:nowrap;">Human Centered</div>
          <div style="position:absolute;bottom:8px;left:8px;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);">Purposeful Design</div>
          <div style="position:absolute;bottom:8px;right:8px;font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);">Clean Aesthetics</div>
          <div style="position:absolute;bottom:30px;left:50%;transform:translateX(-50%);font-size:var(--text-xs);font-weight:600;color:var(--color-text-muted);white-space:nowrap;">Impactful Experiences</div>
        </div>

        <!-- Service list -->
        <?php
        $services = [
          ['icon'=>'bi-controller', 'title'=>'Game Development', 'desc'=>'Immersive 2D/3D Unity games with engaging mechanics.'],
          ['icon'=>'bi-globe2',     'title'=>'Web Development',  'desc'=>'Full-stack apps with React, PHP & MySQL.'],
          ['icon'=>'bi-phone',      'title'=>'Mobile Apps',      'desc'=>'Cross-platform apps for Android & iOS.'],
        ];
        foreach ($services as $svc): ?>
          <div class="glass-card glass-card--sm mb-3 flex gap-3 items-center" style="padding:var(--space-3) var(--space-4);">
            <div style="width:32px;height:32px;border-radius:8px;background:var(--color-accent-soft);border:1px solid var(--color-border-accent);display:flex;align-items:center;justify-content:center;color:var(--color-accent);flex-shrink:0;">
              <i class="bi <?= $svc['icon'] ?>"></i>
            </div>
            <div>
              <div style="font-size:var(--text-sm);font-weight:700;color:var(--color-text-primary);margin-bottom:1px;"><?= $svc['title'] ?></div>
              <div style="font-size:var(--text-xs);color:var(--color-text-muted);"><?= $svc['desc'] ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div><!-- /.skills-pane -->
  </div>
</section>

<!-- ═══════════════════════════════════════════════════ -->
<!--  FEATURED PROJECTS                                  -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="projects" class="page-section" aria-labelledby="projects-heading">
  <div class="container">
    <div class="sec-head reveal">
      <div class="sec-head__left">
        <div class="sec-head__icon"><i class="bi bi-lightning-fill"></i></div>
        <span class="sec-head__title">Featured Case Studies</span>
      </div>
      <a href="<?= APP_URL ?>/projects.php" class="sec-head__action">
        VIEW ALL PROJECTS (<?= count($projects) ?>) <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>

    <?php if (!empty($allTags)): ?>
      <div class="filter-bar reveal delay-100">
        <button class="filter-btn active" data-tag="all">All</button>
        <?php foreach ($allTags as $tag): ?>
          <button class="filter-btn" data-tag="<?= e($tag) ?>"><?= e($tag) ?></button>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if (empty($projects)): ?>
      <!-- Empty state: Show 3 sample placeholders -->
      <div class="projects-grid">
        <?php $samples = [
          ['num'=>'01','title'=>'Stellar Command','cat'=>'UI/UX Design · Dashboard','icon'=>'bi-joystick','color'=>'linear-gradient(135deg,#0f1923 0%,#1a2f48 100%)'],
          ['num'=>'02','title'=>'Quantum Drive','cat'=>'Web Design · Interaction','icon'=>'bi-car-front','color'=>'linear-gradient(135deg,#e8f4ff 0%,#c0d8f0 100%)'],
          ['num'=>'03','title'=>'Zenith App','cat'=>'Mobile App · UX Research','icon'=>'bi-phone','color'=>'linear-gradient(135deg,#0a1628 0%,#1a2d50 100%)'],
        ]; foreach ($samples as $i => $s): ?>
          <div class="project-card reveal delay-<?= ($i+1)*100 ?>">
            <div class="project-card__thumb" style="background:<?= $s['color'] ?>;">
              <div class="project-card__thumb--placeholder" style="background:transparent;color:rgba(255,255,255,0.4);">
                <i class="bi <?= $s['icon'] ?>" style="font-size:3.5rem;"></i>
              </div>
              <div class="project-card__num"><?= $s['num'] ?></div>
            </div>
            <div class="project-card__body">
              <div class="project-card__title"><?= $s['title'] ?></div>
              <div class="project-card__desc">Add your projects via the admin panel to showcase them here.</div>
              <div class="project-card__tags">
                <?php foreach (explode('·', $s['cat']) as $t): ?>
                  <span class="tag tag--neutral"><?= trim($t) ?></span>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <div class="projects-grid" id="projects-grid">
        <?php foreach ($featuredProjects as $i => $project):
          $tags = json_decode($project['tags'] ?? '[]', true) ?: [];
          $num  = str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT);
        ?>
          <div class="project-card-wrapper reveal delay-<?= ($i % 6 + 1) * 100 ?>"
               data-tags="<?= htmlspecialchars(json_encode($tags), ENT_QUOTES, 'UTF-8') ?>"
               onclick="window.location='<?= APP_URL ?>/project-detail.php?slug=<?= urlencode($project['slug']) ?>'">
            <div class="project-card" style="cursor:pointer;">
              <div class="project-card__thumb">
                <?php if (!empty($project['thumbnail'])): ?>
                  <img src="<?= APP_URL ?>/uploads/projects/<?= e($project['thumbnail']) ?>" alt="<?= e($project['title']) ?>" loading="lazy">
                <?php else: ?>
                  <div class="project-card__thumb--placeholder">
                    <i class="bi bi-image" style="font-size:3rem; color:rgba(91,142,240,.4);"></i>
                  </div>
                <?php endif; ?>
                <div class="project-card__num"><?= $num ?></div>
              </div>
              <div class="project-card__body">
                <div class="project-card__title"><?= e($project['title']) ?></div>
                <div class="project-card__desc"><?= e($project['short_description'] ?? '') ?></div>
                <div class="project-card__tags">
                  <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                    <span class="tag tag--neutral"><?= e($tag) ?></span>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="no-results" class="text-center text-muted mt-8" style="display:none;">
        <p>No projects found.</p>
      </div>

      <!-- View All Projects Button -->
      <div class="flex-center mt-8 reveal">
        <a href="<?= APP_URL ?>/projects.php" class="btn btn--primary btn--lg" style="padding: 14px 34px; font-size: var(--text-base); box-shadow: 0 10px 30px rgba(91,142,240,0.3);">
          <i class="bi bi-grid-3x3-gap-fill me-2"></i>
          VIEW ALL PROJECTS (<?= count($projects) ?>)
          <i class="bi bi-arrow-right ms-2"></i>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════ -->
<!--  BLOGS SECTION                                      -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="blog" class="page-section" aria-labelledby="blog-heading">
  <div class="container">
    <div class="section-header reveal">
      <div class="overline">THOUGHTS &amp; INSIGHTS</div>
      <h2 class="section-title" id="blog-heading">RECENT <span style="background:linear-gradient(135deg,#7bb3ff 0%,#3a6dd8 25%,#ffffff 50%,#3a6dd8 75%,#1d4ed8 100%);background-size:200% auto;-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;animation:shine 5s linear infinite;">BLOGS</span></h2>
      <p class="section-sub">Writing about code, design, and building things.</p>
    </div>

    <?php if (empty($recentBlogs)): ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-journal-x" style="font-size:3rem;opacity:.3;"></i>
        <p class="mt-3">No blog posts yet — check back soon!</p>
      </div>
    <?php else: ?>
      <div class="grid-2 reveal" style="gap:var(--space-6);">
        <?php foreach ($recentBlogs as $b): ?>
          <a href="<?= APP_URL ?>/blog.php?slug=<?= e($b['slug']) ?>" class="blog-card" style="text-decoration:none;">
            <?php if (!empty($b['cover_image'])): ?>
              <div class="blog-card__img">
                <img src="<?= APP_URL ?>/uploads/blogs/<?= e($b['cover_image']) ?>" alt="<?= e($b['title']) ?>" loading="lazy">
              </div>
            <?php else: ?>
              <div class="blog-card__img blog-card__img--placeholder">
                <i class="bi bi-journal-richtext"></i>
              </div>
            <?php endif; ?>
            <div class="blog-card__body">
              <div class="blog-card__meta">
                <span><i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($b['created_at'])) ?></span>
                <span><i class="bi bi-eye"></i> <?= e($b['views']) ?> views</span>
              </div>
              <h3 class="blog-card__title"><?= e($b['title']) ?></h3>
              <p class="blog-card__excerpt"><?= e(mb_substr($b['excerpt'] ?? '', 0, 120)) ?><?= strlen($b['excerpt'] ?? '') > 120 ? '…' : '' ?></p>
              <span class="blog-card__read">Read more <i class="bi bi-arrow-right"></i></span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="text-center mt-8 reveal">
        <a href="<?= APP_URL ?>/blogs.php" class="btn btn--ghost btn--lg">
          <i class="bi bi-journal-text"></i> View All Blogs <i class="bi bi-arrow-right"></i>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ═══════════════════════════════════════════════════ -->
<!--  CONTACT SECTION                                    -->
<!-- ═══════════════════════════════════════════════════ -->
<section id="contact" class="page-section" aria-labelledby="contact-heading">
  <div class="container">
    <div class="contact-layout">

      <!-- LEFT: Heading + Form -->
      <div class="reveal from-left" style="grid-column: span 1;">
        <h2 class="contact-heading" id="contact-heading">
          LET'S BUILD<br>SOMETHING<br>EXTRAORDINARY
        </h2>
        <p class="mb-6" style="font-size:var(--text-sm);">
          Have a project in mind or want to collaborate? I'm always open to new opportunities.
        </p>
        <a href="#contact" class="btn btn--primary mb-8">
          <i class="bi bi-send"></i> GET IN TOUCH
          <i class="bi bi-arrow-right"></i>
        </a>

        <!-- Social row -->
        <div style="display:flex; gap:var(--space-2); margin-top:var(--space-4);">
          <a href="https://linkedin.com/in/mudassir-munir-794785393" target="_blank" rel="noopener" class="btn btn--ghost btn--sm btn--circle" aria-label="LinkedIn" title="LinkedIn">
            <i class="bi bi-linkedin"></i>
          </a>
          <a href="https://instagram.com" target="_blank" rel="noopener" class="btn btn--ghost btn--sm btn--circle" aria-label="Instagram" title="Instagram">
            <i class="bi bi-instagram"></i>
          </a>
          <a href="https://github.com/14879-dev" target="_blank" rel="noopener" class="btn btn--ghost btn--sm btn--circle" aria-label="GitHub" title="GitHub">
            <i class="bi bi-github"></i>
          </a>
        </div>
      </div>

      <!-- MIDDLE: Contact Info + Form -->
      <div class="reveal delay-100">
        <div class="glass-card">
          <div class="text-xs font-semibold uppercase mb-5" style="letter-spacing:.1em; color:var(--color-text-muted);">CONTACT</div>

          <div class="contact-item">
            <div class="contact-icon"><i class="bi bi-envelope"></i></div>
            <div class="contact-text">mudassirmunir.dev@gmail.com</div>
          </div>
          <div class="contact-item">
            <div class="contact-icon"><i class="bi bi-telephone"></i></div>
            <div class="contact-text">+92 333-7252810</div>
          </div>
          <div class="contact-item">
            <div class="contact-icon"><i class="bi bi-geo-alt"></i></div>
            <div class="contact-text">Peshawar, Pakistan</div>
          </div>
          <div class="contact-item">
            <div class="contact-icon"><i class="bi bi-globe2"></i></div>
            <div class="contact-text">hexspire.site</div>
          </div>

          <div class="neu-divider"></div>

          <!-- Quick contact form -->
          <form id="contact-form" action="<?= APP_URL ?>/api/messages/create.php" method="POST" novalidate>
            <?= csrfField() ?>
            <div style="display:none;"><input type="text" name="website" tabindex="-1"></div>
            <div class="grid-2" style="gap:var(--space-3); margin-bottom:var(--space-3);">
              <div class="neu-input-group" style="margin-bottom:0;">
                <input type="text" id="name" name="name" class="neu-input" placeholder=" " required maxlength="255">
                <label for="name" class="neu-label">Your Name</label>
              </div>
              <div class="neu-input-group" style="margin-bottom:0;">
                <input type="email" id="email" name="email" class="neu-input" placeholder=" " required maxlength="255">
                <label for="email" class="neu-label">Email</label>
              </div>
            </div>
            <div class="neu-input-group">
              <input type="text" id="subject" name="subject" class="neu-input" placeholder=" " required maxlength="255">
              <label for="subject" class="neu-label">Subject</label>
            </div>
            <div class="neu-input-group">
              <textarea id="message" name="message" class="neu-input neu-textarea" placeholder=" " required minlength="10" maxlength="3000"></textarea>
              <label for="message" class="neu-label">Message</label>
            </div>
            <div id="contact-feedback" class="neu-alert mb-4" style="display:none;"></div>
            <button type="submit" id="contact-submit" class="btn btn--primary w-full">
              <i class="bi bi-send"></i> SEND MESSAGE
            </button>
          </form>
        </div>
      </div>

    </div><!-- /.contact-layout -->
  </div>
</section>

<?php
$extraScripts = '
<script src="' . APP_URL . '/assets/js/projects.js" defer></script>
<script src="' . APP_URL . '/assets/js/contact.js" defer></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  // Animate skill bars on scroll
  const barObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const fill = e.target;
        fill.style.width = fill.dataset.width;
        barObserver.unobserve(fill);
      }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll(".skill-bar__fill").forEach(el => barObserver.observe(el));

  // Also animate neu-progress__fill bars
  const progObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const fill = e.target;
        fill.style.width = fill.dataset.width;
        progObserver.unobserve(fill);
      }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll(".neu-progress__fill").forEach(el => progObserver.observe(el));
});
</script>
';
require_once __DIR__ . '/includes/footer.php';
?>
