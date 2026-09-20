<?php
// إدارة الحجوزات — يستطيع الأدمن هنا تأكيد أو إلغاء أي حجز، والفلترة حسب حالته
$pageTitle = 'إدارة الحجوزات';
require_once __DIR__ . '/includes/admin_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];
    // in_array(): طبقة تحقق إضافية تمنع تحديث الحالة إلى أي قيمة غير الثلاث المسموحة
    if (in_array($action, ['confirmed', 'cancelled', 'pending'])) {
        $pdo->prepare('UPDATE bookings SET status=? WHERE id=?')->execute([$action, $id]);
        setFlash('success', 'تم تحديث حالة الحجز.');
    }
    header('Location: bookings.php');
    exit;
}

// بناء الاستعلام ديناميكيًا: نضيف شرط WHERE فقط إن كان هناك فلتر حالة مطلوب من الرابط
$statusFilter = $_GET['status'] ?? '';
$sql = 'SELECT bookings.*, rooms.title, users.full_name, users.email FROM bookings
        JOIN rooms ON rooms.id = bookings.room_id
        JOIN users ON users.id = bookings.user_id';
$params = [];
if (in_array($statusFilter, ['pending','confirmed','cancelled'])) {
    $sql .= ' WHERE bookings.status = ?';
    $params[] = $statusFilter;
}
$sql .= ' ORDER BY bookings.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$statusLabels = ['pending' => 'قيد الانتظار', 'confirmed' => 'مؤكد', 'cancelled' => 'ملغى'];
?>

<div class="admin-panel">
  <div class="filters">
    <a href="bookings.php" class="filter-btn <?php echo !$statusFilter ? 'active' : ''; ?>">الكل</a>
    <a href="bookings.php?status=pending" class="filter-btn <?php echo $statusFilter==='pending'?'active':''; ?>">قيد الانتظار</a>
    <a href="bookings.php?status=confirmed" class="filter-btn <?php echo $statusFilter==='confirmed'?'active':''; ?>">مؤكدة</a>
    <a href="bookings.php?status=cancelled" class="filter-btn <?php echo $statusFilter==='cancelled'?'active':''; ?>">ملغاة</a>
  </div>

  <table class="data-table">
    <thead><tr><th>العميل</th><th>البريد</th><th>الغرفة</th><th>الوصول</th><th>المغادرة</th><th>الإجمالي</th><th>الحالة</th><th>إجراءات</th></tr></thead>
    <tbody>
    <?php foreach ($bookings as $b): ?>
      <tr>
        <td><?php echo htmlspecialchars($b['full_name']); ?></td>
        <td><?php echo htmlspecialchars($b['email']); ?></td>
        <td><?php echo htmlspecialchars($b['title']); ?></td>
        <td><?php echo formatDate($b['check_in']); ?></td>
        <td><?php echo formatDate($b['check_out']); ?></td>
        <td><?php echo number_format($b['total_price'], 2); ?> $</td>
        <td><span class="status status-<?php echo $b['status']; ?>"><?php echo $statusLabels[$b['status']]; ?></span></td>
        <td class="actions-cell">
          <?php if ($b['status'] !== 'confirmed'): ?>
          <form method="post"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $b['id']; ?>"><input type="hidden" name="action" value="confirmed"><button class="btn btn-sm btn-success">تأكيد</button></form>
          <?php endif; ?>
          <?php if ($b['status'] !== 'cancelled'): ?>
          <form method="post"><input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>"><input type="hidden" name="id" value="<?php echo $b['id']; ?>"><input type="hidden" name="action" value="cancelled"><button class="btn btn-sm btn-danger">إلغاء</button></form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$bookings): ?><tr><td colspan="8">لا توجد حجوزات.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
