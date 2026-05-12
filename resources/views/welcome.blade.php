<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Efiewura API — Documentation</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
    theme: { extend: { colors: {
        brand: { DEFAULT:'#7c3aed', light:'#8b5cf6' },
        surface: { DEFAULT:'#0f0f1a', 1:'#13131f', 2:'#1a1a2e', 3:'#1e1e35' },
        accent: '#00d4aa',
    }}}
}
</script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  *{font-family:'Inter',sans-serif}
  ::-webkit-scrollbar{width:6px;height:6px}
  ::-webkit-scrollbar-track{background:#0f0f1a}
  ::-webkit-scrollbar-thumb{background:#2d2d4e;border-radius:3px}
  ::-webkit-scrollbar-thumb:hover{background:#7c3aed}

  .gradient-text{background:linear-gradient(135deg,#a78bfa 0%,#00d4aa 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
  .hero-glow{background:radial-gradient(ellipse 80% 50% at 50% -20%,#7c3aed22 0%,transparent 70%)}
  .card-hover{transition:all .2s ease}
  .card-hover:hover{border-color:#7c3aed55!important;transform:translateY(-1px)}

  .method-badge{font-family:'JetBrains Mono',monospace;font-size:.65rem;font-weight:600;letter-spacing:.08em;padding:2px 8px;border-radius:4px}
  .get   {background:#00d4aa22;color:#00d4aa;border:1px solid #00d4aa44}
  .post  {background:#3b82f622;color:#60a5fa;border:1px solid #3b82f644}
  .put   {background:#f59e0b22;color:#fbbf24;border:1px solid #f59e0b44}
  .patch {background:#8b5cf622;color:#a78bfa;border:1px solid #8b5cf644}
  .delete{background:#ef444422;color:#f87171;border:1px solid #ef444444}

  .nav-link{transition:all .15s;border-left:2px solid transparent}
  .nav-link:hover{color:#a78bfa;background:#7c3aed15}
  .nav-link.active{color:#a78bfa;background:#7c3aed15;border-left-color:#7c3aed}

  pre{background:#080810;border:1px solid #ffffff0d;border-radius:8px;overflow-x:auto;margin:0}
  code{font-family:'JetBrains Mono',monospace;font-size:.78rem}

  .endpoint-path{font-family:'JetBrains Mono',monospace;font-size:.82rem;color:#e2e8f0}
  .auth-badge  {font-size:.62rem;padding:1px 7px;border-radius:3px;background:#f59e0b22;color:#fbbf24;border:1px solid #f59e0b33}
  .public-badge{font-size:.62rem;padding:1px 7px;border-radius:3px;background:#00d4aa22;color:#00d4aa;border:1px solid #00d4aa33}
  .role-badge  {font-size:.62rem;padding:1px 7px;border-radius:3px;background:#8b5cf622;color:#c4b5fd;border:1px solid #8b5cf633}

  .section-anchor{scroll-margin-top:80px}
  .step-connector{position:absolute;left:19px;top:44px;bottom:-16px;width:1px;background:linear-gradient(to bottom,#7c3aed66,transparent)}

  .example-panel{display:none}
  .example-panel.open{display:block}

  .tab-btn{transition:all .15s;border-bottom:2px solid transparent}
  .tab-btn.active{color:#a78bfa;border-bottom-color:#7c3aed}
  .tab-pane{display:none}
  .tab-pane.active{display:block}

  .copy-btn{opacity:0;transition:opacity .2s}
  .code-wrap:hover .copy-btn{opacity:1}

  @media(max-width:768px){
    #sidebar{transform:translateX(-100%);transition:transform .3s;position:fixed;z-index:50;height:100vh;top:0}
    #sidebar.open{transform:translateX(0)}
  }
</style>
</head>
<body class="bg-[#0f0f1a] text-slate-300 min-h-screen">

<!-- Top bar -->
<header class="fixed top-0 left-0 right-0 z-40 border-b border-white/[0.06] bg-[#0f0f1a]/90 backdrop-blur-md">
  <div class="flex items-center justify-between px-5 h-14">
    <div class="flex items-center gap-3">
      <button id="menuToggle" class="md:hidden text-slate-400 hover:text-white p-1">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-600 to-cyan-500 flex items-center justify-center shadow-lg shadow-violet-900/40">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        </div>
        <span class="font-semibold text-white text-sm tracking-tight">Efiewura</span>
        <span class="text-white/20 text-xs">/</span>
        <span class="text-slate-500 text-xs">API Docs</span>
      </div>
    </div>
    <div class="flex items-center gap-4">
      <span class="text-xs text-slate-600 hidden sm:block" style="font-family:'JetBrains Mono',monospace">v1.0 · Laravel 12</span>
      <span class="flex items-center gap-1.5 text-xs text-[#00d4aa] font-medium">
        <span class="w-1.5 h-1.5 rounded-full bg-[#00d4aa] animate-pulse inline-block"></span>Live
      </span>
    </div>
  </div>
</header>

<div class="flex pt-14 min-h-screen">

  <!-- Sidebar -->
  <aside id="sidebar" class="w-56 shrink-0 fixed md:sticky top-14 h-[calc(100vh-56px)] bg-[#13131f] border-r border-white/[0.06] overflow-y-auto">
    <nav class="p-3 text-xs space-y-0.5">
      <div class="text-slate-700 uppercase tracking-[.12em] text-[9px] font-bold px-3 pt-3 pb-1.5">Getting Started</div>
      <a href="#overview"   class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400">Overview</a>
      <a href="#quickstart" class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400">Quick Start</a>
      <a href="#auth-model" class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400">Authentication</a>
      <a href="#responses"  class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400">Response Format</a>
      <a href="#errors"     class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400">Error Codes</a>
      <div class="text-slate-700 uppercase tracking-[.12em] text-[9px] font-bold px-3 pt-4 pb-1.5">Endpoints</div>
      <a href="#ep-auth"         class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-sky-500/60 shrink-0"></span>Authentication</a>
      <a href="#ep-profile"      class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400 pl-7">↳ Profile</a>
      <a href="#ep-properties"   class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500/60 shrink-0"></span>Properties</a>
      <a href="#ep-bookings"     class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-amber-500/60 shrink-0"></span>Bookings</a>
      <a href="#ep-notifications"class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-pink-500/60 shrink-0"></span>Notifications</a>
      <a href="#ep-2fa"          class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-violet-500/60 shrink-0"></span>Two-Factor Auth</a>
      <a href="#ep-landlord"     class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-orange-500/60 shrink-0"></span>Landlord</a>
      <a href="#ep-admin"        class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400"><span class="w-1.5 h-1.5 rounded-full bg-red-500/60 shrink-0"></span>Admin</a>
      <div class="text-slate-700 uppercase tracking-[.12em] text-[9px] font-bold px-3 pt-4 pb-1.5">Reference</div>
      <a href="#roles" class="nav-link flex items-center gap-2 px-3 py-1.5 rounded-md text-slate-400">Role Reference</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="flex-1 min-w-0 px-6 md:px-10 xl:px-16 py-12 max-w-4xl">

    <!-- Hero -->
    <section class="hero-glow mb-16">
      <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-violet-500/30 bg-violet-500/10 text-xs text-violet-300 font-medium mb-6">
        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
        REST · JSON · Sanctum Bearer Token
      </div>
      <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight mb-4">Efiewura <span class="gradient-text">API</span></h1>
      <p class="text-slate-400 text-lg max-w-xl leading-relaxed mb-8">A property rental platform connecting landlords and tenants. Listings, bookings, notifications, 2FA — everything in one RESTful API.</p>
      <div class="flex flex-wrap gap-3">
        <a href="#quickstart" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold transition-colors shadow-lg shadow-violet-900/40">
          Get Started <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
        <a href="#ep-auth" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-white/10 hover:border-violet-500/40 hover:bg-violet-500/5 text-slate-300 text-sm font-medium transition-colors">Browse Endpoints</a>
      </div>
    </section>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-16">
      @foreach([['30+','Endpoints'],['4','User Roles'],['REST','Architecture'],['JSON','All Responses']] as $s)
      <div class="bg-[#1a1a2e] border border-white/[0.06] rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-white mb-1">{{ $s[0] }}</div>
        <div class="text-xs text-slate-600">{{ $s[1] }}</div>
      </div>
      @endforeach
    </div>

    <!-- Overview -->
    <section id="overview" class="section-anchor mb-14">
      <h2 class="text-xl font-bold text-white mb-1">Overview</h2>
      <div class="w-10 h-0.5 bg-gradient-to-r from-violet-500 to-cyan-400 mb-5 rounded-full"></div>
      <p class="text-slate-400 leading-relaxed text-sm mb-5">Built on <strong class="text-white">Laravel 12</strong> with <strong class="text-white">Laravel Sanctum</strong> stateless token auth. All endpoints are prefixed with <code class="bg-[#1e1e35] text-[#00d4aa] px-1.5 py-0.5 rounded text-xs" style="font-family:'JetBrains Mono',monospace">/api</code>.</p>
      <div class="bg-[#1a1a2e] border border-white/[0.06] rounded-xl p-5 code-wrap relative">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-slate-600" style="font-family:'JetBrains Mono',monospace">Base URL</span>
          <button class="copy-btn text-xs text-slate-500 hover:text-slate-300 px-2 py-1 rounded bg-[#1e1e35]" onclick="copyText('http://127.0.0.1:8000/api',this)">Copy</button>
        </div>
        <code class="text-[#00d4aa] text-sm" style="font-family:'JetBrains Mono',monospace">http://127.0.0.1:8000/api</code>
      </div>
    </section>

    <!-- Quick Start -->
    <section id="quickstart" class="section-anchor mb-14">
      <h2 class="text-xl font-bold text-white mb-1">Quick Start</h2>
      <div class="w-10 h-0.5 bg-gradient-to-r from-violet-500 to-cyan-400 mb-5 rounded-full"></div>
      <p class="text-slate-500 mb-8 text-sm">Make your first authenticated request in three steps.</p>
      <!-- Step 1 -->
      <div class="relative flex gap-5 mb-8">
        <div class="relative shrink-0">
          <div class="w-10 h-10 rounded-full bg-violet-500/20 border border-violet-500/40 flex items-center justify-center text-violet-300 font-bold text-sm">1</div>
          <div class="step-connector"></div>
        </div>
        <div class="flex-1 pb-2">
          <h3 class="font-semibold text-white mb-1 text-sm">Register an account</h3>
          <p class="text-xs text-slate-500 mb-3">Choose a role: <span class="text-slate-300">landlord</span>, <span class="text-slate-300">tenant</span>, or <span class="text-slate-300">user</span>.</p>
          <div class="bg-[#1a1a2e] border border-white/[0.06] rounded-xl overflow-hidden code-wrap">
            <div class="flex items-center justify-between px-4 py-2 border-b border-white/[0.06] bg-[#13131f]">
              <span class="text-xs text-slate-600" style="font-family:'JetBrains Mono',monospace">POST /api/register</span>
              <button class="copy-btn text-xs text-slate-500 hover:text-slate-300 px-2 py-1 rounded bg-[#1e1e35]" onclick="copyCode('qs-reg',this)">Copy</button>
            </div>
            <pre class="p-4 text-xs leading-relaxed"><code id="qs-reg" class="text-slate-300">curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "firstname": "Kofi",
    "lastname":  "Mensah",
    "email":     "kofi@example.com",
    "password":  "password123",
    "password_confirmation": "password123",
    "role": "tenant"
  }'</code></pre>
          </div>
        </div>
      </div>
      <!-- Step 2 -->
      <div class="relative flex gap-5 mb-8">
        <div class="relative shrink-0">
          <div class="w-10 h-10 rounded-full bg-violet-500/20 border border-violet-500/40 flex items-center justify-center text-violet-300 font-bold text-sm">2</div>
          <div class="step-connector"></div>
        </div>
        <div class="flex-1 pb-2">
          <h3 class="font-semibold text-white mb-1 text-sm">Save your token</h3>
          <p class="text-xs text-slate-500 mb-3">Both register and login return a <code class="bg-[#1e1e35] px-1 rounded text-xs" style="font-family:'JetBrains Mono',monospace">token</code> field.</p>
          <div class="bg-[#1a1a2e] border border-white/[0.06] rounded-xl overflow-hidden">
            <div class="px-4 py-2 border-b border-white/[0.06] bg-[#13131f]"><span class="text-xs text-slate-600" style="font-family:'JetBrains Mono',monospace">Response 201</span></div>
            <pre class="p-4 text-xs leading-relaxed"><code class="text-slate-300">{
  <span style="color:#00d4aa">"success"</span>: <span style="color:#60a5fa">true</span>,
  <span style="color:#00d4aa">"message"</span>: <span style="color:#fbbf24">"User registered successfully"</span>,
  <span style="color:#00d4aa">"data"</span>: {
    <span style="color:#00d4aa">"user"</span>: { <span style="color:#475569">...</span> },
    <span style="color:#00d4aa">"token"</span>: <span style="color:#fbbf24">"1|abc123xyz..."</span>,
    <span style="color:#00d4aa">"token_type"</span>: <span style="color:#fbbf24">"Bearer"</span>
  }
}</code></pre>
          </div>
        </div>
      </div>
      <!-- Step 3 -->
      <div class="relative flex gap-5">
        <div class="shrink-0">
          <div class="w-10 h-10 rounded-full bg-cyan-500/20 border border-cyan-500/40 flex items-center justify-center text-cyan-300 font-bold text-sm">3</div>
        </div>
        <div class="flex-1">
          <h3 class="font-semibold text-white mb-1 text-sm">Attach the token</h3>
          <p class="text-xs text-slate-500 mb-3">Every protected endpoint expects <code class="bg-[#1e1e35] px-1 rounded text-xs" style="font-family:'JetBrains Mono',monospace">Authorization: Bearer {token}</code>.</p>
          <div class="bg-[#1a1a2e] border border-white/[0.06] rounded-xl overflow-hidden code-wrap">
            <div class="flex items-center justify-between px-4 py-2 border-b border-white/[0.06] bg-[#13131f]">
              <span class="text-xs text-slate-600" style="font-family:'JetBrains Mono',monospace">GET /api/profile</span>
              <button class="copy-btn text-xs text-slate-500 hover:text-slate-300 px-2 py-1 rounded bg-[#1e1e35]" onclick="copyCode('qs-token',this)">Copy</button>
            </div>
            <pre class="p-4 text-xs leading-relaxed"><code id="qs-token" class="text-slate-300">curl http://127.0.0.1:8000/api/profile \
  -H "Authorization: Bearer 1|abc123xyz..." \
  -H "Accept: application/json"</code></pre>
          </div>
        </div>
      </div>
    </section>

    <!-- Auth model -->
    <section id="auth-model" class="section-anchor mb-14">
      <h2 class="text-xl font-bold text-white mb-1">Authentication</h2>
      <div class="w-10 h-0.5 bg-gradient-to-r from-violet-500 to-cyan-400 mb-5 rounded-full"></div>
      <p class="text-slate-400 leading-relaxed text-sm mb-6">Include <code class="bg-[#1e1e35] text-[#00d4aa] px-1.5 py-0.5 rounded text-xs" style="font-family:'JetBrains Mono',monospace">Authorization: Bearer {token}</code> and <code class="bg-[#1e1e35] text-[#00d4aa] px-1.5 py-0.5 rounded text-xs" style="font-family:'JetBrains Mono',monospace">Accept: application/json</code> on every protected request.</p>
      <div class="grid sm:grid-cols-3 gap-3">
        @foreach([['🔓','Public','No token needed — register, login, browse properties.','border-emerald-900/60 bg-emerald-950/20'],['🔑','Auth Required','Valid Bearer token from login or register.','border-amber-900/60 bg-amber-950/20'],['👑','Role-Scoped','Token + role check enforced by CheckRole middleware.','border-violet-900/60 bg-violet-950/20']] as $a)
        <div class="rounded-xl border {{ $a[3] }} p-4">
          <div class="text-xl mb-2">{{ $a[0] }}</div>
          <div class="font-semibold text-white text-sm mb-1.5">{{ $a[1] }}</div>
          <p class="text-xs text-slate-500 leading-relaxed">{{ $a[2] }}</p>
        </div>
        @endforeach
      </div>
    </section>

    <!-- Response format -->
    <section id="responses" class="section-anchor mb-14">
      <h2 class="text-xl font-bold text-white mb-1">Response Format</h2>
      <div class="w-10 h-0.5 bg-gradient-to-r from-violet-500 to-cyan-400 mb-5 rounded-full"></div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <div class="text-xs text-slate-600 mb-2" style="font-family:'JetBrains Mono',monospace">Success (2xx)</div>
          <pre class="p-4 text-xs leading-relaxed"><code class="text-slate-300">{
  <span style="color:#00d4aa">"success"</span>: <span style="color:#60a5fa">true</span>,
  <span style="color:#00d4aa">"message"</span>: <span style="color:#fbbf24">"Human readable"</span>,
  <span style="color:#00d4aa">"data"</span>: { <span style="color:#475569">/* payload */</span> }
}</code></pre>
        </div>
        <div>
          <div class="text-xs text-slate-600 mb-2" style="font-family:'JetBrains Mono',monospace">Validation Error (422)</div>
          <pre class="p-4 text-xs leading-relaxed"><code class="text-slate-300">{
  <span style="color:#00d4aa">"success"</span>: <span style="color:#f87171">false</span>,
  <span style="color:#00d4aa">"message"</span>: <span style="color:#fbbf24">"Validation failed"</span>,
  <span style="color:#00d4aa">"errors"</span>: {
    <span style="color:#00d4aa">"email"</span>: [<span style="color:#fbbf24">"Already taken."</span>]
  }
}</code></pre>
        </div>
      </div>
    </section>

    <!-- Error codes -->
    <section id="errors" class="section-anchor mb-14">
      <h2 class="text-xl font-bold text-white mb-1">Error Codes</h2>
      <div class="w-10 h-0.5 bg-gradient-to-r from-violet-500 to-cyan-400 mb-5 rounded-full"></div>
      <div class="bg-[#1a1a2e] border border-white/[0.06] rounded-xl overflow-hidden">
        <table class="w-full">
          <thead><tr class="border-b border-white/[0.06] bg-[#13131f]">
            <th class="text-left px-5 py-3 text-slate-600 font-medium text-xs" style="font-family:'JetBrains Mono',monospace">Code</th>
            <th class="text-left px-5 py-3 text-slate-600 font-medium text-xs">Meaning</th>
          </tr></thead>
          <tbody class="divide-y divide-white/[0.04]">
            @foreach([['200','text-emerald-400','OK — succeeded'],['201','text-emerald-400','Created — resource created'],['401','text-amber-400','Unauthenticated — missing or invalid token'],['403','text-amber-400','Forbidden — role check or ownership mismatch'],['422','text-red-400','Unprocessable — validation errors in errors object'],['500','text-red-400','Server error — unexpected exception']] as $e)
            <tr class="hover:bg-white/[0.02]">
              <td class="px-5 py-3 font-semibold text-sm {{ $e[1] }}" style="font-family:'JetBrains Mono',monospace">{{ $e[0] }}</td>
              <td class="px-5 py-3 text-slate-400 text-sm">{{ $e[2] }}</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>

    {{-- ═══════════ ENDPOINT CARDS ═══════════ --}}
    @php
    $epCount = 0;
    function ep($method,$path,$title,$desc,$auth='public',$params=[],$note=''){
      global $epCount; $epCount++;
      $id = 'ep-'.$epCount;
      $key = strtoupper($method).'||'.$path;
      $cls=['GET'=>'get','POST'=>'post','PUT'=>'put','PATCH'=>'patch','DELETE'=>'delete'][strtoupper($method)]??'get';
      $badgeCls=$auth==='public'?'public-badge':($auth==='auth'?'auth-badge':'role-badge');
      $badgeTxt=$auth==='public'?'Public':($auth==='auth'?'Auth required':$auth);
      echo '<div class="card-hover bg-[#1a1a2e] border border-white/[0.06] rounded-xl p-5 mb-3" id="'.$id.'">';
      echo '<div class="flex flex-wrap items-center gap-2 mb-3">';
      echo '<span class="method-badge '.$cls.'">'.strtoupper($method).'</span>';
      echo '<span class="endpoint-path flex-1 min-w-0 truncate">'.$path.'</span>';
      echo '<span class="'.$badgeCls.' shrink-0">'.$badgeTxt.'</span>';
      echo '</div>';
      echo '<div class="font-medium text-white text-sm mb-1">'.$title.'</div>';
      echo '<p class="text-xs text-slate-500 leading-relaxed">'.$desc.'</p>';
      if($note) echo '<p class="text-xs text-amber-400/80 mt-2 bg-amber-950/30 border border-amber-800/30 rounded-lg px-3 py-2">⚠ '.$note.'</p>';
      if($params){
        echo '<div class="mt-3 pt-3 border-t border-white/[0.06]">';
        echo '<div class="text-[10px] text-slate-700 uppercase tracking-widest mb-2">Key fields</div>';
        echo '<div class="flex flex-wrap gap-1.5">';
        foreach($params as $p) echo '<span class="text-[10px] bg-[#1e1e35] text-slate-400 px-2 py-0.5 rounded" style="font-family:\'JetBrains Mono\',monospace">'.$p.'</span>';
        echo '</div></div>';
      }
      // Example toggle button
      echo '<div class="mt-3 pt-3 border-t border-white/[0.06]">';
      echo '<button onclick="toggleExample(\''.$id.'\',\''.$key.'\')" class="flex items-center gap-1.5 text-xs text-slate-500 hover:text-violet-400 transition-colors" id="btn-'.$id.'">';
      echo '<svg class="w-3.5 h-3.5 toggle-icon transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
      echo 'Show example</button></div>';
      // Expandable panel
      echo '<div class="example-panel mt-3" id="panel-'.$id.'">';
      echo '<div class="bg-[#0d0d18] border border-white/[0.06] rounded-xl overflow-hidden">';
      // Tab headers
      echo '<div class="flex border-b border-white/[0.06] bg-[#0a0a12]">';
      echo '<button onclick="switchTab(\''.$id.'\',\'req\')" class="tab-btn active px-4 py-2.5 text-xs font-medium text-slate-400" id="tab-req-'.$id.'">Request</button>';
      echo '<button onclick="switchTab(\''.$id.'\',\'res\')" class="tab-btn px-4 py-2.5 text-xs font-medium text-slate-400" id="tab-res-'.$id.'">Response</button>';
      echo '</div>';
      // Request pane
      echo '<div class="tab-pane active" id="pane-req-'.$id.'">';
      echo '<div class="code-wrap relative">';
      echo '<button class="copy-btn absolute top-3 right-3 text-xs text-slate-500 hover:text-slate-300 px-2 py-1 rounded bg-[#1e1e35]" onclick="copyCode(\'req-code-'.$id.'\',this)">Copy</button>';
      echo '<pre class="p-4 text-xs leading-relaxed" style="background:transparent;border:none;border-radius:0"><code id="req-code-'.$id.'" class="text-slate-400 italic">Loading...</code></pre>';
      echo '</div></div>';
      // Response pane
      echo '<div class="tab-pane" id="pane-res-'.$id.'">';
      echo '<div class="code-wrap relative">';
      echo '<button class="copy-btn absolute top-3 right-3 text-xs text-slate-500 hover:text-slate-300 px-2 py-1 rounded bg-[#1e1e35]" onclick="copyCode(\'res-code-'.$id.'\',this)">Copy</button>';
      echo '<pre class="p-4 text-xs leading-relaxed" style="background:transparent;border:none;border-radius:0"><code id="res-code-'.$id.'" class="text-slate-400 italic">Loading...</code></pre>';
      echo '</div></div>';
      echo '</div></div>';
      echo '</div>';
    }
    function grp($svg,$label,$color){ echo '<div class="flex items-center gap-3 mb-5"><div class="w-8 h-8 rounded-lg flex items-center justify-center '.$color[0].' text-'.$color[1].'">'.$svg.'</div><h2 class="text-xl font-bold text-white">'.$label.'</h2></div>'; }
    @endphp

    <!-- Auth -->
    <section id="ep-auth" class="section-anchor mb-14">
      @php grp('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>','Authentication',['bg-sky-500/20 border border-sky-500/30','sky-400']) @endphp
      {!! ep('POST','/api/register','Register','Create a new account. Returns a Bearer token and role-specific profile.','public',['firstname','lastname','email','password','role']) !!}
      {!! ep('POST','/api/login','Login','Authenticate and receive a Bearer token. Supports optional 2FA.','public',['email','password','two_factor_code?','recovery_code?']) !!}
      {!! ep('POST','/api/logout','Logout','Revoke the current access token immediately.','auth') !!}
      <h3 id="ep-profile" class="section-anchor text-xs text-slate-600 uppercase tracking-widest mt-8 mb-4">Profile</h3>
      {!! ep('GET', '/api/profile',         'Get Profile',    'Returns the authenticated user with role-specific relations and preferences.','auth') !!}
      {!! ep('PUT', '/api/profile',         'Update Profile', 'Update name, email, role fields, and preferences in one call.','auth',['firstname?','lastname?','email?','phone?','preferences?']) !!}
      {!! ep('PUT', '/api/change-password', 'Change Password','Requires current password. New password must be confirmed.','auth',['current_password','password','password_confirmation']) !!}
    </section>

    <!-- Properties -->
    <section id="ep-properties" class="section-anchor mb-14">
      @php grp('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>','Properties',['bg-emerald-500/20 border border-emerald-500/30','emerald-400']) @endphp
      {!! ep('GET',   '/api/property-categories',   'List Categories', 'All active categories. Use category_id when filtering or creating listings.','public') !!}
      {!! ep('GET',   '/api/properties',            'Browse Properties','Paginated public list. Filters: city, category_id, min_price, max_price, bedrooms, furnished, pets_allowed.','public',['?city','?category_id','?min_price','?max_price','?bedrooms','?sort_by']) !!}
      {!! ep('GET',   '/api/properties/{id}',       'Get Property',    'Full detail. Increments view count (not for owner). Triggers milestone notifications.','public') !!}
      {!! ep('POST',  '/api/properties',            'Create Property', 'List a new property. Landlord profile required.','Landlord',['title','description','price','currency','city','bedrooms','bathrooms','images?','amenities?']) !!}
      {!! ep('PUT',   '/api/properties/{id}',       'Update Property', 'Full update. Only the owning landlord can edit.','Landlord') !!}
      {!! ep('DELETE','/api/properties/{id}',       'Delete Property', 'Soft-deletes (status=deleted, is_active=false). Data preserved.','Landlord') !!}
      {!! ep('GET',   '/api/my-properties',         'My Properties',   'Landlord\'s own listings. Filter by ?status=available|occupied|maintenance.','Landlord') !!}
      {!! ep('PATCH', '/api/properties/{id}/status','Update Status',   'Change availability: available, occupied, maintenance, reserved.','Landlord',['status']) !!}
    </section>

    <!-- Bookings -->
    <section id="ep-bookings" class="section-anchor mb-14">
      @php grp('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>','Bookings',['bg-amber-500/20 border border-amber-500/30','amber-400']) @endphp
      <div class="bg-[#1a1a2e] border border-amber-900/40 rounded-xl px-5 py-4 mb-5 text-xs text-slate-400 leading-relaxed">
        <strong class="text-amber-300">Workflow:</strong>
        <span class="mx-1 px-1.5 py-0.5 rounded bg-[#1e1e35] text-slate-300" style="font-family:'JetBrains Mono',monospace">pending</span>→
        <span class="mx-1 px-1.5 py-0.5 rounded bg-[#1e1e35] text-slate-300" style="font-family:'JetBrains Mono',monospace">confirmed</span>/<span class="mx-1 px-1.5 py-0.5 rounded bg-[#1e1e35] text-slate-300" style="font-family:'JetBrains Mono',monospace">rejected</span> (landlord) →
        <span class="mx-1 px-1.5 py-0.5 rounded bg-[#1e1e35] text-slate-300" style="font-family:'JetBrains Mono',monospace">cancelled</span>/<span class="mx-1 px-1.5 py-0.5 rounded bg-[#1e1e35] text-slate-300" style="font-family:'JetBrains Mono',monospace">completed</span>.
        Property auto-updates to <em>occupied</em> on confirm and <em>available</em> on cancel.
      </div>
      {!! ep('GET',  '/api/browse-available-properties','Browse Available', 'Available properties with no active bookings. Same filters as /properties.','auth') !!}
      {!! ep('POST', '/api/bookings',                  'Create Booking',   'Submit a booking request. Move-in must be future date. Duration: 1–60 months.','auth',['property_id','move_in_date','lease_duration_months','tenant_name','tenant_phone','tenant_email']) !!}
      {!! ep('GET',  '/api/my-bookings',               'My Bookings',      'User\'s paginated bookings with status summary. Filter by ?status.','auth') !!}
      {!! ep('GET',  '/api/bookings/{id}',             'Get Booking',      'Full detail. Only accessible by the booking owner.','auth') !!}
      {!! ep('PATCH','/api/bookings/{id}/cancel',      'Cancel Booking',   'Cancel a pending booking. Tenant only.','auth') !!}
      {!! ep('GET',  '/api/bookings',                  'Landlord Bookings','All booking requests for the landlord\'s properties.','Landlord') !!}
      {!! ep('PATCH','/api/bookings/{id}/confirm',     'Confirm Booking',  'Confirms booking; property → occupied. Tenant notified.','Landlord') !!}
      {!! ep('PATCH','/api/bookings/{id}/reject',      'Reject Booking',   'Rejects a pending request. Tenant notified.','Landlord') !!}
      {!! ep('POST', '/api/test-bookings',             'Test Booking',     'Create a booking with past dates for notification testing.','auth',[],'Non-production only') !!}
    </section>

    <!-- Notifications -->
    <section id="ep-notifications" class="section-anchor mb-14">
      @php grp('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>','Notifications',['bg-pink-500/20 border border-pink-500/30','pink-400']) @endphp
      <p class="text-slate-500 text-xs mb-5 leading-relaxed">Auto-generated daily at 09:00: lease expiry (60/30/7 days), payment reminders (5 days, due day, 3 days overdue), move-in/out reminders, and property auto-release alerts.</p>
      {!! ep('GET',   '/api/notifications',                    'List Notifications',   'User\'s paginated notifications. Filter by ?is_read=true|false or ?type.','auth') !!}
      {!! ep('GET',   '/api/notifications/counts',             'Counts',               'Returns total, unread, and read counts.','auth') !!}
      {!! ep('GET',   '/api/notifications/types',              'Types',                'All notification type strings for filtering.','auth') !!}
      {!! ep('PATCH', '/api/notifications/{id}/read',          'Mark as Read',         'Sets read_at timestamp.','auth') !!}
      {!! ep('PATCH', '/api/notifications/{id}/unread',        'Mark as Unread',       'Clears read_at.','auth') !!}
      {!! ep('PATCH', '/api/notifications/mark-all-read',      'Mark All as Read',     'Marks every unread notification for this user.','auth') !!}
      {!! ep('POST',  '/api/notifications/{id}/resend-email',  'Resend Email',         'Re-dispatches the notification email.','auth') !!}
      {!! ep('DELETE','/api/notifications/{id}',               'Delete',               'Soft-deletes a notification.','auth') !!}
      {!! ep('DELETE','/api/notifications/clear-read',         'Clear All Read',       'Soft-deletes all read notifications.','auth') !!}
    </section>

    <!-- 2FA -->
    <section id="ep-2fa" class="section-anchor mb-14">
      @php grp('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>','Two-Factor Authentication',['bg-violet-500/20 border border-violet-500/30','violet-400']) @endphp
      <div class="bg-[#1a1a2e] border border-violet-900/40 rounded-xl px-5 py-4 mb-5 text-xs text-slate-400 leading-relaxed">
        <strong class="text-violet-300">Setup flow:</strong> generate-secret → scan QR → confirm with first code → 2FA active. Recovery codes are single-use.
      </div>
      {!! ep('GET', '/api/2fa/status',                   '2FA Status',        'Returns enabled, confirmed, and method (app/sms).','auth') !!}
      {!! ep('POST','/api/2fa/generate-secret',          'Generate Secret',   'Returns TOTP secret and QR code URL.','auth') !!}
      {!! ep('POST','/api/2fa/confirm',                  'Confirm Setup',     'Activate 2FA with first code. Returns 8 recovery codes.','auth',['code']) !!}
      {!! ep('POST','/api/2fa/verify',                   'Verify Code',       'Verify a TOTP code mid-session.','auth',['code']) !!}
      {!! ep('POST','/api/2fa/disable',                  'Disable 2FA',       'Clears secret and recovery codes.','auth',['password']) !!}
      {!! ep('GET', '/api/2fa/recovery-codes',           'Recovery Codes',    'Returns remaining unused recovery codes.','auth') !!}
      {!! ep('POST','/api/2fa/recovery-codes/regenerate','Regenerate Codes',  'Issues 8 new cryptographically secure recovery codes.','auth') !!}
    </section>

    <!-- Landlord -->
    <section id="ep-landlord" class="section-anchor mb-14">
      @php grp('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>','Landlord Settings',['bg-orange-500/20 border border-orange-500/30','orange-400']) @endphp
      {!! ep('GET',  '/api/landlord/settings','Get Settings',   'Business profile and auto-release configuration.','Landlord') !!}
      {!! ep('PATCH','/api/landlord/settings','Update Settings','Update business info and overdue_release_days (7–365).','Landlord',['business_name?','phone?','address?','overdue_release_days?']) !!}
    </section>

    <!-- Admin -->
    <section id="ep-admin" class="section-anchor mb-14">
      @php grp('<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>','Admin',['bg-red-500/20 border border-red-500/30','red-400']) @endphp
      <p class="text-slate-500 text-xs mb-5">All prefixed <code class="bg-[#1e1e35] text-[#00d4aa] px-1.5 py-0.5 rounded" style="font-family:'JetBrains Mono',monospace">/api/admin/</code> · requires <span class="role-badge">Admin</span></p>
      <p class="text-xs text-slate-600 uppercase tracking-widest mb-3">Dashboard & Analytics</p>
      {!! ep('GET','/api/admin/dashboard',               'Dashboard',         'Platform-wide counts: users, properties, bookings, revenue this month.','Admin') !!}
      {!! ep('GET','/api/admin/analytics/user-growth',   'User Growth',       'Registration trends over 7/30/90-day windows.','Admin') !!}
      {!! ep('GET','/api/admin/analytics/booking-trends','Booking Trends',    'Booking volume and status breakdown over time.','Admin') !!}
      {!! ep('GET','/api/admin/analytics/revenue',       'Revenue',           'Revenue from confirmed/completed bookings by period.','Admin') !!}
      <p class="text-xs text-slate-600 uppercase tracking-widest mb-3 mt-6">User Management</p>
      {!! ep('GET',   '/api/admin/users',             'List Users',   'Paginated user list with role filter, search, and soft-delete visibility.','Admin') !!}
      {!! ep('GET',   '/api/admin/users/{id}',        'Get User',     'Full profile including role-specific data.','Admin') !!}
      {!! ep('PATCH', '/api/admin/users/{id}',        'Update User',  'Edit role, status, 2FA requirements.','Admin') !!}
      {!! ep('POST',  '/api/admin/users/{id}/actions','User Actions', 'Perform: soft_delete, restore, verify, suspend.','Admin',['action']) !!}
      {!! ep('DELETE','/api/admin/users/{id}',        'Delete User',  'Soft-deletes the user account.','Admin') !!}
      <p class="text-xs text-slate-600 uppercase tracking-widest mb-3 mt-6">Property & Booking Management</p>
      {!! ep('GET',  '/api/admin/properties',              'All Properties',   'Admin view including soft-deleted records.','Admin') !!}
      {!! ep('PATCH','/api/admin/properties/{id}',         'Update Property',  'Edit any property field.','Admin') !!}
      {!! ep('POST', '/api/admin/properties/{id}/moderate','Moderate Property','Approve, suspend, or flag with optional reason.','Admin',['action','reason?']) !!}
      {!! ep('GET',  '/api/admin/bookings',                'All Bookings',     'Platform-wide booking list.','Admin') !!}
      {!! ep('PATCH','/api/admin/bookings/{id}',           'Update Booking',   'Edit fields or add admin_notes.','Admin') !!}
      <p class="text-xs text-slate-600 uppercase tracking-widest mb-3 mt-6">Notifications & Settings</p>
      {!! ep('GET',  '/api/admin/notifications','All Notifications',  'Platform-wide notification list.','Admin') !!}
      {!! ep('POST', '/api/admin/notifications','Create Notification','Manually dispatch to any user.','Admin',['user_id','type','title','message']) !!}
      {!! ep('GET',  '/api/admin/settings',    'Get Settings',       'All platform configuration settings.','Admin') !!}
      {!! ep('PATCH','/api/admin/settings',    'Update Settings',    'Modify platform-wide configuration.','Admin') !!}
    </section>

    <!-- Roles -->
    <section id="roles" class="section-anchor mb-14">
      <h2 class="text-xl font-bold text-white mb-1">Role Reference</h2>
      <div class="w-10 h-0.5 bg-gradient-to-r from-violet-500 to-cyan-400 mb-5 rounded-full"></div>
      <div class="grid sm:grid-cols-2 gap-3">
        @foreach([['user','#818cf8','Default. Browse properties and view content.'],['tenant','#38bdf8','Request bookings, manage own bookings, receive notifications.'],['landlord','#fbbf24','List properties, manage bookings, configure auto-release settings.'],['admin','#f87171','Full platform access — users, moderation, analytics, settings.']] as $r)
        <div class="card-hover bg-[#1a1a2e] border border-white/[0.06] rounded-xl p-4">
          <span class="text-sm font-semibold mb-1.5 block" style="color:{{ $r[1] }};font-family:'JetBrains Mono',monospace">{{ $r[0] }}</span>
          <p class="text-xs text-slate-500 leading-relaxed">{{ $r[2] }}</p>
        </div>
        @endforeach
      </div>
    </section>

    <footer class="border-t border-white/[0.06] pt-8 mt-4 text-center">
      <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-600 to-cyan-500 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-violet-900/30">
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      </div>
      <p class="text-slate-700 text-sm">Efiewura API — Laravel 12 &amp; Sanctum</p>
      <p class="text-slate-800 text-xs mt-1">All responses are JSON · Timestamps ISO 8601 UTC</p>
    </footer>
  </main>
</div>

<div id="overlay" class="hidden fixed inset-0 bg-black/60 z-40 md:hidden" onclick="closeSidebar()"></div>

<script>
// ── Sidebar ──────────────────────────────────────────────────
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
document.getElementById('menuToggle').onclick = () => { sidebar.classList.toggle('open'); overlay.classList.toggle('hidden'); };
function closeSidebar(){ sidebar.classList.remove('open'); overlay.classList.add('hidden'); }
document.querySelectorAll('.nav-link').forEach(l => l.addEventListener('click', closeSidebar));

// ── Active nav scroll spy ────────────────────────────────────
document.querySelectorAll('.section-anchor[id]').forEach(s =>
  new IntersectionObserver(entries => {
    entries.forEach(e => { if(e.isIntersecting) document.querySelectorAll('.nav-link').forEach(l => l.classList.toggle('active', l.getAttribute('href')==='#'+e.target.id)); });
  }, {rootMargin:'-15% 0px -75% 0px'}).observe(s)
);

// ── Copy helpers ─────────────────────────────────────────────
function copyText(t,btn){ navigator.clipboard.writeText(t).then(()=>flash(btn)); }
function copyCode(id,btn){ const el=document.getElementById(id); if(el) navigator.clipboard.writeText(el.innerText).then(()=>flash(btn)); }
function flash(btn){ const o=btn.textContent; btn.textContent='Copied!'; btn.style.color='#00d4aa'; setTimeout(()=>{btn.textContent=o;btn.style.color='';},1600); }

// ── Example panel toggle ─────────────────────────────────────
function toggleExample(id, key){
  const panel = document.getElementById('panel-'+id);
  const btn   = document.getElementById('btn-'+id);
  const icon  = btn.querySelector('.toggle-icon');
  const isOpen = panel.classList.contains('open');

  panel.classList.toggle('open', !isOpen);
  icon.style.transform = isOpen ? '' : 'rotate(90deg)';
  btn.childNodes[1].textContent = isOpen ? ' Show example' : ' Hide example';

  if(!isOpen){ populateExample(id, key); }
}

function switchTab(id, tab){
  ['req','res'].forEach(t => {
    document.getElementById('pane-'+t+'-'+id).classList.toggle('active', t===tab);
    document.getElementById('tab-'+t+'-'+id).classList.toggle('active', t===tab);
  });
}

function populateExample(id, key){
  const ex = examples[key];
  if(!ex) return;
  const reqEl = document.getElementById('req-code-'+id);
  const resEl = document.getElementById('res-code-'+id);
  if(reqEl) reqEl.innerHTML = syntaxHighlight(ex.req, 'bash');
  if(resEl) resEl.innerHTML = syntaxHighlight(ex.res, 'json');
}

// Simple syntax highlighter
function syntaxHighlight(code, lang){
  const esc = s => s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  let s = esc(code);
  if(lang==='json'){
    s = s
      .replace(/(&quot;[^&]*&quot;)\s*:/g, '<span style="color:#00d4aa">$1</span>:')
      .replace(/:\s*(&quot;[^&]*&quot;)/g, ': <span style="color:#fbbf24">$1</span>')
      .replace(/:\s*(true|false)/g, ': <span style="color:#60a5fa">$1</span>')
      .replace(/:\s*(\d+\.?\d*)/g, ': <span style="color:#a78bfa">$1</span>')
      .replace(/:\s*(null)/g, ': <span style="color:#f87171">$1</span>')
      .replace(/&quot;/g,'"');
  } else {
    s = s
      .replace(/(curl\s+-[XH]?\s*)/g,'<span style="color:#60a5fa">$1</span>')
      .replace(/(-H\s+"[^"]*")/g,'<span style="color:#a78bfa">$1</span>')
      .replace(/(-d\s+')/g,'<span style="color:#fbbf24">$1</span>')
      .replace(/(http:\/\/[^\s\\]+)/g,'<span style="color:#00d4aa">$1</span>');
  }
  return s;
}

// ── Example data ─────────────────────────────────────────────
const B = 'http://127.0.0.1:8000/api';
const H = '-H "Authorization: Bearer 1|abc123..." \\\n  -H "Accept: application/json"';

const examples = {

'POST||/api/register': {
req: `curl -X POST ${B}/register \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "firstname": "Kofi",
    "lastname": "Mensah",
    "email": "kofi@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "tenant"
  }'`,
res: `{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 3,
      "firstname": "Kofi",
      "lastname": "Mensah",
      "email": "kofi@example.com",
      "role": "tenant",
      "status": "active",
      "name": "Kofi Mensah",
      "created_at": "2026-05-09T10:00:00.000000Z"
    },
    "token": "3|xYzAbC123...",
    "token_type": "Bearer"
  }
}`},

'POST||/api/login': {
req: `curl -X POST ${B}/login \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -d '{
    "email": "kofi@example.com",
    "password": "password123"
  }'`,
res: `{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 3,
      "firstname": "Kofi",
      "lastname": "Mensah",
      "email": "kofi@example.com",
      "role": "tenant",
      "name": "Kofi Mensah"
    },
    "token": "3|xYzAbC123...",
    "token_type": "Bearer"
  }
}`},

'POST||/api/logout': {
req: `curl -X POST ${B}/logout \\
  ${H}`,
res: `{
  "success": true,
  "message": "Logged out successfully"
}`},

'GET||/api/profile': {
req: `curl ${B}/profile \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "id": 3,
    "firstname": "Kofi",
    "lastname": "Mensah",
    "email": "kofi@example.com",
    "role": "tenant",
    "name": "Kofi Mensah",
    "preferences": {
      "timezone": "Africa/Lagos",
      "date_format": "DD/MM/YYYY",
      "theme": "light",
      "email_notifications": true,
      "sms_notifications": false,
      "bio": null
    },
    "tenant": {
      "id": 2,
      "phone": "+233501234567",
      "occupation": "Software Engineer",
      "verified": false
    }
  }
}`},

'PUT||/api/profile': {
req: `curl -X PUT ${B}/profile \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "firstname": "Kwame",
    "preferences": {
      "theme": "dark",
      "notifications": {
        "email": true,
        "new_bookings": true
      }
    }
  }'`,
res: `{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 3,
    "firstname": "Kwame",
    "lastname": "Mensah",
    "email": "kofi@example.com",
    "role": "tenant",
    "preferences": {
      "theme": "dark",
      "email_notifications": true,
      "new_bookings": true
    }
  }
}`},

'PUT||/api/change-password': {
req: `curl -X PUT ${B}/change-password \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "current_password": "password123",
    "password": "newSecure456!",
    "password_confirmation": "newSecure456!"
  }'`,
res: `{
  "success": true,
  "message": "Password changed successfully"
}`},

'GET||/api/property-categories': {
req: `curl ${B}/property-categories \\
  -H "Accept: application/json"`,
res: `{
  "success": true,
  "data": [
    { "id": 1, "name": "Apartment", "description": "Residential apartments", "is_active": true },
    { "id": 2, "name": "House",     "description": "Standalone houses",      "is_active": true },
    { "id": 3, "name": "Studio",    "description": "Studio units",           "is_active": true }
  ]
}`},

'GET||/api/properties': {
req: `curl "${B}/properties?city=Accra&min_price=500&max_price=2000&bedrooms=2&sort_by=price&sort_order=asc" \\
  -H "Accept: application/json"`,
res: `{
  "success": true,
  "data": [
    {
      "id": 7,
      "title": "Modern 2-Bed in East Legon",
      "price": "750.00",
      "currency": "GHS",
      "formatted_price": "GHS 750.00",
      "city": "Accra",
      "bedrooms": 2,
      "bathrooms": 1,
      "furnished": true,
      "availability_status": "available",
      "main_image": "images/prop-7-main.jpg",
      "views_count": 124,
      "category": { "id": 1, "name": "Apartment" },
      "landlord": { "business_name": "Mensah Properties" }
    }
  ],
  "meta": {
    "total": 42,
    "per_page": 15,
    "current_page": 1,
    "last_page": 3
  }
}`},

'GET||/api/properties/{id}': {
req: `curl ${B}/properties/7 \\
  -H "Accept: application/json"`,
res: `{
  "success": true,
  "data": {
    "id": 7,
    "title": "Modern 2-Bed in East Legon",
    "description": "Spacious apartment with AC and 24hr security.",
    "price": "750.00",
    "currency": "GHS",
    "city": "Accra",
    "state": "Greater Accra",
    "bedrooms": 2,
    "bathrooms": 1,
    "size_sqm": "85.00",
    "furnished": true,
    "pets_allowed": false,
    "amenities": ["WiFi", "AC", "Security", "Parking"],
    "images": ["images/prop-7-1.jpg", "images/prop-7-2.jpg"],
    "availability_status": "available",
    "security_deposit": "750.00",
    "minimum_lease_months": 6,
    "views_count": 125
  }
}`},

'POST||/api/properties': {
req: `curl -X POST ${B}/properties \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "title": "Cozy Studio in Osu",
    "description": "Newly renovated studio near Danquah Circle.",
    "property_category_id": 3,
    "price": 450.00,
    "currency": "GHS",
    "bedrooms": 0,
    "bathrooms": 1,
    "city": "Accra",
    "state": "Greater Accra",
    "country": "Ghana",
    "address": "12 Ring Road East, Osu",
    "furnished": true,
    "amenities": ["WiFi", "Water"],
    "available_from": "2026-06-01",
    "minimum_lease_months": 3
  }'`,
res: `{
  "success": true,
  "message": "Property listed successfully",
  "data": {
    "id": 12,
    "title": "Cozy Studio in Osu",
    "price": "450.00",
    "currency": "GHS",
    "city": "Accra",
    "availability_status": "available",
    "is_active": true,
    "landlord_id": 2,
    "created_at": "2026-05-09T14:00:00.000000Z"
  }
}`},

'PATCH||/api/properties/{id}/status': {
req: `curl -X PATCH ${B}/properties/7/status \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{ "status": "maintenance" }'`,
res: `{
  "success": true,
  "message": "Property status updated successfully",
  "data": {
    "property_id": 7,
    "new_status": "maintenance"
  }
}`},

'GET||/api/my-properties': {
req: `curl "${B}/my-properties?status=available&per_page=10" \\
  ${H}`,
res: `{
  "success": true,
  "data": [ { "id": 7, "title": "Modern 2-Bed in East Legon", "availability_status": "available", "price": "750.00" } ],
  "meta": { "total": 1, "per_page": 10, "current_page": 1, "last_page": 1 },
  "summary": { "total_properties": 5, "available_properties": 1 }
}`},

'POST||/api/bookings': {
req: `curl -X POST ${B}/bookings \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "property_id": 7,
    "move_in_date": "2026-07-01",
    "lease_duration_months": 12,
    "tenant_name": "Kofi Mensah",
    "tenant_phone": "+233501234567",
    "tenant_email": "kofi@example.com",
    "tenant_message": "I am a quiet professional, no pets.",
    "occupation": "Software Engineer",
    "monthly_income": 3500
  }'`,
res: `{
  "success": true,
  "message": "Booking request submitted successfully",
  "data": {
    "id": 14,
    "property_id": 7,
    "user_id": 3,
    "landlord_id": 2,
    "status": "pending",
    "move_in_date": "2026-07-01",
    "move_out_date": "2027-07-01",
    "lease_duration_months": 12,
    "monthly_rent": "750.00",
    "security_deposit": "750.00",
    "total_amount": "1500.00",
    "currency": "GHS",
    "tenant_name": "Kofi Mensah",
    "created_at": "2026-05-09T15:00:00.000000Z"
  }
}`},

'GET||/api/my-bookings': {
req: `curl "${B}/my-bookings?status=pending" \\
  ${H}`,
res: `{
  "success": true,
  "data": [
    {
      "id": 14,
      "status": "pending",
      "move_in_date": "2026-07-01",
      "monthly_rent": "750.00",
      "currency": "GHS",
      "property": { "id": 7, "title": "Modern 2-Bed in East Legon", "city": "Accra" }
    }
  ],
  "meta": { "total": 1, "per_page": 15, "current_page": 1, "last_page": 1 },
  "summary": { "total_bookings": 1, "pending_bookings": 1, "confirmed_bookings": 0 }
}`},

'GET||/api/bookings/{id}': {
req: `curl ${B}/bookings/14 \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "id": 14,
    "status": "pending",
    "move_in_date": "2026-07-01",
    "move_out_date": "2027-07-01",
    "monthly_rent": "750.00",
    "security_deposit": "750.00",
    "total_amount": "1500.00",
    "currency": "GHS",
    "tenant_name": "Kofi Mensah",
    "tenant_phone": "+233501234567",
    "occupation": "Software Engineer",
    "monthly_income": "3500.00",
    "property": { "id": 7, "title": "Modern 2-Bed in East Legon" },
    "landlord": { "business_name": "Mensah Properties" }
  }
}`},

'PATCH||/api/bookings/{id}/cancel': {
req: `curl -X PATCH ${B}/bookings/14/cancel \\
  ${H}`,
res: `{
  "success": true,
  "message": "Booking cancelled successfully",
  "data": { "booking_id": 14, "new_status": "cancelled" }
}`},

'GET||/api/bookings': {
req: `curl "${B}/bookings?status=pending" \\
  ${H}`,
res: `{
  "success": true,
  "data": [
    {
      "id": 14,
      "status": "pending",
      "tenant_name": "Kofi Mensah",
      "move_in_date": "2026-07-01",
      "monthly_rent": "750.00",
      "property": { "id": 7, "title": "Modern 2-Bed in East Legon" },
      "user": { "email": "kofi@example.com", "name": "Kofi Mensah" }
    }
  ],
  "meta": { "total": 1, "per_page": 15, "current_page": 1, "last_page": 1 },
  "summary": { "total_bookings": 3, "pending_bookings": 1, "confirmed_bookings": 2 }
}`},

'PATCH||/api/bookings/{id}/confirm': {
req: `curl -X PATCH ${B}/bookings/14/confirm \\
  ${H}`,
res: `{
  "success": true,
  "message": "Booking confirmed successfully",
  "data": { "booking_id": 14, "new_status": "confirmed" }
}`},

'PATCH||/api/bookings/{id}/reject': {
req: `curl -X PATCH ${B}/bookings/14/reject \\
  ${H}`,
res: `{
  "success": true,
  "message": "Booking rejected successfully",
  "data": { "booking_id": 14, "new_status": "rejected" }
}`},

'GET||/api/notifications': {
req: `curl "${B}/notifications?is_read=false&per_page=10" \\
  ${H}`,
res: `{
  "success": true,
  "data": [
    {
      "id": 22,
      "type": "booking_confirmed",
      "title": "Booking Confirmed",
      "message": "Your booking for Modern 2-Bed in East Legon has been confirmed.",
      "is_read": false,
      "read_at": null,
      "priority": "high",
      "created_at": "2026-05-09T15:05:00.000000Z"
    }
  ],
  "meta": { "total": 5, "per_page": 10, "current_page": 1, "last_page": 1 }
}`},

'GET||/api/notifications/counts': {
req: `curl ${B}/notifications/counts \\
  ${H}`,
res: `{
  "success": true,
  "data": { "total": 12, "unread": 5, "read": 7 }
}`},

'PATCH||/api/notifications/{id}/read': {
req: `curl -X PATCH ${B}/notifications/22/read \\
  ${H}`,
res: `{
  "success": true,
  "message": "Notification marked as read",
  "data": { "id": 22, "is_read": true, "read_at": "2026-05-09T16:00:00.000000Z" }
}`},

'PATCH||/api/notifications/mark-all-read': {
req: `curl -X PATCH ${B}/notifications/mark-all-read \\
  ${H}`,
res: `{
  "success": true,
  "message": "All notifications marked as read",
  "data": { "updated_count": 5 }
}`},

'GET||/api/2fa/status': {
req: `curl ${B}/2fa/status \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "enabled": false,
    "confirmed": false,
    "required": false,
    "method": null
  }
}`},

'POST||/api/2fa/generate-secret': {
req: `curl -X POST ${B}/2fa/generate-secret \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "secret": "JBSWY3DPEHPK3PXP",
    "qr_code_url": "otpauth://totp/Efiewura:kofi%40example.com?secret=JBSWY3DPEHPK3PXP&issuer=Efiewura"
  }
}`},

'POST||/api/2fa/confirm': {
req: `curl -X POST ${B}/2fa/confirm \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{ "code": "482910" }'`,
res: `{
  "success": true,
  "message": "Two-factor authentication enabled",
  "data": {
    "recovery_codes": [
      "ABCD1234", "EF5G6789", "HIJK0LMN",
      "OPQR2STU", "VWX3YZ45", "6789ABCD",
      "EFGH1234", "IJKL5678"
    ]
  }
}`},

'GET||/api/landlord/settings': {
req: `curl ${B}/landlord/settings \\
  ${H}`,
res: `{
  "success": true,
  "message": "Landlord settings retrieved successfully",
  "data": {
    "business_name": "Mensah Properties",
    "phone": "+233201234567",
    "address": "14 Oxford Street, Osu",
    "city": "Accra",
    "state": "Greater Accra",
    "country": "Ghana",
    "commission_rate": "10.00",
    "overdue_release_days": 30,
    "status": "active",
    "verified": true
  }
}`},

'PATCH||/api/landlord/settings': {
req: `curl -X PATCH ${B}/landlord/settings \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "overdue_release_days": 14,
    "phone": "+233201234567",
    "business_name": "Mensah Premium Properties"
  }'`,
res: `{
  "success": true,
  "message": "Settings updated successfully",
  "data": {
    "business_name": "Mensah Premium Properties",
    "overdue_release_days": 14
  }
}`},

'GET||/api/admin/dashboard': {
req: `curl ${B}/admin/dashboard \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "users":      { "total": 142, "active": 138, "landlords": 24, "tenants": 98, "new_this_month": 12 },
    "properties": { "total": 87,  "active": 65,  "pending_review": 5, "new_this_month": 8 },
    "bookings":   { "total": 310, "pending": 14, "confirmed": 210, "completed": 72, "cancelled": 14, "revenue_this_month": 48750.00 },
    "notifications": { "total": 1240, "unread": 88, "critical": 3 }
  }
}`},

'GET||/api/admin/users': {
req: `curl "${B}/admin/users?role=landlord&per_page=5" \\
  ${H}`,
res: `{
  "success": true,
  "data": [
    { "id": 2, "name": "Ama Osei", "email": "ama@example.com", "role": "landlord", "status": "active", "created_at": "2026-01-15T09:00:00.000000Z" }
  ],
  "meta": { "total": 24, "per_page": 5, "current_page": 1, "last_page": 5 }
}`},

'POST||/api/admin/notifications': {
req: `curl -X POST ${B}/admin/notifications \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "user_id": 3,
    "type": "system",
    "title": "Platform Maintenance",
    "message": "Scheduled maintenance on 2026-05-15 from 02:00–04:00 UTC.",
    "priority": "high"
  }'`,
res: `{
  "success": true,
  "message": "Notification created successfully",
  "data": {
    "id": 56,
    "user_id": 3,
    "type": "system",
    "title": "Platform Maintenance",
    "is_read": false,
    "created_at": "2026-05-09T16:30:00.000000Z"
  }
}`},

'PUT||/api/properties/{id}': {
req: `curl -X PUT ${B}/properties/12 \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "title": "Updated 3-Bedroom Apartment in East Legon",
    "price": 2800,
    "description": "Newly renovated with modern kitchen.",
    "amenities": ["WiFi","AC","Security","Parking"]
  }'`,
res: `{
  "success": true,
  "message": "Property updated successfully",
  "data": {
    "id": 12,
    "title": "Updated 3-Bedroom Apartment in East Legon",
    "price": "2800.00",
    "availability_status": "available",
    "updated_at": "2026-05-09T11:20:00.000000Z"
  }
}`},

'DELETE||/api/properties/{id}': {
req: `curl -X DELETE ${B}/properties/12 \\
  ${H}`,
res: `{
  "success": true,
  "message": "Property deleted successfully"
}`},

'GET||/api/browse-available-properties': {
req: `curl "${B}/browse-available-properties?city=Accra&min_price=1000&max_price=3000" \\
  ${H}`,
res: `{
  "success": true,
  "data": [
    {
      "id": 5,
      "title": "2-Bedroom Apartment — Airport Residential",
      "price": "1800.00",
      "city": "Accra",
      "availability_status": "available",
      "bedrooms": 2,
      "landlord": { "id": 3, "business_name": "PrimeHomes GH" }
    }
  ],
  "meta": { "total": 18, "per_page": 15, "current_page": 1, "last_page": 2 }
}`},

'POST||/api/test-bookings': {
req: `curl -X POST ${B}/test-bookings \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "property_id": 5,
    "move_in_date": "2025-11-01",
    "lease_duration_months": 6,
    "tenant_name": "Test Tenant",
    "tenant_phone": "+233201234567",
    "tenant_email": "tenant@example.com"
  }'`,
res: `{
  "success": true,
  "message": "Test booking created successfully",
  "data": {
    "id": 99,
    "status": "confirmed",
    "move_in_date": "2025-11-01",
    "move_out_date": "2026-05-01",
    "note": "Test booking — past dates allowed"
  }
}`},

'GET||/api/notifications/types': {
req: `curl ${B}/notifications/types \\
  ${H}`,
res: `{
  "success": true,
  "data": [
    "new_booking_request",
    "booking_confirmed",
    "booking_rejected",
    "booking_cancelled",
    "lease_expiry_reminder",
    "payment_reminder_5_days",
    "payment_due_today",
    "payment_overdue_3_days",
    "move_in_reminder_7_days",
    "move_in_today",
    "move_out_reminder",
    "property_auto_released",
    "system"
  ]
}`},

'PATCH||/api/notifications/{id}/unread': {
req: `curl -X PATCH ${B}/notifications/42/unread \\
  ${H}`,
res: `{
  "success": true,
  "message": "Notification marked as unread"
}`},

'POST||/api/notifications/{id}/resend-email': {
req: `curl -X POST ${B}/notifications/42/resend-email \\
  ${H}`,
res: `{
  "success": true,
  "message": "Email notification resent successfully"
}`},

'DELETE||/api/notifications/{id}': {
req: `curl -X DELETE ${B}/notifications/42 \\
  ${H}`,
res: `{
  "success": true,
  "message": "Notification deleted"
}`},

'DELETE||/api/notifications/clear-read': {
req: `curl -X DELETE ${B}/notifications/clear-read \\
  ${H}`,
res: `{
  "success": true,
  "message": "All read notifications cleared",
  "data": { "deleted_count": 17 }
}`},

'POST||/api/2fa/verify': {
req: `curl -X POST ${B}/2fa/verify \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{ "code": "482019" }'`,
res: `{
  "success": true,
  "message": "Two-factor code verified successfully"
}`},

'POST||/api/2fa/disable': {
req: `curl -X POST ${B}/2fa/disable \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{ "password": "mypassword123" }'`,
res: `{
  "success": true,
  "message": "Two-factor authentication disabled"
}`},

'GET||/api/2fa/recovery-codes': {
req: `curl ${B}/2fa/recovery-codes \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "recovery_codes": [
      "A3BX9KPQ",
      "ZRMW4FDT",
      "H7NJCYE2",
      "QP5SLVB8"
    ],
    "remaining_count": 4
  }
}`},

'POST||/api/2fa/recovery-codes/regenerate': {
req: `curl -X POST ${B}/2fa/recovery-codes/regenerate \\
  ${H}`,
res: `{
  "success": true,
  "message": "Recovery codes regenerated",
  "data": {
    "recovery_codes": [
      "KX7TMRQZ", "NB2WCVFP", "DL9YAHJE", "QS4UGROK",
      "PM3ZXCWT", "FA8NVDYB", "TH6EKLQS", "VR1JPMGC"
    ]
  }
}`},

'GET||/api/admin/analytics/user-growth': {
req: `curl "${B}/admin/analytics/user-growth?period=30" \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "period": 30,
    "total_new_users": 38,
    "by_role": { "tenant": 26, "landlord": 9, "user": 3 },
    "daily": [
      { "date": "2026-05-01", "count": 2 },
      { "date": "2026-05-02", "count": 4 }
    ]
  }
}`},

'GET||/api/admin/analytics/booking-trends': {
req: `curl "${B}/admin/analytics/booking-trends?period=30" \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "period": 30,
    "total": 64,
    "by_status": { "pending": 8, "confirmed": 41, "rejected": 7, "cancelled": 8 },
    "daily": [
      { "date": "2026-05-01", "total": 3, "confirmed": 2 },
      { "date": "2026-05-02", "total": 5, "confirmed": 4 }
    ]
  }
}`},

'GET||/api/admin/analytics/revenue': {
req: `curl "${B}/admin/analytics/revenue?period=90" \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "period": 90,
    "total_revenue": 187400.00,
    "currency": "GHS",
    "monthly": [
      { "month": "2026-03", "revenue": 58200.00, "bookings": 21 },
      { "month": "2026-04", "revenue": 62750.00, "bookings": 24 },
      { "month": "2026-05", "revenue": 66450.00, "bookings": 26 }
    ]
  }
}`},

'GET||/api/admin/users/{id}': {
req: `curl ${B}/admin/users/3 \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "id": 3,
    "name": "Kofi Mensah",
    "email": "kofi@example.com",
    "role": "tenant",
    "status": "active",
    "two_factor_enabled": false,
    "tenant": {
      "id": 2,
      "occupation": "Software Engineer",
      "monthly_income": "5000.00",
      "emergency_contact_name": "Ama Mensah"
    },
    "created_at": "2026-01-20T08:30:00.000000Z"
  }
}`},

'PATCH||/api/admin/users/{id}': {
req: `curl -X PATCH ${B}/admin/users/3 \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "role": "landlord",
    "status": "active",
    "two_factor_required": true
  }'`,
res: `{
  "success": true,
  "message": "User updated successfully",
  "data": {
    "id": 3,
    "role": "landlord",
    "status": "active",
    "two_factor_required": true
  }
}`},

'POST||/api/admin/users/{id}/actions': {
req: `curl -X POST ${B}/admin/users/3/actions \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{ "action": "suspend" }'`,
res: `{
  "success": true,
  "message": "User suspended successfully",
  "data": { "id": 3, "status": "suspended" }
}`},

'DELETE||/api/admin/users/{id}': {
req: `curl -X DELETE ${B}/admin/users/3 \\
  ${H}`,
res: `{
  "success": true,
  "message": "User deleted successfully"
}`},

'GET||/api/admin/properties': {
req: `curl "${B}/admin/properties?status=pending_review&per_page=10" \\
  ${H}`,
res: `{
  "success": true,
  "data": [
    {
      "id": 7,
      "title": "Studio Apartment — Osu",
      "city": "Accra",
      "status": "active",
      "availability_status": "available",
      "price": "1200.00",
      "landlord": { "id": 2, "business_name": "CityRent Ltd" }
    }
  ],
  "meta": { "total": 87, "per_page": 10, "current_page": 1, "last_page": 9 }
}`},

'PATCH||/api/admin/properties/{id}': {
req: `curl -X PATCH ${B}/admin/properties/7 \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{ "is_active": true, "availability_status": "available" }'`,
res: `{
  "success": true,
  "message": "Property updated successfully",
  "data": { "id": 7, "is_active": true, "availability_status": "available" }
}`},

'POST||/api/admin/properties/{id}/moderate': {
req: `curl -X POST ${B}/admin/properties/7/moderate \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "action": "suspend",
    "reason": "Listing violates pricing guidelines"
  }'`,
res: `{
  "success": true,
  "message": "Property suspended",
  "data": { "id": 7, "status": "suspended", "moderation_reason": "Listing violates pricing guidelines" }
}`},

'GET||/api/admin/bookings': {
req: `curl "${B}/admin/bookings?status=pending&per_page=10" \\
  ${H}`,
res: `{
  "success": true,
  "data": [
    {
      "id": 44,
      "status": "pending",
      "move_in_date": "2026-06-01",
      "lease_duration_months": 12,
      "tenant_name": "Kofi Mensah",
      "property": { "id": 5, "title": "3-Bedroom Apartment in East Legon" },
      "landlord": { "id": 2, "business_name": "PrimeHomes GH" }
    }
  ],
  "meta": { "total": 14, "per_page": 10, "current_page": 1, "last_page": 2 }
}`},

'PATCH||/api/admin/bookings/{id}': {
req: `curl -X PATCH ${B}/admin/bookings/44 \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{ "admin_notes": "Flagged for manual review — income docs pending." }'`,
res: `{
  "success": true,
  "message": "Booking updated successfully",
  "data": { "id": 44, "admin_notes": "Flagged for manual review — income docs pending." }
}`},

'GET||/api/admin/notifications': {
req: `curl "${B}/admin/notifications?per_page=10" \\
  ${H}`,
res: `{
  "success": true,
  "data": [
    {
      "id": 56,
      "user_id": 3,
      "type": "booking_confirmed",
      "title": "Booking Confirmed",
      "is_read": false,
      "created_at": "2026-05-09T14:00:00.000000Z"
    }
  ],
  "meta": { "total": 1240, "per_page": 10, "current_page": 1, "last_page": 124 }
}`},

'GET||/api/admin/settings': {
req: `curl ${B}/admin/settings \\
  ${H}`,
res: `{
  "success": true,
  "data": {
    "platform_name": "Efiewura",
    "maintenance_mode": false,
    "allow_registrations": true,
    "require_two_factor": false,
    "max_booking_duration_months": 60,
    "email_notifications_enabled": true
  }
}`},

'PATCH||/api/admin/settings': {
req: `curl -X PATCH ${B}/admin/settings \\
  ${H} \\
  -H "Content-Type: application/json" \\
  -d '{
    "require_two_factor": true,
    "max_booking_duration_months": 24
  }'`,
res: `{
  "success": true,
  "message": "Settings updated successfully",
  "data": {
    "require_two_factor": true,
    "max_booking_duration_months": 24
  }
}`},

};
</script>
</body>
</html>
