<?php
require_once __DIR__ . '/config/db.php';
audit('LOGOUT', 'auth', (int)($_SESSION['user_id'] ?? 0));
session_destroy();
header('Location: ' . BASE_URL . '/login.php');
exit;
