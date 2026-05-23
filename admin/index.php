<?php
// ══════════════════════════════════════════
//  AUTOVERSE — admin/index.php
//  Full Admin Dashboard — Cars, Users, Enquiries
// ══════════════════════════════════════════

require_once __DIR__ . '/../config/db.php';
session_name(SESSION_NAME);
session_start();

// Redirect to login if not admin
$user = getCurrentUser();
if (!$user || $user['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$db = getDB();

// ─── Stats ───
$stats = [
    'cars'       => $db->query('SELECT COUNT(*) FROM cars WHERE is_active=1')->fetchColumn(),
    'users'      => $db->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'enquiries'  => $db->query("SELECT COUNT(*) FROM enquiries WHERE status='new'")->fetchColumn(),
    'featured'   => $db->query('SELECT COUNT(*) FROM cars WHERE is_featured=1 AND is_active=1')->fetchColumn(),
];

// ─── Recent cars ───
$recentCars = $db->query(
    'SELECT c.*, u.name AS seller FROM cars c
     LEFT JOIN users u ON c.user_id = u.id
     WHERE c.is_active=1 ORDER BY c.created_at DESC LIMIT 10'
)->fetchAll();

// ─── Recent enquiries ───
$recentEnqs = $db->query(
    "SELECT e.*, c.brand, c.model FROM enquiries e
     LEFT JOIN cars c ON e.car_id = c.id
     ORDER BY e.created_at DESC LIMIT 8"
)->fetchAll();

// ─── All users ───
$allUsers = $db->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>AutoVerse Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    :root{--red:#e60012;--dark:#0d0f14;--dark2:#141820;--card:#1a1f2e;--border:rgba(255,255,255,0.08);--text:#f0f2f5;--muted:#8a9ab5}
    *{box-sizing:border-box}
    body{font-family:'DM Sans',sans-serif;background:var(--dark);color:var(--text);margin:0}
    .sidebar{width:240px;background:var(--dark2);border-right:1px solid var(--border);height:100vh;position:fixed;top:0;left:0;padding:1.5rem 1rem;display:flex;flex-direction:column;gap:.3rem;z-index:100}
    .sidebar-logo{font-family:'Syne',sans-serif;font-size:1.4rem;font-weight:800;letter-spacing:2px;padding:.5rem .5rem 1.5rem;border-bottom:1px solid var(--border);margin-bottom:.8rem}
    .sidebar-logo span{color:var(--red)}
    .snav{display:flex;align-items:center;gap:.8rem;padding:.6rem .8rem;border-radius:8px;color:var(--muted);text-decoration:none;font-size:.88rem;font-weight:600;font-family:'Syne',sans-serif;transition:all .2s;cursor:pointer;border:none;background:none;width:100%;text-align:left}
    .snav:hover,.snav.active{background:rgba(230,0,18,0.1);color:var(--red)}
    .snav i{width:18px;text-align:center}
    .sidebar-footer{margin-top:auto;padding-top:1rem;border-top:1px solid var(--border)}
    .main{margin-left:240px;padding:2rem}
    .page{display:none}.page.active{display:block}
    .topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem}
    .topbar h1{font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800}
    .stat-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.5rem;display:flex;align-items:center;gap:1.2rem}
    .stat-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.3rem}
    .stat-val{font-family:'Syne',sans-serif;font-size:2rem;font-weight:800;line-height:1}
    .stat-label{font-size:.8rem;color:var(--muted);margin-top:.2rem}
    .av-table{width:100%;border-collapse:collapse;font-size:.88rem}
    .av-table th{background:var(--dark2);color:var(--muted);font-family:'Syne',sans-serif;font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;padding:.7rem 1rem;text-align:left;border:1px solid var(--border)}
    .av-table td{padding:.7rem 1rem;border:1px solid var(--border);color:var(--muted);vertical-align:middle}
    .av-table tr:hover td{background:rgba(255,255,255,0.02)}
    .badge-new{background:rgba(0,201,125,.15);color:#00c97d;font-size:.7rem;padding:.2rem .6rem;border-radius:4px;font-weight:700}
    .badge-featured{background:rgba(245,166,35,.15);color:#f5a623;font-size:.7rem;padding:.2rem .6rem;border-radius:4px;font-weight:700}
    .badge-new-enq{background:rgba(230,0,18,.15);color:var(--red);font-size:.7rem;padding:.2rem .6rem;border-radius:4px;font-weight:700}
    .badge-read{background:rgba(255,255,255,.06);color:var(--muted);font-size:.7rem;padding:.2rem .6rem;border-radius:4px}
    .section-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:1.5rem;margin-bottom:1.5rem}
    .section-card h5{font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;margin-bottom:1.2rem}
    .btn-red{background:var(--red);color:#fff;border:none;border-radius:8px;padding:.4rem .9rem;font-family:'Syne',sans-serif;font-weight:700;font-size:.8rem;cursor:pointer;transition:all .2s}
    .btn-red:hover{background:#b3000e}
    .btn-ghost{background:transparent;border:1px solid var(--border);color:var(--muted);border-radius:8px;padding:.4rem .9rem;font-size:.8rem;cursor:pointer;transition:all .2s}
    .btn-ghost:hover{border-color:var(--muted);color:var(--text)}
    .form-field{background:var(--dark2);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:.6rem .9rem;font-size:.88rem;width:100%;outline:none;transition:border .2s;font-family:'DM Sans',sans-serif}
    .form-field:focus{border-color:var(--red)}
    .form-field option{background:var(--dark)}
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);backdrop-filter:blur(8px);z-index:999;align-items:center;justify-content:center;padding:1rem}
    .modal-box{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:2rem;max-width:600px;width:100%;max-height:85vh;overflow-y:auto;animation:mIn .25s ease}
    @keyframes mIn{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}
    .modal-title{font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:800;margin-bottom:1.2rem}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.8rem}
    label{font-size:.78rem;font-family:'Syne',sans-serif;font-weight:700;letter-spacing:.06em;color:var(--muted);display:block;margin-bottom:.3rem;text-transform:uppercase}
    .toast-bar{position:fixed;bottom:1.5rem;right:1.5rem;background:var(--card);border:1px solid var(--border);border-left:3px solid var(--red);border-radius:10px;padding:.8rem 1.2rem;font-size:.88rem;font-weight:600;z-index:9999;transform:translateX(200%);transition:transform .3s;max-width:300px}
    .toast-bar.show{transform:translateX(0)}
    .toast-bar.ok{border-left-color:#00c97d}
    @media(max-width:768px){.sidebar{display:none}.main{margin-left:0}}
  </style>
</head>
<body>

<!-- ═══ SIDEBAR ═══ -->
<div class="sidebar">
  <div class="sidebar-logo">⬡ AUTO<span>VERSE</span></div>
  <button class="snav active" onclick="showPage('dashboard',this)"><i class="fa fa-chart-pie"></i> Dashboard</button>
  <button class="snav" onclick="showPage('cars',this)"><i class="fa fa-car"></i> Cars</button>
  <button class="snav" onclick="showPage('users',this)"><i class="fa fa-users"></i> Users</button>
  <button class="snav" onclick="showPage('enquiries',this)"><i class="fa fa-envelope"></i> Enquiries <span style="background:var(--red);color:#fff;border-radius:50px;padding:.05rem .45rem;font-size:.7rem;margin-left:.3rem"><?= $stats['enquiries'] ?></span></button>
  <div class="sidebar-footer">
    <div style="font-size:.8rem;color:var(--muted);margin-bottom:.6rem">👤 <?= htmlspecialchars($user['name']) ?></div>
    <a href="<?= APP_URL ?>/api/auth.php?action=logout" style="text-decoration:none">
      <button class="snav" style="color:#ff7070"><i class="fa fa-right-from-bracket"></i> Logout</button>
    </a>
  </div>
</div>

<!-- ═══ MAIN ═══ -->
<div class="main">

  <!-- ── DASHBOARD PAGE ── -->
  <div class="page active" id="page-dashboard">
    <div class="topbar">
      <h1>Dashboard</h1>
      <span style="font-size:.85rem;color:var(--muted)"><?= date('l, d M Y') ?></span>
    </div>

    <div class="row g-3 mb-4">
      <?php
      $statCards = [
        ['Cars Listed',     $stats['cars'],      'fa-car',      'rgba(230,0,18,.15)',   '#e60012'],
        ['Total Users',     $stats['users'],     'fa-users',    'rgba(0,201,125,.15)',  '#00c97d'],
        ['New Enquiries',   $stats['enquiries'], 'fa-envelope', 'rgba(245,166,35,.15)', '#f5a623'],
        ['Featured Cars',   $stats['featured'],  'fa-star',     'rgba(91,141,217,.15)', '#5b8dd9'],
      ];
      foreach ($statCards as [$label, $val, $icon, $bg, $color]): ?>
      <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
          <div class="stat-icon" style="background:<?= $bg ?>">
            <i class="fa <?= $icon ?>" style="color:<?= $color ?>"></i>
          </div>
          <div>
            <div class="stat-val"><?= number_format($val) ?></div>
            <div class="stat-label"><?= $label ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="row g-3">
      <div class="col-lg-7">
        <div class="section-card">
          <h5>Recent Listings</h5>
          <table class="av-table">
            <thead><tr><th>Car</th><th>Price</th><th>Type</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach (array_slice($recentCars, 0, 6) as $car): ?>
              <tr>
                <td style="color:var(--text)"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?> <span style="font-size:.75rem;color:var(--muted)">(<?= $car['year'] ?>)</span></td>
                <td><?= '₹' . number_format($car['price']) ?></td>
                <td><?= $car['type'] ?></td>
                <td><?php if ($car['badge'] === 'Featured'): ?><span class="badge-featured">Featured</span><?php elseif ($car['badge'] === 'New'): ?><span class="badge-new">New</span><?php else: ?><span style="font-size:.75rem;color:var(--muted)">—</span><?php endif; ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="section-card">
          <h5>Recent Enquiries</h5>
          <?php foreach (array_slice($recentEnqs, 0, 5) as $enq): ?>
          <div style="padding:.6rem 0;border-bottom:1px solid var(--border);font-size:.85rem">
            <strong style="color:var(--text)"><?= htmlspecialchars($enq['name']) ?></strong>
            <span style="color:var(--muted);font-size:.78rem"> · <?= $enq['type'] ?></span>
            <?php if ($enq['brand']): ?><div style="color:var(--muted);font-size:.78rem"><?= htmlspecialchars($enq['brand'] . ' ' . $enq['model']) ?></div><?php endif; ?>
            <?php if ($enq['status'] === 'new'): ?><span class="badge-new-enq">New</span><?php else: ?><span class="badge-read"><?= ucfirst($enq['status']) ?></span><?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- ── CARS PAGE ── -->
  <div class="page" id="page-cars">
    <div class="topbar">
      <h1>Cars Management</h1>
      <button class="btn-red" onclick="openAddCarModal()"><i class="fa fa-plus"></i> Add Car</button>
    </div>
    <div class="section-card">
      <table class="av-table">
        <thead><tr><th>#</th><th>Brand/Model</th><th>Year</th><th>Price</th><th>Type</th><th>Fuel</th><th>Featured</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($recentCars as $car): ?>
          <tr>
            <td><?= $car['id'] ?></td>
            <td style="color:var(--text);font-weight:600"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></td>
            <td><?= $car['year'] ?></td>
            <td>₹<?= number_format($car['price']) ?></td>
            <td><?= $car['type'] ?></td>
            <td><?= $car['fuel'] ?></td>
            <td><?= $car['is_featured'] ? '<span class="badge-featured">Yes</span>' : '—' ?></td>
            <td>
              <button class="btn-ghost" onclick='openEditModal(<?= json_encode($car) ?>)' style="margin-right:.3rem">Edit</button>
              <button class="btn-red" onclick="deleteCar(<?= $car['id'] ?>)">Del</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── USERS PAGE ── -->
  <div class="page" id="page-users">
    <div class="topbar"><h1>Users</h1></div>
    <div class="section-card">
      <table class="av-table">
        <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
        <tbody>
          <?php foreach ($allUsers as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td style="color:var(--text)"><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= $u['role'] === 'admin' ? '<span class="badge-featured">Admin</span>' : '<span class="badge-read">User</span>' ?></td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── ENQUIRIES PAGE ── -->
  <div class="page" id="page-enquiries">
    <div class="topbar"><h1>Enquiries</h1></div>
    <div class="section-card">
      <table class="av-table">
        <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Type</th><th>Car</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($recentEnqs as $enq): ?>
          <tr>
            <td><?= $enq['id'] ?></td>
            <td style="color:var(--text)"><?= htmlspecialchars($enq['name']) ?></td>
            <td><?= htmlspecialchars($enq['phone']) ?></td>
            <td><?= $enq['type'] ?></td>
            <td><?= $enq['brand'] ? htmlspecialchars($enq['brand'] . ' ' . $enq['model']) : '—' ?></td>
            <td><?= $enq['status'] === 'new' ? '<span class="badge-new-enq">New</span>' : '<span class="badge-read">' . ucfirst($enq['status']) . '</span>' ?></td>
            <td><?= date('d M', strtotime($enq['created_at'])) ?></td>
            <td>
              <button class="btn-ghost" onclick="markRead(<?= $enq['id'] ?>)">Mark Read</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div><!-- /main -->

<!-- ═══ ADD/EDIT CAR MODAL ═══ -->
<div class="modal-overlay" id="carModal">
  <div class="modal-box">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem">
      <div class="modal-title" id="carModalTitle">Add New Car</div>
      <button onclick="closeCarModal()" style="background:none;border:none;color:var(--muted);font-size:1.3rem;cursor:pointer">&times;</button>
    </div>
    <form id="carForm" onsubmit="submitCarForm(event)">
      <input type="hidden" id="editCarId"/>
      <div class="form-grid">
        <div><label>Brand</label><input class="form-field" id="fBrand" required placeholder="e.g. Tata"/></div>
        <div><label>Model</label><input class="form-field" id="fModel" required placeholder="e.g. Nexon EV"/></div>
        <div><label>Year</label><input class="form-field" id="fYear" type="number" required min="2000" max="2026"/></div>
        <div><label>Price (₹)</label><input class="form-field" id="fPrice" type="number" required/></div>
        <div>
          <label>Type</label>
          <select class="form-field" id="fType" required>
            <option value="">Select</option>
            <option>SUV</option><option>Sedan</option><option>Hatchback</option>
            <option>Coupe</option><option>Electric</option><option>Luxury</option>
          </select>
        </div>
        <div>
          <label>Fuel</label>
          <select class="form-field" id="fFuel" required>
            <option value="">Select</option>
            <option>Petrol</option><option>Diesel</option><option>Electric</option><option>Hybrid</option>
          </select>
        </div>
        <div><label>KM Driven</label><input class="form-field" id="fKm" type="number" value="0"/></div>
        <div><label>Seats</label><input class="form-field" id="fSeats" type="number" value="5"/></div>
        <div><label>Engine</label><input class="form-field" id="fEngine" placeholder="e.g. 1.5L Turbo"/></div>
        <div><label>Power</label><input class="form-field" id="fPower" placeholder="e.g. 158 bhp"/></div>
        <div><label>Torque</label><input class="form-field" id="fTorque" placeholder="e.g. 253 Nm"/></div>
        <div><label>Transmission</label><input class="form-field" id="fTrans" placeholder="e.g. 7-speed DCT"/></div>
      </div>
      <div style="margin-top:.8rem"><label>Image URL</label><input class="form-field" id="fImg" placeholder="https://..."/></div>
      <div style="margin-top:.8rem"><label>Description</label><textarea class="form-field" id="fDesc" rows="3"></textarea></div>
      <div style="display:flex;gap:1rem;margin-top:1rem;align-items:center">
        <div>
          <label>Badge</label>
          <select class="form-field" id="fBadge">
            <option value="">None</option><option>New</option><option>Featured</option>
          </select>
        </div>
        <div style="display:flex;align-items:center;gap:.5rem;padding-top:1.4rem">
          <input type="checkbox" id="fFeatured" style="accent-color:var(--red)"/>
          <label style="text-transform:none;letter-spacing:0;color:var(--text);margin:0">Mark as Featured</label>
        </div>
      </div>
      <div style="display:flex;gap:.8rem;margin-top:1.5rem">
        <button type="submit" class="btn-red" style="flex:1;padding:.7rem">Save Car</button>
        <button type="button" class="btn-ghost" onclick="closeCarModal()" style="flex:1;padding:.7rem">Cancel</button>
      </div>
    </form>
  </div>
</div>

<div class="toast-bar" id="adminToast"></div>

<script>
const API = '<?= APP_URL ?>/api';

function showPage(page, el) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.snav').forEach(n => n.classList.remove('active'));
  document.getElementById('page-' + page).classList.add('active');
  el.classList.add('active');
}

function toast(msg, ok = false) {
  const t = document.getElementById('adminToast');
  t.textContent = msg;
  t.className = 'toast-bar show' + (ok ? ' ok' : '');
  setTimeout(() => t.classList.remove('show'), 3500);
}

function openAddCarModal() {
  document.getElementById('carModalTitle').textContent = 'Add New Car';
  document.getElementById('editCarId').value = '';
  document.getElementById('carForm').reset();
  document.getElementById('carModal').style.display = 'flex';
}

function openEditModal(car) {
  document.getElementById('carModalTitle').textContent = 'Edit Car';
  document.getElementById('editCarId').value = car.id;
  document.getElementById('fBrand').value  = car.brand;
  document.getElementById('fModel').value  = car.model;
  document.getElementById('fYear').value   = car.year;
  document.getElementById('fPrice').value  = car.price;
  document.getElementById('fType').value   = car.type;
  document.getElementById('fFuel').value   = car.fuel;
  document.getElementById('fKm').value     = car.km_driven;
  document.getElementById('fSeats').value  = car.seats;
  document.getElementById('fEngine').value = car.engine || '';
  document.getElementById('fPower').value  = car.power  || '';
  document.getElementById('fTorque').value = car.torque || '';
  document.getElementById('fTrans').value  = car.transmission || '';
  document.getElementById('fImg').value    = car.img_url || '';
  document.getElementById('fDesc').value   = car.description || '';
  document.getElementById('fBadge').value  = car.badge || '';
  document.getElementById('fFeatured').checked = car.is_featured == 1;
  document.getElementById('carModal').style.display = 'flex';
}

function closeCarModal() {
  document.getElementById('carModal').style.display = 'none';
}

async function submitCarForm(e) {
  e.preventDefault();
  const id = document.getElementById('editCarId').value;
  const payload = {
    brand: fBrand.value, model: fModel.value, year: fYear.value,
    price: fPrice.value, type: fType.value, fuel: fFuel.value,
    km_driven: fKm.value, seats: fSeats.value, engine: fEngine.value,
    power: fPower.value, torque: fTorque.value, transmission: fTrans.value,
    img_url: fImg.value, description: fDesc.value,
    badge: fBadge.value, is_featured: fFeatured.checked ? 1 : 0
  };

  const url    = id ? `${API}/cars.php?id=${id}` : `${API}/cars.php`;
  const method = id ? 'PUT' : 'POST';

  const res  = await fetch(url, { method, headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload), credentials:'include' });
  const data = await res.json();

  if (data.success) {
    toast(data.message, true);
    closeCarModal();
    setTimeout(() => location.reload(), 1200);
  } else {
    toast(data.error || 'Error saving car.');
  }
}

async function deleteCar(id) {
  if (!confirm('Remove this car listing?')) return;
  const res  = await fetch(`${API}/cars.php?id=${id}`, { method:'DELETE', credentials:'include' });
  const data = await res.json();
  if (data.success) { toast('Car removed.', true); setTimeout(() => location.reload(), 1200); }
  else toast(data.error || 'Error.');
}

async function markRead(id) {
  await fetch(`${API}/enquiries.php?id=${id}`, {
    method: 'PUT',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ status: 'read' }),
    credentials: 'include'
  });
  toast('Marked as read.', true);
  setTimeout(() => location.reload(), 1000);
}

// Close modal on backdrop click
document.getElementById('carModal').addEventListener('click', function(e) {
  if (e.target === this) closeCarModal();
});
</script>
</body>
</html>
