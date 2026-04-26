<?php
require_once '../../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $due_date = $_POST['due_date'];
    $course_id = $_POST['course_id'];
    $prof_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO homeworks (course_id, professor_id, title, description, due_date) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$course_id, $prof_id, $title, $desc, $due_date])) {
        echo "<script>alert('تمت إضافة الواجب بنجاح'); window.location='dashboard.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head><meta charset="UTF-8"><title>إضافة واجب</title></head>
<body style="font-family: Arial; padding: 20px;">
    <h2>إضافة واجب منزلي جديد</h2>
    <form method="POST">
        <input type="text" name="title" placeholder="عنوان الواجب" required><br><br>
        <textarea name="description" placeholder="تفاصيل الواجب"></textarea><br><br>
        <label>تاريخ التسليم:</label>
        <input type="date" name="due_date" required><br><br>
        <select name="course_id">
            <option value="1">الخوارزميات</option>
            <option value="2">قاعدة البيانات</option>
        </select><br><br>
        <button type="submit">نشر الواجب</button>
    </form>
</body>
</html>