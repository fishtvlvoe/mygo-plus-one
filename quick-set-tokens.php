<?php
/**
 * 快速設定 LINE Tokens
 * 直接執行，不需要完整載入 WordPress
 */

// WordPress 資料庫設定
define('DB_NAME', 'local');
define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');

// Tokens
$accessToken = 'Q6/qTk3GqfX5TaMDRu2AsFJovzciraKqfN+PpsxkOqDW9xnsCvOaSixyfzUvmigBymUfONpnO5w7db07kG0RxDxLL8COmnm2fCnZC9F3Ikr8NoOyS19OcXy+bKZb3JFDa5raIK/++3rHVhcYRd28uAdB04t89/1O/w1cDnyilFU=';
$channelSecret = '64eb8d8ced5ff853d5a6b9818d667476';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASSWORD,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== 設定 LINE Tokens ===\n\n";
    
    // 設定 Access Token
    $stmt = $pdo->prepare("
        INSERT INTO wp_options (option_name, option_value, autoload) 
        VALUES ('mygo_line_channel_access_token', :token, 'yes')
        ON DUPLICATE KEY UPDATE option_value = :token
    ");
    $stmt->execute(['token' => $accessToken]);
    echo "✅ Channel Access Token 已設定\n";
    echo "   長度：" . strlen($accessToken) . " 字元\n\n";
    
    // 設定 Channel Secret
    $stmt = $pdo->prepare("
        INSERT INTO wp_options (option_name, option_value, autoload) 
        VALUES ('mygo_line_channel_secret', :secret, 'yes')
        ON DUPLICATE KEY UPDATE option_value = :secret
    ");
    $stmt->execute(['secret' => $channelSecret]);
    echo "✅ Channel Secret 已設定\n";
    echo "   長度：" . strlen($channelSecret) . " 字元\n\n";
    
    // 驗證
    $stmt = $pdo->query("SELECT option_value FROM wp_options WHERE option_name = 'mygo_line_channel_access_token'");
    $savedToken = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT option_value FROM wp_options WHERE option_name = 'mygo_line_channel_secret'");
    $savedSecret = $stmt->fetchColumn();
    
    echo "=== 驗證 ===\n";
    echo "Access Token: " . (strlen($savedToken) > 0 ? "✅ 已儲存 (" . strlen($savedToken) . " 字元)" : "❌ 失敗") . "\n";
    echo "Channel Secret: " . (strlen($savedSecret) > 0 ? "✅ 已儲存 (" . strlen($savedSecret) . " 字元)" : "❌ 失敗") . "\n";
    
    echo "\n完成！現在可以在 LINE 測試了。\n";
    
} catch (PDOException $e) {
    echo "❌ 錯誤：" . $e->getMessage() . "\n";
    exit(1);
}
