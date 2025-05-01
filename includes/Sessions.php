<?php
// نمنع الوصول المباشر للملف
if (!defined('SESSION_MANAGER')) {
    exit('Direct access denied');
}

class SessionManager {
    private $sessionTimeout = 1800; // 30 دقيقة بالثواني

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->checkSessionTimeout();
    }

    /**
     * بدء جلسة المستخدم بعد تسجيل الدخول
     */
    public function loginUser($user) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'last_activity' => time()
        ];
        
        // إنشاء رمز CSRF للحماية
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    /**
     * تسجيل خروج المستخدم
     */
    public function logoutUser() {
        // مسح جميع بيانات الجلسة
        $_SESSION = array();

        // إذا كنت تريد حذف الجلسة تماماً
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * التحقق من وجود مستخدم مسجل دخول
     */
    public function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    /**
     * جلب بيانات المستخدم الحالي
     */
    public function getUser() {
        return $this->isLoggedIn() ? $_SESSION['user'] : null;
    }

    /**
     * جلب معرف المستخدم الحالي
     */
    public function getUserId() {
        return $this->isLoggedIn() ? $_SESSION['user']['id'] : null;
    }

    /**
     * التحقق من انتهاء مدة الجلسة
     */
    private function checkSessionTimeout() {
        if ($this->isLoggedIn() && isset($_SESSION['user']['last_activity'])) {
            $inactive = time() - $_SESSION['user']['last_activity'];
            
            if ($inactive > $this->sessionTimeout) {
                $this->logoutUser();
                header("Location: login.php?timeout=1");
                exit();
            }
            
            $_SESSION['user']['last_activity'] = time();
        }
    }

    /**
     * توليد وإرجاع رمز CSRF
     */
    public function getCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * التحقق من صحة رمز CSRF
     */
    public function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * تعيين رسالة مؤقتة للعرض (Flash Message)
     */
    public function setFlashMessage($type, $message) {
        $_SESSION['flash_messages'][] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * جلب وعرض الرسائل المؤقتة
     */
    public function displayFlashMessages() {
        if (empty($_SESSION['flash_messages'])) {
            return;
        }

        foreach ($_SESSION['flash_messages'] as $message) {
            echo '<div class="alert alert-' . htmlspecialchars($message['type']) . '">'
                . htmlspecialchars($message['message']) .
                '</div>';
        }

        unset($_SESSION['flash_messages']);
    }
}

// تعريف ثابت للتحقق من الوصول الآمن
define('SESSION_MANAGER', true);

// إنشاء كائن الجلسات للاستخدام العام
$session = new SessionManager();
?>