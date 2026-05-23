/* ══════════════════════════════════════════
   AUTOVERSE — app.js  (PHP API Edition v3)
   ✅ ES5-safe: no arrow funcs, no template
      literals, no spread, no ??, no const/let
   ✅ Maps PHP field names correctly
══════════════════════════════════════════ */

'use strict';

// ─── Change this to match YOUR XAMPP folder ───
var API_BASE = 'http://localhost/autoverse/api';

/* ═══════════════════════════════════════════
   1. STATE
═══════════════════════════════════════════ */
var AppState = {
  allCars:    [],
  favorites:  [],
  user:       null,
  page:       1,
  totalPages: 1,
};

/* ═══════════════════════════════════════════
   2. INIT
═══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function() {
  initNavbar();
  initModals();
  initCounters();
  calcEMI();
  checkSession().then(function() {
    loadCars({});
    loadFeatured();
  });
});

/* ═══════════════════════════════════════════
   3. API FETCH WRAPPER
═══════════════════════════════════════════ */
function apiFetch(endpoint, options) {
  options = options || {};
  var fetchOptions = {
    credentials: 'include',
    method:      options.method || 'GET',
    headers:     Object.assign({ 'Content-Type': 'application/json' }, options.headers || {}),
  };
  if (options.body) fetchOptions.body = options.body;

  return fetch(API_BASE + '/' + endpoint, fetchOptions)
    .then(function(res) {
      return res.json().then(function(data) {
        return { ok: res.ok, status: res.status, data: data };
      });
    })
    .catch(function(err) {
      console.error('apiFetch error:', endpoint, err);
      return { ok: false, data: { error: 'Network error. Is XAMPP running?' } };
    });
}

/* ═══════════════════════════════════════════
   4. LOAD & RENDER CARS
═══════════════════════════════════════════ */
function loadCars(params) {
  params = params || {};
  showGridLoading();

  var qsParts = ['page=' + AppState.page, 'limit=12'];
  var keys = Object.keys(params);
  for (var i = 0; i < keys.length; i++) {
    if (params[keys[i]]) {
      qsParts.push(encodeURIComponent(keys[i]) + '=' + encodeURIComponent(params[keys[i]]));
    }
  }
  var qs = qsParts.join('&');

  return apiFetch('cars.php?' + qs).then(function(result) {
    if (!result.ok || !result.data.data) {
      document.getElementById('carGrid').innerHTML =
        '<div style="grid-column:1/-1;text-align:center;padding:3rem;color:#ff7070">' +
        '<i class="fa fa-triangle-exclamation fa-2x"></i>' +
        '<p style="margin-top:1rem;font-size:.9rem">Could not load cars. ' +
        (result.data.error || '') + '</p></div>';
      console.error('loadCars failed:', result.data);
      return;
    }
    AppState.allCars  = result.data.data;
    AppState.totalPages = (result.data.meta && result.data.meta.total_pages) ? result.data.meta.total_pages : 1;
    renderCarGrid(AppState.allCars);
    if (result.data.meta) updateLoadMoreBtn(result.data.meta);
  });
}

function loadFeatured() {
  return apiFetch('cars.php?featured=1&limit=6').then(function(result) {
    if (result.ok && result.data.data) renderFeaturedGrid(result.data.data);
  });
}

/* ═══════════════════════════════════════════
   5. CAR CARD BUILDER
   Maps PHP field names: img_url, km_driven,
   review_count, rating (string), price (string)
═══════════════════════════════════════════ */
function createCarCard(car, delay) {
  delay = delay || 0;

  var imgSrc      = car.img_url      || car.img  || '';
  var kmDriven    = parseInt(car.km_driven  || car.km || 0);
  var reviewCount = parseInt(car.review_count || car.reviews || 0);
  var rating      = parseFloat(car.rating  || 4.0);
  var carPrice    = parseFloat(car.price   || 0);
  var isFav       = AppState.favorites.indexOf(Number(car.id)) !== -1;
  var emi         = Math.round(calcEMIValue(carPrice * 0.8, 9, 48));

  // Stars
  var stars = '';
  var full = Math.floor(rating);
  for (var s = 0; s < full; s++)  stars += '★';
  for (var e = full; e < 5; e++)  stars += '☆';

  // Badge
  var badgeMap = { 'New': 'badge-new-live', 'Featured': 'badge-featured-live', 'Sold': 'badge-sold-live' };
  var badgeHTML = (car.badge && car.badge !== '')
    ? '<span class="car-badge ' + (badgeMap[car.badge] || '') + '">' + car.badge + '</span>'
    : '';

  // KM label
  var kmLabel = kmDriven > 0
    ? '<span><i class="fa fa-road"></i>' + (kmDriven / 1000).toFixed(0) + 'k km</span>'
    : '<span><i class="fa fa-circle-check"></i>Brand New</span>';

  var favClass = isFav ? 'fa fa-heart'  : 'far fa-heart';
  var favActive = isFav ? 'active' : '';

  // Escape brand/model for use in onclick string
  var safeName = (car.brand + ' ' + car.model).replace(/'/g, '');

  return '<div class="car-card" style="animation-delay:' + delay + 's" onclick="openCarModal(' + car.id + ')">' +
    '<div class="car-img-wrap">' +
      '<img src="' + imgSrc + '" alt="' + car.brand + ' ' + car.model + '" ' +
        'loading="lazy" ' +
        'onerror="this.src=\'https://placehold.co/400x220/141820/e60012?text=' + encodeURIComponent(car.brand) + '\'"/>' +
      badgeHTML +
      '<button class="car-fav-btn ' + favActive + '" ' +
        'onclick="toggleFav(event,' + car.id + ')" title="Wishlist">' +
        '<i class="' + favClass + '"></i>' +
      '</button>' +
    '</div>' +
    '<div class="car-body">' +
      '<div class="car-rating">' + stars + ' <span>' + rating.toFixed(1) + ' (' + reviewCount + ')</span></div>' +
      '<div class="car-title">' + car.brand + ' ' + car.model + '</div>' +
      '<div class="car-meta">' +
        '<span><i class="fa fa-calendar"></i>' + car.year + '</span>' +
        '<span><i class="fa fa-gas-pump"></i>' + car.fuel + '</span>' +
        '<span><i class="fa fa-car"></i>'      + car.type + '</span>' +
        kmLabel +
      '</div>' +
      '<div class="car-price">' + formatPrice(carPrice) +
        '<span class="emi-hint">EMI from ' + formatPrice(emi) + '/month</span>' +
      '</div>' +
      '<div class="car-footer">' +
        '<button class="btn-card-primary" onclick="openCarModal(' + car.id + ');event.stopPropagation()">View Details</button>' +
        '<button class="btn-card-ghost"   onclick="callDealer(event,\'' + safeName + '\')"><i class="fa fa-phone"></i> Call</button>' +
      '</div>' +
    '</div>' +
  '</div>';
}

function renderCarGrid(cars) {
  var grid  = document.getElementById('carGrid');
  var noRes = document.getElementById('noResults');
  if (!cars || cars.length === 0) {
    grid.innerHTML = '';
    noRes.style.display = 'block';
    return;
  }
  noRes.style.display = 'none';
  var html = '';
  for (var i = 0; i < cars.length; i++) html += createCarCard(cars[i], i * 0.05);
  grid.innerHTML = html;
}

function renderFeaturedGrid(cars) {
  var grid = document.getElementById('featuredGrid');
  if (!grid || !cars) return;
  var html = '';
  for (var i = 0; i < cars.length; i++) html += createCarCard(cars[i], i * 0.07);
  grid.innerHTML = html;
}

function updateLoadMoreBtn(meta) {
  var btn = document.getElementById('loadMoreBtn');
  if (btn) btn.style.display = (meta.page < meta.total_pages) ? 'inline-block' : 'none';
}

function showGridLoading() {
  var grid = document.getElementById('carGrid');
  if (grid) grid.innerHTML =
    '<div style="grid-column:1/-1;text-align:center;padding:3rem;color:var(--text-muted)">' +
    '<i class="fa fa-spinner fa-spin fa-2x"></i>' +
    '<p style="margin-top:1rem;font-size:.9rem">Loading cars from database...</p></div>';
}

/* ═══════════════════════════════════════════
   6. FILTERS
═══════════════════════════════════════════ */
function applyFilters() {
  var search = document.getElementById('searchInput').value.trim();
  var type   = document.getElementById('filterType').value;
  var fuel   = document.getElementById('filterFuel').value;
  var sort   = document.getElementById('filterSort').value;

  var sortMap = {
    'price-asc':  'price_asc',
    'price-desc': 'price_desc',
    'year-desc':  'year_desc',
    'rating':     'rating',
  };

  var params = {};
  if (search)         params.search = search;
  if (type)           params.type   = type;
  if (fuel)           params.fuel   = fuel;
  if (sort && sortMap[sort]) params.sort = sortMap[sort];

  AppState.page = 1;
  loadCars(params).then(function() {
    document.getElementById('search-section').scrollIntoView({ behavior: 'smooth' });
  });
}

function resetFilters() {
  document.getElementById('searchInput').value = '';
  document.getElementById('filterType').value  = '';
  document.getElementById('filterFuel').value  = '';
  document.getElementById('filterSort').value  = '';
  AppState.page = 1;
  loadCars({});
}

function filterByBrand(brand) {
  document.getElementById('searchInput').value = brand;
  applyFilters();
}

function heroSearch() {
  var type   = document.getElementById('heroType').value;
  var brand  = document.getElementById('heroBrand').value;
  var budget = document.getElementById('heroBudget').value;

  var params = {};
  if (type)  params.type   = type;
  if (brand) params.search = brand;

  AppState.page = 1;
  loadCars(params).then(function() {
    if (budget) {
      var budgetNum = parseInt(budget);
      var filtered = AppState.allCars.filter(function(c) {
        return parseFloat(c.price) <= budgetNum;
      });
      renderCarGrid(filtered);
    }
    document.getElementById('search-section').scrollIntoView({ behavior: 'smooth' });
  });
}

function loadMore() {
  if (AppState.page >= AppState.totalPages) return;
  AppState.page++;
  var search = document.getElementById('searchInput').value.trim();
  var type   = document.getElementById('filterType').value;
  var fuel   = document.getElementById('filterFuel').value;
  var qs = 'page=' + AppState.page + '&limit=12';
  if (search) qs += '&search=' + encodeURIComponent(search);
  if (type)   qs += '&type='   + encodeURIComponent(type);
  if (fuel)   qs += '&fuel='   + encodeURIComponent(fuel);

  apiFetch('cars.php?' + qs).then(function(result) {
    if (result.ok && result.data.data) {
      var grid = document.getElementById('carGrid');
      var html = '';
      for (var i = 0; i < result.data.data.length; i++) {
        html += createCarCard(result.data.data[i], i * 0.05);
      }
      grid.innerHTML += html;
      if (result.data.meta) updateLoadMoreBtn(result.data.meta);
    }
  });
}

/* ═══════════════════════════════════════════
   7. CAR DETAIL MODAL
═══════════════════════════════════════════ */
function openCarModal(id) {
  var modal   = document.getElementById('carModal');
  var content = document.getElementById('carModalContent');

  content.innerHTML =
    '<div style="text-align:center;padding:3rem;color:var(--text-muted)">' +
    '<i class="fa fa-spinner fa-spin fa-2x"></i><p style="margin-top:1rem">Loading...</p></div>';
  modal.style.display = 'flex';

  apiFetch('cars.php?id=' + id).then(function(result) {
    if (!result.ok || !result.data.data) {
      content.innerHTML = '<p style="color:#ff7070;padding:2rem">Failed to load car details.</p>';
      return;
    }
    var car = result.data.data;
    var carPrice = parseFloat(car.price || 0);
    var emi = Math.round(calcEMIValue(carPrice * 0.8, 9, 48));
    var isFav = AppState.favorites.indexOf(Number(car.id)) !== -1;

    var specRows = [
      ['engine','Engine'], ['power','Power'], ['torque','Torque'],
      ['transmission','Transmission'], ['seats','Seats']
    ];
    var specsHTML = '';
    for (var i = 0; i < specRows.length; i++) {
      var f = specRows[i][0], label = specRows[i][1];
      if (car[f]) {
        specsHTML += '<div class="spec-item"><span class="spec-label">' + label + '</span>' +
          '<span class="spec-val">' + car[f] + '</span></div>';
      }
    }
    var kmVal = parseInt(car.km_driven || 0);
    specsHTML += '<div class="spec-item"><span class="spec-label">Kilometres</span>' +
      '<span class="spec-val">' + (kmVal === 0 ? 'Brand New' : kmVal.toLocaleString('en-IN') + ' km') + '</span></div>';

    var imgSrc = car.img_url || '';
    var favLabel = isFav ? 'Saved' : 'Save';
    var favIcon  = isFav ? 'fa fa-heart' : 'far fa-heart';

    content.innerHTML =
      '<button class="modal-close" onclick="document.getElementById(\'carModal\').style.display=\'none\'">&times;</button>' +
      '<div class="car-detail-header">' +
        '<img class="car-detail-img" src="' + imgSrc + '" ' +
          'onerror="this.src=\'https://placehold.co/400x220/141820/e60012?text=' + encodeURIComponent(car.brand) + '\'" ' +
          'alt="' + car.brand + ' ' + car.model + '"/>' +
        '<div>' +
          '<h2>' + car.brand + ' ' + car.model + '</h2>' +
          '<div style="font-size:.85rem;color:var(--text-muted);margin:.3rem 0 .8rem">' +
            car.year + ' · ' + car.fuel + ' · ' + car.type +
          '</div>' +
          '<div class="car-detail-price">' + formatPrice(carPrice) + '</div>' +
          '<div style="font-size:.8rem;color:var(--text-dim);margin-bottom:1rem">' +
            'EMI from ' + formatPrice(emi) + '/month · 20% down · 48 months' +
          '</div>' +
          '<p style="font-size:.88rem;color:var(--text-muted);line-height:1.7">' + (car.description || '') + '</p>' +
          (car.seller_name ? '<div style="font-size:.82rem;color:var(--text-dim);margin-top:.7rem">🧑 Seller: <strong>' + car.seller_name + '</strong></div>' : '') +
        '</div>' +
      '</div>' +
      '<h4 style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);margin-bottom:.8rem">Specifications</h4>' +
      '<div class="car-detail-specs">' + specsHTML + '</div>' +
      '<div class="car-detail-actions">' +
        '<button class="btn-card-primary" style="flex:2;padding:.75rem" onclick="buyNow(' + car.id + ')">' +
          '<i class="fa fa-shopping-cart"></i> Book Test Drive' +
        '</button>' +
        '<button class="btn-card-ghost" style="flex:1;padding:.75rem" onclick="callDealer(null,\'' + car.brand + ' ' + car.model + '\')">' +
          '<i class="fa fa-phone"></i> Call' +
        '</button>' +
        '<button class="btn-card-ghost" style="flex:1;padding:.75rem" onclick="toggleFav(null,' + car.id + ')">' +
          '<i class="' + favIcon + '"></i> ' + favLabel +
        '</button>' +
      '</div>';
  });
}

/* ═══════════════════════════════════════════
   8. SELL CAR
═══════════════════════════════════════════ */
function submitListing(e) {
  e.preventDefault();
  if (!AppState.user) {
    document.getElementById('sellModal').style.display = 'none';
    document.getElementById('loginModal').style.display = 'flex';
    showToast('🔐 Please sign in to list your car.');
    return;
  }

  var payload = {
    brand:       document.getElementById('sellBrand').value,
    model:       document.getElementById('sellModel').value,
    year:        document.getElementById('sellYear').value,
    price:       document.getElementById('sellPrice').value,
    fuel:        document.getElementById('sellFuel').value,
    type:        document.getElementById('sellType').value,
    km_driven:   document.getElementById('sellKm').value,
    description: document.getElementById('sellDesc').value,
    badge:       'New',
  };

  apiFetch('cars.php', { method: 'POST', body: JSON.stringify(payload) }).then(function(result) {
    if (result.ok) {
      document.getElementById('sellModal').style.display = 'none';
      e.target.reset();
      showToast('✅ Listing submitted!', 'success');
      AppState.page = 1;
      loadCars({});
    } else {
      showToast('❌ ' + (result.data.error || 'Failed to submit.'));
    }
  });
}

/* ═══════════════════════════════════════════
   9. FAVORITES
═══════════════════════════════════════════ */
function toggleFav(event, id) {
  if (event) event.stopPropagation();
  if (!AppState.user) {
    showToast('🔐 Sign in to save favourites.');
    document.getElementById('loginModal').style.display = 'flex';
    return;
  }
  apiFetch('favorites.php', { method: 'POST', body: JSON.stringify({ car_id: id }) }).then(function(result) {
    if (result.ok) {
      if (result.data.action === 'added') {
        AppState.favorites.push(Number(id));
        showToast('❤️ Added to wishlist');
      } else {
        AppState.favorites = AppState.favorites.filter(function(f) { return f !== Number(id); });
        showToast('💔 Removed from wishlist');
      }
      renderCarGrid(AppState.allCars);
      loadFeatured();
    }
  });
}

function loadUserFavorites() {
  if (!AppState.user) return Promise.resolve();
  return apiFetch('favorites.php').then(function(result) {
    if (result.ok && result.data.data) {
      AppState.favorites = result.data.data.map(function(c) { return Number(c.id); });
    }
  });
}

/* ═══════════════════════════════════════════
   10. AUTH
═══════════════════════════════════════════ */
function checkSession() {
  return apiFetch('auth.php?action=me').then(function(result) {
    if (result.ok && result.data.user) {
      AppState.user = result.data.user;
      return loadUserFavorites();
    }
    updateAuthUI();
  }).then(function() { updateAuthUI(); });
}

function loginUser(e) {
  e.preventDefault();
  var inputs = e.target.querySelectorAll('input');
  apiFetch('auth.php?action=login', {
    method: 'POST',
    body: JSON.stringify({ email: inputs[0].value, password: inputs[1].value }),
  }).then(function(result) {
    if (result.ok) {
      AppState.user = result.data.user;
      loadUserFavorites().then(function() {
        document.getElementById('loginModal').style.display = 'none';
        updateAuthUI();
        renderCarGrid(AppState.allCars);
        showToast('👋 Welcome back, ' + result.data.user.name + '!', 'success');
      });
    } else {
      showToast('❌ ' + (result.data.error || 'Login failed.'));
    }
  });
}

function registerUser(e) {
  e.preventDefault();
  var inputs = e.target.querySelectorAll('input');
  apiFetch('auth.php?action=register', {
    method: 'POST',
    body: JSON.stringify({ name: inputs[0].value, email: inputs[1].value, password: inputs[2].value }),
  }).then(function(result) {
    if (result.ok) {
      AppState.user = result.data.user;
      document.getElementById('loginModal').style.display = 'none';
      updateAuthUI();
      showToast('🎉 Welcome, ' + result.data.user.name + '!', 'success');
    } else {
      showToast('❌ ' + (result.data.error || 'Registration failed.'));
    }
  });
}

function logoutUser() {
  apiFetch('auth.php?action=logout', { method: 'POST' }).then(function() {
    AppState.user      = null;
    AppState.favorites = [];
    updateAuthUI();
    renderCarGrid(AppState.allCars);
    showToast('👋 Signed out.');
  });
}

function updateAuthUI() {
  var btn = document.getElementById('loginBtn');
  if (!btn) return;
  if (AppState.user) {
    btn.textContent = '👤 ' + AppState.user.name.split(' ')[0] + ' · Sign Out';
    btn.onclick = logoutUser;
  } else {
    btn.textContent = 'Sign In';
    btn.onclick = function() { document.getElementById('loginModal').style.display = 'flex'; };
  }
}

/* ═══════════════════════════════════════════
   11. CONTACT FORM
═══════════════════════════════════════════ */
function submitContact(e) {
  e.preventDefault();
  var inputs = e.target.querySelectorAll('input, select, textarea');
  var payload = {
    name:    inputs[0].value,
    phone:   inputs[1].value,
    email:   inputs[2].value,
    type:    inputs[3].value,
    message: inputs[4] ? inputs[4].value : '',
  };
  apiFetch('enquiries.php', { method: 'POST', body: JSON.stringify(payload) }).then(function(result) {
    if (result.ok) {
      showToast("✅ Message sent! We'll contact you shortly.", 'success');
      e.target.reset();
    } else {
      showToast('❌ ' + (result.data.error || 'Failed to send.'));
    }
  });
}

/* ═══════════════════════════════════════════
   12. BUY NOW
═══════════════════════════════════════════ */
function buyNow(carId) {
  if (!AppState.user) {
    document.getElementById('carModal').style.display = 'none';
    document.getElementById('loginModal').style.display = 'flex';
    showToast('🔐 Please sign in to book.');
    return;
  }
  apiFetch('enquiries.php', {
    method: 'POST',
    body: JSON.stringify({
      name:    AppState.user.name,
      email:   AppState.user.email,
      phone:   AppState.user.phone || '0000000000',
      car_id:  carId,
      type:    'TestDrive',
      message: 'Test drive request from website.',
    }),
  }).then(function(result) {
    showToast('🎉 Test Drive booked! Our team will call you within 2 hours.', 'success');
    document.getElementById('carModal').style.display = 'none';
  });
}

/* ═══════════════════════════════════════════
   13. EMI CALCULATOR
═══════════════════════════════════════════ */
function calcEMI() {
  var price  = parseInt(document.getElementById('emiPrice').value);
  var down   = parseInt(document.getElementById('emiDown').value);
  var rate   = parseFloat(document.getElementById('emiRate').value);
  var tenure = parseInt(document.getElementById('emiTenure').value);

  document.getElementById('emiPriceVal').textContent  = formatPrice(price);
  document.getElementById('emiDownVal').textContent   = formatPrice(down);
  document.getElementById('emiRateVal').textContent   = rate + '%';
  document.getElementById('emiTenureVal').textContent = tenure + ' Months';

  var principal = price - down;
  var emi       = calcEMIValue(principal, rate, tenure);
  var total     = emi * tenure;
  var interest  = total - principal;

  document.getElementById('emiResult').textContent = formatPrice(Math.round(emi));
  document.getElementById('emiBreakdown').innerHTML =
    '<div>Loan Amount: <strong>' + formatPrice(principal) + '</strong></div>' +
    '<div>Total Interest: <strong>' + formatPrice(Math.round(interest)) + '</strong></div>' +
    '<div>Total Payable: <strong>' + formatPrice(Math.round(total)) + '</strong></div>';

  drawDonut(principal, Math.round(interest));
  document.getElementById('emiLegend').innerHTML =
    '<div>🔴 Principal: ' + formatPrice(principal) + '</div>' +
    '<div>🟠 Interest: ' + formatPrice(Math.round(interest)) + '</div>';
}

function calcEMIValue(p, r, n) {
  var m = r / 100 / 12;
  return p * m * Math.pow(1 + m, n) / (Math.pow(1 + m, n) - 1);
}

function drawDonut(p, interest) {
  var canvas = document.getElementById('emiDonut');
  if (!canvas) return;
  var ctx   = canvas.getContext('2d');
  var total = p + interest;
  var cx = 100, cy = 100, r = 75, r2 = 45;
  var sa = -Math.PI / 2;
  var pa = (p / total) * 2 * Math.PI;
  ctx.clearRect(0, 0, 200, 200);
  ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, r, sa, sa + pa); ctx.closePath(); ctx.fillStyle = '#e60012'; ctx.fill();
  ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, r, sa + pa, sa + 2 * Math.PI); ctx.closePath(); ctx.fillStyle = '#ff6b00'; ctx.fill();
  ctx.beginPath(); ctx.arc(cx, cy, r2, 0, 2 * Math.PI); ctx.fillStyle = '#1e2330'; ctx.fill();
}

/* ═══════════════════════════════════════════
   14. NAVBAR, MODALS, COUNTERS
═══════════════════════════════════════════ */
function initNavbar() {
  var nav = document.getElementById('mainNav');
  window.addEventListener('scroll', function() {
    nav.classList.toggle('scrolled', window.scrollY > 60);
  });
  document.querySelectorAll('.nav-link').forEach(function(l) {
    l.addEventListener('click', function() {
      document.querySelectorAll('.nav-link').forEach(function(x) { x.classList.remove('active'); });
      l.classList.add('active');
    });
  });
  document.querySelectorAll('.stab').forEach(function(tab) {
    tab.addEventListener('click', function() {
      document.querySelectorAll('.stab').forEach(function(t) { t.classList.remove('active'); });
      tab.classList.add('active');
    });
  });
  var loginBtn = document.getElementById('loginBtn');
  if (loginBtn) {
    loginBtn.addEventListener('click', function() {
      if (AppState.user) logoutUser();
      else document.getElementById('loginModal').style.display = 'flex';
    });
  }
}

function initModals() {
  ['carModal', 'sellModal', 'loginModal'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function(e) {
      if (e.target === el) el.style.display = 'none';
    });
  });
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      ['carModal', 'sellModal', 'loginModal'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = 'none';
      });
    }
  });
}

function showLoginTab(tab, el) {
  document.getElementById('loginForm').style.display    = tab === 'login'    ? 'flex' : 'none';
  document.getElementById('registerForm').style.display = tab === 'register' ? 'flex' : 'none';
  document.querySelectorAll('.ltab').forEach(function(t) { t.classList.remove('active'); });
  el.classList.add('active');
}

function initCounters() {
  if (!('IntersectionObserver' in window)) return;
  var observer = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        var el = entry.target;
        var target = parseInt(el.dataset.count);
        var current = 0;
        var step = target / 60;
        var timer = setInterval(function() {
          current = Math.min(current + step, target);
          el.textContent = Math.round(current).toLocaleString('en-IN');
          if (current >= target) clearInterval(timer);
        }, 20);
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.hstat-num').forEach(function(el) { observer.observe(el); });
}

/* ═══════════════════════════════════════════
   15. HELPERS
═══════════════════════════════════════════ */
function formatPrice(n) {
  n = Number(n);
  if (n >= 10000000) return '₹' + (n / 10000000).toFixed(2) + ' Cr';
  if (n >= 100000)   return '₹' + (n / 100000).toFixed(2)   + ' L';
  return '₹' + n.toLocaleString('en-IN');
}

function showToast(msg, type) {
  var t = document.getElementById('avToast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'av-toast show' + (type ? ' ' + type : '');
  setTimeout(function() { t.classList.remove('show'); }, 3500);
}

function callDealer(event, name) {
  if (event) event.stopPropagation();
  showToast('📞 Connecting to dealer for ' + name + '...');
}
