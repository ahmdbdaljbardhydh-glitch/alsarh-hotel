<?php
// صفحة "تواصل معنا" — تعرض بيانات التواصل + نموذج إرسال رسالة
require_once __DIR__ . '/includes/functions.php';

$errors = [];

// معالجة إرسال نموذج التواصل عند الضغط على "إرسال الرسالة"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'جلسة غير صالحة، أعد المحاولة.';
    }

    $name    = clean($_POST['name'] ?? '');
    $email   = clean($_POST['email'] ?? '');
    $subject = clean($_POST['subject'] ?? '');
    $message = clean($_POST['message'] ?? '');

    if (mb_strlen($name) < 2) $errors[] = 'يرجى إدخال اسم صحيح.';
    if (!isValidEmail($email)) $errors[] = 'صيغة البريد الإلكتروني غير صحيحة.';
    if (mb_strlen($message) < 10) $errors[] = 'نص الرسالة قصير جدًا.';

    if (empty($errors)) {
        // إدراج الرسالة في جدول contact_messages عبر استعلام محضّر آمن
        $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, $subject, $message]);

        // تخزين رسالة نجاح مؤقتة، ثم إعادة توجيه لنفس الصفحة
        setFlash('success', 'تم إرسال رسالتك بنجاح، سنتواصل معك قريبًا.');
        header('Location: contact.php');
        exit;
    }
}

$banner = getPageBanner($pdo, 'contact');
$bannerStyle = $banner ? "background-image:url('assets/img/banners/" . htmlspecialchars($banner) . "')" : '';

$pageTitle = 'تواصل معنا';
require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero" style="<?php echo $bannerStyle; ?>">
  <div class="page-hero-overlay"></div>
  <div class="container page-hero-content">
    <h1>تواصل معنا</h1>
    <p>يسعدنا استقبال استفساراتكم وملاحظاتكم</p>
  </div>
</section>

<section class="section">
  <div class="container contact-grid">
    <!-- عمود بيانات التواصل الثابتة -->
    <div class="contact-info">
      <h3>معلومات التواصل</h3>
      <p><i class="fa-solid fa-location-dot"></i> شارع الخمسين، بجوار جسر بيت بوس</p>
      <p><i class="fa-solid fa-phone"></i> 785059967</p>
      <p><i class="fa-solid fa-phone"></i> 736146222</p>
      <p><i class="fa-solid fa-envelope"></i> info@alsarh.com</p>
    </div>

    <!-- نموذج إرسال رسالة -->
    <div class="contact-form-card">
      <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>' . $e . '</li>'; ?></ul></div>
      <?php endif; ?>
      <form method="post">
        <!-- رمز CSRF مخفي: يُتحقق منه في الخادم عند الإرسال -->
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <div class="form-row">
          <div class="form-group">
            <label>الاسم</label>
            <input type="text" name="name" required>
          </div>
          <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" name="email" required>
          </div>
        </div>
        <div class="form-group">
          <label>الموضوع</label>
          <input type="text" name="subject">
        </div>
        <div class="form-group">
          <label>الرسالة</label>
          <textarea name="message" rows="5" required minlength="10"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">إرسال الرسالة</button>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
