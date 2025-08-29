<?php
// delete-report.php
session_start();

// 1) Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';
$userID   = $_SESSION['user_id'];
$reportID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($reportID <= 0) {
    // invalid ID
    header("Location: cybersafeOWNREPORTS.php?deleted=0");
    exit;
}

// 2) Confirm this report belongs to the current user
$stmt = $pdo->prepare("
    SELECT 1
      FROM reports
     WHERE reportID = :rid
       AND userID   = :uid
");
$stmt->execute([
    'rid' => $reportID,
    'uid' => $userID
]);
if (!$stmt->fetch()) {
    // either not found or not owned by this user
    header("Location: cybersafeOWNREPORTS.php?deleted=0");
    exit;
}

// 3) Delete it
$del = $pdo->prepare("DELETE FROM reports WHERE reportID = :rid");
$success = $del->execute(['rid' => $reportID]);

// 4) Redirect back, with a flag you can read in the UI
if ($success) {
    header("Location: cybersafeOWNREPORTS.php?deleted=1");
} else {
    header("Location: cybersafeOWNREPORTS.php?deleted=0");
}
exit;
