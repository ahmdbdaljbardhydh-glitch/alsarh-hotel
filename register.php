<?php
// صفحة إنشاء حساب جديد لأي زائر (يصبح المستخدم بعدها قادرًا على الحجز وإدارة ملفه الشخصي)
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];

// معالجة إرسال نموذج إنشاء الحساب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'جلسة غير صالحة، يرجى إعادة المحاولة.';
    }

    $fullName = clean($_POST['full_name'] ?? '');
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $phone    = clean($_POST['phone'] ?? '');
    $gender   = clean($_POST['gender'] ?? '');
    $birthdate = clean($_POST['birthdate'] ?? '');

    /* ---------- التحقق من صحة البيانات ---------- */
    if (mb_strlen($fullName) < 3) {
        $errors[] = 'الاسم الكامل يجب ألا يقل عن 3 أحرف.';
    }
    if (!isValidEmail($email)) {
        $errors[] = 'صيغة البريد الإلكتروني غير صحيحة.';
    }
    if (mb_strlen($password) < 8) {
        $errors[] = 'كلمة المرور يجب ألا تقل عن 8 أحرف.';
    }
    if ($password !== $confirm) {
        $errors[] = 'كلمتا المرور غير متطابقتين.';
    }
    if ($birthdate && strtotime($birthdate) > strtotime('-10 years')) {
        $errors[] = 'تاريخ الميلاد غير منطقي.';
    }

    /*  التحقق من عدم تكرار البريد ---------- */
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'هذا البريد الإلكتروني مسجل مسبقًا.';
        }
    }

    /* ---------- رفع الصورة الشخصية (اختياري) ---------- */
    $imageName = 'default.png';
    if (empty($errors) && !empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], __DIR__ . '/assets/img/users', 'user');
        if ($upload['success']) {
            $imageName = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }

    /* ---------- إدخال المستخدم ---------- */
    if (empty($errors)) {
        // (): تشفير كلمة المرور بخوارزمية Bcrypt قبل تخزينها،
        // بحيث لا تُحفظ كلمة المرور الحقيقية إطلاقًا في قاعدة البيانات
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // إدراج المستخدم الجديد بنوع "user" وحالة "active" (مفعّل مباشرة)
        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, password, phone, gender, image, birthdate, user_type, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, "user", "active")'
        );
        $stmt->execute([$fullName, $email, $hashedPassword, $phone, $gender ?: null, $imageName, $birthdate ?: null]);

        setFlash('success', 'تم إنشاء حسابك بنجاح! يمكنك الآن تسجيل الدخول.');
        header('Location: login.php');
        exit;
    }
}

$pageTitle = 'إنشاء حساب';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
  <div class="auth-card">
    <h2><i class="fa-solid fa-user-plus"></i> إنشاء حساب جديد</h2>
    <p class="muted">انضم إلينا واستمتع بتجربة إقامة استثنائية</p>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <ul><?php foreach ($errors as $e) echo '<li>' . $e . '</li>'; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

      <div class="form-row">
        <div class="form-group">
          <label>الاسم الكامل *</label>
          <input type="text" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required minlength="3">
        </div>
        <div class="form-group">
          <label>البريد الإلكتروني *</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>كلمة المرور *</label>
          <input type="password" name="password" required minlength="8">
        </div>
        <div class="form-group">
          <label>تأكيد كلمة المرور *</label>
          <input type="password" name="confirm_password" required minlength="8">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>رقم الهاتف</label>
          <input type="text" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label>تاريخ الميلاد</label>
          <input type="date" name="birthdate" value="<?php echo htmlspecialchars($_POST['birthdate'] ?? ''); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>الجنس</label>
          <select name="gender">
            <option value="">اختر</option>
            <option value="ذكر">ذكر</option>
            <option value="انثى">أنثى</option>
          </select>
        </div>
        <div class="form-group">
          <label>الصورة الشخصية</label>
          <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-block">إنشاء الحساب</button>
    </form>

    <p class="auth-switch">لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a></p>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
