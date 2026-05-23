<?php
// ══════════════════════════════════════════
//  AUTOVERSE — api/enquiries.php  (FIXED)
// ══════════════════════════════════════════

require_once __DIR__ . '/../config/db.php';
setCorsHeaders();

$db     = getDB();
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($method === 'GET')       listEnquiries($db);
elseif ($method === 'POST')  submitEnquiry($db);
elseif ($method === 'PUT')   updateEnquiry($db, $id);
else respond(array('error' => 'Method not allowed.'), 405);

function submitEnquiry($db) {
    $b = getBody();

    $required = array('name', 'email', 'phone');
    foreach ($required as $f) {
        if (empty($b[$f])) respond(array('error' => "Field '$f' is required."), 422);
    }

    $user = getCurrentUser();
    $stmt = $db->prepare(
        'INSERT INTO enquiries (user_id, car_id, name, email, phone, type, message)
         VALUES (:uid, :cid, :name, :email, :phone, :type, :msg)'
    );
    $stmt->execute(array(
        ':uid'   => ($user ? $user['id'] : null),
        ':cid'   => (isset($b['car_id']) ? (int)$b['car_id'] : null),
        ':name'  => $b['name'],
        ':email' => $b['email'],
        ':phone' => $b['phone'],
        ':type'  => isset($b['type'])    ? $b['type']    : 'Other',
        ':msg'   => isset($b['message']) ? $b['message'] : null,
    ));

    respond(array('success' => true, 'message' => 'Enquiry submitted. We will contact you shortly.'), 201);
}

function listEnquiries($db) {
    requireAdmin();
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $where  = $status ? 'WHERE e.status = :status' : '';
    $params = $status ? array(':status' => $status) : array();

    $stmt = $db->prepare(
        "SELECT e.*, c.brand, c.model FROM enquiries e
         LEFT JOIN cars c ON e.car_id = c.id
         $where ORDER BY e.created_at DESC"
    );
    $stmt->execute($params);
    respond(array('success' => true, 'data' => $stmt->fetchAll()));
}

function updateEnquiry($db, $id) {
    if (!$id) respond(array('error' => 'ID required.'), 400);
    requireAdmin();
    $b      = getBody();
    $status = isset($b['status']) ? $b['status'] : null;
    if (!$status) respond(array('error' => 'Status required.'), 422);

    $stmt = $db->prepare('UPDATE enquiries SET status = :s WHERE id = :id');
    $stmt->execute(array(':s' => $status, ':id' => $id));
    respond(array('success' => true, 'message' => 'Status updated.'));
}
