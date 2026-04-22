<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . BASE_URL . '/modules/proposals/'); exit; }

// Get proposal with all details
$p = row(
    "SELECT p.*, ic.cat_name, i.inst_name, i.inst_code, i.address, i.city, i.phone, i.email, i.principal
     FROM proposals p
     LEFT JOIN item_categories ic ON p.cat_id = ic.id
     LEFT JOIN institutes i ON p.inst_id = i.id
     WHERE p.id = ?", 'i', $id
);

if (!$p) { die('Proposal not found.'); }

// Get superadmin signature
$admin = row("SELECT full_name, signature FROM users WHERE role='superadmin' LIMIT 1");
$sigPath = ($admin['signature'] ?? '') 
    ? BASE_URL . '/uploads/signatures/' . $admin['signature']
    : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Proposal Approval Letter — <?= esc($p['proposal_no']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'IBM Plex Sans', sans-serif; background: #f0f2f5; color: #111; font-size: 14px; }

  .no-print {
    background: #1e2235; padding: 12px 24px; display: flex;
    gap: 10px; align-items: center; position: sticky; top: 0; z-index: 100;
  }
  .no-print a, .no-print button {
    padding: 7px 16px; border-radius: 6px; font-size: 13px;
    cursor: pointer; text-decoration: none; border: none; font-family: inherit;
  }
  .btn-back  { background: #2e3450; color: #9198b8; }
  .btn-print { background: #4f8ef7; color: white; font-weight: 500; }
  .btn-back:hover { background: #3d4466; }
  .btn-print:hover { background: #3a6fd8; }

  .page {
    width: 210mm; min-height: 297mm; margin: 24px auto;
    background: white; padding: 20mm 18mm; box-shadow: 0 4px 24px rgba(0,0,0,0.12);
  }

  /* Header */
  .inst-header { text-align: center; border-bottom: 2px solid #1a1a2e; padding-bottom: 14px; margin-bottom: 18px; }
  .inst-name   { font-size: 20px; font-weight: 700; color: #1a1a2e; letter-spacing: 0.3px; }
  .inst-addr   { font-size: 12px; color: #555; margin-top: 4px; }
  .inst-contact{ font-size: 11px; color: #777; margin-top: 3px; }
  .doc-title   { font-size: 15px; font-weight: 600; color: #1a1a2e; margin-top: 10px; letter-spacing: 1px; text-transform: uppercase; }
  .doc-ref     { font-size: 11px; color: #4f8ef7; font-family: 'IBM Plex Mono', monospace; margin-top: 3px; }

  /* Meta row */
  .meta-row { display: flex; justify-content: space-between; margin-bottom: 18px; font-size: 12px; color: #555; }

  /* Section title */
  .section-title { font-size: 11px; font-weight: 600; text-transform: uppercase;
                   letter-spacing: 1px; color: #888; margin: 16px 0 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; }

  /* Details grid */
  .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 20px; margin-bottom: 4px; }
  .detail-item {}
  .detail-label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.7px; color: #999; margin-bottom: 2px; }
  .detail-value { font-size: 13px; font-weight: 500; color: #1a1a2e; }
  .detail-value.amount { font-size: 16px; font-weight: 700; color: #1a7a4a; font-family: 'IBM Plex Mono', monospace; }

  /* Justification box */
  .justification { background: #f8f9fc; border-left: 3px solid #4f8ef7;
                   padding: 10px 14px; border-radius: 0 6px 6px 0; font-size: 13px; color: #333; line-height: 1.6; }

  /* Approval box */
  .approval-box { background: #f0fdf6; border: 1px solid #bbf0d4;
                  border-radius: 8px; padding: 14px 16px; margin-top: 16px; }
  .approval-box.rejected { background: #fff5f5; border-color: #fbbcbc; }
  .approval-status { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
  .approval-status.approved { color: #1a7a4a; }
  .approval-status.rejected { color: #c0392b; }
  .approval-meta { font-size: 12px; color: #555; }

  /* Signature section */
  .sig-section { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
  .sig-box { text-align: center; }
  .sig-img { max-height: 60px; max-width: 160px; margin-bottom: 6px; object-fit: contain; }
  .sig-line { border-top: 1px solid #999; padding-top: 6px; margin-top: 0px; font-size: 12px; color: #555; }
  .sig-name { font-size: 13px; font-weight: 600; color: #1a1a2e; margin-top: 3px; }
  .sig-role { font-size: 11px; color: #888; }

  /* Status badge */
  .status-badge {
    display: inline-block; padding: 3px 12px; border-radius: 20px;
    font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
  }
  .badge-approved  { background: #dcfce7; color: #166534; }
  .badge-pending   { background: #fef9c3; color: #854d0e; }
  .badge-rejected  { background: #fee2e2; color: #991b1b; }
  .badge-purchased { background: #dbeafe; color: #1e40af; }

  /* Footer */
  .page-footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #eee;
                 font-size: 10px; color: #aaa; text-align: center; }

  @media print {
    .no-print { display: none !important; }
    body { background: white; }
    .page { margin: 0; box-shadow: none; padding: 15mm 15mm; }
  }
</style>
</head>
<body>

<!-- Top bar (hidden on print) -->
<div class="no-print">
  <a href="<?= BASE_URL ?>/modules/proposals/?view=<?= $p['id'] ?>" class="btn-back">← Back</a>
  <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
</div>

<!-- A4 Page -->
<div class="page">

  <!-- Institute Header -->
  <div class="inst-header">
    <div class="inst-name"><?= esc($p['inst_name']) ?></div>
    <?php if ($p['address'] || $p['city']): ?>
    <div class="inst-addr"><?= esc(trim(($p['address'] ? $p['address'] . ', ' : '') . ($p['city'] ?? ''))) ?></div>
    <?php endif; ?>
    <?php if ($p['phone'] || $p['email']): ?>
    <div class="inst-contact">
      <?= $p['phone'] ? 'Ph: ' . esc($p['phone']) : '' ?>
      <?= ($p['phone'] && $p['email']) ? ' | ' : '' ?>
      <?= $p['email'] ? 'Email: ' . esc($p['email']) : '' ?>
    </div>
    <?php endif; ?>
    <div class="doc-title">Purchase Proposal — Approval Letter</div>
    <div class="doc-ref"><?= esc($p['proposal_no']) ?></div>
  </div>

  <!-- Meta row -->
  <div class="meta-row">
    <span>Date: <strong><?= date('d F Y', strtotime($p['proposal_date'])) ?></strong></span>
    <span>Status:
      <?php
        $badgeClass = ['Approved'=>'badge-approved','Pending'=>'badge-pending','Rejected'=>'badge-rejected','Purchased'=>'badge-purchased'][$p['status']] ?? 'badge-pending';
      ?>
      <span class="status-badge <?= $badgeClass ?>"><?= esc($p['status']) ?></span>
    </span>
    <span>FY: <strong><?= esc($_SESSION['fy_label'] ?? date('Y')) ?></strong></span>
  </div>

  <!-- Proposal Details -->
  <div class="section-title">Proposal Details</div>
  <div class="detail-grid">
    <div class="detail-item">
      <div class="detail-label">Item / Purpose</div>
      <div class="detail-value"><?= esc($p['item_name']) ?></div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Category</div>
      <div class="detail-value"><?= esc($p['cat_name'] ?? '—') ?></div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Quantity</div>
      <div class="detail-value"><?= esc($p['quantity']) ?> <?= esc($p['unit']) ?></div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Estimated Amount</div>
      <div class="detail-value amount"><?= money($p['est_amount']) ?></div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Department</div>
      <div class="detail-value"><?= esc($p['department'] ?: '—') ?></div>
    </div>
    <div class="detail-item">
      <div class="detail-label">Raised By</div>
      <div class="detail-value"><?= esc($p['raised_by'] ?: '—') ?> <?= $p['designation'] ? '(' . esc($p['designation']) . ')' : '' ?></div>
    </div>
  </div>

  <?php if ($p['justification']): ?>
  <div class="section-title">Justification</div>
  <div class="justification"><?= nl2br(esc($p['justification'])) ?></div>
  <?php endif; ?>

  <!-- Approval Details -->
  <?php if ($p['status'] === 'Approved' || $p['status'] === 'Rejected' || $p['status'] === 'Purchased'): ?>
  <div class="section-title">Approval Details</div>
  <div class="approval-box <?= $p['status'] === 'Rejected' ? 'rejected' : '' ?>">
    <div class="approval-status <?= $p['status'] === 'Rejected' ? 'rejected' : 'approved' ?>">
      <?= $p['status'] === 'Rejected' ? '✗ Rejected' : '✓ Approved' ?>
    </div>
    <div class="approval-meta">
      <strong>By:</strong> <?= esc($p['approved_by'] ?? '—') ?>
      &nbsp;|&nbsp;
      <strong>Date:</strong> <?= $p['approved_date'] ? date('d F Y', strtotime($p['approved_date'])) : '—' ?>
      <?php if ($p['approval_note']): ?>
      <br><strong>Remarks:</strong> <?= esc($p['approval_note']) ?>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Signature Section -->
  <div class="sig-section">
    <div class="sig-box">
      <div style="height:70px;display:flex;align-items:flex-end;justify-content:center;margin-bottom:6px"></div>
      <div class="sig-line">
        <div class="sig-name"><?= esc($p['raised_by'] ?: 'Principal') ?></div>
        <div class="sig-role"><?= esc($p['designation'] ?: 'Principal') ?></div>
      </div>
    </div>
    <div class="sig-box">
      <div style="height:70px;display:flex;align-items:flex-end;justify-content:center;margin-bottom:6px">
        <?php if ($sigPath && ($p['status'] === 'Approved' || $p['status'] === 'Purchased')): ?>
        <img src="<?= $sigPath ?>" alt="Signature" class="sig-img" style="margin-bottom:0">
        <?php endif; ?>
      </div>
      <div class="sig-line">
        <div class="sig-name"><?= esc($admin['full_name'] ?? 'Authorised Signatory') ?></div>
        <div class="sig-role">Super Admin / Management</div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <div class="page-footer">
    This is a computer generated document. · Generated on <?= date('d F Y, h:i A') ?> · <?= esc($p['inst_name']) ?>
  </div>

</div>
</body>
</html>