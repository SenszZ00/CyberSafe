<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$username = htmlspecialchars($_SESSION['username']);
$userType = htmlspecialchars($_SESSION['user_type']);

// === Handle feedback form submission ===
// === Handle feedback form submission with PDO ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    // Grab & trust the POSTed values (PDO will handle escaping)
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO feedback (userID, subject, message, submittedAt)
            VALUES (:userID, :subject, :message, NOW())
        ");
        $stmt->execute([
            ':userID'  => $user_id,
            ':subject' => $subject,
            ':message' => $message
        ]);
        echo "<script>
                alert('Your message has been submitted successfully.');
                window.location = window.location.href.split('#')[0];
              </script>";
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Error submitting your message. Please try again later.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cybersafeABOUT</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles/mainheaderfooter.css">
    <style>
          * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        
        .about-hero {
            background: linear-gradient(rgba(128, 0, 0, 0.8), rgba(128, 0, 0, 0.9)), url('images/team-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            text-align: center;
            padding: 9rem 2rem;
            margin-bottom: 3rem;
        }
        
        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 3rem;
        }
        
        .mission, .team {
            margin-bottom: 3rem;
        }
        
        .team-members {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .team-member {
            margin: 0 auto;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .team-member img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 5px solid #ffcc00;
        }
        
        .contact-info {
            background-color: #f8f8f8;
            padding: 2rem;
            border-radius: 10px;
            margin-top: 2rem;
        }

        /* Mobile Menu Container */
        .mobile-menu-container {
            position: fixed;
            top: 80px;
            right: 0;
            width: 80%;
            height: calc(100vh - 80px);
            background: #f8f8f8;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 1001;
            overflow-y: auto;
            box-shadow: -5px 0 15px rgba(0,0,0,0.2);
            display: none;
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

        .mobile-menu-container.active {
            transform: translateX(0);
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

          .about-hero {
      background-color: maroon;
      color: white;
      text-align: center;
      padding: 6rem 2rem;
      margin-bottom: 3rem;
    }

    .about-hero h2 {
      font-size: 3.5rem;
      font-weight: 800;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin-bottom: 1rem;
    }

    .about-hero h2 span {
      color: #ffcc00;
    }

    .about-hero p {
      font-size: 1.6rem;
      font-style: italic;
      font-weight: bold;
      color: whitesmoke;
    }

    .about-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 2rem 3rem;
    }

    .approach-section {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      padding: 3rem 2rem;
      margin-bottom: 3rem;
      gap: 2rem;
    }

    .approach-text {
      flex: 1 1 40%;
      min-width: 280px;
      text-align: center;
    }

    .approach-text h2 {
      font-size: 2.3rem;
      color: maroon;
      margin-bottom: 1rem;
    }

    .approach-cards {
      flex: 1 1 55%;
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1.5rem;
    }

    .approach-card {
      background-color: #f9f9f9;
      padding: 1.5rem;
      border-radius: 8px;
      border: 1px solid #e0e0e0;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      transition: transform 0.2s ease;
    }

    .approach-card:hover {
      transform: translateY(-5px);
    }

    .approach-card h3 {
      color: maroon;
      margin-bottom: 0.5rem;
    }

    .approach-section {
        display: flex;
        flex-direction: column; /* Stack vertically instead of horizontally */
        padding: 3rem 2rem;
        margin-bottom: 3rem;
        gap: 2rem;
        text-align: center; /* Center all text */
    }

    .approach-text {
        width: 100%; /* Take full width */
        max-width: 800px; /* Limit width for better readability */
        margin: 0 auto; /* Center horizontally */
        text-align: center;
    }

    .approach-cards {
        width: 100%; 
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem; 
    }

    .team {
      margin-bottom: 3rem;
      text-align: center;
    }

    .team h2 {
      font-size: 2.8rem;
      color: maroon;
      margin-bottom: 2rem;
    }

    .team-members {
      display: flex;
      justify-content: center;
      margin-bottom: 2rem;
    }

    .team-member {
      background: white;
      border-radius: 10px;
      padding: 2rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      text-align: center;
    }

    .team-member img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 1rem;
      border: 5px solid #ffcc00;
    }

    .secondary-members {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
    }

    .contact-info {
      background-color: #f8f8f8;
      padding: 2rem;
      border-radius: 10px;
      margin-top: 2rem;
      text-align: center;
    }

    .logos {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 2rem;
      margin-top: 1rem;
    }

    .logos img {
      height: 80px;
      object-fit: contain;
    }
        
        @media (max-width: 768px) {
            .menu-btn {
                margin-left: 10px;
                display: block;
            }

            .desktop-nav {
                display: none;
            }

            .search-container {
                margin: 0;
                max-width: 500px;
            }

            .container {
                grid-template-columns: 1fr;
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

          
            .sidebar {
                display: none;
            }

            
            .mobile-menu-container {
                display: block;
            }

            
            .mobile-nav {
                display: flex;
                flex-direction: column;
                background: transparent;
            }

            .mobile-nav a {
                color: #333;
                padding: 15px 20px;
                border-bottom: 1px solid #ddd;
                text-decoration: none;
            }

            .mobile-nav a:hover, 
            .mobile-nav a.active {
                background: maroon;
                color: white;
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
        }

        /* === Modal-specific CSS additions === */
        #modalOverlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
        }
        #feedbackModal {
            position: fixed;
            top: 50%; left: 50%;
            transform: translate(-50%,-50%);
            background: white;
            padding: 2rem;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            display: none;
            z-index: 1000;
        }
        #feedbackModal h2 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: maroon;
        }
        #feedbackModal .form-group {
            margin-bottom: 1rem;
        }
        #feedbackModal label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: bold;
        }
        #feedbackModal input[type="text"],
        #feedbackModal textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-family: inherit;
        }
        #feedbackModal .modal-actions {
            text-align: right;
            margin-top: 1rem;
        }
        #feedbackModal button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: inherit;
        }
        #feedbackModal .btn-submit {
            background: maroon;
            color: white;
        }
        #feedbackModal .btn-cancel {
            background: #aaa;
            color: white;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    
    <header class="header">
        <div id="logo">
            <img src="images/logo.jpg" alt="CyberSafe USeP Logo"> 
            <img src="images/sdmdlogo.png" alt="sdmdlogo"> 
            <h1>CyberSafe USeP</h1>
        </div>
        <div class="desktop-nav">
            <nav class="nav">
                <a href="cybersafeHOME.php">Home</a>
                <a href="cybersafeARTICLESv2.php">Articles</a>
                <a href="cybersafeABOUT.php" class="active">About</a>
            </nav>
        </div>
        <button class="menu-btn" onclick="toggleMenu()">☰</button>
    </header>

    <!-- Mobile Menu Container -->
    <div class="mobile-menu-container" id="mobileMenuContainer">
        <nav class="mobile-nav">
            <a href="cybersafeHOME.php">Home</a>
            <a href="cybersafeARTICLESv2.php">Articles</a>
            <a href="cybersafeABOUT.php" class="active">About</a>
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
                    <span><i class="fas fa-exclamation-triangle tab-icon"></i> Report Incident</span>
                </a>
            </div>
            <div>
                <p id="notif">Notification Area</p>
            </div>
            <button class="logout-btn" onclick="window.location.href='logout.php'">Log Out</button>
        </aside>
    </div>

    <div class="overlay" id="overlay"></div>

    <section class="about-hero">
        <h2><span>About</span> CyberSafe USeP</h2>
        <p>Committed to safeguarding the USeP community online</p>
    </section>

    <div class="about-container">
        <section class="approach-section" style="background-color: transparent; border: none;">
            <div class="approach-text">
                <h2>Our Mission</h2>
                <p>CyberSafe USeP is dedicated to creating a secure digital environment for all members of the University of Southeastern Philippines community. We provide education, resources, and support to help students, faculty, and staff navigate the complexities of cybersecurity in today's digital world.</p>
            </div>
            <div class="approach-cards">
                <div class="approach-card">
                    <h3>Educate</h3>
                    <p>Educate the USeP community about cybersecurity threats and best practices.</p>
                </div>
                <div class="approach-card">
                    <h3>Inform</h3>
                    <p>Provide timely and relevant cybersecurity information.</p>
                </div>
                <div class="approach-card">
                    <h3>Report</h3>
                    <p>Offer a secure platform for reporting cybersecurity incidents.</p>
                </div>
                <div class="approach-card">
                    <h3>Promote</h3>
                    <p>Promote a culture of cybersecurity awareness across all campuses.</p>
                </div>
            </div>
        </section>

        <!-- Our Team -->
        <section class="team">
            <h2>Our Team</h2>
            <div class="team-members">
                <div class="team-member">
                    <img src="images/justin.png" alt="Justine Mark Lurzano">
                    <h3>Justine Mark Lurzano</h3>
                    <p>Project Leader</p>
                </div>
            </div>
            <div class="secondary-members">
                <div class="team-member">
                    <img src="images/cj.png" alt="Christian Jay Abatas">
                    <h3>Christian Jay Abatas</h3>
                    <p>Systems Analyst</p>
                </div>
                <div class="team-member">
                    <img src="images/vj.png" alt="Veejay Luis Ybanez">
                    <h3>Veejay Luis Ybanez</h3>
                    <p>Quality Assurance</p>
                </div>
                <div class="team-member">
                    <img src="images/arjoy.png" alt="Arjoy Manipis">
                    <h3>Arjoy Manipis</h3>
                    <p>UI/UX Web Designer</p>
                </div>
                <div class="team-member">
                    <img src="images/allen.png" alt="Carl Allen Mancao">
                    <h3>Carl Allen Mancao</h3>
                    <p>Developer</p>
                </div>
            </div>
        </section>

        <!-- Contact -->
        <section class="contact">
            <h2>Contact Us</h2>
            <div class="contact-info">
                <p><strong>Email:</strong> cybersafe@usep.edu.ph</p>
                <p><strong>Phone:</strong> (082) 123-4567</p>
                <p><strong>Office:</strong> IT Center, USeP Main Campus</p>
                <p><strong>Office Hours:</strong> Monday-Friday, 8:00 AM - 5:00 PM</p>
                <div class="logos">
                    <img src="images/logo.jpg" alt="CyberSafe Logo">
                    <img src="images/sdmdlogo.png" alt="SDMD Logo">
                </div>
            </div>

            <div style="text-align:center; margin-top:1.5rem;">
                <button id="openFeedback" class="btn" style="background: maroon; color:white; padding:0.75rem 1.5rem; border:none; border-radius:4px;">
                    Send Message
                </button>
            </div>
        </section>
    </div>

    <!-- Modal Markup -->
    <div id="modalOverlay"></div>
    <div id="feedbackModal">
        <h2>Send Us a Message</h2>
        <form method="POST">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input id="subject" name="subject" type="text" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" required></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" id="cancelFeedback">Cancel</button>
                <button type="submit" name="submit_feedback" class="btn-submit">Submit</button>
            </div>
        </form>
    </div>

    <!-- Footer remains unchanged -->
    <footer>
        &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
    </footer>

    <!-- Existing JS -->
    <script>
        function toggleMenu() {
            const container = document.getElementById('mobileMenuContainer');
            const overlay = document.getElementById('overlay');
            container.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = container.classList.contains('active') ? 'hidden' : '';
        }
        document.getElementById('overlay').addEventListener('click', toggleMenu);
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const container = document.getElementById('mobileMenuContainer');
                const overlayEl = document.getElementById('overlay');
                container.classList.remove('active');
                overlayEl.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Modal JS
        const openBtn  = document.getElementById('openFeedback');
        const cancelBtn= document.getElementById('cancelFeedback');
        const modalOverlay = document.getElementById('modalOverlay');
        const modal    = document.getElementById('feedbackModal');

        openBtn.addEventListener('click', ()=> {
            modalOverlay.style.display = 'block';
            modal.style.display        = 'block';
        });
        cancelBtn.addEventListener('click', ()=> {
            modalOverlay.style.display = 'none';
            modal.style.display        = 'none';
        });
        modalOverlay.addEventListener('click', ()=> {
            modalOverlay.style.display = 'none';
            modal.style.display        = 'none';
        });
    </script>
</body>
</html>
