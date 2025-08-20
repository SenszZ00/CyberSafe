<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'db.php';

$userID = $_SESSION['user_id'];

// Ensure we have a valid article ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: cybersafeOWNARTICLES.php");
    exit;
}
$articleID = (int)$_GET['id'];

// Delete only if it belongs to this user
$stmt = $pdo->prepare("DELETE FROM articles WHERE articleID = :aid AND userID = :uid");
$stmt->execute(['aid' => $articleID, 'uid' => $userID]);

// Redirect back with a flag if needed
header("Location: cybersafeOWNARTICLES.php?deleted=1");
exit;
