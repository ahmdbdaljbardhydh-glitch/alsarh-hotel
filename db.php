<?php
/**
 * ==============================================================
 * ملف الاتصال بقاعدة البيانات
 * فندق الصرح الذهبي
 * ==============================================================
 * هذا الملف مسؤول فقط عن فتح اتصال واحد بقاعدة البيانات (MySQL)
 */

$DB_HOST = 'localhost'; // عنوان خادم قاعدة البيانات
$DB_NAME = 'alsarh_hotel'; // اسم قاعدة البيانات
$DB_USER = 'root'; // اسم مستخدم قاعدة البيانات
$DB_PASS = ''; // كلمة مرور قاعدة البيانات (فارغة افتراضيًا في XAMPP)

try {
    // PDO: كائن جاهز في PHP للتعامل مع قواعد البيانات بأمان وسهولة
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            // ERRMODE_EXCEPTION: أي خطأ في قاعدة البيانات يُطلق استثناءً
            // (Exception) واضحًا بدل تجاهله بصمت
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

            // FETCH_ASSOC: نتائج أي استعلام تُعاد كمصفوفة مفاتيحها
            // أسماء الأعمدة (مثل $row['full_name']) بدل أرقام الأعمدة
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // EMULATE_PREPARES = false: يجبر PDO على استخدام الاستعلامات
            // المحضّرة الحقيقية من MySQL نفسه (أكثر أمانًا ودقة)
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // إن فشل الاتصال (مثل: قاعدة البيانات غير موجودة، أو بيانات دخول خاطئة)
    die('فشل الاتصال بقاعدة البيانات: ' . $e->getMessage());
}
