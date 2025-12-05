<?php
/**
 * 簡單的 ngrok 測試腳本
 * 
 * 訪問：https://unspawned-pseudoregally-esta.ngrok-free.dev/wp-content/plugins/mygo-plus-one/test-ngrok-simple.php
 */

// 記錄所有請求
$logFile = __DIR__ . '/ngrok-test.log';
$timestamp = date('Y-m-d H:i:s');
$method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
$uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
$headers = getallheaders();
$body = file_get_contents('php://input');

$logEntry = "\n=== {$timestamp} ===\n";
$logEntry .= "Method: {$method}\n";
$logEntry .= "URI: {$uri}\n";
$logEntry .= "Headers:\n" . print_r($headers, true) . "\n";
$logEntry .= "Body:\n{$body}\n";

file_put_contents($logFile, $logEntry, FILE_APPEND);

// 回應
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'ngrok test received',
    'timestamp' => $timestamp,
    'method' => $method,
]);
