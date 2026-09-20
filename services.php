<?php
// صفحة عرض خدمات الفندق (مسبح، سبا، مطاعم...)
require_once __DIR__ . '/includes/functions.php';

$services = $pdo->query('SELECT * FROM services ORDER BY id ASC')->fetchAll();

// جلب صورة خلفية رأس هذه الصفحة التي يرفعها الأدمن (إن وُجدت)
$banner = getPageBanner($pdo, 'services');
$bannerStyle = $banner ? "background-image:url('assets/img/banners/" . htmlspecialchars($banner) . "')" : '';

$pageTitle = 'الخدمات';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero" style="<?php echo $bannerStyle; ?>">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <h1>خدماتنا الفندقية</h1>
    <p>كل ما تحتاجه لإقامة استثنائية في مكان واحد</p>
  </div>
</section>

<section class="section">
  <div class="container">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
