<?php
/**
 * 測試 LINE 設定
 * 
 * URL: https://mygo.local/test-line-settings.php
 */

require_once __DIR__ . '/../../../wp-load.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>LINE 設定檢查</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .ok { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 LINE 設定檢查</h1>
    
    <?php
    $channelAccessToken = get_option('mygo_line_channel_access_token', '');
    $channelSecret = get_option('mygo_line_channel_secret', '');
    ?>
    
    <h2>Channel Access Token</h2>
    <div class="status <?php echo !empty($channelAccessToken) ? 'ok' : 'error'; ?>">
        <?php if (!empty($channelAccessToken)): ?>
            ✅ 已設定（長度：<?php echo strlen($channelAccessToken); ?> 字元）<br>
            前 10 字元：<code><?php echo substr($channelAccessToken, 0, 10); ?>...</code>
        <?php else: ?>
            ❌ 未設定
        <?php endif; ?>
    </div>
    
    <h2>Channel Secret</h2>
    <div class="status <?php echo !empty($channelSecret) ? 'ok' : 'warning'; ?>">
        <?php if (!empty($channelSecret)): ?>
            ✅ 已設定（長度：<?php echo strlen($channelSecret); ?> 字元）<br>
            前 10 字元：<code><?php echo substr($channelSecret, 0, 10); ?>...</code>
        <?php else: ?>
            ⚠️ 未設定（開發模式，將跳過簽章驗證）
        <?php endif; ?>
    </div>
    
    <h2>設定方式</h2>
    <p>如果需要設定 Channel Secret，請在 WordPress 後台執行以下指令：</p>
    <pre style="background: #f4f4f4; padding: 10px; border-radius: 5px;">
// 在 WordPress 後台 > 工具 > Site Health > Info > Constants
// 或使用 WP-CLI：
wp option update mygo_line_channel_secret "你的_Channel_Secret"
    </pre>
    
    <h2>LINE Developers Console</h2>
    <p>取得 Channel Secret：</p>
    <ol>
        <li>前往 <a href="https://developers.line.biz/console/" target="_blank">LINE Developers Console</a></li>
        <li>選擇你的 Provider 和 Channel</li>
        <li>在「Basic settings」頁面找到「Channel secret」</li>
        <li>複製並使用上面的指令設定</li>
    </ol>
    
    <hr>
    <p><a href="test-line-bot-status.php">← 回到 Bot 狀態檢查</a></p>
</body>
</html>
