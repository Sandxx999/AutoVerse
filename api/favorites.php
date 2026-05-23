<?php
// ══════════════════════════════════════════
//  AUTOVERSE — api/favorites.php  (FIXED)
// ══════════════════════════════════════════

require_once __DIR__ . '/../config/db.php';
setCorsHeaders();

$db     = getDB();
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

if ($method === 'GET')       getFavorites($db);
elseif ($method === 'POST')  toggleFavorite($db);
else respond(array('error' => 'Method not allowed.'), 405);

function getFavorites($db) {
    $user = requireAuth();

    $stmt = $db->prepare(
        'SELECT c.* FROM favorites f
         JOIN cars c ON f.car_id = c.id
         WHERE f.user_id = :uid AND c.is_active = 1
         ORDER BY f.created_at DESC'
    );
    $stmt->execute(array(':uid' => $user['id']));
    respond(array('success' => true, 'data' => $stmt->fetchAll()));
}

function toggleFavorite($db) {
    $user  = requireAuth();
    $b     = getBody();
    $carId = (int)(isset($b['car_id']) ? $b['car_id'] : 0);

    if (!$carId) respond(array('error' => 'car_id required.'), 422);

    $stmt = $db->prepare('SELECT id FROM favorites WHERE user_id = :uid AND car_id = :cid');
    $stmt->execute(array(':uid' => $user['id'], ':cid' => $carId));

    if ($stmt->fetch()) {
        $db->prepare('DELETE FROM favorites WHERE user_id = :uid AND car_id = :cid')
           ->execute(array(':uid' => $user['id'], ':cid' => $carId));
        respond(array('success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist.'));
    } else {
        $db->prepare('INSERT INTO favorites (user_id, car_id) VALUES (:uid, :cid)')
           ->execute(array(':uid' => $user['id'], ':cid' => $carId));
        respond(array('success' => true, 'action' => 'added', 'message' => 'Added to wishlist.'));
    }
}
