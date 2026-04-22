<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
requireLogin();

$uid  = (int)$_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
$msg  = '';
$err  = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Change password
    if ($action === 'change_password') {
        $current  = trim($_POST['current_password'] ?? '');
        $newPass  = trim($_POST['new_password'] ?? '');
        $confirm  = trim($_POST['confirm_password'] ?? '');

        $user = row("SELECT password FROM users WHERE id=?", 'i', $uid);

        if (!password_verify($current, $user['password'])) {
            $err = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $err = 'New password must be at least 6 characters.';
        } elseif ($newPass !== $confirm) {
            $err = 'New password and confirm password do not match.';
        } else {
            query("UPDATE users SET password=? WHERE id=?", 'si', password_hash($newPass, PASSWORD_DEFAULT), $uid);
            audit('CHANGE_PASSWORD', 'profile', $uid, 'Password changed');
            $msg = 'Password changed successfully!';
        }
    }

    // Upload signature — superadmin only
    if ($action === 'upload_signature' && $role === 'superadmin') {
        if (empty($_FILES['signature']['name'])) {
            $err = 'Please select a file to upload.';
        } else {
            $uploadDir = __DIR__ . '/uploads/signatures/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext     = strtolower(pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowed)) {
                $err = 'Only JPG/PNG files allowed.';
            } elseif ($_FILES['signature']['size'] > 2 * 1024 * 1024) {
                $err = 'File size must be under 2MB.';
            } else {
                // Delete old signature if exists
                $existing = row("SELECT signature FROM users WHERE id=?", 'i', $uid);
                if ($existing['signature'] && file_exists($uploadDir . $existing['signature'])) {
                    unlink($uploadDir . $existing['signature']);
                }
                $filename = 'sig_' . $uid . '_' . time() . '.' . $ext;
                move_uploaded_file($_FILES['signature']['tmp_name'], $uploadDir . $filename);
                query("UPDATE users SET signature=? WHERE id=?", 'si', $filename, $uid);
                audit('UPLOAD_SIGNATURE', 'profile', $uid, 'Signature uploaded');
                $msg = 'Signature uploaded successfully!';
            }
        }
    }
}

// Get current user data
$user = row("SELECT * FROM users WHERE id=?", 'i', $uid);
$sigUrl = ($user['signature'] ?? '') ? BASE_URL . '/uploads/signatures/' . $user['signature'] : null;

layout_head('My Profile', '');
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

<div style="max-width:560px">

  <!-- Profile Info Card -->
  <div class="card" style="margin-bottom:16px">
    <div class="sec-title mb12">👤 My Profile</div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div>
        <div style="font-size:11px;color:var(--tx3)">USERNAME</div>
        <div class="fw500 mono"><?= esc($user['username']) ?></div>
      </div>
      <div>
        <div style="font-size:11px;color:var(--tx3)">FULL NAME</div>
        <div class="fw500"><?= esc($user['full_name']) ?></div>
      </div>
      <div>
        <div style="font-size:11px;color:var(--tx3)">ROLE</div>
        <div><span class="badge bg-purple"><?= esc($user['role']) ?></span></div>
      </div>
      <div>
        <div style="font-size:11px;color:var(--tx3)">EMAIL</div>
        <div class="fw500"><?= esc($user['email'] ?: '—') ?></div>
      </div>
    </div>
  </div>

  <!-- Change Password Card -->
  <div class="card" style="margin-bottom:16px">
    <div class="sec-title mb12">🔒 Change Password</div>
    <form method="POST">
      <input type="hidden" name="action" value="change_password">
      <div class="form-grid2" style="gap:12px">
        <div class="fg">
          <label>Current password *</label>
          <input type="password" name="current_password" required placeholder="Enter current password">
        </div>
        <div class="fg">
          <label>New password *</label>
          <input type="password" name="new_password" required placeholder="Min 6 characters">
        </div>
        <div class="fg">
          <label>Confirm new password *</label>
          <input type="password" name="confirm_password" required placeholder="Repeat new password">
        </div>
      </div>
      <div class="form-actions" style="margin-top:14px">
        <button type="submit" class="btn btn-primary">Update Password</button>
      </div>
    </form>
  </div>

  <!-- Signature Upload — superadmin only -->
  <?php if ($role === 'superadmin'): ?>
  <div class="card">
    <div class="sec-title mb12">✍️ My Signature</div>
    <div class="sec-sub" style="margin-bottom:14px">
      This signature will appear on printed proposal approval letters.
    </div>

    <?php if ($sigUrl): ?>
    <div style="margin-bottom:16px">
      <div style="font-size:11px;color:var(--tx3);margin-bottom:8px">CURRENT SIGNATURE</div>
      <div style="background:white;border-radius:var(--r);padding:16px;display:inline-block;border:1px solid var(--bd)">
        <img src="<?= $sigUrl ?>" alt="Signature" style="max-height:80px;max-width:240px;object-fit:contain">
      </div>
    </div>
    <?php else: ?>
    <div style="background:var(--bg3);border-radius:var(--r);padding:14px;margin-bottom:16px;color:var(--tx3);font-size:13px">
      ⚠️ No signature uploaded yet. Upload one to appear on approval letters.
    </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="upload_signature">
      <div class="fg" style="margin-bottom:14px">
        <label><?= $sigUrl ? 'Replace signature' : 'Upload signature' ?> (JPG/PNG, max 2MB)</label>
        <input type="file" name="signature" accept=".jpg,.jpeg,.png" required
          style="background:var(--bg3);border:1px solid var(--bd2);border-radius:var(--r);padding:8px;color:var(--tx);width:100%">
      </div>
      <div style="background:var(--ams);border-radius:var(--r);padding:10px 14px;margin-bottom:14px;font-size:12px;color:var(--am)">
        💡 Tip: Sign on white paper, take a clear photo or scan, crop tightly and save as JPG/PNG before uploading.
      </div>
      <button type="submit" class="btn btn-primary">Upload Signature</button>
    </form>
  </div>
  <?php endif; ?>

</div>

<?php layout_foot(); ?>