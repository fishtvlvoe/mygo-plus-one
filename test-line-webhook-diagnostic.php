<?php
/**
 * LINE Webhook 診斷工具
 * 
 * 訪問: https://mygo.local/wp-content/plugins/mygo-plus-one/test-line-webhook-diagnostic.php
 */

// 載入 WordPress
require_once __DIR__ . '/../../../wp-load.php';

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>LINE Webhook 診斷</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #00B900; padding-bottom: 10px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #00B900; }
        .success { color: #00B900; font-weight: bold; }
        .error { color: #ff0000; font-weight: bold; }
        .warning { color: #ff9900; font-weight: bold; }
        .info { color: #0066cc; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table td { padding: 8px; border-bottom: 1px solid #ddd; }
        table td:first-child { font-weight: bold; width: 200px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 LINE Webhook 診斷工具</h1>
        
        <?php
        // 1. 檢查 LINE Channel Access Token
        echo '<div class="section">';
        echo '<h2>1️⃣ LINE Channel Access Token</h2>';
        $accessToken = get_option('mygo_line_channel_access_token', '');
        if (empty($accessToken)) {
            echo '<p class="error">❌ 未設定 Channel Access Token</p>';
            echo '<p>請到 WordPress 後台「MYGO +1 → 設定」填入 LINE Channel Access Token</p>';
        } else {
            $tokenLength = strlen($accessToken);
            $tokenPreview = substr($accessToken, 0, 10) . '...' . substr($accessToken, -10);
            echo '<p class="success">✅ 已設定 Channel Access Token</p>';
            echo '<table>';
            echo '<tr><td>Token 長度</td><td>' . $tokenLength . ' 字元</td></tr>';
            echo '<tr><td>Token 預覽</td><td><code>' . esc_html($tokenPreview) . '</code></td></tr>';
            echo '</table>';
        }
        echo '</div>';
        
        // 2. 檢查 LINE Channel Secret
        echo '<div class="section">';
        echo '<h2>2️⃣ LINE Channel Secret</h2>';
        $channelSecret = get_option('mygo_line_channel_secret', '');
        if (empty($channelSecret)) {
            echo '<p class="warning">⚠️ 未設定 Channel Secret（開發模式，跳過簽章驗證）</p>';
        } else {
            $secretLength = strlen($channelSecret);
            $secretPreview = substr($channelSecret, 0, 5) . '...' . substr($channelSecret, -5);
            echo '<p class="success">✅ 已設定 Channel Secret</p>';
            echo '<table>';
            echo '<tr><td>Secret 長度</td><td>' . $secretLength . ' 字元</td></tr>';
            echo '<tr><td>Secret 預覽</td><td><code>' . esc_html($secretPreview) . '</code></td></tr>';
            echo '</table>';
        }
        echo '</div>';
        
        // 3. 檢查已綁定的 LINE 使用者
        echo '<div class="section">';
        echo '<h2>3️⃣ 已綁定的 LINE 使用者</h2>';
        global $wpdb;
        $lineUsers = $wpdb->get_results(
            "SELECT u.ID, u.user_login, u.user_email, 
                    m1.meta_value as line_uid,
                    m2.meta_value as line_name,
                    m3.meta_value as mygo_role
             FROM {$wpdb->users} u
             LEFT JOIN {$wpdb->usermeta} m1 ON u.ID = m1.user_id AND m1.meta_key = '_mygo_line_uid'
             LEFT JOIN {$wpdb->usermeta} m2 ON u.ID = m2.user_id AND m2.meta_key = '_mygo_line_name'
             LEFT JOIN {$wpdb->usermeta} m3 ON u.ID = m3.user_id AND m3.meta_key = '_mygo_role'
             WHERE m1.meta_value IS NOT NULL AND m1.meta_value != ''"
        );
        
        if (empty($lineUsers)) {
            echo '<p class="error">❌ 沒有已綁定的 LINE 使用者</p>';
            echo '<p>請先使用 LINE 登入網站完成帳號綁定</p>';
        } else {
            echo '<p class="success">✅ 找到 ' . count($lineUsers) . ' 個已綁定的使用者</p>';
            echo '<table>';
            echo '<tr><td><strong>WordPress ID</strong></td><td><strong>使用者名稱</strong></td><td><strong>LINE UID</strong></td><td><strong>LINE 名稱</strong></td><td><strong>角色</strong></td></tr>';
            foreach ($lineUsers as $user) {
                $roleClass = in_array($user->mygo_role, ['seller', 'admin']) ? 'success' : 'warning';
                $roleText = $user->mygo_role ?: 'buyer';
                echo '<tr>';
                echo '<td>' . $user->ID . '</td>';
                echo '<td>' . esc_html($user->user_login) . '</td>';
                echo '<td><code>' . esc_html($user->line_uid) . '</code></td>';
                echo '<td>' . esc_html($user->line_name) . '</td>';
                echo '<td class="' . $roleClass . '">' . esc_html($roleText) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            // 檢查是否有 seller
            $hasSeller = false;
            foreach ($lineUsers as $user) {
                if (in_array($user->mygo_role, ['seller', 'admin'])) {
                    $hasSeller = true;
                    break;
                }
            }
            
            if (!$hasSeller) {
                echo '<p class="warning">⚠️ 沒有 seller 或 admin 角色的使用者</p>';
                echo '<p>請到 WordPress 後台「MYGO +1 → 使用者管理」將使用者角色改為「seller」</p>';
            }
        }
        echo '</div>';
        
        // 4. 檢查 Webhook URL
        echo '<div class="section">';
        echo '<h2>4️⃣ Webhook URL</h2>';
        $webhookUrl = home_url('/wp-json/mygo/v1/line-webhook');
        echo '<table>';
        echo '<tr><td>Webhook URL</td><td><code>' . esc_html($webhookUrl) . '</code></td></tr>';
        echo '<tr><td>ngrok URL</td><td><code>https://unspawned-pseudoregally-esta.ngrok-free.dev/wp-json/mygo/v1/line-webhook</code></td></tr>';
        echo '</table>';
        echo '<p class="info">💡 請確認 LINE Developers Console 的 Webhook URL 設定為 ngrok URL</p>';
        echo '</div>';
        
        // 5. 測試 Webhook Endpoint
        echo '<div class="section">';
        echo '<h2>5️⃣ 測試 Webhook Endpoint</h2>';
        echo '<p>執行測試請求...</p>';
        
        // 模擬 LINE Webhook 請求
        $testPayload = [
            'events' => [
                [
                    'type' => 'message',
                    'replyToken' => 'test_' . time(),
                    'source' => [
                        'userId' => !empty($lineUsers) ? $lineUsers[0]->line_uid : 'U_test_user'
                    ],
                    'message' => [
                        'type' => 'text',
                        'id' => 'msg_' . time(),
                        'text' => '測試訊息'
                    ]
                ]
            ]
        ];
        
        $testPayloadJson = json_encode($testPayload);
        
        // 計算簽章
        $signature = '';
        if (!empty($channelSecret)) {
            $signature = base64_encode(hash_hmac('sha256', $testPayloadJson, $channelSecret, true));
        }
        
        echo '<pre>';
        echo 'Test Payload:' . "\n";
        echo json_encode($testPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        echo '</pre>';
        
        if (!empty($signature)) {
            echo '<p class="info">📝 計算的簽章: <code>' . esc_html($signature) . '</code></p>';
        }
        
        echo '<p class="warning">⚠️ 實際測試需要從 LINE 傳送訊息</p>';
        echo '</div>';
        
        // 6. 檢查 debug.log
        echo '<div class="section">';
        echo '<h2>6️⃣ 最近的 Debug Log</h2>';
        $logFile = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($logFile)) {
            $logContent = file_get_contents($logFile);
            $logLines = explode("\n", $logContent);
            $mygoLines = array_filter($logLines, function($line) {
                return strpos($line, 'MYGO') !== false;
            });
            
            if (empty($mygoLines)) {
                echo '<p class="warning">⚠️ 沒有找到 MYGO 相關的 log</p>';
                echo '<p>這表示 Webhook 可能沒有被觸發</p>';
            } else {
                echo '<p class="success">✅ 找到 ' . count($mygoLines) . ' 條 MYGO log</p>';
                echo '<pre>';
                echo esc_html(implode("\n", array_slice($mygoLines, -20)));
                echo '</pre>';
            }
        } else {
            echo '<p class="error">❌ debug.log 檔案不存在</p>';
            echo '<p>請在 wp-config.php 中啟用 debug 模式：</p>';
            echo '<pre>define(\'WP_DEBUG\', true);\ndefine(\'WP_DEBUG_LOG\', true);</pre>';
        }
        echo '</div>';
        
        // 7. 下一步建議
        echo '<div class="section">';
        echo '<h2>7️⃣ 下一步建議</h2>';
        echo '<ol>';
        
        if (empty($accessToken)) {
            echo '<li class="error">設定 LINE Channel Access Token</li>';
        }
        
        if (empty($lineUsers)) {
            echo '<li class="error">使用 LINE 登入網站完成帳號綁定</li>';
        } else {
            $hasSeller = false;
            foreach ($lineUsers as $user) {
                if (in_array($user->mygo_role, ['seller', 'admin'])) {
                    $hasSeller = true;
                    break;
                }
            }
            if (!$hasSeller) {
                echo '<li class="warning">將使用者角色改為 seller</li>';
            }
        }
        
        echo '<li class="info">確認 ngrok 正在運行</li>';
        echo '<li class="info">確認 LINE Developers Console Webhook URL 設定正確</li>';
        echo '<li class="info">在 LINE 傳送訊息測試</li>';
        echo '<li class="info">檢查 debug.log 是否出現 MYGO Webhook 訊息</li>';
        echo '</ol>';
        echo '</div>';
        ?>
        
        <div class="section">
            <h2>🔄 重新整理</h2>
            <p><a href="?" style="display: inline-block; padding: 10px 20px; background: #00B900; color: white; text-decoration: none; border-radius: 4px;">重新檢查</a></p>
        </div>
    </div>
</body>
</html>
