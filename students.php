<?php
require_once '../../config.php';
require_once '../../models/User.php';

session_start();
// حماية الصفحة: التأكد من أن المستخدم مدير
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// معالجة إضافة طالب جديد
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // يفضل تشفيره لاحقاً بـ password_hash
    $role = 'student';

    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([$name, $email, $password, $role]);
        $message = "تم إضافة الطالب بنجاح!";
    } catch (PDOException $e) {
        $message = "خطأ: الإيميل مسجل مسبقاً.";
    }
}

// جلب قائمة الطلاب لعرضهم في الجدول
$students = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'student'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الطلاب</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; display: flex; }
        .sidebar { width: 200px; background: #333; color: #fff; min-height: 100vh; padding: 20px; }
        .main { flex: 1; padding: 30px; }
        .form-card { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
        button { background: #007bff; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #eee; text-align: center; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>لوحة المدير</h3>
    <hr>
    <a href="dashboard.php" style="color:white; text-decoration:none;">الرئيسية</a><br><br>
    <a href="students.php" style="color:white; font-weight:bold;">الطلاب</a>
</div>

<div class="main">
    <h2>إدارة حسابات الطلاب</h2>

    <?php if ($message): ?>
        <div style="background:#d4edda; color:#155724; padding:10px; margin-bottom:20px;"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="form-card">
        <h3>إضافة طالب جديد</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="اسم الطالب الكامل" required>
            <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            <input type="password" name="password" placeholder="كلمة المرور" required>
            <button type="submit" name="add_student">حفظ البيانات</button>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>الاسم</th>
                <th>الإيميل</th>
                <th>تاريخ الإنشاء</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
            <tr>
                <td><?php echo $s['id']; ?></td>
                <td><?php echo $s['name']; ?></td>
                <td><?php echo $s['email']; ?></td>
                <td><?php echo $s['created_at']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>