<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ——— ADD DB CONNECTION ———
require_once 'db.php';

// ——— RETAIN USER INFO ———
$username = htmlspecialchars($_SESSION['username']);
$userType = htmlspecialchars($_SESSION['user_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberSafe USeP - Articles</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/mainheaderfooter.css">
    <style>
       /* Reset styles */
       * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        /* Body */
        body {
            background-color: #ffffff;
            color: #333;
        }

        /* Mobile Menu Button */
        .menu-btn {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
        }

        /* Layout */
        .container {
            display: grid;
            grid-template-columns: 1fr 3fr;
            gap: 2rem;
            width: 90%;
            margin: auto;
            padding: 6rem 0 2rem;
            position: relative;
        }

        /* Sidebar - Desktop */
        .sidebar {
            background-color: #f8f8f8;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            height: 500px;
            position: sticky;
            top: 100px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
        }

        .sidebar #notif {
            padding-top: 10px;
            border-top: 1px solid #aaaaaa;
            color: #868686;
            font-size: 12px;
            text-align: left;
        }

        .profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .profile h3 {
            font-size: 1.2rem;
            margin-bottom: 5px;
        }

        .profile p {
            font-size: 0.9rem;
            color: #868686;
        }

        .profile-tabs {
            margin-top: 20px;
        }
        .profile-tabs #one { /*<<<<<*/
            padding-left: 2px;
        }

        .profile-tabs #one #onee {/*<<<<<*/
            padding-left: 2px;
        } 
        
        .profile-tabs #two { /*<<<<<*/
            padding-left: 2px;
        }

        .profile-tabs #two #twoo {/*<<<<<*/
            padding-left: 2px;
        } 

        .profile-tab {
            display: block;
            padding: 10px 5px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }

        .profile-tab:hover {
            background-color:  #dfdfdf;
            color: #333;
        }

        .profile-tab span {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logout-btn {
            background-color: maroon;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 5px;
            width: 100%;
            margin-top: 20px; 
        }

        .logout-btn:hover {
            background-color: #b30000;
        }

        /* Articles */
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

        /* Article content */
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

        .mobile-sidebar .profile-tabs {
            margin-top: 10px;
        }
        
        .mobile-sidebar .profile-tabs #one { /*<<<<<*/
            padding-left: 2px;
        }
        
        .mobile-sidebar .profile-tabs #one #onee {/*<<<<<*/
            padding-left: 2px;
        }       

        .mobile-sidebar #notif {
            padding-top: 10px;
            border-top: 1px solid #aaaaaa;
            color: #868686;
            font-size: 12px;
            text-align: left;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {          
            .menu-btn {
                margin-left: 10px;/*<<<<<*/
                display: block;
            }

            .desktop-nav {
                display: none;
            }

            .container {
                grid-template-columns: 1fr;
            }

            .search-container {/*<<<<<*/
                margin: 0;
                max-width: 500px;
            }
            
            .mobile-search {
                padding: 15px;
            }
            
            .mobile-search input {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            /* Hide desktop sidebar */
            .sidebar {
                display: none;
            }

            /* Show mobile menu container */
            .mobile-menu-container {
                display: block;
            }

            /* Mobile sidebar/profile styles */
            .mobile-sidebar {
                display: block;
                text-align: center;
                position: static;
                width: 100%;
                height: auto;
                box-shadow: none;
                padding: 20px;
            }

            .mobile-sidebar .profile {
                padding: 20px 0;
            }

            .overlay.active {
                display: block;
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
        <div class="desktop-nav">
            <nav class="nav">
                <a href="cybersafeHOME.php">Home</a>
                <a href="cybersafeARTICLESv2.php" class="active">Articles</a>
                <a href="cybersafeABOUT.php">About</a>
            </nav>
        </div>
        <button class="menu-btn" onclick="toggleMenu()">☰</button>
    </header>

    <!-- Mobile Menu Container -->
    <div class="mobile-menu-container" id="mobileMenuContainer">
        <nav class="mobile-nav">
            <a href="cybersafeHOME.php">Home</a>
            <a href="cybersafeARTICLESv2.php" class="active">Articles</a>
            <a href="cybersafeABOUT.php">About</a>
        </nav>
        
        <aside class="mobile-sidebar">
            <div class="profile">
                <img src="images/profimg.png" alt="User Profile">
                <h3><?= $username ?></h3>
                <p><?= $userType ?></p>
            </div>
            <div class="profile-tabs">
                <a href="cybersafeARTICLESUB.php" class="profile-tab">
                    <span><i class="fas fa-plus-circle tab-icon"></i> Submit Article</span>
                </a>
                <a href="cybersafeOWNARTICLES.php" class="profile-tab">
                    <span id="one"><i class="fas fa-file-alt tab-icon"></i><span id="onee">My Articles</span></span>
                </a>
                <a href="cybersafeREPORT.php" class="profile-tab">
                    <span><i class="fas fa-exclamation-triangle tab-icon"></i> Report Incident </span>
                </a>
                <a href="cybersafeOWNREPORTS.php" class="profile-tab">
                    <span id="two"><i class="fas fa-clipboard-list"></i> <span id="twoo">My Reports</span></span>
                </a>
            </div>
            <div>
                <p id="notif">Notification Area</p>
            </div>
           <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
        </aside>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <!-- Main Content -->
    <div class="container">
        <!-- Desktop Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-content">
                <div class="profile">
                    <img src="images/profimg.png" alt="User Profile">
                    <h3><?= $username ?></h3>
                    <p><?= $userType ?></p>
                </div>
        
                <div class="profile-tabs">
                    <a href="cybersafeARTICLESUB.php" class="profile-tab">
                        <span><i class="fas fa-plus-circle tab-icon"></i>Submit Article</span>
                    </a>
                    <a href="cybersafeOWNARTICLES.php" class="profile-tab">
                        <span id="one"><i class="fas fa-file-alt tab-icon"></i><span id="onee">My Articles</span></span>
                    </a>
                    <a href="cybersafeREPORT.php" class="profile-tab">
                        <span><i class="fas fa-exclamation-triangle tab-icon"></i>Report Incident</span>
                    </a>
                    <a href="cybersafeOWNREPORTS.php" class="profile-tab">
                        <span id="two"><i class="fas fa-clipboard-list"></i> <span id="twoo">My Reports</span></span>
                    </a>
                </div>

                <div>
                    <p id="notif">Notification Area</p>
                </div>
            </div>
            
            <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
        </aside>

        <!-- Articles Section -->
        <main class="articles" id="articles">
    <?php
    // Fetch approved articles
    $sql = "
        SELECT a.title, a.content, a.keywords, u.username
        FROM articles a
        JOIN users u ON a.userID = u.userID
        WHERE a.status = 'approved'
        ORDER BY a.publicationDate DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($articles) === 0) {
        echo '<p>No approved articles available yet. Please check back later.</p>';
    } else {
        foreach ($articles as $art):
            $title   = htmlspecialchars($art['title']);
            $content = htmlspecialchars($art['content']);
            $tags    = array_map('trim', explode(',', $art['keywords']));
            $author  = htmlspecialchars($art['username']);
            $profileImage = ($author === 'admin') ? 'images/logo.jpg' : 'images/profimg.png';
    ?>
        <article>
            <h2><?= $title ?></h2>

            <!-- FULL content in here, trimmed by CSS! -->
            <div class="article-content">
                <?= nl2br($content) ?>
            </div>

            <button class="see-more-btn" onclick="toggleArticle(this)">See more...</button>

            <p class="hashtags">
                <?php foreach ($tags as $tag): ?>
                    #<?= htmlspecialchars($tag) ?>&nbsp;
                <?php endforeach; ?>
            </p>

           <div class="author">
                <img src="<?= $profileImage ?>" alt="Profile Picture">
                <span><?= $author ?></span>
            </div>
        </article>
    <?php
        endforeach;
    }
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

    // Toggle mobile menu
        function toggleMenu() {
            const container = document.getElementById('mobileMenuContainer');
            const overlay = document.getElementById('overlay');
            
            container.classList.toggle('active');
            overlay.classList.toggle('active');
            
            // Toggle body scroll
            document.body.style.overflow = container.classList.contains('active') ? 'hidden' : '';
        }

        // Close menu when clicking overlay
        document.getElementById('overlay').addEventListener('click', toggleMenu);

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
