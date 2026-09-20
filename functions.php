<?php
/**
 * ==============================================================
 * دوال مساعدة عامة + إدارة الجلسات + الأمان
 * فندق الصرح الذهبي
 * ==============================================================
 * هذا الملف هو "قلب" المشروع: يُضمَّن في بداية كل صفحة تقريبًا،
 * ويوفر الاتصال بقاعدة البيانات + كل الدوال المشتركة بين الصفحات
 * (تنظيف المدخلات، التحقق من الصلاحيات، الحماية من CSRF...).
 */

// session_start(): دالة PHP جاهزة تبدأ "جلسة" (Session) جديدة للزائر
// أو تستأنف جلسته الحالية إن كانت موجودة مسبقًا (عبر كوكيز PHPSESSID).
// نتحقق أولًا أن الجلسة لم تبدأ من قبل (PHP_SESSION_NONE) لتفادي
// تحذير PHP الذي يظهر عند استدعاء session_start() أكثر من مرة.


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين ملف الاتصال بقاعدة البيانات ليصبح المتغير $pdo متاحًا
// تلقائيًا في أي ملف يقوم بتضمين functions.php هذا.
require_once __DIR__ . '/db.php';

/* ==================================================================
   تنظيف المدخلات القادمة من المستخدم (لمنع هجمات XSS وحقن الأكواد)
   ================================================================== */
function clean($data) {
    $data = trim($data ?? '');

    $data = stripslashes($data);

    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

    return $data;
}

/* ==================================================================
   التحقق من حالة تسجيل الدخول والصلاحيات
   ================================================================== */

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && ($_SESSION['user_type'] ?? '') === 'admin';
}

function requireLogin($redirect = '../login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

// نفس فكرة requireLogin لكن خاصة بصفحات لوحة تحكم الأدمن فقط
function requireAdmin($redirect = '../login.php') {
    if (!isAdmin()) {
        header('Location: ' . $redirect);
        exit;
    }
}

/* ==================================================================
   ================================================================== */

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        // random_bytes(32): دالة PHP تولّد 32 بايت عشوائية آمنة تشفيريًا
        // bin2hex(): تحوّل تلك البايتات إلى نص سداسي عشري قابل للطباعة
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    // hash_equals(): مقارنة نصّين بطريقة آمنة زمنيًا (Timing-Safe)
    // تمنع هجمات "قياس الزمن" لتخمين الرمز الصحيح حرفًا بحرف
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/* ==================================================================
   رسائل التنبيه المؤقتة (Flash Messages) — تظهر مرة واحدة فقط
   مثال: "تم إنشاء حسابك بنجاح" بعد التسجيل مباشرة
   ================================================================== */

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        // unset(): نحذف الرسالة من الجلسة فور قراءتها حتى لا تتكرر
        // إذا قام المستخدم بتحديث الصفحة (Refresh) لاحقًا
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* ==================================================================
   جلب بيانات المستخدم الحالي من قاعدة البيانات
   ================================================================== */
function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;

    // prepare(): يجهّز نص الاستعلام مع علامة استفهام (؟) بدل القيمة الحقيقية
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');

    // execute([...]): يمرر القيمة الحقيقية بشكل منفصل تمامًا عن نص SQL،
    // وهذا هو ما يمنع هجمات SQL Injection مهما كانت القيمة المُدخلة
    $stmt->execute([$_SESSION['user_id']]);

    // fetch(): يُرجع صفًا واحدًا فقط من نتيجة الاستعلام كمصفوفة
    return $stmt->fetch();
}

/* ==================================================================
   التحقق من صحة صيغة البريد الإلكتروني
   ================================================================== */
function isValidEmail($email) {
    // filter_var مع FILTER_VALIDATE_EMAIL: دالة PHP جاهزة تتحقق فعليًا
    // من صحة تركيب البريد الإلكتروني (وليس مجرد وجود علامة @)
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/* ==================================================================
   رفع الصور بأمان (تُستخدم لصورة المستخدم، صورة الغرفة، صور المعرض...)
   ================================================================== */
function uploadImage($file, $targetDir, $prefix = 'img') {
    // قائمة الامتدادات المسموح بها فقط (لمنع رفع ملفات .php ضارة مثلاً)
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    // pathinfo(..., PATHINFO_EXTENSION): يستخرج امتداد الملف من اسمه
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'نوع الملف غير مسموح به (jpg, jpeg, png, webp فقط)'];
    }

    // التأكد أن حجم الصورة لا يتجاوز 3 ميغابايت
    if ($file['size'] > 3 * 1024 * 1024) {
        return ['success' => false, 'message' => 'حجم الصورة كبير جدًا (الحد الأقصى 3 ميغابايت)'];
    }

    // إنشاء المجلد الهدف تلقائيًا إن لم يكن موجودًا
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // uniqid(): يولّد اسم ملف عشوائي فريد حتى لا تتكرر أسماء الصور
    // أو يستطيع أحد تخمين اسم صورة موجودة والوصول إليها مباشرة
    $newName = $prefix . '_' . uniqid() . '.' . $ext;
    $destination = rtrim($targetDir, '/') . '/' . $newName;

    // move_uploaded_file(): الدالة الوحيدة الآمنة في PHP لنقل ملف
    // مرفوع فعليًا (تتأكد داخليًا أن الملف رُفع عبر HTTP POST حقيقي)
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $newName];
    }
    return ['success' => false, 'message' => 'فشل رفع الصورة'];
}

/* ==================================================================
   تنسيق التاريخ لعرضه بصيغة عربية مقروءة (مثال: 2026/09/18)
   ================================================================== */
function formatDate($date) {
    // strtotime(): يحوّل نص التاريخ إلى timestamp رقمي
    // date('Y/m/d', ...): يعيد تنسيقه بالشكل المطلوب
    return date('Y/m/d', strtotime($date));
}

/* ==================================================================
   جلب صورة خلفية رأس الصفحة (Hero Banner) التي يرفعها الأدمن
   لكل صفحة رئيسية في الموقع (الرئيسية، الغرف، الخدمات...)
   ================================================================== */
function getPageBanner($pdo, $pageKey) {
    $stmt = $pdo->prepare('SELECT image FROM page_banners WHERE page_key = ?');
    $stmt->execute([$pageKey]);
    $row = $stmt->fetch();

    // إن لم توجد صورة مرفوعة بعد لهذه الصفحة، نُرجع null
    // وستُستخدم الخلفية الافتراضية (اللون الغامق) المُعرَّفة في CSS
    return ($row && $row['image']) ? $row['image'] : null;
}
