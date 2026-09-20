<?php
// صفحة "ملفي الشخصي" — يعرّض بها المستخدم المسجّل بياناته ويعدّلها بنفسه
require_once __DIR__ . '/includes/functions.php';

// requireLogin(): يمنع أي زائر غير مسجّل دخول من الوصول لهذه الصفحة إطلاقًا
requireLogin('login.php');

$user = getCurrentUser($pdo);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'جلسة غير صالحة، أعد المحاولة.';
    }

    $fullName  = clean($_POST['full_name'] ?? '');
    $phone     = clean($_POST['phone'] ?? '');
    $gender    = clean($_POST['gender'] ?? '');
    $birthdate = clean($_POST['birthdate'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if (mb_strlen($fullName) < 3) {
        $errors[] = 'الاسم الكامل يجب ألا يقل عن 3 أحرف.';
    }
    if ($newPassword !== '' && mb_strlen($newPassword) < 8) {
        $errors[] = 'كلمة المرور الجديدة يجب ألا تقل عن 8 أحرف.';
    }

    $imageName = $user['image'];
    if (empty($errors) && !empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], __DIR__ . '/assets/img/users', 'user');
        if ($upload['success']) {
            $imageName = $upload['filename'];
        } else {
            $errors[] = $upload['message'];
        }
    }

    if (empty($errors)) {
        if ($newPassword !== '') {
            $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('UPDATE users SET full_name=?, phone=?, gender=?, birthdate=?, image=?, password=? WHERE id=?');
            $stmt->execute([$fullName, $phone, $gender ?: null, $birthdate ?: null, $imageName, $hashed, $user['id']]);
        } else {
            // ترك حقل كلمة المرور فارغًا يعني الإبقاء على كلمة المرور القديمة كما هي
            $stmt = $pdo->prepare('UPDATE users SET full_name=?, phone=?, gender=?, birthdate=?, image=? WHERE id=?');
            $stmt->execute([$fullName, $phone, $gender ?: null, $birthdate ?: null, $imageName, $user['id']]);
        }
        // تحديث الاسم داخل الجلسة أيضًا حتى يظهر الاسم الجديد فورًا في الهيدر
        $_SESSION['user_name'] = $fullName;
        setFlash('success', 'تم تحديث بيانات ملفك الشخصي بنجاح.');
        header('Location: profile.php');
        exit;
    }
    $user = array_merge($user, ['full_name' => $fullName, 'phone' => $phone, 'gender' => $gender, 'birthdate' => $birthdate, 'image' => $imageName]);
}

$pageTitle = 'ملفي الشخصي';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="container">
    <div class="auth-card">
      <h2><i class="fa-solid fa-user"></i> ملفي الشخصي</h2>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>' . $e . '</li>'; ?></ul></div>
      <?php endif; ?>

      <div class="profile-avatar">
        <img src="assets/img/users/<?php echo htmlspecialchars($user['image']); ?>" onerror="this.src='assets/img/users/default.png'" alt="">
      </div>

      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">

        <div class="form-row">
          <div class="form-group">
            <label>الاسم الكامل</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required minlength="3">
          </div>
          <div class="form-group">
            <label>البريد الإلكتروني</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>رقم الهاتف</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
          </div>
          <div class="form-group">
            <label>تاريخ الميلاد</label>
            <input type="date" name="birthdate" value="<?php echo htmlspecialchars($user['birthdate'] ?? ''); ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>الجنس</label>
            <select name="gender">
              <option value="">اختر</option>
              <option value="ذكر" <?php echo ($user['gender'] ?? '') === 'ذكر' ? 'selected' : ''; ?>>ذكر</option>
              <option value="انثى" <?php echo ($user['gender'] ?? '') === 'انثى' ? 'selected' : ''; ?>>أنثى</option>
            </select>
          </div>
          <div class="form-group">
            <label>تغيير الصورة الشخصية</label>
            <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp">
          </div>
        </div>

        <div class="form-group">
          <label>كلمة مرور جديدة (اتركها فارغة إن لم ترغب بالتغيير)</label>
          <input type="password" name="new_password" minlength="8">
        </div>

        <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
      </form>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
