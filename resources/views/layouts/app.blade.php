<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', "Naukaridarpan — India's #1 Mock Test Marketplace")</title>
<meta name="description" content="@yield('meta_desc', 'Buy mock tests for UPSC, SSC, Banking, Railway from verified educators. Secure exam, instant results.')">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tiro+Devanagari+Hindi:ital@0;1&family=Hind:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>window.MathJax={tex:{inlineMath:[['\\(','\\)']],displayMath:[['\\[','\\]']]},startup:{typeset:false}};</script>
<script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js" async></script>
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
@stack('head')
</head>
<body>

<!-- ── NAVBAR ──────────────────────────────────────────────────────────── -->
<nav class="navbar">
  <div class="container">
    <div class="navbar-inner">
      <a href="{{ route('home') }}" class="navbar-brand">
        <svg width="34" height="34" viewBox="0 0 40 40" fill="none">
          <circle cx="20" cy="20" r="19" fill="#0D5C63"/>
          <path d="M11 29L20 11L29 29" stroke="#E8650A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M14 23H26" stroke="#E8650A" stroke-width="2" stroke-linecap="round"/>
          <circle cx="20" cy="11" r="2.5" fill="#D4A017"/>
        </svg>
        <span>Naukari<span style="color:var(--saffron)">darpan</span></span>
      </a>

      <form action="{{ route('exams.browse') }}" method="GET" class="navbar-search">
        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke-width="2" stroke-linecap="round"/></svg>
        <input type="search" name="search" placeholder="Search UPSC, SSC, Banking…" value="{{ request('search') }}" autocomplete="off">
      </form>

      <ul class="navbar-nav">
        <li><a href="{{ route('exams.browse') }}" class="{{ request()->routeIs('exams.*') ? 'active' : '' }}">Browse Exams</a></li>
        <li><a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'active' : '' }}">Blog</a></li>
        <li><a href="{{ route('register.seller') }}" class="{{ request()->routeIs('register.seller') ? 'active' : '' }}">Teach &amp; Earn</a></li>
      </ul>

      <div class="navbar-actions">
        @guest
          <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Login</a>
          <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Sign Up Free</a>
        @else
          <div class="dropdown">
            <button class="btn btn-ghost btn-sm" style="gap:.5rem;display:flex;align-items:center;">
              <span style="width:28px;height:28px;border-radius:50%;background:var(--saffron);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;flex-shrink:0;">
                {{ strtoupper(substr(auth()->user()->name,0,2)) }}
              </span>
              <span>{{ Str::limit(auth()->user()->name,14) }}</span>
              <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <div class="dropdown-menu">
              @if(auth()->user()->isStudent())
                <a href="{{ route('student.dashboard') }}">Dashboard</a>
                <a href="{{ route('student.exams') }}">My Exams</a>
                <a href="{{ route('student.results') }}">Results</a>
                <a href="{{ route('student.profile') }}">Profile</a>
              @elseif(auth()->user()->isSeller())
                <a href="{{ route('seller.dashboard') }}">Seller Dashboard</a>
                <a href="{{ route('seller.papers') }}">My Papers</a>
                <a href="{{ route('seller.earnings') }}">Earnings</a>
                <a href="{{ route('seller.payouts') }}">Payouts</a>
                <a href="{{ route('seller.profile') }}">Profile</a>
              @else
                <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
              @endif
              <div class="dropdown-divider"></div>
              <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="dropdown-logout">Logout</button>
              </form>
            </div>
          </div>
        @endguest
      </div>
    </div>
  </div>
</nav>

<!-- Flash messages -->
@if(session('success'))
<div class="container" style="padding-top:.75rem;">
  <div class="alert alert-success">
    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="2" stroke-linecap="round"/></svg>
    {{ session('success') }}
  </div>
</div>
@endif
@if(session('error'))
<div class="container" style="padding-top:.75rem;">
  <div class="alert alert-error">
    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2" stroke-linecap="round"/></svg>
    {{ session('error') }}
  </div>
</div>
@endif

@yield('content')

<!-- ── FOOTER ──────────────────────────────────────────────────────────── -->
<footer>
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">Naukaridarpan</div>
        <p class="footer-desc">India's trusted marketplace for competitive exam mock tests. Verified educators. Secure TAO exam engine. Instant detailed results. 48-hour seller payouts.</p>
        <div style="display:flex;gap:.75rem;margin-top:1.25rem;flex-wrap:wrap;">
          <a href="#" style="color:rgba(255,255,255,.6);font-size:.82rem;font-family:var(--font-ui);">Telegram</a>
          <a href="#" style="color:rgba(255,255,255,.6);font-size:.82rem;font-family:var(--font-ui);">YouTube</a>
          <a href="#" style="color:rgba(255,255,255,.6);font-size:.82rem;font-family:var(--font-ui);">Twitter</a>
          <a href="#" style="color:rgba(255,255,255,.6);font-size:.82rem;font-family:var(--font-ui);">Instagram</a>
        </div>
      </div>
      <div>
        <p class="footer-heading">Exam Categories</p>
        <ul class="footer-links">
          <li><a href="{{ route('category','upsc') }}">UPSC IAS / IPS</a></li>
          <li><a href="{{ route('category','ssc') }}">SSC CGL / CHSL</a></li>
          <li><a href="{{ route('category','banking') }}">Banking IBPS / SBI</a></li>
          <li><a href="{{ route('category','railway') }}">Railway RRB / NTPC</a></li>
          <li><a href="{{ route('category','state-psc') }}">State PSC</a></li>
          <li><a href="{{ route('category','defence') }}">Defence NDA / CDS</a></li>
          <li><a href="{{ route('category','neet') }}">NEET / Medical</a></li>
        </ul>
      </div>
      <div>
        <p class="footer-heading">Platform</p>
        <ul class="footer-links">
          <li><a href="{{ route('register.seller') }}">Become a Seller</a></li>
          <li><a href="{{ route('blog.index') }}">Sarkari Blog</a></li>
          <li><a href="{{ route('about') }}">About Us</a></li>
          <li><a href="{{ route('contact') }}">Contact</a></li>
          <li><a href="#">Help Center</a></li>
        </ul>
      </div>
      <div>
        <p class="footer-heading">Legal</p>
        <ul class="footer-links">
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Refund Policy</a></li>
          <li><a href="#">Seller Agreement</a></li>
          <li><a href="#">Copyright Policy</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; {{ date('Y') }} Naukaridarpan Technologies Pvt. Ltd. — Built for India's exam warriors 🇮🇳</span>
      <span style="font-family:var(--font-ui);font-size:.8rem;">Payments secured by Razorpay</span>
    </div>
  </div>
</footer>

<script>
// Exam anti-cheat
if(window.__examMode){
  let sc=0;
  document.addEventListener('visibilitychange',()=>{
    if(document.hidden){sc++;const f=document.getElementById('tab_switches');if(f)f.value=sc;if(sc>=3)alert('⚠️ Warning: Multiple tab switches detected. Continued violations may auto-submit your exam.');}
  });
  document.addEventListener('contextmenu',e=>e.preventDefault());
  document.addEventListener('copy',e=>e.preventDefault());
  document.addEventListener('paste',e=>e.preventDefault());
  // Disable F12, Ctrl+U etc
  document.addEventListener('keydown',e=>{if(e.key==='F12'||(e.ctrlKey&&['u','s','a','c'].includes(e.key.toLowerCase())))e.preventDefault();});
}
function renderMath(){if(window.MathJax&&MathJax.typesetPromise)MathJax.typesetPromise();}
</script>
<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
