<?php
// صفحة "حجوزاتي" — يرى فيها المستخدم المسجّل حجوزاته الخاصة فقط ويمكنه إلغاء ما لم يُؤكَّد منها
require_once __DIR__ . '/includes/functions.php';
requireLogin('login.php');

/* إلغاء حجز من قبل المستخدم نفسه */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        // الشرط "AND user_id=? AND status=pending" مهم جدًا هنا:
        $stmt = $pdo->prepare('UPDATE bookings SET status="cancelled" WHERE id=? AND user_id=? AND status="pending"');
        $stmt->execute([(int)$_POST['cancel_id'], $_SESSION['user_id']]);
        setFlash('success', 'تم إلغاء الحجز.');
    }
    header('Location: my-bookings.php');
    exit;
}

$stmt = $pdo->prepare('SELECT bookings.*, rooms.title, rooms.image FROM bookings
                        JOIN rooms ON rooms.id = bookings.room_id
                        WHERE bookings.user_id = ? ORDER BY bookings.created_at DESC');
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();

$statusLabels = ['pending' => 'قيد الانتظار', 'confirmed' => 'مؤكد', 'cancelled' => 'ملغى'];

$pageTitle = 'حجوزاتي';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container">
    <div class="section-title"><h2>حجوزاتي</h2></div>

    <?php if (!$bookings): ?>
      <p class="muted">لا توجد لديك حجوزات حتى الآن. <a href="rooms.php">تصفح الغرف الآن</a></p>
    <?php else: ?>
      <div class="bookings-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>الغرفة</th><th>تاريخ الوصول</th><th>تاريخ المغادرة</th><th>الضيوف</th><th>الإجمالي</th><th>الحالة</th><th>إجراء</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($bookings as $b): ?>
            <tr>
              <td><?php echo htmlspecialchars($b['title']); ?></td>
              <td><?php echo formatDate($b['check_in']); ?></td>
              <td><?php echo formatDate($b['check_out']); ?></td>
              <td><?php echo (int)$b['guests']; ?></td>
              <td><?php echo number_format($b['total_price'], 2); ?> $</td>
              <td><span class="status status-<?php echo $b['status']; ?>"><?php echo $statusLabels[$b['status']]; ?></span></td>
              <td>
                <?php if ($b['status'] === 'pending'): ?>
                  <form method="post" onsubmit="return confirm('هل أنت متأكد من إلغاء هذا الحجز؟');">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" name="cancel_id" value="<?php echo $b['id']; ?>">
                    <button type="submit" class="btn btn-sm btn-danger">إلغاء</button>
                  </form>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
