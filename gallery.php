<?php
// إدارة معرض صور الفندق — إضافة/تعديل/حذف صور المعرض
$pageTitle = 'إدارة معرض الصور';
require_once __DIR__ . '/includes/admin_header.php';

$errors = [];
$editItem = null;

// حذف صورة من المعرض عبر رابط يحمل رقمها ورمز CSRF
if (isset($_GET['delete']) && verifyCsrfToken($_GET['csrf'] ?? '')) {
    $pdo->prepare('DELETE FROM gallery WHERE id=?')->execute([(int)$_GET['delete']]);
    setFlash('success', 'تم حذف الصورة.');
    header('Location: gallery.php');
    exit;
}

// عند الضغط على "تعديل": نجلب بيانات هذه الصورة لعرضها جاهزة داخل نموذج الإضافة نفسه
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM gallery WHERE id=?');
    $stmt->execute([(int)$_GET['edit']]);
    $editItem = $stmt->fetch();
}

// إضافة صورة جديدة أو تعديل صورة موجودة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $caption = clean($_POST['caption'] ?? '');
    $id = (int)($_POST['id'] ?? 0);

    if ($id) {
        // تعديل: الصورة اختيارية، إن لم تُرفع صورة جديدة تبقى القديمة كما هي
        $imageName = $_POST['existing_image'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $upload = uploadImage($_FILES['image'], __DIR__ . '/../assets/img/gallery', 'gallery');
            if ($upload['success']) {
                $imageName = $upload['filename'];
            } else {
                $errors[] = $upload['message'];
            }
        }

        if (empty($errors)) {
            $pdo->prepare('UPDATE gallery SET image=?, caption=? WHERE id=?')->execute([$imageName, $caption, $id]);
            setFlash('success', 'تم تحديث الصورة.');
            header('Location: gallery.php');
            exit;
        }
    } else {
        // إضافة: الصورة إلزامية
        if (empty($_FILES['image']['name'])) {
            $errors[] = 'يرجى اختيار صورة.';
        } else {
            $upload = uploadImage($_FILES['image'], __DIR__ . '/../assets/img/gallery', 'gallery');
            if ($upload['success']) {
                $pdo->prepare('INSERT INTO gallery (image, caption) VALUES (?, ?)')->execute([$upload['filename'], $caption]);
                setFlash('success', 'تمت إضافة الصورة.');
                header('Location: gallery.php');
                exit;
            } else {
                $errors[] = $upload['message'];
            }
        }
    }
}

$images = $pdo->query('SELECT * FROM gallery ORDER BY created_at DESC')->fetchAll();
?>

<div class="admin-panel">
  <h2><?php echo $editItem ? 'تعديل الصورة' : 'إضافة صورة جديدة'; ?></h2>
  <?php if (!empty($errors)): ?><div class="alert alert-error"><ul><?php foreach ($errors as $e) echo '<li>'.$e.'</li>'; ?></ul></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="inline-form">
    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
    <?php if ($editItem): ?>
      <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
      <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($editItem['image']); ?>">
    <?php endif; ?>
    <div class="form-group">
      <label><?php echo $editItem ? 'استبدال الصورة (اختياري)' : 'الصورة'; ?></label>
      <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" <?php echo $editItem ? '' : 'required'; ?>>
    </div>
    <div class="form-group">
      <label>وصف مختصر</label>
      <input type="text" name="caption" value="<?php echo htmlspecialchars($editItem['caption'] ?? ''); ?>">
    </div>
    <button class="btn btn-primary"><?php echo $editItem ? 'حفظ التعديلات' : 'رفع الصورة'; ?></button>
    <?php if ($editItem): ?><a href="gallery.php" class="btn btn-outline">إلغاء</a><?php endif; ?>
  </form>
</div>

<div class="admin-panel">
  <h2>الصور الحالية</h2>
  <div class="admin-gallery-grid">
    <?php foreach ($images as $img): ?>
      <div class="admin-gallery-item">
        <img src="../assets/img/gallery/<?php echo htmlspecialchars($img['image']); ?>" alt="">
        <p><?php echo htmlspecialchars($img['caption']); ?></p>
        <div class="actions-cell">
          <a href="gallery.php?edit=<?php echo $img['id']; ?>" class="btn btn-sm btn-outline">تعديل</a>
          <a href="gallery.php?delete=<?php echo $img['id']; ?>&csrf=<?php echo generateCsrfToken(); ?>" class="btn btn-sm btn-danger" onclick="return confirm('حذف هذه الصورة؟');">حذف</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
