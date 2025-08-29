<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_type']) || $_SESSION['user_type']!=='Admin') {
  echo json_encode(['success'=>false,'error'=>'Not authorized']);
  exit;
}
$r = $_POST['reportID'] ?? null;
$it = $_POST['itPersonnelID'] ?? null;
if (!$r || !$it) {
  echo json_encode(['success'=>false,'error'=>'Invalid input']);
  exit;
}
$stmt = $pdo->prepare(
  "UPDATE reports SET itPersonnelID = :it, status = 'under review'
   WHERE reportID = :r"
);
if ($stmt->execute([':it'=>$it,':r'=>$r])) {
  echo json_encode(['success'=>true]);
} else {
  echo json_encode(['success'=>false,'error'=>'DB error']);
}
