<?php
require_once '../../config.php';
session_start();

$student_id = $_SESSION['user_id'];

// جلب الواجبات الخاصة بالمواد التي سجل فيها الطالب
$sql = "SELECT h.*, c.name as course_name 
        FROM homeworks h
        JOIN courses c ON h.course_id = c.id
        JOIN enrollments e ON c.semester_id = e.semester_id
        WHERE e.student_id = ?
        ORDER BY h.due_date ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$homeworks = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>واجباتي المنزلية</title>
    <style>
        .hw-card { background: #fff; border-left: 5px solid #3498db; padding: 15px; margin-bottom: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .due-date { color: red; font-weight: bold; }
    </style>
</head>
<body style="font-family: Arial; background: #f4f4f4; padding: 20px;">
    <h2>📝 الواجبات المطلوبة مني</h2>
    <?php foreach($homeworks as $hw): ?>
        <div class="hw-card">
            <h3><?= htmlspecialchars($hw['title']) ?> (<?= $hw['course_name'] ?>)</h3>
            <p><?= htmlspecialchars($hw['description']) ?></p>
            <p class="due-date">آخر أجل للتسليم: <?= $hw['due_date'] ?></p>
        </div>
    <?php endforeach; ?>
    <?php if(!$homeworks) echo "<p>لا توجد واجبات حالياً.</p>"; ?>
    <br><a href="dashboard.php">العودة للوحة التحكم</a>
</body>
</html>