<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Sessions.php';
require_once __DIR__ . '/../../controllers/userController.php';

global $session, $userController, $pdo;

// بدء الجلسة
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// إذا كان مسجل دخول بالفعل
if ($session->isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

// معالجة تسجيل الدخول
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // التحقق من البيانات
    if (empty($email)) $errors['email'] = 'البريد الإلكتروني مطلوب';
    if (empty($password)) $errors['password'] = 'كلمة المرور مطلوبة';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                $session->loginUser($user);
                header("Location: ../index.php");
                exit();
            } else {
                $errors['general'] = 'بيانات الدخول غير صحيحة';
            }
        } catch (PDOException $e) {
            $errors['general'] = 'حدث خطأ في النظام';
        }
    }
}

// عرض الصفحة
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تسجيل الدخول</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center">تسجيل الدخول</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors['general'])): ?>
                            <div class="alert alert-danger"><?= $errors['general'] ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">تسجيل الدخول</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>