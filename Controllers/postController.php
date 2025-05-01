<?php
require_once __DIR__ . '/../includes/config.php';

class PostController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * إنشاء بوست جديد
     * @param int $userId
     * @param string $title
     * @param string $content
     * @return array ['success' => bool, 'message' => string, 'post_id' => int]
     */
    public function createPost($userId, $title, $content) {
        try {
            // التحقق من البيانات المدخلة
            if (empty($userId) || empty($title) || empty($content)) {
                return ['success' => false, 'message' => 'جميع الحقول مطلوبة'];
            }

            if (!is_numeric($userId)) {
                return ['success' => false, 'message' => 'معرف المستخدم غير صالح'];
            }

            // إضافة البوست إلى قاعدة البيانات
            $stmt = $this->pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $title, $content]);
            
            return [
                'success' => true,
                'message' => 'تم إنشاء البوست بنجاح',
                'post_id' => $this->pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في إنشاء البوست: ' . $e->getMessage()
            ];
        }
    }

    /**
     * جلب جميع البوستات
     * @return array ['success' => bool, 'posts' => array, 'message' => string]
     */
    public function getAllPosts() {
        try {
            $stmt = $this->pdo->query("
                SELECT posts.*, users.username, users.email 
                FROM posts 
                JOIN users ON posts.user_id = users.id 
                ORDER BY posts.created_at DESC
            ");
            return [
                'success' => true,
                'posts' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في جلب البوستات: ' . $e->getMessage()
            ];
        }
    }

    /**
     * جلب بوست بواسطة ID
     * @param int $id
     * @return array ['success' => bool, 'post' => array, 'message' => string]
     */
    public function getPostById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT posts.*, users.username, users.email 
                FROM posts 
                JOIN users ON posts.user_id = users.id 
                WHERE posts.id = ?
            ");
            $stmt->execute([$id]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($post) {
                return ['success' => true, 'post' => $post];
            } else {
                return ['success' => false, 'message' => 'البوست غير موجود'];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في جلب البوست: ' . $e->getMessage()
            ];
        }
    }

    /**
     * تحديث بوست
     * @param int $id
     * @param string $title
     * @param string $content
     * @return array ['success' => bool, 'message' => string]
     */
    public function updatePost($id, $title, $content) {
        try {
            // التحقق من البيانات
            if (!is_numeric($id)) {
                return ['success' => false, 'message' => 'معرف البوست غير صالح'];
            }

            if (empty($title) || empty($content)) {
                return ['success' => false, 'message' => 'العنوان والمحتوى مطلوبان'];
            }

            // تنفيذ عملية التحديث
            $stmt = $this->pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
            $stmt->execute([$title, $content, $id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'تم تحديث البوست بنجاح'];
            } else {
                return ['success' => false, 'message' => 'لم يتم تحديث أي بيانات أو البوست غير موجود'];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في تحديث البوست: ' . $e->getMessage()
            ];
        }
    }

    /**
     * حذف بوست
     * @param int $id
     * @return array ['success' => bool, 'message' => string]
     */
    public function deletePost($id) {
        try {
            if (!is_numeric($id)) {
                return ['success' => false, 'message' => 'معرف البوست غير صالح'];
            }

            $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'تم حذف البوست بنجاح'];
            } else {
                return ['success' => false, 'message' => 'البوست غير موجود'];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في حذف البوست: ' . $e->getMessage()
            ];
        }
    }

    /**
     * جلب بوستات مستخدم معين
     * @param int $userId
     * @return array ['success' => bool, 'posts' => array, 'message' => string]
     */
    public function getPostsByUser($userId) {
        try {
            if (!is_numeric($userId)) {
                return ['success' => false, 'message' => 'معرف المستخدم غير صالح'];
            }

            $stmt = $this->pdo->prepare("
                SELECT * FROM posts 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$userId]);
            
            return [
                'success' => true,
                'posts' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في جلب بوستات المستخدم: ' . $e->getMessage()
            ];
        }
    }
}

// إنشاء كائن للتحكم بالبوستات لاستخدامه في الملفات الأخرى
$postController = new PostController();
?>