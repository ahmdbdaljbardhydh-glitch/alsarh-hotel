<?php
/**
 * ==============================================================
 * إدارة خلفيات رأس الصفحات (Hero Banners)
 * ==============================================================
 * يسمح هذا الملف للأدمن برفع صورة خلفية لكل صفحة رئيسية بالموقع
 */
$pageTitle = 'خلفيات الصفحات';
require_once __DIR__ . '/includes/admin_header.php';

$errors = [];

// ---------- معالجة رفع/تغيير صورة خلفية لصفحة معينة ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {

    // page_key: معرف الصفحة (home, rooms, services...) قادم من حقل مخفي في النموذج
    $pageKey = clean($_POST['page_key'] ?? '');

    if (empty($_FILES['image']['name'])) {
        $errors[] = 'يرجى اختيار صورة لهذه الصفحة.';
    } else {
        // uploadImage(): تتحقق من نوع وحجم الصورة، وتُنشئ اسمًا عشوائيًا آمنًا لها
        $upload = uploadImage($_FILES['image'], __DIR__ . '/../assets/img/banners', 'banner');

        if ($upload['success']) {
            // تحديث اسم الصورة في جدول page_banners لهذه الصفحة تحديدًا
            $stmt = $pdo->prepare('UPDATE page_banners SET image = ? WHERE page_key = ?');
            $stmt->execute([$upload['filename'], $pageKey]);

            setFlash('success', 'تم تحديث خلفية الصفحة بنجاح.');
        } else {
            $errors[] = $upload['message'];
        }
    }

    if (empty($errors)) {
        header('Location: banners.php');
        exit;
    }
}

// ---------- حذف خلفية صفحة معينة (الرجوع للون الافتراضي) ----------
if (isset($_GET['reset']) && verifyCsrfToken($_GET['csrf'] ?? '')) {
    $stmt = $pdo->prepare('UPDATE page_banners SET image = NULL WHERE page_key = ?');
    $stmt->execute([clean($_GET['reset'])]);
    setFlash('success', 'تم حذف خلفية الصفحة والرجوع للون الافتراضي.');
    header('Location: banners.php');
    exit;
}

// جلب كل الصفحات وحالتها الحالية من جدول page_banners
$banners = $pdo->query('SELECT * FROM page_banners ORDER BY id ASC')->fetchAll();
?>

<div class="admin-panel">
  <h2>خلفيات رأس الصفحات</h2>
  <p class="muted" style="margin-bottom:20px;">
    ارفع صورة عريضة (يفضل بعرض 1600 بكسل تقريبًا) لكل صفحة، ستظهر كخلفية
    متحركة الشعور بصريًا خلف العنوان المكتوب في أعلى تلك الصفحة.
  </p>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>' . $e . '</li>'; ?></ul></div>
  <?php endif; ?>

  <div class="banners-grid">
    <?php foreach ($banners as $b): ?>
      <div class="banner-card">
        <div class="banner-preview" style="<?php echo $b['image'] ? "background-image:url('../assets/img/banners/" . htmlspecialchars($b['image']) . "')" : ''; ?>">
          <?php if (!$b['image']): ?><span>لا توجد صورة بعد</span><?php endif; ?>
        </div>
        <h4><?php echo htmlspecialchars($b['page_label']); ?></h4>

        <form method="post" enctype="multipart/form-data" class="banner-form">
          <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
          <input type="hidden" name="page_key" value="<?php echo htmlspecialchars($b['page_key']); ?>">
          <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required>
          <button type="submit" class="btn btn-sm btn-primary">حفظ الصورة</button>
        </form>

        <?php if ($b['image']): ?>
          <a href="banners.php?reset=<?php echo urlencode($b['page_key']); ?>&csrf=<?php echo generateCsrfToken(); ?>"
             class="btn btn-sm btn-danger" onclick="return confirm('حذف خلفية هذه الصفحة؟');">
            حذف الصورة
          </a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
