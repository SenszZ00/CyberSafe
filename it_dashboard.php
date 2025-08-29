<?php
session_start();
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "it") {
    header("Location: login.php");
    exit();
}
echo "<h1>Welcome, IT Security Team!</h1>";
echo "<a href='logout.php'>Logout</a>";
?>
