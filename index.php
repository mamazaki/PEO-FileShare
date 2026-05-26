<?php
require_once 'config.php';

$stat_file = 'download_stats.json';
$stats = [];
if (file_exists($stat_file)) {
    $stats = json_decode(file_get_contents($stat_file), true) ?? [];
}

$cat_file = 'categories.json';
$categories = [];
if (file_exists($cat_file)) {
    $categories = json_decode(file_get_contents($cat_file), true) ?? [];
}

$current_cat = $_GET['cat'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบดาวน์โหลดเอกสารและจัดเก็บสถิติ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="mb-4 text-center">
        <a href="index.php" class="btn <?php echo empty($current_cat) ? 'btn-primary' : 'btn-outline-primary'; ?> me-2 mb-2">ทั้งหมด</a>
        <?php foreach ($categories as $cat_id => $cat_name): ?>
            <a href="index.php?cat=<?php echo urlencode($cat_id); ?>" class="btn <?php echo $current_cat === $cat_id ? 'btn-primary' : 'btn-outline-primary'; ?> me-2 mb-2">
                <?php echo htmlspecialchars($cat_name); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0 py-1">รายการเอกสาร: <span class="text-warning"><?php echo !empty($current_cat) && isset($categories[$current_cat]) ? htmlspecialchars($categories[$current_cat]) : 'ทั้งหมด'; ?></span></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 10%;">ลำดับ</th>
                            <th style="width: 40%;">ชื่อไฟล์ / เอกสาร</th>
                            <th style="width: 20%;">หมวดหมู่</th>
                            <th style="width: 15%;" class="text-center">ดาวน์โหลด (ครั้ง)</th>
                            <th style="width: 15%;" class="text-center">ดาวน์โหลด</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1; $has_items = false;
                        foreach ($files as $id => $file): 
                            if (!empty($current_cat) && (!isset($file['category']) || $file['category'] !== $current_cat)) continue;
                            $has_items = true;
                            $click_count = $stats[$id] ?? 0;
                            $file_cat_name = $categories[$file['category'] ?? ''] ?? 'ไม่มีหมวดหมู่';
                        ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><strong><?php echo htmlspecialchars($file['name']); ?></strong></td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($file_cat_name); ?></span></td>
                            <td class="text-center"><span class="badge bg-secondary fs-6"><?php echo number_format($click_count); ?></span></td>
                            <td class="text-center"><a href="download.php?id=<?php echo urlencode($id); ?>" target="_blank" class="btn btn-sm btn-success px-3">ดาวน์โหลด</a></td>
                        </tr>
                        <?php endforeach; if (!$has_items): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">ไม่มีเอกสารในหมวดหมู่นี้ค่ะ 📂</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
