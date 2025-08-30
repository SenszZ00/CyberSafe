<?php
// itpASSIGNEDREP.php

session_start();
require 'db.php';

// 1) Restrict to IT Personnel
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'IT Personnel') {
    header('Location: login.php');
    exit;
}

// 2) Handle AJAX status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    $reportID  = (int)$_POST['reportID'];
    $newStatus = $_POST['newStatus'];

    $allowed = ['pending','under review','resolved'];
    if (!in_array($newStatus, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT status 
        FROM reports 
        WHERE reportID = :id
          AND itPersonnelID = :itID
    ");
    $stmt->execute(['id' => $reportID, 'itID' => $_SESSION['user_id']]);
    $currentStatus = $stmt->fetchColumn();

    if ($currentStatus === $newStatus) {
        echo json_encode(['success' => true]);
        exit;
    }

    $upd = $pdo->prepare("
      UPDATE reports
         SET status = :status
       WHERE reportID = :id
         AND itPersonnelID = :itID
    ");
    $upd->execute([
      'status' => $newStatus,
      'id'     => $reportID,
      'itID'   => $_SESSION['user_id']
    ]);

    echo json_encode(['success' => true]);
    exit;
}

// 3) Handle filter inputs
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
        $whereClauses[] = "r.status = :val";
        $params[':val'] = $filterValue;
    }
    elseif ($filterType === 'submissionDate') {
        $whereClauses[] = "DATE(r.submissionTimestamp) = :val";
        $params[':val'] = $filterValue;
    }
}

// 4) Fetch assigned reports
$itID = $_SESSION['user_id'];
$sql = "
  SELECT 
    r.reportID,
    r.incidentType,
    r.description,
    r.attachments,
    r.submissionTimestamp,
    r.status,
    r.anonymousFlag,
    r.userID
  FROM reports AS r
  WHERE r.itPersonnelID = :itID
";
if ($whereClauses) {
    $sql .= " AND " . implode(' AND ', $whereClauses);
}
$sql .= " ORDER BY r.submissionTimestamp DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute(array_merge(['itID' => $itID], $params));
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CyberSafe USeP - Assigned Reports</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="styles/admin-dashboard.css">
  <style>
    body { display: flex; flex-direction: column; min-height: 100vh; padding-top: 80px; }
    .admin-container { flex: 1; padding: 20px; margin: 20px; background: #fff; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
    footer { margin-top: auto; }
    .mobile-menu-container { top: 80px; }
    .desktop-navv .navv a { text-decoration: none; color:white; font-size:1rem; padding:0.5rem 1rem; }
    .desktop-navv .navv a:hover, .desktop-navv .navv a.active { color:#ffcc00; border-radius:5px; }
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid #eee; }
    .page-title { font-size:24px; color:maroon; }

    .filter-form { display:flex; gap:10px; align-items:center; }
    .filter-form select,
    .filter-form input,
    .filter-form button {
      padding:8px 12px; border:1px solid #ddd; border-radius:4px; font-size:14px;
    }

    .data-table { width:100%; border-collapse:collapse; margin-top:20px; font-size:14px; }
    .data-table th, .data-table td { padding:12px 15px; text-align:left; border-bottom:1px solid #ddd; vertical-align:middle; }
    .data-table th { background:maroon; color:#fff; position:sticky; top:80px; }
    .data-table tr:hover { background:#f8f9fa; }

    .status { display:inline-block; padding:5px 10px; border-radius:20px; font-weight:bold; font-size:12px; text-align:center; min-width:100px; }
    .status-pending      { background:#fff3cd; color:#856404; }
    .status-under-review { background:#cce5ff; color:#004085; }
    .status-resolved     { background:#d4edda; color:#155724; }

    .action-btn { padding:5px 10px; border:none; border-radius:4px; cursor:pointer; font-size:12px; color:#fff; display:inline-flex; align-items:center; gap:3px; }
    .attachment-btn { background:#6f42c1; }
    .status-dropdown { padding:5px; border-radius:4px; border:1px solid #ddd; min-width:120px; font-size:12px; }

    @media (max-width: 768px) {
      .data-table { display:block; overflow-x:auto; }
      .filter-form { flex-direction:column; align-items:stretch; }
    }
  </style>
</head>
<body>
  <!-- IT Personnel Header -->
  <header class="header">
    <div id="logo">
      <img src="images/logo.jpg" alt="CyberSafe USeP Logo">
      <img src="images/sdmdlogo.png" alt="sdmdlogo">
      <h1>IT Personnel Dashboard</h1>
    </div>
    <div class="desktop-navv">
      <nav class="navv">
        <a href="itpASSIGNEDREP.php" class="active">Assigned Reports</a>
        <a href="itpREPORTLOG.php">Report Log</a>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
      </nav>
    </div>
  </header>

  <!-- Main Content -->
  <div class="admin-container">
    <div class="page-header">
      <h2 class="page-title">Assigned Reports</h2>

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
            <th>User</th>
            <th>Incident Type</th>
            <th>Description</th>
            <th>Attachments</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Update Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reports)): ?>
            <tr><td colspan="8" style="text-align:center;">No assigned reports.</td></tr>
          <?php else: foreach ($reports as $r):
            $caseId    = 'REP-'.str_pad($r['reportID'],4,'0',STR_PAD_LEFT);
            $submitted = date('Y-m-d H:i', strtotime($r['submissionTimestamp']));
            $cls       = 'status-'.str_replace(' ','-',strtolower($r['status']));
            $userCol   = $r['anonymousFlag'] ? 'Anonymous' : htmlspecialchars($r['userID'], ENT_QUOTES);
          ?>
          <tr>
            <td><?= $caseId ?></td>
            <td><?= $userCol ?></td>
            <td><?= htmlspecialchars($r['incidentType'], ENT_QUOTES) ?></td>
            <td><?= nl2br(htmlspecialchars($r['description'], ENT_QUOTES)) ?></td>
            <td>
              <?php if ($r['attachments']): ?>
                <a href="viewAttachment.php?reportID=<?= $r['reportID'] ?>"
                   target="_blank" class="action-btn attachment-btn">
                  <i class="fas fa-paperclip"></i> View
                </a>
              <?php else: ?>None<?php endif; ?>
            </td>
            <td><?= $submitted ?></td>
            <td><span class="status <?= $cls ?>"><?= ucfirst($r['status']) ?></span></td>
            <td>
              <select class="status-dropdown" data-report-id="<?= $r['reportID'] ?>">
                <option value="">Select Status</option>
                <option value="pending"       <?= $r['status']==='pending'       ? 'selected' : '' ?>>Pending</option>
                <option value="under review"  <?= $r['status']==='under review'  ? 'selected' : '' ?>>Under Review</option>
                <option value="resolved"      <?= $r['status']==='resolved'      ? 'selected' : '' ?>>Resolved</option>
              </select>
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
    function updateInput() {
      const sel = document.getElementById('filterType').value;
      const inp = document.getElementById('filterInput');
      const btn = document.getElementById('filterGo');
      if (!sel) {
        inp.disabled = true; btn.disabled = true;
        inp.type = 'text';
        inp.placeholder = 'Select filter type first';
        return;
      }
      inp.disabled = false; btn.disabled = false;
      if (sel === 'submissionDate') {
        inp.type = 'date'; inp.placeholder = '';
      } else {
        inp.type = 'text';
        inp.placeholder = sel === 'incidentType' ? 'e.g. malware' : 'e.g. pending';
      }
    }
    document.addEventListener('DOMContentLoaded', updateInput);

    document.querySelectorAll('.status-dropdown').forEach(select => {
      select.addEventListener('change', async function() {
        const reportId = this.dataset.reportId;
        const newStatus = this.value;
        if (!newStatus) return;
        if (!confirm(`Change status of report ${reportId} to ${newStatus}?`)) {
          window.location.reload();
          return;
        }
        this.disabled = true;
        try {
          const resp = await fetch('itpASSIGNEDREP.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update_status&reportID=${reportId}&newStatus=${encodeURIComponent(newStatus)}`
          });
          const json = await resp.json();
          if (json.success) {
            const row = this.closest('tr');
            const badge = row.querySelector('td:nth-child(7) .status');
            badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
            badge.className = 'status status-' + newStatus.replace(' ', '-');
          } else {
            alert('Failed to update status: ' + (json.error || 'Unknown error'));
            window.location.reload();
          }
        } catch (e) {
          alert('Error updating status.');
          window.location.reload();
        } finally {
          this.disabled = false;
        }
      });
    });
  </script>
</body>
</html>
