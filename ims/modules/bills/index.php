<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// modules/bills/index.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';
requireLogin();

$iid  = currentInstId();
$fyid = currentFyId();
$sf   = instFilter('b');

// Handle POST — save bill (office_staff and superadmin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_bill') {
    if ($_SESSION['role'] === 'principal') { header("Location: ?err=" . urlencode('Access denied.')); exit; }
    $instId   = isSuperAdmin() ? (int)$_POST['inst_id'] : $iid;
    $catId    = (int)($_POST['cat_id'] ?? 0) ?: null;
    $propId   = (int)($_POST['proposal_id'] ?? 0) ?: null;
    $amount   = (float)str_replace(',', '', $_POST['amount']   ?? 0);
    $gstAmt   = (float)str_replace(',', '', $_POST['gst_amt']  ?? 0);
    $billNo   = trim($_POST['bill_no'] ?? '');
    $vendor   = trim($_POST['vendor']  ?? '');
    $desc     = trim($_POST['description'] ?? '');
    $payMode  = $_POST['pay_mode'] ?? 'Cash';
    $payRef   = trim($_POST['pay_ref'] ?? '');
    $billDate = $_POST['bill_date'] ?? date('Y-m-d');
    $rcvDate  = $_POST['receipt_date'] ?: null;
    $payDate  = $_POST['pay_date'] ?: null;

    if (!$vendor || !$desc || !$amount) {
        header("Location: ?err=" . urlencode('Vendor, description and amount are required.')); exit;
    }

    // Generate internal ref
    $inst = row("SELECT inst_code FROM institutes WHERE id = ?", 'i', $instId);
    $prefix  = 'BILL-' . ($inst['inst_code'] ?? 'INST');
    $intRef  = nextRef($prefix, 'bills', 'internal_ref');

    $id = insert(
        "INSERT INTO bills (inst_id,fy_id,bill_no,internal_ref,bill_date,receipt_date,vendor_name,vendor_gstin,vendor_address,description,cat_id,amount,gst_amount,pay_mode,pay_ref_no,pay_date,proposal_id,entered_by,verified_by,remarks)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        'iissssssssiidssssiss',
        $instId, $fyid, $billNo, $intRef, $billDate, $rcvDate,
        $vendor, trim($_POST['vendor_gstin'] ?? ''), trim($_POST['vendor_address'] ?? ''),
        $desc, $catId, $amount, $gstAmt, $payMode, $payRef, $payDate,
        $propId, $_SESSION['user_id'],
        trim($_POST['verified_by'] ?? ''), trim($_POST['remarks'] ?? '')
    );

    // If linked to proposal, mark as Purchased
    if ($propId) query("UPDATE proposals SET status='Purchased' WHERE id=?", 'i', $propId);

    audit('ADD_BILL', 'bills', $id, "Bill $intRef, vendor $vendor, amount $amount");
    header("Location: ?msg=" . urlencode("Bill $intRef saved successfully.")); exit;
}

// Filters
$catFilter = (int)($_GET['cat'] ?? 0);
$search    = trim($_GET['q'] ?? '');

$where = "$sf";
if ($catFilter) { $where .= " AND b.cat_id = $catFilter"; }
if ($search)    { $safe = db()->real_escape_string($search);
                  $where .= " AND (b.vendor_name LIKE '%$safe%' OR b.bill_no LIKE '%$safe%' OR b.internal_ref LIKE '%$safe%' OR b.description LIKE '%$safe%')"; }

$bills = rows(
    "SELECT b.*, ic.cat_name, i.inst_name, i.inst_code, u.full_name AS entered_by_name
     FROM bills b
     LEFT JOIN item_categories ic ON b.cat_id = ic.id
     LEFT JOIN institutes i ON b.inst_id = i.id
     LEFT JOIN users u ON b.entered_by = u.id
     WHERE $where ORDER BY b.id DESC"
);

$categories  = rows("SELECT * FROM item_categories ORDER BY cat_name");
$institutes  = isSuperAdmin() ? rows("SELECT id, inst_name FROM institutes WHERE is_active=1 ORDER BY inst_name") : [];
$proposals = rows(
    "SELECT id, proposal_no, item_name FROM proposals WHERE " . instFilter() . " AND status='Approved'"
);

$activeTab = isset($_GET['new']) ? 'add' : 'list';
layout_head('Bill Register', 'bills');
?>

<div class="tabs" id="bill-tabs">
  <button class="tab <?= $activeTab==='list'?'active':'' ?>" onclick="showBillTab('list')">Bill register</button>
  <?php if ($_SESSION['role'] !== 'principal'): ?>
  <button class="tab <?= $activeTab==='add' ?'active':'' ?>" onclick="showBillTab('add')">+ Add bill</button>
  <?php endif; ?>
</div>

<!-- LIST PANEL -->
<div id="panel-list" class="tab-panel <?= $activeTab==='list'?'active':'' ?>">
  <div class="sec-hdr">
    <div>
      <div class="sec-title">All bills <span class="muted" style="font-size:12px;font-weight:400">(<?= count($bills) ?> records)</span></div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <form method="get" style="display:flex;gap:6px">
        <input name="q" value="<?= esc($search) ?>" placeholder="Search vendor / bill no…"
               style="background:var(--bg3);border:1px solid var(--bd2);border-radius:var(--r);padding:5px 10px;color:var(--tx);font-size:13px;width:200px">
        <select name="cat" style="background:var(--bg3);border:1px solid var(--bd2);border-radius:var(--r);padding:5px 8px;color:var(--tx);font-size:13px">
          <option value="">All categories</option>
          <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $catFilter==$c['id']?'selected':'' ?>><?= esc($c['cat_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
      </form>
      <?php if ($_SESSION['role'] !== 'principal'): ?>
      <button class="btn btn-primary btn-sm" onclick="showBillTab('add')">+ Add bill</button>
      <?php endif; ?>
    </div>
  </div>
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>Internal ref</th><th>Bill no.</th><th>Date</th><th>Vendor</th>
          <th>Category</th><th>Amount</th><th>GST</th><th>Total</th>
          <th>Pay mode</th><th>Ref / Cheque no.</th>
          <?php if (isSuperAdmin()): ?><th>Institute</th><?php endif; ?>
          <th>Verified / Auth. by</th>
          <th>Entered by</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!$bills): ?>
        <tr><td colspan="12"><div class="empty"><div class="empty-icon">🧾</div><div class="empty-title">No bills found</div></div></td></tr>
      <?php else: foreach ($bills as $b): ?>
        <tr>
          <td class="mono"><?= esc($b['internal_ref']) ?></td>
          <td class="mono"><?= esc($b['bill_no']) ?></td>
          <td><?= esc($b['bill_date']) ?></td>
          <td>
            <div class="fw500"><?= esc($b['vendor_name']) ?></div>
            <div class="muted" style="font-size:11px"><?= esc(mb_strimwidth($b['description'],0,50,'…')) ?></div>
          </td>
          <td><span class="badge bg-gray"><?= esc($b['cat_name'] ?? '—') ?></span></td>
          <td class="mono"><?= money($b['amount']) ?></td>
          <td class="mono"><?= money($b['gst_amount']) ?></td>
          <td class="mono fw500 text-green"><?= money($b['total_amount']) ?></td>
          <td><?= esc($b['pay_mode']) ?></td>
          <td class="mono"><?= esc($b['pay_ref_no'] ?: '—') ?></td>
          <?php if (isSuperAdmin()): ?><td><?= esc($b['inst_name']) ?></td><?php endif; ?>
          <td class="muted"><?= esc($b['verified_by'] ?: '—') ?></td>
          <td class="muted"><?= esc($b['entered_by_name'] ?? '—') ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD PANEL -->
<div id="panel-add" class="tab-panel <?= $activeTab==='add'?'active':'' ?>">
  <div class="card" style="max-width:760px">
    <div class="sec-title mb12">Enter new bill</div>
    <form method="POST">
      <input type="hidden" name="action" value="save_bill">
      <div class="form-grid">
        <?php if (isSuperAdmin()): ?>
        <div class="fg">
          <label>Institute *</label>
          <select name="inst_id" required>
            <?php foreach ($institutes as $ins): ?>
            <option value="<?= $ins['id'] ?>"><?= esc($ins['inst_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="fg">
          <label>Invoice / Bill no. (as on bill)</label>
          <input name="bill_no" placeholder="e.g. INV/2026/00124">
        </div>
        <div class="fg">
          <label>Bill date *</label>
          <input type="date" name="bill_date" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="fg">
          <label>Receipt date</label>
          <input type="date" name="receipt_date" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="fg span2">
          <label>Vendor / supplier name *</label>
          <input name="vendor" placeholder="Shop / company name" required>
        </div>
        <div class="fg">
          <label>Vendor GSTIN</label>
          <input name="vendor_gstin" placeholder="29AAAA…">
        </div>
        <div class="fg span3">
          <label>Description of goods / services *</label>
          <input name="description" placeholder="What was purchased" required>
        </div>
        <div class="fg">
          <label>Category</label>
          <select name="cat_id">
            <option value="">— Select —</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= esc($c['cat_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Amount (₹) *</label>
          <input type="number" name="amount" step="0.01" min="0" placeholder="0.00" required>
        </div>
        <div class="fg">
          <label>GST amount (₹)</label>
          <input type="number" name="gst_amt" step="0.01" min="0" placeholder="0.00" value="0">
        </div>
        <div class="fg">
          <label>Payment mode</label>
          <select name="pay_mode">
            <option>Cheque</option><option>NEFT/RTGS</option><option>Cash</option>
            <option>DD</option><option>UPI</option><option>Credit Card</option><option>Other</option>
          </select>
        </div>
        <div class="fg">
          <label>Cheque / UTR / Ref. no. *</label>
          <input name="pay_ref" placeholder="This number goes on all purchased items">
        </div>
        <div class="fg">
          <label>Payment date</label>
          <input type="date" name="pay_date">
        </div>
        <div class="fg">
          <label>Link to approved proposal</label>
          <select name="proposal_id">
            <option value="">— None —</option>
            <?php foreach ($proposals as $pr): ?>
            <option value="<?= $pr['id'] ?>"><?= esc($pr['proposal_no']) ?> — <?= esc(mb_strimwidth($pr['item_name'],0,40,'…')) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Verified / authorised by</label>
          <input name="verified_by" placeholder="Principal / HOD name">
        </div>
        <div class="fg span2">
          <label>Remarks</label>
          <textarea name="remarks" placeholder="Any notes…"></textarea>
        </div>
      </div>
      <div class="notice notice-amber">
        The cheque / UTR number entered above must be written / labelled on every physical item purchased under this bill.
      </div>
	  <!--Added Extra -->
	  <div class="notice notice-blue" style="margin-top:8px">
    💡 For small expenses like refreshments, tea, stationery or maintenance — you can directly add a bill here without raising a proposal.
</div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save bill entry</button>
        <button type="reset" class="btn btn-ghost">Clear</button>
      </div>
    </form>
  </div>
</div>

<script>
function showBillTab(tab) {
  document.getElementById('panel-list').classList.remove('active');
  document.getElementById('panel-add').classList.remove('active');
  document.getElementById('panel-' + tab).classList.add('active');
  document.querySelectorAll('#bill-tabs .tab').forEach((t,i) => {
    t.classList.toggle('active', (tab === 'list' && i === 0) || (tab === 'add' && i === 1));
  });
}
</script>
<?php layout_foot(); ?>