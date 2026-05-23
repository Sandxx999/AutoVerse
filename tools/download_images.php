<?php
// ══════════════════════════════════════════
//  AUTOVERSE — tools/download_images.php
//  Downloads all car images to /images/cars/
//  Visit once: http://localhost/autoverse/tools/download_images.php
// ══════════════════════════════════════════

// Create folder if not exists
$dir = __DIR__ . '/../images/cars/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Car images list — id => [filename, search_url]
$cars = array(
    1  => array('tata-nexon-ev.jpg',       'https://www.motortrend.com/uploads/2022/11/2023-Tata-Nexon-EV-Max-001.jpg'),
    2  => array('hyundai-creta-nline.jpg',  'https://www.motortrend.com/uploads/2023/01/2023-Hyundai-Creta-N-Line-001.jpg'),
    3  => array('bmw-3series.jpg',          'https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/BMW_3_series_2019.jpg/640px-BMW_3_series_2019.jpg'),
    4  => array('maruti-brezza.jpg',        'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/2022_Maruti_Suzuki_Brezza_%28third_generation%29%2C_front_8.8.22.jpg/640px-2022_Maruti_Suzuki_Brezza_%28third_generation%29%2C_front_8.8.22.jpg'),
    5  => array('honda-city-hybrid.jpg',    'https://upload.wikimedia.org/wikipedia/commons/thumb/e/e3/Honda_City_e%3AHEV_%28GM6%29%2C_front_11.14.22.jpg/640px-Honda_City_e%3AHEV_%28GM6%29%2C_front_11.14.22.jpg'),
    6  => array('mahindra-thar.jpg',        'https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/2023_Mahindra_Thar_%28facelift%2C_red%29%2C_front_11.12.23.jpg/640px-2023_Mahindra_Thar_%28facelift%2C_red%29%2C_front_11.12.23.jpg'),
    7  => array('toyota-camry.jpg',         'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8b/2023_Toyota_Camry_%28AXVH71R%2C_facelift%29_Ascent_sedan_%282023-11-16%29_01.jpg/640px-2023_Toyota_Camry_%28AXVH71R%2C_facelift%29_Ascent_sedan_%282023-11-16%29_01.jpg'),
    8  => array('audi-a4.jpg',             'https://upload.wikimedia.org/wikipedia/commons/thumb/1/16/2020_Audi_A4_S_Line_35_TFSI_in_Chronos_Grey%2C_front_8.20.20.jpg/640px-2020_Audi_A4_S_Line_35_TFSI_in_Chronos_Grey%2C_front_8.20.20.jpg'),
    9  => array('kia-ev6.jpg',             'https://upload.wikimedia.org/wikipedia/commons/thumb/8/8e/Kia_EV6_GT_at_2022_NAIAS.jpg/640px-Kia_EV6_GT_at_2022_NAIAS.jpg'),
    10 => array('maruti-swift.jpg',        'https://upload.wikimedia.org/wikipedia/commons/thumb/5/57/2024_Suzuki_Swift_%28AZG334%29_sedan%2C_front_10.13.24.jpg/640px-2024_Suzuki_Swift_%28AZG334%29_sedan%2C_front_10.13.24.jpg'),
    11 => array('mercedes-cclass.jpg',     'https://upload.wikimedia.org/wikipedia/commons/thumb/9/92/2022_Mercedes-Benz_C200_AMG_Line_%28W206%29_sedan%2C_front_7.31.22.jpg/640px-2022_Mercedes-Benz_C200_AMG_Line_%28W206%29_sedan%2C_front_7.31.22.jpg'),
    12 => array('tata-punch-ev.jpg',       'https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Tata_Punch.ev_Empowered%2B_%282023%29%2C_front_10.14.23.jpg/640px-Tata_Punch.ev_Empowered%2B_%282023%29%2C_front_10.14.23.jpg'),
);

$results = array();

foreach ($cars as $id => $info) {
    $filename  = $info[0];
    $sourceUrl = $info[1];
    $savePath  = $dir . $filename;
    $localUrl  = 'http://localhost/autoverse/images/cars/' . $filename;

    // Skip if already downloaded
    if (file_exists($savePath) && filesize($savePath) > 5000) {
        $results[$id] = array(
            'status'   => 'exists',
            'file'     => $filename,
            'localUrl' => $localUrl,
            'size'     => round(filesize($savePath) / 1024) . ' KB',
        );
        continue;
    }

    // Download using file_get_contents with user agent
    $context = stream_context_create(array(
        'http' => array(
            'timeout'         => 15,
            'follow_location' => true,
            'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ),
        'ssl' => array(
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ),
    ));

    $imageData = @file_get_contents($sourceUrl, false, $context);

    if ($imageData && strlen($imageData) > 5000) {
        file_put_contents($savePath, $imageData);
        $results[$id] = array(
            'status'   => 'downloaded',
            'file'     => $filename,
            'localUrl' => $localUrl,
            'size'     => round(strlen($imageData) / 1024) . ' KB',
        );
    } else {
        $results[$id] = array(
            'status'   => 'failed',
            'file'     => $filename,
            'localUrl' => '',
            'size'     => '0 KB',
        );
    }
}

// Auto-update database with local URLs
require_once __DIR__ . '/../config/db.php';
$db = getDB();
$updated = 0;
foreach ($results as $id => $r) {
    if ($r['status'] !== 'failed' && $r['localUrl']) {
        $stmt = $db->prepare('UPDATE cars SET img_url = :url WHERE id = :id');
        $stmt->execute(array(':url' => $r['localUrl'], ':id' => $id));
        $updated++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>AutoVerse Image Downloader</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: sans-serif; background: #0a0a0a; color: #f0f2f5; padding: 2rem; }
  h1 { font-size: 1.5rem; margin-bottom: .3rem; }
  .sub { color: #8a9ab5; font-size: .9rem; margin-bottom: 2rem; }
  .summary { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
  .stat { background: #141820; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1rem 1.5rem; text-align: center; min-width: 130px; }
  .stat-num { font-size: 2rem; font-weight: 700; }
  .stat-label { font-size: .78rem; color: #8a9ab5; margin-top: .2rem; }
  .green { color: #00c97d; }
  .red { color: #e60012; }
  .blue { color: #5b8dd9; }
  table { width: 100%; border-collapse: collapse; font-size: .88rem; }
  th { background: #141820; color: #8a9ab5; padding: .7rem 1rem; text-align: left; border: 1px solid rgba(255,255,255,0.08); font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; }
  td { padding: .7rem 1rem; border: 1px solid rgba(255,255,255,0.06); color: #8a9ab5; vertical-align: middle; }
  .badge { display: inline-block; padding: .2rem .7rem; border-radius: 4px; font-size: .72rem; font-weight: 700; }
  .badge-ok  { background: rgba(0,201,125,.15); color: #00c97d; }
  .badge-ex  { background: rgba(91,141,217,.15); color: #5b8dd9; }
  .badge-err { background: rgba(230,0,18,.15); color: #e60012; }
  .preview { width: 100px; height: 60px; object-fit: cover; border-radius: 6px; background: #1e2330; display: block; }
  .warn { background: rgba(245,166,35,.1); border: 1px solid rgba(245,166,35,.3); border-radius: 10px; padding: 1rem 1.2rem; font-size: .88rem; color: #f5a623; margin-top: 1.5rem; }
  a.btn { display: inline-block; margin-top: 1.5rem; background: #e60012; color: #fff; border-radius: 8px; padding: .7rem 1.8rem; text-decoration: none; font-weight: 700; font-size: .9rem; }
  a.btn2 { display: inline-block; margin-top: 1.5rem; margin-left: 1rem; background: #141820; border: 1px solid rgba(255,255,255,.1); color: #f0f2f5; border-radius: 8px; padding: .7rem 1.8rem; text-decoration: none; font-weight: 700; font-size: .9rem; }
</style>
</head>
<body>

<h1>⚡ AutoVerse Image Downloader</h1>
<p class="sub">Downloads car images to your local server and updates the database automatically</p>

<?php
$downloaded = count(array_filter($results, function($r) { return $r['status'] === 'downloaded'; }));
$exists     = count(array_filter($results, function($r) { return $r['status'] === 'exists'; }));
$failed     = count(array_filter($results, function($r) { return $r['status'] === 'failed'; }));
?>

<div class="summary">
  <div class="stat"><div class="stat-num green"><?= $downloaded ?></div><div class="stat-label">Downloaded</div></div>
  <div class="stat"><div class="stat-num blue"><?= $exists ?></div><div class="stat-label">Already exist</div></div>
  <div class="stat"><div class="stat-num red"><?= $failed ?></div><div class="stat-label">Failed</div></div>
  <div class="stat"><div class="stat-num green"><?= $updated ?></div><div class="stat-label">DB rows updated</div></div>
</div>

<table>
  <thead>
    <tr><th>#</th><th>Preview</th><th>File</th><th>Size</th><th>Status</th></tr>
  </thead>
  <tbody>
    <?php foreach ($results as $id => $r): ?>
    <tr>
      <td><?= $id ?></td>
      <td>
        <?php if ($r['status'] !== 'failed'): ?>
          <img class="preview" src="<?= htmlspecialchars($r['localUrl']) ?>" alt="car"
               onerror="this.style.background='#e60012';this.style.opacity='.3'"/>
        <?php else: ?>
          <div class="preview" style="background:#e60012;opacity:.2;display:flex;align-items:center;justify-content:center;font-size:.7rem;color:#fff">fail</div>
        <?php endif; ?>
      </td>
      <td style="color:#f0f2f5;font-family:monospace;font-size:.8rem"><?= htmlspecialchars($r['file']) ?></td>
      <td><?= $r['size'] ?></td>
      <td>
        <?php if ($r['status'] === 'downloaded'): ?>
          <span class="badge badge-ok">Downloaded</span>
        <?php elseif ($r['status'] === 'exists'): ?>
          <span class="badge badge-ex">Already exists</span>
        <?php else: ?>
          <span class="badge badge-err">Failed — manual needed</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if ($failed > 0): ?>
<div class="warn">
  <strong><?= $failed ?> image(s) failed to download.</strong> For each failed image, scroll to the manual fix section below.
</div>
<?php endif; ?>

<?php if ($failed === 0): ?>
<div style="background:rgba(0,201,125,.08);border:1px solid rgba(0,201,125,.25);border-radius:10px;padding:1rem 1.2rem;margin-top:1.5rem;font-size:.9rem;color:#00c97d">
  ✅ All images downloaded and database updated successfully!
</div>
<?php endif; ?>

<a class="btn" href="../index.html">→ View Website</a>
<a class="btn2" href="manual_images.php">Manual Fix for Failed Images</a>

<?php if ($failed > 0): ?>
<h2 style="margin-top:2rem;font-size:1.1rem">Manual Fix for Failed Images</h2>
<p style="color:#8a9ab5;margin:.5rem 0 1rem;font-size:.88rem">For each failed image: find any JPG image URL for that car, then run the SQL below in phpMyAdmin.</p>
<table style="margin-top:1rem">
  <thead><tr><th>Car ID</th><th>File</th><th>SQL to run in phpMyAdmin</th></tr></thead>
  <tbody>
    <?php foreach ($results as $id => $r): if ($r['status'] === 'failed'): ?>
    <tr>
      <td><?= $id ?></td>
      <td style="font-family:monospace;font-size:.8rem"><?= $r['file'] ?></td>
      <td style="font-family:monospace;font-size:.75rem;color:#f5a623">UPDATE cars SET img_url = 'YOUR_IMAGE_URL_HERE' WHERE id = <?= $id ?>;</td>
    </tr>
    <?php endif; endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

</body>
</html>
