<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$iid = currentInstId();
$sf  = instFilter('m');

// Filters
$statusFilter = $_GET['status'] ?? '';
$typeFilter   = $_GET['type'] ?? '';
$search       = trim($_GET['q'] ?? '');

// Build WHERE
$where = $sf;
if ($statusFilter) { $safe = db()->real_escape_string($statusFilter); $where .= " AND m.status = '$safe'"; }
if ($typeFilter)   { $safe = db()->real_escape_string($typeFilter);   $where .= " AND m.move_type = '$safe'"; }
if ($search)       { $safe = db()->real_escape_string($search);
                     $where .= " AND (s.item_name LIKE '%$safe%' OR s.asset_tag LIKE '%$safe%' OR m.issued_to LIKE '%$safe%' OR m.ref_no LIKE '%$safe%')"; }

$movements = rows(
    "SELECT m.*, s.asset_tag, s.item_name FROM movements m
     JOIN stock s ON m.stock_id = s.id
     WHERE $where ORDER BY m.id DESC"
);

$inst     = $iid ? row("SELECT * FROM institutes WHERE id=?", 'i', $iid) : null;
$instName = $inst['inst_name'] ?? 'All Institutes';
$instAddr = $inst ? trim(($inst['address'] ?? '') . ', ' . ($inst['city'] ?? '')) : '';

// Filter labels
$filterLabel = [];
if ($statusFilter) $filterLabel[] = 'Status: ' . $statusFilter;
if ($typeFilter)   $filterLabel[] = 'Type: ' . $typeFilter;
if ($search)       $filterLabel[] = 'Search: ' . $search;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Movement Register — <?= esc($instName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'IBM Plex Sans', sans-serif; background: #f0f2f5; color: #111; font-size: 13px; }

  .no-print {
    background: #1e2235; padding: 10px 24px; display: flex;
    gap: 10px; align-items: center; position: sticky; top: 0; z-index: 100;
  }
  .no-print a, .no-print button {
    padding: 7px 16px; border-radius: 6px; font-size: 13px;
    cursor: pointer; text-decoration: none; border: none; font-family: inherit;
  }
  .btn-back  { background: #2e3450; color: #9198b8; }
  .btn-print { background: #4f8ef7; color: white; font-weight: 500; }
  .count-badge { font-size: 12px; color: #9198b8; margin-left: 8px; }

  /* Filter bar */
  .filter-bar {
    background: #252a3d; padding: 10px 24px; display: flex;
    gap: 10px; align-items: center; flex-wrap: wrap;
  }
  .filter-bar select, .filter-bar input {
    padding: 6px 10px; border-radius: 6px; border: 1px solid #3a4060;
    background: #1e2235; color: #c8cde0; font-size: 12px; font-family: inherit;
  }
  .filter-bar button {
    padding: 6px 14px; border-radius: 6px; border: none;
    background: #4f8ef7; color: white; font-size: 12px; cursor: pointer; font-family: inherit;
  }

  .page {
    width: 297mm; min-height: 210mm; margin: 24px auto;
    background: white; padding: 14mm 12mm;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
  }

  .inst-header { text-align: center; border-bottom: 2px solid #1a1a2e; padding-bottom: 12px; margin-bottom: 14px; }
  .inst-name   { font-size: 18px; font-weight: 700; color: #1a1a2e; }
  .inst-addr   { font-size: 11px; color: #666; margin-top: 3px; }
  .doc-title   { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-top: 8px; text-transform: uppercase; letter-spacing: 1px; }

  .meta-row { display: flex; justify-content: space-between; font-size: 11px; color: #666; margin-bottom: 12px; }
  .filter-tag { background: #eef2ff; color: #3730a3; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-left: 4px; }

  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  thead th {
    background: #1a1a2e; color: white; padding: 7px 8px;
    text-align: left; font-size: 10px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
  }
  tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
  tbody tr:nth-child(even) td { background: #f9fafb; }
  .mono { font-family: 'IBM Plex Mono', monospace; font-size: 10px; }

  .badge { padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 600; }
  .badge-issued   { background: #dbeafe; color: #1e40af; }
  .badge-returned { background: #dcfce7; color: #166534; }
  .badge-pending  { background: #fef9c3; color: #854d0e; }

  .summary { margin-top: 14px; display: flex; gap: 12px; }
  .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 16px; font-size: 12px; }
  .summary-box .label { color: #666; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
  .summary-box .value { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-top: 2px; }

  .sig-section { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; margin-top: 30px; padding-top: 16px; border-top: 1px solid #ddd; }
  .sig-box { text-align: center; }
  .sig-line { border-top: 1px solid #999; padding-top: 6px; margin-top: 40px; font-size: 11px; color: #555; }

  .page-footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; font-size: 10px; color: #aaa; text-align: center; }

  @media print {
    .no-print, .filter-bar { display: none !important; }
    body { background: white; }
    .page { margin: 0; box-shadow: none; padding: 10mm 10mm; width: 100%; }
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; }
    thead { display: table-header-group; }
  }
</style>
</head>
<body>

<!-- Top bar -->
<div class="no-print">
  <a href="<?= BASE_URL ?>/modules/movement/" class="btn-back">← Back</a>
  <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
  <span class="count-badge"><?= count($movements) ?> records</span>
</div>

<!-- Filter Bar -->
<div class="filter-bar no-print">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
    <select name="status">
      <option value="">All Status</option>
      <option value="Issued"         <?= $statusFilter==='Issued'?'selected':'' ?>>Issued</option>
      <option value="Returned"       <?= $statusFilter==='Returned'?'selected':'' ?>>Returned</option>
      <option value="Pending Return" <?= $statusFilter==='Pending Return'?'selected':'' ?>>Pending Return</option>
    </select>
    <select name="type">
      <option value="">All Types</option>
      <option value="Issue"              <?= $typeFilter==='Issue'?'selected':'' ?>>Issue</option>
      <option value="Transfer"           <?= $typeFilter==='Transfer'?'selected':'' ?>>Transfer</option>
      <option value="Return"             <?= $typeFilter==='Return'?'selected':'' ?>>Return</option>
      <option value="Send for Repair"    <?= $typeFilter==='Send for Repair'?'selected':'' ?>>Send for Repair</option>
      <option value="Return from Repair" <?= $typeFilter==='Return from Repair'?'selected':'' ?>>Return from Repair</option>
      <option value="Temporary Issue"    <?= $typeFilter==='Temporary Issue'?'selected':'' ?>>Temporary Issue</option>
    </select>
    <input name="q" value="<?= esc($search) ?>" placeholder="Search asset / person…">
    <button type="submit">Apply Filter</button>
    <?php if ($statusFilter || $typeFilter || $search): ?>
    <a href="?" style="color:#9198b8;font-size:12px;text-decoration:none">✕ Clear</a>
    <?php endif; ?>
  </form>
</div>

<!-- Print Page -->
<div class="page">

  <div class="inst-header">
    <div class="inst-name"><?= esc($instName) ?></div>
    <?php if ($instAddr): ?>
    <div class="inst-addr"><?= esc(rtrim($instAddr, ', ')) ?></div>
    <?php endif; ?>
    <div class="doc-title">Movement Register</div>
  </div>

  <div class="meta-row">
    <span>
      Date: <strong><?= date('d F Y') ?></strong>
      <?php foreach ($filterLabel as $fl): ?>
      <span class="filter-tag"><?= esc($fl) ?></span>
      <?php endforeach; ?>
      <?php if (!$filterLabel): ?>
      <span class="filter-tag">All Records</span>
      <?php endif; ?>
    </span>
    <span>Total: <strong><?= count($movements) ?></strong></span>
  </div>

  <?php if ($movements): ?>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Ref No.</th>
        <th>Date</th>
        <th>Type</th>
        <th>Asset Tag</th>
        <th>Item Name</th>
        <th>From</th>
        <th>To</th>
        <th>Issued To</th>
        <th>Issued By</th>
        <th>Exp. Return</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($movements as $i => $m):
      $bc = ['Issued'=>'badge-issued','Returned'=>'badge-returned','Pending Return'=>'badge-pending'][$m['status']] ?? '';
    ?>
      <tr>
        <td class="mono"><?= $i + 1 ?></td>
        <td class="mono"><strong><?= esc($m['ref_no']) ?></strong></td>
        <td><?= esc($m['move_date']) ?></td>
        <td><?= esc($m['move_type']) ?></td>
        <td class="mono"><strong><?= esc($m['asset_tag']) ?></strong></td>
        <td><?= esc(mb_strimwidth($m['item_name'], 0, 35, '…')) ?></td>
        <td><?= esc($m['from_loc']) ?></td>
        <td><?= esc($m['to_loc']) ?></td>
        <td><?= esc($m['issued_to']) ?></td>
        <td><?= esc($m['issued_by'] ?: '—') ?></td>
        <td><?= esc($m['expected_return'] ?: '—') ?></td>
        <td><span class="badge <?= $bc ?>"><?= esc($m['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Summary -->
  <div class="summary">
    <div class="summary-box">
      <div class="label">Total Records</div>
      <div class="value"><?= count($movements) ?></div>
    </div>
    <?php $statusCounts = array_count_values(array_column($movements, 'status')); ?>
    <?php foreach ($statusCounts as $st => $cnt): ?>
    <div class="summary-box">
      <div class="label"><?= esc($st) ?></div>
      <div class="value"><?= $cnt ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php else: ?>
  <div style="text-align:center;padding:40px;color:#999">No movement records found.</div>
  <?php endif; ?>

  <div class="sig-section">
    <div class="sig-box"><div class="sig-line">Stock Incharge / Office Staff</div></div>
    <div class="sig-box"><div class="sig-line">Principal</div></div>
    <div class="sig-box"><div class="sig-line">Management / Trust</div></div>
  </div>

  <div class="page-footer">
    Computer generated movement register · <?= esc($instName) ?> · Printed on <?= date('d F Y, h:i A') ?>
  </div>

</div>
</body>
</html>