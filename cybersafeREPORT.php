<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';
$error = '';

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine anonymous flag
    $submitAnonymously = ($_POST['anonymous'] ?? '0') === '1';
    $userID = $submitAnonymously ? null : $_SESSION['user_id'];

    // Sanitize inputs
    $incidentType = trim($_POST['incidentType'] ?? '');
    $description  = trim($_POST['description']  ?? '');
    $incidentDate = $_POST['incidentDate'] ?? '';

    // Handle file upload (single file assumed)
    $attachments     = null;
    $attachmentMime  = null;
    $attachmentName  = null;
    if (
        !empty($_FILES['evidence']['tmp_name'][0])
        && is_uploaded_file($_FILES['evidence']['tmp_name'][0])
    ) {
        $tmp = $_FILES['evidence']['tmp_name'][0];
        $attachments    = file_get_contents($tmp);
        $attachmentMime = $_FILES['evidence']['type'][0];   // e.g. "application/pdf"
        $attachmentName = $_FILES['evidence']['name'][0];   // original filename
    }

    // Validate required fields
    if (empty($incidentType) || empty($description) || empty($incidentDate)) {
        $error = "Please fill in all required fields.";
    } else {
        // Insert into reports (including mime & filename)
        $sql = "
          INSERT INTO reports 
            (userID, incidentType, description, attachments, attachmentMime, attachmentName, submissionTimestamp)
          VALUES 
            (:userID, :incidentType, :description, :attachments, :attachmentMime, :attachmentName, :submissionTimestamp)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':userID', $userID, $userID === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':incidentType', $incidentType);
        $stmt->bindValue(':description', $description);

        if ($attachments !== null) {
            $stmt->bindParam(':attachments', $attachments, PDO::PARAM_LOB);
            $stmt->bindValue(':attachmentMime',  $attachmentMime);
            $stmt->bindValue(':attachmentName',  $attachmentName);
        } else {
            $stmt->bindValue(':attachments',     null, PDO::PARAM_NULL);
            $stmt->bindValue(':attachmentMime',  null, PDO::PARAM_NULL);
            $stmt->bindValue(':attachmentName',  null, PDO::PARAM_NULL);
        }

        // Combine date from form with current time
        $stmt->bindValue(':submissionTimestamp', $incidentDate . ' ' . date('H:i:s'));

        if ($stmt->execute()) {
            header("Location: cybersafeARTICLESv2.php");
            exit;
        } else {
            $error = $stmt->errorInfo()[2] ?? 'Unknown error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>CyberSafe USeP – Report Incident</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; font-family:Arial,sans-serif; }
    .header {
      display:flex; justify-content:space-between; align-items:center;
      background:maroon; border-bottom:7px solid rgb(97,2,2);
      padding:1rem 3rem; position:fixed; width:100%; top:0; left:0; z-index:1001;
    }
    .header img { width:50px; height:50px; }
    .header #logo { display:flex; gap:10px; align-items:center; color:#fff; }
    .back-btn { color:#fff; font-size:1.5rem; text-decoration:none; margin-right:20px; }
    .back-btn:hover { color:#ffcc00; }
    .report-container {
      max-width:1000px; margin:120px auto 2rem; padding:2rem;
      background:#f8f8f8; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.1);
    }
    .report-container h2 { color:maroon; margin-bottom:1.5rem; text-align:center; }
    .form-group { margin-bottom:1.5rem; }
    .form-group label { display:block; margin-bottom:0.5rem; font-weight:bold; }
    .form-control {
      width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;
      font-size:1rem;
    }
    textarea.form-control { min-height:150px; resize:vertical; }
    .submit-btn {
      background:maroon; color:#fff; border:none; padding:12px 30px;
      font-size:1rem; border-radius:5px; cursor:pointer;
      display:block; margin:2rem auto 0; transition:background-color .3s;
    }
    .submit-btn:hover { background:#b30000; }
    footer {
      background:maroon; border-top:5px solid rgb(97,2,2);
      color:#fff; text-align:center; padding:1rem; margin-top:2rem;
    }
    @media(max-width:768px) {
    .header {padding: 18px 15px;}
    .header #logo img { display: none;}
    .header #logo h1 { margin-right: 10px; font-size: 1.2rem;white-space: nowrap;  max-width: 160px; padding: 5px;}     
    .report-container { margin-top:90px; max-width:450px; }
    }
    .error { color:red; text-align:center; margin-bottom:1rem; }

    /* Toggle‐switch styles */
    .toggle-switch {
      position: relative; width: 50px; height: 24px; background: #ccc;
      border-radius: 12px; cursor: pointer; transition: background 0.3s;
      display: inline-block; vertical-align: middle;
    }
    .toggle-switch .toggle-knob {
      position: absolute; top:2px; left:2px; width:20px; height:20px;
      background:white; border-radius:50%; transition:left 0.3s;
    }
    .toggle-switch.active { background:maroon; }
    .toggle-switch.active .toggle-knob { left:28px; }
    .toggle-label {
      font-size:0.9rem; margin-left:10px; vertical-align:middle;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div id="logo">
      <img src="images/logo.jpg" alt="CyberSafe USeP Logo">
      <img src="images/sdmdlogo.png" alt="SDMD Logo">
      <h1>CyberSafe USeP</h1>
    </div>
    <a href="cybersafeARTICLESv2.php" class="back-btn">
      <i class="fas fa-arrow-left"></i>
    </a>
  </header>

  <!-- Report Form -->
  <main class="report-container">
    <h2>Report a Cybersecurity Incident</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="incidentForm" method="POST" enctype="multipart/form-data">
      <!-- Anonymous toggle -->
      <div class="form-group">
        <div class="toggle-switch <?= (($_POST['anonymous'] ?? '')==='1') ? 'active':'' ?>"
             id="anonymousToggle">
          <div class="toggle-knob"></div>
        </div>
        <span class="toggle-label">Post Anonymously</span>
        <input type="hidden" name="anonymous" id="anonymousInput"
               value="<?= (($_POST['anonymous'] ?? '')==='1') ? '1':'0' ?>">
      </div>

      <div class="form-group">
        <label for="incidentType">Type of Incident</label>
        <select name="incidentType" id="incidentType" class="form-control" required>
          <option value="">Select an incident type</option>
          <option value="phishing"   <?= ($_POST['incidentType'] ?? '')==='phishing'   ? 'selected':'' ?>>Phishing Attempt</option>
          <option value="malware"    <?= ($_POST['incidentType'] ?? '')==='malware'    ? 'selected':'' ?>>Malware Infection</option>
          <option value="hacking"    <?= ($_POST['incidentType'] ?? '')==='hacking'    ? 'selected':'' ?>>Unauthorized Access</option>
          <option value="harassment" <?= ($_POST['incidentType'] ?? '')==='harassment' ? 'selected':'' ?>>Cyber Harassment</option>
          <option value="other"      <?= ($_POST['incidentType'] ?? '')==='other'      ? 'selected':'' ?>>Other</option>
        </select>
      </div>

      <div class="form-group">
        <label for="incidentDate">Date of Incident</label>
        <input type="date" name="incidentDate" id="incidentDate"
               class="form-control"
               value="<?= htmlspecialchars($_POST['incidentDate'] ?? '') ?>"
               required>
      </div>

      <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" class="form-control" required
                  placeholder="Please provide detailed information about the incident..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label for="evidence">Upload Evidence (Screenshots, Files, etc.)</label>
        <input type="file" name="evidence[]" id="evidence" class="form-control" multiple>
      </div>

      <button type="submit" class="submit-btn">Submit Report</button>
    </form>
  </main>

  <!-- Footer -->
  <footer>
    &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
  </footer>

  <script>
    // Toggle anonymous switch
    const toggle = document.getElementById('anonymousToggle');
    const hidden = document.getElementById('anonymousInput');
    toggle.addEventListener('click', () => {
      toggle.classList.toggle('active');
      hidden.value = toggle.classList.contains('active') ? '1' : '0';
    });
  </script>
</body>
</html>
