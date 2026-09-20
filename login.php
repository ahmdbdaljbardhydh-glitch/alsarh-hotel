<?php
// صفحة تسجيل الدخول
require_once __DIR__ . '/includes/functions.php';

// إن كان الزائر مسجّل دخوله بالفعل، لا داعي لعرض نموذج الدخول مرة أخرى
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$maxAttempts = 5; // الحد الأقصى لمحاولات الدخول الفاشلة قبل الإيقاف المؤقت

// معالجة إرسال نموذج تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // التحقق من رمز CSRF لمنع إرسال النموذج من مصدر خارجي
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'جلسة غير صالحة، يرجى إعادة المحاولة.';
    }

    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // لا تُنظَّف كلمة المرور بـ clean() لأنها تُقارن بقيمتها الحرفية

    if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0; //خمسةمحولتل لدخول وبعدها تتوقف موقت

    if ($_SESSION['login_attempts'] >= $maxAttempts) {
        $errors[] = 'تم إيقاف الدخول مؤقتًا بسبب محاولات كثيرة فاشلة. حاول لاحقًا.';
    }

    if (empty($errors)) {
        if (!isValidEmail($email) || $password === '') {
            $errors[] = 'يرجى إدخال بريد إلكتروني وكلمة مرور صحيحين.';
        } else {
            // البحث عن المستخدم بالبريد الإلكتروني فقط عبر استعلام محضّر
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // password_verify(): تقارن كلمة المرور المُدخلة بالنسخة المشفّرة
            // المخزَّنة في قاعدة البيانات دون الحاجة لفك تشفيرها إطلاقًا
            if (!$user || !password_verify($password, $user['password'])) {
                $_SESSION['login_attempts']++;
                $errors[] = 'البريد الإلكتروني أو كلمة المرور غير صحيحة.';
            } elseif ($user['status'] === 'pending') {
                $errors[] = 'حسابك بانتظار تفعيل الإدارة.';
            } elseif ($user['status'] === 'blocked') {
                $errors[] = 'تم حظر هذا الحساب. يرجى التواصل مع الإدارة.';
            } else {
                /* ------------ نجاح تسجيل الدخول ------------ */

                // session_regenerate_id(true): يُصدر رقم جلسة جديدًا تمامًا
                // بعد تسجيل الدخول (مع حذف القديم) لمنع هجوم Session Fixation
                session_regenerate_id(true);

                // تخزين هوية المستخدم داخل الجلسة لاستخدامها في كل صفحة لاحقة
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                unset($_SESSION['login_attempts']); // تصفير عدّاد المحاولات الفاشلة

                setFlash('success', 'مرحبًا بعودتك، ' . $user['full_name'] . '!');
                // توجيه الأدمن مباشرة للوحة التحكم، والمستخدم العادي للرئيسية
                header('Location: ' . ($user['user_type'] === 'admin' ? 'admin/index.php' : 'index.php'));
                exit;
            }
        }
    }
}

$pageTitle = 'تسجيل الدخول';
require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
  <div class="auth-card">
    <h2><i class="fa-solid fa-right-to-bracket"></i> تسجيل الدخول</h2>
    <p class="muted">سعداء بعودتك إلى الصرح الذهبي</p>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <ul><?php foreach ($errors as $e) echo '<li>' . $e . '</li>'; ?></ul>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
      <div class="form-group">
        <label>البريد الإلكتروني</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
      </div>
      <div class="form-group">
        <label>كلمة المرور</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">دخول</button>
    </form>

    <p class="auth-switch">ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a></p>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
