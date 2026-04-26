<?php
require_once '../../config.php';

session_start();

// حماية الصفحة: التأكد من أن المستخدم "أدمن"
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// 1. معالجة إضافة مادة جديدة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $name = $_POST['name'];
    $credits = $_POST['credits'];
    $semester_id = $_POST['semester_id'];

    try {
        $sql = "INSERT INTO courses (name, credits, semester_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $credits, $semester_id]);
        $message = "✅ تمت إضافة المادة بنجاح!";
    } catch (PDOException $e) {
        $message = "❌ خطأ في الإضافة: " . $e->getMessage();
    }
}

// 2. جلب الفصول الدراسية للقائمة المنسدلة (استخدام عمود label كما في صورتك)
$semesters = $pdo->query("SELECT * FROM semesters")->fetchAll(PDO::FETCH_ASSOC);

// 3. جلب المواد المسجلة مع ربطها باسم الفصل الصحيح (label)
// قمت بتغيير semesters.name إلى semesters.label ليناسب قاعدة بياناتك
$sql_courses = "SELECT courses.*, semesters.label as semester_name 
                FROM courses 
                LEFT JOIN semesters ON courses.semester_id = semesters.id";
$courses = $pdo->query($sql_courses)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المواد الدراسية</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; margin: 0; }
        .sidebar { width: 260px; background: #2c3e50; color: white; min-height: 100vh; padding: 20px; position: fixed; }
        .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 12px; border-radius: 5px; margin-bottom: 5px;}
        .sidebar a:hover { background: #34495e; color: white; }
        .main-content { margin-right: 260px; flex: 1; padding: 40px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; }
        input, select { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .btn-add { background: #3498db; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; font-size: 16px;}
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; }
        th, td { padding: 15px; text-align: center; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #7f8c8d; font-size: 14px;}
        .alert { padding: 15px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #c3e6cb;}
    </style>
</head>
<body>

<div class="sidebar">
    <h2>لوحة المدير</h2>
    <hr style="border: 0.5px solid #3e4f5f;">
    <a href="dashboard.php">🏠 الرئيسية</a>
    <a href="students.php">👨‍🎓 الطلاب</a>
    <a href="professors.php">👨‍🏫 الأساتذة</a>
    <a href="courses.php" style="background: #34495e; color: white;">📚 المواد الدراسية</a>
    <a href="../logout.php" style="color: #e74c3c;">🚪 خروج</a>
</div>

<div class="main-content">
    <h2 style="color: #2c3e50;">إدارة المواد الدراسية</h2>

    <?php if ($message): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <h3 style="margin-top: 0;">➕ إضافة مادة جديدة</h3>
        <form method="POST">
            <input type="text" name="name" placeholder="اسم المادة (مثلاً: الخوارزميات)" required>
            <input type="number" name="credits" placeholder="عدد الأرصدة (المعامل)" required min="1">
            
            <label>اختر الفصل الدراسي:</label>
            <select name="semester_id" required>
                <?php foreach($semesters as $sem): ?>
                    <option value="<?php echo $sem['id']; ?>"><?php echo $sem['label']; ?></option> 
                <?php endforeach; ?>
            </select><button type="submit" name="add_course" class="btn-add">حفظ المادة في النظام</button>
        </form>
    </div>

    <div class="card">
        <h3>📋 قائمة المواد المسجلة</h3>
        <table>
            <thead>
                <tr>
                    <th>المعرف</th>
                    <th>اسم المادة</th>
                    <th>الأرصدة</th>
                    <th>الفصل الدراسي</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                <tr>
                    <td><strong>#<?php echo $c['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($c['name']); ?></td>
                    <td><?php echo $c['credits']; ?></td>
                    <td><?php echo htmlspecialchars($c['semester_name']); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($courses)): ?>
                    <tr><td colspan="4" style="color: #95a5a6;">لا توجد مواد مضافة بعد.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>