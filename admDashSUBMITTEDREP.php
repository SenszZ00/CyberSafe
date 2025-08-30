<?php
// admDashSUBMITTEDREP.php
session_start();
require 'db.php';

// 1) Only Admins
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

// 2) Handle filter inputs
$filterType  = $_GET['filterType']  ?? '';
$filterValue = $_GET['filterValue'] ?? '';

$whereClauses = [];
$params       = [];
if ($filterType !== '' && $filterValue !== '') {
    if ($filterType === 'incidentType') {
        $whereClauses[] = "r.incidentType = :val";
        $params[':val'] = $filterValue;
    } elseif ($filterType === 'status') {
        $whereClauses[] = "r.status = :val";
        $params[':val'] = $filterValue;
    } elseif ($filterType === 'submissionDate') {
        $whereClauses[] = "DATE(r.submissionTimestamp) = :val";
        $params[':val'] = $filterValue;
    }
}

// 3) Build & run reports query
$sql = "
    SELECT 
      r.reportID,
      r.userID,
      r.anonymousFlag,
      r.incidentType,
      r.description,
      r.attachments,
      r.submissionTimestamp,
      r.status,
      r.itPersonnelID,
      u.username AS assignedTo
    FROM reports r
    LEFT JOIN users u
      ON r.itPersonnelID = u.userID
";
if ($whereClauses) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}
$sql .= " ORDER BY r.submissionTimestamp DESC";

$stmt    = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4) Load IT Personnel list
$itStmt = $pdo->prepare("
    SELECT userID, username
    FROM users
    WHERE userType = 'IT Personnel'
    ORDER BY username
");
$itStmt->execute();
$itPersonnel = $itStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>CyberSafe USeP - Submitted Reports</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="styles/admin-dashboard.css">
  <style>
    /* paste your existing styles from admDashSUBMITTEDREP here */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        padding-top: 80px;
    }
    .admin-container {
        flex: 1;
        padding: 20px;
        margin: 20px;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    footer {
        margin-top: auto;
    }
    .mobile-menu-container {
        top: 80px;
    }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .page-title {
        font-size: 24px;
        color: maroon;
    }
    .filter-form {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .filter-form select,
    .filter-form input,
    .filter-form button {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    .data-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
    }
    .data-table th, .data-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
    }
    .data-table th {
        background-color: maroon;
        color: white;
        position: sticky;
        top: 80px;
    }
    .data-table tr:hover {
        background-color: #f8f9fa;
    }
    .status { display: inline-block; padding: 5px 10px; border-radius: 20px; font-weight: bold; font-size: 12px; min-width: 80px; text-align: center; }
    .status-pending      { background-color: #fff3cd; color: #856404; }
    .status-investigating{ background-color: #cce5ff; color: #004085; }
    .status-resolved     { background-color: #d4edda; color: #155724; }
    .action-btns { display: flex; gap: 5px; }
    .action-btn { padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; color: white; display: inline-flex; align-items: center; gap: 3px; }
    .attachment-btn { background-color: #6f42c1; }
    .assign-dropdown { padding: 5px; border-radius: 4px; border: 1px solid #ddd; min-width: 150px; font-size: 12px; }
    @media (max-width: 768px) {
      .data-table { display: block; overflow-x: auto; }
      .action-btns { flex-direction: column; gap: 3px; }
      .action-btn, .assign-dropdown { width: 100%; }
      .filter-form { flex-direction: column; align-items: stretch; }
    }
  </style>
</head>
<body>
  <!-- Header & Navigation -->
  <header class="header">
    <div id="logo">
      <img src="images/logo.jpg" alt="CyberSafe USeP Logo">
      <img src="images/sdmdlogo.png" alt="sdmdlogo">
      <h1>Admin Dashboard</h1>
    </div>
    <div class="desktop-nav">
      <nav class="nav">
        <a href="admDashSUBMITTEDART.php">Submitted Articles</a>
        <a href="admDashSUBMITTEDREP.php" class="active">Submitted Reports</a>
        <a href="admDashREPORTLOG.php">Report Log</a>
        <a href="admMessages.php">Messages</a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
      </nav>
    </div>
    <button class="menu-btn" onclick="toggleMenu()">☰</button>
  </header>

  <!-- Mobile Menu -->
  <div class="overlay" id="overlay"></div>
  <div class="mobile-menu-container" id="mobileMenuContainer">
    <nav class="mobile-nav">
      <a href="admDashSUBMITTEDART.php">Submitted Articles</a>
      <a href="admDashSUBMITTEDREP.php" class="active">Submitted Reports</a>
      <a href="admDashREPORTLOG.php">Report Log</a>
      <a href="admMessages.php">User Messages</a>
      <a href="logout.php">Log out</a>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="admin-container">
    <div class="page-header">
      <h2 class="page-title">Submitted Reports</h2>

      <!-- Filter form -->
      <form method="get" class="filter-form">
        <select name="filterType" id="filterType" onchange="updateInput()">
          <option value="" disabled <?= $filterType === '' ? 'selected' : '' ?>>Filter by…</option>
          <option value="incidentType"   <?= $filterType === 'incidentType'   ? 'selected' : '' ?>>Incident Type</option>
          <option value="status"         <?= $filterType === 'status'         ? 'selected' : '' ?>>Status</option>
          <option value="submissionDate" <?= $filterType === 'submissionDate' ? 'selected' : '' ?>>Date</option>
        </select>
        <input
          type="text"
          name="filterValue"
          id="filterInput"
          placeholder="Select filter type first"
          value="<?= htmlspecialchars($filterValue) ?>"
          disabled
        />
        <button type="submit" id="filterGo" disabled>Go</button>
      </form>
    </div>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Report ID</th>
            <th>User</th>
            <th>Type</th>
            <th>Description</th>
            <th>Attach</th>
            <th>Anonymous</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Assigned To</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reports)): ?>
            <tr><td colspan="9" style="text-align:center;">No reports found.</td></tr>
          <?php else: foreach ($reports as $r):
              switch ($r['status']) {
                case 'pending':      $cls='status-pending';        $lbl='Pending';            break;
                case 'under review': $cls='status-investigating';  $lbl='Under Review';       break;
                case 'resolved':     $cls='status-resolved';       $lbl='Resolved';           break;
                default:             $cls='';                      $lbl=htmlspecialchars($r['status']);
              }
              $caseId = sprintf("REP-%s-%03d",
                         date("Y", strtotime($r['submissionTimestamp'])),
                         $r['reportID']);
          ?>
            <tr data-report="<?= $r['reportID'] ?>">
              <td><?= $caseId ?></td>
              <td><?= $r['anonymousFlag'] ? 'Anonymous' : htmlspecialchars($r['userID']) ?></td>
              <td><?= htmlspecialchars($r['incidentType']) ?></td>
              <td><?= nl2br(htmlspecialchars($r['description'])) ?></td>
              <td>
                <?php if ($r['attachments'] !== null): ?>
                  <button class="action-btn attachment-btn" onclick="viewAttachments(<?= $r['reportID'] ?>)">
                    <i class="fas fa-paperclip"></i> View
                  </button>
                <?php else: ?>None<?php endif; ?>
              </td>
              <td><?= $r['anonymousFlag'] ? 'Yes' : 'No' ?></td>
              <td><?= date("Y-m-d H:i", strtotime($r['submissionTimestamp'])) ?></td>
              <td><span class="status <?= $cls ?>"><?= $lbl ?></span></td>
              <td>
                <select class="assign-dropdown" onchange="assignReport(<?= $r['reportID'] ?>, this.value)">
                  <option value="">— Unassigned —</option>
                  <?php foreach ($itPersonnel as $it): ?>
                    <option value="<?= $it['userID'] ?>" <?= $it['userID'] === $r['itPersonnelID'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($it['username']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <footer>
    &copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP
  </footer>

  <script>
    function updateInput() {
      const sel = document.getElementById('filterType').value;
      const inp = document.getElementById('filterInput');
      const btn = document.getElementById('filterGo');
      if (!sel) {
        inp.disabled = true;
        btn.disabled = true;
        inp.type = 'text';
        inp.placeholder = 'Select filter type first';
        return;
      }
      inp.disabled = false;
      btn.disabled = false;
      if (sel === 'submissionDate') {
        inp.type = 'date';
        inp.placeholder = '';
      } else {
        inp.type = 'text';
        inp.placeholder = sel === 'incidentType' ? 'e.g. malware' : 'e.g. pending';
      }
    }
    document.addEventListener('DOMContentLoaded', updateInput);

    function toggleMenu() {
      const c = document.getElementById('mobileMenuContainer');
      const o = document.getElementById('overlay');
      c.classList.toggle('active');
      o.classList.toggle('active');
      document.body.style.overflow = c.classList.contains('active') ? 'hidden' : '';
    }
    document.getElementById('overlay').addEventListener('click', toggleMenu);

    function viewAttachments(reportId) {
      window.location.href = 'viewAttachment.php?reportID=' + reportId;
    }
    function assignReport(reportId, itId) {
      if (!itId) return;
      if (!confirm(`Assign report ${reportId} to ${itId}?`)) return;
      fetch('assign_report.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `reportID=${reportId}&itPersonnelID=${itId}`
      })
      .then(r=>r.json())
      .then(j=>{ if (!j.success) alert('Error: '+ j.error); })
      .catch(e=>alert('Assignment failed.'));
    }
  </script>
</body>
</html>
