<?php
/**
 * هيدر لوحة تحكم الأدمن — يظهر أعلى كل صفحات مجلد admin/
 */
require_once __DIR__ . '/../../includes/functions.php';

// requireAdmin(): يمنع أي شخص غير مسجل دخول، أو مسجل لكن نوعه "user"
// وليس "admin"، من الوصول لأي صفحة داخل لوحة التحكم — يُعيد توجيهه
// فورًا لصفحة تسجيل الدخول قبل تحميل أي محتوى من اللوحة
requireAdmin('../login.php');

$adminUser = getCurrentUser($pdo);
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>لوحة تحكم - الصرح الذهبي</title>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Tajawal:wght@400;500;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">

<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="admin-logo"><i class="fa-solid fa-hotel"></i> الصرح الذهبي</div>
    <nav class="admin-nav">
      <a href="index.php"><i class="fa-solid fa-gauge"></i> لوحة القيادة</a>
      <a href="users.php"><i class="fa-solid fa-users"></i> المستخدمون</a>
      <a href="room_types.php"><i class="fa-solid fa-tags"></i> أنواع الغرف</a>
      <a href="rooms.php"><i class="fa-solid fa-bed"></i> الغرف</a>
      <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> الحجوزات</a>
      <a href="services.php"><i class="fa-solid fa-concierge-bell"></i> الخدمات</a>
      <a href="gallery.php"><i class="fa-solid fa-images"></i> معرض الصور</a>
      <a href="banners.php"><i class="fa-solid fa-image"></i> خلفيات الصفحات</a>
      <a href="reviews.php"><i class="fa-solid fa-star"></i> التقييمات</a>
      <a href="messages.php"><i class="fa-solid fa-envelope"></i> رسائل التواصل</a>
      <hr>
      <a href="../index.php" target="_blank"><i class="fa-solid fa-globe"></i> عرض الموقع</a>
      <a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
    </nav>
  </aside>

  <main class="admin-main">
    <header class="admin-topbar">
      <button class="nav-toggle" id="adminNavToggle"><i class="fa-solid fa-bars"></i></button>
      <h1><?php echo $pageTitle ?? 'لوحة التحكم'; ?></h1>
      <div class="admin-user">
        <img src="../assets/img/users/<?php echo htmlspecialchars($adminUser['image']); ?>" onerror="this.src='../assets/img/users/default.png'" alt="">
        <span><?php echo htmlspecialchars($adminUser['full_name']); ?></span>
      </div>
    </header>

    <?php if ($flash): ?>
      <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
    <?php endif; ?>

    <div class="admin-content">
