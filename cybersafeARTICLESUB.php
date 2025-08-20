<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'db.php';

$userID    = $_SESSION['user_id'];
$articleID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Default form values
$title     = '';
$category  = '';
$content   = '';
$keywords  = '';

// If we're editing, load existing article (and confirm ownership)
if ($articleID > 0) {
    $stmt = $pdo->prepare("
        SELECT title, category, content, keywords
        FROM articles
        WHERE articleID = :aid
          AND userID    = :uid
    ");
    $stmt->execute(['aid' => $articleID, 'uid' => $userID]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $title    = $row['title'];
        $category = $row['category'];
        $content  = $row['content'];
        $keywords = $row['keywords'];
    } else {
        // No such article or not owned by user
        header("Location: cybersafeOWNARTICLES.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= $articleID ? 'Edit' : 'Submit' ?> Article</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
     /* (All existing styles remain the same — no changes made) */
     * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #ffffff;
            color: #333;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: maroon;
            border-bottom: 7px solid rgb(97, 2, 2);
            padding: 1rem 3rem;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1001;
        }

        .header img {
            width: 50px;
            height: 50px;
        }

        .header #logo {
            gap: 10px;
            align-items: center;
            color: white;
            display: flex;
        }

        .back-btn {
            color: white;
            font-size: 1.5rem;
            text-decoration: none;
            margin-right: 20px;
        }

        .back-btn:hover {
            color: #ffcc00;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
            width: 90%;
            margin: auto;
            padding: 6rem 0 2rem;
            position: relative;
        }

        .article-form {
            max-width: 1000px;
            margin: 2rem auto;
            margin-top: 120px;
            background-color: #f8f8f8;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .article-form h2 {
            color: maroon;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        textarea.form-control {
            min-height: 200px;
            resize: vertical;
        }

        .hashtags-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .hashtags-hint {
            font-size: 0.8rem;
            color: #868686;
            margin-top: 0.5rem;
        }

        .submit-btn {
            background-color: maroon;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            margin: 2rem auto 0;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #b30000;
        }

        footer {
            background-color: maroon;
            border-top: 5px solid rgb(97, 2, 2);
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .header {
                padding: 18px 15px;
            }

            .header #logo img {
                display: none;
            }

            .header #logo h1 {
                margin-right: 10px;
                font-size: 1.2rem;
                white-space: nowrap; 
                max-width: 160px; 
                padding: 5px;
            }

            .article-form {
                margin-top: 90px;
                max-width: 450px;
            }

            .container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }
        }
  </style>
</head>
<body>
  <!-- Header with Back Button -->
  <header class="header">
    <div id="logo">
      <img src="images/logo.jpg" alt="CyberSafe USeP Logo">
      <img src="images/sdmdlogo.png" alt="sdmdlogo">
      <h1>CyberSafe USeP</h1>
    </div>
    <div>
      <a href="cybersafeARTICLESv2.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
      </a>
    </div>
  </header>

  <!-- Main Content -->
  <main class="article-form">
    <h2><?= $articleID ? 'Edit Your Article' : 'Submit New Article' ?></h2>
    <form id="articleSubmissionForm" action="submit_article.php" method="POST">
      <?php if ($articleID): ?>
        <input type="hidden" name="articleID" value="<?= $articleID ?>">
      <?php endif; ?>

      <div class="form-group">
        <label for="articleTitle">Article Title</label>
        <input
          type="text"
          id="articleTitle"
          name="title"
          class="form-control"
          required
          placeholder="Enter article title"
          value="<?= htmlspecialchars($title) ?>"
        >
      </div>

      <div class="form-group">
        <label for="articleCategory">Category</label>
        <select
          id="articleCategory"
          name="category"
          class="form-control"
          required
        >
          <option value="" <?= $category=='' ? 'selected':'' ?>>Select a category</option>
          <?php
            $cats = [
              'phishing'=>'Phishing Awareness',
              'malware'=>'Malware Protection',
              'passwords'=>'Password Security',
              'privacy'=>'Data Privacy',
              'social'=>'Social Engineering',
              'bullying'=>'Cyberbullying',
              'other'=>'Other'
            ];
            foreach ($cats as $val=>$label):
          ?>
            <option value="<?= $val ?>" <?= $category===$val ? 'selected':'' ?>>
              <?= $label ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="articleContent">Article Content</label>
        <textarea
          id="articleContent"
          name="content"
          class="form-control"
          required
          placeholder="Write your article content here…"
        ><?= htmlspecialchars($content) ?></textarea>
      </div>

      <div class="form-group">
        <label for="articleHashtags">Keywords/Hashtags</label>
        <input
          type="text"
          id="articleHashtags"
          name="keywords"
          class="hashtags-input"
          placeholder="#cybersecurity #phishing #safety"
          value="<?= htmlspecialchars($keywords) ?>"
        >
        <p class="hashtags-hint">Separate keywords with spaces (e.g. #security #awareness)</p>
      </div>

      <button type="submit" class="submit-btn">
        <?= $articleID ? 'Update Article' : 'Submit Article' ?>
      </button>
    </form>
  </main>

  <!-- Footer -->
  <footer>
    &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
  </footer>
</body>
</html>
