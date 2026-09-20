<?php
// صفحة "من نحن"
require_once __DIR__ . '/includes/functions.php';

$banner = getPageBanner($pdo, 'about');
$bannerStyle = $banner ? "background-image:url('assets/img/banners/" . htmlspecialchars($banner) . "')" : '';

$pageTitle = 'من نحن';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero" style="<?php echo $bannerStyle; ?>">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <h1>من نحن</h1>
    <p>قصة الصرح الذهبي في الضيافة الفاخرة</p>
  </div>
</section>

<section class="section">
  <div class="container about-content">
    <div class="about-text">
      <h2>فندق الصرح الذهبي</h2>
      <p>
        منذ افتتاحه، يُجسّد فندق الصرح الذهبي معايير الفخامة الحقيقية بخمس نجوم، من خلال تصميم معماري آسر،
        وخدمة ضيافة استثنائية، وموقع متميز في قلب المدينة. نحرص على تقديم تجربة إقامة فريدة تجمع بين
        الأصالة والحداثة لتلبية تطلعات ضيوفنا الكرام.
      </p>
      <p>
        يضم الفندق مجموعة متنوعة من الغرف والأجنحة الفاخرة، ومطاعم عالمية، ومسبحًا خارجيًا، ومركزًا صحيًا
        متكاملًا (سبا)، وصالة رياضية حديثة، إلى جانب قاعات مخصصة للمناسبات والفعاليات.
      </p>
    </div>
    <div class="about-stats">
      <div class="stat-box"><h3>5</h3><p>نجوم فخامة</p></div>
      <div class="stat-box"><h3>+20</h3><p>عام من الخبرة</p></div>
      <div class="stat-box"><h3>+150</h3><p>غرفة وجناح</p></div>
      <div class="stat-box"><h3>24/7</h3><p>خدمة عملاء</p></div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
