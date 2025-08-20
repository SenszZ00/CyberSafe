<?php
session_start();

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please log in.");
}

// ✅ Database config
$servername = "localhost";
$dbUsername = "root";       // CHANGE IF NEEDED
$dbPassword = "";           // CHANGE IF NEEDED
$dbname     = "cybersafeusep";

// ✅ Connect to database
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Sanitize and get form data
$userID    = (int)$_SESSION['user_id'];
$title     = trim($_POST['title']    ?? '');
$category  = trim($_POST['category'] ?? '');
$keywords  = trim($_POST['keywords'] ?? '');
$content   = trim($_POST['content']  ?? '');
$articleID = isset($_POST['articleID']) ? (int)$_POST['articleID'] : 0;

// ✅ Validate required fields
if ($title === '' || $category === '' || $content === '') {
    die("Please fill in all required fields (title, category, content).");
}

if ($articleID > 0) {
    //
    // —— EDIT EXISTING ARTICLE ——
    // Reset status to pending, clear publicationDate, update submissionDate
    //
    $sql = "
        UPDATE articles
        SET title           = ?,
            content         = ?,
            category        = ?,
            keywords        = ?,
            submissionDate  = NOW(),
            status          = 'pending',
            publicationDate = NULL
        WHERE articleID = ?
          AND userID    = ?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param(
        "ssssii",
        $title,
        $content,
        $category,
        $keywords,
        $articleID,
        $userID
    );
    $action = 'updated';
} else {
    //
    // —— INSERT NEW ARTICLE ——
    //
    $sql = "
        INSERT INTO articles
            (userID, title, content, category, keywords)
        VALUES
            (?,      ?,     ?,       ?,        ?)
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param(
        "issss",
        $userID,
        $title,
        $content,
        $category,
        $keywords
    );
    $action = 'created';
}

// ✅ Execute and handle result
if ($stmt->execute()) {
    // Redirect back to “My Articles” or main feed
    // Change destination as you prefer:
    header("Location: cybersafeOWNARTICLES.php?{$action}=1");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

// ✅ Clean up
$stmt->close();
$conn->close();
