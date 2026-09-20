<?php
require_once __DIR__ . '/includes/functions.php';

// جلب أحدث 3 غرف متاحة، 4 خدمات، وآخر 3 تقييمات معتمدة فقط لعرضها بالرئيسية
$rooms = $pdo->query('SELECT * FROM rooms WHERE status = "available" ORDER BY created_at DESC LIMIT 3')->fetchAll();
$services = $pdo->query('SELECT * FROM services ORDER BY id ASC LIMIT 4')->fetchAll();
$reviews = $pdo->query('SELECT reviews.*, users.full_name, users.image FROM reviews
                         JOIN users ON users.id = reviews.user_id
                         WHERE reviews.status = "approved" ORDER BY reviews.created_at DESC LIMIT 3')->fetchAll();

// جلب صورة خلفية قسم الهيرو الرئيسي التي رفعها الأدمن (إن وُجدت)
$heroBanner = getPageBanner($pdo, 'home');
$heroStyle = $heroBanner ? "background-image:url('assets/img/banners/" . htmlspecialchars($heroBanner) . "')" : '';

$pageTitle = 'الرئيسية';
require_once __DIR__ . '/includes/header.php';
?>

<!-- قسم الهيرو: الخلفية تأتي من صورة الأدمن (إن وُجدت) وتظهر كخلفية متحركة بصريًا خلف النص -->
<section class="hero" style="<?php echo $heroStyle; ?>">
  <div class="hero-overlay"></div>
  <div class="container hero-content">
    <h1>أهلًا بكم في <span>الصرح الذهبي</span></h1>
    <p>حيث تلتقي الفخامة بالضيافة الأصيلة، تجربة إقامة خمس نجوم لا تُنسى</p>
    <div class="hero-actions">
      <a href="rooms.php" class="btn btn-primary btn-lg">استعرض الغرف</a>
      <a href="#booking" class="btn btn-outline-light btn-lg">احجز الآن</a>
    </div>
  </div>
</section>

<section class="section booking-strip" id="booking">
  <div class="container booking-form-card">
    <form action="rooms.php" method="get" class="quick-booking">
      <div class="form-group">
        <label><i class="fa-solid fa-calendar-days"></i> تاريخ الوصول</label>
        <input type="date" name="check_in" required>
      </div>
      <div class="form-group">
        <label><i class="fa-solid fa-calendar-days"></i> تاريخ المغادرة</label>
        <input type="date" name="check_out" required>
      </div>
      <div class="form-group">
        <label><i class="fa-solid fa-users"></i> عدد الضيوف</label>
        <input type="number" name="guests" min="1" value="2">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i> بحث عن غرفة</button>
    </form>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-title">
      <h2>غرف وأجنحة مميزة</h2>
      <p>اختر من بين تشكيلة واسعة من الغرف الفاخرة المصممة لراحتك</p>
    </div>
    <div class="cards-grid">
      <?php foreach ($rooms as $room): ?>
        <div class="room-card">
          <div class="room-img" style="background-image:url('assets/img/rooms/<?php echo htmlspecialchars($room['image']); ?>')"></div>
          <div class="room-body">
            <h3><?php echo htmlspecialchars($room['title']); ?></h3>
            <p><?php echo htmlspecialchars(mb_substr($room['description'], 0, 80)); ?>...</p>
            <div class="room-footer">
              <span class="price"><?php echo number_format($room['price'], 2); ?> $ / ليلة</span>
              <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn btn-sm btn-outline">التفاصيل</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center">
      <a href="rooms.php" class="btn btn-outline">عرض جميع الغرف</a>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-title">
      <h2>خدماتنا الفندقية</h2>
      <p>نوفر لك كل ما تحتاجه لإقامة استثنائية</p>
    </div>
    <div class="services-grid">
      <?php foreach ($services as $service): ?>
        <div class="service-card">
          <i class="fa-solid <?php echo htmlspecialchars($service['icon'] ?: 'fa-star'); ?>"></i>
          <h4><?php echo htmlspecialchars($service['title']); ?></h4>
          <p><?php echo htmlspecialchars($service['description']); ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($reviews): ?>
<section class="section">
  <div class="container">
    <div class="section-title">
      <h2>آراء ضيوفنا</h2>
    </div>
    <div class="reviews-grid">
      <?php foreach ($reviews as $r): ?>
        <div class="review-card">
          <div class="stars">
            <?php for ($i=0;$i<5;$i++): ?>
              <i class="fa-solid fa-star <?php echo $i < $r['rating'] ? 'active' : ''; ?>"></i>
            <?php endfor; ?>
          </div>
          <p>"<?php echo htmlspecialchars($r['comment']); ?>"</p>
          <div class="reviewer">
            <img src="assets/img/users/<?php echo htmlspecialchars($r['image']); ?>" onerror="this.src='assets/img/users/default.png'" alt="">
            <span><?php echo htmlspecialchars($r['full_name']); ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
