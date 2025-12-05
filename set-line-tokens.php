<?php
/**
 * 設定 LINE Channel Access Token 和 Secret
 * 
 * 使用方式：
 * php set-line-tokens.php "YOUR_ACCESS_TOKEN" "YOUR_CHANNEL_SECRET"
 */

if ($argc < 2) {
    echo "使用方式：php set-line-tokens.php \"YOUR_ACCESS_TOKEN\" [\"YOUR_CHANNEL_SECRET\"]\n";
    exit(1);
}

// 載入 WordPress
$wp_load_paths = [
    __DIR__ . '/../../../wp-load.php',
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

$accessToken = $argv[1];
$channelSecret = $argv[2] ?? '';

echo "=== 設定 LINE Tokens ===\n\n";

// 設定 Access Token
update_option('mygo_line_channel_access_token', $accessToken);
echo "✅ Channel Access Token 已設定\n";
echo "   長度：" . strlen($accessToken) . " 字元\n";
echo "   前 10 字元：" . substr($accessToken, 0, 10) . "...\n\n";

// 設定 Channel Secret（如果有提供）
if (!empty($channelSecret)) {
    update_option('mygo_line_channel_secret', $channelSecret);
    echo "✅ Channel Secret 已設定\n";
    echo "   長度：" . strlen($channelSecret) . " 字元\n";
    echo "   前 10 字元：" . substr($channelSecret, 0, 10) . "...\n\n";
}

echo "=== 驗證設定 ===\n";
$savedToken = get_option('mygo_line_channel_access_token', '');
$savedSecret = get_option('mygo_line_channel_secret', '');

echo "Access Token: " . (strlen($savedToken) > 0 ? "✅ 已設定 (" . strlen($savedToken) . " 字元)" : "❌ 未設定") . "\n";
echo "Channel Secret: " . (strlen($savedSecret) > 0 ? "✅ 已設定 (" . strlen($savedSecret) . " 字元)" : "⚠️  未設定") . "\n";

echo "\n完成！\n";
