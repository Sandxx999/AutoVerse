<?php
// ══════════════════════════════════════════
//  AUTOVERSE — api/auth.php  (FIXED)
//  POST /api/auth.php?action=register
//  POST /api/auth.php?action=login
//  POST /api/auth.php?action=logout
//  GET  /api/auth.php?action=me
// ══════════════════════════════════════════

require_once __DIR__ . '/../config/db.php';
setCorsHeaders();

getCurrentUser(); // boots session early

$action = isset($_GET['action']) ? $_GET['action'] : '';
$db     = getDB();

if ($action === 'register')    register($db);
elseif ($action === 'login')   login($db);
elseif ($action === 'logout')  logoutUser();
elseif ($action === 'me')      me();
else respond(array('error' => 'Invalid action.'), 400);

// ─────────────────────────────────────────
// REGISTER
// ─────────────────────────────────────────
function register($db) {
    $b = getBody();

    $name  = trim(isset($b['name'])     ? $b['name']     : '');
    $email = trim(isset($b['email'])    ? $b['email']    : '');
    $pass  =      isset($b['password']) ? $b['password'] : '';
    $phone = trim(isset($b['phone'])    ? $b['phone']    : '');

    if (!$name || !$email || !$pass)
        respond(array('error' => 'Name, email and password are required.'), 422);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        respond(array('error' => 'Invalid email address.'), 422);

    if (strlen($pass) < 6)
        respond(array('error' => 'Password must be at least 6 characters.'), 422);

    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute(array(':email' => $email));
    if ($stmt->fetch()) respond(array('error' => 'Email already registered.'), 409);

    $hash = password_hash($pass, PASSWORD_BCRYPT, array('cost' => 12));

    $stmt = $db->prepare(
        'INSERT INTO users (name, email, password, phone) VALUES (:name, :email, :pass, :phone)'
    );
    $stmt->execute(array(
        ':name'  => $name,
        ':email' => $email,
        ':pass'  => $hash,
        ':phone' => $phone,
    ));

    $userId = $db->lastInsertId();
    $user   = array('id' => (int)$userId, 'name' => $name, 'email' => $email, 'role' => 'user');

    $_SESSION['user'] = $user;

    respond(array('success' => true, 'message' => 'Account created successfully.', 'user' => $user), 201);
}

// ─────────────────────────────────────────
// LOGIN
// ─────────────────────────────────────────
function login($db) {
    $b     = getBody();
    $email = trim(isset($b['email'])    ? $b['email']    : '');
    $pass  =      isset($b['password']) ? $b['password'] : '';

    if (!$email || !$pass)
        respond(array('error' => 'Email and password are required.'), 422);

    $stmt = $db->prepare(
        'SELECT id, name, email, password, role FROM users WHERE email = :email'
    );
    $stmt->execute(array(':email' => $email));
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password']))
        respond(array('error' => 'Invalid email or password.'), 401);

    unset($user['password']);
    $_SESSION['user'] = $user;

    respond(array('success' => true, 'message' => 'Login successful.', 'user' => $user));
}

// ─────────────────────────────────────────
// LOGOUT
// ─────────────────────────────────────────
function logoutUser() {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    respond(array('success' => true, 'message' => 'Logged out.'));
}

// ─────────────────────────────────────────
// ME
// ─────────────────────────────────────────
function me() {
    $user = getCurrentUser();
    if (!$user) respond(array('error' => 'Not authenticated.'), 401);
    respond(array('success' => true, 'user' => $user));
}
