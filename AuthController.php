<?php
class AuthController {
    private $userModel;

    public function __construct() {
        global $pdo; // استخدام الاتصال المعرف في config.php
        $this->userModel = new User($pdo);
    }

    // 1. معالجة عملية تسجيل الدخول
    public function login() {
        // إذا كان المستخدم مسجلاً بالفعل، نوجهه حسب دوره
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole($_SESSION['role']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email']);
            $password = $_POST['password'];

            $user = $this->userModel->login($email, $password);

            if ($user) {
                // إعداد بيانات الجلسة (Session)
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                $this->redirectBasedOnRole($user['role']);
            } else {
                $error = "بيانات الدخول غير صحيحة";
                require_once 'views/login.php';
            }
        } else {
            require_once 'views/login.php';
        }
    }

    // 2. دالة التوجيه حسب الدور
    private function redirectBasedOnRole($role) {
        switch ($role) {
            case 'admin':
                header("Location: index.php?page=admin.dashboard");
                break;
            case 'professor':
                header("Location: index.php?page=professor.grades");
                break;
            case 'student':
                header("Location: index.php?page=student.history");
                break;
        }
        exit();
    }

    // 3. تسجيل الخروج
    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php?page=login");
        exit();
    }
}