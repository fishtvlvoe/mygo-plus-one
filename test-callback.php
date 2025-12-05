<?php
/**
 * 測試 Callback 路由
 * 
 * 訪問: https://your-domain.com/wp-content/plugins/mygo-plus-one/test-callback.php
 */

// 載入 WordPress
require_once('../../../wp-load.php');

echo '<h1>Callback 路由測試</h1>';
echo '<hr>';

// 測試 1: 檢查 rewrite rules
echo '<h2>測試 1: Rewrite Rules</h2>';
$rules = get_option('rewrite_rules');
$found = false;
foreach ($rules as $pattern => $rewrite) {
    if (strpos($pattern, 'mygo-line') !== false) {
        echo '<p>✓ 找到規則: <code>' . esc_html($pattern) . '</code> => <code>' . esc_html($rewrite) . '</code></p>';
        $found = true;
    }
}
if (!$found) {
    echo '<p style="color: red;">✗ 沒有找到 mygo-line 相關的 rewrite rules</p>';
    echo '<p><a href="' . admin_url('options-permalink.php') . '">點此前往固定網址設定頁面刷新規則</a></p>';
}
echo '<hr>';

// 測試 2: 檢查 query vars
echo '<h2>測試 2: Query Vars</h2>';
global $wp;
if (in_array('mygo_line_callback', $wp->public_query_vars)) {
    echo '<p>✓ mygo_line_callback 已註冊為 query var</p>';
} else {
    echo '<p style="color: red;">✗ mygo_line_callback 未註冊為 query var</p>';
}
echo '<hr>';

// 測試 3: 模擬訪問
echo '<h2>測試 3: 模擬訪問</h2>';
echo '<p>請訪問以下網址測試：</p>';
echo '<ul>';
echo '<li><a href="' . home_url('/mygo-line-login/') . '" target="_blank">' . home_url('/mygo-line-login/') . '</a></li>';
echo '<li><a href="' . home_url('/mygo-line-callback/?code=test&state=test') . '" target="_blank">' . home_url('/mygo-line-callback/?code=test&state=test') . '</a></li>';
echo '</ul>';
echo '<hr>';

// 測試 4: 檢查 LINE 設定
echo '<h2>測試 4: LINE 設定</h2>';
$clientId = get_option('mygo_line_login_channel_id', '');
$clientSecret = get_option('mygo_line_login_channel_secret', '');
echo '<p>Channel ID: ' . ($clientId ? '✓ 已設定 (' . esc_html($clientId) . ')' : '<span style="color: red;">✗ 未設定</span>') . '</p>';
echo '<p>Channel Secret: ' . ($clientSecret ? '✓ 已設定' : '<span style="color: red;">✗ 未設定</span>') . '</p>';
echo '<p>Callback URL: <code>' . esc_html(home_url('/mygo-line-callback/')) . '</code></p>';
echo '<hr>';

// 測試 5: 手動刷新 rewrite rules
echo '<h2>測試 5: 手動刷新</h2>';
if (isset($_GET['flush'])) {
    flush_rewrite_rules();
    echo '<p style="color: green;">✓ Rewrite rules 已刷新！請重新測試。</p>';
} else {
    echo '<p><a href="?flush=1" class="button">點此刷新 Rewrite Rules</a></p>';
}
