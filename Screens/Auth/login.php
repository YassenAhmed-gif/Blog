<?php
define('SESSION_MANAGER', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Sessions.php';
require_once __DIR__ . '/../../controllers/userController.php';

global $session, $pdo; // أضفنا $pdo هنا للوصول إليه مباشرة

// إذا كان المستخدم مسجل دخول بالفعل، نوجهه للصفحة الرئيسية
if ($session->isLoggedIn()) {
    header("Location: ../index.php"); // تم تعديل المسار هنا
    exit();
}

// معالجة بيانات تسجيل الدخول
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من رمز CSRF
    if (!$session->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'طلب تسجيل دخول غير صالح';
    } else {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // التحقق من البيانات المدخلة
        if (empty($email)) {
            $errors['email'] = 'البريد الإلكتروني مطلوب';
        }

        if (empty($password)) {
            $errors['password'] = 'كلمة المرور مطلوبة';
        }

        // إذا لا توجد أخطاء في البيانات المدخلة
        if (empty($errors)) {
            // جلب المستخدم من قاعدة البيانات
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // التحقق من وجود المستخدم وصحة كلمة المرور
            if ($user && password_verify($password, $user['password'])) {
                // تسجيل دخول المستخدم
                $session->loginUser($user);
                
                // تعيين رسالة ترحيبية
                $session->setFlashMessage('success', 'مرحباً بعودتك، ' . $user['username'] . '!');
                
                // توجيه المستخدم للصفحة الرئيسية
                $redirect = isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : '../index.php';
                
                // تنظيف متغير التوجيه المؤقت
                if (isset($_SESSION['redirect_url'])) {
                    unset($_SESSION['redirect_url']);
                }
                
                header("Location: " . $redirect);
                exit();
            } else {
                $errors['general'] = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
            }
        }
    }
}

// إنشاء رمز CSRF جديد للنموذج
$csrf_token = $session->getCSRFToken();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <!-- بقية كود HTML كما هو -->
</head>
<body>
    <!-- بقية كود HTML كما هو -->
</body>
</html>