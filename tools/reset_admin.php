<?php
// ══════════════════════════════════════════
//  AUTOVERSE — tools/reset_admin.php
//  Run once to fix admin password
//  Visit: http://localhost/autoverse/tools/reset_admin.php
//  DELETE this file after use!
// ══════════════════════════════════════════

require_once __DIR__ . '/../config/db.php';

$newPassword = 'Admin@123';   // ← change this to whatever you want
$hash        = password_hash($newPassword, PASSWORD_BCRYPT, array('cost' => 10));

$db   = getDB();

// Check if admin exists
$stmt = $db->prepare('SELECT id, email FROM users WHERE email = :email');
$stmt->execute(array(':email' => 'admin@autoverse.in'));
$admin = $stmt->fetch();

if ($admin) {
    // Update existing admin password
    $upd = $db->prepare('UPDATE users SET password = :hash WHERE email = :email');
    $upd->execute(array(':hash' => $hash, ':email' => 'admin@autoverse.in'));
    $action = 'Password UPDATED';
} else {
    // Insert admin if missing
    $ins = $db->prepare(
        'INSERT INTO users (name, email, password, role, is_verified)
         VALUES (:name, :email, :hash, "admin", 1)'
    );
    $ins->execute(array(
        ':name'  => 'Admin AutoVerse',
        ':email' => 'admin@autoverse.in',
        ':hash'  => $hash,
    ));
    $action = 'Admin user CREATED';
}

// Verify it works
$verify = $db->prepare('SELECT password FROM users WHERE email = :email');
$verify->execute(array(':email' => 'admin@autoverse.in'));
$row    = $verify->fetch();
$works  = password_verify($newPassword, $row['password']) ? 'YES ✅' : 'NO ❌';
?>
<!DOCTYPE html>
<html>
<head>
<title>Admin Reset</title>
<style>
  body { font-family: sans-serif; background: #0a0a0a; color: #f0f2f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
  .card { background: #141820; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 2.5rem; max-width: 480px; width: 100%; }
  h2 { font-size: 1.4rem; margin-bottom: 1.5rem; }
  .row { display: flex; justify-content: space-between; padding: .6rem 0; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: .9rem; }
  .row span:first-child { color: #8a9ab5; }
  .ok  { color: #00c97d; font-weight: 700; }
  .bad { color: #e60012; font-weight: 700; }
  .hash { font-family: monospace; font-size: .72rem; color: #5a6a85; word-break: break-all; margin: 1rem 0; padding: .8rem; background: #1e2330; border-radius: 8px; }
  .warn { background: rgba(230,0,18,0.1); border: 1px solid rgba(230,0,18,0.3); border-radius: 8px; padding: .9rem; font-size: .85rem; color: #ff7070; margin-top: 1.5rem; }
  a { color: #e60012; }
</style>
</head>
<body>
<div class="card">
  <h2>⚡ Admin Password Reset</h2>
  <div class="row"><span>Action</span><span class="ok"><?= $action ?></span></div>
  <div class="row"><span>Email</span><span>admin@autoverse.in</span></div>
  <div class="row"><span>New Password</span><span class="ok"><?= htmlspecialchars($newPassword) ?></span></div>
  <div class="row"><span>Hash verifies</span><span class="<?= $works === 'YES ✅' ? 'ok' : 'bad' ?>"><?= $works ?></span></div>
  <div class="hash"><?= htmlspecialchars($hash) ?></div>
  <div class="warn">
    ⚠️ <strong>Delete this file immediately after use!</strong><br/>
    <code>C:\xampp\htdocs\autoverse\tools\reset_admin.php</code><br/><br/>
    <a href="../admin/login.php">→ Go to Admin Login</a>
  </div>
</div>
</body>
</html>
