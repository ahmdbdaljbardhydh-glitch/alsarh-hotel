<?php
// صفحة تسجيل الخروج — تُنهي جلسة المستخدم الحالية بالكامل بأمان
require_once __DIR__ . '/includes/functions.php';

// إفراغ كل بيانات الجلسة الحالية ة
$_SESSION = [];

// حذف كوكيز الجلسة م،
// بتعيين وقت انتهاء صلاحيتها في الماضي (time() - 42000)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// session_destroy(): يدمّر ملف الجلسة نفسه من على السيرفر نهائيًا
session_destroy();

// إعادة توجيه المستخدم لصفحة تسجيل الدخول بعد إتمام الخروج
header('Location: login.php');
exit;
