<?php
require_once '../../config.php';
session_start();

// حماية الصفحة
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// 1. معالجة التسجيل (استخدام semester_id بدلاً من course_id بناءً على قاعدة بياناتك)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll_student'])) {
    $student_id = $_POST['student_id'];
    $semester_id = $_POST['semester_id']; // تعديل الاسم هنا

    try {
        $sql = "INSERT INTO enrollments (student_id, semester_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id, $semester_id]);
        $message = "✅ تم تسجيل الطالب في الفصل الدراسي بنجاح!";
    } catch (PDOException $e) {
        $message = "❌ خطأ: " . $e->getMessage();
    }
}

// 2. جلب القوائم
$students = $pdo->query("SELECT id, name FROM users WHERE role = 'student'")->fetchAll();
$semesters = $pdo->query("SELECT id, label FROM semesters")->fetchAll(); // جلب الفصول الدراسية

// 3. جلب الجدول للعرض (تعديل الاستعلام ليتوافق مع الأعمدة الموجودة في الصورة)
$sql_list = "SELECT enrollments.id, users.name as student_name, semesters.label as semester_name 
             FROM enrollments 
             INNER JOIN users ON enrollments.student_id = users.id 
             INNER JOIN semesters ON enrollments.semester_id = semesters.id";
$enrollments = $pdo->query($sql_list)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الطلاب</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        select, button { width: 100%; padding: 12px; margin: 10px 0; border-radius: 5px; border: 1px solid #ddd; }
        button { background: #2980b9; color: white; border: none; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 12px; border: 1px solid #eee; text-align: center; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="card">
        <h3>تسجيل طالب في فصل دراسي</h3>
        <form method="POST">
            <label>الطالب:</label>
            <select name="student_id" required>
                <?php foreach($students as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label>الفصل الدراسي:</label>
            <select name="semester_id" required>
                <?php foreach($semesters as $sem): ?>
                    <option value="<?= $sem['id'] ?>"><?= $sem['label'] ?></option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" name="enroll_student">تأكيد التسجيل</button>
        </form>
    </div>

    <div class="card">
        <h3>قائمة التسجيلات</h3>
        <table>
            <tr>
                <th>المعرف</th>
                <th>اسم الطالب</th>
                <th>الفصل الدراسي</th>
            </tr>
            <?php foreach ($enrollments as $e): ?>
            <tr>
                <td><?= $e['id'] ?></td>
                <td><?= $e['student_name'] ?></td>
                <td><?= $e['semester_name'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>