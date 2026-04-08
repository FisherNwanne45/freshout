<style>
  .t3-slider {
    position: relative;
    border-radius: 1.5rem;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.24);
    min-height: 420px;
    background: #020617;
  }
  .t3-slide {
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 600ms ease;
    pointer-events: none;
    display: grid;
    align-items: center;
    background-size: cover;
    background-position: center;
  }
  .t3-slide.active { opacity: 1; pointer-events: auto; }
  .t3-slide::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(115deg, rgba(2,6,23,.82), rgba(2,6,23,.54) 42%, rgba(2,6,23,.28));
  }
  .t3-slide-content {
    position: relative;
    z-index: 2;
    max-width: 620px;
    padding: 2.2rem;
  }
  .t3-slider-dot {
    width: 11px;
    height: 11px;
    border-radius: 9999px;
    border: 1px solid rgba(255,255,255,.45);
    background: transparent;
  }
  .t3-slider-dot.active {
    background: var(--accent);
    border-color: transparent;
  }
  .t3-slider-ctrl {
    width: 38px;
    height: 38px;
    border-radius: 9999px;
    border: 1px solid rgba(255,255,255,.28);
    background: rgba(2,6,23,.5);
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .t3-grid-card {
    transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
  }
  .t3-grid-card:hover {
    transform: translateY(-6px);
    border-color: rgba(56, 189, 248, 0.45);
    box-shadow: 0 18px 40px rgba(2, 6, 23, 0.45);
  }
  .t3-reveal {
    opacity: 0;
    transform: translateY(18px);
    transition: opacity 480ms ease, transform 480ms ease;
  }
  .t3-reveal.show {
    opacity: 1;
    transform: translateY(0);
  }
</style>

<section class="mb-8 t3-slider" id="t3-home-slider">
  <article class="t3-slide active" style="background-image:url('https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=1600&q=80');">
    <div class="t3-slide-content">
      <p class="text-xs uppercase tracking-[0.22em] text-cyan-200">Strategic Banking</p>
      <h1 class="mt-3 text-3xl md:text-5xl font-black text-white leading-tight">Built For Confident Capital Decisions</h1>
      <p class="mt-3 text-sm md:text-base text-slate-200">Integrated commercial accounts, treasury movement, and relationship management tailored for growth leaders.</p>
      <div class="mt-6 flex flex-wrap gap-3">
        <a href="Contact-Us.php" class="px-5 py-3 rounded-xl text-sm font-semibold text-slate-950" style="background: var(--accent)">Speak With Advisor</a>
        <a href="Business-Checking.php" class="px-5 py-3 rounded-xl text-sm font-semibold border border-white/20 text-slate-100">Business Accounts</a>
      </div>
    </div>
  </article>

  <article class="t3-slide" style="background-image:url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1600&q=80');">
    <div class="t3-slide-content">
      <p class="text-xs uppercase tracking-[0.22em] text-emerald-200">Digital Excellence</p>
      <h2 class="mt-3 text-3xl md:text-5xl font-black text-white leading-tight">Control Payments Across Every Channel</h2>
      <p class="mt-3 text-sm md:text-base text-slate-200">Operate seamlessly with online banking, payment orchestration, and secure approval workflows.</p>
      <div class="mt-6 flex flex-wrap gap-3">
        <a href="Online-Banking.php" class="px-5 py-3 rounded-xl text-sm font-semibold text-slate-950" style="background: var(--accent)">Explore Digital Banking</a>
        <a href="Payment-and-Receivables.php" class="px-5 py-3 rounded-xl text-sm font-semibold border border-white/20 text-slate-100">Payments & Receivables</a>
      </div>
    </div>
  </article>

  <article class="t3-slide" style="background-image:url('https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1600&q=80');">
    <div class="t3-slide-content">
      <p class="text-xs uppercase tracking-[0.22em] text-amber-200">Trust & Protection</p>
      <h2 class="mt-3 text-3xl md:text-5xl font-black text-white leading-tight">Security That Moves At Business Speed</h2>
      <p class="mt-3 text-sm md:text-base text-slate-200">Layered controls, active monitoring, and specialist response ensure resilient financial operations.</p>
      <div class="mt-6 flex flex-wrap gap-3">
        <a href="Security-Center.php" class="px-5 py-3 rounded-xl text-sm font-semibold text-slate-950" style="background: var(--accent)">Security Center</a>
        <a href="Contact-Us.php" class="px-5 py-3 rounded-xl text-sm font-semibold border border-white/20 text-slate-100">Request Consultation</a>
      </div>
    </div>
  </article>

  <div class="absolute left-6 right-6 bottom-5 z-10 flex items-center justify-between gap-3">
    <div class="flex items-center gap-2" id="t3-slider-dots">
      <button class="t3-slider-dot active" data-slide="0" aria-label="Slide 1"></button>
      <button class="t3-slider-dot" data-slide="1" aria-label="Slide 2"></button>
      <button class="t3-slider-dot" data-slide="2" aria-label="Slide 3"></button>
    </div>
    <div class="flex items-center gap-2">
      <button class="t3-slider-ctrl" id="t3-prev" aria-label="Previous slide">&#8249;</button>
      <button class="t3-slider-ctrl" id="t3-next" aria-label="Next slide">&#8250;</button>
    </div>
  </div>
</section>

<section class="mb-8">
  <div class="flex items-end justify-between gap-3 mb-4">
    <div>
      <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Core Pillars</p>
      <h2 class="text-2xl md:text-3xl font-black text-white mt-1">Distinct Banking Capabilities</h2>
    </div>
  </div>
  <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <a href="Business-Checking.php" class="t3-grid-card t3-reveal rounded-2xl border border-white/10 bg-slate-950/45 p-5 block">
      <div class="flex items-center justify-between gap-3">
        <p class="text-xs uppercase tracking-[0.16em] text-slate-400">01</p>
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-cyan-300/30 bg-cyan-400/10 text-cyan-200">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M3 11h18M7 7h10M7 15h10M6 3h12a2 2 0 0 1 2 2v14l-4-3-4 3-4-3-4 3V5a2 2 0 0 1 2-2z" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
      </div>
      <h3 class="mt-2 text-lg font-bold text-white">Business Velocity</h3>
      <p class="mt-2 text-sm text-slate-300">Structured accounts for operational agility and controlled spend.</p>
    </a>
    <a href="Payment-and-Receivables.php" class="t3-grid-card t3-reveal rounded-2xl border border-white/10 bg-slate-950/45 p-5 block">
      <div class="flex items-center justify-between gap-3">
        <p class="text-xs uppercase tracking-[0.16em] text-slate-400">02</p>
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-300/30 bg-emerald-400/10 text-emerald-200">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M4 7h16M6 4h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M8 12h3v4H8zM13 10h3v6h-3z" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
      </div>
      <h3 class="mt-2 text-lg font-bold text-white">Payments Grid</h3>
      <p class="mt-2 text-sm text-slate-300">Unified receivables, approvals, and settlement oversight in one flow.</p>
    </a>
    <a href="Consumer-Lending.php" class="t3-grid-card t3-reveal rounded-2xl border border-white/10 bg-slate-950/45 p-5 block">
      <div class="flex items-center justify-between gap-3">
        <p class="text-xs uppercase tracking-[0.16em] text-slate-400">03</p>
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-amber-300/30 bg-amber-300/10 text-amber-100">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M12 3v18M6 9l6-6 6 6M6 15l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
      </div>
      <h3 class="mt-2 text-lg font-bold text-white">Lending Intelligence</h3>
      <p class="mt-2 text-sm text-slate-300">Adaptive lending products with transparent lifecycle support.</p>
    </a>
    <a href="Security-Center.php" class="t3-grid-card t3-reveal rounded-2xl border border-white/10 bg-slate-950/45 p-5 block">
      <div class="flex items-center justify-between gap-3">
        <p class="text-xs uppercase tracking-[0.16em] text-slate-400">04</p>
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-indigo-300/30 bg-indigo-400/10 text-indigo-200">
          <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M12 3l7 3v6c0 4.5-3 7.8-7 9-4-1.2-7-4.5-7-9V6l7-3z" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </span>
      </div>
      <h3 class="mt-2 text-lg font-bold text-white">Security Command</h3>
      <p class="mt-2 text-sm text-slate-300">Layered controls, response playbooks, and identity assurance.</p>
    </a>
  </div>
</section>

<section class="mb-6 t3-reveal rounded-3xl border border-white/10 p-6 md:p-8" style="background:linear-gradient(140deg, rgba(2,6,23,.65), rgba(15,23,42,.5));">
  <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
      <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Service Framework</p>
      <h2 class="text-2xl md:text-3xl font-black text-white mt-1">Improved Support Architecture</h2>
    </div>
    <a href="Contact-Us.php" class="px-4 py-2 rounded-xl text-sm font-semibold border border-white/20 text-slate-100">Speak With A Specialist</a>
  </div>

  <div class="grid md:grid-cols-3 gap-4">
    <div class="rounded-2xl overflow-hidden bg-white/5 border border-white/10">
      <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=900&q=80" alt="Onboarding consultation" class="h-36 w-full object-cover">
      <div class="p-5">
      <p class="text-sm font-semibold text-white">Dedicated Onboarding Desk</p>
      <p class="text-sm text-slate-300 mt-2">Migration support with account setup, channel activation, and policy guidance.</p>
      </div>
    </div>
    <div class="rounded-2xl overflow-hidden bg-white/5 border border-white/10">
      <img src="https://images.unsplash.com/photo-1529078155058-5d716f45d604?auto=format&fit=crop&w=900&q=80" alt="Transaction operations support" class="h-36 w-full object-cover">
      <div class="p-5">
      <p class="text-sm font-semibold text-white">Transaction Escalation Lane</p>
      <p class="text-sm text-slate-300 mt-2">Priority routing for time-sensitive operations with specialist intervention.</p>
      </div>
    </div>
    <div class="rounded-2xl overflow-hidden bg-white/5 border border-white/10">
      <img src="https://images.unsplash.com/photo-1553484771-371a605b060b?auto=format&fit=crop&w=900&q=80" alt="Relationship review meeting" class="h-36 w-full object-cover">
      <div class="p-5">
      <p class="text-sm font-semibold text-white">Relationship Review Cycle</p>
      <p class="text-sm text-slate-300 mt-2">Scheduled performance reviews and strategy recommendations from your advisor.</p>
      </div>
    </div>
  </div>
</section>

<section class="mb-8 grid lg:grid-cols-3 gap-4 t3-reveal">
  <article class="rounded-2xl border border-white/10 bg-slate-950/45 p-5">
    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Business Banking</p>
    <h3 class="mt-2 text-xl font-bold text-white">Accounts That Fit How You Operate</h3>
    <p class="mt-2 text-sm text-slate-300">From daily transactions to payroll and vendor settlements, choose accounts built for practical business flow.</p>
    <a href="Business-Checking.php" class="inline-flex mt-4 text-sm font-semibold" style="color:var(--accent)">Explore business accounts</a>
  </article>
  <article class="rounded-2xl border border-white/10 bg-slate-950/45 p-5">
    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Payments</p>
    <h3 class="mt-2 text-xl font-bold text-white">Move Funds With Clear Controls</h3>
    <p class="mt-2 text-sm text-slate-300">Manage receivables, outgoing payments, and approvals with straightforward visibility at each stage.</p>
    <a href="Payment-and-Receivables.php" class="inline-flex mt-4 text-sm font-semibold" style="color:var(--accent)">View payment services</a>
  </article>
  <article class="rounded-2xl border border-white/10 bg-slate-950/45 p-5">
    <p class="text-xs uppercase tracking-[0.16em] text-slate-400">Support</p>
    <h3 class="mt-2 text-xl font-bold text-white">Real Help From Real Bankers</h3>
    <p class="mt-2 text-sm text-slate-300">Speak with a specialist when you need assistance with transfers, account setup, or service requests.</p>
    <a href="Contact-Us.php" class="inline-flex mt-4 text-sm font-semibold" style="color:var(--accent)">Contact support</a>
  </article>
</section>

<section class="mb-8 t3-reveal">
  <div class="flex items-end justify-between gap-3 mb-4">
    <div>
      <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Featured Services</p>
      <h2 class="text-2xl md:text-3xl font-black text-white mt-1">Highlights You Can Start With Today</h2>
    </div>
  </div>
  <div class="grid md:grid-cols-3 gap-4">
    <article class="rounded-2xl overflow-hidden border border-white/10 bg-slate-950/50">
      <img src="<?= htmlspecialchars(theme_asset_url('images/subpage-photo.jpg')) ?>" alt="Business checking" class="w-full h-44 object-cover">
      <div class="p-5">
        <h3 class="text-lg font-bold text-white">Business Checking</h3>
        <p class="text-sm text-slate-300 mt-2">Handle everyday transactions with dependable access and practical account management tools.</p>
        <a href="Business-Checking.php" class="inline-flex mt-4 text-sm font-semibold" style="color:var(--accent)">Learn more</a>
      </div>
    </article>
    <article class="rounded-2xl overflow-hidden border border-white/10 bg-slate-950/50">
      <img src="<?= htmlspecialchars(theme_asset_url('images/contact-icon.png')) ?>" alt="Online banking" class="w-full h-44 object-contain bg-slate-900 p-8">
      <div class="p-5">
        <h3 class="text-lg font-bold text-white">Online & Mobile Banking</h3>
        <p class="text-sm text-slate-300 mt-2">Access your accounts, review activity, and manage key banking actions across devices.</p>
        <a href="Online-Banking.php" class="inline-flex mt-4 text-sm font-semibold" style="color:var(--accent)">See digital options</a>
      </div>
    </article>
    <article class="rounded-2xl overflow-hidden border border-white/10 bg-slate-950/50">
      <img src="<?= htmlspecialchars(theme_asset_url('images/logo-sba.png')) ?>" alt="Lending programs" class="w-full h-44 object-contain bg-slate-900 p-8">
      <div class="p-5">
        <h3 class="text-lg font-bold text-white">Lending Programs</h3>
        <p class="text-sm text-slate-300 mt-2">Explore structured lending options and advisory support for growth and working capital.</p>
        <a href="Consumer-Lending.php" class="inline-flex mt-4 text-sm font-semibold" style="color:var(--accent)">Review lending</a>
      </div>
    </article>
  </div>
</section>

<section class="mb-8 t3-reveal rounded-3xl border border-white/10 p-6 md:p-8" style="background:linear-gradient(130deg, rgba(15,23,42,.82), rgba(2,6,23,.72));">
  <div class="grid md:grid-cols-2 gap-8 items-start">
    <div>
      <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Why Clients Choose Us</p>
      <h2 class="mt-2 text-2xl md:text-3xl font-black text-white">Reliable Banking Backed By Practical Service</h2>
      <p class="mt-3 text-sm text-slate-300">We combine secure channels, responsive support, and flexible account products to serve both personal and business goals.</p>
      <div class="mt-5 grid grid-cols-2 gap-3 text-sm">
        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
          <p class="text-xl font-black text-white">4.8/5</p>
          <p class="text-slate-300 text-xs mt-1">Client Satisfaction</p>
        </div>
        <div class="rounded-xl border border-white/10 bg-white/5 p-3">
          <p class="text-xl font-black text-white">150+</p>
          <p class="text-slate-300 text-xs mt-1">Advisors & Specialists</p>
        </div>
      </div>
    </div>
    <div class="space-y-3">
      <div class="rounded-xl border border-white/10 bg-white/5 p-4">
        <p class="text-sm font-semibold text-white">Operational Confidence</p>
        <p class="text-sm text-slate-300 mt-1">High-clarity account tools reduce friction across daily finance workflows.</p>
      </div>
      <div class="rounded-xl border border-white/10 bg-white/5 p-4">
        <p class="text-sm font-semibold text-white">Professional Coverage</p>
        <p class="text-sm text-slate-300 mt-1">Dedicated contact paths for urgent requests and strategic account conversations.</p>
      </div>
      <div class="rounded-xl border border-white/10 bg-white/5 p-4">
        <p class="text-sm font-semibold text-white">Security Discipline</p>
        <p class="text-sm text-slate-300 mt-1">Clear controls and specialist guidance for high-value transaction integrity.</p>
      </div>
    </div>
  </div>
</section>

<section class="mb-2 t3-reveal rounded-3xl border border-white/10 p-6 md:p-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4" style="background:color-mix(in srgb, var(--primary2) 65%, #020617);">
  <div>
    <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Next Step</p>
    <h2 class="mt-1 text-2xl font-black text-white">Ready To Elevate Your Banking Operations?</h2>
    <p class="mt-1 text-sm text-slate-200">Connect with our advisory team to structure the right account and service mix.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="Contact-Us.php" class="px-5 py-3 rounded-xl text-sm font-semibold text-slate-950" style="background:var(--accent)">Request Consultation</a>
    <a href="Locations.php" class="px-5 py-3 rounded-xl text-sm font-semibold border border-white/20 text-white">Find Location</a>
  </div>
</section>

<script>
  (function () {
    var slides = document.querySelectorAll('#t3-home-slider .t3-slide');
    var dots = document.querySelectorAll('#t3-slider-dots .t3-slider-dot');
    var prev = document.getElementById('t3-prev');
    var next = document.getElementById('t3-next');
    var idx = 0;
    var timer = null;

    function setSlide(target) {
      if (!slides.length) return;
      idx = (target + slides.length) % slides.length;
      slides.forEach(function (s, i) { s.classList.toggle('active', i === idx); });
      dots.forEach(function (d, i) { d.classList.toggle('active', i === idx); });
    }

    function startAuto() {
      stopAuto();
      timer = setInterval(function () { setSlide(idx + 1); }, 6000);
    }

    function stopAuto() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    dots.forEach(function(dot){
      dot.addEventListener('click', function(){ setSlide(parseInt(dot.dataset.slide || '0', 10)); startAuto(); });
    });
    if (prev) prev.addEventListener('click', function(){ setSlide(idx - 1); startAuto(); });
    if (next) next.addEventListener('click', function(){ setSlide(idx + 1); startAuto(); });

    setSlide(0);
    startAuto();

    var nodes = document.querySelectorAll('.t3-reveal');
    if (!('IntersectionObserver' in window) || !nodes.length) {
      nodes.forEach(function(n){ n.classList.add('show'); });
      return;
    }
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(entry){
        if (entry.isIntersecting) {
          entry.target.classList.add('show');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    nodes.forEach(function(n){ io.observe(n); });
  })();
</script>
