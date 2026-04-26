<?php
// إعدادات قاعدة البيانات
$host = "localhost";
$dbname = "gpa_records"; // تأكدي أن هذا هو اسم قاعدة البيانات عندك
$username = "root";
$password = "";

try {
    // إنشاء الاتصال باستخدام PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // ضبط وضع الأخطاء لإظهار التحذيرات
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // echo "تم الاتصال بنجاح!"; // يمكنك تفعيل هذا السطر للتأكد فقط
} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>