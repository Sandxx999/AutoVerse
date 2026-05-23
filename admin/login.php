<?php
require_once __DIR__ . '/../config/db.php';
session_name(SESSION_NAME);
session_start();
if (getCurrentUser() && getCurrentUser()['role'] === 'admin') {
    header('Location: index.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>AutoVerse Admin Login</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet"/>
<style>
:root{--red:#e60012;--dark:#0a0a0a;--dark2:#141820;--border:rgba(255,255,255,0.08);--text:#f0f2f5;--muted:#8a9ab5}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',sans-serif;background:var(--dark);color:var(--text);display:flex;align-items:center;justify-content:center;min-height:100vh;background:radial-gradient(ellipse at center,#15050a 0%,var(--dark) 70%)}
.login-card{background:var(--dark2);border:1px solid var(--border);border-radius:18px;padding:2.5rem;width:100%;max-width:400px}
.login-logo{font-family:'Syne',sans-serif;font-size:1.8rem;font-weight:800;letter-spacing:3px;text-align:center;margin-bottom:.3rem}
.login-logo span{color:var(--red)}
.login-sub{text-align:center;font-size:.85rem;color:var(--muted);margin-bottom:2rem}
label{font-family:'Syne',sans-serif;font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);display:block;margin-bottom:.4rem}
input{width:100%;background:#1e2330;border:1px solid var(--border);color:var(--text);border-radius:10px;padding:.75rem 1rem;font-size:.9rem;outline:none;transition:border .2s;margin-bottom:1rem}
input:focus{border-color:var(--red)}
.btn{width:100%;background:var(--red);color:#fff;border:none;border-radius:10px;padding:.85rem;font-family:'Syne',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;transition:all .2s}
.btn:hover{background:#b3000e}
.error{background:rgba(230,0,18,.1);border:1px solid rgba(230,0,18,.3);border-radius:8px;padding:.7rem 1rem;font-size:.85rem;color:#ff7070;margin-bottom:1rem}
</style>
</head>
<body>
<div class="login-card">
  <div class="login-logo">⬡ AUTO<span>VERSE</span></div>
  <div class="login-sub">Admin Dashboard Login</div>
  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $db    = getDB();
      $email = trim($_POST['email'] ?? '');
      $pass  = $_POST['password'] ?? '';
      $stmt  = $db->prepare('SELECT id,name,email,password,role FROM users WHERE email=:e AND role="admin"');
      $stmt->execute([':e' => $email]);
      $user = $stmt->fetch();
      if ($user && password_verify($pass, $user['password'])) {
          unset($user['password']);
          $_SESSION['user'] = $user;
          header('Location: index.php');
          exit;
      }
      echo '<div class="error">❌ Invalid credentials or not an admin account.</div>';
  }
  ?>
  <form method="POST">
    <label>Email</label>
    <input type="email" name="email" required placeholder="admin@autoverse.in"/>
    <label>Password</label>
    <input type="password" name="password" required placeholder="••••••••"/>
    <button type="submit" class="btn">Sign In to Admin →</button>
  </form>
  <div style="text-align:center;margin-top:1rem;font-size:.8rem;color:var(--muted)">Default: admin@autoverse.in / password</div>
</div>
</body>
</html>
