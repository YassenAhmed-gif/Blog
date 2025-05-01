<?php
// إنشاء بوست جديد
function createPost($userId, $title, $content) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
    return $stmt->execute([$userId, $title, $content]);
}

// جلب كل البوستات
function getPosts() {
    global $pdo;
    $stmt = $pdo->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// جلب بوست بواسطة الـ ID
function getPostById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// تحديث بوست
function updatePost($id, $title, $content) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
    return $stmt->execute([$title, $content, $id]);
}

// حذف بوست
function deletePost($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    return $stmt->execute([$id]);
}
?>