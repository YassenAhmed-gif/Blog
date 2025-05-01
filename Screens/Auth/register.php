<?php
define('SESSION_MANAGER', true); // أضف هذا السطر في البداية
require_once '../../includes/config.php';
require_once '../../includes/Sessions.php';
require_once '../../controllers/userController.php';

// تهيئة الكائنات
global $session, $userController;
$userController = new UserController(); // أضف هذا السطر

// بدء الجلسة إذا لم تكن بدأت
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// إذا كان المستخدم مسجل دخول بالفعل، نوجهه للصفحة الرئيسية
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// معالجة بيانات التسجيل
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // التحقق من البيانات
    if (empty($username)) {
        $errors['username'] = 'اسم المستخدم مطلوب';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل';
    }

    if (empty($email)) {
        $errors['email'] = 'البريد الإلكتروني مطلوب';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'البريد الإلكتروني غير صالح';
    }

    if (empty($password)) {
        $errors['password'] = 'كلمة المرور مطلوبة';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    }

    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'كلمتا المرور غير متطابقتين';
    }

    // إذا لا توجد أخطاء، ننشئ المستخدم
    if (empty($errors)) {
        $result = $userController->createUser($username, $email, $password);
        
        if ($result['success']) {
            $success = true;
            
            // يمكنك هنا تسجيل دخول المستخدم تلقائياً إذا أردت:
            // $_SESSION['user_id'] = $result['user_id'];
            // header("Location: index.php");
            // exit();
        } else {
            $errors['general'] = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل حساب جديد</title>
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
        .success-message {
            background-color: #2ecc71;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>تسجيل حساب جديد</h1>
        
        <?php if ($success): ?>
            <div class="success-message">
                تم تسجيل حسابك بنجاح! يمكنك <a href="login.php" style="color: white; font-weight: bold;">تسجيل الدخول الآن</a>.
            </div>
        <?php elseif (isset($errors['general'])): ?>
            <div class="error" style="margin-bottom: 20px; text-align: center;">
                <?php echo $errors['general']; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="username">اسم المستخدم</label>
                <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                <?php if (isset($errors['username'])): ?>
                    <span class="error"><?php echo $errors['username']; ?></span>
                <?php endif; ?>
            </div>
            
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
                <label for="confirm_password">تأكيد كلمة المرور</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="error"><?php echo $errors['confirm_password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit">تسجيل الحساب</button>
            </div>
        </form>
        
        <div class="login-link">
            لديك حساب بالفعل؟ <a href="login.php">سجل الدخول هنا</a>
        </div>
    </div>
</body>
</html>