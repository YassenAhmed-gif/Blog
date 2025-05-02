<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Sessions.php';
require_once __DIR__ . '/../controllers/userController.php';
require_once __DIR__ . '/../controllers/postController.php';

// تعريف المتغيرات العالمية
global $session, $userController, $postController;

// بدء الجلسة إذا لم تكن بدأت
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// جلب جميع البوستات مع التحقق من نجاح العملية
$postsResult = $postController->getAllPosts();
$posts = $postsResult['success'] ? $postsResult['posts'] : [];

// إذا كان المستخدم غير مسجل دخول، نوجهه لصفحة تسجيل الدخول عند محاولة إنشاء بوست
if (isset($_GET['action']) && $_GET['action'] === 'create' && !$session->isLoggedIn()) {
    $_SESSION['redirect_url'] = 'index.php?action=create';
    header("Location: login.php?unauthorized=1");
    exit();
}

// معالجة إنشاء المنشور إذا تم إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {
    if ($session->isLoggedIn()) {
        $userId = $session->getUserId();
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        $result = $postController->createPost($userId, $title, $content);
        
        if ($result['success']) {
            $session->setFlashMessage('success', 'تم إنشاء المنشور بنجاح!');
            header("Location: index.php");
            exit();
        } else {
            $session->setFlashMessage('error', $result['message']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرئيسية - مدونتي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --danger-color: #e74c3c;
        }
        body {
            background-color: var(--light-color);
            font-family: 'Tajawal', sans-serif;
            padding-top: 56px; /* لحساب ارتفاع navbar ثابت */
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .post-card {
            transition: all 0.3s ease;
            margin-bottom: 30px;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 100%;
        }
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .post-img-container {
            height: 200px;
            overflow: hidden;
        }
        .post-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .post-card:hover .post-img {
            transform: scale(1.05);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: 10px;
            border: 2px solid #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .create-post-btn {
            position: fixed;
            bottom: 30px;
            left: 30px;
            z-index: 1000;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        .create-post-btn:hover {
            transform: scale(1.1);
        }
        .empty-state {
            text-align: center;
            padding: 50px 0;
            color: #6c757d;
        }
        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        .text-ellipsis {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        footer {
            box-shadow: 0 -2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">مدونتي</a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">من نحن</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($session->isLoggedIn()): ?>
                        <?php $user = $session->getUser(); ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'assets/images/default-avatar.jpg'; ?>" class="user-avatar me-2">
                                <span><?php echo htmlspecialchars($user['username']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>الملف الشخصي</a></li>
                                <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>لوحة التحكم</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>تسجيل الخروج</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php"><i class="fas fa-sign-in-alt me-2"></i>تسجيل الدخول</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php"><i class="fas fa-user-plus me-2"></i>تسجيل حساب</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- المحتوى الرئيسي -->
    <main class="container my-5" style="padding-top: 20px;">
        <?php $session->displayFlashMessages(); ?>
        
        <!-- عنوان الصفحة وأزرار التحكم -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3 mb-0">أحدث المنشورات</h1>
            
            <?php if ($session->isLoggedIn()): ?>
                <a href="posts/create.php" class="btn btn-primary d-none d-lg-inline-flex">
                    <i class="fas fa-plus me-2"></i> إنشاء منشور جديد
                </a>
            <?php endif; ?>
        </div>
        
        <!-- قائمة البوستات -->
        <div class="row">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card post-card h-100">
                            <?php if (!empty($post['image'])): ?>
                                <div class="post-img-container">
                                    <img src="<?php echo htmlspecialchars($post['image']); ?>" class="post-img" alt="<?php echo htmlspecialchars($post['title']); ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h2 class="h5 post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <img src="<?php echo !empty($post['user_avatar']) ? htmlspecialchars($post['user_avatar']) : 'assets/images/default-avatar.jpg'; ?>" class="user-avatar">
                                    <div class="d-flex flex-column ms-2">
                                        <span class="fw-bold"><?php echo htmlspecialchars($post['username']); ?></span>
                                        <small class="text-muted"><?php echo date('Y/m/d', strtotime($post['created_at'])); ?></small>
                                    </div>
                                </div>
                                
                                <p class="card-text text-ellipsis flex-grow-1">
                                    <?php echo htmlspecialchars(strip_tags($post['content'])); ?>
                                </p>
                                
                                <div class="mt-3 d-flex justify-content-between align-items-center">
                                    <a href="posts/view.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-book-reader me-1"></i> قراءة المزيد
                                    </a>
                                    
                                    <?php if ($session->isLoggedIn() && $session->getUserId() == $post['user_id']): ?>
                                        <div class="btn-group">
                                            <a href="posts/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="posts/delete.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المنشور؟');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="far fa-newspaper"></i>
                        </div>
                        <h3 class="h4">لا توجد منشورات بعد</h3>
                        <p class="mb-4">كن أول من ينشر محتوى مثيرًا للاهتمام!</p>
                        <?php if ($session->isLoggedIn()): ?>
                            <a href="../Screens/post/createPost.php" class="btn btn-primary px-4">
                                <i class="fas fa-plus me-2"></i> إنشاء أول منشور
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary px-4">
                                <i class="fas fa-user-plus me-2"></i> سجل حسابًا جديدًا
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- زر إنشاء بوست جديد (للأجهزة المحمولة) -->
    <?php if ($session->isLoggedIn()): ?>
        <a href="../Screens/post/createPost.php" class="btn btn-primary px-4">
            <i class="fas fa-plus me-2"></i> إنشاء منشور
        </a>
    <?php endif; ?>

    <!-- تذييل الصفحة -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© <?php echo date('Y'); ?> مدونتي. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <!-- الأكواد الجافاسكريبت -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // تفعيل عناصر البوبوفر والتولتيب
        document.addEventListener('DOMContentLoaded', function() {
            // تفعيل dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            // تفعيل tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</body>
</html>