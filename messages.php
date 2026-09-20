<?php
// عرض رسائل نموذج "تواصل معنا" — تعليمها كمقروءة أو حذفها
$pageTitle = 'رسائل التواصل';
require_once __DIR__ . '/includes/admin_header.php';

// تعليم رسالة كمقروءة (is_read=1) حتى لا تظهر في عداد "غير مقروءة" بلوحة القيادة
if (isset($_GET['read'])) {
    $pdo->prepare('UPDATE contact_messages SET is_read=1 WHERE id=?')->execute([(int)$_GET['read']]);
    header('Location: messages.php');
    exit;
}
// حذف رسالة نهائيًا من قاعدة البيانات
if (isset($_GET['delete']) && verifyCsrfToken($_GET['csrf'] ?? '')) {
    $pdo->prepare('DELETE FROM contact_messages WHERE id=?')->execute([(int)$_GET['delete']]);
    setFlash('success', 'تم حذف الرسالة.');
    header('Location: messages.php');
    exit;
}

$messages = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC')->fetchAll();
?>

<div class="admin-panel">
  <h2>رسائل الزوار</h2>
  <div class="messages-list">
    <?php foreach ($messages as $m): ?>
      <div class="message-item <?php echo $m['is_read'] ? '' : 'unread'; ?>">
        <div class="message-head">
          <strong><?php echo htmlspecialchars($m['name']); ?></strong>
          <span><?php echo htmlspecialchars($m['email']); ?></span>
          <span class="date"><?php echo formatDate($m['created_at']); ?></span>
        </div>
        <p class="subject"><?php echo htmlspecialchars($m['subject'] ?: 'بدون عنوان'); ?></p>
        <p><?php echo nl2br(htmlspecialchars($m['message'])); ?></p>
        <div class="actions-cell">
          <?php if (!$m['is_read']): ?><a href="messages.php?read=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline">تعليم كمقروءة</a><?php endif; ?>
          <a href="messages.php?delete=<?php echo $m['id']; ?>&csrf=<?php echo generateCsrfToken(); ?>" class="btn btn-sm btn-danger" onclick="return confirm('حذف هذه الرسالة؟');">حذف</a>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$messages): ?><p class="muted">لا توجد رسائل بعد.</p><?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
