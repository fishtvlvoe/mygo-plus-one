<?php
/**
 * LINE Bot 狀態診斷工具
 * 
 * 訪問此頁面：https://mygo.local/wp-content/plugins/mygo-plus-one/test-line-bot-status.php
 */

// 載入 WordPress
require_once __DIR__ . '/../../../wp-load.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>LINE Bot 狀態診斷</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .ok { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        h2 { margin-top: 0; }
        pre { background: #f0f0f0; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🤖 LINE Bot 狀態診斷</h1>
    
    <div class="section">
        <h2>1️⃣ WordPress 設定</h2>
        <?php
        $channelAccessToken = get_option('mygo_line_channel_access_token', '');
        $channelSecret = get_option('mygo_line_channel_secret', '');
        
        if (empty($channelAccessToken)) {
            echo '<p class="error">❌ Channel Access Token 未設定</p>';
        } else {
            echo '<p class="ok">✅ Channel Access Token 已設定（長度：' . strlen($channelAccessToken) . '）</p>';
        }
        
        if (empty($channelSecret)) {
            echo '<p class="warning">⚠️ Channel Secret 未設定（開發模式）</p>';
        } else {
            echo '<p class="ok">✅ Channel Secret 已設定</p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>2️⃣ Webhook URL</h2>
        <?php
        $webhookUrl = rest_url('mygo/v1/line-webhook');
        echo '<p>Webhook URL: <code>' . esc_html($webhookUrl) . '</code></p>';
        
        // 測試 Webhook URL 是否可訪問
        $testResponse = wp_remote_post($webhookUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'events' => [],
            ]),
            'timeout' => 10,
        ]);
        
        if (is_wp_error($testResponse)) {
            echo '<p class="error">❌ Webhook URL 無法訪問：' . esc_html($testResponse->get_error_message()) . '</p>';
        } else {
            $code = wp_remote_retrieve_response_code($testResponse);
            if ($code === 200) {
                echo '<p class="ok">✅ Webhook URL 可正常訪問（HTTP ' . $code . '）</p>';
            } else {
                echo '<p class="warning">⚠️ Webhook URL 回傳 HTTP ' . $code . '</p>';
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>3️⃣ 使用者資料</h2>
        <?php
        $currentUser = wp_get_current_user();
        if ($currentUser->ID) {
            echo '<p>目前登入：' . esc_html($currentUser->user_login) . ' (ID: ' . $currentUser->ID . ')</p>';
            
            $lineUid = get_user_meta($currentUser->ID, '_mygo_line_uid', true);
            $role = get_user_meta($currentUser->ID, '_mygo_role', true);
            
            if (empty($lineUid)) {
                echo '<p class="warning">⚠️ 未綁定 LINE 帳號</p>';
            } else {
                echo '<p class="ok">✅ LINE UID: ' . esc_html($lineUid) . '</p>';
            }
            
            if (empty($role)) {
                echo '<p class="warning">⚠️ 未設定角色</p>';
            } else {
                echo '<p class="ok">✅ 角色：' . esc_html($role) . '</p>';
            }
        } else {
            echo '<p class="warning">⚠️ 未登入</p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>4️⃣ 測試 LINE Bot API</h2>
        <?php
        if (!empty($channelAccessToken)) {
            // 測試 Bot Info API
            $response = wp_remote_get('https://api.line.me/v2/bot/info', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $channelAccessToken,
                ],
                'timeout' => 10,
            ]);
            
            if (is_wp_error($response)) {
                echo '<p class="error">❌ 無法連接 LINE API：' . esc_html($response->get_error_message()) . '</p>';
            } else {
                $code = wp_remote_retrieve_response_code($response);
                $body = wp_remote_retrieve_body($response);
                
                if ($code === 200) {
                    $data = json_decode($body, true);
                    echo '<p class="ok">✅ LINE Bot API 連接成功</p>';
                    echo '<pre>' . esc_html(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
                } else {
                    echo '<p class="error">❌ LINE API 回傳錯誤（HTTP ' . $code . '）</p>';
                    echo '<pre>' . esc_html($body) . '</pre>';
                }
            }
        } else {
            echo '<p class="warning">⚠️ 無法測試（未設定 Access Token）</p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>5️⃣ 最近的 Debug Log</h2>
        <?php
        $logFile = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $mygoLines = array_filter($lines, function($line) {
                return strpos($line, 'MYGO') !== false;
            });
            
            if (empty($mygoLines)) {
                echo '<p class="warning">⚠️ 沒有找到 MYGO 相關的 log</p>';
            } else {
                echo '<p class="ok">✅ 找到 ' . count($mygoLines) . ' 筆 MYGO log</p>';
                echo '<pre>' . esc_html(implode('', array_slice($mygoLines, -20))) . '</pre>';
            }
        } else {
            echo '<p class="warning">⚠️ debug.log 不存在</p>';
        }
        ?>
    </div>
    
    <div class="section">
        <h2>6️⃣ 診斷建議</h2>
        <ol>
            <li>確認 LINE Developers Console 中「Use webhook」開關已開啟</li>
            <li>確認 Webhook URL 設定為：<code><?php echo esc_html(str_replace('https://mygo.local', 'https://unspawned-pseudoregally-esta.ngrok-free.dev', rest_url('mygo/v1/line-webhook'))); ?></code></li>
            <li>確認 ngrok 正在運行：<code>ngrok http https://mygo.local:443 --host-header=mygo.local</code></li>
            <li>確認 LINE Bot 已加為好友</li>
            <li>在 LINE 傳送訊息給 Bot，然後重新整理此頁面查看 log</li>
        </ol>
    </div>
    
    <div class="section">
        <h2>7️⃣ 手動測試 Webhook</h2>
        <p>在終端機執行以下指令測試 Webhook：</p>
        <pre>curl -X POST <?php echo esc_html($webhookUrl); ?> \
  -H "Content-Type: application/json" \
  -d '{
    "events": [{
      "type": "message",
      "replyToken": "test-token",
      "source": {"userId": "<?php echo esc_html($lineUid ?? 'YOUR_LINE_UID'); ?>"},
      "message": {"type": "text", "text": "測試"}
    }]
  }'</pre>
    </div>
</body>
</html>
