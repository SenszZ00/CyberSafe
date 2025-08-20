<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ——— DB CONNECTION ———
require_once 'db.php';

$userID = $_SESSION['user_id'];

// Fetch this user's reports
$sql = "
    SELECT 
        r.reportID,
        r.incidentType,
        r.description,
        r.submissionTimestamp,
        r.status,
        u.username AS assignedTo,
        r.attachments IS NOT NULL AS hasAttachment
    FROM reports r
    LEFT JOIN users u
      ON r.itPersonnelID = u.userID
    WHERE r.userID = :userID
    ORDER BY r.submissionTimestamp DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':userID' => $userID]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>CyberSafe USeP - My Reports</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            width: 90%;
            margin: auto;
            padding: 6rem 0 2rem;
            position: relative;
        }

        .my-reports {
            display: grid;
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .report-card {
            background-color: #f8f8f8;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            position: relative;
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .report-title {
            font-size: 1.3rem;
            color: maroon;
            margin: 0;
        }

        .report-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
        }

        .status-investigating {
            background-color: #CCE5FF;
            color: #004085;
        }

        .status-resolved {
            background-color: #D4EDDA;
            color: #155724;
        }

        .report-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
        }

        .report-desc {
            margin: 1rem 0;
            line-height: 1.6;
        }

        .report-details {
            background-color: #fff;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            border-left: 4px solid maroon;
        }

        .report-details p {
            margin: 0.5rem 0;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
        }

        /* Delete Button Styles */
        .report-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
            gap: 10px;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .no-reports {
            text-align: center;
            padding: 3rem;
            background-color: #f8f8f8;
            border-radius: 10px;
            max-width: 600px;
            margin: 2rem auto;
        }

        .no-reports h3 {
            color: maroon;
            margin-bottom: 1rem;
        }

        .no-reports p {
            margin-bottom: 1.5rem;
            color: #666;
        }

        .create-btn {
            background-color: maroon;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .create-btn:hover {
            background-color: #b30000;
        }

        footer {
            background-color: maroon;
            border-top: 5px solid rgb(97, 2, 2);
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 220px;
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
            
            .container {
                padding: 5rem 0 1.5rem;
            }

            .report-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .report-status {
                align-self: flex-start;
            }

            .report-actions {
                justify-content: flex-start;
            }
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
    <a href="cybersafeARTICLESv2.php" class="back-btn">
      <i class="fas fa-arrow-left"></i>
    </a>
  </header>

  <div class="container">
    <main class="my-reports">
      <h2>My Submitted Reports</h2>

      <?php if (count($reports) === 0): ?>
        <div class="no-reports">
          <h3>No Reports Submitted Yet</h3>
          <p>You haven't submitted any cybersecurity incident reports yet.</p>
          <button class="create-btn" onclick="window.location.href='cybersafeREPORT.php'">
            Report an Incident
          </button>
        </div>
      <?php else: ?>
        <?php foreach ($reports as $r): 
          // Map status to CSS class & label
          switch ($r['status']) {
            case 'pending':
              $statusClass = 'status-pending';
              $statusLabel = 'Pending Review';
              break;
            case 'under review':
              $statusClass = 'status-investigating';
              $statusLabel = 'Under Investigation';
              break;
            case 'resolved':
              $statusClass = 'status-resolved';
              $statusLabel = 'Resolved';
              break;
            default:
              $statusClass = '';
              $statusLabel = htmlspecialchars($r['status']);
          }
          // Format dates and IDs
          $submitted = date("F j, Y", strtotime($r['submissionTimestamp']));
          $caseId    = sprintf("REP-%s-%03d",
                       date("Y", strtotime($r['submissionTimestamp'])),
                       $r['reportID']);
        ?>
          <div class="report-card">
            <div class="report-header">
              <h3 class="report-title"><?= htmlspecialchars($r['incidentType']) ?></h3>
              <span class="report-status <?= $statusClass ?>"><?= $statusLabel ?></span>
            </div>
            <div class="report-meta">
              Submitted: <?= $submitted ?> | Case ID: <?= $caseId ?>
            </div>
            <div class="report-desc">
              <?= nl2br(htmlspecialchars($r['description'])) ?>
            </div>
            <div class="report-details">
              <p><span class="detail-label">Type:</span> <?= htmlspecialchars($r['incidentType']) ?></p>
              <?php if ($r['hasAttachment']): ?>
                <p><span class="detail-label">Attachments:</span> Yes</p>
              <?php endif; ?>
              <?php if (!empty($r['assignedTo'])): ?>
                <p><span class="detail-label">IT Personnel:</span> <?= htmlspecialchars($r['assignedTo']) ?></p>
              <?php endif; ?>
            </div>
            <div class="report-actions">
              <button class="delete-btn"
        onclick="if(confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
          window.location.href='delete-report.php?id=<?= $r['reportID'] ?>';
        }">
       <i class="fas fa-trash-alt"></i> Delete Report
          </button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </main>
  </div>

  <footer>
    &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
  </footer>

  <script>
    function deleteReport(caseId) {
      if (confirm(`Are you sure you want to delete report ${caseId}? This action cannot be undone.`)) {
        // Implement actual deletion via AJAX or form submission in your app
        console.log(`Deleted report: ${caseId}`);
      }
    }
  </script>
</body>
</html>
