<?php
/**
 * 檢查 LINE 設定
 * 
 * 執行方式：php check-line-settings.php
 */

// 載入 WordPress
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
    __DIR__ . '/../../../../wp-load.php',
];

foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

if (!defined('ABSPATH')) {
    die("無法載入 WordPress\n");
}

echo "=== LINE 設定檢查 ===\n\n";

$channelAccessToken = get_option('mygo_line_channel_access_token', '');
$channelSecret = get_option('mygo_line_channel_secret', '');

echo "Channel Access Token:\n";
if (!empty($channelAccessToken)) {
    echo "  ✅ 已設定（長度：" . strlen($channelAccessToken) . " 字元）\n";
    echo "  前 10 字元：" . substr($channelAccessToken, 0, 10) . "...\n";
} else {
    echo "  ❌ 未設定\n";
}

echo "\nChannel Secret:\n";
if (!empty($channelSecret)) {
    echo "  ✅ 已設定（長度：" . strlen($channelSecret) . " 字元）\n";
    echo "  前 10 字元：" . substr($channelSecret, 0, 10) . "...\n";
} else {
    echo "  ⚠️  未設定（開發模式，將跳過簽章驗證）\n";
}

echo "\n=== 設定方式 ===\n";
echo "如果需要設定 Channel Secret，請執行：\n";
echo "wp option update mygo_line_channel_secret \"你的_Channel_Secret\" --allow-root\n";

echo "\n=== Webhook Log ===\n";
$logFile = WP_CONTENT_DIR . '/mygo-webhook.log';
if (file_exists($logFile)) {
    echo "Log 檔案：{$logFile}\n";
    echo "檔案大小：" . filesize($logFile) . " bytes\n";
    echo "\n最後 20 行：\n";
    echo "---\n";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -20);
    echo implode('', $lastLines);
} else {
    echo "Log 檔案不存在：{$logFile}\n";
}
