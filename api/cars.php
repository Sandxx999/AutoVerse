<?php
// ══════════════════════════════════════════
//  AUTOVERSE — api/cars.php
//  GET  /api/cars.php          → list / search / filter
//  GET  /api/cars.php?id=N     → single car
//  POST /api/cars.php          → create listing (auth required)
//  PUT  /api/cars.php?id=N     → update car (owner or admin)
//  DELETE /api/cars.php?id=N   → delete car (admin only)
// ══════════════════════════════════════════

require_once __DIR__ . '/../config/db.php';
setCorsHeaders();

$db     = getDB();
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

// ─── Router ───────────────────────────────
if ($method === 'GET') {
    if ($id) getOneCar($db, $id); else getCars($db);
} elseif ($method === 'POST') {
    createCar($db);
} elseif ($method === 'PUT') {
    updateCar($db, $id);
} elseif ($method === 'DELETE') {
    deleteCar($db, $id);
} else {
    respond(array('error' => 'Method not allowed'), 405);
}

// ─────────────────────────────────────────
// GET ALL CARS  (with filters, search, sort, pagination)
// ─────────────────────────────────────────
function getCars($db) {
    $search   = trim(isset($_GET['search'])   ? $_GET['search']   : '');
    $type     = trim(isset($_GET['type'])     ? $_GET['type']     : '');
    $fuel     = trim(isset($_GET['fuel'])     ? $_GET['fuel']     : '');
    $brand    = trim(isset($_GET['brand'])    ? $_GET['brand']    : '');
    $featured = isset($_GET['featured'])      ? 1                 : null;
    $sort     = isset($_GET['sort'])          ? $_GET['sort']     : 'created_at_desc';
    $page     = max(1, (int)(isset($_GET['page'])  ? $_GET['page']  : 1));
    $limit    = min(50, max(1, (int)(isset($_GET['limit']) ? $_GET['limit'] : 12)));
    $offset   = ($page - 1) * $limit;

    $where  = array('c.is_active = 1');
    $params = array();

    if ($search) {
        $where[]               = '(c.brand LIKE :searchLike OR c.model LIKE :searchLike OR c.description LIKE :searchLike)';
        $params[':searchLike'] = '%' . $search . '%';
    }
    if ($type)     { $where[] = 'c.type  = :type';  $params[':type']  = $type; }
    if ($fuel)     { $where[] = 'c.fuel  = :fuel';  $params[':fuel']  = $fuel; }
    if ($brand)    { $where[] = 'c.brand = :brand'; $params[':brand'] = $brand; }
    if ($featured) { $where[] = 'c.is_featured = 1'; }

    $orderMap = array(
        'price_asc'       => 'c.price ASC',
        'price_desc'      => 'c.price DESC',
        'year_desc'       => 'c.year DESC',
        'rating'          => 'c.rating DESC',
        'created_at_desc' => 'c.created_at DESC',
    );
    $order = isset($orderMap[$sort]) ? $orderMap[$sort] : 'c.created_at DESC';

    $whereSQL = implode(' AND ', $where);

    // Total count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM cars c WHERE $whereSQL");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // Cars with seller info
    $sql = "SELECT c.*, u.name AS seller_name, u.phone AS seller_phone
            FROM cars c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE $whereSQL
            ORDER BY $order
            LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $cars = $stmt->fetchAll();

    respond(array(
        'success' => true,
        'data'    => $cars,
        'meta'    => array(
            'total'       => $total,
            'page'        => $page,
            'limit'       => $limit,
            'total_pages' => (int)ceil($total / $limit),
        ),
    ));
}

// ─────────────────────────────────────────
// GET ONE CAR
// ─────────────────────────────────────────
function getOneCar($db, $id) {
    $stmt = $db->prepare(
        'SELECT c.*, u.name AS seller_name, u.phone AS seller_phone, u.email AS seller_email
         FROM cars c
         LEFT JOIN users u ON c.user_id = u.id
         WHERE c.id = :id AND c.is_active = 1'
    );
    $stmt->execute(array(':id' => $id));
    $car = $stmt->fetch();

    if (!$car) respond(array('error' => 'Car not found.'), 404);
    respond(array('success' => true, 'data' => $car));
}

// ─────────────────────────────────────────
// CREATE CAR
// ─────────────────────────────────────────
function createCar($db) {
    $user = requireAuth();
    $b    = getBody();

    $required = array('brand','model','year','price','type','fuel');
    foreach ($required as $field) {
        if (empty($b[$field])) respond(array('error' => "Field '$field' is required."), 422);
    }

    $stmt = $db->prepare(
        'INSERT INTO cars
         (user_id, brand, model, year, price, type, fuel, km_driven, engine, power,
          torque, transmission, seats, color, description, img_url, badge, is_featured)
         VALUES
         (:user_id,:brand,:model,:year,:price,:type,:fuel,:km,:engine,:power,
          :torque,:trans,:seats,:color,:desc,:img,:badge,:featured)'
    );

    $stmt->execute(array(
        ':user_id'  => $user['id'],
        ':brand'    => $b['brand'],
        ':model'    => $b['model'],
        ':year'     => (int)$b['year'],
        ':price'    => (float)$b['price'],
        ':type'     => $b['type'],
        ':fuel'     => $b['fuel'],
        ':km'       => (int)(isset($b['km_driven'])    ? $b['km_driven']    : 0),
        ':engine'   => isset($b['engine'])       ? $b['engine']       : null,
        ':power'    => isset($b['power'])        ? $b['power']        : null,
        ':torque'   => isset($b['torque'])       ? $b['torque']       : null,
        ':trans'    => isset($b['transmission']) ? $b['transmission'] : null,
        ':seats'    => (int)(isset($b['seats'])  ? $b['seats']        : 5),
        ':color'    => isset($b['color'])        ? $b['color']        : null,
        ':desc'     => isset($b['description'])  ? $b['description']  : null,
        ':img'      => isset($b['img_url'])      ? $b['img_url']      : null,
        ':badge'    => isset($b['badge'])        ? $b['badge']        : '',
        ':featured' => (int)(isset($b['is_featured']) ? $b['is_featured'] : 0),
    ));

    $newId = $db->lastInsertId();
    respond(array('success' => true, 'message' => 'Listing created.', 'id' => $newId), 201);
}

// ─────────────────────────────────────────
// UPDATE CAR
// ─────────────────────────────────────────
function updateCar($db, $id) {
    if (!$id) respond(array('error' => 'Car ID required.'), 400);
    $user = requireAuth();
    $b    = getBody();

    $stmt = $db->prepare('SELECT user_id FROM cars WHERE id = :id');
    $stmt->execute(array(':id' => $id));
    $car = $stmt->fetch();

    if (!$car) respond(array('error' => 'Car not found.'), 404);
    if ($car['user_id'] != $user['id'] && $user['role'] !== 'admin')
        respond(array('error' => 'Forbidden.'), 403);

    $fields = array('brand','model','year','price','type','fuel','km_driven','engine',
                    'power','torque','transmission','seats','color','description',
                    'img_url','badge','is_featured','is_active');
    $sets   = array();
    $params = array(':id' => $id);

    foreach ($fields as $f) {
        if (array_key_exists($f, $b)) {
            $sets[]       = "$f = :$f";
            $params[":$f"] = $b[$f];
        }
    }

    if (empty($sets)) respond(array('error' => 'No fields to update.'), 422);

    $db->prepare('UPDATE cars SET ' . implode(',', $sets) . ' WHERE id = :id')
       ->execute($params);

    respond(array('success' => true, 'message' => 'Car updated.'));
}

// ─────────────────────────────────────────
// DELETE CAR (soft-delete)
// ─────────────────────────────────────────
function deleteCar($db, $id) {
    if (!$id) respond(array('error' => 'Car ID required.'), 400);
    requireAdmin();

    $stmt = $db->prepare('UPDATE cars SET is_active = 0 WHERE id = :id');
    $stmt->execute(array(':id' => $id));

    if ($stmt->rowCount() === 0) respond(array('error' => 'Car not found.'), 404);
    respond(array('success' => true, 'message' => 'Car removed.'));
}
