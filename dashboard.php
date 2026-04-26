<?php
require_once '../../config.php';
session_start();

// حماية الصفحة للمدير فقط
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

try {
    // جلب الإحصائيات الحقيقية
    $count_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
    $count_profs = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'professor'")->fetchColumn();
    $count_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $count_enrolls = $pdo->query("SELECT COUNT(*) FROM enrollments")->fetchColumn();

} catch (PDOException $e) {
    die("خطأ في الاتصال: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة الإدارة - نظام GPA</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f7f9; margin: 0; padding: 20px; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; border-bottom: 5px solid; }
        .card .val { font-size: 30px; font-weight: bold; display: block; margin: 10px 0; }
        
        .nav-section { margin-top: 40px; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; margin-top: 20px; }
        
        .nav-link { text-decoration: none; padding: 15px; background: #f8f9fa; color: #2c3e50; border: 1px solid #dee2e6; border-radius: 10px; text-align: center; font-weight: bold; transition: 0.3s; }
        .nav-link:hover { background: #3498db; color: white; border-color: #3498db; transform: translateY(-3px); }
        
        .logout-btn { background: #e74c3c; color: white; padding: 8px 18px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>

<div class="header">
    <h2>لوحة التحكم الإدارية 🛠️</h2>
    <a href="../logout.php" class="logout-btn">تسجيل الخروج</a>
</div>

<div class="stats-grid">
    <div class="card" style="border-color: #27ae60;">
        <span class="val"><?= $count_students ?></span>
        <span>إجمالي الطلاب</span>
    </div>
    <div class="card" style="border-color: #e67e22;">
        <span class="val"><?= $count_profs ?></span>
        <span>إجمالي الأساتذة</span>
    </div>
    <div class="card" style="border-color: #9b59b6;">
        <span class="val"><?= $count_courses ?></span>
        <span>المواد الدراسية</span>
    </div>
    <div class="card" style="border-color: #3498db;">
        <span class="val"><?= $count_enrolls ?></span>
        <span>عمليات التسجيل</span>
    </div>
</div>

<div class="nav-section">
    <h3 style="margin-top:0; color: #34495e; border-right: 4px solid #3498db; padding-right: 10px;">📋 القائمة البرمجية للمدير</h3>
    <div class="nav-grid">
        <a href="students.php" class="nav-link">👨‍🎓 إدارة الطلاب</a>
        <a href="professors.php" class="nav-link">👨‍🏫 إدارة الأساتذة</a>
        <a href="semesters.php" class="nav-link">📅 إدارة الفصول</a>
        <a href="courses.php" class="nav-link">📚 إدارة المواد</a>
        <a href="enrollments.php" class="nav-link">📝 تسجيل الطلاب</a>
        <a href="assignments.php" class="nav-link">🔗 توزيع المواد</a>
    </div>
</div>

</body>
</html>