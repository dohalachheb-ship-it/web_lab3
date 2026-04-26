<?php
class AdminController {
    
    // عرض لوحة التحكم الرئيسية للمدير
    public function dashboard() {
        global $pdo;
        
        // جلب بعض الإحصائيات السريعة لعرضها في اللوحة
        $stmt1 = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
        $studentCount = $stmt1->fetchColumn();

        $stmt2 = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'professor'");
        $professorCount = $stmt2->fetchColumn();

        $stmt3 = $pdo->query("SELECT COUNT(*) FROM semesters");
        $semesterCount = $stmt3->fetchColumn();

        // استدعاء واجهة العرض (View)
        require_once 'views/admin_dashboard.php';
    }

    // هنا سنضيف مستقبلاً وظائف مثل addSemester, deleteCourse... إلخ
}