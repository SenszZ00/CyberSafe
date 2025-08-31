<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// db.php
$host   = 'localhost';
$dbname = 'cybersafeusep';  // contains both usepemails & users tables
$user   = 'root';
$pass   = '';                    // or your MySQL password

try {
  $pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $user, $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
  );
} catch (PDOException $e) {
  die("DB connection failed: " . $e->getMessage());
}
