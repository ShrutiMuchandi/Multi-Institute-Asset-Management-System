<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// admin/index.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
requireRole(['superadmin']);

// Handle POST — create institute + its user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_institute') {
        $typeId   = (int)$_POST['type_id'];
        $code     = strtoupper(trim($_POST['inst_code'] ?? ''));
        $name     = trim($_POST['inst_name'] ?? '');
        $principal= trim($_POST['principal'] ?? '');
        if (!$code || !$name) { header("Location: ?err=" . urlencode('Code and name required.')); exit; }

        $instId = insert(
            "INSERT INTO institutes (type_id,inst_code,inst_name,address,city,district,state,pincode,phone,email,principal)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)",
            'issssssssss',
            $typeId, $code, $name,
            trim($_POST['address'] ?? ''), trim($_POST['city'] ?? ''),
            trim($_POST['district'] ?? ''), trim($_POST['state'] ?? 'Karnataka'),
            trim($_POST['pincode'] ?? ''), trim($_POST['phone'] ?? ''),
            trim($_POST['email'] ?? ''), $principal
        );

        // Auto-create office admin user
        $username = strtolower($code) . '_office';
        $password = password_hash('Institute@123', PASSWORD_DEFAULT);
        insert(
            "INSERT INTO users (inst_id,username,password,full_name,email,role) VALUES (?,?,?,?,?,'office_staff')",
            'issss',
            $instId, $username, $password,
            //$name . ' — Office Staff',
			'Office Staff',
            trim($_POST['email'] ?? '')
        );
        // Also auto-create principal user
        $principalUser = strtolower($code) . '_principal';
        insert(
            "INSERT INTO users (inst_id,username,password,full_name,email,role) VALUES (?,?,?,?,?,'principal')",
            'issss',
            $instId, $principalUser, $password,
            $principal ?: $name . ' — Principal',
            trim($_POST['email'] ?? '')
        );
        audit('CREATE_INSTITUTE', 'admin', $instId, "Institute $name ($code) created");
        header("Location: ?msg=" . urlencode("Institute created! Office login: {$username} / Institute@123 | Principal login: {$principalUser} / Institute@123")); exit;
    }

    if ($action === 'create_user') {
        $instId   = (int)$_POST['inst_id'];
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $role     = $_POST['role'] ?? 'office_staff';
        $pass     = trim($_POST['password'] ?? 'Institute@123');
        if (!$username || !$fullName) { header("Location: ?err=" . urlencode('Username and name required.')); exit; }

        // Check unique
        $exists = row("SELECT id FROM users WHERE username=?", 's', $username);
        if ($exists) { header("Location: ?err=" . urlencode("Username '$username' already exists.")); exit; }

        insert(
            "INSERT INTO users (inst_id,username,password,full_name,email,role) VALUES (?,?,?,?,?,?)",
            'isssss',
            $instId, $username, password_hash($pass, PASSWORD_DEFAULT),
            $fullName, trim($_POST['email'] ?? ''), $role
        );
        audit('CREATE_USER', 'admin', 0, "User $username created");
        header("Location: ?msg=" . urlencode("User $username created. Password: $pass")); exit;
    }

    if ($action === 'toggle_user') {
        $uid  = (int)$_POST['uid'];
        query("UPDATE users SET is_active = 1 - is_active WHERE id=?", 'i', $uid);
        header("Location: ?tab=users&msg=User status updated."); exit;
    }

    if ($action === 'edit_user') {
        $uid      = (int)$_POST['uid'];
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = $_POST['role'] ?? 'office_staff';
        $newPass  = trim($_POST['new_password'] ?? '');

        if (!$fullName || !$username) {
            header("Location: ?tab=users&err=" . urlencode('Full name and username are required.')); exit;
        }

        // Check username not taken by another user
        $exists = row("SELECT id FROM users WHERE username=? AND id != ?", 'si', $username, $uid);
        if ($exists) {
            header("Location: ?tab=users&err=" . urlencode("Username '$username' is already taken.")); exit;
        }

        // Handle signature upload
        $signaturePath = null;
        if (!empty($_FILES['signature']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/signatures/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext      = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));
            $allowed  = ['jpg','jpeg','png'];
            if (!in_array($ext, $allowed)) {
                header("Location: ?tab=users&err=" . urlencode('Only JPG/PNG allowed for signature.')); exit;
            }
            $filename = 'sig_' . $uid . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['signature']['tmp_name'], $uploadDir . $filename);
            $signaturePath = $filename;
        }

        if ($signaturePath) {
            if ($newPass) {
                query("UPDATE users SET username=?, full_name=?, email=?, role=?, password=?, signature=? WHERE id=?",
                    'ssssssi', $username, $fullName, $email, $role,
                    password_hash($newPass, PASSWORD_DEFAULT), $signaturePath, $uid);
            } else {
                query("UPDATE users SET username=?, full_name=?, email=?, role=?, signature=? WHERE id=?",
                    'sssssi', $username, $fullName, $email, $role, $signaturePath, $uid);
            }
        } else {
            if ($newPass) {
                query("UPDATE users SET username=?, full_name=?, email=?, role=?, password=? WHERE id=?",
                    'sssssi', $username, $fullName, $email, $role,
                    password_hash($newPass, PASSWORD_DEFAULT), $uid);
            } else {
                query("UPDATE users SET username=?, full_name=?, email=?, role=? WHERE id=?",
                    'ssssi', $username, $fullName, $email, $role, $uid);
            }
        }
        audit('EDIT_USER', 'admin', $uid, "User $username updated");
        header("Location: ?tab=users&msg=" . urlencode("User updated successfully.")); exit;
    }
}

$institutes   = rows("SELECT i.*, it.type_name FROM institutes i JOIN institute_types it ON i.type_id=it.id WHERE i.id != 0 ORDER BY i.inst_name");
$users        = rows("SELECT u.*, i.inst_name FROM users u LEFT JOIN institutes i ON u.inst_id=i.id ORDER BY i.inst_name, u.username");
$types        = rows("SELECT * FROM institute_types ORDER BY type_name");
$allInstitutes= rows("SELECT id, inst_name FROM institutes ORDER BY inst_name");

$activeTab = $_GET['tab'] ?? 'institutes';
layout_head('Admin Panel', 'admin');
?>

<?php if (!empty($_GET['msg'])): ?>
<div style="background:var(--gns);border:1px solid var(--gn);color:var(--gn);padding:10px 16px;border-radius:var(--r);margin-bottom:16px">
  ✅ <?= esc($_GET['msg']) ?>
</div>
<?php endif; ?>
<?php if (!empty($_GET['err'])): ?>
<div style="background:var(--rds);border:1px solid var(--rd);color:var(--rd);padding:10px 16px;border-radius:var(--r);margin-bottom:16px">
  ❌ <?= esc($_GET['err']) ?>
</div>
<?php endif; ?>
<div class="tabs" id="admin-tabs">
  <button class="tab <?= $activeTab==='institutes'?'active':'' ?>" onclick="location.href='?tab=institutes'">Institutes</button>
  <button class="tab <?= $activeTab==='users'?'active':'' ?>"       onclick="location.href='?tab=users'">Users</button>
  <button class="tab <?= $activeTab==='add_inst'?'active':'' ?>"    onclick="location.href='?tab=add_inst'">+ Add institute</button>
  <button class="tab <?= $activeTab==='add_user'?'active':'' ?>"    onclick="location.href='?tab=add_user'">+ Add user</button>
</div>

<?php if ($activeTab === 'institutes'): ?>
<div class="tbl-wrap">
  <table>
    <thead><tr><th>Code</th><th>Institute name</th><th>Type</th><th>Principal</th><th>City</th><th>Phone</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($institutes as $i): ?>
    <tr>
      <td class="mono"><?= esc($i['inst_code']) ?></td>
      <td class="fw500"><?= esc($i['inst_name']) ?></td>
      <td><span class="badge bg-blue"><?= esc($i['type_name']) ?></span></td>
      <td><?= esc($i['principal'] ?: '—') ?></td>
      <td class="muted"><?= esc($i['city'] ?: '—') ?></td>
      <td class="muted"><?= esc($i['phone'] ?: '—') ?></td>
      <td><span class="badge <?= $i['is_active'] ? 'bg-green' : 'bg-red' ?>"><?= $i['is_active'] ? 'Active' : 'Inactive' ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php elseif ($activeTab === 'users'): ?>
<div class="tbl-wrap">
  <table>
    <thead><tr><th>Username</th><th>Full name</th><th>Institute</th><th>Role</th><th>Last login</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): $rb = ['superadmin'=>'bg-purple','principal'=>'bg-blue','office_staff'=>'bg-gray','teacher'=>'bg-teal','auditor'=>'bg-amber'][$u['role']] ?? 'bg-gray'; ?>
    <tr>
      <td class="mono"><?= esc($u['username']) ?></td>
      <td class="fw500"><?= esc($u['full_name']) ?></td>
      <td><?= esc($u['inst_name'] ?? 'Super Admin') ?></td>
      <td><span class="badge <?= $rb ?>"><?= esc($u['role']) ?></span></td>
      <td class="muted"><?= $u['last_login'] ? esc(date('d M Y H:i', strtotime($u['last_login']))) : '—' ?></td>
      <td><span class="badge <?= $u['is_active'] ? 'bg-green' : 'bg-red' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
      <td style="white-space:nowrap">
        <?php if ($u['username'] !== 'superadmin'): ?>
        <button class="btn btn-ghost btn-sm" style="margin-right:4px"
          onclick="openEditUser(<?= $u['id'] ?>, '<?= esc($u['username']) ?>', '<?= esc($u['full_name']) ?>', '<?= esc($u['email'] ?? '') ?>', '<?= $u['role'] ?>')"
        >Edit</button>
        <form method="POST" style="display:inline">
          <input type="hidden" name="action" value="toggle_user">
          <input type="hidden" name="uid" value="<?= $u['id'] ?>">
          <button type="submit" class="btn btn-ghost btn-sm"><?= $u['is_active'] ? 'Disable' : 'Enable' ?></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php elseif ($activeTab === 'add_inst'): ?>
<div class="card" style="max-width:760px">
  <div class="sec-title mb12">Add new institute</div>
  <div class="sec-sub mb12">Two logins will be auto-created — Office Staff: <b>{code}_office</b> and Principal: <b>{code}_principal</b> — both with password: <b>Institute@123</b></div>
  <form method="POST">
    <input type="hidden" name="action" value="create_institute">
    <div class="form-grid">
      <div class="fg">
        <label>Institute type *</label>
        <select name="type_id" required>
          <?php foreach ($types as $t): ?><option value="<?= $t['id'] ?>"><?= esc($t['type_name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="fg">
        <label>Short code * (used in asset tags)</label>
        <input name="inst_code" placeholder="e.g. SVEC, VMS, ITI1" style="text-transform:uppercase" required maxlength="12">
      </div>
      <div class="fg span2">
        <label>Institute name *</label>
        <input name="inst_name" placeholder="Full official name" required>
      </div>
      <div class="fg"><label>Principal name</label><input name="principal" placeholder="Dr. / Mr. / Mrs."></div>
      <div class="fg"><label>Phone</label><input name="phone" placeholder="0831-XXXXXXX"></div>
      <div class="fg"><label>Email</label><input type="email" name="email" placeholder="principal@…"></div>
      <div class="fg span3"><label>Address</label><textarea name="address" placeholder="Full postal address"></textarea></div>
      <div class="fg"><label>City</label><input name="city" placeholder="Belagavi"></div>
      <div class="fg"><label>District</label><input name="district" placeholder="Belagavi"></div>
      <div class="fg"><label>Pincode</label><input name="pincode" placeholder="590001"></div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Create institute</button>
      <button type="reset" class="btn btn-ghost">Clear</button>
    </div>
  </form>
</div>

<?php elseif ($activeTab === 'add_user'): ?>
<div class="card" style="max-width:560px">
  <div class="sec-title mb12">Add new user</div>
  <form method="POST">
    <input type="hidden" name="action" value="create_user">
    <div class="form-grid2">
      <div class="fg">
        <label>Institute</label>
        <select name="inst_id">
          <option value="0">— Super Admin (no institute) —</option>
          <?php foreach ($allInstitutes as $i): ?><option value="<?= $i['id'] ?>"><?= esc($i['inst_name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="fg">
        <label>Role</label>
        <select name="role">
          <option value="office_staff">Office Staff</option>
          <option value="principal">Principal</option>
          <option value="superadmin">Super Admin</option>
        </select>
      </div>
      <div class="fg"><label>Username *</label><input name="username" placeholder="svec_principal" required></div>
      <div class="fg"><label>Full name *</label><input name="full_name" required></div>
      <div class="fg"><label>Email</label><input type="email" name="email"></div>
      <div class="fg"><label>Initial password</label><input name="password" value="Institute@123"></div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Create user</button>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- Edit User Modal -->
<div id="editUserModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center">
  <div style="background:var(--bg2);border-radius:var(--rl);padding:24px;width:100%;max-width:500px;border:1px solid var(--bd);max-height:90vh;overflow-y:auto">
    <div class="fw500" style="font-size:16px;margin-bottom:16px">✏️ Edit User</div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit_user">
      <input type="hidden" name="uid" id="edit_uid">
      <div class="form-grid2" style="gap:12px">
        <div class="fg">
          <label>Username *</label>
          <input name="username" id="edit_username" required placeholder="e.g. svec_office">
        </div>
        <div class="fg">
          <label>Full name *</label>
          <input name="full_name" id="edit_full_name" required>
        </div>
        <div class="fg">
          <label>Email</label>
          <input type="email" name="email" id="edit_email">
        </div>
        <div class="fg">
          <label>Role</label>
          <select name="role" id="edit_role">
            <option value="office_staff">Office Staff</option>
            <option value="principal">Principal</option>
            <option value="superadmin">Super Admin</option>
          </select>
        </div>
        <div class="fg">
          <label>New password <span class="muted" style="font-size:11px">(leave blank to keep current)</span></label>
          <input type="password" name="new_password" placeholder="Leave blank to keep current">
        </div>
        <div class="fg" id="sig-upload-wrap">
          <label>Signature <span class="muted" style="font-size:11px">(JPG/PNG only — superadmin only)</span></label>
          <input type="file" name="signature" accept=".jpg,.jpeg,.png" id="sig_input" style="background:var(--bg3);border:1px solid var(--bd2);border-radius:var(--r);padding:6px;color:var(--tx);width:100%">
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:16px">
        <button type="submit" class="btn btn-primary">Save changes</button>
        <button type="button" class="btn btn-ghost" onclick="closeEditUser()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditUser(uid, username, fullName, email, role) {
  document.getElementById('edit_uid').value       = uid;
  document.getElementById('edit_username').value  = username;
  document.getElementById('edit_full_name').value = fullName;
  document.getElementById('edit_email').value     = email;
  document.getElementById('edit_role').value      = role;
  // Show signature upload only for superadmin role
  document.getElementById('sig-upload-wrap').style.display = role === 'superadmin' ? 'block' : 'none';
  document.getElementById('editUserModal').style.display = 'flex';
}
function closeEditUser() {
  document.getElementById('editUserModal').style.display = 'none';
}
document.getElementById('edit_role').addEventListener('change', function() {
  document.getElementById('sig-upload-wrap').style.display = this.value === 'superadmin' ? 'block' : 'none';
});
document.getElementById('editUserModal').addEventListener('click', function(e) {
  if (e.target === this) closeEditUser();
});
</script>

<?php layout_foot(); ?>