<?php
require_once '../../config.php';
session_start();

// حماية الصفحة
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professor') {
    header("Location: ../login.php");
    exit();
}

$professor_id = $_SESSION['user_id'];
$message = "";

// 1. معالجة حفظ الدرجة (استخدام اسم العمود grade)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_grade'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $semester_id = $_POST['semester_id'];
    $grade = $_POST['grade']; // تم التعديل هنا

    try {
        $check = $pdo->prepare("SELECT id FROM grades WHERE student_id = ? AND course_id = ?");
        $check->execute([$student_id, $course_id]);
        
        if ($check->rowCount() > 0) {
            $sql = "UPDATE grades SET grade = ?, professor_id = ? WHERE student_id = ? AND course_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$grade, $professor_id, $student_id, $course_id]);
        } else {
            $sql = "INSERT INTO grades (student_id, course_id, semester_id, professor_id, grade) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$student_id, $course_id, $semester_id, $professor_id, $grade]);
        }
        $message = "✅ تم حفظ الدرجة بنجاح!";
    } catch (PDOException $e) {
        $message = "❌ خطأ: " . $e->getMessage();
    }
}

// 2. جلب المواد المسندة للأستاذ
$stmt_c = $pdo->prepare("SELECT c.id, c.name, a.semester_id FROM assignments a 
                         INNER JOIN courses c ON a.course_id = c.id 
                         WHERE a.professor_id = ?");
$stmt_c->execute([$professor_id]);
$assigned_courses = $stmt_c->fetchAll();

// 3. جلب الطلاب (تعديل الاستعلام ليتوافق مع الصورة الأخيرة)
$enrollments = [];
if (isset($_GET['course_id'])) {
    $selected_course = $_GET['course_id'];
    // جلب الـ semester_id الخاص بالمادة المختارة
    $stmt_sem = $pdo->prepare("SELECT semester_id FROM courses WHERE id = ?");
    $stmt_sem->execute([$selected_course]);
    $sem_data = $stmt_sem->fetch();
    $current_semester = $sem_data['semester_id'];

    $sql_students = "SELECT u.id as student_id, u.name as student_name, g.grade 
                     FROM users u
                     INNER JOIN enrollments e ON u.id = e.student_id
                     LEFT JOIN grades g ON (g.student_id = u.id AND g.course_id = ?)
                     WHERE e.semester_id = ? AND u.role = 'student'";
    
    $stmt_s = $pdo->prepare($sql_students);
    $stmt_s->execute([$selected_course, $current_semester]);
    $enrollments = $stmt_s->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رصد الدرجات</title>
    <style>
        body { font-family: sans-serif; background: #f4f7f6; padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2>لوحة الأستاذ</h2>
        <?php if($message) echo "<p class='success'>$message</p>"; ?>
        <form method="GET">
            <select name="course_id" onchange="this.form.submit()">
                <option value="">-- اختر المادة --</option>
                <?php foreach ($assigned_courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= isset($_GET['course_id']) && $_GET['course_id'] == $c['id'] ? 'selected' : '' ?>>
                        <?= $c['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (isset($_GET['course_id'])): ?>
    <div class="card">
        <table>
            <tr><th>الطالب</th><th>الدرجة الحالية</th><th>رصد درجة</th></tr>
            <?php foreach ($enrollments as $row): ?>
            <tr>
                <td><?= $row['student_name'] ?></td><td><?=$row['grade'] ?? 'غير مرصودة' ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                        <input type="hidden" name="course_id" value="<?= $_GET['course_id'] ?>">
                        <input type="hidden" name="semester_id" value="<?= $current_semester ?>">
                        <input type="number" name="grade" step="0.01" min="0" max="20" required>
                        <button type="submit" name="save_grade">حفظ</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>
</body>
</html>