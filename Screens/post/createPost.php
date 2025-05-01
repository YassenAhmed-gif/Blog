<?php
// نضمن ملف الاتصال بقاعدة البيانات والوظائف
require_once '../../includes/config.php';
require_once '../../Controllers/postController.php.php';

// هنا في الواقع هتتحقق من وجود مستخدم مسجل دخول (session)
// لكن حالياً هنفترض أن اليوزر اللي عامل login هو اليوزر رقم 1
$current_user_id = 1;

// عملية إنشاء البوست الجديد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    // التحقق من عدم وجود حقول فارغة
    if (empty($title) || empty($content)) {
        $error = "جميع الحقول مطلوبة!";
    } else {
        // ننفذ عملية الإنشاء
        if (createPost($current_user_id, $title, $content)) {
            header("Location: index.php?success=post_created");
            exit();
        } else {
            $error = "حدث خطأ أثناء إنشاء البوست!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء بوست جديد</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        textarea {
            height: 300px;
            resize: vertical;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: #d32f2f;
            background-color: #fde0e0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #333;
            text-decoration: none;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: all 0.3s;
        }
        .back-link:hover {
            background-color: #f0f0f0;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>إنشاء بوست جديد</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">عنوان البوست</label>
                <input type="text" id="title" name="title" required placeholder="أدخل عنوان البوست هنا">
            </div>
            
            <div class="form-group">
                <label for="content">محتوى البوست</label>
                <textarea id="content" name="content" required placeholder="أدخل محتوى البوست هنا"></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit">إنشاء البوست</button>
                <a href="index.php" class="back-link">← رجوع لقائمة البوستات</a>
            </div>
        </form>
    </div>
</body>
</html>