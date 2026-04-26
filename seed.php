<?php
require_once 'config.php';

try {
    // 1. إضافة حساب أدمن تجريبي
    $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['مدير النظام', 'admin@gpa.com', $admin_pass, 'admin']);

    // 2. إضافة حساب أستاذ تجريبي
    $prof_pass = password_hash('prof123', PASSWORD_DEFAULT);
    $stmt->execute(['الأستاذ أحمد', 'prof@gpa.com', $prof_pass, 'professor']);

    // 3. إضافة حساب طالب تجريبي
    $student_pass = password_hash('student123', PASSWORD_DEFAULT);
    $stmt->execute(['الطالبة ضحى', 'student@gpa.com', $student_pass, 'student']);

    echo "✅ تم إضافة المستخدمين التجريبيين بنجاح!<br>";
    echo "---<br>";
    echo "بيانات الدخول (الأدمن): admin@gpa.com / admin123<br>";
    echo "بيانات الدخول (الأستاذ): prof@gpa.com / prof123<br>";
    echo "بيانات الدخول (الطالب): student@gpa.com / student123";

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo "⚠️ المستخدمون موجودون مسبقاً في قاعدة البيانات.";
    } else {
        echo "❌ خطأ: " . $e->getMessage();
    }
}
?>