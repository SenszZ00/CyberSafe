<?php
// view-article.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: cybersafeLOGIN.html');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid article ID.";
    exit;
}

$articleID = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT a.*, u.username AS author 
    FROM articles a
    JOIN users u ON a.userID = u.userID
    WHERE a.articleID = ?
");
$stmt->execute([$articleID]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    echo "Article not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Article Preview - <?= htmlspecialchars($article['title']) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
      color: #333;
    }

    header {
      background-color: maroon;
      color: white;
      padding: 1rem 2rem;
      text-align: center;
    }

    .container {
      max-width: 900px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .article-title {
      font-size: 2rem;
      color: maroon;
      margin-bottom: 0.5rem;
    }

    .meta {
      font-size: 0.95rem;
      color: #666;
      margin-bottom: 1.5rem;
    }

    .meta span {
      display: inline-block;
      margin-right: 1rem;
    }

    .status-pill {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: bold;
      text-transform: capitalize;
    }

    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }

    .article-content {
      line-height: 1.7;
      font-size: 1rem;
      white-space: pre-wrap;
      color: #444;
    }

    .back-btn {
      display: inline-block;
      margin-top: 2rem;
      padding: 10px 20px;
      background: maroon;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      transition: background 0.3s ease;
    }

    .back-btn:hover {
      background: #a00000;
    }

    @media (max-width: 600px) {
      .container {
        padding: 1rem;
        margin: 1rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>Article Preview</h1>
  </header>

  <div class="container">
    <div class="article-title"><?= htmlspecialchars($article['title']) ?></div>
    <div class="meta">
      <span><i class="fas fa-user"></i> <?= htmlspecialchars($article['author']) ?></span>
      <span><i class="fas fa-calendar-alt"></i> Submitted: <?= htmlspecialchars($article['submissionDate']) ?></span>
      <?php if ($article['publicationDate']): ?>
        <span><i class="fas fa-globe"></i> Published: <?= htmlspecialchars($article['publicationDate']) ?></span>
      <?php endif; ?>
      <span class="status-pill status-<?= htmlspecialchars($article['status']) ?>">
        <?= htmlspecialchars($article['status']) ?>
      </span>
    </div>

    <div class="article-content">
      <?= nl2br(htmlspecialchars($article['content'])) ?>
    </div>

    <a class="back-btn" href="admDashSUBMITTEDART.php"><i class="fas fa-arrow-left"></i> Back to Articles</a>
  </div>
</body>
</html>
