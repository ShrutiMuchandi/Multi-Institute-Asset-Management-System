<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// modules/movement/index.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';
requireLogin();

$iid = currentInstId();
$sf  = instFilter('m');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_location') {
    header('Content-Type: application/json');
    $locName = trim($_POST['location_name'] ?? '');
    $instId  = (int)$iid;
    if (!$locName) { echo json_encode(['success'=>false,'message'=>'Name required']); exit; }
    $exists = row("SELECT id FROM locations WHERE inst_id=? AND location_name=?", 'is', $instId, $locName);
    if ($exists) { echo json_encode(['success'=>false,'message'=>'Already exists!']); exit; }
    insert("INSERT INTO locations (inst_id, location_name) VALUES (?,?)", 'is', $instId, $locName);
    echo json_encode(['success'=>true,'message'=>"'$locName' added!"]); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_move') {
   // if ($_SESSION['role'] === 'principal') { header("Location: ?err=" . urlencode('Access denied.')); exit; }
	if ($_SESSION['role'] === 'superadmin') { header("Location: ?err=" . urlencode('Access denied.')); exit; }
    $instId  = isSuperAdmin() ? (int)$_POST['inst_id'] : (int)$iid;
    $stockId = (int)($_POST['stock_id'] ?? 0);
    $fromLoc = trim($_POST['from_loc'] ?? '');
    $toLoc   = trim($_POST['to_loc'] ?? '');
    $issueTo = trim($_POST['issued_to'] ?? '');

    if (!$stockId || !$fromLoc || !$toLoc || !$issueTo) {
        header("Location: ?err=" . urlencode('Asset, from/to locations, and issued to are required.')); exit;
    }

    $inst   = row("SELECT inst_code FROM institutes WHERE id=?", 'i', $instId);
    $refNo  = nextRef('MOV-' . ($inst['inst_code'] ?? 'INST'), 'movements', 'ref_no');

    $id = insert(
        "INSERT INTO movements (inst_id,ref_no,move_date,move_type,stock_id,qty_moved,from_loc,to_loc,issued_to,issued_by,expected_return,remarks,created_by)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)",
        'isssidssssssi',
        $instId, $refNo,
        $_POST['move_date'] ?? date('Y-m-d'),
        $_POST['move_type'] ?? 'Issue',
        $stockId, (float)($_POST['qty_moved'] ?? 1),
        $fromLoc, $toLoc, $issueTo,
        trim($_POST['issued_by'] ?? ''),
        trim($_POST['expected_return'] ?: '') ?: null,
        trim($_POST['remarks'] ?? ''),
        $_SESSION['user_id']
    );

    // Update stock location
    query("UPDATE stock SET location=?, status='In Use' WHERE id=?", 'si', $toLoc, $stockId);
    audit('ADD_MOVEMENT', 'movements', $id, "Ref $refNo");
    header("Location: ?msg=" . urlencode("Movement $refNo logged.")); exit;
}

// Mark return
if (isset($_GET['return'])) {
    $mid = (int)$_GET['return'];
    $mov = row("SELECT * FROM movements WHERE id=?", 'i', $mid);
    if ($mov) {
        query("UPDATE movements SET status='Returned', actual_return=CURDATE() WHERE id=?", 'i', $mid);
        query("UPDATE stock SET location=?, status='Active' WHERE id=?", 'si', $mov['from_loc'], $mov['stock_id']);
        audit('RETURN_MOVEMENT', 'movements', $mid);
        header("Location: ?msg=" . urlencode("Item returned and stock updated.")); exit;
    }
}

$movements = rows(
    "SELECT m.*, s.asset_tag, s.item_name FROM movements m
     JOIN stock s ON m.stock_id = s.id
     WHERE $sf ORDER BY m.id DESC"
);

$stockItems = rows(
    "SELECT id, asset_tag, item_name, location FROM stock WHERE " . instFilter() . " AND status != 'Condemned' ORDER BY item_name"
);
$institutes = isSuperAdmin() ? rows("SELECT id, inst_name FROM institutes WHERE is_active=1") : [];

//$locations = rows(
//    "SELECT location_name FROM locations WHERE inst_id=? ORDER BY location_name ASC",
//);
$locations = rows("SELECT location_name FROM locations WHERE inst_id=?", 'i', (int)$iid);

$activeTab = isset($_GET['add']) ? 'add' : 'list';
layout_head('Movement Register', 'movement');
?>

<div class="tabs">
  <button class="tab <?= $activeTab==='list'?'active':'' ?>" onclick="showMoveTab('list')">Movement register</button>
  <?php //if ($_SESSION['role'] !== 'principal'): ?>
  <?php if ($_SESSION['role'] !== 'superadmin' && $_SESSION['role'] !== 'principal'): ?>
  <button class="tab <?= $activeTab==='add'?'active':''  ?>" onclick="showMoveTab('add')">+ Log movement</button>
  <a href="<?= BASE_URL ?>/modules/movement/print_list.php" class="btn btn-ghost btn-sm" style="margin-left:auto">🖨️ Print List</a>
  <?php endif; ?>
</div>

<div id="mpanel-list" class="tab-panel <?= $activeTab==='list'?'active':'' ?>">
  <div class="tbl-wrap">
    <table>
      <thead><tr><th>Ref no.</th><th>Date</th><th>Type</th><th>Asset tag</th><th>Item</th><th>From</th><th>To</th><th>Issued to</th><th>Return date</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php if (!$movements): ?>
        <tr><td colspan="11"><div class="empty"><div class="empty-icon">⇄</div><div class="empty-title">No movements recorded</div></div></td></tr>
      <?php else: foreach ($movements as $m):
        $sb = ['Issued'=>'bg-blue','Returned'=>'bg-green','Pending Return'=>'bg-amber'][$m['status']] ?? 'bg-gray';
      ?>
        <tr>
          <td class="mono"><?= esc($m['ref_no']) ?></td>
          <td><?= esc($m['move_date']) ?></td>
          <td><span class="badge bg-gray"><?= esc($m['move_type']) ?></span></td>
          <td><span class="asset-tag"><?= esc($m['asset_tag']) ?></span></td>
          <td><?= esc(mb_strimwidth($m['item_name'],0,40,'…')) ?></td>
          <td class="muted"><?= esc($m['from_loc']) ?></td>
          <td class="text-green"><?= esc($m['to_loc']) ?></td>
          <td><?= esc($m['issued_to']) ?></td>
          <td class="muted"><?= esc($m['expected_return'] ?: '—') ?></td>
          <td><span class="badge <?= $sb ?>"><?= esc($m['status']) ?></span></td>
          <td>
            <?php //if ($m['status'] === 'Issued' && $_SESSION['role'] !== 'principal'): ?>
			<?php if ($m['status'] === 'Issued' && $_SESSION['role'] === 'office_staff'): ?>
            <a href="?return=<?= $m['id'] ?>" class="btn btn-success btn-sm"
               onclick="return confirm('Mark as returned?')">Return</a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/modules/movement/print.php?id=<?= $m['id'] ?>&type=full" class="btn btn-ghost btn-sm">Print🖨️</a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="mpanel-add" class="tab-panel <?= $activeTab==='add'?'active':'' ?>">
  <div class="card" style="max-width:700px">
    <div class="sec-title mb12">Log asset movement</div>
    <form method="POST">
      <input type="hidden" name="action" value="save_move">
      <div class="form-grid">
        <?php if (isSuperAdmin()): ?>
        <div class="fg"><label>Institute</label><select name="inst_id"><?php foreach($institutes as $ins):?><option value="<?=$ins['id']?>"><?=esc($ins['inst_name'])?></option><?php endforeach;?></select></div>
        <?php endif; ?>
        <div class="fg">
          <label>Movement type</label>
          <select name="move_type">
            <option>Issue</option><option>Transfer</option><option>Return</option>
            <option>Send for Repair</option><option>Return from Repair</option><option>Temporary Issue</option>
          </select>
        </div>
        <div class="fg"><label>Date</label><input type="date" name="move_date" value="<?= date('Y-m-d') ?>"></div>
        <div class="fg span2">
          <label>Asset *</label>
          <select name="stock_id" required>
            <option value="">— Select asset —</option>
            <?php foreach ($stockItems as $s): ?>
            <option value="<?= $s['id'] ?>"><?= esc($s['asset_tag']) ?> — <?= esc(mb_strimwidth($s['item_name'],0,50,'…')) ?> (<?= esc($s['location'] ?: 'Store') ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Quantity</label><input type="number" name="qty_moved" value="1" min="1"></div>

        <!-- From location with inline + Add -->
        <div class="fg">
          <label>From location *</label>
          <div style="display:flex;gap:6px">
            <select name="from_loc" id="from_loc_select" required style="flex:1">
              <option value="">— Select location —</option>
              <?php foreach ($locations as $loc): ?>
              <option value="<?= esc($loc['location_name']) ?>"><?= esc($loc['location_name']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-ghost btn-sm" onclick="openAddLocation()" style="white-space:nowrap">+ Add</button>
          </div>
        </div>

        <div class="fg"><label>To location *</label><input name="to_loc" placeholder="Room 204 / Lab 3" required></div>
        <div class="fg span2"><label>Issued to (name + designation) *</label><input name="issued_to" placeholder="Mr. Ramesh, Lab Assistant" required></div>
        <div class="fg"><label>Issued by</label><input name="issued_by" placeholder="Store incharge"></div>
        <div class="fg"><label>Expected return date</label><input type="date" name="expected_return"></div>
        <div class="fg span3"><label>Remarks / purpose</label><textarea name="remarks" placeholder="Purpose of movement…"></textarea></div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Log movement</button>
        <button type="reset" class="btn btn-ghost">Clear</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Location Modal -->
<div id="addLocModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center">
  <div style="background:var(--bg2);border-radius:var(--rl);padding:24px;width:100%;max-width:380px;border:1px solid var(--bd)">
    <div class="fw500" style="font-size:15px;margin-bottom:14px">📍 Add New Location</div>
    <div class="fg" style="margin-bottom:12px">
      <label>Location name *</label>
      <input type="text" id="new_loc_name" placeholder="e.g. Computer Lab 1, Library">
    </div>
    <div id="loc_msg" style="font-size:12px;margin-bottom:8px;display:none"></div>
    <div style="display:flex;gap:8px">
      <button type="button" class="btn btn-primary" onclick="saveLocationAjax()">Add Location</button>
      <button type="button" class="btn btn-ghost" onclick="closeAddLocation()">Cancel</button>
    </div>
  </div>
</div>

<script>
function showMoveTab(t) {
  ['list','add'].forEach(p => document.getElementById('mpanel-'+p).classList.toggle('active', p===t));
  document.querySelectorAll('.tabs .tab').forEach((el,i) => el.classList.toggle('active', ['list','add'][i]===t));
}
function openAddLocation() {
  document.getElementById('addLocModal').style.display = 'flex';
  document.getElementById('new_loc_name').focus();
}
function closeAddLocation() {
  document.getElementById('addLocModal').style.display = 'none';
  document.getElementById('new_loc_name').value = '';
  document.getElementById('loc_msg').style.display = 'none';
}
document.getElementById('addLocModal').addEventListener('click', function(e) {
  if (e.target === this) closeAddLocation();
});
function saveLocationAjax() {
  const locName = document.getElementById('new_loc_name').value.trim();
  const msg = document.getElementById('loc_msg');
  if (!locName) {
    msg.style.display = 'block';
    msg.style.color = 'var(--rd)';
    msg.textContent = '❌ Location name is required!';
    return;
  }
  const fd = new FormData();
  fd.append('action', 'add_location');
  fd.append('location_name', locName);
  fetch('', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      msg.style.display = 'block';
      if (data.success) {
        const sel = document.getElementById('from_loc_select');
        const opt = document.createElement('option');
        opt.value = locName;
        opt.textContent = locName;
        opt.selected = true;
        sel.appendChild(opt);
        msg.style.color = 'var(--gn)';
        msg.textContent = '✅ ' + data.message;
        setTimeout(() => closeAddLocation(), 800);
      } else {
        msg.style.color = 'var(--rd)';
        msg.textContent = '❌ ' + data.message;
      }
    });
}
</script>
<?php layout_foot(); ?>