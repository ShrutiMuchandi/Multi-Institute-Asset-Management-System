<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// modules/proposals/index.php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';
requireLogin();

$iid  = currentInstId();
$fyid = currentFyId();
$sf   = instFilter('p');

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Submit new proposal
    if ($action === 'save_proposal') {
        $instId   = isSuperAdmin() ? (int)$_POST['inst_id'] : $iid;
        $catId    = (int)($_POST['cat_id'] ?? 0) ?: null;
        $itemName = trim($_POST['item_name'] ?? '');
        $amount   = (float)str_replace(',', '', $_POST['est_amount'] ?? 0);
        $qty      = (float)($_POST['quantity'] ?? 1);

        if (!$itemName || !$amount) { header("Location: ?err=" . urlencode('Item name and estimated amount are required.')); exit; }

        $inst   = row("SELECT inst_code FROM institutes WHERE id=?", 'i', $instId);
        $prefix = 'PROP-' . ($inst['inst_code'] ?? 'INST');
        $propNo = nextRef($prefix, 'proposals', 'proposal_no');

        $id = insert(
            "INSERT INTO proposals (inst_id,fy_id,proposal_no,proposal_date,department,item_name,cat_id,quantity,unit,est_amount,justification,raised_by,designation,created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            'iissssidsdsssi',
            $instId, $fyid, $propNo,
            $_POST['proposal_date'] ?? date('Y-m-d'),
            trim($_POST['department'] ?? ''), $itemName, $catId, $qty,
            trim($_POST['unit'] ?? 'Nos'), $amount,
            trim($_POST['justification'] ?? ''),
            trim($_POST['raised_by'] ?? ''),
            trim($_POST['designation'] ?? ''),
            $_SESSION['user_id']
        );
        audit('ADD_PROPOSAL', 'proposals', $id, "Proposal $propNo for $itemName");
        header("Location: ?msg=" . urlencode("Proposal $propNo submitted.")); exit;
    }

    // Approve / reject — superadmin only
    if ($action === 'approve' || $action === 'reject') {
        if (!isSuperAdmin()) { header("Location: ?err=" . urlencode('Access denied.')); exit; }
        $propId = (int)($_POST['prop_id'] ?? 0);
        $status = $action === 'approve' ? 'Approved' : 'Rejected';
        $note   = trim($_POST['approval_note'] ?? '');
        $by     = trim($_POST['approved_by'] ?? ($_SESSION['full_name'] ?? 'Super Admin'));
        query("UPDATE proposals SET status=?, approved_by=?, approved_date=CURDATE(), approval_note=? WHERE id=?",
              'sssi', $status, $by, $note, $propId);
        audit($action === 'approve' ? 'APPROVE_PROPOSAL' : 'REJECT_PROPOSAL', 'proposals', $propId);
        header("Location: ?msg=" . urlencode("Proposal $status.")); exit;
    }
}

$statusFilter = $_GET['status'] ?? '';
$proposals = rows(
    "SELECT p.*, ic.cat_name, i.inst_name FROM proposals p
     LEFT JOIN item_categories ic ON p.cat_id = ic.id
     LEFT JOIN institutes i ON p.inst_id = i.id
     WHERE $sf " . ($statusFilter ? "AND p.status = '" . db()->real_escape_string($statusFilter) . "'" : '') . "
     ORDER BY p.id DESC"
);

$categories = rows("SELECT * FROM item_categories ORDER BY cat_name");
$institutes = isSuperAdmin() ? rows("SELECT id, inst_name FROM institutes WHERE is_active=1") : [];
$viewProp   = isset($_GET['view']) ? row("SELECT p.*, ic.cat_name, i.inst_name FROM proposals p LEFT JOIN item_categories ic ON p.cat_id=ic.id LEFT JOIN institutes i ON p.inst_id=i.id WHERE p.id=?", 'i', (int)$_GET['view']) : null;

layout_head('Proposals', 'proposals');
?>

<?php if ($viewProp): // DETAIL VIEW ?>
<div style="max-width:720px">
  <!--<div style="margin-bottom:14px">
    <a href="<?= BASE_URL ?>/modules/proposals/" class="btn btn-ghost btn-sm">← Back to list</a>
  </div> -->
  
  <!-- added pritn button -->
  <div style="margin-bottom:14px;display:flex;gap:8px">
  <a href="<?= BASE_URL ?>/modules/proposals/" class="btn btn-ghost btn-sm">← Back to list</a>
  <?php if ($viewProp['status'] === 'Approved' || $viewProp['status'] === 'Rejected' || $viewProp['status'] === 'Purchased'): ?>
  <a href="<?= BASE_URL ?>/modules/proposals/print.php?id=<?= $viewProp['id'] ?>" class="btn btn-primary btn-sm">🖨️ Print Approval Letter</a>
  <?php endif; ?>
</div> 

  <div class="card">
    <div class="sec-hdr">
      <div>
        <div class="sec-title"><?= esc($viewProp['proposal_no']) ?></div>
        <div class="sec-sub"><?= esc($viewProp['inst_name']) ?> · <?= esc($viewProp['proposal_date']) ?></div>
      </div>
      <?php
        $sb = ['Pending'=>'bg-amber','Approved'=>'bg-green','Rejected'=>'bg-red','Purchased'=>'bg-blue'][$viewProp['status']] ?? 'bg-gray';
      ?>
      <span class="badge <?= $sb ?>" style="font-size:13px;padding:5px 12px"><?= esc($viewProp['status']) ?></span>
    </div>
    <div class="divider"></div>
    <div class="form-grid2" style="gap:16px;margin-bottom:16px">
      <div><div class="muted" style="font-size:11px;margin-bottom:3px">ITEM / PURPOSE</div><div class="fw500"><?= esc($viewProp['item_name']) ?></div></div>
      <div><div class="muted" style="font-size:11px;margin-bottom:3px">CATEGORY</div><div><?= esc($viewProp['cat_name'] ?? '—') ?></div></div>
      <div><div class="muted" style="font-size:11px;margin-bottom:3px">DEPARTMENT</div><div><?= esc($viewProp['department'] ?: '—') ?></div></div>
      <div><div class="muted" style="font-size:11px;margin-bottom:3px">QUANTITY</div><div><?= esc($viewProp['quantity']) ?> <?= esc($viewProp['unit']) ?></div></div>
      <div><div class="muted" style="font-size:11px;margin-bottom:3px">ESTIMATED AMOUNT</div><div class="mono text-green" style="font-size:16px;font-weight:600"><?= money($viewProp['est_amount']) ?></div></div>
      <div><div class="muted" style="font-size:11px;margin-bottom:3px">RAISED BY</div><div><?= esc($viewProp['raised_by']) ?> (<?= esc($viewProp['designation']) ?>)</div></div>
    </div>
    <?php if ($viewProp['justification']): ?>
    <div style="background:var(--bg3);border-radius:var(--r);padding:12px;margin-bottom:16px">
      <div class="muted" style="font-size:11px;margin-bottom:5px">JUSTIFICATION</div>
      <div style="color:var(--tx2);line-height:1.6"><?= nl2br(esc($viewProp['justification'])) ?></div>
    </div>
    <?php endif; ?>

    <?php if ($viewProp['status'] === 'Approved' || $viewProp['status'] === 'Rejected'): ?>
    <div style="background:var(--bg3);border-radius:var(--r);padding:12px">
      <div class="muted" style="font-size:11px;margin-bottom:5px">CHAIRMAN DECISION</div>
      <div class="fw500"><?= esc($viewProp['status']) ?> by <?= esc($viewProp['approved_by']) ?> on <?= esc($viewProp['approved_date']) ?></div>
      <?php if ($viewProp['approval_note']): ?><div class="muted" style="margin-top:5px"><?= esc($viewProp['approval_note']) ?></div><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($viewProp['status'] === 'Pending' && isSuperAdmin()): ?>
    <div class="divider"></div>
    <div class="fw500 mb12">Approval decision</div>
    <form method="POST">
      <input type="hidden" name="prop_id" value="<?= $viewProp['id'] ?>">
      <div class="form-grid2" style="margin-bottom:12px">
        <div class="fg">
          <label>Approved / rejected by</label>
          <input name="approved_by" value="<?= esc($_SESSION['full_name'] ?? '') ?>" placeholder="Name">
        </div>
        <div class="fg span2">
          <label>Remarks / conditions</label>
          <textarea name="approval_note" placeholder="Conditions or reason for rejection…"></textarea>
        </div>
      </div>
      <div style="display:flex;gap:8px">
        <button type="submit" name="action" value="approve" class="btn btn-success">✓ Approve proposal</button>
        <button type="submit" name="action" value="reject"  class="btn btn-danger"  onclick="return confirm('Reject this proposal?')">✗ Reject</button>
      </div>
    </form>
    <?php elseif ($viewProp['status'] === 'Pending'): ?>
    <div class="divider"></div>
    <div style="background:var(--bg3);border-radius:var(--r);padding:12px;color:var(--tx2)">
      ⏳ This proposal is awaiting approval by the Super Admin.
    </div>
    <?php endif; ?>
  </div>
</div>

<?php else: // LIST VIEW ?>

<div class="tabs" id="prop-tabs">
  <button class="tab <?= !$statusFilter?'active':'' ?>" onclick="location.href='?'">All proposals</button>
  <button class="tab <?= $statusFilter==='Pending'?'active':'' ?>"  onclick="location.href='?status=Pending'">Pending</button>
  <button class="tab <?= $statusFilter==='Approved'?'active':'' ?>" onclick="location.href='?status=Approved'">Approved</button>
  <button class="tab <?= $statusFilter==='Rejected'?'active':'' ?>" onclick="location.href='?status=Rejected'">Rejected</button>
  <button class="tab" onclick="showPropTab('add')">+ New proposal</button>
</div>

<div id="prop-list">
  <div class="sec-hdr">
    <div class="sec-title">Proposals <span class="muted" style="font-size:12px;font-weight:400">(<?= count($proposals) ?>)</span></div>
    <button class="btn btn-primary btn-sm" onclick="showPropTab('add')">+ New proposal</button>
  </div>
  <div class="tbl-wrap">
    <table>
      <thead>
        <tr><th>Proposal no.</th><th>Date</th><th>Item / purpose</th><th>Qty</th>
        <th>Est. amount</th><th>Raised by</th><th>Department</th><th>Status</th><th>Action</th></tr>
      </thead>
      <tbody>
      <?php if (!$proposals): ?>
        <tr><td colspan="9"><div class="empty"><div class="empty-icon">📋</div><div class="empty-title">No proposals found</div></div></td></tr>
      <?php else: foreach ($proposals as $p):
        $sb = ['Pending'=>'bg-amber','Approved'=>'bg-green','Rejected'=>'bg-red','Purchased'=>'bg-blue'][$p['status']] ?? 'bg-gray';
      ?>
        <tr>
          <td class="mono"><?= esc($p['proposal_no']) ?></td>
          <td><?= esc($p['proposal_date']) ?></td>
          <td>
            <div class="fw500"><?= esc(mb_strimwidth($p['item_name'],0,50,'…')) ?></div>
            <?php if (isSuperAdmin()): ?><div class="muted" style="font-size:11px"><?= esc($p['inst_name']) ?></div><?php endif; ?>
          </td>
          <td><?= esc($p['quantity']) ?> <?= esc($p['unit']) ?></td>
          <td class="mono"><?= money($p['est_amount']) ?></td>
          <td><?= esc($p['raised_by']) ?><br><span class="muted" style="font-size:11px"><?= esc($p['designation']) ?></span></td>
          <td class="muted"><?= esc($p['department'] ?: '—') ?></td>
          <td><span class="badge <?= $sb ?>"><?= esc($p['status']) ?></span></td>
		  
          <!--<td><a href="?view=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">View</a></td> -->
		  
		  <td style="white-space:nowrap">
			<a href="?view=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">View</a>
			<?php if ($p['status'] === 'Approved'): ?>
			<a href="<?= BASE_URL ?>/modules/quotations/?proposal_id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">Quotations</a>
			<?php endif; ?>
		  </td> 

        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="prop-add" style="display:none;max-width:720px">
  <div class="card">
    <div class="sec-title mb12">Raise new purchase proposal</div>
    <form method="POST">
      <input type="hidden" name="action" value="save_proposal">
      <div class="form-grid">
        <?php if (isSuperAdmin()): ?>
        <div class="fg">
          <label>Institute</label>
          <select name="inst_id">
            <?php foreach ($institutes as $ins): ?><option value="<?= $ins['id'] ?>"><?= esc($ins['inst_name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="fg">
          <label>Date</label>
          <input type="date" name="proposal_date" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="fg">
          <label>Department / section</label>
          <input name="department" placeholder="Computer Lab / Library">
        </div>
        <div class="fg span3">
          <label>Item / purpose *</label>
          <input name="item_name" placeholder="e.g. Desktop computers for new computer lab" required>
        </div>
        <div class="fg">
          <label>Category</label>
          <select name="cat_id">
            <option value="">— Select —</option>
            <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= esc($c['cat_name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg">
          <label>Quantity *</label>
          <input type="number" name="quantity" min="1" value="1" required>
        </div>
        <div class="fg">
          <label>Unit</label>
          <input name="unit" value="Nos" placeholder="Nos / Sets">
        </div>
        <div class="fg">
          <label>Estimated amount (₹) *</label>
          <input type="number" name="est_amount" step="0.01" min="0" required placeholder="500000">
        </div>
        <div class="fg">
          <label>Raised by (name)</label>
          <input name="raised_by" placeholder="Principal / HOD name">
        </div>
        <div class="fg">
          <label>Designation</label>
          <select name="designation">
            <option>Principal</option><option>Vice Principal</option><option>HOD</option>
            <option>Teacher</option><option>Office Superintendent</option>
          </select>
        </div>
        <div class="fg span3">
          <label>Justification / remarks</label>
          <textarea name="justification" placeholder="Reason — how it benefits students, number of beneficiaries…"></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Submit proposal</button>
        <button type="button" class="btn btn-ghost" onclick="showPropTab('list')">Cancel</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
function showPropTab(t) {
  const list = document.getElementById('prop-list');
  const add  = document.getElementById('prop-add');
  if (!list || !add) return;
  list.style.display = t === 'list' ? 'block' : 'none';
  add.style.display  = t === 'add'  ? 'block' : 'none';
}
</script>
<?php layout_foot(); ?>
