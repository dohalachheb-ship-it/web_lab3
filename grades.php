<?php
require_once '../config.php';
session_start();

header('Content-Type: application/json');

// --- إعدادات الاختبار (تجاوز الحماية مؤقتاً إذا كنتِ تختبرين الرابط مباشرة) ---
$professor_id = $_SESSION['user_id'] ?? 2; // إذا لم يوجد جلسة، نعتبر الأستاذ رقم 2 هو المستخدم

// استقبال البيانات من الرابط (GET) أو من الطلبات البرمجية (JSON/POST)
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$student_id = $data['student_id'] ?? $_GET['student_id'] ?? null;
$course_id  = $data['course_id'] ?? $_GET['course_id'] ?? null;
$grade      = $data['grade'] ?? $_GET['grade'] ?? null;

if ($student_id && $course_id && $grade !== null) {
    try {
        // التحقق من وجود سجل مسبق لتحديثه أو إنشاء واحد جديد
        $check = $pdo->prepare("SELECT id FROM grades WHERE student_id = ? AND course_id = ?");
        $check->execute([$student_id, $course_id]);
        
        if ($check->rowCount() > 0) {
            // تحديث الدرجة
            $stmt = $pdo->prepare("UPDATE grades SET grade = ?, professor_id = ?, entered_at = NOW() WHERE student_id = ? AND course_id = ?");
            $stmt->execute([$grade, $professor_id, $student_id, $course_id]);
            $msg = "تم تحديث الدرجة بنجاح";
        } else {
            // إضافة درجة جديدة (جلب semester_id تلقائياً من جدول المواد)
            $get_sem = $pdo->prepare("SELECT semester_id FROM courses WHERE id = ?");
            $get_sem->execute([$course_id]);
            $semester_id = $get_sem->fetchColumn() ?: 1;

            $stmt = $pdo->prepare("INSERT INTO grades (student_id, course_id, semester_id, professor_id, grade, entered_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$student_id, $course_id, $semester_id, $professor_id, $grade]);
            $msg = "تم إضافة الدرجة بنجاح";
        }

        echo json_encode([
            "status" => "success",
            "message" => $msg,
            "data" => ["student_id" => $student_id, "course_id" => $course_id, "grade" => $grade]
        ]);

    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "خطأ في قاعدة البيانات: " . $e->getMessage()]);
    }
} else {
    echo json_encode([
        "status" => "error", 
        "message" => "بيانات ناقصة. للاختبار استعملي الرابط مع ?student_id=3&course_id=1&grade=18"
    ]);
}