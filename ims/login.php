<?php
require_once __DIR__ . '/config/db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $user = row(
            "SELECT u.*, i.inst_name, i.inst_code, fy.id AS fy_id, fy.fy_label
             FROM users u
             LEFT JOIN institutes i ON u.inst_id = i.id
             JOIN financial_years fy ON fy.is_active = 1
             WHERE u.username = ? AND u.is_active = 1",
            's', $username
        );
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['inst_id']   = $user['inst_id'];
            $_SESSION['inst_name'] = $user['inst_name'] ?? 'All Institutes';
            $_SESSION['inst_code'] = $user['inst_code'] ?? 'ADM';
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['fy_id']     = $user['fy_id'];
            $_SESSION['fy_label']  = $user['fy_label'];

            query("UPDATE users SET last_login = NOW() WHERE id = ?", 'i', $user['id']);
            audit('LOGIN', 'auth', $user['id'], 'Login from ' . ($_SERVER['REMOTE_ADDR'] ?? ''));

            header('Location: ' . BASE_URL . '/index.php'); exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — Institute Management System</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">IMS — Multi Institute Portal</div>
    <div class="login-title">Sign in</div>
    <div class="login-sub">Each institute uses their own login credentials</div>
    <?php if ($error): ?>
    <div class="login-error"><?= esc($error) ?></div>
    <?php endif; ?>
    <form method="POST" autocomplete="off">
      <div class="fg mb12">
        <label>Username</label>
        <input type="text" name="username" required autofocus
               value="<?= esc($_POST['username'] ?? '') ?>" placeholder="e.g. svec_admin">
      </div>
      <div class="fg mb20">
        <label>Password</label>
        <input type="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Sign in</button>
    </form>
    <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--bd);font-size:11px;color:var(--tx3)">
      Contact your administrator to get login credentials for your institute.
    </div>
  </div>
</div>
</body>
</html>
