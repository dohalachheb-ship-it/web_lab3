<?php
class User {
    private $pdo;

    // عند إنشاء كائن من هذا الكلاس، نمرر له اتصال قاعدة البيانات
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // دالة للتحقق من بيانات تسجيل الدخول
    public function login($email, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // التحقق من وجود المستخدم ومطابقة كلمة المرور المشفرة
        if ($user && password_verify($password, $user['password'])) {
            return $user; // إرجاع بيانات المستخدم في حال النجاح
        }
        return false; // فشل الدخول
    }

    // دالة لجلب مستخدم معين بواسطة المعرف (ID)
    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // دالة لجلب جميع المستخدمين حسب الرتبة (مثلاً جلب كل الأساتذة)
    public function getAllByRole($role) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = ?");
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>