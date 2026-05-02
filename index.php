<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$latestArticles = [];
try {
    $st = $pdo->query(
        "SELECT title, slug, excerpt, featured_image, published_at
         FROM articles
         WHERE status = 'published'
         ORDER BY published_at DESC, id DESC
         LIMIT 3"
    );
    $latestArticles = $st->fetchAll();
} catch (Throwable $e) {
    $latestArticles = [];
}

$charged_page_title = 'CHARGED — Africa fintech & e-mobility intelligence';
$charged_meta_description = 'Weekly intelligence on Africa’s fintech and e-mobility sectors. Published from Dar es Salaam. Where fintech meets the electric road across Africa.';
$charged_nav = 'home';
$charged_extra_head = <<<'HTML'
  <link rel="canonical" href="https://charged.news/">
  <meta property="og:type" content="website">
  <meta property="og:title" content="CHARGED — Africa fintech &amp; e-mobility intelligence">
  <meta property="og:description" content="Weekly intelligence on Africa’s fintech and e-mobility sectors. Published from Dar es Salaam.">
  <meta property="og:url" content="https://charged.news/">
  <meta property="og:image" content="https://charged.news/assets/images/brand/charged-newsletter-graphic.svg">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="CHARGED">
  <meta name="twitter:description" content="Where fintech meets the electric road across Africa.">
  <link rel="apple-touch-icon" href="assets/images/favicon.svg">
HTML;

require_once __DIR__ . '/includes/header.php';
?>

      <section class="ch-hero" aria-labelledby="hero-heading">
        <div class="ch-hero__mesh" aria-hidden="true"></div>
        <div class="ch-hero__glow" aria-hidden="true"></div>
        <div class="ch-container ch-hero__grid">
          <div class="ch-hero__content">
            <p class="ch-kicker ch-kicker--rule">Africa’s fintech &amp; e-mobility record · Dar es Salaam</p>
            <h1 id="hero-heading">Connecting Africa <span class="ch-hero__emph">where money flows and wheels turn.</span></h1>
            <p class="ch-hero__lead">Two editions each week—<em>Mondays and Thursdays.</em> Payments, policy, EV deployment, fleets, and deals in concise analysis for founders, investors, and operators.</p>
            <div class="ch-hero__actions">
              <a class="ch-btn ch-btn--primary" href="#subscribe">Subscribe</a>
              <a class="ch-btn ch-btn--on-dark" href="article.html">Read the inaugural issue</a>
            </div>
            <p class="ch-hero__tagline">Strategy teams, capital allocators, and operators tracking the next infrastructure stack across the continent.</p>
          </div>
          <div class="ch-hero__brief" aria-label="Latest issue">
            <p class="ch-kicker">Issue 001</p>
            <h2>The Continent Is Charging Up. Literally.</h2>
            <p>EV deployment, grid constraints, and the payment infrastructure that determines who scales.</p>
            <a class="ch-link-arrow" href="article.html">Read issue</a>
          </div>
        </div>
      </section>

      <div class="ch-marquee" aria-hidden="true">
        <div class="ch-marquee__track">
          <span>Fintech infrastructure</span>
          <span>E-mobility &amp; fleets</span>
          <span>Policy &amp; markets</span>
          <span>Funding &amp; M&amp;A</span>
          <span>Dar es Salaam desk</span>
          <span>Pan-African lens</span>
          <span>Fintech infrastructure</span>
          <span>E-mobility &amp; fleets</span>
          <span>Policy &amp; markets</span>
          <span>Funding &amp; M&amp;A</span>
          <span>Dar es Salaam desk</span>
          <span>Pan-African lens</span>
        </div>
      </div>

      <section class="ch-section ch-section--tight" aria-labelledby="stats-heading">
        <div class="ch-container">
          <h2 id="stats-heading" class="ch-sr-only">Publication snapshot</h2>
          <div class="ch-stat-bar">
            <div class="ch-stat">
              <span class="ch-stat__value">2×</span>
              <span class="ch-stat__label">Issues per week</span>
            </div>
            <div class="ch-stat">
              <span class="ch-stat__value">4</span>
              <span class="ch-stat__label">Coverage areas</span>
            </div>
            <div class="ch-stat">
              <span class="ch-stat__value">TZ</span>
              <span class="ch-stat__label">Editorial base</span>
            </div>
            <div class="ch-stat">
              <span class="ch-stat__value">Pan-African</span>
              <span class="ch-stat__label">Market lens</span>
            </div>
          </div>
        </div>
      </section>

      <section id="subscribe" class="ch-section ch-section--surface" aria-labelledby="subscribe-heading">
        <div class="ch-container ch-container--narrow">
          <div class="ch-subscribe-block">
            <div class="ch-section-head">
              <span class="ch-eyebrow">Newsletter</span>
              <h2 id="subscribe-heading" class="ch-section-title">Subscribe</h2>
              <p class="ch-section-dek">Receive both weekly editions. Unsubscribe at any time.</p>
            </div>
            <form class="ch-subscribe-form" action="#" method="post" novalidate>
              <div class="ch-form-row">
                <div class="ch-field">
                  <label class="ch-label" for="email-main">Work email</label>
                  <input class="ch-input" type="email" id="email-main" name="email" autocomplete="email" inputmode="email" placeholder="name@company.com" required>
                  <p class="ch-field-error" role="alert"></p>
                </div>
                <div class="ch-field ch-form-actions">
                  <span class="ch-label ch-sr-only">Submit subscription</span>
                  <button class="ch-btn ch-btn--primary ch-btn--submit" type="submit">Subscribe</button>
                </div>
              </div>
              <p class="ch-field-hint">We use your address only for CHARGED. See our <a href="privacy.html">privacy policy</a>.</p>
              <div class="ch-honeypot" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
              </div>
              <p class="ch-form-status" role="status" aria-live="polite"></p>
            </form>
          </div>
        </div>
      </section>

      <section class="ch-section" aria-labelledby="featured-heading">
        <div class="ch-container">
          <header class="ch-section-head">
            <span class="ch-eyebrow">Latest</span>
            <h2 id="featured-heading" class="ch-section-title">Featured issue</h2>
            <p class="ch-section-dek">Issue 001 establishes the editorial frame: financial infrastructure and electric mobility as one operating environment.</p>
          </header>
          <article class="ch-card ch-card--featured">
            <div class="ch-card__visual" style="background-image:url(img/bg-img/6.jpg);" role="img" aria-hidden="true"></div>
            <div>
              <div class="ch-tag-row">
                <span class="ch-badge">Issue 001</span>
                <span class="ch-badge">Deep Signal</span>
              </div>
              <h3 class="ch-card__title"><a href="article.html">The Continent Is Charging Up. Literally.</a></h3>
              <p class="ch-card__excerpt">Payment orchestration, fleet financing, and grid-adjacent policy: what converges first as the market scales.</p>
              <div class="ch-meta-row">
                <time datetime="2026-04-18">18 Apr 2026</time>
                <span>~12 min read</span>
              </div>
              <div class="ch-card__actions">
                <a class="ch-btn ch-btn--primary" href="article.html">Read full issue</a>
              </div>
            </div>
          </article>
          <p class="ch-archive-more"><a href="archive.html">View all issues in the archive</a></p>
        </div>
      </section>

      <section class="ch-section ch-section--surface" aria-labelledby="latest-articles-heading">
        <div class="ch-container">
          <header class="ch-section-head">
            <span class="ch-eyebrow">From the desk</span>
            <h2 id="latest-articles-heading" class="ch-section-title">Latest articles</h2>
            <p class="ch-section-dek">New publishings on the site. <a href="articles.php">Browse all articles</a>.</p>
          </header>
          <?php if ($latestArticles === []) : ?>
          <p class="ch-section-dek">Editorial is being updated—posts will appear here when published.</p>
          <?php else : ?>
          <div class="ch-card-grid ch-card-grid--3">
            <?php foreach ($latestArticles as $a) : ?>
            <article class="ch-card ch-card--issue">
              <?php if (!empty($a['featured_image'])) : ?>
              <div class="ch-card__visual" style="min-height:140px;background-size:cover;background-position:center;background-image:url(<?php echo charged_e($a['featured_image']); ?>);" role="img" aria-label=""></div>
              <?php else : ?>
              <div class="ch-card__visual" style="min-height:140px;background:var(--ch-surface-2);" aria-hidden="true"></div>
              <?php endif; ?>
              <div class="ch-tag-row">
                <span class="ch-badge">Article</span>
              </div>
              <h3 class="ch-card__title"><a href="article.php?slug=<?php echo charged_e($a['slug']); ?>"><?php echo charged_e($a['title']); ?></a></h3>
              <?php if (!empty($a['excerpt'])) : ?>
              <p class="ch-card__excerpt"><?php echo charged_e($a['excerpt']); ?></p>
              <?php endif; ?>
              <div class="ch-card__meta">
                <?php if (!empty($a['published_at'])) : ?>
                <time datetime="<?php echo charged_e(charged_format_date_iso($a['published_at'])); ?>"><?php echo charged_e(charged_format_date($a['published_at'])); ?></time>
                <?php endif; ?>
                <a class="ch-btn ch-btn--primary" href="article.php?slug=<?php echo charged_e($a['slug']); ?>">Read more</a>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </section>

      <section class="ch-section" aria-labelledby="coverage-heading">
        <div class="ch-container">
          <header class="ch-section-head ch-section-head--center">
            <span class="ch-eyebrow">Coverage</span>
            <h2 id="coverage-heading" class="ch-section-title">Focus areas</h2>
            <p class="ch-section-dek">Four lanes—structured for diligence, strategy, and board-ready forwarding.</p>
          </header>
          <div class="ch-card-grid ch-card-grid--4">
            <div class="ch-card ch-card--topic">
              <span class="ch-badge">01</span>
              <h3>E-mobility &amp; clean transport</h3>
              <p>Fleets, charging networks, OEM relationships, and route-level unit economics.</p>
            </div>
            <div class="ch-card ch-card--topic">
              <span class="ch-badge">02</span>
              <h3>Fintech &amp; payments</h3>
              <p>Acquiring, treasury, embedded finance, and settlement—where mobility meets merchant flows.</p>
            </div>
            <div class="ch-card ch-card--topic">
              <span class="ch-badge">03</span>
              <h3>Fundraises &amp; deals</h3>
              <p>Material transactions and what they indicate about risk appetite and sector maturity.</p>
            </div>
            <div class="ch-card ch-card--topic">
              <span class="ch-badge">04</span>
              <h3>Markets &amp; policy</h3>
              <p>Tariffs, licensing, grid access, and cross-border rules that define operating ceilings.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="ch-section" aria-labelledby="schedule-heading">
        <div class="ch-container">
          <header class="ch-section-head">
            <span class="ch-eyebrow">Cadence</span>
            <h2 id="schedule-heading" class="ch-section-title">Schedule</h2>
            <p class="ch-section-dek">Two editions per week: data first, then analysis.</p>
          </header>
          <div class="ch-schedule">
            <div class="ch-schedule__item">
              <p class="ch-schedule__day">Monday</p>
              <h3>The Week in Numbers</h3>
              <p>Flows, tariffs, fleet metrics, and the single chart that best captures the week.</p>
            </div>
            <div class="ch-schedule__item">
              <p class="ch-schedule__day">Thursday</p>
              <h3>Deep Signal</h3>
              <p>One thesis with sourced notes and implications for operators and capital allocators.</p>
            </div>
          </div>
        </div>
      </section>

      <section class="ch-cta-band" aria-labelledby="cta-mid-heading">
        <div class="ch-container ch-cta-band__inner">
          <div>
            <h2 id="cta-mid-heading">For investors, founders, and policy teams</h2>
            <p>CHARGED connects payment infrastructure and mobility deployment—the same stack your counterparties are negotiating.</p>
          </div>
          <a class="ch-btn ch-btn--primary" href="#subscribe">Subscribe</a>
        </div>
      </section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
