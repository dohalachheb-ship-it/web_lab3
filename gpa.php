<?php
// تأكدي من أن المسار لملف config صحيح
require_once '../config.php';
session_start();

header('Content-Type: application/json');

// استقبال ID الطالب من الرابط (?id=3) أو من الجلسة
$student_id = $_GET['id'] ?? $_SESSION['user_id'] ?? null;

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'يرجى تحديد رقم الطالب']);
    exit();
}

try {
    // استعلام دقيق يربط الجداول بناءً على الصور التي أرسلتِها
    // نستخدم SUM(grade * credits) لحساب النقاط الإجمالية
    $sql = "SELECT SUM(g.grade * c.credits) as total_points, SUM(c.credits) as total_credits 
            FROM grades g
            JOIN courses c ON g.course_id = c.id
            WHERE g.student_id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // التحقق من وجود نتائج ومعاملات أكبر من صفر
    if ($result && $result['total_credits'] > 0) {
        $gpa = $result['total_points'] / $result['total_credits'];
        
        echo json_encode([
            'status' => 'success',
            'student_id' => $student_id,
            'gpa' => number_format($gpa, 2),
            'debug_info' => [
                'points' => $result['total_points'],
                'credits' => $result['total_credits']
            ]
        ]);
    } else {
        // في حال لم يجد بيانات، نخرج رسالة توضح السبب
        echo json_encode([
            'status' => 'success',
            'gpa' => "0.00",
            'message' => "لا توجد درجات مرصودة للطالب رقم ($student_id) في قاعدة البيانات"
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
}