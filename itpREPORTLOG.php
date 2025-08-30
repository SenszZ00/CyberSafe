<?php
// itpREPORTLOG.php

session_start();
require 'db.php';

// Restrict to IT Personnel
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'IT Personnel') {
    header('Location: login.php');
    exit;
}

// Handle filter inputs
$filterType  = $_GET['filterType']  ?? '';
$filterValue = $_GET['filterValue'] ?? '';

$whereClauses = [];
$params       = [];
if ($filterType !== '' && $filterValue !== '') {
    if ($filterType === 'incidentType') {
        $whereClauses[] = "rl.incidentType = :val";
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

$itID = $_SESSION['user_id'];

// Fetch report-log entries for reports assigned to this IT user
$sql = "
  SELECT 
    rl.reportID,
    rl.incidentType,
    rl.resolutionDetails,
    rl.logTimestamp,
    rl.itPersonnelID,
    rl.status
  FROM reportlog AS rl
  JOIN reports    AS r  ON rl.reportID = r.reportID
  WHERE r.itPersonnelID = :itID
";
if ($whereClauses) {
    $sql .= " AND " . implode(' AND ', $whereClauses);
}
$sql .= " ORDER BY rl.logTimestamp ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge(['itID' => $itID], $params));
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
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    footer { margin-top: auto; }
    .mobile-menu-container { top: 80px; }

    /* Header nav */
    .desktop-navv .navv a { text-decoration: none; color: white; font-size: 1rem; padding: 0.5rem 1rem; }
    .desktop-navv .navv a.active,
    .desktop-navv .navv a:hover { color: #ffcc00; border-radius: 5px; }

    /* Page title + filter */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom:10px;
      border-bottom: 1px solid #eee;
    }
    .page-title { font-size: 24px; color: maroon; }

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

    /* Table */
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
      background: maroon;
      color: #fff;
      position: sticky;
      top: 80px;
    }
    .data-table tr:hover { background: #f8f9fa; }

    /* Status badges */
    .status {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 12px;
      text-align: center;
      min-width: 100px;
    }
    .status-pending      { background: #fff3cd; color: #856404; }
    .status-under-review { background: #cce5ff; color: #004085; }
    .status-resolved     { background: #d4edda; color: #155724; }

    @media (max-width: 768px) {
      .data-table { display: block; overflow-x: auto; }
      .filter-form { flex-direction: column; align-items: stretch; }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div id="logo">
      <img src="images/logo.jpg" alt="Logo">
      <img src="images/sdmdlogo.png" alt="SDMD Logo">
      <h1>IT Personnel Dashboard</h1>
    </div>
    <div class="desktop-navv">
      <nav class="navv">
        <a href="itpASSIGNEDREP.php">Assigned Reports</a>
        <a href="itpREPORTLOG.php" class="active">Report Log</a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
      </nav>
    </div>
    <button class="menu-btn" onclick="toggleMenu()">☰</button>
  </header>

  <div class="overlay" id="overlay"></div>
  <div class="mobile-menu-container" id="mobileMenuContainer">
    <nav class="mobile-nav">
      <a href="itpASSIGNEDREP.php">Assigned Reports</a>
      <a href="itpREPORTLOG.php" class="active">Report Log</a>
      <a href="logout.php">Log out</a>
    </nav>
  </div>

  <!-- Main -->
  <div class="admin-container">
    <div class="page-header">
      <h2 class="page-title">Report Log</h2>

      <!-- Filter form -->
      <form method="get" class="filter-form">
        <select name="filterType" id="filterType" onchange="updateInput()">
          <option value="" disabled <?= $filterType === '' ? 'selected' : '' ?>>Filter by…</option>
          <option value="incidentType"   <?= $filterType==='incidentType'   ? 'selected' : '' ?>>Incident Type</option>
          <option value="status"         <?= $filterType==='status'         ? 'selected' : '' ?>>Status</option>
          <option value="submissionDate" <?= $filterType==='submissionDate' ? 'selected' : '' ?>>Date</option>
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
            $caseId = 'REP-'.str_pad($row['reportID'],4,'0',STR_PAD_LEFT);
            $time   = date('Y-m-d H:i:s', strtotime($row['logTimestamp']));
            $cls    = 'status-'.str_replace(' ','-', strtolower($row['status']));
          ?>
          <tr>
            <td><?= $caseId ?></td>
            <td><?= htmlspecialchars($row['incidentType'] ?? '', ENT_QUOTES) ?></td>
            <td><?= nl2br(htmlspecialchars($row['resolutionDetails'] ?? '', ENT_QUOTES)) ?></td>
            <td><?= $time ?></td>
            <td><?= htmlspecialchars($row['itPersonnelID'] ?? '', ENT_QUOTES) ?></td>
            <td>
              <span class="status <?= $cls ?>">
                <?= ucfirst(htmlspecialchars($row['status'] ?? '', ENT_QUOTES)) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Footer -->
  <footer>&copy; 2025 CyberSafe USeP | Promoting a secure online environment for USeP</footer>

  <script>
    // Enable & adapt filter input
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

    // Toggle mobile menu
    function toggleMenu() {
      document.getElementById('mobileMenuContainer').classList.toggle('active');
      document.getElementById('overlay').classList.toggle('active');
      document.body.style.overflow =
        document.getElementById('mobileMenuContainer').classList.contains('active') ? 'hidden' : '';
    }
    document.getElementById('overlay').addEventListener('click', toggleMenu);
  </script>
</body>
</html>