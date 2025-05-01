<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/Sessions.php';
require_once __DIR__ . '/controllers/userController.php';
require_once __DIR__ . '/controllers/postController.php';

global $session, $userController, $postController;

// جلب جميع البوستات
$posts = $postController->getAllPosts();

// إذا كان المستخدم غير مسجل دخول، نوجهه لصفحة تسجيل الدخول عند محاولة إنشاء بوست
if (isset($_GET['action']) && $_GET['action'] === 'create' && !$session->isLoggedIn()) {
    $_SESSION['redirect_url'] = 'index.php?action=create';
    header("Location: login.php?unauthorized=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الرئيسية - مدونتي</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Tajawal', sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .post-card {
            transition: transform 0.3s, box-shadow 0.3s;
            margin-bottom: 30px;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .post-img {
            height: 200px;
            object-fit: cover;
        }
        .post-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .post-meta {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .post-content {
            color: #444;
            line-height: 1.8;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: 10px;
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
    </style>
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
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
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'assets/images/default-avatar.jpg'; ?>" class="user-avatar">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profile.php">الملف الشخصي</a></li>
                                <li><a class="dropdown-item" href="dashboard.php">لوحة التحكم</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">تسجيل الخروج</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">تسجيل الدخول</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">تسجيل حساب</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- المحتوى الرئيسي -->
    <main class="container my-5">
        <?php $session->displayFlashMessages(); ?>
        
        <!-- عنوان الصفحة وأزرار التحكم -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="h3">أحدث المنشورات</h1>
            
            <?php if ($session->isLoggedIn()): ?>
                <a href="posts/create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إنشاء منشور جديد
                </a>
            <?php endif; ?>
        </div>
        
        <!-- قائمة البوستات -->
        <div class="row">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card post-card">
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?php echo htmlspecialchars($post['image']); ?>" class="card-img-top post-img" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h2 class="h5 post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                                
                                <div class="d-flex align-items-center mb-3 post-meta">
                                    <img src="<?php echo !empty($post['user_avatar']) ? htmlspecialchars($post['user_avatar']) : 'assets/images/default-avatar.jpg'; ?>" class="user-avatar">
                                    <span><?php echo htmlspecialchars($post['username']); ?></span>
                                    <span class="mx-2">•</span>
                                    <span><?php echo date('Y/m/d', strtotime($post['created_at'])); ?></span>
                                </div>
                                
                                <p class="card-text post-content">
                                    <?php 
                                    $content = strip_tags($post['content']);
                                    echo mb_substr($content, 0, 150) . (mb_strlen($content) > 150 ? '...' : '');
                                    ?>
                                </p>
                                
                                <a href="posts/view.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary">قراءة المزيد</a>
                                
                                <?php if ($session->isLoggedIn() && $session->getUserId() == $post['user_id']): ?>
                                    <div class="mt-3 d-flex justify-content-end">
                                        <a href="posts/edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-secondary me-2">
                                            <i class="fas fa-edit"></i> تعديل
                                        </a>
                                        <a href="posts/delete.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('هل أنت متأكد من حذف هذا المنشور؟');">
                                            <i class="fas fa-trash"></i> حذف
                                        </a>
                                    </div>
                                <?php endif; ?>
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
                            <a href="posts/create.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> إنشاء أول منشور
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary">سجل حسابًا جديدًا</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- زر إنشاء بوست جديد (للأجهزة المحمولة) -->
    <?php if ($session->isLoggedIn()): ?>
        <a href="posts/create.php" class="btn btn-primary create-post-btn d-lg-none">
            <i class="fas fa-plus"></i>
        </a>
    <?php endif; ?>

    <!-- تذييل الصفحة -->
    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">© 2023 مدونتي. جميع الحقوق محفوظة.</p>
        </div>
    </footer>

    <!-- الأكواد الجافاسكريبت -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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