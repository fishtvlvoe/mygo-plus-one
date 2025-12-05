<?php
/**
 * 設定 LINE Tokens
 * 
 * URL: https://mygo.local/wp-content/plugins/mygo-plus-one/setup-line-tokens.php
 */

// 載入 WordPress
require_once __DIR__ . '/../../../wp-load.php';

// 檢查是否為管理員
if (!current_user_can('manage_options')) {
    wp_die('您沒有權限訪問此頁面');
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $accessToken = sanitize_text_field($_POST['access_token'] ?? '');
    $channelSecret = sanitize_text_field($_POST['channel_secret'] ?? '');
    
    if (!empty($accessToken)) {
        update_option('mygo_line_channel_access_token', $accessToken);
        $success = true;
    }
    
    if (!empty($channelSecret)) {
        update_option('mygo_line_channel_secret', $channelSecret);
    }
}

$currentToken = get_option('mygo_line_channel_access_token', '');
$currentSecret = get_option('mygo_line_channel_secret', '');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>設定 LINE Tokens</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"] { width: 100%; padding: 8px; font-size: 14px; }
        button { background: #007AFF; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .ok { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .success { background: #d1ecf1; color: #0c5460; padding: 15px; margin: 20px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔧 設定 LINE Tokens</h1>
    
    <?php if (isset($success)): ?>
        <div class="success">✅ 設定已儲存！</div>
    <?php endif; ?>
    
    <h2>目前狀態</h2>
    <div class="status <?php echo !empty($currentToken) ? 'ok' : 'error'; ?>">
        <strong>Channel Access Token:</strong>
        <?php if (!empty($currentToken)): ?>
            已設定（<?php echo strlen($currentToken); ?> 字元）
        <?php else: ?>
            未設定
        <?php endif; ?>
    </div>
    
    <div class="status <?php echo !empty($currentSecret) ? 'ok' : 'error'; ?>">
        <strong>Channel Secret:</strong>
        <?php if (!empty($currentSecret)): ?>
            已設定（<?php echo strlen($currentSecret); ?> 字元）
        <?php else: ?>
            未設定
        <?php endif; ?>
    </div>
    
    <h2>更新設定</h2>
    <form method="POST">
        <div class="form-group">
            <label>Channel Access Token:</label>
            <input type="text" name="access_token" value="<?php echo esc_attr($currentToken); ?>" placeholder="貼上你的 Channel Access Token">
        </div>
        
        <div class="form-group">
            <label>Channel Secret:</label>
            <input type="text" name="channel_secret" value="<?php echo esc_attr($currentSecret); ?>" placeholder="貼上你的 Channel Secret">
        </div>
        
        <button type="submit" name="submit">儲存設定</button>
    </form>
    
    <hr style="margin: 40px 0;">
    
    <h2>📝 如何取得這些值</h2>
    <ol>
        <li>前往 <a href="https://developers.line.biz/console/" target="_blank">LINE Developers Console</a></li>
        <li>選擇你的 Messaging API Channel</li>
        <li>在「Messaging API」頁籤：
            <ul>
                <li><strong>Channel access token</strong>：如果沒有，點擊「Issue」產生</li>
                <li><strong>Channel secret</strong>：在「Basic settings」頁籤</li>
            </ul>
        </li>
    </ol>
</body>
</html>
