<?php
// إدارة أنواع الغرف (تصنيفات مثل: ديلوكس، جناح ملكي، عائلية...) — إضافة/تعديل/حذف كامل (CRUD)
$pageTitle = 'أنواع الغرف';
require_once __DIR__ . '/includes/admin_header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    // $action تحدد أي عملية سنُنفّذ: add (إضافة) أو edit (تعديل) أو delete (حذف)
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $name = clean($_POST['name'] ?? '');
        $desc = clean($_POST['description'] ?? '');
        if (mb_strlen($name) < 2) $errors[] = 'اسم النوع مطلوب.';

        if (empty($errors)) {
            if ($action === 'add') {
                $pdo->prepare('INSERT INTO room_types (name, description) VALUES (?, ?)')->execute([$name, $desc]);
                setFlash('success', 'تمت إضافة نوع الغرفة.');
            } else {
                $id = (int)$_POST['id'];
                $pdo->prepare('UPDATE room_types SET name=?, description=? WHERE id=?')->execute([$name, $desc, $id]);
                setFlash('success', 'تم تحديث نوع الغرفة.');
            }
            header('Location: room_types.php');
            exit;
        }
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM room_types WHERE id=?')->execute([(int)$_POST['id']]);
        setFlash('success', 'تم حذف النوع.');
        header('Location: room_types.php');
        exit;
    }
}

$types = $pdo->query('SELECT * FROM room_types ORDER BY id DESC')->fetchAll();
?>

<div class="admin-panel">
  <div class="panel-header">
    <h2>إضافة نوع غرفة جديد</h2>
  </div>
  <?php if (!empty($errors)): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>'.$e.'</li>'; ?></ul></div><?php endif; ?>
  <form method="post" class="inline-form">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <input type="hidden" name="action" value="add">
    <div class="form-group"><label>اسم النوع</label><input type="text" name="name" required></div>
    <div class="form-group"><label>الوصف</label><input type="text" name="description"></div>
    <button class="btn btn-primary">إضافة</button>
  </form>
</div>

<div class="admin-panel">
  <h2>الأنواع الحالية</h2>
  <table class="data-table">
    <thead><tr><th>الاسم</th><th>الوصف</th><th>إجراءات</th></tr></thead>
    <tbody>
    <?php foreach ($types as $t): ?>
      <tr>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
          <td><input type="text" name="name" value="<?php echo htmlspecialchars($t['name']); ?>" class="table-input"></td>
          <td><input type="text" name="description" value="<?php echo htmlspecialchars($t['description']); ?>" class="table-input"></td>
          <td class="actions-cell">
            <button class="btn btn-sm btn-outline">حفظ</button>
        </form>
        <form method="post" onsubmit="return confirm('حذف هذا النوع؟ سيؤثر على الغرف المرتبطة به.');">
          <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
          <button class="btn btn-sm btn-danger">حذف</button>
        </form>
          </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
