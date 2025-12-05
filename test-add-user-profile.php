<?php
/**
 * 測試腳本：為使用者加入個人資料
 * 
 * 使用方式：在瀏覽器訪問 https://mygo.local/wp-content/plugins/mygo-plus-one/test-add-user-profile.php
 */

// 載入 WordPress
require_once __DIR__ . '/../../../wp-load.php';

// 檢查是否為管理員
if (!current_user_can('manage_options')) {
    die('需要管理員權限');
}

// 使用者 ID
$userId = 1;

// 加入測試資料
update_user_meta($userId, '_mygo_phone', '0912345678');
update_user_meta($userId, '_mygo_address', '台北市信義區信義路五段7號');
update_user_meta($userId, '_mygo_shipping_method', '宅配');

echo '<h1>測試資料已加入</h1>';
echo '<p>使用者 ID: ' . $userId . '</p>';
echo '<p>電話: 0912345678</p>';
echo '<p>地址: 台北市信義區信義路五段7號</p>';
echo '<p>寄送方式: 宅配</p>';
echo '<p><a href="' . admin_url('admin.php?page=mygo-users') . '">返回使用者管理</a></p>';
