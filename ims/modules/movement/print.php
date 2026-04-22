
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/modules/movement/'); exit; }

$m = row(
    "SELECT m.*, s.asset_tag, s.item_name, s.make_model, s.serial_no, s.cat_id,
            ic.cat_name, i.inst_name, i.inst_code, i.address, i.city, i.phone
     FROM movements m
     JOIN stock s ON m.stock_id = s.id
     LEFT JOIN item_categories ic ON s.cat_id = ic.id
     LEFT JOIN institutes i ON m.inst_id = i.id
     WHERE m.id = ?", 'i', $id
);

if (!$m) { die('Movement record not found.'); }
$type = $_GET['type'] ?? 'full'; // full or chit
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Movement Slip — <?= esc($m['ref_no']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'IBM Plex Sans', sans-serif; background: #f0f2f5; color: #111; font-size: 14px; }

  .no-print {
    background: #1e2235; padding: 10px 24px; display: flex;
    gap: 10px; align-items: center; position: sticky; top: 0; z-index: 100;
  }
  .no-print a, .no-print button {
    padding: 7px 16px; border-radius: 6px; font-size: 13px;
    cursor: pointer; text-decoration: none; border: none; font-family: inherit;
  }
  .btn-back   { background: #2e3450; color: #9198b8; }
  .btn-print  { background: #4f8ef7; color: white; font-weight: 500; }
  .btn-toggle { background: #252a3d; color: #9198b8; }

  /* ── FULL PAGE ── */
  .page-full {
    width: 210mm; margin: 24px auto; background: white;
    padding: 18mm 16mm; box-shadow: 0 4px 24px rgba(0,0,0,0.12);
  }
  .inst-header { text-align:center; border-bottom:2px solid #1a1a2e; padding-bottom:12px; margin-bottom:16px; }
  .inst-name   { font-size:18px; font-weight:700; color:#1a1a2e; }
  .inst-addr   { font-size:11px; color:#666; margin-top:3px; }
  .doc-title   { font-size:14px; font-weight:600; color:#1a1a2e; margin-top:8px; text-transform:uppercase; letter-spacing:1px; }
  .doc-ref     { font-size:11px; color:#4f8ef7; font-family:'IBM Plex Mono',monospace; margin-top:3px; }

  .meta-row { display:flex; justify-content:space-between; font-size:12px; color:#666; margin-bottom:16px; }

  .section-title { font-size:11px; font-weight:600; text-transform:uppercase;
                   letter-spacing:1px; color:#888; margin:16px 0 8px;
                   border-bottom:1px solid #eee; padding-bottom:4px; }

  .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px 20px; margin-bottom:4px; }
  .detail-label { font-size:10px; text-transform:uppercase; letter-spacing:0.7px; color:#999; margin-bottom:2px; }
  .detail-value { font-size:13px; font-weight:500; color:#1a1a2e; }

  .arrow-row {
    display:flex; align-items:center; gap:16px; margin:16px 0;
    background:#f8fafc; border-radius:8px; padding:14px 16px;
  }
  .loc-box { flex:1; text-align:center; }
  .loc-label { font-size:10px; text-transform:uppercase; color:#999; letter-spacing:0.7px; margin-bottom:4px; }
  .loc-name  { font-size:15px; font-weight:700; color:#1a1a2e; }
  .arrow     { font-size:24px; color:#4f8ef7; }

  .status-badge {
    display:inline-block; padding:3px 12px; border-radius:20px;
    font-size:12px; font-weight:600; text-transform:uppercase;
  }
  .badge-issued   { background:#dbeafe; color:#1e40af; }
  .badge-returned { background:#dcfce7; color:#166534; }
  .badge-pending  { background:#fef9c3; color:#854d0e; }

  .sig-section { display:grid; grid-template-columns:1fr 1fr; gap:40px; margin-top:40px; padding-top:16px; border-top:1px solid #ddd; }
  .sig-box { text-align:center; }
  .sig-line { border-top:1px solid #999; padding-top:6px; margin-top:50px; font-size:12px; color:#555; }

  .page-footer { margin-top:20px; padding-top:10px; border-top:1px solid #eee; font-size:10px; color:#aaa; text-align:center; }

  /* ── CHIT ── */
  .page-chit {
    width:148mm; margin:24px auto; background:white;
    border:2px dashed #999; padding:10mm 10mm;
    box-shadow:0 4px 24px rgba(0,0,0,0.12);
  }
  .chit-header { text-align:center; border-bottom:1px solid #333; padding-bottom:8px; margin-bottom:10px; }
  .chit-inst   { font-size:14px; font-weight:700; }
  .chit-title  { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px; margin-top:4px; }
  .chit-ref    { font-size:10px; color:#4f8ef7; font-family:'IBM Plex Mono',monospace; }

  .chit-row { display:flex; justify-content:space-between; padding:4px 0; border-bottom:1px dotted #ddd; font-size:12px; }
  .chit-row:last-child { border-bottom:none; }
  .chit-label { color:#666; }
  .chit-value { font-weight:600; text-align:right; max-width:55%; }

  .chit-arrow { text-align:center; margin:10px 0; font-size:13px; }
  .chit-from  { font-weight:700; color:#1a1a2e; }
  .chit-to    { font-weight:700; color:#166534; }

  .chit-sig { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:16px; padding-top:10px; border-top:1px solid #ddd; }
  .chit-sig-box { text-align:center; }
  .chit-sig-line { border-top:1px solid #999; padding-top:4px; margin-top:30px; font-size:10px; color:#555; }

  @media print {
    .no-print { display:none !important; }
    body { background:white; }
    .page-full, .page-chit { margin:0; box-shadow:none; }
    .page-full { padding:12mm 12mm; width:100%; }
    .page-chit { border:1px dashed #999; }
  }
</style>
</head>
<body>

<!-- Top bar -->
<div class="no-print">
  <a href="<?= BASE_URL ?>/modules/movement/" class="btn-back">← Back</a>
  <button onclick="window.print()" class="btn-print">🖨️ Print</button>
  <a href="?id=<?= $id ?>&type=<?= $type === 'full' ? 'chit' : 'full' ?>" class="btn-toggle">
    Switch to <?= $type === 'full' ? '📄 Chit view' : '📋 Full view' ?>
  </a>
</div>

<?php if ($type === 'chit'): ?>
<!-- ══════════════════════════ CHIT VIEW ══════════════════════════ -->
<div class="page-chit">
  <div class="chit-header">
    <div class="chit-inst"><?= esc($m['inst_name']) ?></div>
    <div class="chit-title">Asset Movement Slip</div>
    <div class="chit-ref"><?= esc($m['ref_no']) ?> · <?= esc($m['move_date']) ?></div>
  </div>

  <div class="chit-row">
    <span class="chit-label">Asset Tag</span>
    <span class="chit-value mono"><?= esc($m['asset_tag']) ?></span>
  </div>
  <div class="chit-row">
    <span class="chit-label">Item</span>
    <span class="chit-value"><?= esc(mb_strimwidth($m['item_name'], 0, 35, '…')) ?></span>
  </div>
  <div class="chit-row">
    <span class="chit-label">Type</span>
    <span class="chit-value"><?= esc($m['move_type']) ?></span>
  </div>
  <div class="chit-row">
    <span class="chit-label">Issued to</span>
    <span class="chit-value"><?= esc($m['issued_to']) ?></span>
  </div>

  <div class="chit-arrow">
    <span class="chit-from">📍 <?= esc($m['from_loc']) ?></span>
    &nbsp;→&nbsp;
    <span class="chit-to">📍 <?= esc($m['to_loc']) ?></span>
  </div>

  <?php if ($m['expected_return']): ?>
  <div class="chit-row">
    <span class="chit-label">Return by</span>
    <span class="chit-value"><?= esc($m['expected_return']) ?></span>
  </div>
  <?php endif; ?>

  <div class="chit-sig">
    <div class="chit-sig-box">
      <div class="chit-sig-line">Issued by</div>
    </div>
    <div class="chit-sig-box">
      <div class="chit-sig-line">Received by</div>
    </div>
  </div>

  <div style="text-align:center;font-size:9px;color:#aaa;margin-top:10px">
    <?= date('d M Y, h:i A') ?>
  </div>
</div>

<?php else: ?>
<!-- ══════════════════════════ FULL VIEW ══════════════════════════ -->
<div class="page-full">

  <div class="inst-header">
    <div class="inst-name"><?= esc($m['inst_name']) ?></div>
    <?php if ($m['address'] || $m['city']): ?>
    <div class="inst-addr"><?= esc(trim(($m['address'] ?? '') . ', ' . ($m['city'] ?? ''))) ?></div>
    <?php endif; ?>
    <div class="doc-title">Asset Movement Register — Movement Slip</div>
    <div class="doc-ref"><?= esc($m['ref_no']) ?></div>
  </div>

  <div class="meta-row">
    <span>Date: <strong><?= date('d F Y', strtotime($m['move_date'])) ?></strong></span>
    <span>Type: <strong><?= esc($m['move_type']) ?></strong></span>
    <?php
      $bc = ['Issued'=>'badge-issued','Returned'=>'badge-returned','Pending Return'=>'badge-pending'][$m['status']] ?? 'badge-issued';
    ?>
    <span>Status: <span class="status-badge <?= $bc ?>"><?= esc($m['status']) ?></span></span>
  </div>

  <!-- Asset Details -->
  <div class="section-title">Asset Details</div>
  <div class="detail-grid">
    <div>
      <div class="detail-label">Asset Tag</div>
      <div class="detail-value mono"><?= esc($m['asset_tag']) ?></div>
    </div>
    <div>
      <div class="detail-label">Item Name</div>
      <div class="detail-value"><?= esc($m['item_name']) ?></div>
    </div>
    <div>
      <div class="detail-label">Category</div>
      <div class="detail-value"><?= esc($m['cat_name'] ?? '—') ?></div>
    </div>
    <div>
      <div class="detail-label">Make / Model</div>
      <div class="detail-value"><?= esc($m['make_model'] ?: '—') ?></div>
    </div>
    <?php if ($m['serial_no']): ?>
    <div>
      <div class="detail-label">Serial No.</div>
      <div class="detail-value mono"><?= esc($m['serial_no']) ?></div>
    </div>
    <?php endif; ?>
    <div>
      <div class="detail-label">Quantity Moved</div>
      <div class="detail-value"><?= esc($m['qty_moved']) ?></div>
    </div>
  </div>

  <!-- Movement Details -->
  <div class="section-title">Movement Details</div>
  <div class="arrow-row">
    <div class="loc-box">
      <div class="loc-label">From Location</div>
      <div class="loc-name">📍 <?= esc($m['from_loc']) ?></div>
    </div>
    <div class="arrow">→</div>
    <div class="loc-box">
      <div class="loc-label">To Location</div>
      <div class="loc-name">📍 <?= esc($m['to_loc']) ?></div>
    </div>
  </div>

  <div class="detail-grid">
    <div>
      <div class="detail-label">Issued To</div>
      <div class="detail-value"><?= esc($m['issued_to']) ?></div>
    </div>
    <div>
      <div class="detail-label">Issued By</div>
      <div class="detail-value"><?= esc($m['issued_by'] ?: '—') ?></div>
    </div>
    <?php if ($m['expected_return']): ?>
    <div>
      <div class="detail-label">Expected Return</div>
      <div class="detail-value"><?= esc($m['expected_return']) ?></div>
    </div>
    <?php endif; ?>
    <?php if ($m['actual_return']): ?>
    <div>
      <div class="detail-label">Actual Return</div>
      <div class="detail-value"><?= esc($m['actual_return']) ?></div>
    </div>
    <?php endif; ?>
    <?php if ($m['remarks']): ?>
    <div style="grid-column:span 2">
      <div class="detail-label">Remarks</div>
      <div class="detail-value"><?= esc($m['remarks']) ?></div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Signatures -->
  <div class="sig-section">
    <div class="sig-box">
      <div class="sig-line">
        Issued by<br>
        <strong><?= esc($m['issued_by'] ?: 'Store Incharge') ?></strong>
      </div>
    </div>
    <div class="sig-box">
      <div class="sig-line">
        Received by<br>
        <strong><?= esc($m['issued_to']) ?></strong>
      </div>
    </div>
  </div>

  <div class="page-footer">
    Computer generated movement slip · <?= esc($m['inst_name']) ?> · <?= date('d F Y, h:i A') ?>
  </div>

</div>
<?php endif; ?>

</body>
</html>







