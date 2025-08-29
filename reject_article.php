<?php
// reject_article.php
session_start();
header('Content-Type: application/json');

require 'db.php';

// Only Admins may reject
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$id     = isset($_POST['id'])     ? (int)trim($_POST['id'])     : 0;
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

if ($id > 0 && $reason !== '') {
    try {
        $stmt = $pdo->prepare("UPDATE articles SET status = 'rejected' WHERE articleID = ?");
        $stmt->execute([$id]);
        // Optionally log $reason into your ReportLog or another table here
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid data']);
}
exit;
