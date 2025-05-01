<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Sessions.php';
require_once __DIR__ . '/controllers/userController.php';

global $session;

// إذا كان المستخدم مسجل دخول بالفعل، نوجهه للصفحة الرئيسية
if ($session->isLoggedIn()) {
    header("Location: index.php");
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
                
                // توجيه المستخدم للصفحة الرئيسية أو الصفحة التي كان يحاول الوصول إليها
                $redirect = $_SESSION['redirect_url'] ?? 'index.php';
                unset($_SESSION['redirect_url']);
                
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        input:focus {
            border-color: #3498db;
            outline: none;
        }
        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .register-link a {
            color: #3498db;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .alert-error {
            background-color: #fde0e0;
            color: #e74c3c;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تسجيل الدخول</h1>
        
        <?php if (isset($_GET['timeout'])): ?>
            <div class="alert alert-error">انتهت مدة جلستك، يرجى تسجيل الدخول مرة أخرى</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['unauthorized'])): ?>
            <div class="alert alert-error">يجب تسجيل الدخول للوصول إلى هذه الصفحة</div>
        <?php endif; ?>
        
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error"><?php echo $errors['general']; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-group">
                <label for="email">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">كلمة المرور</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit">تسجيل الدخول</button>
            </div>
        </form>
        
        <div class="register-link">
            ليس لديك حساب؟ <a href="register.php">سجل حساب جديد</a>
        </div>
    </div>
</body>
</html>