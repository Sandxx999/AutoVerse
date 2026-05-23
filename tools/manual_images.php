<?php
// ══════════════════════════════════════════
//  AUTOVERSE — tools/manual_images.php
//  Manually upload images for any car
//  Visit: http://localhost/autoverse/tools/manual_images.php
// ══════════════════════════════════════════

require_once __DIR__ . '/../config/db.php';

$dir = __DIR__ . '/../images/cars/';
if (!is_dir($dir)) mkdir($dir, 0755, true);

$db   = getDB();
$msg  = '';
$type = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['carimg'])) {
    $carId    = (int)$_POST['car_id'];
    $file     = $_FILES['carimg'];
    $allowed  = array('image/jpeg','image/jpg','image/png','image/webp');
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $extMap   = array('jpeg'=>'jpg','jpg'=>'jpg','png'=>'png','webp'=>'webp');
    $safeExt  = isset($extMap[$ext]) ? $extMap[$ext] : 'jpg';

    if (!in_array($file['type'], $allowed)) {
        $msg  = 'Only JPG, PNG or WebP images allowed.';
        $type = 'error';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $msg  = 'File too large. Max 5MB.';
        $type = 'error';
    } else {
        // Get car brand/model for filename
        $stmt = $db->prepare('SELECT brand, model FROM cars WHERE id = :id');
        $stmt->execute(array(':id' => $carId));
        $car  = $stmt->fetch();
        $name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $car['brand'] . '-' . $car['model']));
        $name = preg_replace('/-+/', '-', $name);
        $filename  = $name . '.' . $safeExt;
        $savePath  = $dir . $filename;
        $localUrl  = 'http://localhost/autoverse/images/cars/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $savePath)) {
            $upd = $db->prepare('UPDATE cars SET img_url = :url WHERE id = :id');
            $upd->execute(array(':url' => $localUrl, ':id' => $carId));
            $msg  = 'Image uploaded and saved for car #' . $carId . ' (' . $car['brand'] . ' ' . $car['model'] . ')';
            $type = 'success';
        } else {
            $msg  = 'Upload failed. Check folder permissions.';
            $type = 'error';
        }
    }
}

// Get all cars
$cars = $db->query('SELECT id, brand, model, img_url FROM cars WHERE is_active=1 ORDER BY id')->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<title>AutoVerse Manual Image Upload</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: sans-serif; background: #0a0a0a; color: #f0f2f5; padding: 2rem; }
  h1 { font-size: 1.4rem; margin-bottom: .3rem; }
  .sub { color: #8a9ab5; font-size: .88rem; margin-bottom: 2rem; }
  .alert { border-radius: 10px; padding: .9rem 1.2rem; font-size: .88rem; margin-bottom: 1.5rem; }
  .success { background: rgba(0,201,125,.1); border: 1px solid rgba(0,201,125,.3); color: #00c97d; }
  .error   { background: rgba(230,0,18,.1);  border: 1px solid rgba(230,0,18,.3);  color: #e60012; }
  .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; }
  .card { background: #141820; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; }
  .card-img { width: 100%; height: 140px; object-fit: cover; background: #1e2330; display: block; }
  .card-img-placeholder { width: 100%; height: 140px; background: #1e2330; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #e60012; font-weight: 700; }
  .card-body { padding: 1rem; }
  .card-title { font-weight: 700; font-size: .95rem; margin-bottom: .8rem; }
  .card-id { font-size: .75rem; color: #5a6a85; margin-bottom: .3rem; }
  form { display: flex; flex-direction: column; gap: .6rem; }
  input[type=file] { background: #1e2330; border: 1px solid rgba(255,255,255,.1); color: #f0f2f5; border-radius: 8px; padding: .5rem .7rem; font-size: .82rem; width: 100%; }
  button { background: #e60012; color: #fff; border: none; border-radius: 8px; padding: .55rem; font-weight: 700; font-size: .85rem; cursor: pointer; }
  button:hover { background: #b3000e; }
  .has-img { border-top: 2px solid #00c97d; }
  .no-img  { border-top: 2px solid #e60012; }
  .tag { display: inline-block; font-size: .68rem; font-weight: 700; padding: .15rem .5rem; border-radius: 4px; margin-bottom: .5rem; }
  .tag-ok  { background: rgba(0,201,125,.15); color: #00c97d; }
  .tag-err { background: rgba(230,0,18,.15);  color: #e60012; }
  a.back { display: inline-block; margin-bottom: 1.5rem; color: #8a9ab5; text-decoration: none; font-size: .88rem; }
  a.back:hover { color: #e60012; }
</style>
</head>
<body>

<a class="back" href="download_images.php">← Back to Auto Downloader</a>
<h1>📷 Manual Car Image Upload</h1>
<p class="sub">Upload a JPG/PNG image from your computer for any car. Max 5MB per image.</p>

<?php if ($msg): ?>
<div class="alert <?= $type ?>"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="grid">
  <?php foreach ($cars as $car):
    $hasImg = !empty($car['img_url']);
  ?>
  <div class="card <?= $hasImg ? 'has-img' : 'no-img' ?>">

    <?php if ($hasImg): ?>
      <img class="card-img" src="<?= htmlspecialchars($car['img_url']) ?>"
           onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
           alt="<?= htmlspecialchars($car['brand']) ?>"/>
      <div class="card-img-placeholder" style="display:none"><?= htmlspecialchars($car['brand'][0]) ?></div>
    <?php else: ?>
      <div class="card-img-placeholder"><?= htmlspecialchars($car['brand'][0]) ?></div>
    <?php endif; ?>

    <div class="card-body">
      <div class="card-id">ID: <?= $car['id'] ?></div>
      <div class="card-title"><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></div>
      <span class="tag <?= $hasImg ? 'tag-ok' : 'tag-err' ?>">
        <?= $hasImg ? '✓ Has image' : '✗ No image' ?>
      </span>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="car_id" value="<?= $car['id'] ?>"/>
        <input type="file" name="carimg" accept="image/jpeg,image/png,image/webp" required/>
        <button type="submit">Upload Image</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div style="margin-top:2rem;text-align:center">
  <a href="../index.html" style="background:#e60012;color:#fff;border-radius:8px;padding:.7rem 2rem;text-decoration:none;font-weight:700;font-size:.9rem">→ View Website</a>
</div>

</body>
</html>
