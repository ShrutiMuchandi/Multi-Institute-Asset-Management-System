<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';
requireLogin();

$iid  = currentInstId();
$role = $_SESSION['role'] ?? '';

// ── POST HANDLERS ──────────────────────────────────────────

// Office staff: add quotation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_quote') {
    if ($role === 'principal') { header("Location: ?err=" . urlencode('Access denied.')); exit; }
    $propId  = (int)($_POST['proposal_id'] ?? 0);
    $vendor  = trim($_POST['vendor_name'] ?? '');
    $amount  = (float)str_replace(',', '', $_POST['quoted_amount'] ?? 0);
    if (!$propId || !$vendor || !$amount) {
        header("Location: ?proposal_id=$propId&err=" . urlencode('Vendor name and amount are required.')); exit;
    }
    $instId = $iid;
    if (!$instId) {
        $prop = row("SELECT inst_id FROM proposals WHERE id=?", 'i', $propId);
        $instId = $prop['inst_id'] ?? 0;
    }
    insert(
        "INSERT INTO quotations (inst_id,proposal_id,vendor_name,vendor_contact,vendor_address,vendor_gstin,quoted_amount,delivery_days,validity_days,remarks,added_by)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
        'iissssdiisi',
        $instId, $propId,
        $vendor,
        trim($_POST['vendor_contact'] ?? ''),
        trim($_POST['vendor_address'] ?? ''),
        trim($_POST['vendor_gstin'] ?? ''),
        $amount,
        (int)($_POST['delivery_days'] ?? 0),
        (int)($_POST['validity_days'] ?? 30),
        trim($_POST['remarks'] ?? ''),
        $_SESSION['user_id']
    );
    // Update proposal quotation_status
    query("UPDATE proposals SET quotation_status='Quotes Collected' WHERE id=?", 'i', $propId);
    audit('ADD_QUOTATION', 'quotations', $propId, "Vendor: $vendor, Amount: $amount");
    header("Location: ?proposal_id=$propId&msg=" . urlencode("Quotation from $vendor added.")); exit;
}

// Principal: select vendor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'select_vendor') {
    if (!in_array($role, ['principal', 'superadmin'])) {
        header("Location: ?err=" . urlencode('Access denied.')); exit;
    }
    $quoteId = (int)($_POST['quote_id'] ?? 0);
    $propId  = (int)($_POST['proposal_id'] ?? 0);
    // Unselect all quotes for this proposal
    query("UPDATE quotations SET is_selected=0, selected_by=NULL, selected_at=NULL WHERE proposal_id=?", 'i', $propId);
    // Select the chosen one
    query("UPDATE quotations SET is_selected=1, selected_by=?, selected_at=NOW() WHERE id=?",
        'si', $_SESSION['full_name'] ?? 'Principal', $quoteId);
    // Update proposal status
    query("UPDATE proposals SET quotation_status='Vendor Selected' WHERE id=?", 'i', $propId);
    audit('SELECT_VENDOR', 'quotations', $quoteId, "Selected for proposal $propId");
    header("Location: ?proposal_id=$propId&msg=" . urlencode("Vendor selected! Office staff can now raise Purchase Order.")); exit;
}

// Office staff: raise PO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'raise_po') {
    if ($role === 'principal') { header("Location: ?err=" . urlencode('Access denied.')); exit; }
    $propId = (int)($_POST['proposal_id'] ?? 0);
    query("UPDATE proposals SET quotation_status='PO Raised' WHERE id=?", 'i', $propId);
    audit('RAISE_PO', 'quotations', $propId, "PO raised for proposal $propId");
    header("Location: ?proposal_id=$propId&po=1&msg=" . urlencode("Purchase Order raised successfully!")); exit;
}

// ── LOAD DATA ──────────────────────────────────────────────
$propId    = (int)($_GET['proposal_id'] ?? 0);
$sf        = instFilter('p');

// All approved proposals with quotation status
$proposals = rows(
    "SELECT p.*, i.inst_name FROM proposals p
     LEFT JOIN institutes i ON p.inst_id = i.id
     WHERE $sf AND p.status = 'Approved'
     ORDER BY p.id DESC"
);

$proposal  = $propId ? row(
    "SELECT p.*, i.inst_name, i.inst_code FROM proposals p
     LEFT JOIN institutes i ON p.inst_id = i.id
     WHERE p.id=?", 'i', $propId
) : null;

$quotes    = $propId ? rows(
    "SELECT q.*, u.full_name AS added_by_name FROM quotations q
     LEFT JOIN users u ON q.added_by = u.id
     WHERE q.proposal_id=? ORDER BY q.quoted_amount ASC", 'i', $propId
) : [];

$selectedQuote = $propId ? row(
    "SELECT * FROM quotations WHERE proposal_id=? AND is_selected=1", 'i', $propId
) : null;

$showPO = isset($_GET['po']) && $selectedQuote;

layout_head('Quotations', 'quotations');
?>

<?php
$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';
if ($msg): ?>
<div style="background:var(--gns);border:1px solid var(--gn);color:var(--gn);padding:10px 16px;border-radius:var(--r);margin-bottom:16px">
  ✅ <?= esc($msg) ?>
</div>
<?php endif; ?>
<?php if ($err): ?>
<div style="background:var(--rds);border:1px solid var(--rd);color:var(--rd);padding:10px 16px;border-radius:var(--r);margin-bottom:16px">
  ❌ <?= esc($err) ?>
</div>
<?php endif; ?>

<?php if ($showPO && $proposal && $selectedQuote): ?>
<!-- ══════════════════════════════════════════════════════════ -->
<!-- PURCHASE ORDER PRINT VIEW                                  -->
<!-- ══════════════════════════════════════════════════════════ -->
<div style="max-width:780px">
  <div style="display:flex;gap:8px;margin-bottom:16px">
    <a href="?proposal_id=<?= $propId ?>" class="btn btn-ghost btn-sm">← Back</a>
    <button onclick="window.print()" class="btn btn-primary btn-sm">🖨 Print Purchase Order</button>
  </div>

  <div id="po-print" class="card" style="padding:32px">
    <div style="text-align:center;border-bottom:2px solid var(--bd);padding-bottom:16px;margin-bottom:20px">
      <div style="font-size:20px;font-weight:700"><?= esc($proposal['inst_name']) ?></div>
      <div style="font-size:13px;color:var(--tx2);margin-top:4px">PURCHASE ORDER</div>
      <div style="font-family:var(--mono);font-size:12px;color:var(--ac);margin-top:4px">
        PO-<?= esc($proposal['inst_code']) ?>-<?= date('Y') ?>-<?= str_pad($proposal['id'], 3, '0', STR_PAD_LEFT) ?>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
      <div>
        <div style="font-size:11px;color:var(--tx3);margin-bottom:4px">TO (VENDOR)</div>
        <div style="font-weight:600"><?= esc($selectedQuote['vendor_name']) ?></div>
        <?php if ($selectedQuote['vendor_address']): ?>
        <div style="color:var(--tx2);font-size:12px;margin-top:3px"><?= nl2br(esc($selectedQuote['vendor_address'])) ?></div>
        <?php endif; ?>
        <?php if ($selectedQuote['vendor_gstin']): ?>
        <div style="font-size:12px;color:var(--tx3);margin-top:3px">GSTIN: <?= esc($selectedQuote['vendor_gstin']) ?></div>
        <?php endif; ?>
        <?php if ($selectedQuote['vendor_contact']): ?>
        <div style="font-size:12px;color:var(--tx3)">Contact: <?= esc($selectedQuote['vendor_contact']) ?></div>
        <?php endif; ?>
      </div>
      <div>
        <div style="font-size:11px;color:var(--tx3);margin-bottom:4px">PO DETAILS</div>
        <table style="font-size:12px;border:none">
          <tr><td style="color:var(--tx3);padding:2px 12px 2px 0;border:none">PO Date</td><td style="border:none"><?= date('d M Y') ?></td></tr>
          <tr><td style="color:var(--tx3);padding:2px 12px 2px 0;border:none">Proposal Ref</td><td style="border:none;font-family:var(--mono)"><?= esc($proposal['proposal_no']) ?></td></tr>
          <tr><td style="color:var(--tx3);padding:2px 12px 2px 0;border:none">Delivery in</td><td style="border:none"><?= $selectedQuote['delivery_days'] ?> days</td></tr>
          <tr><td style="color:var(--tx3);padding:2px 12px 2px 0;border:none">Selected by</td><td style="border:none"><?= esc($selectedQuote['selected_by']) ?></td></tr>
        </table>
      </div>
    </div>

    <div style="margin-bottom:20px">
      <div style="font-size:11px;color:var(--tx3);margin-bottom:8px">ORDER DETAILS</div>
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Item / Description</th><th>Qty</th><th>Unit</th><th>Unit Rate (₹)</th><th>Total (₹)</th></tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td><?= esc($proposal['item_name']) ?><?php if ($proposal['justification']): ?><div style="font-size:11px;color:var(--tx3)"><?= esc(mb_strimwidth($proposal['justification'],0,80,'…')) ?></div><?php endif; ?></td>
              <td class="mono"><?= esc($proposal['quantity']) ?></td>
              <td><?= esc($proposal['unit']) ?></td>
              <td class="mono"><?= money($selectedQuote['quoted_amount'] / max(1,$proposal['quantity'])) ?></td>
              <td class="mono fw500 text-green"><?= money($selectedQuote['quoted_amount']) ?></td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="5" style="text-align:right;font-weight:600;padding:10px 13px;border-top:1px solid var(--bd)">TOTAL AMOUNT</td>
              <td class="mono fw500" style="color:var(--gn);font-size:15px;border-top:1px solid var(--bd)"><?= money($selectedQuote['quoted_amount']) ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <?php if ($selectedQuote['remarks']): ?>
    <div style="background:var(--bg3);border-radius:var(--r);padding:12px;margin-bottom:20px">
      <div style="font-size:11px;color:var(--tx3);margin-bottom:4px">REMARKS / TERMS</div>
      <div style="font-size:13px"><?= nl2br(esc($selectedQuote['remarks'])) ?></div>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:40px;margin-top:40px;padding-top:20px;border-top:1px solid var(--bd)">
      <div style="text-align:center">
        <div style="border-top:1px solid var(--tx3);padding-top:8px;margin-top:40px;font-size:12px;color:var(--tx3)">
          Office Staff Signature
        </div>
      </div>
      <div style="text-align:center">
        <div style="border-top:1px solid var(--tx3);padding-top:8px;margin-top:40px;font-size:12px;color:var(--tx3)">
          Principal Signature
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@media print {
  .sidebar, .topbar, .btn, #po-print > div:first-child { display: none !important; }
  .main-wrap { margin: 0; }
  #po-print { border: none; padding: 0; }
  body { background: white; color: black; }
  :root { --bg2: white; --bg3: #f5f5f5; --bd: #ddd; --tx: black; --tx2: #444; --tx3: #666; --gn: #1a7a4a; --ac: #1a4a9a; }
}
</style>

<?php elseif ($proposal): ?>
<!-- ══════════════════════════════════════════════════════════ -->
<!-- QUOTATION DETAIL VIEW FOR A PROPOSAL                       -->
<!-- ══════════════════════════════════════════════════════════ -->
<div style="max-width:860px">
  <div style="margin-bottom:14px">
    <a href="<?= BASE_URL ?>/modules/quotations/" class="btn btn-ghost btn-sm">← Back to list</a>
  </div>

  <!-- Proposal summary card -->
  <div class="card" style="margin-bottom:16px">
    <div class="sec-hdr">
      <div>
        <div class="sec-title"><?= esc($proposal['proposal_no']) ?></div>
        <div class="sec-sub"><?= esc($proposal['item_name']) ?> · <?= esc($proposal['inst_name']) ?></div>
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <?php
          $qstatus = $proposal['quotation_status'] ?? 'Not Started';
          $qbadge  = ['Not Started'=>'bg-gray','Quotes Collected'=>'bg-amber','Vendor Selected'=>'bg-blue','PO Raised'=>'bg-green'][$qstatus] ?? 'bg-gray';
        ?>
        <span class="badge <?= $qbadge ?>"><?= esc($qstatus) ?></span>
        <span class="badge bg-green">Approved</span>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-top:8px">
      <div><div style="font-size:11px;color:var(--tx3)">QUANTITY</div><div class="fw500"><?= esc($proposal['quantity']) ?> <?= esc($proposal['unit']) ?></div></div>
      <div><div style="font-size:11px;color:var(--tx3)">EST. AMOUNT</div><div class="fw500 mono"><?= money($proposal['est_amount']) ?></div></div>
      <div><div style="font-size:11px;color:var(--tx3)">DEPARTMENT</div><div class="fw500"><?= esc($proposal['department'] ?: '—') ?></div></div>
      <div><div style="font-size:11px;color:var(--tx3)">RAISED BY</div><div class="fw500"><?= esc($proposal['raised_by'] ?: '—') ?></div></div>
    </div>
  </div>

  <!-- Quotations table -->
  <div class="card" style="margin-bottom:16px">
    <div class="sec-hdr">
      <div>
        <div class="sec-title">Vendor Quotations</div>
        <div class="sec-sub"><?= count($quotes) ?> quote(s) collected · sorted by lowest price</div>
      </div>
	  <?php if ($role === 'office_staff' && ($proposal['quotation_status'] ?? '') !== 'PO Raised'): ?>
      <?php //if (!in_array($role, ['principal']) && ($proposal['quotation_status'] ?? '') !== 'PO Raised'): ?>
      <button class="btn btn-primary btn-sm" onclick="document.getElementById('add-quote-form').style.display='block';this.style.display='none'">+ Add Quotation</button>
      <?php endif; ?>
    </div>

    <?php if ($quotes): ?>
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Vendor</th><th>Contact</th><th>Quoted Amount</th>
            <th>Delivery</th><th>Validity</th><th>Added by</th><th>Status</th>
            <?php if (in_array($role, ['principal','superadmin']) && ($proposal['quotation_status'] ?? '') === 'Quotes Collected'): ?>
            <th>Action</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($quotes as $i => $q): ?>
          <tr style="<?= $q['is_selected'] ? 'background:var(--gns)' : '' ?>">
            <td class="muted"><?= $i+1 ?></td>
            <td>
              <div class="fw500"><?= esc($q['vendor_name']) ?></div>
              <?php if ($q['vendor_address']): ?><div class="muted" style="font-size:11px"><?= esc(mb_strimwidth($q['vendor_address'],0,40,'…')) ?></div><?php endif; ?>
            </td>
            <td class="muted"><?= esc($q['vendor_contact'] ?: '—') ?></td>
            <td class="mono fw500" style="color:<?= $i===0 ? 'var(--gn)' : 'var(--tx)' ?>">
              <?= money($q['quoted_amount']) ?>
              <?php if ($i===0 && count($quotes)>1): ?><div style="font-size:10px;color:var(--gn)">★ Lowest</div><?php endif; ?>
            </td>
            <td class="muted"><?= $q['delivery_days'] ? $q['delivery_days'].' days' : '—' ?></td>
            <td class="muted"><?= $q['validity_days'] ? $q['validity_days'].' days' : '—' ?></td>
            <td class="muted"><?= esc($q['added_by_name'] ?? '—') ?></td>
            <td>
              <?php if ($q['is_selected']): ?>
                <span class="badge bg-green">✓ Selected</span>
                <?php if ($q['selected_by']): ?><div class="muted" style="font-size:10px">by <?= esc($q['selected_by']) ?></div><?php endif; ?>
              <?php else: ?>
                <span class="badge bg-gray">—</span>
              <?php endif; ?>
            </td>
            <?php if (in_array($role, ['principal','superadmin']) && ($proposal['quotation_status'] ?? '') === 'Quotes Collected'): ?>
            <td>
              <form method="POST">
                <input type="hidden" name="action" value="select_vendor">
                <input type="hidden" name="quote_id" value="<?= $q['id'] ?>">
                <input type="hidden" name="proposal_id" value="<?= $proposal['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm"
                  onclick="return confirm('Select <?= esc($q['vendor_name']) ?> for this purchase?')">
                  Select
                </button>
              </form>
            </td>
            <?php endif; ?>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty"><div class="empty-icon">📄</div><div class="empty-title">No quotations added yet</div><div class="empty-sub">Office staff should collect quotes from vendors and add them here</div></div>
    <?php endif; ?>
  </div>

  <!-- Raise PO button (office staff, after vendor selected) -->
  <?php //if (!in_array($role, ['principal']) && ($proposal['quotation_status'] ?? '') === 'Vendor Selected' && $selectedQuote): ?>
  <?php if ($role === 'office_staff' && ($proposal['quotation_status'] ?? '') === 'Vendor Selected' && $selectedQuote): ?>
  <div class="card" style="margin-bottom:16px">
    <div class="sec-hdr">
      <div>
        <div class="sec-title">Selected Vendor: <?= esc($selectedQuote['vendor_name']) ?></div>
        <div class="sec-sub">Amount: <?= money($selectedQuote['quoted_amount']) ?> · Selected by: <?= esc($selectedQuote['selected_by']) ?></div>
      </div>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="raise_po">
      <input type="hidden" name="proposal_id" value="<?= $proposal['id'] ?>">
      <button type="submit" class="btn btn-primary"
        onclick="return confirm('Raise Purchase Order for <?= esc($selectedQuote['vendor_name']) ?>?')">
        🧾 Raise Purchase Order
      </button>
    </form>
  </div>
  <?php endif; ?>

  <?php if (($proposal['quotation_status'] ?? '') === 'PO Raised' && $selectedQuote): ?>
  <div class="card">
    <div class="sec-hdr">
      <div>
        <div class="sec-title">Purchase Order Raised ✅</div>
        <div class="sec-sub">Vendor: <?= esc($selectedQuote['vendor_name']) ?> · <?= money($selectedQuote['quoted_amount']) ?></div>
      </div>
      <a href="?proposal_id=<?= $proposal['id'] ?>&po=1" class="btn btn-primary btn-sm">🖨 View / Print PO</a>
    </div>
    <div style="color:var(--tx2);font-size:13px">
      Purchase Order has been raised. Office staff can now proceed with Bill entry and Stock entry once goods are delivered.
    </div>
  </div>
  <?php endif; ?>

  <!-- Add quotation form -->
  <?php //if (!in_array($role, ['principal']) && ($proposal['quotation_status'] ?? '') !== 'PO Raised'): ?>
  <?php if ($role === 'office_staff' && ($proposal['quotation_status'] ?? '') !== 'PO Raised'): ?>
  <div id="add-quote-form" style="display:none" class="card">
    <div class="sec-title mb12">Add Vendor Quotation</div>
    <form method="POST">
      <input type="hidden" name="action" value="add_quote">
      <input type="hidden" name="proposal_id" value="<?= $proposal['id'] ?>">
      <div class="form-grid">
        <div class="fg span2">
          <label>Vendor / Supplier name *</label>
          <input name="vendor_name" placeholder="Company or shop name" required>
        </div>
        <div class="fg">
          <label>Contact (phone / email)</label>
          <input name="vendor_contact" placeholder="9XXXXXXXXX">
        </div>
        <div class="fg">
          <label>GSTIN</label>
          <input name="vendor_gstin" placeholder="29AAAA…">
        </div>
        <div class="fg span2">
          <label>Address</label>
          <input name="vendor_address" placeholder="Vendor address">
        </div>
        <div class="fg">
          <label>Quoted amount (₹) *</label>
          <input type="number" name="quoted_amount" step="0.01" min="0" required placeholder="0.00">
        </div>
        <div class="fg">
          <label>Delivery in (days)</label>
          <input type="number" name="delivery_days" min="0" placeholder="e.g. 15">
        </div>
        <div class="fg">
          <label>Quote validity (days)</label>
          <input type="number" name="validity_days" value="30" min="1">
        </div>
        <div class="fg span3">
          <label>Remarks / terms</label>
          <textarea name="remarks" placeholder="Any terms, conditions or notes from vendor…"></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Quotation</button>
        <button type="button" class="btn btn-ghost"
          onclick="document.getElementById('add-quote-form').style.display='none';document.querySelector('.btn-primary.btn-sm').style.display='inline-flex'">
          Cancel
        </button>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>

<?php else: ?>
<!-- ══════════════════════════════════════════════════════════ -->
<!-- PROPOSALS LIST — show approved proposals with quote status -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="sec-hdr">
  <div>
    <div class="sec-title">Quotations</div>
    <div class="sec-sub">Manage vendor quotations for approved proposals</div>
  </div>
</div>

<?php if ($proposals): ?>
<div class="tbl-wrap">
  <table>
    <thead>
      <tr>
        <th>Proposal no.</th><th>Item</th><th>Est. Amount</th>
        <th>Raised by</th><th>Quotation status</th><th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($proposals as $p):
      $qstatus = $p['quotation_status'] ?? 'Not Started';
      $qbadge  = ['Not Started'=>'bg-gray','Quotes Collected'=>'bg-amber','Vendor Selected'=>'bg-blue','PO Raised'=>'bg-green'][$qstatus] ?? 'bg-gray';
    ?>
      <tr>
        <td class="mono"><?= esc($p['proposal_no']) ?></td>
        <td>
          <div class="fw500"><?= esc(mb_strimwidth($p['item_name'],0,50,'…')) ?></div>
          <?php if (isSuperAdmin()): ?><div class="muted" style="font-size:11px"><?= esc($p['inst_name']) ?></div><?php endif; ?>
        </td>
        <td class="mono"><?= money($p['est_amount']) ?></td>
        <td class="muted"><?= esc($p['raised_by'] ?: '—') ?></td>
        <td><span class="badge <?= $qbadge ?>"><?= esc($qstatus) ?></span></td>
        <td>
          <a href="?proposal_id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">
            <?= $role === 'principal' && $qstatus === 'Quotes Collected' ? 'Select Vendor' : 'View / Add Quotes' ?>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="empty">
  <div class="empty-icon">📋</div>
  <div class="empty-title">No approved proposals yet</div>
  <div class="empty-sub">Proposals need to be approved before quotations can be collected</div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php layout_foot(); ?>