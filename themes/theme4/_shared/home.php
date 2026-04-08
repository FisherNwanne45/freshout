<section class="mb-0 rounded-t-3xl rounded-b-none overflow-hidden border" style="border-color:var(--line)">
  <div class="relative" id="t4-slider">
    <article class="t4-slide active" style="background-image:url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=1600&q=80')">
      <div class="t4-overlay"></div>
      <div class="t4-content">
        <p class="text-xs uppercase tracking-[0.2em] text-white/75"><?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?></p>
        <h2 class="mt-3 text-4xl md:text-6xl font-black text-white">Financial Clarity For Ambitious Clients</h2>
        <p class="mt-4 text-sm md:text-base text-slate-100 max-w-2xl">Move from planning to execution with specialists who understand business pace and personal priorities.</p>
        <div class="mt-6 flex flex-wrap gap-3">
          <a href="Contact-Us.php" class="px-6 py-3 rounded-xl text-sm font-semibold text-slate-950" style="background:var(--accent)">Book Advisory Call</a>
          <a href="Locations.php" class="px-6 py-3 rounded-xl text-sm font-semibold border border-white/35 text-white">Find Nearby Branch</a>
        </div>
      </div>
    </article>
    <article class="t4-slide" style="background-image:url('https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=1600&q=80')">
      <div class="t4-overlay"></div>
      <div class="t4-content">
        <p class="text-xs uppercase tracking-[0.2em] text-white/75"><?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?></p>
        <h2 class="mt-3 text-4xl md:text-6xl font-black text-white">Digital Banking Without Friction</h2>
        <p class="mt-4 text-sm md:text-base text-slate-100 max-w-2xl">Account visibility, payment control, and secure workflows built for modern decision-making.</p>
        <div class="mt-6 flex flex-wrap gap-3">
          <a href="Online-Banking.php" class="px-6 py-3 rounded-xl text-sm font-semibold text-slate-950" style="background:var(--accent)">Explore Digital Tools</a>
          <a href="Security-Center.php" class="px-6 py-3 rounded-xl text-sm font-semibold border border-white/35 text-white">Security Center</a>
        </div>
      </div>
    </article>
    <article class="t4-slide" style="background-image:url('https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1600&q=80')">
      <div class="t4-overlay"></div>
      <div class="t4-content">
        <p class="text-xs uppercase tracking-[0.2em] text-white/75"><?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?></p>
        <h2 class="mt-3 text-4xl md:text-6xl font-black text-white">Human Support, Structured Delivery</h2>
        <p class="mt-4 text-sm md:text-base text-slate-100 max-w-2xl">Onboarding, transaction support, and relationship guidance delivered through one coordinated service path.</p>
        <div class="mt-6 flex flex-wrap gap-3">
          <a href="Contact-Us.php" class="px-6 py-3 rounded-xl text-sm font-semibold text-slate-950" style="background:var(--accent)">Speak With Specialist</a>
          <a href="Business-Checking.php" class="px-6 py-3 rounded-xl text-sm font-semibold border border-white/35 text-white">Business Accounts</a>
        </div>
      </div>
    </article>

    <div class="absolute left-5 right-5 bottom-4 flex items-center justify-between gap-3 z-20">
      <div class="flex items-center gap-2" id="t4-slider-dots">
        <button class="t4-dot active" data-slide="0" aria-label="Slide 1"></button>
        <button class="t4-dot" data-slide="1" aria-label="Slide 2"></button>
        <button class="t4-dot" data-slide="2" aria-label="Slide 3"></button>
      </div>
      <div class="flex items-center gap-2">
        <button class="t4-ctrl" id="t4-prev" aria-label="Previous">&#8249;</button>
        <button class="t4-ctrl" id="t4-next" aria-label="Next">&#8250;</button>
      </div>
    </div>
  </div>
</section>

<section class="mb-8 -mt-px rounded-b-3xl rounded-t-none border overflow-hidden" style="border-color:var(--line); background:linear-gradient(130deg, color-mix(in srgb,var(--primary2) 90%, #0b1220), color-mix(in srgb,var(--primary) 82%, #1f2937));">
  <div class="grid lg:grid-cols-12 gap-0">
    <aside class="hidden lg:flex lg:col-span-3 p-6 border-r border-white/15 flex-col justify-between">
      <div>
        <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Client Insight</p>
        <p class="mt-3 text-sm text-slate-200">Dedicated specialists for business and personal banking strategy.</p>
        <div class="mt-5 rounded-2xl border border-white/20 bg-white/5 p-4">
          <svg viewBox="0 0 180 90" class="w-full h-auto" aria-hidden="true">
            <defs>
              <linearGradient id="atmGlow" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0%" stop-color="rgba(255,255,255,0.95)"/>
                <stop offset="100%" stop-color="rgba(255,255,255,0.55)"/>
              </linearGradient>
            </defs>
            <rect x="46" y="12" width="88" height="66" rx="10" fill="none" stroke="url(#atmGlow)" stroke-width="2"/>
            <rect x="58" y="24" width="64" height="20" rx="4" fill="rgba(255,255,255,0.12)" stroke="rgba(255,255,255,0.65)"/>
            <rect x="64" y="52" width="24" height="10" rx="3" fill="rgba(255,255,255,0.2)"/>
            <rect x="92" y="52" width="28" height="5" rx="2.5" fill="rgba(255,255,255,0.6)"/>
            <circle cx="64" cy="34" r="2" fill="rgba(255,255,255,0.9)"/>
            <circle cx="70" cy="34" r="2" fill="rgba(255,255,255,0.7)"/>
            <circle cx="76" cy="34" r="2" fill="rgba(255,255,255,0.5)"/>
          </svg>
          <p class="mt-3 text-xs leading-relaxed text-slate-200/95">ATM access designed for speed, with intuitive screens and secure transaction prompts.</p>
          <p class="mt-1 text-[11px] text-slate-300/90">Always available for cash, balance checks, and quick account actions.</p>
        </div>
      </div>
      <a href="Contact-Us.php" class="inline-flex text-sm font-semibold text-white/95 hover:underline">Book Advisory Conversation</a>
    </aside>

    <div class="lg:col-span-6 px-6 py-10 md:px-10 md:py-14">
      <p class="text-xs uppercase tracking-[0.2em] text-white/70"><?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?></p>
      <h2 class="mt-3 text-4xl md:text-6xl font-black text-white leading-tight">Elevated Banking That Feels Intentional</h2>
      <p class="mt-4 text-sm md:text-base text-slate-100/90 max-w-2xl">Fewer distractions, clearer actions, and stronger service pathways across payments, lending, and support.</p>
      <div class="mt-7 flex flex-wrap gap-3">
        <a href="Contact-Us.php" class="px-6 py-3 rounded-xl text-sm font-semibold text-slate-950" style="background:var(--accent)">Get Started</a>
        <a href="Online-Banking.php" class="px-6 py-3 rounded-xl text-sm font-semibold border border-white/30 text-white">Digital Banking</a>
      </div>
    </div>

    <aside class="hidden lg:block lg:col-span-3 p-6 border-l border-white/15">
      <p class="text-[11px] uppercase tracking-[0.2em] text-white/70">Fast Access</p>
      <div class="mt-4 space-y-3 text-sm">
        <a href="Business-Checking.php" class="block rounded-lg px-3 py-2 bg-white/10 text-slate-100">Business Accounts</a>
        <a href="Payment-and-Receivables.php" class="block rounded-lg px-3 py-2 bg-white/10 text-slate-100">Payments & Receivables</a>
        <a href="Locations.php" class="block rounded-lg px-3 py-2 bg-white/10 text-slate-100">Find a Location</a>
      </div>
      <div class="mt-5 rounded-2xl border border-white/20 bg-white/5 p-4">
        <svg viewBox="0 0 190 90" class="w-full h-auto" aria-hidden="true">
          <defs>
            <linearGradient id="bankFlow" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="rgba(255,255,255,0.95)"/>
              <stop offset="100%" stop-color="rgba(255,255,255,0.55)"/>
            </linearGradient>
          </defs>
          <path d="M18 70h154" stroke="rgba(255,255,255,0.35)" stroke-width="2" stroke-linecap="round"/>
          <rect x="30" y="33" width="18" height="37" rx="3" fill="none" stroke="url(#bankFlow)" stroke-width="2"/>
          <rect x="58" y="25" width="18" height="45" rx="3" fill="none" stroke="url(#bankFlow)" stroke-width="2"/>
          <rect x="86" y="40" width="18" height="30" rx="3" fill="none" stroke="url(#bankFlow)" stroke-width="2"/>
          <rect x="114" y="18" width="18" height="52" rx="3" fill="none" stroke="url(#bankFlow)" stroke-width="2"/>
          <path d="M26 24l18-8 18 8 18-8 18 8 18-8 18 8" fill="none" stroke="rgba(255,255,255,0.7)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <p class="mt-3 text-xs leading-relaxed text-slate-200/95">See all your banking activity at a glance, from deposits and transfers to service requests.</p>
      </div>
    </aside>
  </div>
</section>

<section class="mb-8">
  <div class="flex items-end justify-between gap-3 mb-4">
    <div>
      <p class="text-xs uppercase tracking-[0.18em]" style="color:var(--muted)">Featured Services</p>
      <h2 class="text-2xl md:text-3xl font-black mt-1" style="color:var(--primary2)">Start With What You Need Most</h2>
    </div>
  </div>
  <div class="grid md:grid-cols-3 gap-5">
    <article class="rounded-2xl overflow-hidden border" style="border-color:var(--line); background:var(--surface)">
      <img src="https://images.unsplash.com/photo-1554224154-22dec7ec8818?auto=format&fit=crop&w=900&q=80" alt="Business checking services" class="h-44 w-full object-cover">
      <div class="p-5">
        <p class="text-xs uppercase tracking-[0.16em]" style="color:var(--muted)">Business Banking</p>
        <h3 class="mt-2 text-xl font-bold" style="color:var(--ink)">Operate With Confidence</h3>
        <p class="mt-2 text-sm" style="color:var(--muted)">Structured account solutions for transactions, payroll, and treasury flow.</p>
        <a href="Business-Checking.php" class="inline-flex mt-4 text-sm font-semibold" style="color:var(--primary)">Explore business accounts</a>
      </div>
    </article>
    <article class="rounded-2xl overflow-hidden border" style="border-color:var(--line); background:var(--surface)">
      <img src="https://images.unsplash.com/photo-1526628953301-3e589a6a8b74?auto=format&fit=crop&w=900&q=80" alt="Payment and receivable operations" class="h-44 w-full object-cover">
      <div class="p-5">
        <p class="text-xs uppercase tracking-[0.16em]" style="color:var(--muted)">Payments Control</p>
        <h3 class="mt-2 text-xl font-bold" style="color:var(--ink)">Move Funds With Clarity</h3>
        <p class="mt-2 text-sm" style="color:var(--muted)">Consolidated approvals, receivables tracking, and execution oversight.</p>
        <a href="Payment-and-Receivables.php" class="inline-flex mt-4 text-sm font-semibold" style="color:var(--primary)">View payment services</a>
      </div>
    </article>
    <article class="rounded-2xl overflow-hidden border" style="border-color:var(--line); background:var(--surface)">
      <img src="https://images.unsplash.com/photo-1521791055366-0d553872125f?auto=format&fit=crop&w=900&q=80" alt="Customer support specialists" class="h-44 w-full object-cover">
      <div class="p-5">
        <p class="text-xs uppercase tracking-[0.16em]" style="color:var(--muted)">Concierge Support</p>
        <h3 class="mt-2 text-xl font-bold" style="color:var(--ink)">Direct Access To Specialists</h3>
        <p class="mt-2 text-sm" style="color:var(--muted)">Practical guidance for onboarding, service requests, and account transitions.</p>
        <a href="Contact-Us.php" class="inline-flex mt-4 text-sm font-semibold" style="color:var(--primary)">Contact support</a>
      </div>
    </article>
  </div>
</section>

<section class="rounded-3xl border p-6 md:p-8" style="border-color:var(--line); background:color-mix(in srgb, var(--accent) 8%, #fff)">
  <div class="grid md:grid-cols-2 gap-6 items-center">
    <div>
      <p class="text-xs uppercase tracking-[0.2em]" style="color:var(--muted)">Experience Upgrade</p>
      <h2 class="mt-2 text-2xl md:text-3xl font-black" style="color:var(--primary2)">Simple Decisions, Powerful Outcomes</h2>
      <p class="mt-3 text-sm" style="color:var(--muted)"><?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?> emphasizes fast decision paths and stronger call-to-action flow, so visitors can move from discovery to engagement quickly.</p>
      <div class="mt-5 flex flex-wrap gap-3">
        <a href="Contact-Us.php" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:var(--primary)">Talk To An Advisor</a>
        <a href="Locations.php" class="px-5 py-2.5 rounded-xl text-sm font-semibold border" style="border-color:var(--line)">Our Locations</a>
      </div>
    </div>
    <div class="rounded-2xl overflow-hidden border" style="border-color:var(--line)">
      <img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1200&q=80" alt="Professional financial planning" class="h-64 w-full object-cover">
    </div>
  </div>
</section>

<style>
  #t4-slider { min-height: 520px; }
  .t4-slide {
    display: none;
    position: relative;
    min-height: 520px;
    background-size: cover;
    background-position: center;
  }
  .t4-slide.active { display: block; }
  .t4-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(120deg, rgba(2,6,23,.72), rgba(15,23,42,.45));
  }
  .t4-content {
    position: relative;
    z-index: 2;
    padding: 2.5rem;
  }
  .t4-dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.5);
  }
  .t4-dot.active { background: #fff; }
  .t4-ctrl {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    background: rgba(255,255,255,.22);
    color: #fff;
    font-size: 20px;
    line-height: 1;
  }
  @media (max-width: 760px) {
    .t4-content { padding: 1.4rem; }
    #t4-slider { min-height: 430px; }
    .t4-slide { min-height: 430px; }
  }
</style>
<script>
  (function () {
    var root = document.getElementById('t4-slider');
    if (!root) return;
    var slides = Array.prototype.slice.call(root.querySelectorAll('.t4-slide'));
    var dots = Array.prototype.slice.call(document.querySelectorAll('#t4-slider-dots .t4-dot'));
    var nextBtn = document.getElementById('t4-next');
    var prevBtn = document.getElementById('t4-prev');
    var idx = 0;
    var timer = null;

    function show(i) {
      idx = (i + slides.length) % slides.length;
      slides.forEach(function (s, si) { s.classList.toggle('active', si === idx); });
      dots.forEach(function (d, di) { d.classList.toggle('active', di === idx); });
    }

    function start() {
      stop();
      timer = setInterval(function () { show(idx + 1); }, 5500);
    }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    dots.forEach(function (d, di) {
      d.addEventListener('click', function () {
        show(di);
        start();
      });
    });
    if (nextBtn) nextBtn.addEventListener('click', function () { show(idx + 1); start(); });
    if (prevBtn) prevBtn.addEventListener('click', function () { show(idx - 1); start(); });

    show(0);
    start();
  }());
</script>
