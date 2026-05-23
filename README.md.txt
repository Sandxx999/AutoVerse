╔══════════════════════════════════════════════════════╗
║         AUTOVERSE — Smart Car Marketplace            ║
║         Complete Project Package                     ║
╚══════════════════════════════════════════════════════╝

TECH STACK
──────────
Frontend : HTML5, CSS3, Bootstrap 5, Vanilla JavaScript
Backend  : PHP 7.4+ (compatible with 8.x)
Database : MySQL 8 / MariaDB 10.5
Server   : XAMPP (Apache + MySQL)

LOGIN CREDENTIALS
──────────────────
Admin Email    : admin@autoverse.in
Admin Password : Admin@123

FOLDER STRUCTURE
─────────────────
autoverse/
 ├── index.html           ← Main frontend page
 ├── style.css            ← All CSS styles
 ├── app.js               ← Frontend JavaScript (PHP API edition)
 ├── guide.html           ← Frontend build guide
 ├── guide-php.html       ← PHP integration guide
 ├── images/cars/         ← Car images saved here by download tool
 │
 ├── config/
 │   └── db.php           ← Database connection + helpers
 │
 ├── api/
 │   ├── cars.php         ← CRUD API for car listings
 │   ├── auth.php         ← Register / Login / Logout
 │   ├── favorites.php    ← Wishlist toggle
 │   └── enquiries.php    ← Contact / test-drive forms
 │
 ├── admin/
 │   ├── index.php        ← Admin dashboard
 │   └── login.php        ← Admin login page
 │
 ├── database/
 │   └── schema_fixed.sql ← Run this in phpMyAdmin to set up DB
 │
 └── tools/
     ├── fix_admin.php        ← Fix admin password (run once)
     ├── download_images.php  ← Auto-download car images (run once)
     ├── manual_images.php    ← Upload images manually
     └── reset_admin.php      ← Emergency password reset

SETUP STEPS (in order)
───────────────────────
STEP 1 — Install XAMPP
  Download from apachefriends.org
  Start Apache + MySQL modules

STEP 2 — Place files
  Copy this entire "autoverse" folder to:
  Windows : C:\xampp\htdocs\autoverse\
  Linux   : /opt/lampp/htdocs/autoverse/
  Mac     : /Applications/XAMPP/htdocs/autoverse/

STEP 3 — Create database
  Open : http://localhost/phpmyadmin
  Click New → Name: autoverse_db → Collation: utf8mb4_unicode_ci → Create
  Click autoverse_db → SQL tab
  Paste contents of database/schema_fixed.sql → Click Go

STEP 4 — Fix admin password
  Open : http://localhost/autoverse/tools/fix_admin.php
  You should see "Hash verifies YES ✅"

STEP 5 — Download car images
  Open : http://localhost/autoverse/tools/download_images.php
  Wait for all images to show green status

STEP 6 — View website
  Frontend : http://localhost/autoverse/index.html
  Admin    : http://localhost/autoverse/admin/login.php

API ENDPOINTS
─────────────
GET  /api/cars.php                   List all cars
GET  /api/cars.php?id=1              Single car
GET  /api/cars.php?search=tata       Search cars
GET  /api/cars.php?type=SUV          Filter by type
GET  /api/cars.php?fuel=Electric     Filter by fuel
GET  /api/cars.php?sort=price_asc    Sort results
GET  /api/cars.php?featured=1        Featured cars only
POST /api/cars.php                   Create listing (login required)
PUT  /api/cars.php?id=1              Update car (owner/admin)
DEL  /api/cars.php?id=1              Delete car (admin only)

POST /api/auth.php?action=register   Register user
POST /api/auth.php?action=login      Login
POST /api/auth.php?action=logout     Logout
GET  /api/auth.php?action=me         Get session user

GET  /api/favorites.php              Get user favourites
POST /api/favorites.php              Toggle favourite

POST /api/enquiries.php              Submit enquiry
GET  /api/enquiries.php              List enquiries (admin)
PUT  /api/enquiries.php?id=1         Update status (admin)

COMMON ISSUES
─────────────
Images not showing    → Run tools/download_images.php
Admin login fails     → Run tools/fix_admin.php
DB connection error   → Check config/db.php credentials
Blank car grid        → Open F12 console, check for errors


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>AutoVerse — Step-by-Step Build Guide</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<style>
:root{--red:#e60012;--dark:#0a0a0a;--dark2:#111318;--card:#141820;--border:rgba(255,255,255,0.08);--text:#f0f2f5;--muted:#8a9ab5;--dim:#5a6a85;--green:#00c97d;--gold:#f5a623}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--dark);color:var(--text);line-height:1.7}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-thumb{background:var(--red);border-radius:10px}
.guide-nav{position:sticky;top:0;background:rgba(10,10,10,0.9);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:.7rem 2rem;display:flex;align-items:center;justify-content:space-between;z-index:100}
.guide-nav-brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem;color:var(--red)}
.guide-nav-links{display:flex;gap:1.5rem}
.guide-nav-links a{font-size:.8rem;color:var(--muted);text-decoration:none;font-weight:600;letter-spacing:.05em;text-transform:uppercase;transition:color .2s}
.guide-nav-links a:hover{color:var(--red)}
.guide-hero{padding:5rem 2rem 3rem;text-align:center;border-bottom:1px solid var(--border);background:radial-gradient(ellipse at center top,rgba(230,0,18,0.07),transparent 60%)}
.guide-hero h1{font-family:'Syne',sans-serif;font-size:clamp(2rem,5vw,3.5rem);font-weight:800;margin-bottom:.8rem}
.guide-hero p{color:var(--muted);max-width:580px;margin:0 auto 1.5rem}
.tech-pills{display:flex;gap:.5rem;flex-wrap:wrap;justify-content:center}
.pill{font-size:.75rem;font-weight:700;padding:.3rem .9rem;border-radius:50px;background:rgba(255,255,255,0.06);border:1px solid var(--border);color:var(--muted);letter-spacing:.04em}
.pill.red{background:rgba(230,0,18,0.12);border-color:rgba(230,0,18,0.3);color:var(--red)}
.guide-body{max-width:900px;margin:0 auto;padding:3rem 2rem}
.step{margin-bottom:3.5rem}
.step-header{display:flex;align-items:flex-start;gap:1.2rem;margin-bottom:1.2rem}
.step-num{font-family:'Syne',sans-serif;font-size:3rem;font-weight:800;color:var(--red);line-height:1;min-width:50px;opacity:.7}
.step-title{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;margin-bottom:.3rem}
.step-sub{color:var(--muted);font-size:.9rem}
.step-body{padding-left:62px}
p{color:var(--muted);margin-bottom:1rem;font-size:.95rem}
h4{font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;margin:1.5rem 0 .6rem;color:var(--text)}
pre{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:1.2rem 1.5rem;overflow-x:auto;font-family:'DM Mono',monospace;font-size:.82rem;line-height:1.7;margin:1rem 0}
code{font-family:'DM Mono',monospace;font-size:.85rem;background:rgba(255,255,255,0.06);padding:.15rem .4rem;border-radius:4px;color:var(--red)}
.file-tree{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:1.2rem 1.5rem;font-family:'DM Mono',monospace;font-size:.85rem;line-height:2;margin:1rem 0}
.ft-folder{color:var(--gold)}
.ft-file{color:var(--muted)}
.ft-file.html{color:#e34c26}
.ft-file.css{color:#264de4;color:#5b8dd9}
.ft-file.js{color:var(--gold)}
.note{background:rgba(0,201,125,0.07);border:1px solid rgba(0,201,125,0.25);border-radius:10px;padding:.9rem 1.1rem;font-size:.88rem;color:var(--green);margin:1rem 0}
.warn{background:rgba(230,0,18,0.07);border:1px solid rgba(230,0,18,0.25);border-radius:10px;padding:.9rem 1.1rem;font-size:.88rem;color:#ff7070;margin:1rem 0}
.info{background:rgba(245,166,35,0.07);border:1px solid rgba(245,166,35,0.25);border-radius:10px;padding:.9rem 1.1rem;font-size:.88rem;color:var(--gold);margin:1rem 0}
.feature-list{list-style:none;display:flex;flex-direction:column;gap:.5rem;margin:1rem 0}
.feature-list li{font-size:.9rem;color:var(--muted);padding-left:1.4rem;position:relative}
.feature-list li::before{content:'→';position:absolute;left:0;color:var(--red);font-weight:700}
table{width:100%;border-collapse:collapse;margin:1rem 0;font-size:.88rem}
th{background:var(--card);color:var(--muted);font-family:'Syne',sans-serif;font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;padding:.7rem 1rem;text-align:left;border:1px solid var(--border)}
td{padding:.7rem 1rem;border:1px solid var(--border);color:var(--muted);vertical-align:top}
td:first-child{color:var(--text);font-weight:600;white-space:nowrap;font-family:'DM Mono',monospace;font-size:.82rem}
.divider{border:none;border-top:1px solid var(--border);margin:3rem 0}
</style>
</head>
<body>

<nav class="guide-nav">
  <div class="guide-nav-brand">⬡ AutoVerse Build Guide</div>
  <div class="guide-nav-links">
    <a href="#step1">Setup</a>
    <a href="#step2">HTML</a>
    <a href="#step3">CSS</a>
    <a href="#step4">JS</a>
    <a href="#step5">Features</a>
    <a href="#step6">Deploy</a>
  </div>
</nav>

<div class="guide-hero">
  <h1>Build <span style="color:var(--red)">AutoVerse</span><br/>Car Marketplace — Full Guide</h1>
  <p>A complete step-by-step guide to building a premium, sporty CarDekho-style car marketplace using HTML, CSS, Bootstrap 5 and vanilla JavaScript.</p>
  <div class="tech-pills">
    <span class="pill red">HTML5</span>
    <span class="pill red">CSS3</span>
    <span class="pill red">Bootstrap 5</span>
    <span class="pill red">Vanilla JS</span>
    <span class="pill">No Framework</span>
    <span class="pill">No Build Tool</span>
    <span class="pill">localStorage Backend</span>
  </div>
</div>

<div class="guide-body">

<!-- ══ OVERVIEW ══ -->
<div class="step">
  <div class="step-header">
    <div class="step-num">00</div>
    <div>
      <div class="step-title">Project Overview & Features</div>
      <div class="step-sub">What we're building and what's included</div>
    </div>
  </div>
  <div class="step-body">
    <p>AutoVerse is a fully functional car marketplace frontend inspired by CarDekho, Spinny and Cars24 — built with zero dependencies beyond Bootstrap 5 and Font Awesome. All "backend" features use localStorage for persistence.</p>
    <h4>Features included</h4>
    <ul class="feature-list">
      <li>🏠 Hero section with animated floating car, counter stats, multi-tab search bar</li>
      <li>🔍 Live search + filter system (type, fuel, sort, brand)</li>
      <li>🚗 Car listing grid with 12 pre-loaded cars + dynamic cards from user submissions</li>
      <li>❤️ Wishlist/favorites (localStorage persistent)</li>
      <li>📊 EMI Calculator with animated donut chart (no library)</li>
      <li>📝 Sell Your Car form → dynamically adds to listing grid</li>
      <li>🔐 User auth system (Register/Login/Logout via localStorage)</li>
      <li>📋 Car Detail modal with full specs</li>
      <li>📬 Contact form with toast notifications</li>
      <li>📱 Fully responsive (mobile, tablet, desktop)</li>
      <li>🎨 Premium dark theme with red accent, Bebas Neue + Syne typography</li>
      <li>✨ Scroll animations, floating car animation, counter animation</li>
    </ul>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 1: FILE STRUCTURE ══ -->
<div class="step" id="step1">
  <div class="step-header">
    <div class="step-num">01</div>
    <div>
      <div class="step-title">Project Setup & File Structure</div>
      <div class="step-sub">Create your folder structure and link dependencies</div>
    </div>
  </div>
  <div class="step-body">
    <p>Create a project folder called <code>autoverse/</code> with these 3 files — no npm, no build tools required.</p>
    <div class="file-tree">
      <span class="ft-folder">📁 autoverse/</span><br/>
      &nbsp;&nbsp;<span class="ft-file html">├── 📄 index.html</span> &nbsp;&nbsp;<span style="color:var(--dim)">← All markup, modals, sections</span><br/>
      &nbsp;&nbsp;<span class="ft-file css">├── 🎨 style.css</span> &nbsp;&nbsp;<span style="color:var(--dim)">← All design, themes, animations</span><br/>
      &nbsp;&nbsp;<span class="ft-file js">└── ⚡ app.js</span> &nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--dim)">← All logic, data, filtering, auth</span>
    </div>
    <h4>Dependencies (CDN — no download needed)</h4>
    <p>Add these in the <code>&lt;head&gt;</code> of index.html:</p>
    <pre>&lt;!-- Bootstrap 5 CSS --&gt;
&lt;link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/&gt;

&lt;!-- Font Awesome Icons --&gt;
&lt;link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/&gt;

&lt;!-- Google Fonts: Bebas Neue + Syne + DM Sans --&gt;
&lt;link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&amp;family=Syne:wght@400;600;700;800&amp;family=DM+Sans:wght@300;400;500&amp;display=swap" rel="stylesheet"/&gt;

&lt;!-- Your CSS --&gt;
&lt;link rel="stylesheet" href="style.css"/&gt;</pre>
    <p>Add these before <code>&lt;/body&gt;</code>:</p>
    <pre>&lt;!-- Bootstrap 5 JS --&gt;
&lt;script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"&gt;&lt;/script&gt;

&lt;!-- Your JS --&gt;
&lt;script src="app.js"&gt;&lt;/script&gt;</pre>
    <div class="note">✅ That's all you need! Bootstrap 5 handles responsive grid, navbar collapse, and utility classes. Font Awesome provides all icons.</div>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 2: HTML STRUCTURE ══ -->
<div class="step" id="step2">
  <div class="step-header">
    <div class="step-num">02</div>
    <div>
      <div class="step-title">HTML Structure</div>
      <div class="step-sub">Building all 9 major sections of the page</div>
    </div>
  </div>
  <div class="step-body">
    <h4>Page Architecture (top to bottom)</h4>
    <table>
      <thead><tr><th>Section</th><th>Element/ID</th><th>Purpose</th></tr></thead>
      <tbody>
        <tr><td>&lt;nav&gt;</td><td>#mainNav</td><td>Sticky navbar with logo, links, auth buttons</td></tr>
        <tr><td>&lt;section&gt;</td><td>#home</td><td>Hero with car image, search card, stats</td></tr>
        <tr><td>&lt;section&gt;</td><td>.brands-strip</td><td>Horizontal brand filter chips</td></tr>
        <tr><td>&lt;section&gt;</td><td>#search-section</td><td>Filter bar + dynamic car grid</td></tr>
        <tr><td>&lt;section&gt;</td><td>#features</td><td>6-card why-us grid</td></tr>
        <tr><td>&lt;section&gt;</td><td>#listings</td><td>Featured cars from data</td></tr>
        <tr><td>&lt;section&gt;</td><td>#emi-section</td><td>Sliders + donut chart EMI calculator</td></tr>
        <tr><td>&lt;section&gt;</td><td>#contact</td><td>Contact info + enquiry form</td></tr>
        <tr><td>&lt;footer&gt;</td><td>.av-footer</td><td>Links, brand, copyright</td></tr>
      </tbody>
    </table>

    <h4>Navbar Structure</h4>
    <p>Uses Bootstrap's <code>navbar-expand-lg</code> with custom CSS classes on top:</p>
    <pre>&lt;nav class="navbar navbar-expand-lg fixed-top av-navbar" id="mainNav"&gt;
  &lt;div class="container-fluid px-4"&gt;
    &lt;a class="navbar-brand av-brand" href="#"&gt;
      &lt;span class="brand-icon"&gt;⬡&lt;/span&gt; AUTO&lt;span class="brand-accent"&gt;VERSE&lt;/span&gt;
    &lt;/a&gt;
    &lt;!-- Bootstrap mobile toggle --&gt;
    &lt;button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu"&gt;...&lt;/button&gt;
    &lt;div class="collapse navbar-collapse" id="navMenu"&gt;
      &lt;!-- nav links, auth buttons --&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/nav&gt;</pre>

    <h4>Hero Section Key Parts</h4>
    <pre>&lt;section class="hero-section" id="home"&gt;
  &lt;div class="hero-bg-overlay"&gt;&lt;/div&gt;    &lt;!-- gradient overlay --&gt;
  &lt;div class="hero-grid-lines"&gt;&lt;/div&gt;    &lt;!-- CSS grid texture --&gt;
  &lt;div class="hero-content container"&gt;
    &lt;h1 class="hero-title"&gt;FIND YOUR&lt;br/&gt;
      &lt;span class="hero-accent"&gt;DREAM RIDE&lt;/span&gt;
    &lt;/h1&gt;
    &lt;div class="hero-search-card"&gt;...&lt;/div&gt;    &lt;!-- search card --&gt;
    &lt;div class="hero-stats"&gt;...&lt;/div&gt;          &lt;!-- animated counters --&gt;
  &lt;/div&gt;
  &lt;div class="hero-car-visual"&gt;
    &lt;img class="hero-car-img" .../&gt;     &lt;!-- floating car --&gt;
  &lt;/div&gt;
&lt;/section&gt;</pre>

    <h4>Car Grid Container</h4>
    <p>The grid is empty in HTML — cards are rendered by JavaScript into these containers:</p>
    <pre>&lt;div class="car-grid" id="carGrid"&gt;&lt;/div&gt;         &lt;!-- All cars (filtered) --&gt;
&lt;div class="featured-grid" id="featuredGrid"&gt;&lt;/div&gt; &lt;!-- Featured cars only --&gt;</pre>

    <h4>EMI Calculator</h4>
    <p>Uses HTML <code>&lt;input type="range"&gt;</code> sliders for all 4 parameters:</p>
    <pre>&lt;input type="range" id="emiPrice" min="300000" max="10000000"
  step="50000" value="1500000" oninput="calcEMI()"/&gt;
&lt;span id="emiPriceVal"&gt;₹15,00,000&lt;/span&gt;

&lt;!-- Canvas for donut chart --&gt;
&lt;canvas id="emiDonut" width="200" height="200"&gt;&lt;/canvas&gt;</pre>

    <h4>Modals (3 total)</h4>
    <pre>&lt;div class="av-modal" id="carModal"&gt;...&lt;/div&gt;   &lt;!-- Car details --&gt;
&lt;div class="av-modal" id="sellModal"&gt;...&lt;/div&gt;  &lt;!-- Sell listing form --&gt;
&lt;div class="av-modal" id="loginModal"&gt;...&lt;/div&gt; &lt;!-- Auth form --&gt;</pre>
    <div class="note">✅ Modals use CSS <code>display:flex</code> / <code>display:none</code> toggled by JS — no Bootstrap modal JS needed.</div>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 3: CSS ══ -->
<div class="step" id="step3">
  <div class="step-header">
    <div class="step-num">03</div>
    <div>
      <div class="step-title">CSS Design System</div>
      <div class="step-sub">Variables, themes, animations and component styles</div>
    </div>
  </div>
  <div class="step-body">
    <h4>CSS Variables (Design Tokens)</h4>
    <p>Defined on <code>:root</code> — change these to completely retheme the app:</p>
    <pre>:root {
  --red:       #e60012;     /* Primary accent — all CTAs, highlights */
  --red-dark:  #b3000e;     /* Hover state for red elements */
  --red-glow:  rgba(230, 0, 18, 0.35); /* Glow shadows */
  --black:     #0a0a0a;     /* Page background */
  --dark:      #111318;     /* Section backgrounds */
  --dark2:     #181c24;     /* Alternate sections */
  --dark3:     #1e2330;     /* Input/card inner backgrounds */
  --card-bg:   #141820;     /* Card backgrounds */
  --text:      #f0f2f5;     /* Primary text */
  --text-muted:#8a9ab5;     /* Secondary text */
  --text-dim:  #5a6a85;     /* Placeholder/disabled text */
  --gold:      #f5a623;     /* Star ratings, highlights */
  --font-head: 'Bebas Neue'; /* Display font — headings */
  --font-body: 'DM Sans';    /* Body text */
  --font-ui:   'Syne';       /* UI elements, buttons, labels */
}</pre>

    <h4>Navbar Scroll Effect</h4>
    <p>The navbar gains a solid background when user scrolls down:</p>
    <pre>/* CSS: frosted glass start state */
.av-navbar {
  background: rgba(10,10,10,0.6);
  backdrop-filter: blur(20px);
  transition: background 0.3s;
}

/* JS adds this class on scroll */
.av-navbar.scrolled {
  background: rgba(10,10,10,0.95);
  border-bottom-color: var(--red);
}</pre>

    <h4>Hero Background Layers</h4>
    <p>Multiple layered backgrounds create depth:</p>
    <pre>.hero-section {
  background: radial-gradient(ellipse at 70% 50%, #1a0005 0%, var(--black) 60%);
}
.hero-bg-overlay {
  background: 
    radial-gradient(circle at 80% 50%, rgba(230,0,18,0.08) 0%, transparent 60%),
    radial-gradient(circle at 20% 80%, rgba(255,107,0,0.05) 0%, transparent 40%);
}
.hero-grid-lines {
  background-image: 
    linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 60px 60px; /* grid line spacing */
}</pre>

    <h4>Floating Car Animation</h4>
    <pre>.hero-car-img {
  animation: floatCar 4s ease-in-out infinite;
}

@keyframes floatCar {
  0%, 100% { transform: translateY(0); }
  50%       { transform: translateY(-18px); }
}</pre>

    <h4>Card Hover Effects</h4>
    <pre>.car-card {
  transition: transform .25s, box-shadow .25s, border-color .25s;
}
.car-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 40px rgba(0,0,0,0.5);
}
/* Zoom image on hover */
.car-card:hover .car-img-wrap img {
  transform: scale(1.06);
}</pre>

    <h4>Card Entrance Animation</h4>
    <pre>.car-card {
  animation: fadeInUp .4s ease both;
}

/* Stagger delay applied via inline style in JS */
/* style="animation-delay: 0.05s" */

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: translateY(0); }
}</pre>

    <h4>Range Slider Styling</h4>
    <pre>input[type="range"] {
  -webkit-appearance: none;
  height: 4px;
  background: var(--dark3);
  border-radius: 4px;
}
input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 18px; height: 18px;
  background: var(--red);
  border-radius: 50%;
  box-shadow: 0 0 0 4px rgba(230,0,18,0.2);
}</pre>

    <h4>Toast Notification</h4>
    <pre>.av-toast {
  position: fixed;
  bottom: 2rem; right: 2rem;
  transform: translateX(200%);   /* hidden off-screen */
  transition: transform .3s ease;
}
.av-toast.show {
  transform: translateX(0);      /* slide in */
}</pre>

    <div class="info">💡 <strong>Responsive Breakpoints:</strong> The layout collapses hero car visual at 991px, switches search form to column at 768px, and stacks filter bar at 576px.</div>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 4: JAVASCRIPT ══ -->
<div class="step" id="step4">
  <div class="step-header">
    <div class="step-num">04</div>
    <div>
      <div class="step-title">JavaScript Architecture</div>
      <div class="step-sub">Data store, rendering engine, filtering and state management</div>
    </div>
  </div>
  <div class="step-body">
    <h4>App State Object</h4>
    <p>All dynamic state lives in one central object:</p>
    <pre>const AppState = {
  allCars:      [...CAR_DATABASE],   // source of truth
  filtered:     [...CAR_DATABASE],   // current filtered view
  displayCount: 8,                   // for "load more"
  favorites:    JSON.parse(localStorage.getItem('av_favorites') || '[]'),
  user:         JSON.parse(localStorage.getItem('av_user') || 'null'),
  userListings: JSON.parse(localStorage.getItem('av_listings') || '[]'),
};</pre>

    <h4>Car Data Structure</h4>
    <p>Each car object in <code>CAR_DATABASE</code> follows this schema:</p>
    <pre>const CAR_DATABASE = [
  {
    id: 1,
    brand: 'Tata', model: 'Nexon EV Max',
    year: 2024, price: 1899000,
    type: 'SUV', fuel: 'Electric',
    km: 0,
    rating: 4.8, reviews: 320,
    badge: 'New',        // null | 'New' | 'Featured'
    featured: true,      // appears in featured section
    img: 'https://...',  // car image URL
    specs: {
      engine: 'Electric 40.5 kWh',
      power: '143 bhp',
      torque: '250 Nm',
      transmission: 'Auto',
      seats: 5,
      range: '453 km'
    },
    desc: 'Description shown in detail modal.'
  },
  // ... 11 more cars
];</pre>

    <h4>Card Renderer (createCarCard)</h4>
    <p>Returns an HTML string for each car. The key parts:</p>
    <pre>function createCarCard(car, delay = 0) {
  const isFav = AppState.favorites.includes(car.id);
  const emi   = Math.round(calcEMIValue(car.price * 0.8, 9, 48));

  return `
    &lt;div class="car-card" style="animation-delay:${delay}s"
         onclick="openCarModal(${car.id})"&gt;
      &lt;!-- image, badge, fav button --&gt;
      &lt;!-- rating stars --&gt;
      &lt;!-- title, meta (year/fuel/type) --&gt;
      &lt;!-- price + EMI hint --&gt;
      &lt;!-- View Details + Call buttons --&gt;
    &lt;/div&gt;
  `;
}</pre>

    <h4>Filtering Pipeline</h4>
    <p>All 4 filters are applied together on every change:</p>
    <pre>function applyFilters() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const type   = document.getElementById('filterType').value;
  const fuel   = document.getElementById('filterFuel').value;
  const sort   = document.getElementById('filterSort').value;

  // 1. Combine DB + user-submitted cars
  let cars = [...AppState.allCars, ...AppState.userListings];

  // 2. Text search (brand + model + type)
  if (search) {
    cars = cars.filter(c =>
      `${c.brand} ${c.model} ${c.type}`.toLowerCase().includes(search)
    );
  }

  // 3. Dropdown filters
  if (type) cars = cars.filter(c => c.type === type);
  if (fuel) cars = cars.filter(c => c.fuel === fuel);

  // 4. Sort
  switch (sort) {
    case 'price-asc':  cars.sort((a,b) => a.price - b.price); break;
    case 'price-desc': cars.sort((a,b) => b.price - a.price); break;
    case 'year-desc':  cars.sort((a,b) => b.year  - a.year);  break;
    case 'rating':     cars.sort((a,b) => b.rating - a.rating); break;
  }

  // 5. Update state and re-render
  AppState.filtered = cars;
  AppState.displayCount = 8;
  renderCarGrid();
}</pre>

    <h4>EMI Calculation Formula</h4>
    <pre>// Standard reducing balance EMI formula
function calcEMIValue(principal, annualRate, months) {
  const r = annualRate / 100 / 12;    // monthly rate
  return principal * r *
    Math.pow(1+r, months) /
    (Math.pow(1+r, months) - 1);
}</pre>

    <h4>Donut Chart (Pure Canvas)</h4>
    <p>No charting library — drawn directly with HTML5 Canvas:</p>
    <pre>function drawDonut(principal, interest) {
  const canvas = document.getElementById('emiDonut');
  const ctx = canvas.getContext('2d');
  const total = principal + interest;
  const cx = 100, cy = 100, outerR = 75, innerR = 45;
  const startAngle = -Math.PI / 2;
  const principalAngle = (principal / total) * 2 * Math.PI;

  ctx.clearRect(0, 0, 200, 200);

  // Draw principal slice (red)
  ctx.beginPath();
  ctx.moveTo(cx, cy);
  ctx.arc(cx, cy, outerR, startAngle, startAngle + principalAngle);
  ctx.fillStyle = '#e60012';
  ctx.fill();

  // Draw interest slice (orange)
  ctx.beginPath();
  ctx.moveTo(cx, cy);
  ctx.arc(cx, cy, outerR, startAngle + principalAngle, startAngle + 2*Math.PI);
  ctx.fillStyle = '#ff6b00';
  ctx.fill();

  // Punch inner hole (donut effect)
  ctx.beginPath();
  ctx.arc(cx, cy, innerR, 0, 2 * Math.PI);
  ctx.fillStyle = '#1e2330';
  ctx.fill();
}</pre>

    <h4>Price Formatter</h4>
    <pre>function formatPrice(n) {
  if (n >= 10000000) return '₹' + (n / 10000000).toFixed(2) + ' Cr';
  if (n >= 100000)   return '₹' + (n / 100000).toFixed(2) + ' L';
  return '₹' + n.toLocaleString('en-IN');
}</pre>

    <h4>Counter Animation (Intersection Observer)</h4>
    <pre>function initCounters() {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.dataset.count);
        let current = 0;
        const step = target / 60;    // 60 frames
        const timer = setInterval(() => {
          current = Math.min(current + step, target);
          el.textContent = Math.round(current).toLocaleString('en-IN');
          if (current >= target) clearInterval(timer);
        }, 20);                       // ~50fps
        observer.unobserve(el);      // animate only once
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.hstat-num').forEach(el => observer.observe(el));
}</pre>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 5: FEATURES ══ -->
<div class="step" id="step5">
  <div class="step-header">
    <div class="step-num">05</div>
    <div>
      <div class="step-title">Feature Deep-Dives</div>
      <div class="step-sub">Auth, Sell Car, Wishlist, Modals, Toast</div>
    </div>
  </div>
  <div class="step-body">
    <h4>Authentication Flow (localStorage)</h4>
    <p>Users are stored in <code>localStorage</code> as a JSON array. No backend required:</p>
    <pre>// REGISTER
function registerUser(e) {
  e.preventDefault();
  const users = JSON.parse(localStorage.getItem('av_users') || '[]');
  const newUser = { name, email, pass };
  users.push(newUser);
  localStorage.setItem('av_users', JSON.stringify(users));

  // Auto-login after register
  AppState.user = newUser;
  localStorage.setItem('av_user', JSON.stringify(newUser));
  updateAuthUI();
}

// LOGIN
function loginUser(e) {
  e.preventDefault();
  const users = JSON.parse(localStorage.getItem('av_users') || '[]');
  const user  = users.find(u => u.email === email);
  if (!user) { showToast('Account not found'); return; }

  AppState.user = user;
  localStorage.setItem('av_user', JSON.stringify(user));
  updateAuthUI();
}

// Update navbar button text
function updateAuthUI() {
  const btn = document.getElementById('loginBtn');
  if (AppState.user) {
    btn.textContent = `👤 ${AppState.user.name} · Sign Out`;
  } else {
    btn.textContent = 'Sign In';
  }
}</pre>

    <h4>Sell Car → Live Listing</h4>
    <p>New listing from the Sell form is instantly added to the car grid:</p>
    <pre>function submitListing(e) {
  e.preventDefault();

  const newCar = {
    id:    Date.now(),    // unique ID using timestamp
    brand: document.getElementById('sellBrand').value,
    model: document.getElementById('sellModel').value,
    // ... other fields
    img: `https://placehold.co/400x220/141820/e60012?text=...`,
  };

  // Add to user listings (localStorage persistent)
  AppState.userListings.push(newCar);
  localStorage.setItem('av_listings', JSON.stringify(AppState.userListings));

  // Add to all cars and refresh grid
  AppState.allCars.push(newCar);
  AppState.filtered = [...AppState.allCars];
  renderCarGrid();

  showToast('Your listing has been posted!', 'success');
}</pre>

    <h4>Wishlist / Favorites</h4>
    <pre>function toggleFav(event, id) {
  if (event) event.stopPropagation();   // don't open modal

  const idx = AppState.favorites.indexOf(id);
  if (idx === -1) {
    AppState.favorites.push(id);        // add to favorites
  } else {
    AppState.favorites.splice(idx, 1);  // remove from favorites
  }

  // Persist to localStorage
  localStorage.setItem('av_favorites', JSON.stringify(AppState.favorites));

  // Re-render to update heart icons
  renderCarGrid();
  renderFeatured();
}</pre>

    <h4>Toast Notification System</h4>
    <pre>function showToast(msg, type = '') {
  const toast = document.getElementById('avToast');
  toast.textContent = msg;
  toast.className = 'av-toast show' + (type ? ' ' + type : '');

  // Auto hide after 3.5s
  setTimeout(() => toast.classList.remove('show'), 3500);
}</pre>

    <h4>Modal Open/Close</h4>
    <pre>// Open: set display to flex (CSS centers it)
document.getElementById('sellModal').style.display = 'flex';

// Close button
document.getElementById('sellModal').style.display = 'none';

// Close on backdrop click
modal.addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});

// Close on ESC key
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    ['carModal','sellModal','loginModal'].forEach(id => {
      document.getElementById(id).style.display = 'none';
    });
  }
});</pre>

    <div class="note">✅ <strong>Gated Buy Now:</strong> Clicking "Buy Now" checks <code>AppState.user</code>. If not logged in, it closes the car modal and opens the login modal automatically.</div>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 6: DEPLOY ══ -->
<div class="step" id="step6">
  <div class="step-header">
    <div class="step-num">06</div>
    <div>
      <div class="step-title">Deploy & Extend</div>
      <div class="step-sub">Go live and upgrade paths</div>
    </div>
  </div>
  <div class="step-body">
    <h4>Deploy for free (3 options)</h4>
    <table>
      <thead><tr><th>Platform</th><th>Steps</th><th>URL</th></tr></thead>
      <tbody>
        <tr><td>GitHub Pages</td><td>Push to repo → Settings → Pages → Deploy from main</td><td>username.github.io/autoverse</td></tr>
        <tr><td>Netlify</td><td>Drag &amp; drop folder on netlify.com/drop</td><td>random-name.netlify.app</td></tr>
        <tr><td>Vercel</td><td>vercel.com → New → import git repo</td><td>autoverse.vercel.app</td></tr>
      </tbody>
    </table>

    <h4>Adding More Cars</h4>
    <p>Just add objects to the <code>CAR_DATABASE</code> array in <code>app.js</code> following the same schema. The grid re-renders automatically.</p>

    <h4>Backend Upgrade Path</h4>
    <p>Replace localStorage calls with API calls when ready:</p>
    <pre>// Current (localStorage):
localStorage.setItem('av_user', JSON.stringify(user));

// Upgrade to (REST API):
const res = await fetch('/api/auth/login', {
  method: 'POST',
  body: JSON.stringify({ email, password }),
  headers: { 'Content-Type': 'application/json' }
});
const user = await res.json();</pre>

    <h4>Recommended Backend Stack</h4>
    <ul class="feature-list">
      <li><strong>Node.js + Express</strong> — REST API for cars, users, enquiries</li>
      <li><strong>MongoDB</strong> — flexible schema for cars and users</li>
      <li><strong>Cloudinary</strong> — image uploads for car listings</li>
      <li><strong>JWT</strong> — replace localStorage auth with tokens</li>
      <li><strong>Razorpay</strong> — booking payments and EMI integration</li>
    </ul>

    <div class="note">✅ The frontend is already structured to make this migration easy — all data access goes through AppState and centralized functions.</div>
    <div class="warn">⚠️ Remember: localStorage auth is only for prototyping. Never store passwords in plaintext in production.</div>
  </div>
</div>

</div><!-- /guide-body -->
</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>AutoVerse — Step-by-Step Build Guide</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<style>
:root{--red:#e60012;--dark:#0a0a0a;--dark2:#111318;--card:#141820;--border:rgba(255,255,255,0.08);--text:#f0f2f5;--muted:#8a9ab5;--dim:#5a6a85;--green:#00c97d;--gold:#f5a623}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--dark);color:var(--text);line-height:1.7}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-thumb{background:var(--red);border-radius:10px}
.guide-nav{position:sticky;top:0;background:rgba(10,10,10,0.9);backdrop-filter:blur(16px);border-bottom:1px solid var(--border);padding:.7rem 2rem;display:flex;align-items:center;justify-content:space-between;z-index:100}
.guide-nav-brand{font-family:'Syne',sans-serif;font-weight:800;font-size:1.1rem;color:var(--red)}
.guide-nav-links{display:flex;gap:1.5rem}
.guide-nav-links a{font-size:.8rem;color:var(--muted);text-decoration:none;font-weight:600;letter-spacing:.05em;text-transform:uppercase;transition:color .2s}
.guide-nav-links a:hover{color:var(--red)}
.guide-hero{padding:5rem 2rem 3rem;text-align:center;border-bottom:1px solid var(--border);background:radial-gradient(ellipse at center top,rgba(230,0,18,0.07),transparent 60%)}
.guide-hero h1{font-family:'Syne',sans-serif;font-size:clamp(2rem,5vw,3.5rem);font-weight:800;margin-bottom:.8rem}
.guide-hero p{color:var(--muted);max-width:580px;margin:0 auto 1.5rem}
.tech-pills{display:flex;gap:.5rem;flex-wrap:wrap;justify-content:center}
.pill{font-size:.75rem;font-weight:700;padding:.3rem .9rem;border-radius:50px;background:rgba(255,255,255,0.06);border:1px solid var(--border);color:var(--muted);letter-spacing:.04em}
.pill.red{background:rgba(230,0,18,0.12);border-color:rgba(230,0,18,0.3);color:var(--red)}
.guide-body{max-width:900px;margin:0 auto;padding:3rem 2rem}
.step{margin-bottom:3.5rem}
.step-header{display:flex;align-items:flex-start;gap:1.2rem;margin-bottom:1.2rem}
.step-num{font-family:'Syne',sans-serif;font-size:3rem;font-weight:800;color:var(--red);line-height:1;min-width:50px;opacity:.7}
.step-title{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:800;margin-bottom:.3rem}
.step-sub{color:var(--muted);font-size:.9rem}
.step-body{padding-left:62px}
p{color:var(--muted);margin-bottom:1rem;font-size:.95rem}
h4{font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;margin:1.5rem 0 .6rem;color:var(--text)}
pre{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:1.2rem 1.5rem;overflow-x:auto;font-family:'DM Mono',monospace;font-size:.82rem;line-height:1.7;margin:1rem 0}
code{font-family:'DM Mono',monospace;font-size:.85rem;background:rgba(255,255,255,0.06);padding:.15rem .4rem;border-radius:4px;color:var(--red)}
.file-tree{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:1.2rem 1.5rem;font-family:'DM Mono',monospace;font-size:.85rem;line-height:2;margin:1rem 0}
.ft-folder{color:var(--gold)}
.ft-file{color:var(--muted)}
.ft-file.html{color:#e34c26}
.ft-file.css{color:#264de4;color:#5b8dd9}
.ft-file.js{color:var(--gold)}
.note{background:rgba(0,201,125,0.07);border:1px solid rgba(0,201,125,0.25);border-radius:10px;padding:.9rem 1.1rem;font-size:.88rem;color:var(--green);margin:1rem 0}
.warn{background:rgba(230,0,18,0.07);border:1px solid rgba(230,0,18,0.25);border-radius:10px;padding:.9rem 1.1rem;font-size:.88rem;color:#ff7070;margin:1rem 0}
.info{background:rgba(245,166,35,0.07);border:1px solid rgba(245,166,35,0.25);border-radius:10px;padding:.9rem 1.1rem;font-size:.88rem;color:var(--gold);margin:1rem 0}
.feature-list{list-style:none;display:flex;flex-direction:column;gap:.5rem;margin:1rem 0}
.feature-list li{font-size:.9rem;color:var(--muted);padding-left:1.4rem;position:relative}
.feature-list li::before{content:'→';position:absolute;left:0;color:var(--red);font-weight:700}
table{width:100%;border-collapse:collapse;margin:1rem 0;font-size:.88rem}
th{background:var(--card);color:var(--muted);font-family:'Syne',sans-serif;font-size:.75rem;letter-spacing:.08em;text-transform:uppercase;padding:.7rem 1rem;text-align:left;border:1px solid var(--border)}
td{padding:.7rem 1rem;border:1px solid var(--border);color:var(--muted);vertical-align:top}
td:first-child{color:var(--text);font-weight:600;white-space:nowrap;font-family:'DM Mono',monospace;font-size:.82rem}
.divider{border:none;border-top:1px solid var(--border);margin:3rem 0}
</style>
</head>
<body>

<nav class="guide-nav">
  <div class="guide-nav-brand">⬡ AutoVerse Build Guide</div>
  <div class="guide-nav-links">
    <a href="#step1">Setup</a>
    <a href="#step2">HTML</a>
    <a href="#step3">CSS</a>
    <a href="#step4">JS</a>
    <a href="#step5">Features</a>
    <a href="#step6">Deploy</a>
  </div>
</nav>

<div class="guide-hero">
  <h1>Build <span style="color:var(--red)">AutoVerse</span><br/>Car Marketplace — Full Guide</h1>
  <p>A complete step-by-step guide to building a premium, sporty CarDekho-style car marketplace using HTML, CSS, Bootstrap 5 and vanilla JavaScript.</p>
  <div class="tech-pills">
    <span class="pill red">HTML5</span>
    <span class="pill red">CSS3</span>
    <span class="pill red">Bootstrap 5</span>
    <span class="pill red">Vanilla JS</span>
    <span class="pill">No Framework</span>
    <span class="pill">No Build Tool</span>
    <span class="pill">localStorage Backend</span>
  </div>
</div>

<div class="guide-body">

<!-- ══ OVERVIEW ══ -->
<div class="step">
  <div class="step-header">
    <div class="step-num">00</div>
    <div>
      <div class="step-title">Project Overview & Features</div>
      <div class="step-sub">What we're building and what's included</div>
    </div>
  </div>
  <div class="step-body">
    <p>AutoVerse is a fully functional car marketplace frontend inspired by CarDekho, Spinny and Cars24 — built with zero dependencies beyond Bootstrap 5 and Font Awesome. All "backend" features use localStorage for persistence.</p>
    <h4>Features included</h4>
    <ul class="feature-list">
      <li>🏠 Hero section with animated floating car, counter stats, multi-tab search bar</li>
      <li>🔍 Live search + filter system (type, fuel, sort, brand)</li>
      <li>🚗 Car listing grid with 12 pre-loaded cars + dynamic cards from user submissions</li>
      <li>❤️ Wishlist/favorites (localStorage persistent)</li>
      <li>📊 EMI Calculator with animated donut chart (no library)</li>
      <li>📝 Sell Your Car form → dynamically adds to listing grid</li>
      <li>🔐 User auth system (Register/Login/Logout via localStorage)</li>
      <li>📋 Car Detail modal with full specs</li>
      <li>📬 Contact form with toast notifications</li>
      <li>📱 Fully responsive (mobile, tablet, desktop)</li>
      <li>🎨 Premium dark theme with red accent, Bebas Neue + Syne typography</li>
      <li>✨ Scroll animations, floating car animation, counter animation</li>
    </ul>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 1: FILE STRUCTURE ══ -->
<div class="step" id="step1">
  <div class="step-header">
    <div class="step-num">01</div>
    <div>
      <div class="step-title">Project Setup & File Structure</div>
      <div class="step-sub">Create your folder structure and link dependencies</div>
    </div>
  </div>
  <div class="step-body">
    <p>Create a project folder called <code>autoverse/</code> with these 3 files — no npm, no build tools required.</p>
    <div class="file-tree">
      <span class="ft-folder">📁 autoverse/</span><br/>
      &nbsp;&nbsp;<span class="ft-file html">├── 📄 index.html</span> &nbsp;&nbsp;<span style="color:var(--dim)">← All markup, modals, sections</span><br/>
      &nbsp;&nbsp;<span class="ft-file css">├── 🎨 style.css</span> &nbsp;&nbsp;<span style="color:var(--dim)">← All design, themes, animations</span><br/>
      &nbsp;&nbsp;<span class="ft-file js">└── ⚡ app.js</span> &nbsp;&nbsp;&nbsp;&nbsp;<span style="color:var(--dim)">← All logic, data, filtering, auth</span>
    </div>
    <h4>Dependencies (CDN — no download needed)</h4>
    <p>Add these in the <code>&lt;head&gt;</code> of index.html:</p>
    <pre>&lt;!-- Bootstrap 5 CSS --&gt;
&lt;link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/&gt;

&lt;!-- Font Awesome Icons --&gt;
&lt;link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/&gt;

&lt;!-- Google Fonts: Bebas Neue + Syne + DM Sans --&gt;
&lt;link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&amp;family=Syne:wght@400;600;700;800&amp;family=DM+Sans:wght@300;400;500&amp;display=swap" rel="stylesheet"/&gt;

&lt;!-- Your CSS --&gt;
&lt;link rel="stylesheet" href="style.css"/&gt;</pre>
    <p>Add these before <code>&lt;/body&gt;</code>:</p>
    <pre>&lt;!-- Bootstrap 5 JS --&gt;
&lt;script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"&gt;&lt;/script&gt;

&lt;!-- Your JS --&gt;
&lt;script src="app.js"&gt;&lt;/script&gt;</pre>
    <div class="note">✅ That's all you need! Bootstrap 5 handles responsive grid, navbar collapse, and utility classes. Font Awesome provides all icons.</div>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 2: HTML STRUCTURE ══ -->
<div class="step" id="step2">
  <div class="step-header">
    <div class="step-num">02</div>
    <div>
      <div class="step-title">HTML Structure</div>
      <div class="step-sub">Building all 9 major sections of the page</div>
    </div>
  </div>
  <div class="step-body">
    <h4>Page Architecture (top to bottom)</h4>
    <table>
      <thead><tr><th>Section</th><th>Element/ID</th><th>Purpose</th></tr></thead>
      <tbody>
        <tr><td>&lt;nav&gt;</td><td>#mainNav</td><td>Sticky navbar with logo, links, auth buttons</td></tr>
        <tr><td>&lt;section&gt;</td><td>#home</td><td>Hero with car image, search card, stats</td></tr>
        <tr><td>&lt;section&gt;</td><td>.brands-strip</td><td>Horizontal brand filter chips</td></tr>
        <tr><td>&lt;section&gt;</td><td>#search-section</td><td>Filter bar + dynamic car grid</td></tr>
        <tr><td>&lt;section&gt;</td><td>#features</td><td>6-card why-us grid</td></tr>
        <tr><td>&lt;section&gt;</td><td>#listings</td><td>Featured cars from data</td></tr>
        <tr><td>&lt;section&gt;</td><td>#emi-section</td><td>Sliders + donut chart EMI calculator</td></tr>
        <tr><td>&lt;section&gt;</td><td>#contact</td><td>Contact info + enquiry form</td></tr>
        <tr><td>&lt;footer&gt;</td><td>.av-footer</td><td>Links, brand, copyright</td></tr>
      </tbody>
    </table>

    <h4>Navbar Structure</h4>
    <p>Uses Bootstrap's <code>navbar-expand-lg</code> with custom CSS classes on top:</p>
    <pre>&lt;nav class="navbar navbar-expand-lg fixed-top av-navbar" id="mainNav"&gt;
  &lt;div class="container-fluid px-4"&gt;
    &lt;a class="navbar-brand av-brand" href="#"&gt;
      &lt;span class="brand-icon"&gt;⬡&lt;/span&gt; AUTO&lt;span class="brand-accent"&gt;VERSE&lt;/span&gt;
    &lt;/a&gt;
    &lt;!-- Bootstrap mobile toggle --&gt;
    &lt;button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu"&gt;...&lt;/button&gt;
    &lt;div class="collapse navbar-collapse" id="navMenu"&gt;
      &lt;!-- nav links, auth buttons --&gt;
    &lt;/div&gt;
  &lt;/div&gt;
&lt;/nav&gt;</pre>

    <h4>Hero Section Key Parts</h4>
    <pre>&lt;section class="hero-section" id="home"&gt;
  &lt;div class="hero-bg-overlay"&gt;&lt;/div&gt;    &lt;!-- gradient overlay --&gt;
  &lt;div class="hero-grid-lines"&gt;&lt;/div&gt;    &lt;!-- CSS grid texture --&gt;
  &lt;div class="hero-content container"&gt;
    &lt;h1 class="hero-title"&gt;FIND YOUR&lt;br/&gt;
      &lt;span class="hero-accent"&gt;DREAM RIDE&lt;/span&gt;
    &lt;/h1&gt;
    &lt;div class="hero-search-card"&gt;...&lt;/div&gt;    &lt;!-- search card --&gt;
    &lt;div class="hero-stats"&gt;...&lt;/div&gt;          &lt;!-- animated counters --&gt;
  &lt;/div&gt;
  &lt;div class="hero-car-visual"&gt;
    &lt;img class="hero-car-img" .../&gt;     &lt;!-- floating car --&gt;
  &lt;/div&gt;
&lt;/section&gt;</pre>

    <h4>Car Grid Container</h4>
    <p>The grid is empty in HTML — cards are rendered by JavaScript into these containers:</p>
    <pre>&lt;div class="car-grid" id="carGrid"&gt;&lt;/div&gt;         &lt;!-- All cars (filtered) --&gt;
&lt;div class="featured-grid" id="featuredGrid"&gt;&lt;/div&gt; &lt;!-- Featured cars only --&gt;</pre>

    <h4>EMI Calculator</h4>
    <p>Uses HTML <code>&lt;input type="range"&gt;</code> sliders for all 4 parameters:</p>
    <pre>&lt;input type="range" id="emiPrice" min="300000" max="10000000"
  step="50000" value="1500000" oninput="calcEMI()"/&gt;
&lt;span id="emiPriceVal"&gt;₹15,00,000&lt;/span&gt;

&lt;!-- Canvas for donut chart --&gt;
&lt;canvas id="emiDonut" width="200" height="200"&gt;&lt;/canvas&gt;</pre>

    <h4>Modals (3 total)</h4>
    <pre>&lt;div class="av-modal" id="carModal"&gt;...&lt;/div&gt;   &lt;!-- Car details --&gt;
&lt;div class="av-modal" id="sellModal"&gt;...&lt;/div&gt;  &lt;!-- Sell listing form --&gt;
&lt;div class="av-modal" id="loginModal"&gt;...&lt;/div&gt; &lt;!-- Auth form --&gt;</pre>
    <div class="note">✅ Modals use CSS <code>display:flex</code> / <code>display:none</code> toggled by JS — no Bootstrap modal JS needed.</div>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 3: CSS ══ -->
<div class="step" id="step3">
  <div class="step-header">
    <div class="step-num">03</div>
    <div>
      <div class="step-title">CSS Design System</div>
      <div class="step-sub">Variables, themes, animations and component styles</div>
    </div>
  </div>
  <div class="step-body">
    <h4>CSS Variables (Design Tokens)</h4>
    <p>Defined on <code>:root</code> — change these to completely retheme the app:</p>
    <pre>:root {
  --red:       #e60012;     /* Primary accent — all CTAs, highlights */
  --red-dark:  #b3000e;     /* Hover state for red elements */
  --red-glow:  rgba(230, 0, 18, 0.35); /* Glow shadows */
  --black:     #0a0a0a;     /* Page background */
  --dark:      #111318;     /* Section backgrounds */
  --dark2:     #181c24;     /* Alternate sections */
  --dark3:     #1e2330;     /* Input/card inner backgrounds */
  --card-bg:   #141820;     /* Card backgrounds */
  --text:      #f0f2f5;     /* Primary text */
  --text-muted:#8a9ab5;     /* Secondary text */
  --text-dim:  #5a6a85;     /* Placeholder/disabled text */
  --gold:      #f5a623;     /* Star ratings, highlights */
  --font-head: 'Bebas Neue'; /* Display font — headings */
  --font-body: 'DM Sans';    /* Body text */
  --font-ui:   'Syne';       /* UI elements, buttons, labels */
}</pre>

    <h4>Navbar Scroll Effect</h4>
    <p>The navbar gains a solid background when user scrolls down:</p>
    <pre>/* CSS: frosted glass start state */
.av-navbar {
  background: rgba(10,10,10,0.6);
  backdrop-filter: blur(20px);
  transition: background 0.3s;
}

/* JS adds this class on scroll */
.av-navbar.scrolled {
  background: rgba(10,10,10,0.95);
  border-bottom-color: var(--red);
}</pre>

    <h4>Hero Background Layers</h4>
    <p>Multiple layered backgrounds create depth:</p>
    <pre>.hero-section {
  background: radial-gradient(ellipse at 70% 50%, #1a0005 0%, var(--black) 60%);
}
.hero-bg-overlay {
  background: 
    radial-gradient(circle at 80% 50%, rgba(230,0,18,0.08) 0%, transparent 60%),
    radial-gradient(circle at 20% 80%, rgba(255,107,0,0.05) 0%, transparent 40%);
}
.hero-grid-lines {
  background-image: 
    linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
  background-size: 60px 60px; /* grid line spacing */
}</pre>

    <h4>Floating Car Animation</h4>
    <pre>.hero-car-img {
  animation: floatCar 4s ease-in-out infinite;
}

@keyframes floatCar {
  0%, 100% { transform: translateY(0); }
  50%       { transform: translateY(-18px); }
}</pre>

    <h4>Card Hover Effects</h4>
    <pre>.car-card {
  transition: transform .25s, box-shadow .25s, border-color .25s;
}
.car-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 8px 40px rgba(0,0,0,0.5);
}
/* Zoom image on hover */
.car-card:hover .car-img-wrap img {
  transform: scale(1.06);
}</pre>

    <h4>Card Entrance Animation</h4>
    <pre>.car-card {
  animation: fadeInUp .4s ease both;
}

/* Stagger delay applied via inline style in JS */
/* style="animation-delay: 0.05s" */

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: translateY(0); }
}</pre>

    <h4>Range Slider Styling</h4>
    <pre>input[type="range"] {
  -webkit-appearance: none;
  height: 4px;
  background: var(--dark3);
  border-radius: 4px;
}
input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 18px; height: 18px;
  background: var(--red);
  border-radius: 50%;
  box-shadow: 0 0 0 4px rgba(230,0,18,0.2);
}</pre>

    <h4>Toast Notification</h4>
    <pre>.av-toast {
  position: fixed;
  bottom: 2rem; right: 2rem;
  transform: translateX(200%);   /* hidden off-screen */
  transition: transform .3s ease;
}
.av-toast.show {
  transform: translateX(0);      /* slide in */
}</pre>

    <div class="info">💡 <strong>Responsive Breakpoints:</strong> The layout collapses hero car visual at 991px, switches search form to column at 768px, and stacks filter bar at 576px.</div>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 4: JAVASCRIPT ══ -->
<div class="step" id="step4">
  <div class="step-header">
    <div class="step-num">04</div>
    <div>
      <div class="step-title">JavaScript Architecture</div>
      <div class="step-sub">Data store, rendering engine, filtering and state management</div>
    </div>
  </div>
  <div class="step-body">
    <h4>App State Object</h4>
    <p>All dynamic state lives in one central object:</p>
    <pre>const AppState = {
  allCars:      [...CAR_DATABASE],   // source of truth
  filtered:     [...CAR_DATABASE],   // current filtered view
  displayCount: 8,                   // for "load more"
  favorites:    JSON.parse(localStorage.getItem('av_favorites') || '[]'),
  user:         JSON.parse(localStorage.getItem('av_user') || 'null'),
  userListings: JSON.parse(localStorage.getItem('av_listings') || '[]'),
};</pre>

    <h4>Car Data Structure</h4>
    <p>Each car object in <code>CAR_DATABASE</code> follows this schema:</p>
    <pre>const CAR_DATABASE = [
  {
    id: 1,
    brand: 'Tata', model: 'Nexon EV Max',
    year: 2024, price: 1899000,
    type: 'SUV', fuel: 'Electric',
    km: 0,
    rating: 4.8, reviews: 320,
    badge: 'New',        // null | 'New' | 'Featured'
    featured: true,      // appears in featured section
    img: 'https://...',  // car image URL
    specs: {
      engine: 'Electric 40.5 kWh',
      power: '143 bhp',
      torque: '250 Nm',
      transmission: 'Auto',
      seats: 5,
      range: '453 km'
    },
    desc: 'Description shown in detail modal.'
  },
  // ... 11 more cars
];</pre>

    <h4>Card Renderer (createCarCard)</h4>
    <p>Returns an HTML string for each car. The key parts:</p>
    <pre>function createCarCard(car, delay = 0) {
  const isFav = AppState.favorites.includes(car.id);
  const emi   = Math.round(calcEMIValue(car.price * 0.8, 9, 48));

  return `
    &lt;div class="car-card" style="animation-delay:${delay}s"
         onclick="openCarModal(${car.id})"&gt;
      &lt;!-- image, badge, fav button --&gt;
      &lt;!-- rating stars --&gt;
      &lt;!-- title, meta (year/fuel/type) --&gt;
      &lt;!-- price + EMI hint --&gt;
      &lt;!-- View Details + Call buttons --&gt;
    &lt;/div&gt;
  `;
}</pre>

    <h4>Filtering Pipeline</h4>
    <p>All 4 filters are applied together on every change:</p>
    <pre>function applyFilters() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const type   = document.getElementById('filterType').value;
  const fuel   = document.getElementById('filterFuel').value;
  const sort   = document.getElementById('filterSort').value;

  // 1. Combine DB + user-submitted cars
  let cars = [...AppState.allCars, ...AppState.userListings];

  // 2. Text search (brand + model + type)
  if (search) {
    cars = cars.filter(c =>
      `${c.brand} ${c.model} ${c.type}`.toLowerCase().includes(search)
    );
  }

  // 3. Dropdown filters
  if (type) cars = cars.filter(c => c.type === type);
  if (fuel) cars = cars.filter(c => c.fuel === fuel);

  // 4. Sort
  switch (sort) {
    case 'price-asc':  cars.sort((a,b) => a.price - b.price); break;
    case 'price-desc': cars.sort((a,b) => b.price - a.price); break;
    case 'year-desc':  cars.sort((a,b) => b.year  - a.year);  break;
    case 'rating':     cars.sort((a,b) => b.rating - a.rating); break;
  }

  // 5. Update state and re-render
  AppState.filtered = cars;
  AppState.displayCount = 8;
  renderCarGrid();
}</pre>

    <h4>EMI Calculation Formula</h4>
    <pre>// Standard reducing balance EMI formula
function calcEMIValue(principal, annualRate, months) {
  const r = annualRate / 100 / 12;    // monthly rate
  return principal * r *
    Math.pow(1+r, months) /
    (Math.pow(1+r, months) - 1);
}</pre>

    <h4>Donut Chart (Pure Canvas)</h4>
    <p>No charting library — drawn directly with HTML5 Canvas:</p>
    <pre>function drawDonut(principal, interest) {
  const canvas = document.getElementById('emiDonut');
  const ctx = canvas.getContext('2d');
  const total = principal + interest;
  const cx = 100, cy = 100, outerR = 75, innerR = 45;
  const startAngle = -Math.PI / 2;
  const principalAngle = (principal / total) * 2 * Math.PI;

  ctx.clearRect(0, 0, 200, 200);

  // Draw principal slice (red)
  ctx.beginPath();
  ctx.moveTo(cx, cy);
  ctx.arc(cx, cy, outerR, startAngle, startAngle + principalAngle);
  ctx.fillStyle = '#e60012';
  ctx.fill();

  // Draw interest slice (orange)
  ctx.beginPath();
  ctx.moveTo(cx, cy);
  ctx.arc(cx, cy, outerR, startAngle + principalAngle, startAngle + 2*Math.PI);
  ctx.fillStyle = '#ff6b00';
  ctx.fill();

  // Punch inner hole (donut effect)
  ctx.beginPath();
  ctx.arc(cx, cy, innerR, 0, 2 * Math.PI);
  ctx.fillStyle = '#1e2330';
  ctx.fill();
}</pre>

    <h4>Price Formatter</h4>
    <pre>function formatPrice(n) {
  if (n >= 10000000) return '₹' + (n / 10000000).toFixed(2) + ' Cr';
  if (n >= 100000)   return '₹' + (n / 100000).toFixed(2) + ' L';
  return '₹' + n.toLocaleString('en-IN');
}</pre>

    <h4>Counter Animation (Intersection Observer)</h4>
    <pre>function initCounters() {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        const target = parseInt(el.dataset.count);
        let current = 0;
        const step = target / 60;    // 60 frames
        const timer = setInterval(() => {
          current = Math.min(current + step, target);
          el.textContent = Math.round(current).toLocaleString('en-IN');
          if (current >= target) clearInterval(timer);
        }, 20);                       // ~50fps
        observer.unobserve(el);      // animate only once
      }
    });
  }, { threshold: 0.5 });

  document.querySelectorAll('.hstat-num').forEach(el => observer.observe(el));
}</pre>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 5: FEATURES ══ -->
<div class="step" id="step5">
  <div class="step-header">
    <div class="step-num">05</div>
    <div>
      <div class="step-title">Feature Deep-Dives</div>
      <div class="step-sub">Auth, Sell Car, Wishlist, Modals, Toast</div>
    </div>
  </div>
  <div class="step-body">
    <h4>Authentication Flow (localStorage)</h4>
    <p>Users are stored in <code>localStorage</code> as a JSON array. No backend required:</p>
    <pre>// REGISTER
function registerUser(e) {
  e.preventDefault();
  const users = JSON.parse(localStorage.getItem('av_users') || '[]');
  const newUser = { name, email, pass };
  users.push(newUser);
  localStorage.setItem('av_users', JSON.stringify(users));

  // Auto-login after register
  AppState.user = newUser;
  localStorage.setItem('av_user', JSON.stringify(newUser));
  updateAuthUI();
}

// LOGIN
function loginUser(e) {
  e.preventDefault();
  const users = JSON.parse(localStorage.getItem('av_users') || '[]');
  const user  = users.find(u => u.email === email);
  if (!user) { showToast('Account not found'); return; }

  AppState.user = user;
  localStorage.setItem('av_user', JSON.stringify(user));
  updateAuthUI();
}

// Update navbar button text
function updateAuthUI() {
  const btn = document.getElementById('loginBtn');
  if (AppState.user) {
    btn.textContent = `👤 ${AppState.user.name} · Sign Out`;
  } else {
    btn.textContent = 'Sign In';
  }
}</pre>

    <h4>Sell Car → Live Listing</h4>
    <p>New listing from the Sell form is instantly added to the car grid:</p>
    <pre>function submitListing(e) {
  e.preventDefault();

  const newCar = {
    id:    Date.now(),    // unique ID using timestamp
    brand: document.getElementById('sellBrand').value,
    model: document.getElementById('sellModel').value,
    // ... other fields
    img: `https://placehold.co/400x220/141820/e60012?text=...`,
  };

  // Add to user listings (localStorage persistent)
  AppState.userListings.push(newCar);
  localStorage.setItem('av_listings', JSON.stringify(AppState.userListings));

  // Add to all cars and refresh grid
  AppState.allCars.push(newCar);
  AppState.filtered = [...AppState.allCars];
  renderCarGrid();

  showToast('Your listing has been posted!', 'success');
}</pre>

    <h4>Wishlist / Favorites</h4>
    <pre>function toggleFav(event, id) {
  if (event) event.stopPropagation();   // don't open modal

  const idx = AppState.favorites.indexOf(id);
  if (idx === -1) {
    AppState.favorites.push(id);        // add to favorites
  } else {
    AppState.favorites.splice(idx, 1);  // remove from favorites
  }

  // Persist to localStorage
  localStorage.setItem('av_favorites', JSON.stringify(AppState.favorites));

  // Re-render to update heart icons
  renderCarGrid();
  renderFeatured();
}</pre>

    <h4>Toast Notification System</h4>
    <pre>function showToast(msg, type = '') {
  const toast = document.getElementById('avToast');
  toast.textContent = msg;
  toast.className = 'av-toast show' + (type ? ' ' + type : '');

  // Auto hide after 3.5s
  setTimeout(() => toast.classList.remove('show'), 3500);
}</pre>

    <h4>Modal Open/Close</h4>
    <pre>// Open: set display to flex (CSS centers it)
document.getElementById('sellModal').style.display = 'flex';

// Close button
document.getElementById('sellModal').style.display = 'none';

// Close on backdrop click
modal.addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});

// Close on ESC key
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    ['carModal','sellModal','loginModal'].forEach(id => {
      document.getElementById(id).style.display = 'none';
    });
  }
});</pre>

    <div class="note">✅ <strong>Gated Buy Now:</strong> Clicking "Buy Now" checks <code>AppState.user</code>. If not logged in, it closes the car modal and opens the login modal automatically.</div>
  </div>
</div>

<hr class="divider"/>

<!-- ══ STEP 6: DEPLOY ══ -->
<div class="step" id="step6">
  <div class="step-header">
    <div class="step-num">06</div>
    <div>
      <div class="step-title">Deploy & Extend</div>
      <div class="step-sub">Go live and upgrade paths</div>
    </div>
  </div>
  <div class="step-body">
    <h4>Deploy for free (3 options)</h4>
    <table>
      <thead><tr><th>Platform</th><th>Steps</th><th>URL</th></tr></thead>
      <tbody>
        <tr><td>GitHub Pages</td><td>Push to repo → Settings → Pages → Deploy from main</td><td>username.github.io/autoverse</td></tr>
        <tr><td>Netlify</td><td>Drag &amp; drop folder on netlify.com/drop</td><td>random-name.netlify.app</td></tr>
        <tr><td>Vercel</td><td>vercel.com → New → import git repo</td><td>autoverse.vercel.app</td></tr>
      </tbody>
    </table>

    <h4>Adding More Cars</h4>
    <p>Just add objects to the <code>CAR_DATABASE</code> array in <code>app.js</code> following the same schema. The grid re-renders automatically.</p>

    <h4>Backend Upgrade Path</h4>
    <p>Replace localStorage calls with API calls when ready:</p>
    <pre>// Current (localStorage):
localStorage.setItem('av_user', JSON.stringify(user));

// Upgrade to (REST API):
const res = await fetch('/api/auth/login', {
  method: 'POST',
  body: JSON.stringify({ email, password }),
  headers: { 'Content-Type': 'application/json' }
});
const user = await res.json();</pre>

    <h4>Recommended Backend Stack</h4>
    <ul class="feature-list">
      <li><strong>Node.js + Express</strong> — REST API for cars, users, enquiries</li>
      <li><strong>MongoDB</strong> — flexible schema for cars and users</li>
      <li><strong>Cloudinary</strong> — image uploads for car listings</li>
      <li><strong>JWT</strong> — replace localStorage auth with tokens</li>
      <li><strong>Razorpay</strong> — booking payments and EMI integration</li>
    </ul>

    <div class="note">✅ The frontend is already structured to make this migration easy — all data access goes through AppState and centralized functions.</div>
    <div class="warn">⚠️ Remember: localStorage auth is only for prototyping. Never store passwords in plaintext in production.</div>
  </div>
</div>

</div><!-- /guide-body -->
</body>
</html>

