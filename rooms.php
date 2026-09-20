<?php
// صفحة عرض كل الغرف والأجنحة، مع إمكانية الفلترة حسب النوع
require_once __DIR__ . '/includes/functions.php';

$types = $pdo->query('SELECT * FROM room_types')->fetchAll();

$typeFilter = isset($_GET['type']) ? (int)$_GET['type'] : 0;

if ($typeFilter) {
    // استعلام محضّر (Prepared Statement) لأن القيمة قادمة من المستخدم
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE status="available" AND room_type_id = ? ORDER BY created_at DESC');
    $stmt->execute([$typeFilter]);
} else {
    $stmt = $pdo->query('SELECT * FROM rooms WHERE status="available" ORDER BY created_at DESC');
}
$rooms = $stmt->fetchAll();

$banner = getPageBanner($pdo, 'rooms');
$bannerStyle = $banner ? "background-image:url('assets/img/banners/" . htmlspecialchars($banner) . "')" : '';

$pageTitle = 'الغرف والأجنحة';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero" style="<?php echo $bannerStyle; ?>">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <h1>الغرف والأجنحة</h1>
    <p>اختر الغرفة المناسبة لإقامتك في الصرح الذهبي</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="filters">
      <a href="rooms.php" class="filter-btn <?php echo !$typeFilter ? 'active' : ''; ?>">الكل</a>
      <?php foreach ($types as $t): ?>
        <a href="rooms.php?type=<?php echo $t['id']; ?>" class="filter-btn <?php echo $typeFilter == $t['id'] ? 'active' : ''; ?>">
          <?php echo htmlspecialchars($t['name']); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="cards-grid">
      <?php if (!$rooms): ?>
        <p class="muted">لا توجد غرف متاحة حاليًا ضمن هذا التصنيف.</p>
      <?php endif; ?>
      <?php foreach ($rooms as $room): ?>
        <div class="room-card">
          <div class="room-img" style="background-image:url('assets/img/rooms/<?php echo htmlspecialchars($room['image']); ?>')"></div>
          <div class="room-body">
            <h3><?php echo htmlspecialchars($room['title']); ?></h3>
            <p><?php echo htmlspecialchars(mb_substr($room['description'], 0, 90)); ?>...</p>
            <ul class="room-meta">
              <li><i class="fa-solid fa-users"></i> حتى <?php echo (int)$room['capacity']; ?> أشخاص</li>
            </ul>
            <div class="room-footer">
              <span class="price"><?php echo number_format($room['price'], 2); ?> $ / ليلة</span>
              <a href="room-details.php?id=<?php echo $room['id']; ?>" class="btn btn-sm btn-outline">التفاصيل والحجز</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
