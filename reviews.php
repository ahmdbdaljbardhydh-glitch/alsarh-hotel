<?php
// إدارة تقييمات الزوار — الأدمن وحده من يوافق على ظهور تقييم في الصفحة الرئيسية أو يحذفه
$pageTitle = 'إدارة التقييمات';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    if ($action === 'approve') {
        // الموافقة تغيّر status إلى "approved" فتظهر تلقائيًا في index.php للزوار
        $pdo->prepare('UPDATE reviews SET status="approved" WHERE id=?')->execute([$id]);
        setFlash('success', 'تمت الموافقة على التقييم.');
    } elseif ($action === 'delete') {
        $pdo->prepare('DELETE FROM reviews WHERE id=?')->execute([$id]);
        setFlash('success', 'تم حذف التقييم.');
    }
    header('Location: reviews.php');
    exit;
}

// جلب كل التقييمات (المعتمدة وغير المعتمدة معًا) مع اسم صاحب كل تقييم
$reviews = $pdo->query('SELECT reviews.*, users.full_name FROM reviews JOIN users ON users.id = reviews.user_id ORDER BY reviews.created_at DESC')->fetchAll();
?>

<div class="admin-panel">
  <h2>جميع التقييمات</h2>
  <table class="data-table">
    <thead><tr><th>المستخدم</th><th>التقييم</th><th>التعليق</th><th>الحالة</th><th>إجراءات</th></tr></thead>
    <tbody>
    <?php foreach ($reviews as $r): ?>
      <tr>
        <td><?php echo htmlspecialchars($r['full_name']); ?></td>
        <td><?php echo str_repeat('⭐', (int)$r['rating']); ?></td>
        <td><?php echo htmlspecialchars(mb_substr($r['comment'],0,80)); ?></td>
        <td><span class="status status-<?php echo $r['status']==='approved'?'confirmed':'pending'; ?>"><?php echo $r['status']==='approved'?'معتمد':'قيد المراجعة'; ?></span></td>
        <td class="actions-cell">
          <?php if ($r['status'] !== 'approved'): ?>
            <form method="post"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $r['id']; ?>"><input type="hidden" name="action" value="approve"><button class="btn btn-sm btn-success">موافقة</button></form>
          <?php endif; ?>
          <form method="post" onsubmit="return confirm('حذف هذا التقييم؟');"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $r['id']; ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-danger">حذف</button></form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$reviews): ?><tr><td colspan="5">لا توجد تقييمات.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
