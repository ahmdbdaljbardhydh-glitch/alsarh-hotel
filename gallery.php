<?php
// صفحة معرض صور الفندق
require_once __DIR__ . '/includes/functions.php';

$images = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC')->fetchAll();

$banner = getPageBanner($pdo, 'gallery');
$bannerStyle = $banner ? "background-image:url('assets/img/banners/" . htmlspecialchars($banner) . "')" : '';

$pageTitle = 'معرض الصور';
require_once __DIR__ . '/includes/header.php';
?>

<!-- رأس الصفحة: صورة خلفية (من الأدمن) + طبقة تعتيم + العنوان فوقها -->
<section class="page-hero" style="<?php echo $bannerStyle; ?>">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <h1>معرض الصور</h1>
    <p>جولة بصرية داخل الصرح الذهبي</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="gallery-grid">
      <?php if (!$images): ?>
        <p class="muted">لم تتم إضافة صور بعد.</p>
      <?php endif; ?>
      <?php foreach ($images as $img): ?>
        <div class="gallery-item">
          <img src="assets/img/gallery/<?php echo htmlspecialchars($img['image']); ?>" alt="<?php echo htmlspecialchars($img['caption']); ?>">
          <?php if ($img['caption']): ?><span><?php echo htmlspecialchars($img['caption']); ?></span><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
