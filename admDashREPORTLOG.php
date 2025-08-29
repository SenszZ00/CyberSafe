<?php
// admDashREPORTLOG.php

session_start();
require 'db.php';

// 1) Restrict access to Admins
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'Admin') {
    header('Location: cybersafeLOGIN.html');
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
    }
    elseif ($filterType === 'status') {
        $whereClauses[] = "rl.status = :val";
        $params[':val'] = $filterValue;
    }
    elseif ($filterType === 'submissionDate') {
        $whereClauses[] = "DATE(rl.logTimestamp) = :val";
        $params[':val'] = $filterValue;
    }
}

// 3) Fetch all log entries with optional filtering
$sql = "
  SELECT 
    rl.reportID,
    r.incidentType,
    rl.resolutionDetails,
    rl.logTimestamp,
    rl.itPersonnelID,
    rl.status
  FROM ReportLog rl
  JOIN reports    r ON rl.reportID = r.reportID
";
if ($whereClauses) {
    $sql .= " WHERE " . implode(' AND ', $whereClauses);
}
$sql .= " ORDER BY rl.logTimestamp ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CyberSafe USeP - Report Log</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="styles/admin-dashboard.css">
  <style>
    /* Additional styles to fix footer and layout */
    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        padding-top: 80px; /* To account for fixed header */
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
        margin-top: auto; /* Pushes footer to bottom */
    }
    /* Ensure mobile menu appears below header */
    .mobile-menu-container { top: 80px; }

    /* Report Log Styles */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    .page-title { font-size: 24px; color: maroon; }

    /* Filter form */
    .filter-form { display: flex; gap: 10px; align-items: center; }
    .filter-form select,
    .filter-form input,
    .filter-form button {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }

    /* Table Styles */
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
        font-weight: bold;
        position: sticky;
        top: 80px;
    }
    .data-table tr:hover { background-color: #f8f9fa; }

    /* Log Entry Styles */
    .log-details { font-size: 0.9rem; color: #555; }
    .log-timestamp { font-size: 0.8rem; color: #777; white-space: nowrap; }

    /* Status Styles */
    .status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 12px;
        text-align: center;
        min-width: 80px;
    }
    .status-pending      { background-color: #fff3cd; color: #856404; }
    .status-assigned     { background-color: #d1ecf1; color: #0c5460; }
    .status-under-review { background-color: #cce5ff; color: #004085; }
    .status-resolved     { background-color: #d4edda; color: #155724; }
    .status-rejected     { background-color: #f8d7da; color: #721c24; }

    /* Responsive Table */
    @media (max-width: 768px) {
        .data-table { display: block; overflow-x: auto; }
        .filter-form { flex-direction: column; align-items: stretch; }
    }
  </style>
</head>
<body>
  <!-- Admin Header -->
  <header class="header">
    <div id="logo">
      <img src="images/logo.jpg" alt="CyberSafe USeP Logo">
      <img src="images/sdmdlogo.png" alt="sdmdlogo">
      <h1>Admin Dashboard</h1>
    </div>
    <div class="desktop-nav">
      <nav class="nav">
        <a href="admDashSUBMITTEDART.php">Submitted Articles</a>
        <a href="admDashSUBMITTEDREP.php">Submitted Reports</a>
        <a href="admDashREPORTLOG.php" class="active">Report Log</a>
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
      <a href="admDashSUBMITTEDREP.php">Submitted Reports</a>
      <a href="admDashREPORTLOG.php" class="active">Report Log</a>
      <a href="admMessages.php">User Messages</a>
      <a href="logout.php">Log out</a>
    </nav>
  </div>

  <!-- Main Content -->
  <div class="admin-container">
    <div class="page-header">
      <h2 class="page-title">Incident Report Log</h2>

      <!-- Filter form: select + single input -->
      <form method="get" class="filter-form">
        <select name="filterType" id="filterType" onchange="updateInput()" required>
          <option value="" disabled <?= $filterType===''?'selected':'' ?>>Filter by…</option>
          <option value="incidentType"   <?= $filterType==='incidentType'   ?'selected':'' ?>>Incident Type</option>
          <option value="status"         <?= $filterType==='status'         ?'selected':'' ?>>Status</option>
          <option value="submissionDate" <?= $filterType==='submissionDate' ?'selected':'' ?>>Date</option>
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
            <th>Incident Type</th>
            <th>Resolution Details</th>
            <th>Timestamp</th>
            <th>Handled By</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logs)): ?>
            <tr><td colspan="6" style="text-align:center;">No log entries found.</td></tr>
          <?php else: foreach ($logs as $row):
            $caseId = 'REP-' . str_pad($row['reportID'],4,'0',STR_PAD_LEFT);
            $handledBy = $row['itPersonnelID'] ?: '--';
            $cls = str_replace(' ','-',strtolower($row['status']));
          ?>
          <tr class="log-entry">
            <td><?= $caseId ?></td>
            <td><?= htmlspecialchars($row['incidentType'], ENT_QUOTES) ?></td>
            <td class="log-details"><?= nl2br(htmlspecialchars($row['resolutionDetails'], ENT_QUOTES)) ?></td>
            <td class="log-timestamp"><?= date('Y-m-d H:i:s', strtotime($row['logTimestamp'])) ?></td>
            <td><?= htmlspecialchars($handledBy, ENT_QUOTES) ?></td>
            <td>
              <span class="status status-<?= $cls ?>">
                <?= ucfirst(htmlspecialchars($row['status'], ENT_QUOTES)) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
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
      document.getElementById('mobileMenuContainer').classList.toggle('active');
      document.getElementById('overlay').classList.toggle('active');
      document.body.style.overflow = document.getElementById('mobileMenuContainer').classList.contains('active')
        ? 'hidden'
        : '';
    }
    document.getElementById('overlay').addEventListener('click', toggleMenu);
  </script>
</body>
</html>
