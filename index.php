<?php
// 1. بدء الجلسة (Session) [cite: 97]
session_start();

// 2. استدعاء ملفات الإعدادات والنماذج (Models) [cite: 33, 46, 47]
require_once 'config.php';
require_once 'models/User.php';
require_once 'models/Semester.php';
require_once 'models/Course.php';

// 3. تحديد الصفحة المطلوبة (Default هي login) [cite: 98]
$page = $_GET['page'] ?? 'login';

// 4. نظام التوجيه (Routing Logic) [cite: 99]
switch ($page) {
    case 'login':
    case 'logout':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        if ($page == 'login') $controller->login();
        else $controller->logout();
        break;

    // توجيهات المسؤول (Admin) [cite: 104, 105]
    case (strpos($page, 'admin.') === 0):
        requireRole('admin'); // حماية المسار [cite: 105]
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        
        // استدعاء الأكشن المناسب (مثال: admin.semesters)
        $action = str_replace('admin.', '', $page);
        $controller->$action();
        break;

    // توجيهات الأستاذ (Professor) [cite: 107, 108]
    case (strpos($page, 'professor.') === 0):
        requireRole('professor'); // حماية المسار [cite: 108]
        require_once 'controllers/ProfessorController.php';
        $controller = new ProfessorController();
        
        $action = str_replace('professor.', '', $page);
        $controller->$action();
        break;

    // توجيهات الطالب (Student) [cite: 111, 112]
    case (strpos($page, 'student.') === 0):
        requireRole('student'); // حماية المسار [cite: 112]
        require_once 'controllers/StudentController.php';
        $controller = new StudentController();
        
        $action = str_replace('student.', '', $page);
        $controller->$action();
        break;

    // المسار الافتراضي عند وجود خطأ [cite: 113]
    default:
        header("Location: index.php?page=login");
        exit();
}