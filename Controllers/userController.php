<?php
require_once __DIR__ . '/../includes/config.php';

class UserController {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * إنشاء مستخدم جديد
     * @param string $username
     * @param string $email
     * @param string $password
     * @return array ['success' => bool, 'message' => string, 'user_id' => int]
     */
    public function createUser($username, $email, $password) {
        try {
            // التحقق من البيانات المدخلة
            if (empty($username) || empty($email) || empty($password)) {
                return ['success' => false, 'message' => 'جميع الحقول مطلوبة'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'البريد الإلكتروني غير صالح'];
            }

            // التحقق من عدم وجود البريد الإلكتروني مسبقاً
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'البريد الإلكتروني مسجل بالفعل'];
            }

            // تشفير كلمة المرور
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // إضافة المستخدم إلى قاعدة البيانات
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            return [
                'success' => true,
                'message' => 'تم إنشاء المستخدم بنجاح',
                'user_id' => $this->pdo->lastInsertId()
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في إنشاء المستخدم: ' . $e->getMessage()
            ];
        }
    }

    /**
     * جلب جميع المستخدمين
     * @return array
     */
    public function getUsers() {
        try {
            $stmt = $this->pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
            return [
                'success' => true,
                'users' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في جلب المستخدمين: ' . $e->getMessage()
            ];
        }
    }

    /**
     * جلب مستخدم بواسطة ID
     * @param int $id
     * @return array
     */
    public function getUserById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                return ['success' => true, 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'المستخدم غير موجود'];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في جلب المستخدم: ' . $e->getMessage()
            ];
        }
    }

    /**
     * تحديث بيانات المستخدم
     * @param int $id
     * @param string $username
     * @param string $email
     * @return array
     */
    public function updateUser($id, $username, $email) {
        try {
            // التحقق من البيانات
            if (!is_numeric($id)) {
                return ['success' => false, 'message' => 'معرف المستخدم غير صالح'];
            }

            if (empty($username) || empty($email)) {
                return ['success' => false, 'message' => 'جميع الحقول مطلوبة'];
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'message' => 'البريد الإلكتروني غير صالح'];
            }

            // التحقق من عدم استخدام البريد الإلكتروني من قبل مستخدم آخر
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'البريد الإلكتروني مسجل بالفعل لمستخدم آخر'];
            }

            // تنفيذ عملية التحديث
            $stmt = $this->pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'تم تحديث بيانات المستخدم بنجاح'];
            } else {
                return ['success' => false, 'message' => 'لم يتم تحديث أي بيانات أو المستخدم غير موجود'];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في تحديث المستخدم: ' . $e->getMessage()
            ];
        }
    }

    /**
     * حذف مستخدم
     * @param int $id
     * @return array
     */
    public function deleteUser($id) {
        try {
            if (!is_numeric($id)) {
                return ['success' => false, 'message' => 'معرف المستخدم غير صالح'];
            }

            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'تم حذف المستخدم بنجاح'];
            } else {
                return ['success' => false, 'message' => 'المستخدم غير موجود'];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في حذف المستخدم: ' . $e->getMessage()
            ];
        }
    }

    /**
     * تغيير كلمة مرور المستخدم
     * @param int $id
     * @param string $currentPassword
     * @param string $newPassword
     * @return array
     */
    public function changePassword($id, $currentPassword, $newPassword) {
        try {
            if (!is_numeric($id)) {
                return ['success' => false, 'message' => 'معرف المستخدم غير صالح'];
            }

            if (empty($currentPassword) || empty($newPassword)) {
                return ['success' => false, 'message' => 'كلمة المرور الحالية والجديدة مطلوبة'];
            }

            // جلب كلمة المرور الحالية
            $stmt = $this->pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();

            if (!$user) {
                return ['success' => false, 'message' => 'المستخدم غير موجود'];
            }

            // التحقق من كلمة المرور الحالية
            if (!password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'message' => 'كلمة المرور الحالية غير صحيحة'];
            }

            // تحديث كلمة المرور
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $id]);

            return ['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح'];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'خطأ في تغيير كلمة المرور: ' . $e->getMessage()
            ];
        }
    }
}

// إنشاء كائن للتحكم بالمستخدمين لاستخدامه في الملفات الأخرى
$userController = new UserController();
?>