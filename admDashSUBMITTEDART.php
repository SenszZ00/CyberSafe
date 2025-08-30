<?php
// admDASHSUBMITTEDART.php
session_start();
require 'db.php';

// Restrict access to Admins only
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

// Fetch all articles (newest first)
$stmt = $pdo->query("
    SELECT a.articleID, a.title, a.category, a.submissionDate, a.publicationDate,
           a.status, u.username AS author
    FROM articles a
    JOIN users u ON a.userID = u.userID
    ORDER BY a.submissionDate DESC
");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>CyberSafe USeP - Submitted Articles</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="styles/admin-dashboard.css">
  <style>
    /* Additional styles to fix footer and layout */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        padding-top: 80px; /* To account for fixed header */
    }
    .admin-container {
        flex: 1;
        padding: 20px;
        margin: 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    footer {
        margin-top: auto; /* Pushes footer to bottom */
    }
    /* Ensure mobile menu appears below header */
    .mobile-menu-container {
        top: 80px;
    }
    /* Article Management Styles */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .page-title {
        font-size: 24px;
        color: maroon;
    }
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    .btn-primary {
        background-color: maroon;
        color: white;
    }
    .btn-primary:hover {
        background-color: #b30000;
    }
    /* Table Styles */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
    }
    .data-table th, 
    .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }
    .data-table th {
        background-color: maroon;
        color: white;
        font-weight: bold;
        position: sticky;
        top: 80px;
    }
    .data-table tr:hover {
        background-color: #f8f9fa;
    }
    /* Status Styles */
    .status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 12px;
        text-align: center;
        min-width: 80px;
    }
    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-approved { background-color: #d4edda; color: #155724; }
    .status-rejected { background-color: #f8d7da; color: #721c24; }
    /* Action Buttons */
    .action-btns {
        display: flex;
        gap: 5px;
    }
    .action-btn {
        padding: 5px 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        color: white;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .view-btn { background-color: #17a2b8; }
    .approve-btn { background-color: #28a745; }
    .reject-btn { background-color: #dc3545; }
    /* Responsive Table */
    @media (max-width: 768px) {
        .data-table { display: block; overflow-x: auto; }
        .action-btns { flex-direction: column; gap: 3px; }
        .action-btn { width: 100%; }
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
        <a href="admDashSUBMITTEDART.php" class="active">Submitted Articles</a>
        <a href="admDashSUBMITTEDREP.php">Submitted Reports</a>
        <a href="admDashREPORTLOG.php">Report Log</a>
        <a href="admMessages.php">Messages</a>
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
      <a href="admDashSUBMITTEDART.php" class="active">Submitted Articles</a>
      <a href="admDashSUBMITTEDREP.php">Submitted Reports</a>
      <a href="admDashREPORTLOG.php">Report Log</a>
      <a href="admMessages.php">User Messages</a>
      <a href="login.php">Log Out</a>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="admin-container">
    <div class="page-header">
      <h2 class="page-title">Submitted Articles</h2>
      <div>
        <button class="btn btn-primary" onclick="window.location.href='admDashArticles.php'">
          <i class="fas fa-newspaper"></i> View Feed
        </button>
        <button class="btn btn-primary" onclick="window.location.href='admArticleSubmission.html'">
          <i class="fas fa-plus"></i> Upload Article
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Author</th>
            <th>Date Submitted</th>
            <th>Status</th>
            <th>Date Published</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($articles as $row): ?>
            <tr data-article="<?= $row['articleID'] ?>">
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['category']) ?></td>
              <td><?= htmlspecialchars($row['author']) ?></td>
              <td><?= htmlspecialchars($row['submissionDate']) ?></td>
              <td>
                <span class="status status-<?= $row['status'] ?>">
                  <?= ucfirst($row['status']) ?>
                </span>
              </td>
              <td><?= $row['publicationDate'] ?: '-' ?></td>
              <td>
                <div class="action-btns">
                  <button class="action-btn view-btn" onclick="viewArticle(<?= $row['articleID'] ?>)">
                    <i class="fas fa-eye"></i> View
                  </button>
                  <?php if ($row['status'] === 'pending'): ?>
                    <button class="action-btn approve-btn" onclick="approveArticle(<?= $row['articleID'] ?>)">
                      <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="action-btn reject-btn" onclick="rejectArticle(<?= $row['articleID'] ?>)">
                      <i class="fas fa-times"></i> Reject
                    </button>
                  <?php else: ?>
                    <button class="action-btn approve-btn" disabled>
                      <i class="fas fa-check"></i> Approve
                    </button>
                    <button class="action-btn reject-btn" disabled>
                      <i class="fas fa-times"></i> Reject
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
  </footer>

  <script>
    // Toggle mobile menu
    function toggleMenu() {
      const c = document.getElementById('mobileMenuContainer');
      const o = document.getElementById('overlay');
      c.classList.toggle('active');
      o.classList.toggle('active');
      document.body.style.overflow = c.classList.contains('active') ? 'hidden' : '';
    }
    document.getElementById('overlay').addEventListener('click', toggleMenu);

    // Actions
    function viewArticle(id) {
      window.location.href = `view-article.php?id=${id}`;
    }
    function approveArticle(id) {
      if (!confirm(`Approve article ${id}?`)) return;
      fetch(`approve_article.php?id=${id}`)
        .then(r => r.json())
        .then(json => {
          if (json.success) {
            const row = document.querySelector(`tr[data-article='${id}']`);
            row.querySelector('.status').textContent = 'Approved';
            row.querySelector('.status').className = 'status status-approved';
            row.querySelector('.approve-btn').disabled = true;
            row.querySelector('.reject-btn').disabled = true;
          } else {
            alert('Error: ' + json.error);
          }
        })
        .catch(err => alert('Request failed: ' + err));
    }
    function rejectArticle(id) {
      const reason = prompt(`Reason for rejecting article ${id}?`);
      if (reason === null) return;
      fetch('reject_article.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(id)}&reason=${encodeURIComponent(reason)}`
      })
      .then(r => r.json())
      .then(json => {
        if (json.success) {
          const row = document.querySelector(`tr[data-article='${id}']`);
          row.querySelector('.status').textContent = 'Rejected';
          row.querySelector('.status').className = 'status status-rejected';
          row.querySelector('.approve-btn').disabled = true;
          row.querySelector('.reject-btn').disabled = true;
        } else {
          alert('Error: ' + json.error);
        }
      })
      .catch(err => alert('Request failed: ' + err));
    }
  </script>
</body>
</html>
