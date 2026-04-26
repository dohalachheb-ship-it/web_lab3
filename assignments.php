<?php
require_once '../../config.php';

session_start();

// حماية الصفحة
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// 1. معالجة ربط أستاذ بمادة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['assign_course'])) {
    $professor_id = $_POST['professor_id'];
    $course_id = $_POST['course_id'];
    $semester_id = $_POST['semester_id'];

    try {
        $sql = "INSERT INTO assignments (professor_id, course_id, semester_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$professor_id, $course_id, $semester_id]);
        $message = "✅ تم ربط الأستاذ بالمادة بنجاح!";
    } catch (PDOException $e) {
        $message = "❌ خطأ: ربما تم ربط هذا الأستاذ بهذه المادة مسبقاً.";
    }
}

// 2. جلب البيانات للقوائم المنسدلة
$professors = $pdo->query("SELECT id, name FROM users WHERE role = 'professor'")->fetchAll();
$courses = $pdo->query("SELECT id, name FROM courses")->fetchAll();
$semesters = $pdo->query("SELECT id, label FROM semesters")->fetchAll();

// 3. جلب التوزيعات الحالية للعرض
$sql_list = "SELECT a.*, u.name as prof_name, c.name as course_name, s.label as sem_label 
             FROM assignments a
             JOIN users u ON a.professor_id = u.id
             JOIN courses c ON a.course_id = c.id
             JOIN semesters s ON a.semester_id = s.id";
$assignments = $pdo->query($sql_list)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>توزيع المواد على الأساتذة</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #2c3e50; color: white; min-height: 100vh; padding: 20px; position: fixed; }
        .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 12px; border-radius: 5px; }
        .main-content { margin-right: 260px; flex: 1; padding: 40px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
        select, button { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 1px solid #ddd; }
        button { background: #e67e22; color: white; border: none; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #eee; }
        .alert { padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>لوحة المدير</h2>
    <hr>
    <a href="dashboard.php">🏠 الرئيسية</a>
    <a href="professors.php">👨‍🏫 الأساتذة</a>
    <a href="courses.php">📚 المواد</a>
    <a href="assignments.php" style="background: #34495e;">🔗 توزيع المواد</a>
    <a href="../logout.php" style="color: #e74c3c;">🚪 خروج</a>
</div>

<div class="main-content">
    <h2>توزيع المواد التعليمية</h2>
    
    <?php if ($message): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <h3>🔗 ربط أستاذ بمادة دراسية</h3>
        <form method="POST">
            <label>اختر الأستاذ:</label>
            <select name="professor_id" required>
                <?php foreach($professors as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label>اختر المادة:</label>
            <select name="course_id" required>
                <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label>الفصل الدراسي:</label>
            <select name="semester_id" required>
                <?php foreach($semesters as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= $s['label'] ?></option><?php endforeach; ?>
            </select>

            <button type="submit" name="assign_course">تأكيد عملية الربط</button>
        </form>
    </div>

    <div class="card">
        <h3>📋 قائمة التوزيع الحالي</h3>
        <table>
            <thead>
                <tr>
                    <th>الأستاذ</th>
                    <th>المادة</th>
                    <th>الفصل</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['prof_name']) ?></td>
                    <td><?= htmlspecialchars($a['course_name']) ?></td>
                    <td><?= htmlspecialchars($a['sem_label']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>