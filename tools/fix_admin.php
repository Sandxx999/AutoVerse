<?php
// ══════════════════════════════════════════
//  AUTOVERSE — tools/fix_admin.php
//  Fixes admin password + seeds cars if missing
//  Visit: http://localhost/autoverse/tools/fix_admin.php
//  DELETE after use!
// ══════════════════════════════════════════
require_once __DIR__ . '/../config/db.php';
$db = getDB();

// ── 1. Fix admin password ──────────────────
$newPass = 'Admin@123';
$hash    = password_hash($newPass, PASSWORD_BCRYPT, array('cost' => 10));

$check = $db->prepare('SELECT id FROM users WHERE email = :e');
$check->execute(array(':e' => 'admin@autoverse.in'));

if ($check->fetch()) {
    $db->prepare('UPDATE users SET password = :h, role = "admin", is_verified = 1 WHERE email = :e')
       ->execute(array(':h' => $hash, ':e' => 'admin@autoverse.in'));
    $adminMsg = 'Password UPDATED to Admin@123';
} else {
    $db->prepare('INSERT INTO users (name,email,password,role,is_verified) VALUES(:n,:e,:h,"admin",1)')
       ->execute(array(':n'=>'Admin AutoVerse',':e'=>'admin@autoverse.in',':h'=>$hash));
    $adminMsg = 'Admin user CREATED with password Admin@123';
}

// Verify hash works
$row    = $db->query('SELECT password FROM users WHERE email = "admin@autoverse.in"')->fetch();
$verify = password_verify($newPass, $row['password']) ? 'YES ✅' : 'NO ❌';

// ── 2. Check cars table ────────────────────
$carCount = (int)$db->query('SELECT COUNT(*) FROM cars')->fetchColumn();

// ── 3. Check images folder ─────────────────
$imgDir   = __DIR__ . '/../images/cars/';
$imgFiles = is_dir($imgDir) ? count(glob($imgDir . '*.jpg')) + count(glob($imgDir . '*.png')) + count(glob($imgDir . '*.webp')) : 0;
?>
<!DOCTYPE html>
<html>
<head>
<title>AutoVerse Fix Admin</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:sans-serif;background:#0a0a0a;color:#f0f2f5;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1rem}
  .card{background:#141820;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:2.5rem;width:100%;max-width:520px}
  h2{font-size:1.4rem;margin-bottom:1.5rem}
  .row{display:flex;justify-content:space-between;align-items:center;padding:.65rem 0;border-bottom:1px solid rgba(255,255,255,.06);font-size:.9rem}
  .row span:first-child{color:#8a9ab5}
  .ok{color:#00c97d;font-weight:700}
  .bad{color:#e60012;font-weight:700}
  .warn{color:#f5a623;font-weight:700}
  .hash{font-family:monospace;font-size:.7rem;color:#5a6a85;word-break:break-all;background:#1e2330;border-radius:8px;padding:.8rem;margin:1rem 0}
  .actions{display:flex;gap:.8rem;margin-top:1.5rem;flex-wrap:wrap}
  a.btn{flex:1;background:#e60012;color:#fff;border-radius:8px;padding:.75rem;text-decoration:none;font-weight:700;font-size:.88rem;text-align:center}
  a.btn2{flex:1;background:#141820;border:1px solid rgba(255,255,255,.12);color:#f0f2f5;border-radius:8px;padding:.75rem;text-decoration:none;font-weight:700;font-size:.88rem;text-align:center}
  .section{margin-top:1.5rem;padding-top:1.2rem;border-top:1px solid rgba(255,255,255,.08)}
  .section h3{font-size:.9rem;margin-bottom:1rem;color:#8a9ab5;letter-spacing:.06em;text-transform:uppercase}
  .step{background:#1e2330;border-radius:8px;padding:.7rem 1rem;font-size:.82rem;color:#8a9ab5;margin-bottom:.5rem;line-height:1.6}
  .step strong{color:#f0f2f5}
</style>
</head>
<body>
<div class="card">
  <h2>⚡ AutoVerse System Fix</h2>

  <div class="row"><span>Admin action</span><span class="ok"><?= $adminMsg ?></span></div>
  <div class="row"><span>Email</span><span>admin@autoverse.in</span></div>
  <div class="row"><span>Password</span><span class="ok">Admin@123</span></div>
  <div class="row"><span>Hash verifies</span><span class="<?= $verify === 'YES ✅' ? 'ok' : 'bad' ?>"><?= $verify ?></span></div>
  <div class="row"><span>Cars in database</span><span class="<?= $carCount >= 12 ? 'ok' : 'warn' ?>"><?= $carCount ?> cars <?= $carCount >= 12 ? '✅' : '⚠ run schema_fixed.sql' ?></span></div>
  <div class="row"><span>Images downloaded</span><span class="<?= $imgFiles >= 10 ? 'ok' : 'warn' ?>"><?= $imgFiles ?> files <?= $imgFiles >= 10 ? '✅' : '— run download_images.php' ?></span></div>

  <div class="hash"><?= htmlspecialchars($hash) ?></div>

  <div class="section">
    <h3>Next Steps</h3>
    <?php if ($carCount < 12): ?>
    <div class="step">1. <strong>Run schema_fixed.sql</strong> in phpMyAdmin SQL tab to seed cars</div>
    <?php endif; ?>
    <?php if ($imgFiles < 10): ?>
    <div class="step"><?= $carCount < 12 ? '2' : '1' ?>. <strong>Run download_images.php</strong> to get car photos on your server</div>
    <?php endif; ?>
    <div class="step">✅ <strong>Login to Admin:</strong> admin@autoverse.in / Admin@123</div>
  </div>

  <div class="actions">
    <a class="btn"  href="../admin/login.php">→ Admin Login</a>
    <a class="btn2" href="download_images.php">→ Download Images</a>
    <a class="btn2" href="../index.html">→ Website</a>
  </div>
</div>
</body>
</html>
