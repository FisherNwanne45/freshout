<?php
require __DIR__ . '/bootstrap.php';
$isContactPage = strcasecmp($currentPage, 'Contact-Us.php') === 0;
$isLocationsPage = strcasecmp($currentPage, 'Locations.php') === 0;

$contactForm = [
  'department' => 'General Inquiry',
  'name' => '',
  'email' => '',
  'phone' => '',
  'comments' => '',
];
$contactErrors = [];
$contactSuccess = '';

$branchList = [];
try {
  $branchRes = $conn->query("SELECT branch_name, address, phone FROM site_branches WHERE is_active = 1 ORDER BY sort_order, id");
  if ($branchRes) {
    while ($b = $branchRes->fetch_assoc()) {
      $branchList[] = [
        'name' => (string)($b['branch_name'] ?? ''),
        'address' => (string)($b['address'] ?? ''),
        'phone' => (string)($b['phone'] ?? ''),
      ];
    }
  }
} catch (Throwable $e) {
}

$contactBranch = null;
if (!empty($branchList)) {
  $contactBranch = $branchList[0];
}

if ($isContactPage && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['theme2_contact_submit'])) {
  $contactForm['department'] = trim((string)($_POST['department'] ?? 'General Inquiry'));
  $contactForm['name'] = trim((string)($_POST['name'] ?? ''));
  $contactForm['email'] = trim((string)($_POST['email'] ?? ''));
  $contactForm['phone'] = trim((string)($_POST['phone'] ?? ''));
  $contactForm['comments'] = trim((string)($_POST['comments'] ?? ''));

  $allowedDepartments = ['General Inquiry', 'Customer Service', 'Online Banking', 'Marketing', 'Loan Services', 'Card Services'];
  if (!in_array($contactForm['department'], $allowedDepartments, true)) {
    $contactForm['department'] = 'General Inquiry';
  }

  if ($contactForm['name'] === '') {
    $contactErrors[] = 'Please enter your full name.';
  }
  if ($contactForm['email'] === '' || !filter_var($contactForm['email'], FILTER_VALIDATE_EMAIL)) {
    $contactErrors[] = 'Please provide a valid email address.';
  }
  if ($contactForm['comments'] === '') {
    $contactErrors[] = 'Please include your message.';
  }

  if (empty($contactErrors)) {
    $recipient = filter_var((string)($site['email'] ?? ''), FILTER_VALIDATE_EMAIL) ? (string)$site['email'] : '';

    if ($recipient === '') {
      $contactErrors[] = 'Contact recipient is not configured. Please set site email in admin settings.';
    } else {
      try {
        require_once dirname(__DIR__, 3) . '/user/class.user.php';
        $mailer = new user();
        
        $templateData = [
          'department' => $contactForm['department'],
          'name' => $contactForm['name'],
          'email' => $contactForm['email'],
          'phone' => $contactForm['phone'],
          'comments' => $contactForm['comments'],
        ];
        
        $subject = 'Website Contact: ' . $contactForm['department'];
        $flg = $mailer->send_mail($recipient, '', $subject, 'contact_form_submission', $templateData);

        if ($flg) {
          $contactSuccess = 'Thank you. Your message has been sent successfully.';
          $contactForm = [
            'department' => 'General Inquiry',
            'name' => '',
            'email' => '',
            'phone' => '',
            'comments' => '',
          ];
        } else {
          $contactErrors[] = 'We could not send your message right now. Please try again shortly.';
          error_log('Theme2 contact form send_mail failed.');
        }
      } catch (Throwable $e) {
        $contactErrors[] = 'We could not send your message right now. Please try again shortly.';
        error_log('Theme2 contact form exception: ' . $e->getMessage());
      }
    }
  }
}

require __DIR__ . '/header.php';
$sourcePage = $sourcePage ?? basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
$sourcePath = dirname(__DIR__, 2) . '/theme1/' . $sourcePage;
$middle = $isHomePage ? '' : theme1_render_middle($sourcePath);
?>
<?php if ($isHomePage): ?>
<main class="pb-14 md:pb-16">
  <section class="pt-0">
    <div class="relative overflow-hidden bg-slate-950 shadow-editorial">
      <div class="home-slider h-[560px] md:h-[640px] lg:h-[700px]" data-home-slider>
          <?php foreach ($homeSlides as $index => $slide): ?>
            <article class="home-slide <?= $index === 0 ? 'is-active' : '' ?>" data-home-slide>
              <img src="<?= htmlspecialchars(theme_asset_url((string)$slide['image'])) ?>" alt="<?= htmlspecialchars((string)$slide['eyebrow']) ?>" class="h-full w-full object-cover">
              <div class="absolute inset-0 z-10 flex items-end">
                <div class="w-full px-6 py-8 md:px-10 md:py-10 lg:px-14 lg:py-14">
                  <div class="max-w-3xl">
                    <p class="text-[11px] uppercase tracking-[0.26em] text-white/70"><?= htmlspecialchars((string)$slide['eyebrow']) ?></p>
                    <h1 class="mt-4 font-display text-4xl leading-tight text-white md:text-6xl"><?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?></h1>
                    <p class="home-slide-description mt-5 max-w-2xl text-sm leading-7 md:text-base" style="color:#ffffff"><?= htmlspecialchars((string)$slide['text']) ?></p>
                    <div class="mt-7 flex flex-wrap gap-3">
                      <a href="<?= htmlspecialchars(theme_page_url((string)$slide['primary']['href'])) ?>" class="inline-flex rounded-full bg-white px-5 py-3 text-sm font-semibold" style="color:var(--primary2)"><?= htmlspecialchars((string)$slide['primary']['label']) ?></a>
                      <a href="<?= htmlspecialchars(theme_page_url((string)$slide['secondary']['href'])) ?>" class="inline-flex rounded-full border border-white/18 px-5 py-3 text-sm font-semibold text-white"><?= htmlspecialchars((string)$slide['secondary']['label']) ?></a>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          <?php endforeach; ?>

          <div class="absolute bottom-6 right-6 z-20 flex gap-2 md:bottom-8 md:right-10">
            <?php foreach ($homeSlides as $index => $slide): ?>
              <button type="button" class="home-slider-dot <?= $index === 0 ? 'is-active' : '' ?>" data-home-slider-dot data-slide-index="<?= $index ?>" aria-label="Go to slide <?= $index + 1 ?>"></button>
            <?php endforeach; ?>
          </div>
      </div>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-4 pt-8 md:pt-10">
    <div class="grid gap-5 lg:grid-cols-3">
      <?php foreach ($homePrimaryCards as $card): ?>
        <article class="surface-card overflow-hidden rounded-[1.8rem]">
          <img src="<?= htmlspecialchars(theme_asset_url((string)$card['image'])) ?>" alt="<?= htmlspecialchars((string)$card['title']) ?>" class="h-56 w-full object-cover">
          <div class="p-6 md:p-7">
            <p class="section-kicker"><?= htmlspecialchars((string)$card['title']) ?></p>
            <p class="mt-3 text-xl font-semibold leading-8" style="color:var(--primary2)"><?= htmlspecialchars((string)$card['text']) ?></p>
            <a href="<?= htmlspecialchars(theme_page_url((string)$card['href'])) ?>" class="inline-flex mt-6 rounded-full px-4 py-2.5 text-sm font-semibold text-white" style="background:linear-gradient(135deg, var(--primary), var(--primary2))">
              <?= htmlspecialchars((string)$card['label']) ?>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-4 pt-8 md:pt-10">
    <div class="surface-card rounded-[1.9rem] p-6 md:p-8">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <p class="section-kicker">Quick Pathways</p>
          <h2 class="mt-2 font-display text-3xl leading-tight" style="color:var(--primary2)">Find what you need faster.</h2>
        </div>
        <p class="max-w-2xl text-sm leading-7" style="color:var(--muted)">Navigate directly to frequently requested personal, business, digital, and support actions without searching through multiple pages.</p>
      </div>

      <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($homeQuickActions as $item): ?>
          <a href="<?= htmlspecialchars(theme_page_url((string)$item['href'])) ?>" class="block rounded-2xl border p-4 transition hover:shadow-panel" style="border-color:var(--line); background:color-mix(in srgb, var(--bg) 38%, white)">
            <p class="text-sm font-semibold" style="color:var(--primary2)"><?= htmlspecialchars((string)$item['title']) ?></p>
            <p class="mt-2 text-sm leading-7" style="color:var(--muted)"><?= htmlspecialchars((string)$item['text']) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-4 pt-8 md:pt-10">
    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.4fr)_minmax(0,0.9fr)]">
      <div class="grid gap-5 md:grid-cols-3">
        <?php foreach ($homeFeatureCards as $card): ?>
          <article class="surface-card overflow-hidden rounded-[1.7rem]">
            <div class="feature-art feature-art-<?= htmlspecialchars((string)($card['art'] ?? 'card')) ?>">
              <span class="feature-art-chip"><?= htmlspecialchars((string)($card['badge'] ?? 'Service')) ?></span>
            </div>
            <div class="p-6">
              <h2 class="text-lg font-semibold leading-7" style="color:var(--primary2)"><?= htmlspecialchars((string)$card['title']) ?></h2>
              <p class="mt-3 text-sm leading-7" style="color:var(--muted)"><?= htmlspecialchars((string)$card['text']) ?></p>
              <a href="<?= htmlspecialchars(theme_page_url((string)$card['href'])) ?>" class="inline-flex mt-5 text-sm font-semibold" style="color:var(--primary)"><?= htmlspecialchars((string)$card['label']) ?></a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <article class="overflow-hidden rounded-[1.9rem] text-white shadow-editorial" style="background:linear-gradient(135deg, #0c2037 0%, var(--primary2) 46%, var(--primary) 100%)">
        <div class="p-7 md:p-8">
          <p class="text-[11px] uppercase tracking-[0.24em] text-white/68">Institutional Updates</p>
          <h2 class="mt-3 font-display text-3xl leading-tight">Current information, branch access, and direct contact in one place.</h2>
          <div class="mt-6 grid gap-3">
            <?php foreach ($homeInsightCards as $card): ?>
              <a href="<?= htmlspecialchars(theme_page_url((string)$card['href'])) ?>" class="flex items-center gap-4 rounded-2xl border border-white/12 bg-white/10 p-4 transition hover:bg-white/14">
                <img src="<?= htmlspecialchars(theme_asset_url((string)$card['image'])) ?>" alt="<?= htmlspecialchars((string)$card['title']) ?>" class="h-16 w-16 rounded-xl object-cover">
                <span>
                  <span class="block text-sm font-semibold"><?= htmlspecialchars((string)$card['title']) ?></span>
                  <span class="mt-1 block text-xs leading-6 text-white/72"><?= htmlspecialchars((string)$card['text']) ?></span>
                </span>
              </a>
            <?php endforeach; ?>
          </div>
          <div class="mt-6 flex flex-wrap gap-3">
            <a href="<?= htmlspecialchars(theme_page_url('Contact-Us.php')) ?>" class="inline-flex rounded-full bg-white px-5 py-3 text-sm font-semibold" style="color:var(--primary2)">Contact Us</a>
            <a href="<?= htmlspecialchars(theme_page_url('Locations.php')) ?>" class="inline-flex rounded-full border border-white/18 px-5 py-3 text-sm font-semibold text-white">Find a Branch</a>
          </div>
        </div>
      </article>
    </div>
  </section>

  <section class="max-w-7xl mx-auto px-4 pt-8 md:pt-10">
    <div class="grid gap-5 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
      <article class="surface-card rounded-[1.9rem] p-6 md:p-8">
        <p class="section-kicker">Trust & Standards</p>
        <h2 class="mt-2 font-display text-3xl leading-tight" style="color:var(--primary2)">Institutional signals clients expect.</h2>
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          <?php foreach ($homeTrustSignals as $signal): ?>
            <div class="rounded-2xl border p-4" style="border-color:var(--line); background:color-mix(in srgb, var(--surface) 96%, white)">
              <p class="text-sm font-semibold" style="color:var(--primary2)"><?= htmlspecialchars((string)$signal['label']) ?></p>
              <p class="mt-2 text-sm leading-7" style="color:var(--muted)"><?= htmlspecialchars((string)$signal['text']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </article>

      <article class="surface-card rounded-[1.9rem] p-6 md:p-8">
        <p class="section-kicker">Service Facts</p>
        <h2 class="mt-2 font-display text-3xl leading-tight" style="color:var(--primary2)">At-a-glance operating profile.</h2>
        <div class="mt-6 space-y-3">
          <?php foreach ($homeServiceFacts as $fact): ?>
            <div class="rounded-2xl border px-4 py-3" style="border-color:var(--line); background:color-mix(in srgb, var(--bg) 40%, white)">
              <p class="text-xs uppercase tracking-[0.14em]" style="color:var(--muted)"><?= htmlspecialchars((string)$fact['label']) ?></p>
              <p class="mt-1 text-sm font-semibold" style="color:var(--primary2)"><?= htmlspecialchars((string)$fact['value']) ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </article>
    </div>
  </section>
</main>
<?php else: ?>
<main class="max-w-7xl mx-auto px-4 py-8 md:py-12">
  <section class="mb-8 overflow-hidden rounded-[2rem] text-white shadow-editorial" style="background:linear-gradient(135deg, #0c2037 0%, var(--primary2) 42%, var(--primary) 100%)">
    <div class="grid gap-8 px-6 py-10 md:px-10 md:py-12 lg:grid-cols-[minmax(0,1.2fr)_280px] lg:items-end">
      <div>
        <p class="text-[11px] uppercase tracking-[0.24em] text-white/65"><?= htmlspecialchars((string)($pageProfile['eyebrow'] ?? 'Banking Services')) ?></p>
        <h1 class="mt-3 font-display text-4xl leading-tight md:text-5xl"><?= htmlspecialchars((string)$pageTitle) ?></h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-white/80 md:text-base"><?= htmlspecialchars((string)($pageProfile['summary'] ?? 'Banking information and service details.')) ?></p>
      </div>
      <div class="space-y-3">
        <?php foreach (array_slice($sectionLinks, 0, 4) as $link): ?>
          <a href="<?= htmlspecialchars(theme_page_url((string)$link[1])) ?>" class="block rounded-2xl border border-white/12 bg-white/8 px-4 py-3 text-sm font-medium text-white transition hover:bg-white/12">
            <?= htmlspecialchars((string)$link[0]) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php if ($isContactPage): ?>
  <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)]">
    <article class="surface-card rounded-[2rem] p-6 md:p-8">
      <p class="section-kicker">Contact Channels</p>
      <h2 class="mt-2 font-display text-3xl leading-tight" style="color:var(--primary2)">Reach our service team.</h2>
      <div class="mt-5 space-y-3 text-sm leading-7" style="color:var(--muted)">
        <?php if (!empty($site['phone'])): ?><p><strong style="color:var(--ink)">Phone:</strong> <?= htmlspecialchars((string)$site['phone']) ?></p><?php endif; ?>
        <?php if (!empty($site['email'])): ?><p><strong style="color:var(--ink)">Email:</strong> <?= htmlspecialchars((string)$site['email']) ?></p><?php endif; ?>
      </div>

      <div class="mt-6 rounded-2xl border p-5" style="border-color:var(--line); background:color-mix(in srgb, var(--bg) 36%, white)">
        <p class="text-xs uppercase tracking-[0.14em]" style="color:var(--muted)">Head Office Address</p>
        <p class="mt-2 text-sm leading-7" style="color:var(--ink)">
          <?= !empty($site['addr']) ? nl2br(htmlspecialchars((string)$site['addr'])) : 'Address has not been configured yet.' ?>
        </p>
      </div>

      <div class="mt-6">
        <p class="text-xs uppercase tracking-[0.14em]" style="color:var(--muted)">Branch Locations</p>
        <?php if ($contactBranch !== null): ?>
          <div class="mt-3 grid gap-3">
              <div class="rounded-2xl border p-4" style="border-color:var(--line); background:color-mix(in srgb, var(--surface) 96%, white)">
                <p class="text-sm font-semibold" style="color:var(--primary2)"><?= htmlspecialchars((string)$contactBranch['name']) ?></p>
                <?php if ((string)$contactBranch['address'] !== ''): ?><p class="mt-1 text-sm leading-7" style="color:var(--muted)"><?= htmlspecialchars((string)$contactBranch['address']) ?></p><?php endif; ?>
                <?php if ((string)$contactBranch['phone'] !== ''): ?><p class="mt-1 text-sm" style="color:var(--muted)"><strong style="color:var(--ink)">Phone:</strong> <?= htmlspecialchars((string)$contactBranch['phone']) ?></p><?php endif; ?>
              </div>
          </div>
        <?php endif; ?>
        <a href="<?= htmlspecialchars(theme_page_url('Locations.php')) ?>" class="inline-flex mt-4 rounded-full px-4 py-2 text-sm font-semibold" style="border:1px solid var(--line); color:var(--primary2); background:color-mix(in srgb, var(--bg) 34%, white)">See all branches</a>
      </div>
    </article>

    <article class="surface-card rounded-[2rem] p-6 md:p-8">
      <p class="section-kicker">Message Form</p>
      <h2 class="mt-2 font-display text-3xl leading-tight" style="color:var(--primary2)">Send us your enquiry.</h2>
      <p class="mt-3 text-sm leading-7" style="color:var(--muted)">Use this form for general support requests. Please do not include account numbers, passcodes, PINs, or sensitive financial information.</p>

      <?php if ($contactSuccess !== ''): ?>
        <div class="mt-5 rounded-xl border px-4 py-3 text-sm" style="border-color:#8ad2ad; background:#e9f9ef; color:#1f6d43">
          <?= htmlspecialchars($contactSuccess) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($contactErrors)): ?>
        <div class="mt-5 rounded-xl border px-4 py-3 text-sm" style="border-color:#ef9f9f; background:#fff1f1; color:#9e2f2f">
          <ul class="list-disc pl-5">
            <?php foreach ($contactErrors as $err): ?>
              <li><?= htmlspecialchars((string)$err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= htmlspecialchars(theme_page_url('Contact-Us.php')) ?>" class="mt-5 grid gap-4">
        <input type="hidden" name="theme2_contact_submit" value="1">

        <label class="text-xs font-semibold uppercase tracking-[0.12em]" style="color:var(--muted)">
          Department
          <select name="department" class="mt-2 w-full rounded-xl border px-3 py-3 text-sm" style="border-color:var(--line); background:#fff; color:var(--ink)">
            <?php foreach (['General Inquiry', 'Customer Service', 'Online Banking', 'Marketing', 'Loan Services', 'Card Services'] as $opt): ?>
              <option value="<?= htmlspecialchars($opt) ?>" <?= $contactForm['department'] === $opt ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
            <?php endforeach; ?>
          </select>
        </label>

        <label class="text-xs font-semibold uppercase tracking-[0.12em]" style="color:var(--muted)">
          Full Name
          <input type="text" name="name" value="<?= htmlspecialchars($contactForm['name']) ?>" class="mt-2 w-full rounded-xl border px-3 py-3 text-sm" style="border-color:var(--line); background:#fff; color:var(--ink)" required>
        </label>

        <div class="grid gap-4 sm:grid-cols-2">
          <label class="text-xs font-semibold uppercase tracking-[0.12em]" style="color:var(--muted)">
            Email
            <input type="email" name="email" value="<?= htmlspecialchars($contactForm['email']) ?>" class="mt-2 w-full rounded-xl border px-3 py-3 text-sm" style="border-color:var(--line); background:#fff; color:var(--ink)" required>
          </label>
          <label class="text-xs font-semibold uppercase tracking-[0.12em]" style="color:var(--muted)">
            Phone
            <input type="text" name="phone" value="<?= htmlspecialchars($contactForm['phone']) ?>" class="mt-2 w-full rounded-xl border px-3 py-3 text-sm" style="border-color:var(--line); background:#fff; color:var(--ink)">
          </label>
        </div>

        <label class="text-xs font-semibold uppercase tracking-[0.12em]" style="color:var(--muted)">
          Message
          <textarea name="comments" rows="6" class="mt-2 w-full rounded-xl border px-3 py-3 text-sm" style="border-color:var(--line); background:#fff; color:var(--ink)" required><?= htmlspecialchars($contactForm['comments']) ?></textarea>
        </label>

        <button type="submit" class="inline-flex w-fit rounded-full px-5 py-3 text-sm font-semibold text-white" style="background:linear-gradient(135deg, var(--primary), var(--primary2))">Submit Message</button>
      </form>
    </article>
  </section>
  <?php elseif ($isLocationsPage): ?>
  <section class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
    <article class="surface-card rounded-[2rem] p-6 md:p-8">
      <p class="section-kicker">Branch Network</p>
      <h2 class="mt-2 font-display text-3xl leading-tight" style="color:var(--primary2)">Visit our branches and service centers.</h2>
      <p class="mt-3 text-sm leading-7" style="color:var(--muted)">Our locations are organized for convenient access to account support, branch services, and in-person banking assistance.</p>

      <div class="mt-6 rounded-2xl border p-5" style="border-color:var(--line); background:color-mix(in srgb, var(--bg) 36%, white)">
        <p class="text-xs uppercase tracking-[0.14em]" style="color:var(--muted)">Head Office</p>
        <p class="mt-2 text-sm leading-7" style="color:var(--ink)"><?= !empty($site['addr']) ? nl2br(htmlspecialchars((string)$site['addr'])) : 'Address has not been configured yet.' ?></p>
        <?php if (!empty($site['phone'])): ?><p class="mt-2 text-sm" style="color:var(--muted)"><strong style="color:var(--ink)">Phone:</strong> <?= htmlspecialchars((string)$site['phone']) ?></p><?php endif; ?>
        <?php if (!empty($site['email'])): ?><p class="mt-1 text-sm" style="color:var(--muted)"><strong style="color:var(--ink)">Email:</strong> <?= htmlspecialchars((string)$site['email']) ?></p><?php endif; ?>
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <a href="<?= htmlspecialchars(theme_page_url('Contact-Us.php')) ?>" class="inline-flex rounded-full px-4 py-2.5 text-sm font-semibold text-white" style="background:linear-gradient(135deg, var(--primary), var(--primary2))">Contact Us</a>
      </div>
    </article>

    <article class="surface-card rounded-[2rem] p-6 md:p-8">
      <p class="section-kicker">Locations Overview</p>
      <h3 class="mt-2 font-display text-2xl leading-tight" style="color:var(--primary2)">All Active Branches</h3>
      <p class="mt-3 text-sm leading-7" style="color:var(--muted)">Branch records shown here are managed in admin settings and update automatically.</p>

      <?php if (!empty($branchList)): ?>
        <div class="mt-5 grid gap-3">
          <?php foreach ($branchList as $branch): ?>
            <div class="rounded-2xl border p-4" style="border-color:var(--line); background:color-mix(in srgb, var(--surface) 96%, white)">
              <p class="text-sm font-semibold" style="color:var(--primary2)"><?= htmlspecialchars((string)$branch['name']) ?></p>
              <?php if ((string)$branch['address'] !== ''): ?><p class="mt-1 text-sm leading-7" style="color:var(--muted)"><?= htmlspecialchars((string)$branch['address']) ?></p><?php endif; ?>
              <?php if ((string)$branch['phone'] !== ''): ?><p class="mt-1 text-sm" style="color:var(--muted)"><strong style="color:var(--ink)">Phone:</strong> <?= htmlspecialchars((string)$branch['phone']) ?></p><?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="mt-4 text-sm" style="color:var(--muted)">No branch entries are currently active. Please add locations in admin settings.</p>
      <?php endif; ?>
    </article>
  </section>
  <?php else: ?>
  <article class="legacy-wrap surface-card rounded-[2rem] p-6 md:p-8 lg:p-10">
    <?= $middle ?>
  </article>
  <?php endif; ?>

  <section class="mt-8 grid gap-5 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
    <div class="surface-card rounded-[1.7rem] p-6">
      <p class="section-kicker">Client Access</p>
      <div class="mt-4 flex flex-wrap gap-3">
        <?php foreach ($clientActions as $action): ?>
          <a href="<?= htmlspecialchars((string)$action['href']) ?>" class="inline-flex rounded-full px-4 py-2.5 text-sm font-semibold" style="background:<?= $action['variant'] === 'primary' ? 'linear-gradient(135deg, var(--primary), var(--primary2))' : 'transparent' ?>; color:<?= $action['variant'] === 'primary' ? '#ffffff' : 'var(--primary2)' ?>; border:1px solid <?= $action['variant'] === 'primary' ? 'transparent' : 'color-mix(in srgb, var(--line) 92%, white)' ?>;">
            <?= htmlspecialchars((string)$action['label']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="surface-card rounded-[1.7rem] p-6 text-sm leading-7" style="color:var(--muted)">
      <?php if (!empty($site['phone'])): ?><p><strong style="color:var(--ink)">Phone:</strong> <?= htmlspecialchars((string)$site['phone']) ?></p><?php endif; ?>
      <?php if (!empty($site['email'])): ?><p><strong style="color:var(--ink)">Email:</strong> <?= htmlspecialchars((string)$site['email']) ?></p><?php endif; ?>
    </div>
  </section>
</main>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
