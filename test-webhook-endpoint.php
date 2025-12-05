<?php
/**
 * 測試 Webhook Endpoint
 * 訪問: https://your-ngrok-url.ngrok-free.dev/wp-content/plugins/mygo-plus-one/test-webhook-endpoint.php
 */

// 載入 WordPress
require_once('../../../wp-load.php');

echo "<h1>Webhook Endpoint 測試</h1>";

// 1. 檢查 REST API 是否可用
echo "<h2>1. REST API 狀態</h2>";
$rest_url = rest_url();
echo "<p>REST URL: <code>{$rest_url}</code></p>";

// 2. 檢查 mygo/v1 namespace
echo "<h2>2. MYGO REST Routes</h2>";
$routes = rest_get_server()->get_routes();
$mygo_routes = array_filter($routes, function($route) {
    return strpos($route, '/mygo/v1') === 0;
}, ARRAY_FILTER_USE_KEY);

if (empty($mygo_routes)) {
    echo "<p style='color: red;'>❌ 沒有找到 /mygo/v1 路由</p>";
} else {
    echo "<p style='color: green;'>✓ 找到 " . count($mygo_routes) . " 個 MYGO 路由：</p>";
    echo "<ul>";
    foreach ($mygo_routes as $route => $handlers) {
        $methods = array_keys($handlers);
        echo "<li><code>{$route}</code> - 方法: " . implode(', ', $methods) . "</li>";
    }
    echo "</ul>";
}

// 3. 測試 Webhook URL
echo "<h2>3. 測試 Webhook URL</h2>";
$webhook_url = rest_url('mygo/v1/line-webhook');
echo "<p>Webhook URL: <code>{$webhook_url}</code></p>";

// 4. 模擬 POST 請求
echo "<h2>4. 模擬 LINE Webhook POST 請求</h2>";

$test_payload = [
    'events' => [
        [
            'type' => 'message',
            'replyToken' => 'test_token_' . time(),
            'source' => [
                'userId' => 'U_test_user_123',
                'type' => 'user',
            ],
            'message' => [
                'type' => 'text',
                'id' => 'test_msg_' . time(),
                'text' => '測試訊息',
            ],
        ],
    ],
];

$response = wp_remote_post($webhook_url, [
    'headers' => [
        'Content-Type' => 'application/json',
    ],
    'body' => json_encode($test_payload),
    'timeout' => 10,
]);

if (is_wp_error($response)) {
    echo "<p style='color: red;'>❌ 請求失敗: " . $response->get_error_message() . "</p>";
} else {
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    
    if ($code === 200) {
        echo "<p style='color: green;'>✓ 請求成功 (HTTP {$code})</p>";
    } else {
        echo "<p style='color: red;'>❌ 請求失敗 (HTTP {$code})</p>";
    }
    
    echo "<p>回應內容:</p>";
    echo "<pre>" . htmlspecialchars($body) . "</pre>";
}

// 5. 檢查 Permalink 設定
echo "<h2>5. Permalink 設定</h2>";
$permalink_structure = get_option('permalink_structure');
if (empty($permalink_structure)) {
    echo "<p style='color: red;'>❌ Permalink 使用預設格式（Plain），REST API 可能無法正常運作</p>";
    echo "<p>請到「設定 → 永久連結」選擇「文章名稱」或其他格式</p>";
} else {
    echo "<p style='color: green;'>✓ Permalink 結構: <code>{$permalink_structure}</code></p>";
}

// 6. 提供 ngrok 設定建議
echo "<h2>6. ngrok 設定檢查</h2>";
echo "<p>請確認以下設定：</p>";
echo "<ol>";
echo "<li>ngrok 是否正在運行？執行: <code>ngrok http 80</code></li>";
echo "<li>LINE Webhook URL 是否正確？應該是: <code>https://your-domain.ngrok-free.dev/wp-json/mygo/v1/line-webhook</code></li>";
echo "<li>ngrok 是否有設定 <code>--host-header=rewrite</code>？</li>";
echo "</ol>";

echo "<h2>7. 手動測試連結</h2>";
echo "<p>使用 curl 測試:</p>";
echo "<pre>curl -X POST {$webhook_url} \\
  -H 'Content-Type: application/json' \\
  -d '{\"events\":[]}'</pre>";
