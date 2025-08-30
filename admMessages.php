<?php
// admMessages.php
session_start();
require 'db.php';

// Restrict to Admins only
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

// Fetch all feedback messages
$stmt = $pdo->query("
    SELECT f.feedbackID,
           f.userID,
           COALESCE(u.username, 'Anonymous') AS username,
           f.subject,
           f.message,
           f.submittedAt
    FROM feedback f
    LEFT JOIN users u ON f.userID = u.userID
    ORDER BY f.submittedAt DESC
");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>CyberSafe USeP - User Messages</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="styles/admin-dashboard.css">
  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      padding-top: 80px;
    }
    .admin-container {
      flex: 1;
      padding: 20px;
      margin: 20px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    footer { margin-top: auto; }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    .page-title { font-size: 24px; color: maroon; }

    .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
    .data-table th, .data-table td {
      padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top;
    }
    .data-table th {
      background: maroon; color: white; position: sticky; top: 80px;
    }
    .data-table tr:hover { background: #f8f9fa; }

    @media (max-width: 768px) {
      .data-table { display: block; overflow-x: auto; }
    }
  </style>
</head>
<body>
  <!-- Admin Header -->
  <header class="header">
    <div id="logo">
      <img src="images/logo.jpg" alt="CyberSafe USeP Logo">
      <img src="images/sdmdlogo.png" alt="sdmdlogo">
      <h1>Admin Dashboard</h1>
    </div>
    <div class="desktop-nav">
      <nav class="nav">
        <a href="admDashSUBMITTEDART.php">Submitted Articles</a>
        <a href="admDashSUBMITTEDREP.php">Submitted Reports</a>
        <a href="admDashREPORTLOG.php">Report Log</a>
        <a href="admMessages.php" class="active">Messages</a>
        <a href="logout.php" class="logout-btn" title="Logout">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </nav>
    </div>
    <button class="menu-btn" onclick="toggleMenu()">☰</button>
  </header>

  <!-- Mobile Menu -->
  <div class="overlay" id="overlay"></div>
  <div class="mobile-menu-container" id="mobileMenuContainer">
    <nav class="mobile-nav">
      <a href="admDashSUBMITTEDART.php">Submitted Articles</a>
      <a href="admDashSUBMITTEDREP.php">Submitted Reports</a>
      <a href="admDashREPORTLOG.php">Report Log</a>
      <a href="admMessages.php" class="active">User Messages</a>
      <a href="logout.php">Log out</a>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="admin-container">
    <div class="page-header">
      <h2 class="page-title">User Messages</h2>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Subject</th>
          <th>Message</th>
          <th>Submitted At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($messages as $msg): ?>
          <tr>
            <td><?= htmlspecialchars($msg['feedbackID']) ?></td>
            <td><?= htmlspecialchars($msg['username']) ?></td>
            <td><?= htmlspecialchars($msg['subject']) ?></td>
            <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>
            <td><?= htmlspecialchars($msg['submittedAt']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Footer -->
  <footer>
    &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
  </footer>

  <script>
    function toggleMenu() {
      const c = document.getElementById('mobileMenuContainer');
      const o = document.getElementById('overlay');
      c.classList.toggle('active');
      o.classList.toggle('active');
      document.body.style.overflow = c.classList.contains('active') ? 'hidden' : '';
    }
    document.getElementById('overlay').addEventListener('click', toggleMenu);
  </script>
</body>
</html>
