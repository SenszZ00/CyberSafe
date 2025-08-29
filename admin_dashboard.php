<?php
session_start();
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: login.php");
    exit();
}
echo "<h1>Welcome, Admin!</h1>";
echo "<a href='logout.php'>Logout</a>";
?>
