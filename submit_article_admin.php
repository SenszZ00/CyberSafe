<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

require 'db.php';

$title     = trim($_POST['title']    ?? '');
$category  = trim($_POST['category'] ?? '');
$content   = trim($_POST['content']  ?? '');
$keywords  = trim($_POST['keywords'] ?? '');
$userID    = $_SESSION['user_id'];

if ($title === '' || $category === '' || $content === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO articles (userID, title, content, category, keywords)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$userID, $title, $content, $category, $keywords]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;
