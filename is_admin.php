<?php
session_start();
header('Content-Type: application/json');

// Debug logs détaillés
$logFile = __DIR__ . '/debug_detailed.log';
file_put_contents($logFile, "\n=== IS_ADMIN.PHP START ===\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' METHOD: ' . ($_SERVER['REQUEST_METHOD'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' URI: ' . ($_SERVER['REQUEST_URI'] ?? '-') . "\n", FILE_APPEND);
file_put_contents($logFile, date('c') . ' Session is_admin: ' . (isset($_SESSION['is_admin']) ? ($_SESSION['is_admin'] ? 'true' : 'false') : 'not set') . "\n", FILE_APPEND);

echo json_encode(['is_admin' => isset($_SESSION['is_admin']) && $_SESSION['is_admin']]);
file_put_contents($logFile, date('c') . ' Response sent' . "\n=== IS_ADMIN.PHP END ===\n", FILE_APPEND);
