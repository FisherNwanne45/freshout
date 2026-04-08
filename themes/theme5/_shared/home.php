<?php if (!isset($site) || !isset($palette)) { require __DIR__ . '/bootstrap.php'; } ?>
<!-- HERO SLIDER SECTION -->
<section class="mb-0 relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw] w-screen overflow-hidden border-y" style="border-color:var(--line)">
  <div class="relative" id="t5-slider">
    <article class="t5-slide active" style="background-image:url('https://images.pexels.com/photos/6801874/pexels-photo-6801874.jpeg?auto=compress&cs=tinysrgb&w=1600')">
      <div class="t5-overlay"></div>
      <div class="t5-content">
        <p class="text-xs uppercase tracking-[0.2em] text-white/80 font-semibold"><?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?> / Premium Service</p>
        <h2 class="mt-4 text-5xl md:text-7xl font-black text-white leading-tight">Everyday Banking That Actually Feels Easy</h2>
        <p class="mt-5 text-base md:text-lg text-slate-100 max-w-2xl">Pay bills, move money, and stay on top of your account without jumping through extra steps.</p>
        <div class="mt-8 flex flex-wrap gap-3">
          <a href="Contact-Us.php" class="px-7 py-3.5 rounded-xl text-sm font-bold text-slate-950" style="background:var(--accent)">Schedule Consultation</a>
          <a href="Online-Banking.php" class="px-7 py-3.5 rounded-xl text-sm font-bold border border-white/40 text-white hover:border-white">Explore Digital Suite</a>
        </div>
      </div>
    </article>
    <article class="t5-slide" style="background-image:url('https://images.pexels.com/photos/7567467/pexels-photo-7567467.jpeg?auto=compress&cs=tinysrgb&w=1600')">
      <div class="t5-overlay"></div>
      <div class="t5-content">
        <p class="text-xs uppercase tracking-[0.2em] text-white/80 font-semibold"><?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?> / Modern Solutions</p>
        <h2 class="mt-4 text-5xl md:text-7xl font-black text-white leading-tight">Tools That Work for Real Business Days</h2>
        <p class="mt-5 text-base md:text-lg text-slate-100 max-w-2xl">From payroll to vendor payments, keep daily operations moving with less friction and better visibility.</p>
        <div class="mt-8 flex flex-wrap gap-3">
          <a href="Business-Checking.php" class="px-7 py-3.5 rounded-xl text-sm font-bold text-slate-950" style="background:var(--accent)">Business Solutions</a>
          <a href="Security-Center.php" class="px-7 py-3.5 rounded-xl text-sm font-bold border border-white/40 text-white hover:border-white">Trust & Security</a>
        </div>
      </div>
    </article>
    <article class="t5-slide" style="background-image:url('https://images.pexels.com/photos/7567434/pexels-photo-7567434.jpeg?auto=compress&cs=tinysrgb&w=1600')">
      <div class="t5-overlay"></div>
      <div class="t5-content">
        <p class="text-xs uppercase tracking-[0.2em] text-white/80 font-semibold"><?= htmlspecialchars((string)($site['name'] ?? 'Bank')) ?> / Dedicated Support</p>
        <h2 class="mt-4 text-5xl md:text-7xl font-black text-white leading-tight">Help From People Who Pick Up the Phone</h2>
        <p class="mt-5 text-base md:text-lg text-slate-100 max-w-2xl">When something is urgent, you get support from a real specialist who can solve it quickly.</p>
        <div class="mt-8 flex flex-wrap gap-3">
          <a href="Contact-Us.php" class="px-7 py-3.5 rounded-xl text-sm font-bold text-slate-950" style="background:var(--accent)">Connect With Experts</a>
          <a href="Locations.php" class="px-7 py-3.5 rounded-xl text-sm font-bold border border-white/40 text-white hover:border-white">Visit Our Branches</a>
        </div>
      </div>
    </article>

    <div class="absolute left-5 right-5 bottom-5 flex items-center justify-between gap-3 z-20">
      <div class="flex items-center gap-2.5" id="t5-slider-dots">
        <button class="t5-dot active" data-slide="0" aria-label="Slide 1"></button>
        <button class="t5-dot" data-slide="1" aria-label="Slide 2"></button>
        <button class="t5-dot" data-slide="2" aria-label="Slide 3"></button>
      </div>
      <div class="flex items-center gap-2">
        <button class="t5-ctrl" id="t5-prev" aria-label="Previous">&#8249;</button>
        <button class="t5-ctrl" id="t5-next" aria-label="Next">&#8250;</button>
      </div>
    </div>
  </div>
</section>

<!-- KEY METRICS SECTION -->
<section class="mb-8 -mt-px border overflow-hidden" style="border-color:var(--line); background:linear-gradient(135deg, color-mix(in srgb,var(--primary2) 95%, #0b1220), color-mix(in srgb,var(--primary) 88%, #1f2937));">
  <div class="grid lg:grid-cols-12 gap-0">
    <div class="lg:col-span-4 px-6 py-10 md:px-10 md:py-14 border-b lg:border-b-0 lg:border-r" style="border-color:rgba(255,255,255,0.1)">
      <p class="text-xs uppercase tracking-[0.2em] text-white/75 font-semibold">Platform Strength</p>
      <h3 class="mt-3 text-3xl md:text-5xl font-black text-white">
        <span class="counter" data-value="50">50</span>B+
      </h3>
      <p class="mt-2 text-sm text-slate-200/90">Customer deposits and managed balances across personal and business accounts.</p>
    </div>
    <div class="lg:col-span-4 px-6 py-10 md:px-10 md:py-14 border-b lg:border-b-0 lg:border-r" style="border-color:rgba(255,255,255,0.1)">
      <p class="text-xs uppercase tracking-[0.2em] text-white/75 font-semibold">Service Availability</p>
      <h3 class="mt-3 text-3xl md:text-5xl font-black text-white">
        <span class="counter" data-value="99">99</span>.<span class="counter" data-value="99">99</span>%
      </h3>
      <p class="mt-2 text-sm text-slate-200/90">Uptime for online banking, cards, and payment services during peak hours.</p>
    </div>
    <div class="lg:col-span-4 px-6 py-10 md:px-10 md:py-14">
      <p class="text-xs uppercase tracking-[0.2em] text-white/75 font-semibold">Expert Team</p>
      <h3 class="mt-3 text-3xl md:text-5xl font-black text-white">
        <span class="counter" data-value="500">500</span>+
      </h3>
      <p class="mt-2 text-sm text-slate-200/90">Team members supporting customers in branches, by phone, and online.</p>
    </div>
  </div>
</section>

<!-- WHY CHOOSE US SECTION -->
<section class="mb-8">
  <div class="text-center mb-8">
    <p class="text-xs uppercase tracking-[0.2em] font-semibold" style="color:var(--muted)">Why Customers Stay</p>
    <h2 class="mt-3 text-3xl md:text-4xl font-black" style="color:var(--primary2)">What Customers Notice Right Away</h2>
    <p class="mt-2 text-sm md:text-base" style="color:var(--muted)">These are the practical improvements people mention after moving their daily banking to us.</p>
  </div>
  <div class="grid md:grid-cols-3 gap-5">
    <article class="rounded-2xl overflow-hidden border p-6" style="border-color:var(--line); background:var(--surface)">
      <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl" style="background:color-mix(in srgb,var(--accent) 24%, #fff)">
        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" style="color:var(--primary2)" stroke-width="2"><path d="M4 7h16M7 12h10M9 17h6" stroke-linecap="round"/><rect x="3" y="4" width="18" height="16" rx="2"/></svg>
      </div>
      <h3 class="text-xl font-bold mb-2" style="color:var(--primary2)">Straightforward Fees</h3>
      <p class="text-sm" style="color:var(--muted)">No hidden surprises. Charges are clearly listed before you open an account or request a service.</p>
    </article>
    <article class="rounded-2xl overflow-hidden border p-6" style="border-color:var(--line); background:var(--surface)">
      <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl" style="background:color-mix(in srgb,var(--accent) 24%, #fff)">
        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" style="color:var(--primary2)" stroke-width="2"><path d="M12 3v6l4 2" stroke-linecap="round"/><circle cx="12" cy="12" r="9"/></svg>
      </div>
      <h3 class="text-xl font-bold mb-2" style="color:var(--primary2)">Faster Onboarding</h3>
      <p class="text-sm" style="color:var(--muted)">Most customers get fully set up quickly, including cards, online access, and transfer tools.</p>
    </article>
    <article class="rounded-2xl overflow-hidden border p-6" style="border-color:var(--line); background:var(--surface)">
      <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl" style="background:color-mix(in srgb,var(--accent) 24%, #fff)">
        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" style="color:var(--primary2)" stroke-width="2"><path d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4z"/><path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <h3 class="text-xl font-bold mb-2" style="color:var(--primary2)">Reliable Security</h3>
      <p class="text-sm" style="color:var(--muted)">Fraud monitoring, login protection, and alerts work quietly in the background every day.</p>
    </article>
    <article class="rounded-2xl overflow-hidden border p-6" style="border-color:var(--line); background:var(--surface)">
      <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl" style="background:color-mix(in srgb,var(--accent) 24%, #fff)">
        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" style="color:var(--primary2)" stroke-width="2"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M10 6h4M9 17h6" stroke-linecap="round"/></svg>
      </div>
      <h3 class="text-xl font-bold mb-2" style="color:var(--primary2)">Smooth Access Anywhere</h3>
      <p class="text-sm" style="color:var(--muted)">Phone, desktop, and branch support feel connected, so you do not have to repeat yourself.</p>
    </article>
    <article class="rounded-2xl overflow-hidden border p-6" style="border-color:var(--line); background:var(--surface)">
      <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl" style="background:color-mix(in srgb,var(--accent) 24%, #fff)">
        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" style="color:var(--primary2)" stroke-width="2"><circle cx="12" cy="8" r="3"/><path d="M5 20c1.5-3 4-5 7-5s5.5 2 7 5" stroke-linecap="round"/></svg>
      </div>
      <h3 class="text-xl font-bold mb-2" style="color:var(--primary2)">Human Support</h3>
      <p class="text-sm" style="color:var(--muted)">When something is urgent, you can speak with a real specialist who handles it end-to-end.</p>
    </article>
    <article class="rounded-2xl overflow-hidden border p-6" style="border-color:var(--line); background:var(--surface)">
      <div class="mb-3 inline-flex h-11 w-11 items-center justify-center rounded-xl" style="background:color-mix(in srgb,var(--accent) 24%, #fff)">
        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" style="color:var(--primary2)" stroke-width="2"><path d="M4 14h4l2-3 3 5 2-3h5" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 19h16M4 5h16" stroke-linecap="round"/></svg>
      </div>
      <h3 class="text-xl font-bold mb-2" style="color:var(--primary2)">Tools for Daily Work</h3>
      <p class="text-sm" style="color:var(--muted)">Payments, reporting, and account controls are built for owners, teams, and busy households.</p>
    </article>
  </div>
</section>

<section class="mb-10 rounded-3xl border overflow-hidden" style="border-color:var(--line); background:linear-gradient(135deg, color-mix(in srgb,var(--primary2) 86%, #0f172a), color-mix(in srgb,var(--primary) 76%, #1e293b));">
  <div class="grid lg:grid-cols-2 gap-6 items-center px-6 md:px-10 py-8 md:py-10">
    <div>
      <p class="text-xs uppercase tracking-[0.2em] text-white/75">Next Step</p>
      <h3 class="mt-2 text-3xl md:text-4xl font-black text-white">Ready to simplify your banking routine?</h3>
      <p class="mt-3 text-sm md:text-base text-slate-200/95">Open an account, connect your payments, and get set up with support that stays with you after day one.</p>
      <div class="mt-5 flex flex-wrap gap-3">
        <a href="<?= htmlspecialchars(($site['login'] ?? 'user')) ?>/register.php" class="px-6 py-3 rounded-lg text-sm font-bold text-slate-950" style="background:var(--accent)">Open an Account</a>
        <a href="Contact-Us.php" class="px-6 py-3 rounded-lg text-sm font-bold text-white border border-white/40">Talk to a Banker</a>
      </div>
    </div>
    <div class="rounded-2xl border border-white/20 bg-white/5 p-5">
      <svg viewBox="0 0 260 160" class="w-full h-auto" aria-hidden="true">
        <defs>
          <linearGradient id="bankingGlow" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="rgba(255,255,255,0.95)"/>
            <stop offset="100%" stop-color="rgba(255,255,255,0.55)"/>
          </linearGradient>
        </defs>
        <rect x="20" y="22" width="220" height="118" rx="14" fill="none" stroke="url(#bankingGlow)" stroke-width="2"/>
        <rect x="36" y="38" width="92" height="86" rx="10" fill="rgba(255,255,255,0.08)" stroke="rgba(255,255,255,0.35)"/>
        <rect x="142" y="38" width="82" height="24" rx="6" fill="rgba(255,255,255,0.12)"/>
        <rect x="142" y="70" width="82" height="12" rx="6" fill="rgba(255,255,255,0.22)"/>
        <rect x="142" y="88" width="58" height="10" rx="5" fill="rgba(255,255,255,0.3)"/>
        <path d="M52 98l18-18 14 12 20-20" fill="none" stroke="rgba(255,255,255,0.85)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="52" cy="98" r="3" fill="rgba(255,255,255,0.95)"/>
        <circle cx="70" cy="80" r="3" fill="rgba(255,255,255,0.95)"/>
        <circle cx="84" cy="92" r="3" fill="rgba(255,255,255,0.95)"/>
        <circle cx="104" cy="72" r="3" fill="rgba(255,255,255,0.95)"/>
      </svg>
    </div>
  </div>
</section>

<!-- COMPREHENSIVE SERVICES -->
<section class="mb-8 pt-8">
  <div class="flex items-end justify-between gap-3 mb-6">
    <div>
      <p class="text-xs uppercase tracking-[0.18em] font-semibold" style="color:var(--muted)">Popular Services</p>
      <h2 class="text-3xl md:text-4xl font-black mt-2" style="color:var(--primary2)">Services Customers Use Most</h2>
    </div>
  </div>
  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
    <article class="rounded-2xl overflow-hidden border" style="border-color:var(--line); background:var(--surface)">
      <img src="https://images.pexels.com/photos/4968391/pexels-photo-4968391.jpeg?auto=compress&cs=tinysrgb&w=900" alt="Business checking" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='https://picsum.photos/900/500?random=11';" class="h-48 w-full object-cover">
      <div class="p-6">
        <p class="text-xs uppercase tracking-[0.16em] font-semibold" style="color:var(--muted)">Business Banking</p>
        <h3 class="mt-2 text-lg font-bold" style="color:var(--ink)">Operate With Confidence</h3>
        <p class="mt-2 text-sm" style="color:var(--muted)">Structured accounts for transactions, payroll, and cash management.</p>
        <a href="Business-Checking.php" class="inline-flex mt-4 text-sm font-bold" style="color:var(--primary)">Explore →</a>
      </div>
    </article>
    <article class="rounded-2xl overflow-hidden border" style="border-color:var(--line); background:var(--surface)">
      <img src="https://images.pexels.com/photos/6802042/pexels-photo-6802042.jpeg?auto=compress&cs=tinysrgb&w=900" alt="Payments" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='https://picsum.photos/900/500?random=12';" class="h-48 w-full object-cover">
      <div class="p-6">
        <p class="text-xs uppercase tracking-[0.16em] font-semibold" style="color:var(--muted)">Payments Control</p>
        <h3 class="mt-2 text-lg font-bold" style="color:var(--ink)">Move Funds With Clarity</h3>
        <p class="mt-2 text-sm" style="color:var(--muted)">Consolidated approvals, receivables tracking, and oversight.</p>
        <a href="Payment-and-Receivables.php" class="inline-flex mt-4 text-sm font-bold" style="color:var(--primary)">Explore →</a>
      </div>
    </article>
    <article class="rounded-2xl overflow-hidden border" style="border-color:var(--line); background:var(--surface)">
      <img src="https://images.pexels.com/photos/7709208/pexels-photo-7709208.jpeg?auto=compress&cs=tinysrgb&w=900" alt="Support" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='https://picsum.photos/900/500?random=13';" class="h-48 w-full object-cover">
      <div class="p-6">
        <p class="text-xs uppercase tracking-[0.16em] font-semibold" style="color:var(--muted)">Concierge Support</p>
        <h3 class="mt-2 text-lg font-bold" style="color:var(--ink)">Excellence On Call</h3>
        <p class="mt-2 text-sm" style="color:var(--muted)">24/7 specialist support for your questions and transactions.</p>
        <a href="Contact-Us.php" class="inline-flex mt-4 text-sm font-bold" style="color:var(--primary)">Explore →</a>
      </div>
    </article>
    <article class="rounded-2xl overflow-hidden border" style="border-color:var(--line); background:var(--surface)">
      <img src="https://images.pexels.com/photos/7821479/pexels-photo-7821479.jpeg?auto=compress&cs=tinysrgb&w=900" alt="Lending" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='https://picsum.photos/900/500?random=14';" class="h-48 w-full object-cover">
      <div class="p-6">
        <p class="text-xs uppercase tracking-[0.16em] font-semibold" style="color:var(--muted)">Lending Solutions</p>
        <h3 class="mt-2 text-lg font-bold" style="color:var(--ink)">Grow Without Friction</h3>
        <p class="mt-2 text-sm" style="color:var(--muted)">Flexible credit lines and financing tailored to your business stage.</p>
        <a href="Business-Loans-and-Lines-of-Credit.php" class="inline-flex mt-4 text-sm font-bold" style="color:var(--primary)">Explore →</a>
      </div>
    </article>
    <article class="rounded-2xl overflow-hidden border" style="border-color:var(--line); background:var(--surface)">
      <img src="https://images.pexels.com/photos/3183197/pexels-photo-3183197.jpeg?auto=compress&cs=tinysrgb&w=900" alt="Treasury" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='https://picsum.photos/900/500?random=15';" class="h-48 w-full object-cover">
      <div class="p-6">
        <p class="text-xs uppercase tracking-[0.16em] font-semibold" style="color:var(--muted)">Treasury Management</p>
        <h3 class="mt-2 text-lg font-bold" style="color:var(--ink)">Optimize Every Dollar</h3>
        <p class="mt-2 text-sm" style="color:var(--muted)">Advanced tools for liquidity management and investment strategies.</p>
        <a href="Money-Market.php" class="inline-flex mt-4 text-sm font-bold" style="color:var(--primary)">Explore →</a>
      </div>
    </article>
    <article class="rounded-2xl overflow-hidden border" style="border-color:var(--line); background:var(--surface)">
      <img src="https://images.pexels.com/photos/164571/pexels-photo-164571.jpeg?auto=compress&cs=tinysrgb&w=900" alt="Cards" loading="lazy" referrerpolicy="no-referrer" onerror="this.onerror=null;this.src='https://picsum.photos/900/500?random=16';" class="h-48 w-full object-cover">
      <div class="p-6">
        <p class="text-xs uppercase tracking-[0.16em] font-semibold" style="color:var(--muted)">Card Services</p>
        <h3 class="mt-2 text-lg font-bold" style="color:var(--ink)">Control At Your Fingertips</h3>
        <p class="mt-2 text-sm" style="color:var(--muted)">Debit and credit solutions with advanced fraud protection.</p>
        <a href="Debit-Cards.php" class="inline-flex mt-4 text-sm font-bold" style="color:var(--primary)">Explore →</a>
      </div>
    </article>
  </div>
</section>

<!-- TESTIMONIALS SECTION -->
<section class="mb-8">
  <div class="text-center mb-8">
    <p class="text-xs uppercase tracking-[0.2em] font-semibold" style="color:var(--muted)">Trusted By Businesses</p>
    <h2 class="mt-3 text-3xl md:text-4xl font-black" style="color:var(--primary2)">What Our Clients Say</h2>
  </div>
  <div class="grid md:grid-cols-3 gap-5">
    <div class="rounded-2xl border p-6" style="border-color:var(--line); background:var(--surface)">
      <div class="flex items-center gap-1 mb-4">★★★★★</div>
      <p class="text-sm mb-4" style="color:var(--ink)">Our payroll and vendor payments are much easier to manage now, and support answers quickly when we need help.</p>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full" style="background:var(--primary2)"></div>
        <div class="text-sm">
          <p class="font-bold" style="color:var(--primary2)">Sarah Mitchell</p>
          <p style="color:var(--muted)">CEO, Growth Digital</p>
        </div>
      </div>
    </div>
    <div class="rounded-2xl border p-6" style="border-color:var(--line); background:var(--surface)">
      <div class="flex items-center gap-1 mb-4">★★★★★</div>
      <p class="text-sm mb-4" style="color:var(--ink)">We moved our daily banking here and immediately got better visibility across transactions and cash flow.</p>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full" style="background:var(--primary)"></div>
        <div class="text-sm">
          <p class="font-bold" style="color:var(--primary2)">James Rodriguez</p>
          <p style="color:var(--muted)">CTO, TechFlow Inc</p>
        </div>
      </div>
    </div>
    <div class="rounded-2xl border p-6" style="border-color:var(--line); background:var(--surface)">
      <div class="flex items-center gap-1 mb-4">★★★★★</div>
      <p class="text-sm mb-4" style="color:var(--ink)">Branch staff and online support have both been reliable. It feels like a bank that actually knows our business.</p>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full" style="background:var(--accent)"></div>
        <div class="text-sm">
          <p class="font-bold" style="color:var(--primary2)">Emily Chen</p>
          <p style="color:var(--muted)">CFO, Strategic Ventures</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA SECTION -->
<section class="mb-8 rounded-3xl border overflow-hidden p-8 md:p-12 text-center" style="border-color:var(--line); background:linear-gradient(135deg, var(--primary2), var(--primary))">
  <p class="text-xs uppercase tracking-[0.2em] text-white/85 font-semibold">Ready to Get Started?</p>
  <h2 class="mt-4 text-3xl md:text-4xl font-black text-white">Open an Account With Confidence</h2>
  <p class="mt-4 text-base text-white/90 max-w-2xl mx-auto">Choose personal or business banking and get support from a team that stays with you after signup.</p>
  <div class="mt-8 flex flex-wrap gap-4 justify-center">
    <a href="Contact-Us.php" class="px-8 py-4 rounded-xl text-sm font-bold text-slate-950" style="background:var(--accent)">Schedule Demo</a>
    <a href="Online-Banking.php" class="px-8 py-4 rounded-xl text-sm font-bold border border-white text-white" style="border-color:rgba(255,255,255,0.4)">Learn More</a>
  </div>
</section>

<style>
#t5-slider {
  position: relative;
  width: 100%;
  background: var(--surface);
}
.t5-slide {
  display: none;
  position: relative;
  width: 100%;
  height: 520px;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}
@media (max-width: 768px) {
  .t5-slide { height: 430px; }
}
.t5-slide.active { display: flex; align-items: center; justify-content: center; }
.t5-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(0,0,0,0.55), rgba(0,0,0,0.35));
  z-index: 1;
}
.t5-content {
  position: relative;
  z-index: 2;
  padding: 3rem 2rem;
  text-align: center;
  max-width: 900px;
  margin: 0 auto;
}
@media (max-width: 768px) {
  .t5-content { padding: 2rem 1.5rem; }
}
.t5-content h2 {
  letter-spacing: -0.015em;
  animation: slideInUp 0.8s ease-out;
}
@keyframes slideInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
#t5-slider-dots {
  animation: fadeIn 0.6s ease-out 0.3s both;
}
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
.t5-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255,255,255,0.5);
  border: none;
  cursor: pointer;
  transition: all 300ms;
}
.t5-dot.active {
  background: white;
  width: 28px;
  border-radius: 5px;
}
.t5-ctrl {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.15);
  border: 1px solid rgba(255,255,255,0.3);
  color: white;
  border-radius: 8px;
  font-size: 20px;
  cursor: pointer;
  transition: all 250ms;
}
.t5-ctrl:hover {
  background: rgba(255,255,255,0.25);
  border-color: rgba(255,255,255,0.5);
}
.counter {
  font-variant-numeric: tabular-nums;
}
</style>

<script>
(function() {
  const slider = document.getElementById('t5-slider');
  if (!slider) return;

  const slides = slider.querySelectorAll('.t5-slide');
  const dots = slider.querySelectorAll('.t5-dot');
  const prevBtn = document.getElementById('t5-prev');
  const nextBtn = document.getElementById('t5-next');
  let currentIndex = 0;
  let interval;

  function showSlide(index) {
    slides.forEach((s, i) => {
      s.classList.toggle('active', i === index);
      if (dots[i]) dots[i].classList.toggle('active', i === index);
    });
    currentIndex = index;
  }

  function nextSlide() {
    showSlide((currentIndex + 1) % slides.length);
  }

  function prevSlide() {
    showSlide((currentIndex - 1 + slides.length) % slides.length);
  }

  function startAutoRotate() {
    interval = setInterval(nextSlide, 5500);
  }

  function resetAutoRotate() {
    clearInterval(interval);
    startAutoRotate();
  }

  if (prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); resetAutoRotate(); });
  if (nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); resetAutoRotate(); });
  
  dots.forEach((dot, index) => {
    dot.addEventListener('click', () => { showSlide(index); resetAutoRotate(); });
  });

  startAutoRotate();

  // Mutation observer to handle page updates
  new MutationObserver(() => {
    const activeSlides = slider.querySelectorAll('.t5-slide.active');
    if (activeSlides.length === 0 && slides.length > 0) {
      showSlide(0);
    }
  }).observe(slider, { childList: true, subtree: true });
})();

// Counter animation
document.querySelectorAll('.counter').forEach(el => {
  const target = parseInt(el.getAttribute('data-value'), 10);
  let current = 0;
  const increment = Math.ceil(target / 50);
  const timer = setInterval(() => {
    current = Math.min(current + increment, target);
    el.textContent = current;
    if (current >= target) clearInterval(timer);
  }, 30);
});
</script>
