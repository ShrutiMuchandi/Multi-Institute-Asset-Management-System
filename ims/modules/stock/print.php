<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$iid  = currentInstId();
$sf   = instFilter('s');

// Get filters from URL
$statusFilter = $_GET['status'] ?? '';
$catFilter    = (int)($_GET['cat'] ?? 0);
$search       = trim($_GET['q'] ?? '');

// Build WHERE
$where = $sf;
if ($statusFilter) { $safe = db()->real_escape_string($statusFilter); $where .= " AND s.status = '$safe'"; }
if ($catFilter)    { $where .= " AND s.cat_id = $catFilter"; }
if ($search)       { $safe = db()->real_escape_string($search);
                     $where .= " AND (s.item_name LIKE '%$safe%' OR s.asset_tag LIKE '%$safe%' OR s.location LIKE '%$safe%' OR s.make_model LIKE '%$safe%')"; }

// Get items
$items = rows(
    "SELECT s.*, ic.cat_name, b.internal_ref AS bill_ref, i.inst_name, i.inst_code
     FROM stock s
     LEFT JOIN item_categories ic ON s.cat_id = ic.id
     LEFT JOIN bills b ON s.bill_id = b.id
     LEFT JOIN institutes i ON s.inst_id = i.id
     WHERE $where ORDER BY s.cat_id, s.asset_tag ASC"
);

// Get institute info
$inst = $iid ? row("SELECT * FROM institutes WHERE id=?", 'i', $iid) : null;
$instName = $inst['inst_name'] ?? 'All Institutes';
$instAddr = $inst ? trim(($inst['address'] ?? '') . ', ' . ($inst['city'] ?? '')) : '';

// Total value
$totalValue = array_sum(array_column($items, 'unit_cost'));

// Filter label for heading
$filterLabel = [];
if ($statusFilter) $filterLabel[] = 'Status: ' . $statusFilter;
if ($catFilter) {
    $cat = row("SELECT cat_name FROM item_categories WHERE id=?", 'i', $catFilter);
    if ($cat) $filterLabel[] = 'Category: ' . $cat['cat_name'];
}
if ($search) $filterLabel[] = 'Search: ' . $search;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Stock Register — <?= esc($instName) ?></title>
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

  .page {
    width: 297mm; min-height: 210mm; margin: 24px auto;
    background: white; padding: 14mm 12mm;
    box-shadow: 0 4px 24px rgba(0,0,0,0.12);
  }

  /* Header */
  .inst-header { text-align: center; border-bottom: 2px solid #1a1a2e; padding-bottom: 12px; margin-bottom: 14px; }
  .inst-name   { font-size: 18px; font-weight: 700; color: #1a1a2e; }
  .inst-addr   { font-size: 11px; color: #666; margin-top: 3px; }
  .doc-title   { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-top: 8px; text-transform: uppercase; letter-spacing: 1px; }

  /* Meta */
  .meta-row { display: flex; justify-content: space-between; font-size: 11px; color: #666; margin-bottom: 12px; }
  .filter-tag { background: #eef2ff; color: #3730a3; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin-left: 4px; }

  /* Table */
  table { width: 100%; border-collapse: collapse; font-size: 11px; }
  thead th {
    background: #1a1a2e; color: white; padding: 7px 8px;
    text-align: left; font-size: 10px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
  }
  tbody td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
  tbody tr:nth-child(even) td { background: #f9fafb; }
  tbody tr:hover td { background: #f0f4ff; }
  .mono { font-family: 'IBM Plex Mono', monospace; font-size: 10px; }
  .status-active    { color: #166534; background: #dcfce7; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 600; }
  .status-inuse     { color: #1e40af; background: #dbeafe; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 600; }
  .status-repair    { color: #854d0e; background: #fef9c3; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 600; }
  .status-condemned { color: #991b1b; background: #fee2e2; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 600; }

  /* Summary footer */
  .summary { margin-top: 14px; display: flex; justify-content: space-between; align-items: flex-start; }
  .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 16px; font-size: 12px; }
  .summary-box .label { color: #666; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
  .summary-box .value { font-size: 15px; font-weight: 700; color: #1a1a2e; margin-top: 2px; font-family: 'IBM Plex Mono', monospace; }
  .summary-box .value.green { color: #166534; }

  /* Signature */
  .sig-section { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 30px; margin-top: 30px; padding-top: 16px; border-top: 1px solid #ddd; }
  .sig-box { text-align: center; }
  .sig-line { border-top: 1px solid #999; padding-top: 6px; margin-top: 40px; font-size: 11px; color: #555; }

  /* Page footer */
  .page-footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; font-size: 10px; color: #aaa; text-align: center; }

  @media print {
    .no-print { display: none !important; }
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
  <a href="<?= BASE_URL ?>/modules/stock/" class="btn-back">← Back to Stock</a>
  <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
  <span class="count-badge"><?= count($items) ?> items</span>
</div>

<!-- A3 Landscape Page -->
<div class="page">

  <!-- Header -->
  <div class="inst-header">
    <div class="inst-name"><?= esc($instName) ?></div>
    <?php if ($instAddr): ?>
    <div class="inst-addr"><?= esc(rtrim($instAddr, ', ')) ?></div>
    <?php endif; ?>
    <div class="doc-title">Stock Register</div>
  </div>

  <!-- Meta row -->
  <div class="meta-row">
    <span>
      Date: <strong><?= date('d F Y') ?></strong>
      <?php foreach ($filterLabel as $fl): ?>
      <span class="filter-tag"><?= esc($fl) ?></span>
      <?php endforeach; ?>
      <?php if (!$filterLabel): ?>
      <span class="filter-tag">All Items</span>
      <?php endif; ?>
    </span>
    <span>Total Items: <strong><?= count($items) ?></strong></span>
  </div>

  <!-- Stock Table -->
  <?php if ($items): ?>
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Asset Tag</th>
        <th>Item Name</th>
        <th>Category</th>
        <th>Make / Model</th>
        <th>Serial No.</th>
        <th>Bill Ref</th>
        <th>Receipt Date</th>
        <th>Location</th>
        <th>Department</th>
        <th>Unit Cost (₹)</th>
        <th>Warranty</th>
        <th>Status</th>
        <?php if (isSuperAdmin()): ?><th>Institute</th><?php endif; ?>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $i => $s):
      $statusClass = [
        'Active'       => 'status-active',
        'In Use'       => 'status-inuse',
        'Under Repair' => 'status-repair',
        'Condemned'    => 'status-condemned',
      ][$s['status']] ?? '';
    ?>
      <tr>
        <td class="mono"><?= $i + 1 ?></td>
        <td class="mono"><strong><?= esc($s['asset_tag']) ?></strong></td>
        <td>
          <strong><?= esc($s['item_name']) ?></strong>
          <?php if ($s['remarks']): ?>
          <div style="font-size:10px;color:#888"><?= esc(mb_strimwidth($s['remarks'], 0, 40, '…')) ?></div>
          <?php endif; ?>
        </td>
        <td><?= esc($s['cat_name'] ?? '—') ?></td>
        <td><?= esc($s['make_model'] ?: '—') ?></td>
        <td class="mono"><?= esc($s['serial_no'] ?: '—') ?></td>
        <td class="mono"><?= esc($s['bill_ref'] ?: '—') ?></td>
        <td><?= esc($s['receipt_date']) ?></td>
        <td><?= esc($s['location'] ?: '—') ?></td>
        <td><?= esc($s['dept'] ?: '—') ?></td>
        <td class="mono"><strong><?= money($s['unit_cost']) ?></strong></td>
        <td><?= esc($s['warranty_end'] ?: '—') ?></td>
        <td><span class="<?= $statusClass ?>"><?= esc($s['status']) ?></span></td>
        <?php if (isSuperAdmin()): ?><td><?= esc($s['inst_name'] ?? '—') ?></td><?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Summary -->
  <div class="summary">
    <div style="display:flex;gap:12px">
      <div class="summary-box">
        <div class="label">Total Items</div>
        <div class="value"><?= count($items) ?></div>
      </div>
      <div class="summary-box">
        <div class="label">Total Value</div>
        <div class="value green"><?= money($totalValue) ?></div>
      </div>
      <?php
        $statusCounts = array_count_values(array_column($items, 'status'));
      ?>
      <?php foreach ($statusCounts as $st => $cnt): ?>
      <div class="summary-box">
        <div class="label"><?= esc($st) ?></div>
        <div class="value"><?= $cnt ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php else: ?>
  <div style="text-align:center;padding:40px;color:#999">No stock items found for the selected filters.</div>
  <?php endif; ?>

  <!-- Signature Section -->
  <div class="sig-section">
    <div class="sig-box">
      <div class="sig-line">Stock Incharge / Office Staff</div>
    </div>
    <div class="sig-box">
      <div class="sig-line">Principal</div>
    </div>
    <div class="sig-box">
      <div class="sig-line">Management / Trust</div>
    </div>
  </div>

  <!-- Page Footer -->
  <div class="page-footer">
    Computer generated stock register · <?= esc($instName) ?> · Printed on <?= date('d F Y, h:i A') ?>
  </div>

</div>
</body>
</html>