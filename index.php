<?php
// لوحة القيادة (Dashboard) — أول صفحة يراها الأدمن، تعرض إحصائيات سريعة عن حالة الموقع
$pageTitle = 'لوحة القيادة';
require_once __DIR__ . '/includes/admin_header.php';

// COUNT(*) c: نطلب من MySQL نفسه حساب عدد الصفوف مباشرة (أسرع من جلبها كلها وعدّها في PHP)
// ['c'] في نهاية كل سطر: نقرأ قيمة العمود c مباشرة من الصف الوحيد الناتج
$usersCount    = $pdo->query('SELECT COUNT(*) c FROM users WHERE user_type="user"')->fetch()['c'];
$pendingUsers  = $pdo->query('SELECT COUNT(*) c FROM users WHERE status="pending"')->fetch()['c'];
$roomsCount    = $pdo->query('SELECT COUNT(*) c FROM rooms')->fetch()['c'];
$bookingsCount = $pdo->query('SELECT COUNT(*) c FROM bookings WHERE status="pending"')->fetch()['c'];
$messagesCount = $pdo->query('SELECT COUNT(*) c FROM contact_messages WHERE is_read=0')->fetch()['c'];

// آخر 5 حجوزات مع اسم الغرفة واسم العميل عبر JOIN بين 3 جداول دفعة واحدة
$recentBookings = $pdo->query('SELECT bookings.*, rooms.title, users.full_name FROM bookings
                                JOIN rooms ON rooms.id = bookings.room_id
                                JOIN users ON users.id = bookings.user_id
                                ORDER BY bookings.created_at DESC LIMIT 5')->fetchAll();
?>

<div class="stats-grid">
  <div class="stat-card">
    <i class="fa-solid fa-users"></i>
    <div><h3><?php echo $usersCount; ?></h3><p>إجمالي المستخدمين</p></div>
  </div>
  <div class="stat-card warn">
    <i class="fa-solid fa-user-clock"></i>
    <div><h3><?php echo $pendingUsers; ?></h3><p>حسابات بانتظار التفعيل</p></div>
  </div>
  <div class="stat-card">
    <i class="fa-solid fa-bed"></i>
    <div><h3><?php echo $roomsCount; ?></h3><p>إجمالي الغرف</p></div>
  </div>
  <div class="stat-card warn">
    <i class="fa-solid fa-calendar-check"></i>
    <div><h3><?php echo $bookingsCount; ?></h3><p>حجوزات بانتظار التأكيد</p></div>
  </div>
  <div class="stat-card">
    <i class="fa-solid fa-envelope"></i>
    <div><h3><?php echo $messagesCount; ?></h3><p>رسائل غير مقروءة</p></div>
  </div>
</div>

<div class="admin-panel">
  <h2>أحدث الحجوزات</h2>
  <table class="data-table">
    <thead><tr><th>العميل</th><th>الغرفة</th><th>الوصول</th><th>المغادرة</th><th>الحالة</th></tr></thead>
    <tbody>
      <?php foreach ($recentBookings as $b): ?>
        <tr>
          <td><?php echo htmlspecialchars($b['full_name']); ?></td>
          <td><?php echo htmlspecialchars($b['title']); ?></td>
          <td><?php echo formatDate($b['check_in']); ?></td>
          <td><?php echo formatDate($b['check_out']); ?></td>
          <td><span class="status status-<?php echo $b['status']; ?>"><?php echo $b['status']; ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$recentBookings): ?><tr><td colspan="5">لا توجد حجوزات بعد.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
