<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$userID   = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberSafe USeP - My Articles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
          /* Use all existing styles from previous pages */
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

        /* Layout */
        .container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            width: 90%;
            margin: auto;
            padding: 6rem 0 2rem;
            position: relative;
        }

        /* My Articles Section - Updated to match articles page style */
        .my-articles {
            display: grid;
            gap: 1.5rem;
        }

        .my-articles h2 {
            margin-left: 10px;
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

        /* Article content */
        .article-content {
            max-width: 90%;
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

        /* Hashtags styling */
        .hashtags {
            color: #555;
            font-size: 0.9rem;
            font-style: italic;
            margin: 5px 0;
        }

        /* Author section */
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
        
        article h2, article h3 {
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

        /* Article status and actions */
        .article-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-left: 10px;
        }

        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
        }

        .status-approved {
            background-color: #D4EDDA;
            color: #155724;
        }

        .status-rejected {
            background-color: #F8D7DA;
            color: #721C24;
        }

        .article-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .action-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .edit-btn {
            background-color: #007BFF;
            color: white;
        }

        .edit-btn:hover {
            background-color: #0069D9;
        }

        .delete-btn {
            background-color: #DC3545;
            color: white;
        }

        .delete-btn:hover {
            background-color: #C82333;
        }

        .no-articles {
            text-align: center;
            padding: 2rem;
            background-color: #f8f8f8;
            border-radius: 10px;
        }

        .no-articles p {
            margin-bottom: 1rem;
        }

        .create-btn {
            background-color: maroon;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .create-btn:hover {
            background-color: #b30000;
        }

        /* Footer */
        footer {
            background-color: maroon;
            border-top: 5px solid rgb(97, 2, 2);
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: 350px;
        }

        /* Responsive Design */
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
            
            .container {
                grid-template-columns: 1fr;
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
    <div class="container">
        <!-- My Articles Section -->
        <main class="my-articles">
            <h2>My Articles</h2>

            <?php
            // Fetch this user’s articles
            $sql = "SELECT * FROM articles
                    WHERE userID = :uid
                    ORDER BY submissionDate DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['uid' => $userID]);
            $myArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($myArticles) === 0): ?>
                <div class="no-articles">
                    <h3>No Articles Submitted Yet</h3>
                    <p>You haven't submitted any articles yet. Share your cybersecurity knowledge with the community!</p>
                    <button class="create-btn" onclick="window.location.href='cybersafeARTICLESUB.php'">
                        Create Your First Article
                    </button>
                </div>
            <?php
            else:
                foreach ($myArticles as $art):
                    $title    = htmlspecialchars($art['title']);
                    $content  = htmlspecialchars($art['content']);
                    $tags     = array_map('trim', explode(',', $art['keywords']));
                    $status   = $art['status'];
                    $subDate  = date("F j, Y", strtotime($art['submissionDate']));
                    $pubDate  = $art['publicationDate']
                                 ? date("F j, Y", strtotime($art['publicationDate']))
                                 : '';
                    // Determine badge class & label
                    switch ($status) {
                        case 'approved':
                            $badgeClass = 'status-approved';
                            $badgeLabel = 'Approved';
                            $dateLabel  = "Published: $pubDate";
                            $expanded   = 'expanded';
                            $btnLabel   = 'See less...';
                            break;
                        case 'rejected':
                            $badgeClass = 'status-rejected';
                            $badgeLabel = 'Rejected';
                            $dateLabel  = "Submitted: $subDate";
                            $expanded   = '';
                            $btnLabel   = 'See more...';
                            break;
                        default: // pending
                            $badgeClass = 'status-pending';
                            $badgeLabel = 'Pending Review';
                            $dateLabel  = "Submitted: $subDate";
                            $expanded   = '';
                            $btnLabel   = 'See more...';
                            break;
                    }
            ?>
            <article>
                <h3>
                    <?= $title ?>
                    <span class="article-status <?= $badgeClass ?>">
                        <?= $badgeLabel ?>
                    </span>
                </h3>

                <div class="article-content <?= $expanded ?>">
                    <?= nl2br($content) ?>
                </div>
                <button class="see-more-btn" onclick="toggleArticle(this)">
                    <?= $btnLabel ?>
                </button>

                <p class="hashtags">
                    <?php foreach ($tags as $tag): ?>
                        #<?= htmlspecialchars($tag) ?>&nbsp;
                    <?php endforeach; ?>
                </p>

                <div class="article-actions">
                    <button class="action-btn edit-btn"
                            onclick="window.location.href='cybersafeARTICLESUB.php?id=<?= $art['articleID'] ?>'">
                        Edit
                    </button>
                    <button class="action-btn delete-btn"
                            onclick="if(confirm('Delete this article?')) window.location.href='delete-article.php?id=<?= $art['articleID'] ?>'">
                        Delete
                    </button>
                </div>

                <div class="author">
                    <span><?= $dateLabel ?></span>
                </div>
            </article>
            <?php
                endforeach;
            endif;
            ?>
        </main>
    </div>

    <!-- Footer -->
    <footer>
        &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
    </footer>

    <!-- JavaScript -->
    <script>
       // SEE MORE / SEE LESS functionality
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
    </script>
</body>
</html>
