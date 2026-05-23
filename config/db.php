<?php
// ══════════════════════════════════════════
//  AUTOVERSE — config/db.php  (v2 - FIXED)
//  Compatible: PHP 7.4, 8.0, 8.1, 8.2+
//  Fix: guard double-load, removed PHP8-only
//       type hints (mixed, :void, :array, ?array)
// ══════════════════════════════════════════

// ─── Guard: prevent fatal if included twice ───
if (defined('DB_HOST')) return;

// ─── Database credentials ───
define('DB_HOST',     'localhost');
define('DB_NAME',     'autoverse_db');
define('DB_USER',     'root');       // XAMPP default
define('DB_PASS',     '');           // XAMPP default = empty
define('DB_CHARSET',  'utf8mb4');

// ─── App settings ───
define('APP_NAME',    'AutoVerse');
define('APP_URL',     'http://localhost/autoverse');  // no trailing slash
define('SESSION_NAME','av_session');

// ─── CORS origins ───
define('ALLOWED_ORIGINS', array(
    'http://localhost',
    'http://localhost/autoverse',
    'http://127.0.0.1',
    'http://localhost:3000',
    'null',   // browsers send "null" for file:// origins
));

// ─────────────────────────────────────────
//  PDO Singleton
// ─────────────────────────────────────────
function getDB() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname='    . DB_NAME
         . ';charset='   . DB_CHARSET;

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ));
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        // Shows detail locally — remove $e->getMessage() in production
        echo json_encode(array(
            'error'  => 'Database connection failed.',
            'detail' => $e->getMessage()
        ));
        exit;
    }

    return $pdo;
}

// ─────────────────────────────────────────
//  CORS + JSON Headers
// ─────────────────────────────────────────
function setCorsHeaders() {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    if (in_array($origin, ALLOWED_ORIGINS)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    } else {
        header('Access-Control-Allow-Origin: *');  // open during local dev
    }

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json; charset=UTF-8');

    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// ─────────────────────────────────────────
//  JSON Response Helper
// ─────────────────────────────────────────
function respond($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// ─────────────────────────────────────────
//  Read JSON Request Body
// ─────────────────────────────────────────
function getBody() {
    $raw = file_get_contents('php://input');
    if (!$raw) return array();
    $data = json_decode($raw, true);
    return is_array($data) ? $data : array();
}

// ─────────────────────────────────────────
//  Session: Get Logged-In User
// ─────────────────────────────────────────
function getCurrentUser() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params(array(
            'lifetime' => 86400 * 7,
            'path'     => '/',
            'secure'   => false,     // true on HTTPS
            'httponly' => true,
            'samesite' => 'Lax',
        ));
        session_start();
    }
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

// ─────────────────────────────────────────
//  Require Login
// ─────────────────────────────────────────
function requireAuth() {
    $user = getCurrentUser();
    if (!$user) respond(array('error' => 'Authentication required.'), 401);
    return $user;
}

// ─────────────────────────────────────────
//  Require Admin
// ─────────────────────────────────────────
function requireAdmin() {
    $user = requireAuth();
    if (!isset($user['role']) || $user['role'] !== 'admin') {
        respond(array('error' => 'Admin access required.'), 403);
    }
    return $user;
}
