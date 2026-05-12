<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Efiewura — Find Your Home in Ghana</title>
<meta name="description" content="Browse verified rental properties across Ghana. Apartments, houses, studios, and commercial spaces from trusted landlords.">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: { extend: { colors: {
    brand:  { DEFAULT:'#064E3B', 50:'#ECFDF5', 100:'#D1FAE5', 600:'#059669', 700:'#047857', 800:'#065F46', 900:'#064E3B' },
    accent: { DEFAULT:'#D97706', 400:'#FBBF24', 500:'#F59E0B', 600:'#D97706' },
    cream:  { DEFAULT:'#FAFAF7', dark:'#F2EDE4' },
  }}}
}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  *{font-family:'Inter',sans-serif}
  .font-display{font-family:'Cormorant Garamond',serif}

  ::-webkit-scrollbar{width:5px}
  ::-webkit-scrollbar-track{background:#f1f5f9}
  ::-webkit-scrollbar-thumb{background:#064E3B;border-radius:3px}

  .hero-bg{background:linear-gradient(140deg,#022c1e 0%,#064E3B 45%,#065F46 100%)}
  .hero-mesh{background-image:radial-gradient(circle at 25% 40%,rgba(52,211,153,0.12) 0%,transparent 50%),radial-gradient(circle at 75% 70%,rgba(6,78,59,0.4) 0%,transparent 50%)}

  .property-card{transition:transform .25s ease,box-shadow .25s ease}
  .property-card:hover{transform:translateY(-5px);box-shadow:0 24px 48px rgba(0,0,0,.13)}
  .card-img-wrap{overflow:hidden}
  .card-img{transition:transform .4s ease}
  .property-card:hover .card-img{transform:scale(1.06)}

  .card-overlay{opacity:0;transition:opacity .25s ease;background:linear-gradient(to top,rgba(6,78,59,.9) 0%,transparent 60%)}
  .property-card:hover .card-overlay{opacity:1}

  .fade-up{opacity:0;transform:translateY(24px);transition:opacity .65s ease,transform .65s ease}
  .fade-up.in{opacity:1;transform:translateY(0)}

  .glass{background:rgba(255,255,255,.96);backdrop-filter:blur(20px)}

  .tab-pill{transition:all .15s;border:1.5px solid #e5e7eb;color:#6b7280}
  .tab-pill:hover{border-color:#064E3B;color:#064E3B}
  .tab-pill.active{background:#064E3B;color:#fff;border-color:#064E3B}

  #mobile-nav{max-height:0;overflow:hidden;transition:max-height .3s ease,opacity .3s ease;opacity:0}
  #mobile-nav.open{max-height:400px;opacity:1}

  .amenity-pill{font-size:.6rem;padding:2px 7px;border-radius:99px;background:#ECFDF5;color:#065F46;border:1px solid #d1fae5;white-space:nowrap}

  .step-card::before{content:attr(data-num);position:absolute;top:.75rem;right:1rem;font-family:'Cormorant Garamond',serif;font-size:3rem;font-weight:700;color:#e5e7eb;line-height:1}

  .stat-card{transition:transform .2s ease,box-shadow .2s ease}
  .stat-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.08)}

  input:focus,select:focus,textarea:focus{outline:none;box-shadow:0 0 0 3px rgba(6,78,59,.12);border-color:#064E3B!important}
</style>
</head>
<body class="bg-cream">

<!-- ═══════ NAVBAR ═══════ -->
<nav id="navbar" class="fixed top-0 inset-x-0 z-50 transition-all duration-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">

      <!-- Logo -->
      <a href="/" class="flex items-center gap-2.5 group">
        <div class="w-8 h-8 bg-brand rounded-xl flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
          <svg class="w-4.5 h-4.5 text-white w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 22V12h6v10"/>
          </svg>
        </div>
        <span class="text-xl font-bold text-brand tracking-tight">efiewura</span>
      </a>

      <!-- Desktop links -->
      <div class="hidden md:flex items-center gap-7">
        <a href="#browse"       class="nav-link text-sm font-medium text-gray-600 hover:text-brand transition-colors">Browse</a>
        <a href="#how-it-works" class="nav-link text-sm font-medium text-gray-600 hover:text-brand transition-colors">How It Works</a>
        <a href="#about"        class="nav-link text-sm font-medium text-gray-600 hover:text-brand transition-colors">About</a>
        <a href="#contact"      class="nav-link text-sm font-medium text-gray-600 hover:text-brand transition-colors">Contact</a>
      </div>

      <!-- CTA -->
      <div class="hidden md:flex items-center gap-3">
        @if(app()->environment('local'))
        <a href="/docs" class="text-xs text-gray-400 hover:text-gray-600 transition-colors font-medium">API</a>
        @endif
        <a href="#" class="text-sm font-semibold text-brand hover:text-brand-800 transition-colors px-1">Log In</a>
        <a href="#" class="bg-brand text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-brand-800 transition-all shadow-sm hover:shadow-md">Get Started</a>
      </div>

      <!-- Hamburger -->
      <button id="ham-btn" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
        <svg id="ham-open"  class="w-5 h-5 text-gray-700"                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        <svg id="ham-close" class="w-5 h-5 text-gray-700 hidden"         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Mobile nav -->
    <div id="mobile-nav" class="md:hidden border-t border-gray-100 bg-white">
      <div class="py-5 px-2 flex flex-col gap-1">
        @foreach([['#browse','Browse Properties'],['#how-it-works','How It Works'],['#about','About Us'],['#contact','Contact']] as [$href,$label])
        <a href="{{ $href }}" class="mobile-nav-link block px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-brand/5 hover:text-brand rounded-lg transition-colors">{{ $label }}</a>
        @endforeach
        <hr class="my-2 border-gray-100">
        <a href="#" class="block px-4 py-2.5 text-sm font-semibold text-brand">Log In</a>
        <a href="#" class="block mx-2 mt-1 bg-brand text-white text-sm font-semibold px-4 py-2.5 rounded-xl text-center">Get Started</a>
      </div>
    </div>
  </div>
</nav>

<!-- ═══════ HERO ═══════ -->
<section class="hero-bg hero-mesh relative min-h-[95vh] flex items-center pt-16 overflow-hidden">

  <!-- Decorative blobs -->
  <div class="pointer-events-none absolute inset-0 overflow-hidden">
    <div class="absolute -top-20 -right-20 w-96 h-96 rounded-full bg-emerald-500/10 blur-3xl"></div>
    <div class="absolute bottom-0 -left-20 w-[32rem] h-[32rem] rounded-full bg-emerald-900/40 blur-3xl"></div>
    <div class="absolute top-1/2 left-1/2 w-64 h-64 rounded-full bg-teal-500/8 blur-2xl"></div>
  </div>

  <!-- Dot grid -->
  <div class="pointer-events-none absolute inset-0 opacity-[0.07]">
    <svg width="100%" height="100%"><defs><pattern id="grid" x="0" y="0" width="32" height="32" patternUnits="userSpaceOnUse"><circle cx="1.5" cy="1.5" r="1.5" fill="white"/></pattern></defs><rect width="100%" height="100%" fill="url(#grid)"/></svg>
  </div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 w-full">
    <div class="grid lg:grid-cols-2 gap-12 items-center">

      <!-- Left: Headline + Search -->
      <div>
        <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 text-brand text-xs font-semibold px-4 py-1.5 rounded-full mb-7">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
          Ghana's Most Trusted Property Platform
        </div>

        <h1 class="font-display text-5xl sm:text-6xl lg:text-[4.25rem] font-bold text-white leading-[1.04] mb-6">
          Find Your<br>
          <span class="text-accent-400">Perfect Home</span><br>
          in Ghana.
        </h1>

        <p class="text-brand text-lg leading-relaxed mb-10 max-w-lg">
          Browse {{ $stats['available'] }}+ verified properties from trusted landlords across Accra, Kumasi, Tema, and beyond. No agents. No hidden fees.
        </p>

        <!-- Search card -->
        <div class="glass rounded-2xl p-3 shadow-2xl border border-white/30">
          <form method="GET" action="/#browse">
            <div class="flex flex-col sm:flex-row gap-2">
              <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0L6.343 16.657A8 8 0 1117.657 16.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <input type="text" name="city" value="{{ request('city') }}" placeholder="City or neighbourhood…"
                       class="w-full pl-9 pr-3 py-3 text-sm text-gray-800 placeholder-gray-400 bg-transparent rounded-xl border border-transparent focus:border-brand/30">
              </div>
              <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <select name="category" class="w-full pl-9 pr-3 py-3 text-sm text-gray-700 bg-transparent appearance-none rounded-xl border border-transparent focus:border-brand/30">
                  <option value="">All Types</option>
                  @foreach($categories as $cat)
                  <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <select name="max_price" class="w-full pl-9 pr-3 py-3 text-sm text-gray-700 bg-transparent appearance-none rounded-xl border border-transparent focus:border-brand/30">
                  <option value="">Any Budget</option>
                  <option value="1500" {{ request('max_price') == '1500' ? 'selected':'' }}>Up to GHS 1,500</option>
                  <option value="2500" {{ request('max_price') == '2500' ? 'selected':'' }}>Up to GHS 2,500</option>
                  <option value="4000" {{ request('max_price') == '4000' ? 'selected':'' }}>Up to GHS 4,000</option>
                  <option value="6000" {{ request('max_price') == '6000' ? 'selected':'' }}>Up to GHS 6,000</option>
                  <option value="10000" {{ request('max_price') == '10000' ? 'selected':'' }}>Up to GHS 10,000</option>
                </select>
              </div>
              <button type="submit" class="bg-brand hover:bg-brand-800 active:scale-95 text-white font-semibold px-5 py-3 rounded-xl text-sm transition-all shadow-md whitespace-nowrap flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Search
              </button>
            </div>
          </form>
        </div>

        <!-- Trust badges -->
        <div class="flex flex-wrap gap-5 mt-7">
          @foreach(['Verified Landlords','No Agent Fees','Instant Notifications','Secure Bookings'] as $badge)
          <div class="flex items-center gap-1.5 text-brand text-xs font-medium">
            <svg class="w-3.5 h-3.5 text-emerald-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            {{ $badge }}
          </div>
          @endforeach
        </div>
      </div>

      <!-- Right: floating property preview cards -->
      <div class="hidden lg:flex flex-col gap-3 max-w-xs ml-auto">
        @foreach($featuredProperties as $fp)
        <div class="glass rounded-2xl p-3 shadow-xl flex gap-3 items-center fade-up border border-white/40" style="transition-delay:{{ $loop->index * 120 }}ms">
          <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 bg-emerald-100">
            @if(!empty($fp->images))
            <img src="{{ $fp->images[0] }}&w=120&h=120&fit=crop" class="w-full h-full object-cover" alt="" loading="lazy">
            @else
            <div class="w-full h-full bg-gradient-to-br from-emerald-200 to-teal-300"></div>
            @endif
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-gray-900 truncate leading-snug">{{ $fp->title }}</p>
            <p class="text-[0.65rem] text-gray-400 truncate mt-0.5">{{ $fp->city }}</p>
            <p class="text-sm font-bold text-brand mt-1">GHS {{ number_format($fp->price) }}<span class="text-[0.6rem] font-normal text-gray-400">/mo</span></p>
          </div>
          <div class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0 shadow-[0_0_6px_#34d399]"></div>
        </div>
        @endforeach
        <div class="glass rounded-2xl p-4 border border-white/40 fade-up" style="transition-delay:400ms">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A12 12 0 013 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">{{ $stats['available'] }} Available Now</p>
              <p class="text-xs text-gray-400">Across {{ $stats['cities'] }} cities & areas</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bottom wave -->
  <div class="absolute bottom-0 inset-x-0 pointer-events-none">
    <svg viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" class="w-full h-14 sm:h-20">
      <path d="M0 80L60 70C120 60 240 40 360 38C480 36 600 52 720 58C840 64 960 58 1080 48C1200 38 1320 28 1380 24L1440 20V80H0Z" fill="#FAFAF7"/>
    </svg>
  </div>
</section>

<!-- ═══════ STATS ═══════ -->
<section class="bg-cream pt-2 pb-14">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
      @php
      $statItems = [
        ['n' => number_format($stats['properties']).'+', 'label' => 'Properties Listed',    'path' => 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z', 'color' => 'text-brand bg-brand/10'],
        ['n' => $stats['cities'].'',                     'label' => 'Cities & Districts',   'path' => 'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0L6.343 16.657A8 8 0 1117.657 16.657z', 'color' => 'text-blue-600 bg-blue-50'],
        ['n' => number_format($stats['available']).'',   'label' => 'Available Now',        'path' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-emerald-600 bg-emerald-50'],
        ['n' => '4.9★',                                  'label' => 'Average Rating',       'path' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => 'text-amber-600 bg-amber-50'],
      ];
      @endphp
      @foreach($statItems as $s)
      <div class="stat-card bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4 fade-up">
        <div class="w-11 h-11 rounded-xl {{ $s['color'] }} flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['path'] }}"/>
          </svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900 font-display leading-none">{{ $s['n'] }}</p>
          <p class="text-xs text-gray-500 mt-0.5 font-medium">{{ $s['label'] }}</p>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ═══════ BROWSE PROPERTIES ═══════ -->
<section id="browse" class="py-16 bg-white scroll-mt-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header row -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-5 mb-8 fade-up">
      <div>
        <p class="text-brand text-xs font-bold uppercase tracking-widest mb-2">Listings</p>
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 font-display">Featured Properties</h2>
      </div>

      <!-- Category tabs -->
      <div id="cat-tabs" class="flex flex-wrap gap-2">
        <button class="tab-pill active text-xs font-semibold px-4 py-2 rounded-full" data-cat="all">All</button>
        @foreach($categories as $c)
        <button class="tab-pill text-xs font-semibold px-4 py-2 rounded-full" data-cat="{{ $c->id }}">{{ $c->name }}</button>
        @endforeach
      </div>
    </div>

    <!-- Filter bar -->
    <div class="flex flex-col sm:flex-row gap-3 mb-8 fade-up">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0118 0z"/></svg>
        <input id="city-input" type="text" placeholder="Filter by city…" value="{{ request('city') }}"
               class="pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 w-full sm:w-52">
      </div>
      <select id="price-select" class="py-2.5 px-4 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-600">
        <option value="">All Prices</option>
        <option value="1500">Up to GHS 1,500</option>
        <option value="2500">Up to GHS 2,500</option>
        <option value="4000">Up to GHS 4,000</option>
        <option value="6000">Up to GHS 6,000</option>
        <option value="10000">Up to GHS 10,000</option>
      </select>
      <select id="sort-select" class="py-2.5 px-4 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-600">
        <option value="popular">Most Popular</option>
        <option value="price_asc">Price: Low → High</option>
        <option value="price_desc">Price: High → Low</option>
        <option value="newest">Newest First</option>
      </select>
      <p class="text-sm text-gray-400 flex items-center gap-1 sm:ml-auto">
        <span id="count">{{ $properties->total() }}</span>&nbsp;properties found
      </p>
    </div>

    <!-- Grid -->
    <div id="prop-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

      @forelse($properties as $p)
      <article class="property-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden fade-up"
               data-cat="{{ $p->property_category_id }}"
               data-city="{{ strtolower($p->city) }}"
               data-price="{{ $p->price }}"
               data-views="{{ $p->views_count }}"
               data-pub="{{ $p->published_at?->timestamp ?? 0 }}">

        <!-- Image -->
        <div class="card-img-wrap relative h-52">
          @if(!empty($p->images))
          <img src="{{ $p->images[0] }}" alt="{{ $p->title }}" class="card-img w-full h-full object-cover" loading="lazy">
          @else
          <div class="w-full h-full bg-gradient-to-br from-emerald-100 to-teal-200 flex items-center justify-center">
            <svg class="w-14 h-14 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
          </div>
          @endif

          <!-- Hover overlay with CTA -->
          <div class="card-overlay absolute inset-0 flex items-end p-4">
            <div class="flex gap-2 w-full">
              <a href="#" class="flex-1 text-center bg-white text-brand text-xs font-bold py-2 rounded-xl hover:bg-gray-50 transition-colors">View Details</a>
              <a href="#" class="flex-1 text-center bg-accent text-white text-xs font-bold py-2 rounded-xl hover:bg-accent-600 transition-colors">Book Now</a>
            </div>
          </div>

          <!-- Badges -->
          <div class="absolute top-3 left-3 flex gap-1.5">
            <span class="text-[0.6rem] font-bold px-2 py-0.5 rounded-full {{ $p->availability_status === 'available' ? 'bg-emerald-500 text-white' : 'bg-orange-400 text-white' }}">
              {{ ucfirst($p->availability_status) }}
            </span>
            @if($p->furnished)
            <span class="text-[0.6rem] font-semibold px-2 py-0.5 rounded-full bg-white/90 text-gray-700 backdrop-blur-sm">Furnished</span>
            @endif
          </div>

          <!-- Views -->
          <div class="absolute top-3 right-3 flex items-center gap-1 bg-black/40 backdrop-blur-sm text-white text-[0.6rem] px-2 py-1 rounded-full">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            {{ number_format($p->views_count) }}
          </div>
        </div>

        <!-- Body -->
        <div class="p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[0.65rem] font-bold uppercase tracking-wide text-brand bg-brand/8 px-2.5 py-0.5 rounded-full border border-brand/15">
              {{ $p->category?->name ?? 'Property' }}
            </span>
            @if($p->pets_allowed)
            <span class="text-[0.6rem] text-gray-400">🐾 Pets OK</span>
            @endif
          </div>

          <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-1.5 line-clamp-2">{{ $p->title }}</h3>

          <div class="flex items-center gap-1 text-xs text-gray-400 mb-3">
            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0L6.343 16.657A8 8 0 1117.657 16.657z"/></svg>
            {{ $p->city }}
          </div>

          @if(!empty($p->amenities))
          <div class="flex flex-wrap gap-1 mb-3">
            @foreach(array_slice($p->amenities, 0, 3) as $am)
            <span class="amenity-pill">{{ $am }}</span>
            @endforeach
            @if(count($p->amenities) > 3)
            <span class="amenity-pill !bg-gray-100 !text-gray-500 !border-gray-200">+{{ count($p->amenities) - 3 }}</span>
            @endif
          </div>
          @endif

          <div class="flex items-center justify-between pt-3 border-t border-gray-100">
            <div class="leading-none">
              <span class="text-lg font-bold text-gray-900">GHS {{ number_format($p->price) }}</span>
              <span class="text-xs text-gray-400 ml-0.5">/mo</span>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-400">
              @if($p->bedrooms)
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                {{ $p->bedrooms }}
              </span>
              @endif
              @if($p->bathrooms)
              <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                {{ $p->bathrooms }}
              </span>
              @endif
              @if($p->size_sqm)
              <span>{{ (int)$p->size_sqm }}m²</span>
              @endif
            </div>
          </div>
        </div>
      </article>
      @empty
      <div class="col-span-3 py-24 text-center">
        <div class="w-20 h-20 bg-gray-100 rounded-2xl mx-auto mb-5 flex items-center justify-center">
          <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-500 mb-2">No properties match your search</h3>
        <p class="text-sm text-gray-400 mb-5">Try adjusting your filters or search a different city.</p>
        <a href="/" class="text-brand font-semibold text-sm hover:underline">Clear all filters</a>
      </div>
      @endforelse
    </div>

    @if($properties->hasPages())
    <div class="mt-10 flex justify-center">{{ $properties->links() }}</div>
    @endif
  </div>
</section>

<!-- ═══════ HOW IT WORKS ═══════ -->
<section id="how-it-works" class="py-20 bg-cream-dark scroll-mt-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14 fade-up">
      <p class="text-brand text-xs font-bold uppercase tracking-widest mb-3">Process</p>
      <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 font-display mb-4">Renting Made Simple</h2>
      <p class="text-gray-500 max-w-lg mx-auto text-sm leading-relaxed">From search to move-in in four easy steps. No paperwork, no agents — a direct connection with verified landlords.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
      @php
      $steps = [
        ['num'=>'01','title'=>'Create Account','desc'=>'Sign up as a tenant or landlord in under 60 seconds. Your profile is verified within 24 hours.','icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z','grad'=>'from-teal-500 to-emerald-600'],
        ['num'=>'02','title'=>'Browse & Filter','desc'=>'Search by location, type, price, and amenities. Shortlist your favourites and compare.','icon'=>'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0118 0z','grad'=>'from-brand-600 to-brand'],
        ['num'=>'03','title'=>'Request a Booking','desc'=>'Submit a booking request directly to the landlord — no middlemen. Get notified instantly.','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','grad'=>'from-amber-500 to-orange-500'],
        ['num'=>'04','title'=>'Move In','desc'=>'Receive your lease details and move-in instructions. Efiewura handles all reminders automatically.','icon'=>'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z','grad'=>'from-violet-500 to-purple-600'],
      ];
      @endphp
      @foreach($steps as $step)
      <div class="step-card relative bg-white rounded-2xl p-6 shadow-sm border border-gray-100 fade-up" data-num="{{ $step['num'] }}" style="transition-delay:{{ $loop->index * 80 }}ms">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $step['grad'] }} flex items-center justify-center mb-5 shadow">
          <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
          </svg>
        </div>
        <h3 class="font-bold text-gray-900 mb-2 text-sm">{{ $step['title'] }}</h3>
        <p class="text-xs text-gray-500 leading-relaxed">{{ $step['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ═══════ ABOUT ═══════ -->
<section id="about" class="py-20 bg-white scroll-mt-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-16 items-center">

      <!-- Image mosaic -->
      <div class="relative fade-up">
        <div class="grid grid-cols-2 gap-4">
          <img src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=500&auto=format&fit=crop"
               class="rounded-2xl w-full h-64 object-cover shadow-lg" alt="Luxury property" loading="lazy">
          <div class="flex flex-col gap-4">
            <img src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=500&auto=format&fit=crop"
                 class="rounded-2xl w-full h-[118px] object-cover shadow-lg" alt="House" loading="lazy">
            <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=500&auto=format&fit=crop"
                 class="rounded-2xl w-full h-[118px] object-cover shadow-lg" alt="Studio" loading="lazy">
          </div>
        </div>
        <!-- Floating badge -->
        <div class="absolute -bottom-4 -right-4 bg-white rounded-2xl shadow-xl p-4 border border-gray-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-brand rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A12 12 0 013 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
              <p class="text-sm font-bold text-gray-900">KYC Verified</p>
              <p class="text-xs text-gray-400">All landlords screened</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Text -->
      <div class="fade-up">
        <p class="text-brand text-xs font-bold uppercase tracking-widest mb-4">About Efiewura</p>
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 font-display mb-6 leading-snug">
          Ghana's Trusted<br>Property Platform
        </h2>
        <p class="text-gray-600 leading-relaxed mb-4 text-sm">
          <strong class="text-gray-900">Efiewura</strong> — meaning <em>"home"</em> in Twi — was built to fix Ghana's broken rental market. We connect tenants directly with verified landlords, cutting out exploitative agents, hidden fees, and slow paper-based processes.
        </p>
        <p class="text-gray-600 leading-relaxed mb-8 text-sm">
          Our platform manages the full tenancy lifecycle: from discovery and booking to lease reminders, payment notifications, and move-out coordination — all automated, so landlords and tenants can focus on what matters.
        </p>

        <div class="grid grid-cols-2 gap-4 mb-8">
          @foreach([['Transparency','No hidden fees or commissions'],['Verification','Every landlord is KYC-verified'],['Automation','Smart reminders for rent & leases'],['Security','End-to-end encrypted data']] as [$t,$d])
          <div class="flex gap-2.5">
            <div class="w-5 h-5 rounded-full bg-brand/10 flex items-center justify-center flex-shrink-0 mt-0.5">
              <svg class="w-2.5 h-2.5 text-brand" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-gray-900">{{ $t }}</p>
              <p class="text-xs text-gray-400">{{ $d }}</p>
            </div>
          </div>
          @endforeach
        </div>

        <a href="#contact" class="inline-flex items-center gap-2 bg-brand text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-800 transition-all shadow-sm text-sm">
          Get in Touch
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ TESTIMONIALS ═══════ -->
<section class="hero-bg py-20 relative overflow-hidden">
  <div class="absolute inset-0 opacity-[0.06] pointer-events-none">
    <svg width="100%" height="100%"><defs><pattern id="dots2" x="0" y="0" width="28" height="28" patternUnits="userSpaceOnUse"><circle cx="1.5" cy="1.5" r="1.5" fill="white"/></pattern></defs><rect width="100%" height="100%" fill="url(#dots2)"/></svg>
  </div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14 fade-up">
      <p class="text-emerald-400 text-xs font-bold uppercase tracking-widest mb-3">Testimonials</p>
      <h2 class="text-3xl sm:text-4xl font-bold text-white font-display">What Our Users Say</h2>
    </div>
    <div class="grid md:grid-cols-3 gap-5">
      @php
      $reviews = [
        ['name'=>'Akosua Mensah','role'=>'Tenant — East Legon','init'=>'AM','text'=>'Found my dream apartment in 3 days. The process was completely transparent — no agent fees, no surprises. I would recommend Efiewura to every Ghanaian looking for a home.'],
        ['name'=>'Kwame Darko',  'role'=>'Landlord — Kumasi',  'init'=>'KD','text'=>'Managing my four properties has never been easier. Automatic rent reminders alone have reduced my late payments by 90%. The platform basically runs itself.'],
        ['name'=>'Ama Owusu',    'role'=>'Tenant — Tema',      'init'=>'AO','text'=>'I was worried about finding a place near Tema Harbour. Efiewura had listings I couldn\'t find anywhere else, and the landlord verification gave me total peace of mind.'],
      ];
      @endphp
      @foreach($reviews as $r)
      <div class="fade-up bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6" style="transition-delay:{{ $loop->index * 100 }}ms">
        <div class="flex gap-0.5 mb-4">
          @for($i=0;$i<5;$i++)
          <svg class="w-4 h-4 text-accent-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
          @endfor
        </div>
        <p class="text-white/80 text-sm leading-relaxed mb-5 italic">"{{ $r['text'] }}"</p>
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white text-xs font-bold">{{ $r['init'] }}</div>
          <div>
            <p class="text-white text-sm font-semibold">{{ $r['name'] }}</p>
            <p class="text-brand/70 text-xs">{{ $r['role'] }}</p>
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>

<!-- ═══════ LANDLORD CTA ═══════ -->
<section class="py-20 bg-cream-dark">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-3xl shadow-xl overflow-hidden fade-up">
      <div class="grid lg:grid-cols-5">
        <div class="lg:col-span-3 p-10 lg:p-14">
          <span class="inline-block bg-accent/10 text-accent-600 text-xs font-bold px-3 py-1 rounded-full mb-5 uppercase tracking-wide">For Landlords</span>
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 font-display mb-5 leading-snug">List Your Property.<br>Reach Thousands of Tenants.</h2>
          <p class="text-gray-500 leading-relaxed mb-8 text-sm max-w-lg">Join verified landlords on Efiewura. List in minutes, receive booking requests instantly, and let our platform handle rent reminders, lease notifications, and tenant communication automatically.</p>
          <div class="flex flex-wrap gap-3">
            <a href="#" class="bg-brand text-white font-semibold px-6 py-3 rounded-xl hover:bg-brand-800 transition-all shadow-sm text-sm">List Your Property — Free</a>
            <a href="#how-it-works" class="border border-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl hover:border-brand hover:text-brand transition-colors text-sm">Learn More</a>
          </div>
          <div class="grid grid-cols-3 gap-6 mt-10 pt-10 border-t border-gray-100">
            @foreach([['Free','Listing'],['48h','Verification'],['24/7','Support']] as [$val,$lbl])
            <div>
              <p class="text-2xl font-bold text-brand font-display">{{ $val }}</p>
              <p class="text-xs text-gray-400 uppercase tracking-wide mt-0.5">{{ $lbl }}</p>
            </div>
            @endforeach
          </div>
        </div>
        <div class="lg:col-span-2 relative h-60 lg:h-auto bg-gradient-to-br from-brand to-emerald-600 overflow-hidden">
          <img src="https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=600&auto=format&fit=crop"
               class="absolute inset-0 w-full h-full object-cover opacity-40 mix-blend-multiply" alt="" loading="lazy">
          <div class="absolute inset-0 flex items-end p-7">
            <div class="bg-white/15 backdrop-blur-sm border border-white/30 rounded-2xl p-4 text-white w-full">
              <p class="text-sm font-semibold mb-1">✓ Verified within 48 hours</p>
              <p class="text-xs text-white/65">Our team manually verifies all landlord profiles and property listings for your tenants' protection.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ CONTACT ═══════ -->
<section id="contact" class="py-20 bg-white scroll-mt-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-14">

      <!-- Info -->
      <div class="fade-up">
        <p class="text-brand text-xs font-bold uppercase tracking-widest mb-4">Contact</p>
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 font-display mb-5">Get in Touch</h2>
        <p class="text-gray-500 leading-relaxed mb-10 text-sm max-w-md">Questions about a listing, your account, or listing your property? Our team is here to help — usually within a few hours.</p>

        <div class="space-y-6">
          @php
          $info = [
            ['icon'=>'M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0L6.343 16.657A8 8 0 1117.657 16.657z','label'=>'Office','val'=>'Independence Avenue, Accra, Ghana'],
            ['icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','label'=>'Email','val'=>'hello@efiewura.com'],
            ['icon'=>'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z','label'=>'Phone','val'=>'+233 30 123 4567'],
            ['icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','label'=>'Hours','val'=>'Mon – Fri, 8am – 6pm GMT'],
          ];
          @endphp
          @foreach($info as $item)
          <div class="flex gap-4">
            <div class="w-10 h-10 bg-brand/8 rounded-xl flex items-center justify-center flex-shrink-0">
              <svg class="w-5 h-5 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
              </svg>
            </div>
            <div>
              <p class="text-[0.65rem] font-bold text-gray-400 uppercase tracking-widest">{{ $item['label'] }}</p>
              <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $item['val'] }}</p>
            </div>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Form -->
      <div class="fade-up">
        <div class="bg-cream rounded-2xl p-8 border border-gray-100">
          <h3 class="text-xl font-bold text-gray-900 mb-6 font-display">Send a Message</h3>

          <div id="form-success" class="hidden mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl p-4 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span><strong>Message sent!</strong> We'll get back to you within 24 hours.</span>
          </div>

          <form id="contact-form" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">First Name</label>
                <input type="text" placeholder="Kofi" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-white">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Last Name</label>
                <input type="text" placeholder="Mensah" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-white">
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email Address</label>
              <input type="email" placeholder="kofi@example.com" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-white">
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1.5">I am a…</label>
              <select class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-white text-gray-700">
                <option>Tenant looking for a property</option>
                <option>Landlord wanting to list</option>
                <option>Business / commercial inquiry</option>
                <option>Other</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1.5">Message</label>
              <textarea rows="4" placeholder="How can we help?" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-white resize-none"></textarea>
            </div>
            <button type="submit" class="w-full bg-brand hover:bg-brand-800 active:scale-[.98] text-white font-semibold py-3 rounded-xl transition-all shadow-sm text-sm">
              Send Message
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════ FOOTER ═══════ -->
<footer class="bg-gray-950 text-gray-500">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
    <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-10 mb-12">

      <!-- Brand col -->
      <div class="lg:col-span-2">
        <div class="flex items-center gap-2.5 mb-4">
          <div class="w-8 h-8 bg-brand rounded-xl flex items-center justify-center">
            <svg class="w-[17px] h-[17px] text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 22V12h6v10"/></svg>
          </div>
          <span class="text-white text-xl font-bold tracking-tight">efiewura</span>
        </div>
        <p class="text-sm leading-relaxed mb-5 max-w-xs">Ghana's premier property rental platform. Find your home, list your property, manage your tenancy — all in one place.</p>
        <div class="flex gap-2.5">
          @foreach(['FB','TW','IG','LI'] as $s)
          <a href="#" class="w-8 h-8 rounded-lg bg-white/8 hover:bg-brand transition-colors flex items-center justify-center text-[0.6rem] font-bold text-gray-400 hover:text-white">{{ $s }}</a>
          @endforeach
        </div>
      </div>

      @php
      $fcols = [
        'Platform' => ['Browse Properties','List a Property','How It Works','API Documentation'],
        'Company'  => ['About Us','Careers','Press','Blog'],
        'Support'  => ['Help Center','Tenant Guide','Privacy Policy','Terms of Service'],
      ];
      @endphp
      @foreach($fcols as $title => $links)
      <div>
        <h4 class="text-white text-xs font-bold uppercase tracking-widest mb-5">{{ $title }}</h4>
        <ul class="space-y-3">
          @foreach($links as $link)
          <li>
            @if($link !== 'API Documentation' || app()->environment('local'))
            <a href="{{ $link === 'API Documentation' ? '/docs' : '#' }}" class="text-xs hover:text-white transition-colors">{{ $link }}</a>
            @endif
          </li>
          @endforeach
        </ul>
      </div>
      @endforeach
    </div>

    <div class="pt-8 border-t border-white/8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
      <p>© {{ date('Y') }} Efiewura. All rights reserved.</p>
      <div class="flex gap-6">
        <a href="#" class="hover:text-white transition-colors">Privacy</a>
        <a href="#" class="hover:text-white transition-colors">Terms</a>
        <a href="#" class="hover:text-white transition-colors">Cookies</a>
      </div>
      <div class="flex items-center gap-2">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
        All systems operational
      </div>
    </div>
  </div>
</footer>

<!-- ═══════ SCRIPTS ═══════ -->
<script>
// ── Navbar scroll ──────────────────────────────────────
const navbar = document.getElementById('navbar');
const solidNav = () => navbar.classList.contains('bg-white');
function updateNav() {
  if (window.scrollY > 50) {
    navbar.classList.add('bg-white', 'shadow-md');
  } else {
    navbar.classList.remove('bg-white', 'shadow-md');
  }
}
window.addEventListener('scroll', updateNav, { passive: true });
updateNav();

// ── Mobile hamburger ───────────────────────────────────
const hamBtn   = document.getElementById('ham-btn');
const mobileNav = document.getElementById('mobile-nav');
const hamOpen  = document.getElementById('ham-open');
const hamClose = document.getElementById('ham-close');

hamBtn.addEventListener('click', () => {
  const open = mobileNav.classList.toggle('open');
  hamOpen.classList.toggle('hidden', open);
  hamClose.classList.toggle('hidden', !open);
  navbar.classList.add('bg-white', 'shadow-md');
});

document.querySelectorAll('.mobile-nav-link').forEach(a => {
  a.addEventListener('click', () => {
    mobileNav.classList.remove('open');
    hamOpen.classList.remove('hidden');
    hamClose.classList.add('hidden');
  });
});

// ── Smooth anchor scroll ───────────────────────────────
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const id = a.getAttribute('href').slice(1);
    if (!id) return;
    const el = document.getElementById(id);
    if (el) { e.preventDefault(); el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});

// ── Fade-up on scroll ──────────────────────────────────
const io = new IntersectionObserver((entries) => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

document.querySelectorAll('.fade-up').forEach(el => io.observe(el));

// ── Client-side property filtering ────────────────────
const cards = Array.from(document.querySelectorAll('#prop-grid [data-cat]'));
let activeCat  = 'all';
let cityFilter = document.getElementById('city-input').value.toLowerCase();

function applyFilters() {
  const maxPrice = document.getElementById('price-select').value;
  const sortBy   = document.getElementById('sort-select').value;
  let count = 0;

  cards.forEach(c => {
    const catOk   = activeCat === 'all' || c.dataset.cat === activeCat;
    const cityOk  = !cityFilter || c.dataset.city.includes(cityFilter);
    const priceOk = !maxPrice  || parseFloat(c.dataset.price) <= parseFloat(maxPrice);
    const show = catOk && cityOk && priceOk;
    c.style.display = show ? '' : 'none';
    if (show) count++;
  });

  // Sort visible cards
  const grid    = document.getElementById('prop-grid');
  const visible = cards.filter(c => c.style.display !== 'none');
  visible.sort((a, b) => {
    if (sortBy === 'price_asc')  return +a.dataset.price - +b.dataset.price;
    if (sortBy === 'price_desc') return +b.dataset.price - +a.dataset.price;
    if (sortBy === 'newest')     return +b.dataset.pub   - +a.dataset.pub;
    return +b.dataset.views - +a.dataset.views;
  });
  visible.forEach(c => grid.appendChild(c));
  document.getElementById('count').textContent = count;
}

// Category tab clicks
document.getElementById('cat-tabs').addEventListener('click', e => {
  const btn = e.target.closest('[data-cat]');
  if (!btn) return;
  document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  activeCat = btn.dataset.cat;
  applyFilters();
});

// City input debounce
let debTimer;
document.getElementById('city-input').addEventListener('input', e => {
  clearTimeout(debTimer);
  debTimer = setTimeout(() => { cityFilter = e.target.value.toLowerCase(); applyFilters(); }, 220);
});

document.getElementById('price-select').addEventListener('change', applyFilters);
document.getElementById('sort-select').addEventListener('change', applyFilters);

// ── Contact form ───────────────────────────────────────
document.getElementById('contact-form').addEventListener('submit', function(e) {
  e.preventDefault();
  const box = document.getElementById('form-success');
  box.classList.remove('hidden');
  this.reset();
  setTimeout(() => box.classList.add('hidden'), 7000);
});

// ── Auto-scroll to #browse if search was submitted ────
if (window.location.search && window.location.hash !== '#browse') {
  setTimeout(() => document.getElementById('browse')?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 300);
}
</script>
</body>
</html>
