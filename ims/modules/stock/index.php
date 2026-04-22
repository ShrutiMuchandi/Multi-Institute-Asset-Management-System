<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// modules/stock/index.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';
requireLogin();

$iid  = currentInstId();
$fyid = currentFyId();
$sf   = instFilter('s');

// Handle POST — add stock items (office_staff and superadmin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_stock') {
    if ($_SESSION['role'] === 'principal') { header("Location: ?err=" . urlencode('Access denied.')); exit; }
    $instId   = isSuperAdmin() ? (int)$_POST['inst_id'] : $iid;
    $qty      = max(1, (int)($_POST['quantity'] ?? 1));
    $catId    = (int)($_POST['cat_id'] ?? 0) ?: null;
    $billId   = (int)($_POST['bill_id'] ?? 0) ?: null;
    $unitCost = (float)str_replace(',', '', $_POST['unit_cost'] ?? 0);
    $itemName = trim($_POST['item_name'] ?? '');
    $rcvDate  = $_POST['receipt_date'] ?? date('Y-m-d');

    if (!$itemName) { header("Location: ?err=" . urlencode('Item name is required.')); exit; }

    // Get category code for asset tag
    $cat = $catId ? row("SELECT cat_code FROM item_categories WHERE id=?", 'i', $catId) : null;
    $catCode = $cat['cat_code'] ?? 'OT';

    $tags = [];
    for ($i = 0; $i < $qty; $i++) {
        $tag = nextAssetTag($instId, $catCode);
        insert(
            "INSERT INTO stock (inst_id,fy_id,asset_tag,item_name,cat_id,quantity,unit,bill_id,receipt_date,unit_cost,location,dept,supplier,make_model,serial_no,warranty_end,remarks,added_by)
             VALUES (?,?,?,?,?,1,?,?,?,?,?,?,?,?,?,?,?,?)",
            'iissisisdsssssssi',
            $instId, $fyid, $tag, $itemName, $catId,
            trim($_POST['unit'] ?? 'Nos'), $billId, $rcvDate, $unitCost,
            trim($_POST['location'] ?? ''), trim($_POST['dept'] ?? ''),
            trim($_POST['supplier'] ?? ''), trim($_POST['make_model'] ?? ''),
            trim($_POST['serial_no'] ?? ''), trim($_POST['warranty_end'] ?: '') ?: null,
            trim($_POST['remarks'] ?? ''), $_SESSION['user_id']
        );
        $tags[] = $tag;
    }

    audit('ADD_STOCK', 'stock', 0, "Added $qty × $itemName, tags: " . implode(',', $tags));
    $msg = $qty > 1
        ? "$qty items added. Tags: {$tags[0]} to {$tags[$qty-1]}"
        : "Item {$tags[0]} added to stock.";
    header("Location: ?msg=" . urlencode($msg)); exit;
}

// Handle POST — bulk add existing stock (opening stock)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bulk_stock') {
    // Handled same way as add_stock but qty can be large
    // Redirect back to add_stock form - no separate logic needed
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$catFilter    = (int)($_GET['cat'] ?? 0);
$search       = trim($_GET['q'] ?? '');

$where = $sf;
$params = $iid ? ['i', $iid] : [];
if ($statusFilter) { $safe = db()->real_escape_string($statusFilter); $where .= " AND s.status = '$safe'"; }
if ($catFilter)    { $where .= " AND s.cat_id = $catFilter"; }
if ($search)       { $safe = db()->real_escape_string($search);
                     $where .= " AND (s.item_name LIKE '%$safe%' OR s.asset_tag LIKE '%$safe%' OR s.location LIKE '%$safe%' OR s.make_model LIKE '%$safe%')"; }

$items = rows(
    "SELECT s.*, ic.cat_name, b.bill_no, b.internal_ref FROM stock s
     LEFT JOIN item_categories ic ON s.cat_id = ic.id
     LEFT JOIN bills b ON s.bill_id = b.id
     WHERE $where ORDER BY s.id DESC"
);

$categories = rows("SELECT * FROM item_categories ORDER BY cat_name");
$institutes = isSuperAdmin() ? rows("SELECT id, inst_name FROM institutes WHERE is_active=1 ORDER BY inst_name") : [];
$bills = rows(
    "SELECT id, internal_ref, bill_no, vendor_name FROM bills WHERE " . instFilter() . " ORDER BY id DESC"
);

// Summary by category
$summary = rows(
    "SELECT ic.cat_name, s.status, COUNT(*) cnt, SUM(s.unit_cost) total_val
     FROM stock s LEFT JOIN item_categories ic ON s.cat_id=ic.id
     WHERE $sf GROUP BY ic.id, s.status ORDER BY ic.cat_name"
);

$activeTab = isset($_GET['add']) ? 'add' : (isset($_GET['summary']) ? 'summary' : 'list');
layout_head('Stock Book', 'stock');
?>

<div class="tabs" id="stock-tabs">
  <button class="tab <?= $activeTab==='list'?'active':'' ?>"    onclick="showStockTab('list')">Stock book</button>
  <button class="tab <?= $activeTab==='summary'?'active':'' ?>" onclick="showStockTab('summary')">Category summary</button>
  <?php if ($_SESSION['role'] !== 'principal'): ?>
  <button class="tab <?= $activeTab==='add'?'active':'' ?>"     onclick="showStockTab('add')">+ Add / enter stock</button>
  <?php endif; ?>
</div>

<!-- STOCK LIST -->
<div id="spanel-list" class="tab-panel <?= $activeTab==='list'?'active':'' ?>">
  <div class="sec-hdr">
    <div class="sec-title">All stock items <span class="muted" style="font-size:12px;font-weight:400">(<?= count($items) ?>)</span></div>
    <form method="get" style="display:flex;gap:6px;flex-wrap:wrap">
      <input name="q" value="<?= esc($search) ?>" placeholder="Search item / tag / location…"
             style="background:var(--bg3);border:1px solid var(--bd2);border-radius:var(--r);padding:5px 10px;color:var(--tx);font-size:13px;width:200px">
      <select name="cat" style="background:var(--bg3);border:1px solid var(--bd2);border-radius:var(--r);padding:5px 8px;color:var(--tx);font-size:13px">
        <option value="">All categories</option>
        <?php foreach ($categories as $c): ?>
        <option value="<?= $c['id'] ?>" <?= $catFilter==$c['id']?'selected':'' ?>><?= esc($c['cat_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status" style="background:var(--bg3);border:1px solid var(--bd2);border-radius:var(--r);padding:5px 8px;color:var(--tx);font-size:13px">
        <option value="">All status</option>
        <option value="Active"       <?= $statusFilter==='Active'?'selected':'' ?>>Active</option>
        <option value="In Use"       <?= $statusFilter==='In Use'?'selected':'' ?>>In Use</option>
        <option value="Under Repair" <?= $statusFilter==='Under Repair'?'selected':'' ?>>Under Repair</option>
        <option value="Condemned"    <?= $statusFilter==='Condemned'?'selected':'' ?>>Condemned</option>
      </select>
      <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
	  
	  <!--added print button -->
	  <a href="<?= BASE_URL ?>/modules/stock/print.php?<?= http_build_query($_GET) ?>" class="btn btn-ghost btn-sm">🖨️ Print</a>
	  
    </form>
  </div>
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Asset tag</th><th>Item name</th><th>Category</th><th>Bill / Invoice</th>
        <th>Received</th><th>Location</th><th>Dept</th><th>Unit cost</th><th>Warranty</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php if (!$items): ?>
        <tr><td colspan="11"><div class="empty"><div class="empty-icon">📦</div><div class="empty-title">No stock items found</div></div></td></tr>
      <?php else: foreach ($items as $s):
        $statusBadge = ['Active'=>'bg-green','In Use'=>'bg-blue','Under Repair'=>'bg-amber','Condemned'=>'bg-red'][$s['status']] ?? 'bg-gray';
      ?>
        <tr>
          <td><span class="asset-tag"><?= esc($s['asset_tag']) ?></span></td>
          <td>
            <div class="fw500"><?= esc($s['item_name']) ?></div>
            <?php if ($s['make_model']): ?><div class="muted" style="font-size:11px"><?= esc($s['make_model']) ?></div><?php endif; ?>
          </td>
          <td><span class="badge bg-gray"><?= esc($s['cat_name'] ?? '—') ?></span></td>
          <td class="mono"><?= esc($s['internal_ref'] ?: $s['bill_no'] ?: '—') ?></td>
          <td><?= esc($s['receipt_date']) ?></td>
          <td><?= esc($s['location'] ?: '—') ?></td>
          <td class="muted"><?= esc($s['dept'] ?: '—') ?></td>
          <td class="mono"><?= money($s['unit_cost']) ?></td>
          <td class="muted" style="font-size:11px"><?= esc($s['warranty_end'] ?: '—') ?></td>
          <td><span class="badge <?= $statusBadge ?>"><?= esc($s['status']) ?></span></td>
          <td>
            <?php if ($s['status'] !== 'Condemned' && $_SESSION['role'] !== 'principal'): ?>
            <a href="<?= BASE_URL ?>/modules/scrap/?stock_id=<?= $s['id'] ?>"
               class="btn btn-danger btn-sm"
               onclick="return confirm('Condemn <?= esc($s['asset_tag']) ?>?')">Condemn</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- SUMMARY -->
<div id="spanel-summary" class="tab-panel <?= $activeTab==='summary'?'active':'' ?>">
  <div class="sec-title mb12">Stock summary by category</div>
  <div class="tbl-wrap">
    <table>
      <thead><tr><th>Category</th><th>Status</th><th>Count</th><th>Total value (₹)</th></tr></thead>
      <tbody>
      <?php if (!$summary): ?>
        <tr><td colspan="4" class="muted" style="padding:20px;text-align:center">No data</td></tr>
      <?php else: foreach ($summary as $row):
        $sb = ['Active'=>'bg-green','In Use'=>'bg-blue','Under Repair'=>'bg-amber','Condemned'=>'bg-red'][$row['status']] ?? 'bg-gray';
      ?>
        <tr>
          <td class="fw500"><?= esc($row['cat_name'] ?? '—') ?></td>
          <td><span class="badge <?= $sb ?>"><?= esc($row['status']) ?></span></td>
          <td class="mono"><?= number_format($row['cnt']) ?></td>
          <td class="mono text-green"><?= money($row['total_val']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ADD STOCK -->
<div id="spanel-add" class="tab-panel <?= $activeTab==='add'?'active':'' ?>">
  <div class="card" style="max-width:760px">
    <div class="sec-title">Add item(s) to stock book</div>
    <div class="sec-sub mb12">
      For existing stock (opening entries), enter how many you already have. For new purchases, link to the bill.
      Each item gets its own unique asset tag automatically.
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="add_stock">
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
        <div class="fg span2">
          <label>Item name *</label>
          <input name="item_name" placeholder="e.g. Desktop Computer — Dell OptiPlex 3000" required>
        </div>
        <div class="fg">
          <label>Category *</label>
          <select name="cat_id" required>
            <option value="">— Select —</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>"><?= esc($c['cat_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Quantity (no. of items) *</label>
          <input type="number" name="quantity" min="1" value="1" required>
        </div>
        <div class="fg">
          <label>Unit</label>
          <input name="unit" value="Nos" placeholder="Nos / Sets / Ltrs">
        </div>
        <div class="fg">
          <label>Link to bill / invoice</label>
          <select name="bill_id">
            <option value="">— None / Opening stock —</option>
            <?php foreach ($bills as $b): ?>
            <option value="<?= $b['id'] ?>"><?= esc($b['internal_ref']) ?> — <?= esc(mb_strimwidth($b['vendor_name'],0,30,'…')) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Receipt / entry date *</label>
          <input type="date" name="receipt_date" value="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="fg">
          <label>Unit cost (₹)</label>
          <input type="number" name="unit_cost" step="0.01" min="0" placeholder="0.00">
        </div>
        <div class="fg span2">
          <label>Location / room</label>
          <input name="location" placeholder="Computer Lab 1 / Store / Library">
        </div>
        <div class="fg">
          <label>Department</label>
          <input name="dept" placeholder="CS / Mech / Admin">
        </div>
        <div class="fg">
          <label>Supplier / brand</label>
          <input name="supplier" placeholder="Dell / HP / Local">
        </div>
        <div class="fg">
          <label>Make / model</label>
          <input name="make_model" placeholder="OptiPlex 3000 / Optiplex 7080">
        </div>
        <div class="fg">
          <label>Serial no. (if single item)</label>
          <input name="serial_no" placeholder="For single-item entries">
        </div>
        <div class="fg">
          <label>Warranty end date</label>
          <input type="date" name="warranty_end">
        </div>
        <div class="fg span3">
          <label>Remarks</label>
          <textarea name="remarks" placeholder="Configuration details, condition, notes…"></textarea>
        </div>
      </div>
      <div class="notice notice-blue">
        Asset tags are auto-generated (e.g. SVEC-IT-2026-001 … SVEC-IT-2026-020 for 20 computers).
        Print and affix / engrave on each item.
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Add to stock book</button>
        <button type="reset" class="btn btn-ghost">Clear</button>
      </div>
    </form>
  </div>
</div>

<script>
function showStockTab(t) {
  ['list','summary','add'].forEach(p => {
    document.getElementById('spanel-'+p).classList.toggle('active', p===t);
  });
  document.querySelectorAll('#stock-tabs .tab').forEach((el,i) => {
    el.classList.toggle('active', ['list','summary','add'][i] === t);
  });
}
</script>
<?php layout_foot(); ?>
