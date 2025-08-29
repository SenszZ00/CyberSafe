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
    <title>cybersafeHOME</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/mainheaderfooter.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
       
        /* Hero Section */
        .hero {
            position: relative;
            height: 90vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }
        
        .hero .swiper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .hero .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            padding: 2rem;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            max-width: 800px;
        }

        .hero h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }
        
        .cta-button {
            background-color: #ffcc00;
            color: #333;
            padding: 12px 30px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .cta-button:hover {
            background-color: #e6b800;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        /* Features Grid */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            padding: 0 2rem 3rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            text-align: center;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: maroon;
            margin-bottom: 1rem;
        }
        
        /* Stats Section */
        .stats {
            background-color: #f8f8f8;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .stats-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .stat-item {
            margin: 1rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: maroon;
            margin-bottom: 0.5rem;
        }
        
        /* Testimonials */
        .testimonials {
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .testimonial-card {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .mobile-sidebar .profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .mobile-sidebar .logout-btn {
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

        .mobile-sidebar .logout-btn:hover {
            background-color: #b30000;
        }

        
        .mobile-menu-container.active {
            transform: translateX(0);
        }

        .mobile-sidebar .profile-tabs {
            margin-top: 10px;
        }
        
        .mobile-sidebar .profile-tabs #one {
            padding-left: 2px;
        }
        .mobile-sidebar .profile-tabs #one #onee {
            padding-left: 2px;
        }       
        .mobile-sidebar #notif {
            padding-top: 10px;
            border-top: 1px solid #aaaaaa;
            color: #868686;
            font-size: 12px;
            text-align: left;
        }    

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {           
            .menu-btn {
                margin-left: 10px;
                display: block;
            }

            .desktop-nav {
                display: none;
            }

            .container {
                grid-template-columns: 1fr;
            }

            .search-container {
                margin: 0;
                max-width: 500px;
            }
            
            .mobile-menu-container {
                display: block;
            }

            .mobile-sidebar {
                display: flex;
                flex-direction: column;
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

            .overlay.active {
                display: block;
            }

            .hero {
                height: 70vh;
                padding: 2rem;
            }
            
            .hero h2 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Same Header as Articles Page -->
    <header class="header">
        <div id="logo">
            <img src="images/logo.jpg" alt="CyberSafe USeP Logo"> 
            <img src="images/sdmdlogo.png" alt="sdmdlogo"> 
            <h1>CyberSafe USeP</h1>
        </div>
        
        <div class="desktop-nav">
            <nav class="nav">
                <a href="cybersafeHOME.php" class="active">Home</a>
                <a href="cybersafeARTICLESv2.php">Articles</a>
                <a href="cybersafeABOUT.php">About</a>
            </nav>
        </div>
        <button class="menu-btn" onclick="toggleMenu()">☰</button>
    </header>

    <!-- Mobile Menu Container -->
    <div class="mobile-menu-container" id="mobileMenuContainer">
        <nav class="mobile-nav">
            <a href="cybersafeHOME.php" class="active">Home</a>
            <a href="cybersafeARTICLESv2.php">Articles</a>
            <a href="cybersafeABOUT.php">About</a>
        </nav>
        
        <aside class="mobile-sidebar">
            <div class="profile">
                <img src="images/profimg.png" alt="User Profile">
                <h3><?= $username ?></h3>
                <p><?= $userType ?></p>
            </div>
            <div class="profile-tabs">
                <a href="cybersafeARTICLESUB.html" class="profile-tab">
                    <span><i class="fas fa-plus-circle tab-icon"></i> Submit Article</span>
                </a>
                <a href="cybersafeOWNARTICLES.html" class="profile-tab">
                    <span id="one"><i class="fas fa-file-alt tab-icon"></i><span id="onee">My Articles</span></span>
                </a>
                <a href="cybersafeREPORT.html" class="profile-tab">
                    <span><i class="fas fa-exclamation-triangle tab-icon"></i> Report Incident </span>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="swiper hero-swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide"><img src="images/slide1.jpg" alt="Cybersecurity image 1"></div>
                <div class="swiper-slide"><img src="images/slide2.jpg" alt="Cybersecurity image 2"></div>
                <div class="swiper-slide"><img src="images/slide3.jpg" alt="Cybersecurity image 3"></div>
            </div>
        </div>
        <div class="hero-content">
            <h2>Secure Your Digital Life</h2>
            <p>CyberSafe USeP provides resources, tools, and support to help the USeP community navigate the digital world safely and securely.</p>
            <a href="cybersafeABOUT.php" class="cta-button">Learn More</a>
        </div>
    </section>
    
    <!-- Features Grid -->
    <section class="features">
        <div class="feature-card">
            <div class="feature-icon"><img src="images/idea.png" width="100" height="100" alt="Education icon"></div>
            <h3>Educational Resources</h3>
            <p>Access our library of articles and guides to learn about cybersecurity best practices.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon"><img src="images/shield.png" width="100" height="100" alt="Shield icon"></div>
            <h3>Threat Protection</h3>
            <p>Learn how to protect yourself from phishing, malware, and other cyber threats.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon"><img src="images/contract.png" width="100" height="100" alt="Report icon"></div>
            <h3>Incident Reporting</h3>
            <p>Quickly and securely report any cybersecurity incidents you encounter.</p>
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="stats">
        <h2>Our Impact</h2>
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Articles Published</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">1.2K</div>
                <div class="stat-label">Incidents Reported</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">98%</div>
                <div class="stat-label">User Satisfaction</div>
            </div>
        </div>
    </section>
    
    <!-- Testimonials -->
    <section class="testimonials">
        <h2>What Our Community Says</h2>
        <div class="testimonial-card">
            <p>"CyberSafe USeP helped me identify a phishing attempt that could have compromised my university account. Their resources are invaluable!"</p>
            <p><strong>- Computer Science Student</strong></p>
        </div>
    </section>
    
    <!-- Same Footer as Articles Page -->
    <footer>
        &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
    </footer>

    <!-- Same JavaScript as Articles Page -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        function toggleMenu() {
            const container = document.getElementById('mobileMenuContainer');
            const overlay = document.getElementById('overlay');
            container.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = container.classList.contains('active') ? 'hidden' : '';
        }
        document.getElementById('overlay').addEventListener('click', toggleMenu);

        const swiper = new Swiper('.hero-swiper', {
            loop: true,
            autoplay: { delay:2000, disableOnInteraction:false },
            effect: 'fade',
            speed: 1000,
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) toggleMenu();
        });
    </script>
</body>
</html>
