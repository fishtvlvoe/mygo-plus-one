<?php
/**
 * 測試 FluentCart API
 * 
 * 訪問: http://mygo.local/wp-content/plugins/mygo-plus-one/test-fluentcart-api.php
 */

// 載入 WordPress
require_once('../../../wp-load.php');

// 檢查是否為管理員
if (!current_user_can('manage_options')) {
    die('需要管理員權限');
}

header('Content-Type: text/html; charset=utf-8');

echo '<h1>FluentCart API 測試</h1>';
echo '<hr>';

// 測試 1: 取得商品資訊
echo '<h2>測試 1: 取得商品 ID 32</h2>';
$request = new WP_REST_Request('GET', '/fluent-cart/v2/products/32');
$response = rest_do_request($request);

echo '<p>狀態碼: ' . $response->get_status() . '</p>';
echo '<pre>';
print_r($response->get_data());
echo '</pre>';
echo '<hr>';

// 測試 2: 建立訂單（簡化版）
echo '<h2>測試 2: 建立訂單（簡化格式）</h2>';
$orderData = [
    'customer_id' => get_current_user_id(),
    'items' => [
        [
            'product_id' => 32,
            'quantity' => 1,
        ],
    ],
];

echo '<p>請求資料:</p>';
echo '<pre>' . json_encode($orderData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';

$request = new WP_REST_Request('POST', '/fluent-cart/v2/orders');
$request->set_body(json_encode($orderData));
$request->set_header('Content-Type', 'application/json');
$request->set_body_params($orderData);

$response = rest_do_request($request);

echo '<p>狀態碼: ' . $response->get_status() . '</p>';
echo '<p>回應資料:</p>';
echo '<pre>';
print_r($response->get_data());
echo '</pre>';
echo '<hr>';

// 測試 3: 檢查 FluentCart 版本和端點
echo '<h2>測試 3: FluentCart 資訊</h2>';
echo '<p>FluentCart 已安裝: ' . (class_exists('FluentCart\App\App') ? '是' : '否') . '</p>';

if (class_exists('FluentCart\App\App')) {
    $rest_server = rest_get_server();
    $routes = $rest_server->get_routes();
    
    echo '<p>可用的 FluentCart 端點:</p>';
    echo '<ul>';
    foreach ($routes as $route => $handlers) {
        if (strpos($route, 'fluent-cart') !== false) {
            echo '<li>' . esc_html($route) . '</li>';
        }
    }
    echo '</ul>';
}
