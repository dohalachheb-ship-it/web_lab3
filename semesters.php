<?php
session_start();
// تفعيل عرض الأخطاء للمساعدة في التصحيح
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../config.php';
require_once '../../models/Semester.php';

// التأكد من أن المستخدم "أدمن"
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$semesterModel = new Semester($pdo);
// تصحيح: تعريف المتغير بقيمة فارغة ليتجنب المتصفح إظهار "Warning"
$message = ""; 

// معالجة طلب الإضافة عند الضغط على الزر
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_semester'])) {
    $label = $_POST['label'];
    $year = $_POST['academic_year'];
    
    // تأكدي أن دالة create في المودل ترجع true عند النجاح
    if ($semesterModel->create($label, $year)) {
        $message = "✅ تم إضافة الفصل الدراسي بنجاح!";
    } else {
        $message = "❌ حدث خطأ أثناء الإضافة.";
    }
}

// جلب قائمة الفصول لعرضها في الجدول
$semesters = $semesterModel->getAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الفصول الدراسية</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            display: flex; 
            min-height: 100vh;
        }

        /* تنسيق القائمة الجانبية السوداء */
        .sidebar {
            width: 250px;
            background-color: #212529; /* لون أسود احترافي */
            color: #adb5bd;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar h3 {
            color: #fff;
            text-align: center;
            padding: 20px 0;
            margin: 0;
            background: #1a1d20;
        }

        .sidebar a {
            display: block;
            color: #c2c7d0;
            padding: 15px 20px;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #343a40;
            color: #fff;
            border-left: 4px solid #007bff;
        }

        .sidebar a.active {
            background-color: #007bff;
            color: white;
            border-left: 4px solid #fff;
        }

        /* محتوى الصفحة */
        .main-content {
            flex-grow: 1;
            padding: 40px;
        }

        .header h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .form-card, .table-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn-add {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: right;
        }

        th { background: #f8f9fa; color: #555; }

        .alert {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h3>نظام GPA</h3>
        <a href="dashboard.php">🏠 الرئيسية</a>
        <a href="semesters.php" class="active">📅 الفصول الدراسية</a>
        <a href="courses.php">📚 المواد الدراسية</a>
        <a href="professors.php">👨‍🏫 الأساتذة</a>
        <a href="students.php">👨‍🎓 الطلاب</a>
        <a href="../login.php" style="margin-top: 50px; color: #ff6b6b;">🚪 خروج</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>إدارة الفصول الدراسية</h1>
        </div>

        <?php if(!empty($message)): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h3>إضافة فصل دراسي جديد</h3>
            <form method="POST">
                <div class="input-group">
                    <input type="text" name="label" placeholder="مثال: S1" required>
                    <input type="text" name="academic_year" placeholder="مثال: 2024/2025" required>
                    <button type="submit" name="add_semester" class="btn-add">حفظ الفصل</button>
                </div>
            </form>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>رقم</th>
                        <th>المسمى</th>
                        <th>السنة</th>
                        <th>تاريخ الإضافة</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($semesters)): ?>
                        <tr><td colspan="4">لا توجد فصول حالياً.</td></tr>
                    <?php else: ?>
                        <?php foreach ($semesters as $sem): ?>
                        <tr>
                            <td><?php echo $sem['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($sem['label']); ?></strong></td>
                            <td><?php echo htmlspecialchars($sem['academic_year']); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($sem['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>