<?php
// إدارة خدمات الفندق (مسبح، سبا، مطاعم...) — إضافة/تعديل/حذف
$pageTitle = 'إدارة الخدمات';
require_once __DIR__ . '/includes/admin_header.php';

$errors = [];
$editItem = null;

// حذف خدمة: يصل رقمها عبر رابط ?delete=.. مع رمز CSRF كجزء من الرابط نفسه
// (csrf في الرابط) للتأكد أن طلب الحذف صادر من داخل لوحة التحكم فعلًا
if (isset($_GET['delete']) && verifyCsrfToken($_GET['csrf'] ?? '')) {
    $pdo->prepare('DELETE FROM services WHERE id=?')->execute([(int)$_GET['delete']]);
    setFlash('success', 'تم حذف الخدمة.');
    header('Location: services.php');
    exit;
}

// عند الضغط على "تعديل": نجلب بيانات هذه الخدمة لعرضها جاهزة داخل نموذج الإضافة نفسه
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $editItem = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $title = clean($_POST['title'] ?? '');
    $desc  = clean($_POST['description'] ?? '');
    $icon  = clean($_POST['icon'] ?? '');
    $id    = (int)($_POST['id'] ?? 0);

    if (mb_strlen($title) < 2) $errors[] = 'عنوان الخدمة مطلوب.';

    if (empty($errors)) {
        // إن كان $id موجودًا (رقم حقيقي أكبر من صفر) فهذا تعديل، وإلا فهي إضافة جديدة
        if ($id) {
            $pdo->prepare('UPDATE services SET title=?, description=?, icon=? WHERE id=?')->execute([$title, $desc, $icon, $id]);
            setFlash('success', 'تم تحديث الخدمة.');
        } else {
            $pdo->prepare('INSERT INTO services (title, description, icon) VALUES (?, ?, ?)')->execute([$title, $desc, $icon]);
            setFlash('success', 'تمت إضافة الخدمة.');
        }
        header('Location: services.php');
        exit;
    }
}

$services = $pdo->query('SELECT * FROM services ORDER BY id DESC')->fetchAll();
?>

<div class="admin-panel">
  <h2><?php echo $editItem ? 'تعديل الخدمة' : 'إضافة خدمة جديدة'; ?></h2>
  <?php if (!empty($errors)): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>'.$e.'</li>'; ?></ul></div><?php endif; ?>
  <form method="post" class="grid-form">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <?php if ($editItem): ?><input type="hidden" name="id" value="<?php echo $editItem['id']; ?>"><?php endif; ?>
    <div class="form-group">
      <label>عنوان الخدمة</label>
      <input type="text" name="title" value="<?php echo htmlspecialchars($editItem['title'] ?? ''); ?>" required>
    </div>
    <div class="form-group">
      <label>أيقونة Font Awesome (مثال: fa-spa)</label>
      <input type="text" name="icon" value="<?php echo htmlspecialchars($editItem['icon'] ?? ''); ?>" placeholder="fa-spa">
    </div>
    <div class="form-group full">
      <label>الوصف</label>
      <textarea name="description" rows="3"><?php echo htmlspecialchars($editItem['description'] ?? ''); ?></textarea>
    </div>
    <button class="btn btn-primary"><?php echo $editItem ? 'حفظ' : 'إضافة'; ?></button>
    <?php if ($editItem): ?><a href="services.php" class="btn btn-outline">إلغاء</a><?php endif; ?>
  </form>
</div>

<div class="admin-panel">
  <h2>جميع الخدمات</h2>
  <table class="data-table">
    <thead><tr><th>الأيقونة</th><th>العنوان</th><th>الوصف</th><th>إجراءات</th></tr></thead>
    <tbody>
    <?php foreach ($services as $s): ?>
      <tr>
        <td><i class="fa-solid <?php echo htmlspecialchars($s['icon'] ?: 'fa-star'); ?>"></i></td>
        <td><?php echo htmlspecialchars($s['title']); ?></td>
        <td><?php echo htmlspecialchars(mb_substr($s['description'],0,60)); ?></td>
        <td class="actions-cell">
          <a href="services.php?edit=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline">تعديل</a>
          <a href="services.php?delete=<?php echo $s['id']; ?>&csrf=<?php echo generateCsrfToken(); ?>" class="btn btn-sm btn-danger" onclick="return confirm('حذف هذه الخدمة؟');">حذف</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
