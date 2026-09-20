<?php
// إدارة الغرف — إضافة/تعديل/حذف غرفة، مع رفع صورتها وربطها بنوعها
$pageTitle = 'إدارة الغرف';
require_once __DIR__ . '/includes/admin_header.php';

$errors = [];
$editRoom = null;

/* ---------- حذف ---------- */
if (isset($_GET['delete']) && verifyCsrfToken($_GET['csrf'] ?? '')) {
    $pdo->prepare('DELETE FROM rooms WHERE id=?')->execute([(int)$_GET['delete']]);
    setFlash('success', 'تم حذف الغرفة.');
    header('Location: rooms.php');
    exit;
}

/* ---------- تحميل بيانات التعديل ---------- */
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $editRoom = $stmt->fetch();
}

/* ---------- إضافة / تعديل ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $title = clean($_POST['title'] ?? '');
    $desc  = clean($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $capacity = (int)($_POST['capacity'] ?? 1);
    $typeId = (int)($_POST['room_type_id'] ?? 0);
    $status = $_POST['status'] === 'unavailable' ? 'unavailable' : 'available';
    $roomId = (int)($_POST['id'] ?? 0);

    if (mb_strlen($title) < 3) $errors[] = 'عنوان الغرفة قصير جدًا.';
    if ($price <= 0) $errors[] = 'يرجى إدخال سعر صحيح.';
    if ($capacity < 1) $errors[] = 'السعة يجب أن تكون على الأقل شخص واحد.';
    if (!$typeId) $errors[] = 'يرجى اختيار نوع الغرفة.';

    // existing_image: حقل مخفي يحمل اسم الصورة الحالية أثناء التعديل،
    // حتى لا تُفقد الصورة القديمة إن لم يرفع الأدمن صورة جديدة بديلة
    $imageName = $_POST['existing_image'] ?? 'room-default.jpg';
    if (empty($errors) && !empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], __DIR__ . '/../assets/img/rooms', 'room');
        if ($upload['success']) $imageName = $upload['filename'];
        else $errors[] = $upload['message'];
    }

    if (empty($errors)) {
        // $roomId موجود = تعديل غرفة قائمة، غير موجود = إضافة غرفة جديدة
        if ($roomId) {
            $stmt = $pdo->prepare('UPDATE rooms SET room_type_id=?, title=?, description=?, price=?, capacity=?, image=?, status=? WHERE id=?');
            $stmt->execute([$typeId, $title, $desc, $price, $capacity, $imageName, $status, $roomId]);
            setFlash('success', 'تم تحديث الغرفة بنجاح.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO rooms (room_type_id, title, description, price, capacity, image, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$typeId, $title, $desc, $price, $capacity, $imageName, $status]);
            setFlash('success', 'تمت إضافة الغرفة بنجاح.');
        }
        header('Location: rooms.php');
        exit;
    }
}

$types = $pdo->query('SELECT * FROM room_types')->fetchAll();
$rooms = $pdo->query('SELECT rooms.*, room_types.name AS type_name FROM rooms JOIN room_types ON room_types.id = rooms.room_type_id ORDER BY rooms.created_at DESC')->fetchAll();
?>

<div class="admin-panel">
  <h2><?php echo $editRoom ? 'تعديل الغرفة' : 'إضافة غرفة جديدة'; ?></h2>
  <?php if (!empty($errors)): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>'.$e.'</li>'; ?></ul></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="grid-form">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <?php if ($editRoom): ?>
      <input type="hidden" name="id" value="<?php echo $editRoom['id']; ?>">
      <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($editRoom['image']); ?>">
    <?php endif; ?>

    <div class="form-group">
      <label>نوع الغرفة</label>
      <select name="room_type_id" required>
        <option value="">اختر النوع</option>
        <?php foreach ($types as $t): ?>
          <option value="<?php echo $t['id']; ?>" <?php echo ($editRoom && $editRoom['room_type_id'] == $t['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label>عنوان الغرفة</label>
      <input type="text" name="title" value="<?php echo htmlspecialchars($editRoom['title'] ?? ''); ?>" required>
    </div>
    <div class="form-group full">
      <label>الوصف</label>
      <textarea name="description" rows="3"><?php echo htmlspecialchars($editRoom['description'] ?? ''); ?></textarea>
    </div>
    <div class="form-group">
      <label>السعر لليلة ($)</label>
      <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($editRoom['price'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
      <label>السعة (عدد الأشخاص)</label>
      <input type="number" name="capacity" value="<?php echo htmlspecialchars($editRoom['capacity'] ?? 2); ?>" required>
    </div>
    <div class="form-group">
      <label>الحالة</label>
      <select name="status">
        <option value="available" <?php echo (($editRoom['status'] ?? '') === 'available') ? 'selected' : ''; ?>>متاحة</option>
        <option value="unavailable" <?php echo (($editRoom['status'] ?? '') === 'unavailable') ? 'selected' : ''; ?>>غير متاحة</option>
      </select>
    </div>
    <div class="form-group">
      <label>صورة الغرفة</label>
      <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
    </div>

    <button class="btn btn-primary"><?php echo $editRoom ? 'حفظ التعديلات' : 'إضافة الغرفة'; ?></button>
    <?php if ($editRoom): ?><a href="rooms.php" class="btn btn-outline">إلغاء</a><?php endif; ?>
  </form>
</div>

<div class="admin-panel">
  <h2>جميع الغرف</h2>
  <table class="data-table">
    <thead><tr><th>الصورة</th><th>العنوان</th><th>النوع</th><th>السعر</th><th>السعة</th><th>الحالة</th><th>إجراءات</th></tr></thead>
    <tbody>
    <?php foreach ($rooms as $r): ?>
      <tr>
        <td><img class="avatar-sm" src="../assets/img/rooms/<?php echo htmlspecialchars($r['image']); ?>" alt=""></td>
        <td><?php echo htmlspecialchars($r['title']); ?></td>
        <td><?php echo htmlspecialchars($r['type_name']); ?></td>
        <td><?php echo number_format($r['price'], 2); ?> $</td>
        <td><?php echo (int)$r['capacity']; ?></td>
        <td><span class="status status-<?php echo $r['status'] === 'available' ? 'confirmed' : 'cancelled'; ?>"><?php echo $r['status'] === 'available' ? 'متاحة' : 'غير متاحة'; ?></span></td>
        <td class="actions-cell">
          <a href="rooms.php?edit=<?php echo $r['id']; ?>" class="btn btn-sm btn-outline">تعديل</a>
          <a href="rooms.php?delete=<?php echo $r['id']; ?>&csrf=<?php echo generateCsrfToken(); ?>" class="btn btn-sm btn-danger" onclick="return confirm('حذف هذه الغرفة؟');">حذف</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
