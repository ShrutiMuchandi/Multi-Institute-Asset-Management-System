cat > /home/claude/ims_new/ims/modules/movement/locations.php << 'PHPEOF'
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/layout.php';
requireLogin();
requireRole(['office_staff', 'principal', 'superadmin']);

$iid  = currentInstId();
$role = $_SESSION['role'] ?? '';
$msg  = '';
$err  = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add location
    if ($action === 'add_location') {
        $locName = trim($_POST['location_name'] ?? '');
        $instId  = isSuperAdmin() ? (int)$_POST['inst_id'] : $iid;

        if (!$locName) {
            $err = 'Location name is required.';
        } else {
            // Check duplicate
            $exists = row("SELECT id FROM locations WHERE inst_id=? AND location_name=?", 'is', $instId, $locName);
            if ($exists) {
                $err = "Location '$locName' already exists!";
            } else {
                insert("INSERT INTO locations (inst_id, location_name) VALUES (?,?)", 'is', $instId, $locName);
                audit('ADD_LOCATION', 'locations', $instId, "Location: $locName");
                $msg = "Location '$locName' added successfully!";
            }
        }
    }

    // Delete location
    if ($action === 'delete_location') {
        $locId = (int)$_POST['loc_id'];
        // Check if location is used in movements
        $used = row("SELECT id FROM movements WHERE from_loc=(SELECT location_name FROM locations WHERE id=?) LIMIT 1", 'i', $locId);
        if ($used) {
            $err = 'Cannot delete — this location is already used in movement records!';
        } else {
            query("DELETE FROM locations WHERE id=?", 'i', $locId);
            $msg = 'Location deleted successfully!';
        }
    }
}

// Load locations
$sf = instFilter('l');
$locations = rows(
    "SELECT l.*, i.inst_name FROM locations l
     LEFT JOIN institutes i ON l.inst_id = i.id
     WHERE $sf ORDER BY l.inst_id, l.location_name ASC"
);

$institutes = isSuperAdmin() ? rows("SELECT id, inst_name FROM institutes WHERE is_active=1 ORDER BY inst_name") : [];

layout_head('Manage Locations', 'movement');
?>

<?php if ($msg): ?>
<div style="background:var(--gns);border:1px solid var(--gn);color:var(--gn);padding:10px 16px;border-radius:var(--r);margin-bottom:16px">
  ✅ <?= esc($msg) ?>
</div>
<?php endif; ?>
<?php if ($err): ?>
<div style="background:var(--rds);border:1px solid var(--rd);color:var(--rd);padding:10px 16px;border-radius:var(--r);margin-bottom:16px">
  ❌ <?= esc($err) ?>
</div>
<?php endif; ?>

<div class="sec-hdr" style="margin-bottom:16px">
  <div>
    <div class="sec-title">📍 Manage Locations</div>
    <div class="sec-sub">Add institute locations for movement From field</div>
  </div>
  <a href="<?= BASE_URL ?>/modules/movement/" class="btn btn-ghost btn-sm">← Back to Movement</a>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;max-width:900px">

  <!-- Add Location Form -->
  <div class="card">
    <div class="sec-title mb12">+ Add Location</div>
    <form method="POST">
      <input type="hidden" name="action" value="add_location">
      <?php if (isSuperAdmin()): ?>
      <div class="fg" style="margin-bottom:12px">
        <label>Institute *</label>
        <select name="inst_id" required>
          <option value="">— Select —</option>
          <?php foreach ($institutes as $ins): ?>
          <option value="<?= $ins['id'] ?>"><?= esc($ins['inst_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="fg" style="margin-bottom:12px">
        <label>Location name *</label>
        <input name="location_name" placeholder="e.g. Computer Lab 1, Library, Store Room" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Add Location</button>
    </form>

    <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--bd);font-size:12px;color:var(--tx3)">
      💡 Examples:<br>
      Computer Lab 1, Computer Lab 2<br>
      Library, Store Room, Principal Office<br>
      Physics Lab, Chemistry Lab<br>
      Staff Room, Seminar Hall
    </div>
  </div>

  <!-- Locations List -->
  <div class="card">
    <div class="sec-title mb12">All Locations <span class="muted" style="font-size:12px;font-weight:400">(<?= count($locations) ?>)</span></div>
    <?php if ($locations): ?>
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Location name</th>
            <?php if (isSuperAdmin()): ?><th>Institute</th><?php endif; ?>
            <th>Added on</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($locations as $i => $l): ?>
          <tr>
            <td class="muted"><?= $i + 1 ?></td>
            <td class="fw500">📍 <?= esc($l['location_name']) ?></td>
            <?php if (isSuperAdmin()): ?>
            <td class="muted"><?= esc($l['inst_name'] ?? '—') ?></td>
            <?php endif; ?>
            <td class="muted"><?= date('d M Y', strtotime($l['created_at'])) ?></td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="delete_location">
                <input type="hidden" name="loc_id" value="<?= $l['id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"
                  onclick="return confirm('Delete <?= esc($l['location_name']) ?>?')">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="empty">
      <div class="empty-icon">📍</div>
      <div class="empty-title">No locations added yet</div>
      <div class="empty-sub">Add locations using the form on the left</div>
    </div>
    <?php endif; ?>
  </div>

</div>

<?php layout_foot(); ?>
PHPEOF
echo "Done!"
Output

Done!
Done
Now the movement print slip:

Create new file at:

C:\xampp\htdocs\ims\modules\movement\print.php

Create movement print.php
Create movement print.php
Both files done! Now the changes in modules/movement/index.php — 3 changes:

Change 1 — Add locations query at the top after $sf = instFilter('m');:

php
$locations = rows(
    "SELECT location_name FROM locations WHERE " . instFilter() . " ORDER BY location_name ASC"
);
Change 2 — Add "📍 Manage Locations" button in the tabs section. Find:

php
<button class="tab <?= $activeTab==='add'?'active':''  ?>" onclick="showMoveTab('add')">+ Log movement</button>
Add after it:

php
<a href="<?= BASE_URL ?>/modules/movement/locations.php" class="btn btn-ghost btn-sm" style="margin-left:auto">📍 Manage Locations</a>
Change 3 — Change From location from text input to dropdown. Find:

php
<div class="fg"><label>From location *</label><input name="from_loc" placeholder="Store / Lab 1" required></div>
Change to:

php
<div class="fg">
  <label>From location *</label>
  <select name="from_loc" required>
    <option value="">— Select location —</option>
    <?php foreach ($locations as $loc): ?>
    <option value="<?= esc($loc['location_name']) ?>"><?= esc($loc['location_name']) ?></option>
    <?php endforeach; ?>
  </select>
</div>
Change 4 — Add Print Slip button in movement list. Find:

php
<?php if ($m['status'] === 'Issued' && $_SESSION['role'] !== 'principal'): ?>
<a href="?return=<?= $m['id'] ?>" class="btn btn-success btn-sm"
   onclick="return confirm('Mark as returned?')">Return</a>
<?php endif; ?>
Add after it:

php
<a href="<?= BASE_URL ?>/modules/movement/print.php?id=<?= $m['id'] ?>&type=full" target="_blank" class="btn btn-ghost btn-sm">🖨️</a>
Also run this SQL in phpMyAdmin:

sql
CREATE TABLE IF NOT EXISTS `locations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `inst_id` INT UNSIGNED NOT NULL,
  `location_name` VARCHAR(120) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`inst_id`) REFERENCES `institutes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
That's it bro! 2 new files + 4 small changes + 1 SQL! 😊



