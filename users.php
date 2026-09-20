<?php
// صفحة إدارة المستخدمين — تفعيل/حظر/ترقية/حذف أي حساب في الموقع
$pageTitle = 'إدارة المستخدمين';
require_once __DIR__ . '/includes/admin_header.php';

/* ---------- تنفيذ الإجراءات ---------- */
// كل الأزرار (تفعيل/حظر/حذف...) ترسل نموذجًا صغيرًا بحقلين مخفيين: action و id
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    // حماية إضافية: منع الأدمن من حظر أو حذف حسابه هو نفسه بالخطأ
    if ($id === (int)$_SESSION['user_id'] && in_array($action, ['block', 'delete'])) {
        setFlash('error', 'لا يمكنك تعديل حالة حسابك الخاص بهذه الطريقة.');
    } else {
        // سلسلة شروط تنفّذ الإجراء المطلوب فقط حسب قيمة $action القادمة من الزر المضغوط
        if ($action === 'activate') {
            $pdo->prepare('UPDATE users SET status="active" WHERE id=?')->execute([$id]);
            setFlash('success', 'تم تفعيل الحساب بنجاح.');
        } elseif ($action === 'block') {
            $pdo->prepare('UPDATE users SET status="blocked" WHERE id=?')->execute([$id]);
            setFlash('success', 'تم حظر الحساب.');
        } elseif ($action === 'unblock') {
            $pdo->prepare('UPDATE users SET status="active" WHERE id=?')->execute([$id]);
            setFlash('success', 'تم إلغاء حظر الحساب.');
        } elseif ($action === 'make_admin') {
            $pdo->prepare('UPDATE users SET user_type="admin" WHERE id=?')->execute([$id]);
            setFlash('success', 'تم ترقية المستخدم إلى أدمن.');
        } elseif ($action === 'delete') {
            $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
            setFlash('success', 'تم حذف المستخدم.');
        }
    }
    header('Location: users.php');
    exit;
}

$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
$statusLabels = ['pending' => 'بانتظار التفعيل', 'active' => 'مفعل', 'blocked' => 'محظور'];
?>

<div class="admin-panel">
  <div class="panel-header">
    <h2>جميع المستخدمين</h2>
  </div>
  <table class="data-table">
    <thead>
      <tr><th>الصورة</th><th>الاسم</th><th>البريد</th><th>النوع</th><th>الحالة</th><th>تاريخ التسجيل</th><th>إجراءات</th></tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><img class="avatar-sm" src="../assets/img/users/<?php echo htmlspecialchars($u['image']); ?>" onerror="this.src='../assets/img/users/default.png'" alt=""></td>
        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
        <td><?php echo htmlspecialchars($u['email']); ?></td>
        <td><?php echo $u['user_type'] === 'admin' ? 'أدمن' : 'مستخدم'; ?></td>
        <td><span class="status status-<?php echo $u['status']; ?>"><?php echo $statusLabels[$u['status']]; ?></span></td>
        <td><?php echo formatDate($u['created_at']); ?></td>
        <td class="actions-cell">
          <?php if ($u['status'] === 'pending'): ?>
            <form method="post"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="activate"><button class="btn btn-sm btn-success">تفعيل</button></form>
          <?php elseif ($u['status'] === 'active'): ?>
            <form method="post"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="block"><button class="btn btn-sm btn-warning">حظر</button></form>
          <?php else: ?>
            <form method="post"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="unblock"><button class="btn btn-sm btn-success">إلغاء الحظر</button></form>
          <?php endif; ?>

          <?php if ($u['user_type'] !== 'admin'): ?>
            <form method="post"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="make_admin"><button class="btn btn-sm btn-outline">ترقية لأدمن</button></form>
          <?php endif; ?>

          <form method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائيًا؟');">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="delete">
            <button class="btn btn-sm btn-danger">حذف</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
