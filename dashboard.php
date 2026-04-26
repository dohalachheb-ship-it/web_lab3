<?php
require_once '../../config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php"); exit();
}

$student_id = $_SESSION['user_id'];
$total_pts = 0; $total_coeffs = 0;

// استعلام جلب المواد والدرجات
$sql = "SELECT c.name as course_name, c.credits, g.grade 
        FROM enrollments e
        INNER JOIN semesters s ON e.semester_id = s.id
        INNER JOIN courses c ON c.semester_id = s.id
        LEFT JOIN grades g ON (g.student_id = e.student_id AND g.course_id = c.id)
        WHERE e.student_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll();

// --- كود حساب الترتيب (Rank) ---
try {
    $rank_sql = "SELECT student_id, (SUM(grade * credits) / SUM(credits)) as student_avg 
                 FROM grades 
                 JOIN courses ON grades.course_id = courses.id
                 GROUP BY student_id 
                 ORDER BY student_avg DESC";
    
    $stmt_rank = $pdo->query($rank_sql);
    $ranks = $stmt_rank->fetchAll(PDO::FETCH_ASSOC);

    $my_rank = 0;
    foreach ($ranks as $index => $row) {
        if ($row['student_id'] == $student_id) {
            $my_rank = $index + 1;
            break;
        }
    }
    $total_students = count($ranks);
} catch (PDOException $e) {
    $my_rank = "---";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لوحة الطالب</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f7f9; padding: 30px; }
        .container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        th { background: #3498db; color: white; }
        
        .summary-box { 
            margin-top: 20px; 
            padding: 20px; 
            background: #e8f4fd; 
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            text-decoration: none;
            background: #27ae60;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-print:hover { background: #219150; }
        .rank-badge { color: #2c3e50; font-size: 0.9em; margin-top: 5px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <h2>📊 كشف نقاط الطالبة: <?= htmlspecialchars($_SESSION['name']) ?></h2>
    
    <table>
        <thead>
            <tr>
                <th>المادة</th>
                <th>المعامل</th>
                <th>الدرجة</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($courses as $c): ?>
            <?php 
                if($c['grade'] !== null) { 
                    $total_pts += ($c['grade'] * $c['credits']); 
                    $total_coeffs += $c['credits']; 
                } 
            ?>
            <tr>
                <td><?= htmlspecialchars($c['course_name']) ?></td>
                <td><?= htmlspecialchars($c['credits']) ?></td>
                <td><strong><?= $c['grade'] ?? '---' ?></strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary-box">
        <div>
            <strong>المعدل الفصلي: </strong>
            <span style="font-size: 1.5em; color: green; font-weight: bold;">
                <?= $total_coeffs > 0 ? number_format($total_pts / $total_coeffs, 2) : '0.00' ?> / 20
            </span>
            <span class="rank-badge">
                الترتيب في الدفعة: <b>#<?= $my_rank ?></b> من أصل <?= $total_students ?> طلاب
            </span>
        </div>

        <a href="print_grades.php" target="_blank" class="btn-print">
            🖨️ تحميل كشف النقاط (PDF)
        </a>
    </div>
</div>

</body>
</html>