<?php
/**
 * ==============================================================
 * هيدر (رأس) الموقع المشترك — يظهر أعلى كل صفحات الواجهة العامة
 * ==============================================================
 */
require_once __DIR__ . '/functions.php';

// إن كان الزائر مسجّل دخوله، نجلب بياناته الكاملة لعرض اسمه وصورته
$currentUser = isLoggedIn() ? getCurrentUser($pdo) : null;

// جلب أي رسالة تنبيه مؤقتة (نجاح/خطأ) خُزِّنت في الصفحة السابقة
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>فندق الصرح الذهبي</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<!-- خط Amiri للعناوين (طابع فندقي فاخر) وخط Tajawal للنصوص العادية -->
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
<!-- مكتبة أيقونات Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="main-header">
  <div class="container header-inner">
    <a href="index.php" class="logo"><i class="fa-solid fa-hotel"></i> الصرح <span>الذهبي</span></a>

    <!-- شريط التنقل الرئيسي بين صفحات الموقع -->
    <nav class="main-nav" id="mainNav">
      <a href="index.php">الرئيسية</a>
      <a href="rooms.php">الغرف والأجنحة</a>
      <a href="services.php">الخدمات</a>
      <a href="gallery.php">معرض الصور</a>
      <a href="about.php">من نحن</a>
      <a href="contact.php">تواصل معنا</a>
    </nav>

    <div class="header-actions">
      <?php if ($currentUser): ?>
        <!-- المستخدم مسجّل دخوله: نعرض صورته واسمه وقائمة منسدلة -->
        <div class="user-menu">
          <img src="assets/img/users/<?php echo htmlspecialchars($currentUser['image']); ?>" onerror="this.src='assets/img/users/default.png'" class="avatar-sm" alt="">
          <div class="dropdown">
            <span><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
            <div class="dropdown-menu">
              <a href="profile.php"><i class="fa-solid fa-user"></i> ملفي الشخصي</a>
              <a href="my-bookings.php"><i class="fa-solid fa-calendar-check"></i> حجوزاتي</a>
              <?php if (isAdmin()): ?>
                <a href="admin/index.php"><i class="fa-solid fa-gauge"></i> لوحة التحكم</a>
              <?php endif; ?>
              <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
            </div>
          </div>
        </div>
      <?php else: ?>
        <!-- زائر غير مسجّل: نعرض زري الدخول والتسجيل -->
        <a href="login.php" class="btn btn-outline">تسجيل الدخول</a>
        <a href="register.php" class="btn btn-primary">إنشاء حساب</a>
      <?php endif; ?>
      <button class="nav-toggle" id="navToggle"><i class="fa-solid fa-bars"></i></button>
    </div>
  </div>
</header>

<?php if ($flash): ?>
  <!-- رسالة التنبيه المؤقتة (نجاح أو خطأ) إن وُجدت -->
  <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?> container">
    <?php echo htmlspecialchars($flash['message']); ?>
  </div>
<?php endif; ?>
