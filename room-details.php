<?php
// صفحة تفاصيل غرفة واحدة + نموذج إتمام حجزها
require_once __DIR__ . '/includes/functions.php';

// (int) تحوّل رقم الغرفة القادم من الرابط (?id=..) لرقم صحيح إجباريًا،
// حتى لو حاول أحد وضع نص أو كود بدل الرقم في الرابط
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT rooms.*, room_types.name AS type_name FROM rooms
                        JOIN room_types ON room_types.id = rooms.room_type_id
                        WHERE rooms.id = ?');
$stmt->execute([$id]);
$room = $stmt->fetch();

if (!$room) {
    setFlash('error', 'الغرفة المطلوبة غير موجودة.');
    header('Location: rooms.php');
    exit;
}

$errors = [];

/* ---------- معالجة طلب الحجز ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isLoggedIn()) {
        setFlash('error', 'يجب تسجيل الدخول أولًا لإتمام الحجز.');
        header('Location: login.php');
        exit;
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'جلسة غير صالحة، أعد المحاولة.';
    }

    $checkIn  = clean($_POST['check_in'] ?? '');
    $checkOut = clean($_POST['check_out'] ?? '');
    $guests   = (int)($_POST['guests'] ?? 1);

    // strtotime(): تحوّل نص التاريخ إلى رقم (timestamp) يمكن مقارنته وحسابه
    if (!strtotime($checkIn) || !strtotime($checkOut)) {
        $errors[] = 'يرجى إدخال تواريخ صحيحة.';
    } elseif (strtotime($checkOut) <= strtotime($checkIn)) {
        $errors[] = 'يجب أن يكون تاريخ المغادرة بعد تاريخ الوصول.';
    } elseif (strtotime($checkIn) < strtotime('today')) {
        $errors[] = 'لا يمكن الحجز بتاريخ سابق.';
    }

    if ($guests < 1 || $guests > $room['capacity']) {
        $errors[] = 'عدد الضيوف غير مناسب لسعة هذه الغرفة (الحد الأقصى ' . $room['capacity'] . ').';
    }

    if (empty($errors)) {
        // حساب عدد الليالي: الفرق بين التاريخين بالثواني ÷ 86400 (ثواني اليوم الواحد)
        $nights = (strtotime($checkOut) - strtotime($checkIn)) / 86400;
        $total = $nights * $room['price'];

        // إدراج الحجز بحالة "pending" (قيد الانتظار) لحين مراجعة وتأكيد الأدمن،
        // مع ربطه بهوية المستخدم الحقيقية من الجلسة (لا يمكن الحجز باسم غيره)
        $stmt = $pdo->prepare('INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, total_price, status)
                                VALUES (?, ?, ?, ?, ?, ?, "pending")');
        $stmt->execute([$_SESSION['user_id'], $room['id'], $checkIn, $checkOut, $guests, $total]);

        setFlash('success', 'تم إرسال طلب حجزك بنجاح! سيتم تأكيده من قبل الإدارة قريبًا.');
        header('Location: my-bookings.php');
        exit;
    }
}

$pageTitle = $room['title'];
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container room-details-grid">
    <div class="room-details-main">
      <div class="room-details-img" style="background-image:url('assets/img/rooms/<?php echo htmlspecialchars($room['image']); ?>')"></div>
      <h1><?php echo htmlspecialchars($room['title']); ?></h1>
      <span class="badge"><?php echo htmlspecialchars($room['type_name']); ?></span>
      <p class="room-desc"><?php echo nl2br(htmlspecialchars($room['description'])); ?></p>
      <ul class="room-meta">
        <li><i class="fa-solid fa-users"></i> السعة: حتى <?php echo (int)$room['capacity']; ?> أشخاص</li>
        <li><i class="fa-solid fa-tag"></i> السعر: <?php echo number_format($room['price'], 2); ?> $ لليلة الواحدة</li>
      </ul>
    </div>

    <div class="booking-box">
      <h3>احجز هذه الغرفة</h3>
      <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>' . $e . '</li>'; ?></ul></div>
      <?php endif; ?>

      <?php if (isLoggedIn()): ?>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
          <div class="form-group">
            <label>تاريخ الوصول</label>
            <input type="date" name="check_in" value="<?php echo htmlspecialchars($_GET['check_in'] ?? ''); ?>" required>
          </div>
          <div class="form-group">
            <label>تاريخ المغادرة</label>
            <input type="date" name="check_out" value="<?php echo htmlspecialchars($_GET['check_out'] ?? ''); ?>" required>
          </div>
          <div class="form-group">
            <label>عدد الضيوف</label>
            <input type="number" name="guests" min="1" max="<?php echo (int)$room['capacity']; ?>" value="<?php echo htmlspecialchars($_GET['guests'] ?? '1'); ?>" required>
          </div>
          <button type="submit" class="btn btn-primary btn-block">تأكيد الحجز</button>
        </form>
      <?php else: ?>
        <p class="muted">يجب <a href="login.php">تسجيل الدخول</a> لإتمام عملية الحجز.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
