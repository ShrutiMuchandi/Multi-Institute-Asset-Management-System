<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
requireLogin();

$iid  = currentInstId();
$fyid = currentFyId();
$sf   = instFilter('b');
$sf2  = instFilter('s');
$sf3  = instFilter('p');

// Stats
$totalBills     = row("SELECT COUNT(*) c, COALESCE(SUM(total_amount),0) t FROM bills b WHERE $sf AND fy_id=?", 'i', $fyid);
$totalStock     = row("SELECT COUNT(*) c FROM stock s WHERE $sf2 AND status != 'Condemned'");
$pendingProps   = row("SELECT COUNT(*) c FROM proposals p WHERE $sf3 AND status='Pending'");
$totalMovements = row("SELECT COUNT(*) c FROM movements m WHERE " . instFilter('m') . " AND YEAR(move_date)=YEAR(CURDATE())");

// Recent bills
if ($iid) {
    $recentBills = rows(
        "SELECT b.*, ic.cat_name FROM bills b LEFT JOIN item_categories ic ON b.cat_id=ic.id
         WHERE b.inst_id=? ORDER BY b.id DESC LIMIT 6",
        'i', $iid
    );
} else {
    $recentBills = rows(
        "SELECT b.*, ic.cat_name FROM bills b LEFT JOIN item_categories ic ON b.cat_id=ic.id
         ORDER BY b.id DESC LIMIT 6"
    );
}

// Pending proposals
if ($iid) {
    $pendingList = rows(
        "SELECT p.*, i.inst_name FROM proposals p JOIN institutes i ON p.inst_id=i.id
         WHERE p.inst_id=? AND p.status='Pending' ORDER BY p.id DESC LIMIT 6",
        'i', $iid
    );
} else {
    $pendingList = rows(
        "SELECT p.*, i.inst_name FROM proposals p JOIN institutes i ON p.inst_id=i.id
         WHERE p.status='Pending' ORDER BY p.id DESC LIMIT 6"
    );
}

layout_head('Dashboard', 'dashboard');
?>

<div class="stat-grid">
  <div class="stat-card sc-blue">
    <div class="stat-lbl">Bills this FY</div>
    <div class="stat-val"><?= number_format($totalBills['c']) ?></div>
    <div class="stat-sub">Total: <?= money($totalBills['t']) ?></div>
  </div>
  <div class="stat-card sc-green">
    <div class="stat-lbl">Active stock items</div>
    <div class="stat-val"><?= number_format($totalStock['c']) ?></div>
    <div class="stat-sub">Across all categories</div>
  </div>
  <div class="stat-card sc-amber">
    <div class="stat-lbl">Pending proposals</div>
    <div class="stat-val"><?= $pendingProps['c'] ?></div>
    <div class="stat-sub">Awaiting chairman approval</div>
  </div>
  <div class="stat-card sc-purple">
    <div class="stat-lbl">Movements this year</div>
    <div class="stat-val"><?= $totalMovements['c'] ?></div>
    <div class="stat-sub">Issues / transfers / returns</div>
  </div>
</div>

<div class="two-col">
  <div class="card">
    <div class="sec-hdr">
      <div>
        <div class="sec-title">Recent bills</div>
        <div class="sec-sub">Last 6 entries</div>
      </div>
      <a href="<?= BASE_URL ?>/modules/bills/" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <?php if ($recentBills): ?>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>Ref no.</th><th>Vendor</th><th>Category</th><th>Amount</th></tr></thead>
        <tbody>
        <?php foreach ($recentBills as $b): ?>
        <tr>
          <td class="mono"><?= esc($b['internal_ref']) ?></td>
          <td><?= esc($b['vendor_name']) ?></td>
          <td><span class="badge bg-gray"><?= esc($b['cat_name'] ?? '—') ?></span></td>
          <td class="mono text-green"><?= money($b['total_amount']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty"><div class="empty-icon">🧾</div><div class="empty-title">No bills entered yet</div></div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="sec-hdr">
      <div>
        <div class="sec-title">Pending proposals</div>
        <div class="sec-sub">Awaiting chairman approval</div>
      </div>
      <a href="<?= BASE_URL ?>/modules/proposals/" class="btn btn-ghost btn-sm">View all</a>
    </div>
    <?php if ($pendingList): ?>
    <div class="tbl-wrap">
      <table>
        <thead><tr><th>Proposal no.</th><th>Item</th><th>Amount</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($pendingList as $p): ?>
        <tr>
          <td class="mono"><?= esc($p['proposal_no']) ?></td>
          <td><?= esc(mb_strimwidth($p['item_name'], 0, 40, '…')) ?></td>
          <td class="mono"><?= money($p['est_amount']) ?></td>
          <td><a href="<?= BASE_URL ?>/modules/proposals/?view=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty"><div class="empty-icon">✅</div><div class="empty-title">No pending proposals</div></div>
    <?php endif; ?>
  </div>
</div>

<?php layout_foot(); ?>
