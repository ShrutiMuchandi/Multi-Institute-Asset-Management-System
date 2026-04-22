<?php
// includes/auth.php
require_once __DIR__ . '/../config/db.php';

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireRole(array $roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles, true)) {
        die('<p style="font-family:sans-serif;color:red;padding:2rem">Access denied.</p>');
    }
}

function isSuperAdmin(): bool {
    return ($_SESSION['role'] ?? '') === 'superadmin';
}

function currentInstId(): ?int {
    return $_SESSION['inst_id'] ?? null;
}

function currentFyId(): int {
    static $fyId = null;
    if ($fyId === null) {
        $r = row("SELECT id FROM financial_years WHERE is_active = 1 LIMIT 1");
        $fyId = $r ? (int)$r['id'] : 1;
    }
    return $fyId;
}

// Returns inst_id filter SQL fragment for queries
// superadmin sees all (pass inst_id param), others see only their own
function instFilter(string $alias = ''): string {
    $col = $alias ? "$alias.inst_id" : "inst_id";
    if (isSuperAdmin()) return '1=1';
    return "$col = " . (int)currentInstId();
}

// Generate sequential reference numbers like PROP-SVEC-2026-001
function nextRef(string $prefix, string $table, string $col): string {
    $db   = db();
    $year = date('Y');
    $like = $db->real_escape_string($prefix . '-' . $year . '-%');
    $r    = row("SELECT $col FROM $table WHERE $col LIKE '$like' ORDER BY id DESC LIMIT 1");
    if ($r) {
        $last = (int)substr($r[$col], strrpos($r[$col], '-') + 1);
        $next = $last + 1;
    } else {
        $next = 1;
    }
    return $prefix . '-' . $year . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}

// Generate asset tag:  SVEC-IT-2026-001
function nextAssetTag(int $instId, string $catCode): string {
    $db   = db();
    $inst = row("SELECT inst_code FROM institutes WHERE id = ?", 'i', $instId);
    $code = ($inst['inst_code'] ?? 'INST') . '-' . strtoupper($catCode) . '-' . date('Y');
    $like = $db->real_escape_string($code . '-%');
    $r    = row("SELECT asset_tag FROM stock WHERE asset_tag LIKE '$like' ORDER BY id DESC LIMIT 1");
    $next = $r ? ((int)substr($r['asset_tag'], strrpos($r['asset_tag'], '-') + 1) + 1) : 1;
    return $code . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
}
