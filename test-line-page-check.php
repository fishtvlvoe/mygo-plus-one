<?php
/**
 * 測試 LINE 登入頁面是否正確註冊
 * 
 * 訪問此檔案來檢查：
 * https://mygo.local/wp-content/plugins/mygo-plus-one/test-line-page-check.php
 */

// 載入 WordPress
require_once('../../../wp-load.php');

echo '<h1>LINE 登入頁面檢查</h1>';

// 檢查 rewrite rules
$rules = get_option('rewrite_rules');
echo '<h2>Rewrite Rules</h2>';
echo '<pre>';
if (isset($rules['^mygo-line-login/?$'])) {
    echo '✓ mygo-line-login 規則已註冊: ' . $rules['^mygo-line-login/?$'] . "\n";
} else {
    echo '✗ mygo-line-login 規則未找到' . "\n";
}

if (isset($rules['^mygo-line-callback/?$'])) {
    echo '✓ mygo-line-callback 規則已註冊: ' . $rules['^mygo-line-callback/?$'] . "\n";
} else {
    echo '✗ mygo-line-callback 規則未找到' . "\n";
}
echo '</pre>';

// 檢查 query vars
global $wp;
echo '<h2>Query Vars</h2>';
echo '<pre>';
if (in_array('mygo_line_login', $wp->public_query_vars)) {
    echo '✓ mygo_line_login query var 已註冊' . "\n";
} else {
    echo '✗ mygo_line_login query var 未找到' . "\n";
}

if (in_array('mygo_line_callback', $wp->public_query_vars)) {
    echo '✓ mygo_line_callback query var 已註冊' . "\n";
} else {
    echo '✗ mygo_line_callback query var 未找到' . "\n";
}
echo '</pre>';

// 測試 URL
echo '<h2>測試連結</h2>';
echo '<p><a href="' . home_url('/mygo-line-login/') . '" target="_blank">測試 LINE 登入頁面</a></p>';
echo '<p><a href="' . admin_url('options-permalink.php') . '" target="_blank">前往固定網址設定（刷新 rewrite rules）</a></p>';

echo '<h2>建議步驟</h2>';
echo '<ol>';
echo '<li>如果上方顯示規則未註冊，請點擊「前往固定網址設定」</li>';
echo '<li>不需要修改任何設定，直接點擊「儲存變更」按鈕</li>';
echo '<li>重新載入此頁面檢查</li>';
echo '<li>然後點擊「測試 LINE 登入頁面」</li>';
echo '</ol>';
