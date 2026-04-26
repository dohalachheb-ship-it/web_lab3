<?php
require_once '../../config.php';
require_once '../../models/User.php';

session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_professor'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password']; 
    $role = 'professor';

    try {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $password, $role]);
        $message = "✅ تمت إضافة الأستاذ بنجاح!";
    } catch (PDOException $e) {
        $message = "❌ خطأ: البريد الإلكتروني موجود مسبقاً.";
    }
}

$stmt = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'professor'");
$professors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الأساتذة</title>
    <style>
        /* تنسيقات لإصلاح تداخل الشريط الجانبي */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f7f6;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 260px;
            background: #2c3e50; /* لون احترافي داكن */
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .sidebar h2 {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #3498db;
        }

        .sidebar a {
            display: block;
            color: #bdc3c7;
            text-decoration: none;
            padding: 12px 15px;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background: #34495e;
            color: white;
        }

        .main-content {
            margin-right: 260px; /* دفع المحتوى لليسار بعيداً عن الشريط الثابت */
            flex: 1;
            padding: 40px;
            background: #f4f7f6;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            border: 1px solid #e1e8ed;
        }

        .form-group { margin-bottom: 15px; }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 8px;
            box-sizing: border-box; /* يمنع خروج المدخلات عن الإطار */
            font-size: 14px;
        }

        .btn-add {
            background: #27ae60;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            font-size: 16px;
        }

        .btn-add:hover { background: #219150; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th { background: #f8f9fa; color: #7f8c8d; font-size: 13px; text-transform: uppercase; }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>نظام إدارة المنح</h2>
    <p style="text-align: center; font-size: 0.9rem;">مرحباً، مدير النظام</p>
    <hr style="border: 0.5px solid #3e4f5f;">
    <a href="dashboard.php">🏠 الرئيسية</a>
    <a href="students.php">👨‍🎓 الطلاب</a>
    <a href="professors.php" class="active">👨‍🏫 الأساتذة</a>
    <a href="../logout.php" style="color: #e74c3c;">🚪 خروج</a>
</div>

<div class="main-content">
    <h2 style="color: #2c3e50; margin-bottom: 25px;">إدارة هيئة التدريس</h2>

    <?php if ($message): ?>
        <div class="alert"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <h3 style="margin-top: 0;">➕ إضافة أستاذ جديد</h3>
        <form method="POST">
            <div class="form-group"><input type="text" name="name" placeholder="الاسم الكامل للأستاذ" required></div>
            <div class="form-group"><input type="email" name="email" placeholder="البريد الإلكتروني" required></div>
            <div class="form-group"><input type="password" name="password" placeholder="كلمة المرور" required></div>
            <button type="submit" name="add_professor" class="btn-add">حفظ البيانات في النظام</button>
        </form>
    </div>

    <div class="card">
        <h3>📋 قائمة الأساتذة المسجلين</h3>
        <table>
            <thead>
                <tr>
                    <th>المعرف (ID)</th>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>تاريخ الإضافة</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($professors as $prof): ?>
                <tr>
                    <td><strong>#<?php echo $prof['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($prof['name']); ?></td>
                    <td><?php echo htmlspecialchars($prof['email']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($prof['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($professors)): ?>
                    <tr><td colspan="4" style="color: #95a5a6;">لا يوجد أساتذة مسجلين حالياً.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>