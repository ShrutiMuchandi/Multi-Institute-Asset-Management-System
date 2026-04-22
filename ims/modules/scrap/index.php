<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// modules/scrap/index.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';
requireLogin();

$iid = currentInstId();
$sf  = instFilter('sc');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'condemn') {
    if ($_SESSION['role'] === 'principal') { header("Location: ?err=" . urlencode('Access denied.')); exit; }
    $instId  = isSuperAdmin() ? (int)$_POST['inst_id'] : $iid;
    $stockId = (int)($_POST['stock_id'] ?? 0);
    $reason  = trim($_POST['reason'] ?? '');
    if (!$stockId || !$reason) { header("Location: ?err=" . urlencode('Asset and reason are required.')); exit; }

    $inst  = row("SELECT inst_code FROM institutes WHERE id=?", 'i', $instId);
    $scNo  = nextRef('SCR-' . ($inst['inst_code'] ?? 'INST'), 'scrap', 'scrap_no');

    $id = insert(
        "INSERT INTO scrap (inst_id,scrap_no,scrap_date,stock_id,reason,condemned_by,approved_by,disposal,realised_value,remarks,created_by)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)",
        'ississssdsi',
        $instId, $scNo,
        $_POST['scrap_date'] ?? date('Y-m-d'),
        $stockId, $reason,
        trim($_POST['condemned_by'] ?? ''),
        trim($_POST['approved_by'] ?? ''),
        $_POST['disposal'] ?? 'Write Off',
        (float)($_POST['realised_value'] ?? 0),
        trim($_POST['remarks'] ?? ''),
        $_SESSION['user_id']
    );
    query("UPDATE stock SET status='Condemned' WHERE id=?", 'i', $stockId);
    audit('CONDEMN_ITEM', 'scrap', $id, "Scrap no $scNo");
    header("Location: ?msg=" . urlencode("Item condemned. Scrap no: $scNo")); exit;
}

$scrapList = rows(
    "SELECT sc.*, s.asset_tag, s.item_name, s.unit_cost FROM scrap sc
     JOIN stock s ON sc.stock_id = s.id
     WHERE $sf ORDER BY sc.id DESC"
);

$stockItems = rows(
    "SELECT id, asset_tag, item_name, location FROM stock WHERE " . instFilter() . " AND status != 'Condemned' ORDER BY item_name"
);
$institutes = isSuperAdmin() ? rows("SELECT id, inst_name FROM institutes WHERE is_active=1") : [];

// Pre-fill from stock link
$preStock = isset($_GET['stock_id']) ? row("SELECT id, asset_tag, item_name FROM stock WHERE id=?", 'i', (int)$_GET['stock_id']) : null;
$activeTab = ($preStock || isset($_GET['add'])) ? 'add' : 'list';

layout_head('Scrap Register', 'scrap');
?>

<div class="tabs">
  <button class="tab <?= $activeTab==='list'?'active':'' ?>" onclick="showScrapTab('list')">Scrap register</button>
  <?php if ($_SESSION['role'] !== 'principal'): ?>
  <button class="tab <?= $activeTab==='add'?'active':''  ?>" onclick="showScrapTab('add')">+ Condemn item</button>
  <a href="<?= BASE_URL ?>/modules/scrap/print.php" class="btn btn-ghost btn-sm" style="margin-left:auto">🖨️ Print List</a>
  <?php endif; ?>
</div>

<div id="scpanel-list" class="tab-panel <?= $activeTab==='list'?'active':'' ?>">
  <div class="tbl-wrap">
    <table>
      <thead><tr><th>Scrap no.</th><th>Date</th><th>Asset tag</th><th>Item</th><th>Reason</th><th>Condemned by</th><th>Approved by</th><th>Disposal</th><th>Realised (₹)</th></tr></thead>
      <tbody>
      <?php if (!$scrapList): ?>
        <tr><td colspan="9"><div class="empty"><div class="empty-icon">🗑</div><div class="empty-title">No condemned items</div></div></td></tr>
      <?php else: foreach ($scrapList as $s): ?>
        <tr>
          <td class="mono"><?= esc($s['scrap_no']) ?></td>
          <td><?= esc($s['scrap_date']) ?></td>
          <td><span class="asset-tag"><?= esc($s['asset_tag']) ?></span></td>
          <td><?= esc($s['item_name']) ?></td>
          <td style="max-width:180px;color:var(--tx2)"><?= esc(mb_strimwidth($s['reason'],0,60,'…')) ?></td>
          <td class="muted"><?= esc($s['condemned_by'] ?: '—') ?></td>
          <td class="muted"><?= esc($s['approved_by'] ?: '—') ?></td>
          <td><span class="badge bg-gray"><?= esc($s['disposal']) ?></span></td>
          <td class="mono"><?= money($s['realised_value']) ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="scpanel-add" class="tab-panel <?= $activeTab==='add'?'active':'' ?>">
  <div class="card" style="max-width:680px">
    <div class="sec-title mb12">Condemn / scrap asset</div>
    <form method="POST">
      <input type="hidden" name="action" value="condemn">
      <div class="form-grid">
        <?php if (isSuperAdmin()): ?>
        <div class="fg"><label>Institute</label><select name="inst_id"><?php foreach($institutes as $ins):?><option value="<?=$ins['id']?>"><?=esc($ins['inst_name'])?></option><?php endforeach;?></select></div>
        <?php endif; ?>
        <div class="fg span2">
          <label>Asset *</label>
          <select name="stock_id" required>
            <option value="">— Select asset to condemn —</option>
            <?php foreach ($stockItems as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $preStock && $preStock['id']==$s['id']?'selected':'' ?>>
              <?= esc($s['asset_tag']) ?> — <?= esc(mb_strimwidth($s['item_name'],0,50,'…')) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Date of condemnation</label><input type="date" name="scrap_date" value="<?= date('Y-m-d') ?>"></div>
        <div class="fg span3">
          <label>Reason for condemnation *</label>
          <textarea name="reason" placeholder="Beyond repair / Obsolete / Accidental damage…" required></textarea>
        </div>
        <div class="fg"><label>Condemned by</label><input name="condemned_by" placeholder="Name + designation"></div>
        <div class="fg"><label>Approved by</label><input name="approved_by" placeholder="Principal / Management"></div>
        <div class="fg">
          <label>Disposal method</label>
          <select name="disposal">
            <option>Write Off</option><option>Auction</option><option>Donation</option>
            <option>Return to Supplier</option><option>Govt Disposal</option><option>Destroyed</option><option>Other</option>
          </select>
        </div>
        <div class="fg"><label>Realised value (₹)</label><input type="number" name="realised_value" value="0" min="0" step="0.01"></div>
        <div class="fg span2"><label>Remarks</label><textarea name="remarks" placeholder="Additional notes…"></textarea></div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Permanently condemn this item?')">Condemn item</button>
        <button type="reset" class="btn btn-ghost">Clear</button>
      </div>
    </form>
  </div>
</div>

<script>
function showScrapTab(t) {
  ['list','add'].forEach(p => document.getElementById('scpanel-'+p).classList.toggle('active', p===t));
  document.querySelectorAll('.tabs .tab').forEach((el,i) => el.classList.toggle('active', ['list','add'][i]===t));
}
</script>
<?php layout_foot(); ?>
