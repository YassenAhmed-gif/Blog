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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (empty($title)) {
        $errors['title'] = 'عنوان المنشور مطلوب';
    }
    
    if (empty($content)) {
        $errors['content'] = 'محتوى المنشور مطلوب';
    }
    
    if (empty($errors)) {
        $result = $postController->createPost($session->getUserId(), $title, $content);
        
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .editor-container {
            min-height: 300px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">إنشاء منشور جديد</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $errors['general']; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="create.php">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">عنوان المنشور</label>
                                <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" 
                                       id="title" name="title" value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                                <?php if (isset($errors['title'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['title']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">محتوى المنشور</label>
                                <textarea class="form-control <?php echo isset($errors['content']) ? 'is-invalid' : ''; ?>" 
                                          id="content" name="content" rows="10" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                <?php if (isset($errors['content'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $errors['content']; ?>
                                    </div>
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
    
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>