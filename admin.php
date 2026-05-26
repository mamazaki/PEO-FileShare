<?php
session_start();

define('UPLOAD_DIR', 'uploads/');
$cat_file = 'categories.json';

function loadEnv($filePath) {
    if (!file_exists($filePath)) return;
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}
loadEnv(__DIR__ . '/.env');

$all_users = [];
$admin_user = $_ENV['ADMIN_USER'] ?? 'admin';
$admin_pass = $_ENV['ADMIN_PASS'] ?? 'tuk9881234!';
$all_users[$admin_user] = $admin_pass;

if (!empty($_ENV['USER_LIST'])) {
    $pairs = explode(',', $_ENV['USER_LIST']);
    foreach ($pairs as $pair) {
        if (strpos($pair, ':') !== false) {
            list($u, $p) = explode(':', $pair, 2);
            $all_users[trim($u)] = trim($p);
        }
    }
}

if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: admin.php");
    exit;
}

if (isset($_POST['login'])) {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if (array_key_exists($u, $all_users) && $all_users[$u] === $p) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $u;
        $_SESSION['is_admin'] = ($u === $admin_user);
        header("Location: admin.php");
        exit;
    }
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true):
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Login</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-secondary d-flex align-items-center" style="height:100vh;"><div class="container"><div class="row justify-content-center"><div class="col-md-4"><div class="card shadow"><div class="card-header bg-dark text-white text-center"><h5>File Management Login</h5></div><div class="card-body"><form method="post"><div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required autocomplete="off"></div><div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div><button type="submit" name="login" class="btn btn-primary w-100">เข้าสู่ระบบ</button></form></div></div></div></div></div></body></html>
<?php exit; endif;

require_once 'config.php';
$categories = file_exists($cat_file) ? json_decode(file_get_contents($cat_file), true) ?? [] : [];
$current_user = $_SESSION['username'];
$is_admin = $_SESSION['is_admin'];

if (isset($_POST['save_categories']) && $is_admin) {
    $cat_ids = $_POST['cat_id'] ?? [];
    $cat_names = $_POST['cat_name'] ?? [];
    $new_cats = [];
    foreach ($cat_ids as $k => $id) {
        if (!empty(trim($id)) && !empty(trim($cat_names[$k]))) $new_cats[trim($id)] = trim($cat_names[$k]);
    }
    file_put_contents($cat_file, json_encode($new_cats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $categories = $new_cats;
    $message = "บันทึกข้อมูลหมวดหมู่เรียบร้อยแล้วค่ะ!";
}

if (isset($_POST['save_config'])) {
    $posted_ids = $_POST['file_id'] ?? [];
    $posted_names = $_POST['file_name'] ?? [];
    $posted_urls = $_POST['file_url'] ?? [];
    $posted_cats = $_POST['file_cat'] ?? [];
    $uploaded_files = $_FILES['file_upload'] ?? [];
    $new_files = []; $index = 1;
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $base_url = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/';
    
    foreach ($files as $old_id => $old_file) {
        if (!$is_admin && (!isset($old_file['owner']) || $old_file['owner'] !== $current_user)) {
            $new_files["file_" . $index] = $old_file; $index++;
        }
    }
    foreach ($posted_names as $key => $name) {
        $name = trim($name); $final_url = trim($posted_urls[$key] ?? ''); $chosen_cat = trim($posted_cats[$key] ?? ''); $file_key_id = $posted_ids[$key] ?? '';
        if (!empty($file_key_id) && isset($files[$file_key_id])) {
            if (!$is_admin && $files[$file_key_id]['owner'] !== $current_user) continue;
            $file_owner = $files[$file_key_id]['owner'] ?? $current_user;
        } else { $file_owner = $current_user; }
        if (!empty($uploaded_files['name'][$key]) && $uploaded_files['error'][$key] === UPLOAD_ERR_OK) {
            $ext = pathinfo($uploaded_files['name'][$key], PATHINFO_EXTENSION);
            $new_filename = "doc_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            if (move_uploaded_file($uploaded_files['tmp_name'][$key], UPLOAD_DIR . $new_filename)) $final_url = $base_url . UPLOAD_DIR . $new_filename;
        }
        if (!empty($name) && !empty($final_url)) {
            $new_files["file_" . $index] = [ "name" => $name, "url" => $final_url, "category" => $chosen_cat, "owner" => $file_owner ]; $index++;
        }
    }
    file_put_contents('config.php', "<?php\n\$files = " . var_export($new_files, true) . ";\n");
    $files = $new_files; $message = "บันทึกข้อมูลเรียบร้อยแล้วค่ะ! 🎉";
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แผงควบคุมระบบจัดการดาวน์โหลด</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4"><div class="container"><span class="navbar-brand mb-0 h1">ระบบหลังบ้าน [ผู้ใช้: <span class="text-warning"><?php echo htmlspecialchars($current_user); ?></span>]</span><a href="admin.php?action=logout" class="btn btn-outline-danger btn-sm">ออกจากระบบ</a></div></nav>
<div class="container mb-5">
    <?php if (isset($message)): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item"><button class="nav-link active fw-bold" id="files-tab" data-bs-toggle="tab" data-bs-target="#files-pane" type="button">📁 จัดการไฟล์ดาวน์โหลด</button></li>
        <?php if ($is_admin): ?><li class="nav-item"><button class="nav-link fw-bold" id="cats-tab" data-bs-toggle="tab" data-bs-target="#cats-pane" type="button">📂 จัดการหมวดหมู่ (Admin)</button></li><?php endif; ?>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="files-pane">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center"><h5 class="mb-0">รายการไฟล์เอกสาร</h5><button type="button" class="btn btn-light btn-sm" onclick="addNewFileRow()">＋ เพิ่มแถวไฟล์ใหม่</button></div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <table class="table table-bordered align-middle" id="fileTable">
                            <thead class="table-secondary"><tr><th>ชื่อเอกสาร</th><th>หมวดหมู่</th><th>อัปโหลดไฟล์</th><th>หรือระบุ URL ตรง</th><th>ผู้รับผิดชอบ</th><th>ลบ</th></tr></thead>
                            <tbody>
                                <?php foreach ($files as $id => $file): $file_owner = $file['owner'] ?? 'admin'; $can_edit = ($is_admin || $file_owner === $current_user); ?>
                                <tr class="<?php echo !$can_edit ? 'table-light text-muted' : ''; ?>"><input type="hidden" name="file_id[]" value="<?php echo $id; ?>">
                                    <td><input type="text" name="file_name[]" class="form-control" value="<?php echo htmlspecialchars($file['name']); ?>" <?php echo !$can_edit ? 'readonly' : 'required'; ?>></td>
                                    <td><select name="file_cat[]" class="form-select" <?php echo !$can_edit ? 'disabled' : ''; ?>><option value="">-- ไม่ระบุ --</option><?php foreach ($categories as $c_id => $c_name): ?><option value="<?php echo $c_id; ?>" <?php echo ($file['category'] ?? '') === $c_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($c_name); ?></option><?php endforeach; ?></select></td>
                                    <td><input type="file" name="file_upload[]" class="form-control" <?php echo !$can_edit ? 'disabled' : ''; ?>></td>
                                    <td><input type="text" name="file_url[]" class="form-control" value="<?php echo htmlspecialchars($file['url']); ?>" <?php echo !$can_edit ? 'readonly' : ''; ?>></td>
                                    <td><span class="badge <?php echo $file_owner === 'admin' ? 'bg-dark' : 'bg-primary'; ?>"><?php echo htmlspecialchars($file_owner); ?></span></td>
                                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.parentNode.remove()" <?php echo !$can_edit ? 'disabled' : ''; ?>>ลบ</button></td>
                                </tr><?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="text-end"><button type="submit" name="save_config" class="btn btn-success">💾 บันทึกรายการไฟล์ทั้งหมด</button></div>
                    </form>
                </div>
            </div>
        </div>
        <?php if ($is_admin): ?>
        <div class="tab-pane fade" id="cats-pane">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center"><h5 class="mb-0 fw-bold">ตั้งค่าหมวดหมู่ระบบ</h5><button type="button" class="btn btn-dark btn-sm" onclick="addNewCatRow()">＋ เพิ่มหมวดหมู่ใหม่</button></div>
                <div class="card-body">
                    <form method="post">
                        <table class="table table-bordered align-middle" id="catTable">
                            <thead class="table-secondary"><tr><th>ID หมวดหมู่</th><th>ชื่อหมวดหมู่ที่แสดงผล</th><th>ลบ</th></tr></thead>
                            <tbody>
                                <?php foreach ($categories as $c_id => $c_name): ?><tr><td><input type="text" name="cat_id[]" class="form-control" value="<?php echo htmlspecialchars($c_id); ?>" readonly></td><td><input type="text" name="cat_name[]" class="form-control" value="<?php echo htmlspecialchars($c_name); ?>" required></td><td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.parentNode.remove()">ลบ</button></td></tr><?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="text-end"><button type="submit" name="save_categories" class="btn btn-success">💾 บันทึกโครงสร้างหมวดหมู่</button></div>
                    </form>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const catOptions = `<option value="">-- ไม่ระบุ --</option><?php foreach ($categories as $c_id => $c_name): ?><option value="<?php echo $c_id; ?>"><?php echo htmlspecialchars($c_name); ?></option><?php endforeach; ?>`;
function addNewFileRow() {
    const table = document.getElementById('fileTable').getElementsByTagName('tbody')[0]; const row = table.insertRow();
    row.innerHTML = `<input type="hidden" name="file_id[]" value=""><td><input type="text" name="file_name[]" class="form-control" placeholder="ชื่อเอกสาร..." required></td><td><select name="file_cat[]" class="form-select">\${catOptions}</select></td><td><input type="file" name="file_upload[]" class="form-control"></td><td><input type="text" name="file_url[]" class="form-control" placeholder="https://..."></td><td><span class="badge bg-primary"><?php echo htmlspecialchars($current_user); ?></span></td><td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.parentNode.remove()">ลบ</button></td>`;
}
function addNewCatRow() {
    const table = document.getElementById('catTable').getElementsByTagName('tbody')[0]; const row = table.insertRow(); const nextId = 'cat_' + (table.rows.length);
    row.innerHTML = `<td><input type="text" name="cat_id[]" class="form-control" value="\${nextId}" required></td><td><input type="text" name="cat_name[]" class="form-control" placeholder="ชื่อหมวดหมู่..." required></td><td class="text-center"><button type="button" class="btn btn-danger btn-sm" onclick="this.parentNode.parentNode.remove()">ลบ</button></td>`;
}
</script>
</body>
</html>
