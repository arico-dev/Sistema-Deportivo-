<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

function requireUserType($allowedTypes) {
    requireLogin();
    $userType = getUserType();
    if (!in_array($userType, $allowedTypes)) {
        header('Location: unauthorized.php');
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    // Si ya tenemos los datos completos en sesión, los devolvemos
    if (isset($_SESSION['created_at'])) {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? null,
            'user_type' => $_SESSION['user_type'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'created_at' => $_SESSION['created_at'] ?? null
        ];
    }
    
    // Si no tenemos created_at, lo obtenemos de la base de datos
    require_once 'database.php';
    $database = new Database();
    $pdo = $database->getConnection();
    
    $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $_SESSION['created_at'] = $result['created_at'];
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'user_type' => $_SESSION['user_type'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'created_at' => $_SESSION['created_at'] ?? null
    ];
}
?>
