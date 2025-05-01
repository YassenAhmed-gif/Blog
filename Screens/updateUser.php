<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Sessions.php';
require_once __DIR__ . '/../controllers/userController.php';

global $session, $userController;

// التحقق من تسجيل الدخول
if (!$session->isLoggedIn()) {
    header("Location: ../login.php?unauthorized=1");
    exit();
}

// جلب بيانات المستخدم الحالي
$currentUser = $session->getUser();
$user = $userController->getUserById($currentUser['id']);

// إذا لم يتم العثور على المستخدم
if (!$user || !$user['success']) {
    $session->setFlashMessage('error', 'المستخدم غير موجود');
    header("Location: profile.php");
    exit();
}

$user = $user['user'];

// معالجة تحديث البيانات
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // التحقق من رمز CSRF
    if (!$session->validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'طلب غير صالح';
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $currentPassword = trim($_POST['current_password'] ?? '');
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // التحقق من البيانات الأساسية
        if (empty($username)) {
            $errors['username'] = 'اسم المستخدم مطلوب';
        }

        if (empty($email)) {
            $errors['email'] = 'البريد الإلكتروني مطلوب';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'البريد الإلكتروني غير صالح';
        }

        // إذا تم تقديم كلمة مرور جديدة
        if (!empty($newPassword)) {
            if (empty($currentPassword)) {
                $errors['current_password'] = 'كلمة المرور الحالية مطلوبة لتغيير كلمة المرور';
            } elseif (!password_verify($currentPassword, $user['password'])) {
                $errors['current_password'] = 'كلمة المرور الحالية غير صحيحة';
            }

            if (strlen($newPassword) < 6) {
                $errors['new_password'] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
            } elseif ($newPassword !== $confirmPassword) {
                $errors['confirm_password'] = 'كلمتا المرور غير متطابقتين';
            }
        }

        // إذا لا توجد أخطاء
        if (empty($errors)) {
            // تحديث البيانات الأساسية
            $updateResult = $userController->updateUser(
                $user['id'],
                $username,
                $email
            );

            // إذا كان التحديث ناجحاً
            if ($updateResult['success']) {
                // إذا تم تغيير كلمة المرور
                if (!empty($newPassword)) {
                    $passwordResult = $userController->changePassword(
                        $user['id'],
                        $currentPassword,
                        $newPassword
                    );

                    if (!$passwordResult['success']) {
                        $errors['general'] = $passwordResult['message'];
                    }
                }

                // إذا لم تحدث أخطاء في تغيير كلمة المرور
                if (empty($errors)) {
                    // تحديث بيانات الجلسة
                    $updatedUser = $userController->getUserById($user['id'])['user'];
                    $session->loginUser($updatedUser);
                    
                    $session->setFlashMessage('success', 'تم تحديث بياناتك بنجاح');
                    $success = true;
                }
            } else {
                $errors['general'] = $updateResult['message'];
            }
        }
    }
}

// إنشاء رمز CSRF جديد
$csrf_token = $session->getCSRFToken();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تحديث البيانات الشخصية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Tajawal', sans-serif;
        }
        .profile-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 5px solid #fff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: bold;
        }
        .password-fields {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .btn-save {
            min-width: 120px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container my-5">
        <div class="profile-container">
            <div class="profile-header">
                <h2>تحديث البيانات الشخصية</h2>
                <p class="text-muted">يمكنك تحديث معلومات حسابك هنا</p>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    تم تحديث بياناتك بنجاح!
                </div>
            <?php endif; ?>

            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?php echo $errors['general']; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label">اسم المستخدم</label>
                        <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                               id="username" name="username" 
                               value="<?php echo htmlspecialchars($_POST['username'] ?? $user['username']); ?>" required>
                        <?php if (isset($errors['username'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['username']; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">البريد الإلكتروني</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? $user['email']); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback">
                                <?php echo $errors['email']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="password-fields">
                    <h5 class="mb-4">تغيير كلمة المرور</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="current_password" class="form-label">كلمة المرور الحالية</label>
                            <input type="password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>" 
                                   id="current_password" name="current_password">
                            <?php if (isset($errors['current_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['current_password']; ?>
                                </div>
                            <?php endif; ?>
                            <small class="text-muted">أدخل كلمة المرور الحالية فقط إذا كنت تريد تغييرها</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">كلمة المرور الجديدة</label>
                            <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>" 
                                   id="new_password" name="new_password">
                            <?php if (isset($errors['new_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['new_password']; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">تأكيد كلمة المرور الجديدة</label>
                            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                                   id="confirm_password" name="confirm_password">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['confirm_password']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="profile.php" class="btn btn-outline-secondary">رجوع</a>
                    <button type="submit" class="btn btn-primary btn-save">حفظ التغييرات</button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تفعيل عناصر البوبوفر والتولتيب
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>