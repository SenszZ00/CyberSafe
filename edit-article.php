<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'db.php';

$userID = $_SESSION['user_id'];

// Get article ID from query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: cybersafeOWNARTICLES.php");
    exit;
}
$articleID = (int)$_GET['id'];

// Fetch the article and ensure it belongs to this user
$stmt = $pdo->prepare("SELECT * FROM articles WHERE articleID = :aid AND userID = :uid");
$stmt->execute(['aid' => $articleID, 'uid' => $userID]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$article) {
    // No such article or doesn't belong to user
    header("Location: cybersafeOWNARTICLES.php");
    exit;
}

// If form submitted, process update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = trim($_POST['title']);
    $content  = trim($_POST['content']);
    $keywords = trim($_POST['keywords']);

    // Basic validation
    if ($title === '' || $content === '' || $keywords === '') {
        $error = "All fields are required.";
    } else {
        $upd = $pdo->prepare(
            "UPDATE articles
             SET title = :title,
                 content = :content,
                 keywords = :keywords,
                 submissionDate = NOW(),
                 status = 'pending',
                 publicationDate = NULL
             WHERE articleID = :aid AND userID = :uid"
        );
        $upd->execute([
            'title'    => $title,
            'content'  => $content,
            'keywords' => $keywords,
            'aid'      => $articleID,
            'uid'      => $userID
        ]);

        // Redirect back to My Articles
        header("Location: cybersafeOWNARTICLES.php?updated=1");
        exit;
    }
}

// Pre-fill form values
$formTitle    = htmlspecialchars($article['title']);
$formContent  = htmlspecialchars($article['content']);
$formKeywords = htmlspecialchars($article['keywords']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Article</title>
    <style>
      /* Minimal styling—adapt to your CSS */
      body { font-family: Arial; padding: 2rem; }
      form { max-width: 600px; margin: auto; }
      label { display: block; margin-top: 1rem; }
      input[type=text], textarea { width: 100%; padding: .5rem; }
      button { margin-top: 1rem; padding: .5rem 1rem; }
      .error { color: red; }
    </style>
</head>
<body>
    <h1>Edit Article</h1>
    <?php if (!empty($error)): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>
            Title
            <input type="text" name="title" value="<?= $formTitle ?>">
        </label>

        <label>
            Content
            <textarea name="content" rows="10"><?= $formContent ?></textarea>
        </label>

        <label>
            Keywords (comma-separated)
            <input type="text" name="keywords" value="<?= $formKeywords ?>">
        </label>

        <button type="submit">Save Changes</button>
        <button type="button" onclick="window.location='cybersafeOWNARTICLES.php'">Cancel</button>
    </form>
</body>
</html>
