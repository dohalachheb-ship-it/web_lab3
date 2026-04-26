<?php
require_once '../../config.php';
session_start();

// التحقق من تسجيل دخول الطالب
if (!isset($_SESSION['user_id'])) { 
    exit("Access Denied"); 
}

$student_id = $_SESSION['user_id'];

try {
    // تم تعديل u.full_name إلى u.name ليتطابق مع قاعدة بياناتك
    $stmt = $pdo->prepare("SELECT u.name as student_name, c.name as course_name, c.credits, g.grade 
                           FROM users u
                           JOIN grades g ON u.id = g.student_id
                           JOIN courses c ON g.course_id = c.id
                           WHERE u.id = ?");
    $stmt->execute([$student_id]);
    $data = $stmt->fetchAll();
} catch (PDOException $e) { 
    die("خطأ في جلب البيانات: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كشف نقاط - <?= htmlspecialchars($data[0]['student_name'] ?? 'طالب') ?></title>
    <style>
        body { font-family: 'Arial', sans-serif; padding: 40px; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 30px; padding-bottom: 10px; }
        .info { margin-bottom: 20px; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 12px; text-align: center; }
        th { background: #f2f2f2; }
        .footer { margin-top: 50px; text-align: left; font-style: italic; }
        .no-print { margin-top: 30px; text-align: center; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="header">
    <h1>الجمهورية الجزائرية الديمقراطية الشعبية</h1>
    <h2>وزارة التعليم العالي والبحث العلمي</h2>
    <hr>
    <h3>كشف نقاط فصلي</h3>
</div>

<div class="info">
    <p><strong>اسم الطالب(ة):</strong> <?= htmlspecialchars($data[0]['student_name'] ?? 'غير معروف') ?></p>
    <p><strong>تاريخ الاستخراج:</strong> <?= date('Y-m-d H:i') ?></p>
</div>

<table>
    <thead>
        <tr>
            <th>المادة</th>
            <th>المعامل</th>
            <th>الدرجة</th>
            <th>المجموع</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $total_points = 0; 
        $total_credits = 0;
        foreach ($data as $row): 
            $subtotal = $row['grade'] * $row['credits'];
            $total_points += $subtotal;
            $total_credits += $row['credits'];
        ?>
        <tr>
            <td><?= htmlspecialchars($row['course_name']) ?></td>
            <td><?= $row['credits'] ?></td>
            <td><?= number_format($row['grade'], 2) ?></td>
            <td><?= number_format($subtotal, 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr style="background: #f9f9f9; font-weight: bold;">
            <td colspan="2">المجموع الإجمالي</td>
            <td>---</td>
            <td><?= number_format($total_points, 2) ?></td>
        </tr>
        <tr style="background: #eee; font-weight: bold; font-size: 1.2em;">
            <td colspan="2">المعدل العام</td>
            <td colspan="2"><?= ($total_credits > 0) ? number_format($total_points / $total_credits, 2) : '0.00' ?> / 20</td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    <p>ختم وإمضاء الإدارة:</p>
    <br><br>
    <p>...........................................</p>
</div>

<div class="no-print">
    <button onclick="window.print()" style="padding: 12px 25px; background: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
        🖨️ طباعة الآن (PDF)
    </button>
    <br><br>
    <a href="dashboard.php">العودة للوحة التحكم</a>
</div>

</body>
</html>