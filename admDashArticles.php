<?php
// admDashArticles.php
session_start();

// Only Admins should access this page; otherwise redirect
if (!isset($_SESSION['user_id'], $_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

// Fetch all approved articles, newest first
$sql = "
    SELECT 
        a.articleID,
        a.title,
        a.content,
        a.keywords,
        u.username AS author
    FROM articles a
    JOIN users u ON a.userID = u.userID
    WHERE a.status = 'approved'
    ORDER BY a.publicationDate DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberSafe USeP - Approved Articles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/mainheaderfooter.css">
    <style>
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
        .container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            width: 80%;
            margin: auto;
            padding: 6rem 0 2rem;
            position: relative;
        }
        .articles {
            display: grid;
            gap: 1.5rem;
        }
        article {
            background-color: #f8f8f8;
            padding: 1rem;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .article-content {
            max-height: 100px;
            overflow: hidden;
            transition: max-height 0.3s ease;
            position: relative;
        }
        .article-content.expanded {
            max-height: none;
        }
        .see-more-btn {
            background: none;
            border: none;
            color: maroon;
            cursor: pointer;
            font-weight: bold;
            padding: 5px 0;
            display: block;
            text-align: right;
            width: 100%;
            margin: 5px 0;
        }
        .see-more-btn:hover {
            text-decoration: underline;
        }
        .article-content:not(.expanded)::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: linear-gradient(to bottom, rgba(248,248,248,0), rgba(248,248,248,1));
        }
        .hashtags {
            color: #555;
            font-size: 0.9rem;
            font-style: italic;
            margin: 5px 0;
        }
        .author {
            display: flex;
            align-items: center;
            margin-top: auto;
        }
        .author img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .author span {
            font-size: 0.9rem;
            font-weight: bold;
            color: #333;
        }
        article h2 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        article p {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        article ul {
            margin-bottom: 0.5rem;
            padding-left: 20px;
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

        footer {
            background-color: maroon;
            border-top: 5px solid rgb(97, 2, 2);
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 200px;
        }
        @media (max-width: 768px) {
            .container {
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div id="logo">
            <img src="images/logo.jpg" alt="CyberSafe USeP Logo"> 
            <img src="images/sdmdlogo.png" alt="sdmdlogo"> 
            <h1>CyberSafe USeP</h1>
        </div>
        <div class="search-container">
            <input type="text" placeholder="Search articles..." class="search-input" id="articleSearch">
        </div>
        <div>
            <a href="admDashSUBMITTEDART.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <main class="articles" id="articles">
            <?php if (count($articles) === 0): ?>
                <p>No approved articles found.</p>
            <?php else: ?>
                <?php foreach ($articles as $art): 
                    // split keywords into tags
                    $tags = array_map('trim', explode(',', $art['keywords']));
                    // determine which image to use based on author
                    $profileImage = ($art['author'] === 'admin') ? 'images/logo.jpg' : 'images/profimg.png';
                ?>
                <article>
                    <h2><?= htmlspecialchars($art['title']) ?></h2>
                    <div class="article-content">
                        <p><?= nl2br(htmlspecialchars($art['content'])) ?></p>
                    </div>
                    <button class="see-more-btn" onclick="toggleArticle(this)">See more...</button>
                    <p class="hashtags">
                        <?php foreach ($tags as $tag): ?>
                            #<?= htmlspecialchars($tag) ?>&nbsp;
                        <?php endforeach; ?>
                    </p>
                    <div class="author">
                        <img src="<?= $profileImage ?>" alt="Profile Picture">
                        <span><?= htmlspecialchars($art['author']) ?></span>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>

    <!-- Footer -->
    <footer>
        &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
    </footer>

    <!-- JavaScript -->
    <script>
        function toggleArticle(button) {
            // grab the preceding .article-content div
            const articleContent = button.previousElementSibling;
            articleContent.classList.toggle('expanded');

            if (articleContent.classList.contains('expanded')) {
                button.textContent = 'See less...';
            } else {
                button.textContent = 'See more...';
            }

            // prevent event bubbling
            event.stopPropagation();
        }

        // Keyword-based live filter
    document.getElementById('articleSearch').addEventListener('input', function() {
        const term = this.value.trim().toLowerCase();

        document.querySelectorAll('#articles article').forEach(article => {
            const hashtagsEl = article.querySelector('.hashtags');
            const rawKeywords = hashtagsEl ? hashtagsEl.innerText.toLowerCase().replace(/#/g, '') : '';
            article.style.display = (!term || rawKeywords.includes(term)) ? '' : 'none';
        });
    });
    </script>
</body>
</html>