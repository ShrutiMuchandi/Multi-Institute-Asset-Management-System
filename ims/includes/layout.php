<?php
// includes/layout.php  — call layout_head() then layout_foot()
function layout_head(string $pageTitle = '', string $activeMenu = ''): void {
    $inst = $_SESSION['inst_name'] ?? 'All Institutes';
    $user = $_SESSION['full_name'] ?? 'User';
    $role = ucfirst(str_replace('_', ' ', $_SESSION['role'] ?? ''));
    $base = BASE_URL;
    $fy   = $_SESSION['fy_label'] ?? '';

    $active = function(string $menu) use ($activeMenu): string {
        return $menu === $activeMenu ? 'active' : '';
    };

    $adminLink = '';
    if (($_SESSION['role'] ?? '') === 'superadmin') {
        $adminLink = "<div class='sb-section'>Admin</div>
  <a href='{$base}/admin/' class='nav-link " . $active('admin') . "'><span class='ni'>⚙</span> Admin Panel</a>";
    }
    // Principal and office_staff cannot access admin
    if (in_array($_SESSION['role'] ?? '', ['principal', 'office_staff'])) {
        $adminLink = '';
    }

    echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
<meta charset=\"UTF-8\">
<meta name=\"viewport\" content=\"width=device-width,initial-scale=1\">
<title>{$pageTitle} — IMS</title>
<link href=\"https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap\" rel=\"stylesheet\">
<link rel=\"stylesheet\" href=\"{$base}/assets/css/app.css\">
</head>
<body>
<nav class=\"sidebar\" id=\"sidebar\">
  <div class=\"sb-logo\">
    <div class=\"sb-mark\">IMS v1.0</div>
    <div class=\"sb-name\">Institute Mgmt</div>
    <div class=\"sb-inst\">{$inst}</div>
  </div>
  <div class=\"sb-section\">Main</div>
  <a href=\"{$base}/index.php\" class=\"nav-link " . $active('dashboard') . "\"><span class=\"ni\">▦</span> Dashboard</a>
  <div class=\"sb-section\">Procurement</div>
  <a href=\"{$base}/modules/proposals/\" class=\"nav-link " . $active('proposals') . "\"><span class=\"ni\">◧</span> Proposals</a>
  <a href=\"{$base}/modules/quotations/\" class=\"nav-link " . $active('quotations') . "\"><span class=\"ni\">▤</span> Quotations</a>
  <a href=\"{$base}/modules/bills/\" class=\"nav-link " . $active('bills') . "\"><span class=\"ni\">▤</span> Bill Register</a>
  <div class=\"sb-section\">Assets</div>
  <a href=\"{$base}/modules/stock/\" class=\"nav-link " . $active('stock') . "\"><span class=\"ni\">⬡</span> Stock Book</a>
  <a href=\"{$base}/modules/movement/\" class=\"nav-link " . $active('movement') . "\"><span class=\"ni\">⇄</span> Movement</a>
  <a href=\"{$base}/modules/scrap/\" class=\"nav-link " . $active('scrap') . "\"><span class=\"ni\">⊗</span> Scrap Register</a>
  {$adminLink}
  <div class=\"sb-footer\">
    <div class=\"sb-user\">{$user}</div>
    <div class=\"sb-role\">{$role}</div>
	
	<!-- Added profile button -->
	<a href=\"{$base}/profile.php\" >⚙ My Profile</a>
	<br>
    <a href=\"{$base}/logout.php\" class=\"logout-btn\">Logout</a>
  </div>
</nav>
<div class=\"main-wrap\">
  <header class=\"topbar\">
    <button class=\"menu-toggle\" onclick=\"document.getElementById('sidebar').classList.toggle('open')\">☰</button>
    <div class=\"tb-title\">{$pageTitle}</div>
    <div class=\"tb-right\">
      <span class=\"fy-badge\">FY {$fy}</span>
    </div>
  </header>
  <main class=\"content\">
";
}

function layout_foot(): void {
    $base = BASE_URL;
    echo "  </main>
</div>
<div id=\"toast\" class=\"toast\"></div>
<script src=\"{$base}/assets/js/app.js\"></script>
</body></html>";
}
