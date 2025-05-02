<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Sessions.php';
require_once __DIR__ . '/../../controllers/postController.php';

global $session, $postController;

// التحقق من تسجيل الدخول
if (!$session->isLoggedIn()) {
    header("Location: ../login.php?unauthorized=1");
    exit();
}

// معالجة إنشاء المنشور
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // تنظيف البيانات المدخلة
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    // التحقق من صحة البيانات
    if (empty($title)) {
        $errors['title'] = 'عنوان المنشور مطلوب';
    } elseif (strlen($title) < 5) {
        $errors['title'] = 'العنوان يجب أن يكون 5 أحرف على الأقل';
    }
    
    if (empty($content)) {
        $errors['content'] = 'محتوى المنشور مطلوب';
    } elseif (strlen($content) < 20) {
        $errors['content'] = 'المحتوى يجب أن يكون 20 حرفاً على الأقل';
    }
    
    // إذا لا توجد أخطاء
    if (empty($errors)) {
        // استدعاء دالة إنشاء المنشور من postController
        $result = $postController->createPost(
            $session->getUserId(), // ID المستخدم الحالي
            $title,
            $content
        );
        
        if ($result['success']) {
            $session->setFlashMessage('success', 'تم إنشاء المنشور بنجاح!');
            header("Location: ../index.php");
            exit();
        } else {
            $errors['general'] = $result['message'];
        }
    }
}

$csrf_token = $session->getCSRFToken();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء منشور جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
        }
        .card {
            margin-top: 50px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        textarea {
            min-height: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">إنشاء منشور جديد</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">عنوان المنشور</label>
                                <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" 
                                       id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                                <?php if (isset($errors['title'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">محتوى المنشور</label>
                                <textarea class="form-control <?= isset($errors['content']) ? 'is-invalid' : '' ?>" 
                                          id="content" name="content" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                                <?php if (isset($errors['content'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['content']) ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="../index.php" class="btn btn-outline-secondary">إلغاء</a>
                                <button type="submit" class="btn btn-primary">نشر المنشور</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>