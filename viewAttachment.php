<?php
// viewAttachment.php

session_start();
require 'db.php';

// 1) Only IT Personnel or Admin may view
if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['IT Personnel', 'Admin'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// 2) Get the reportID from the query string
$reportID = isset($_GET['reportID']) ? (int)$_GET['reportID'] : 0;
if ($reportID <= 0) {
    http_response_code(400);
    exit('Invalid report ID');
}

// 3) Fetch blob + metadata
$stmt = $pdo->prepare("
    SELECT attachments, attachmentMime, attachmentName, itPersonnelID
    FROM reports
    WHERE reportID = :id
");
$stmt->execute(['id' => $reportID]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || empty($row['attachments'])) {
    http_response_code(404);
    exit('No attachment found.');
}

// 4) Check access if IT Personnel (Admin can view anything)
if ($_SESSION['user_type'] === 'IT Personnel' && $row['itPersonnelID'] !== $_SESSION['user_id']) {
    http_response_code(403);
    exit('Access denied');
}

// 5) Stream it with correct headers
$mime = $row['attachmentMime']  ?? 'application/octet-stream';
$name = $row['attachmentName']  ?? "attachment_{$reportID}";

header("Content-Type: $mime");
header("Content-Disposition: inline; filename=\"{$name}\"");
echo $row['attachments'];
exit;
