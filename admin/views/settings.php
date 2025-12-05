<?php
defined('ABSPATH') or die;

// 處理建立測試帳號
$test_account_message = '';
if (isset($_POST['mygo_create_test_seller']) && wp_verify_nonce($_POST['_wpnonce_test_seller'], 'mygo_create_test_seller')) {
    $result = mygo_create_test_seller_account();
    $test_account_message = $result;
}

// 處理設定使用者角色
$role_message = '';
if (isset($_POST['mygo_set_user_role']) && wp_verify_nonce($_POST['_wpnonce_set_role'], 'mygo_set_user_role')) {
    $user_id = intval($_POST['mygo_user_id'] ?? 0);
    $role = sanitize_text_field($_POST['mygo_user_role'] ?? '');
    
    if ($user_id && in_array($role, ['buyer', 'seller', 'helper', 'admin'])) {
        update_user_meta($user_id, '_mygo_role', $role);
        $role_message = '<div class="mygo-badge mygo-badge-success" style="margin-bottom: 16px;">已將使用者 ID ' . $user_id . ' 的角色設定為 ' . $role . '</div>';
    }
}

// 處理綁定 LINE UID
$bind_message = '';
if (isset($_POST['mygo_bind_line_uid']) && wp_verify_nonce($_POST['_wpnonce_bind_line'], 'mygo_bind_line_uid')) {
    $user_id = intval($_POST['mygo_bind_user_id'] ?? 0);
    $line_uid = sanitize_text_field($_POST['mygo_line_uid'] ?? '');
    $line_name = sanitize_text_field($_POST['mygo_line_display_name'] ?? '');
    
    if ($user_id && !empty($line_uid)) {
        update_user_meta($user_id, '_mygo_line_uid', $line_uid);
        if (!empty($line_name)) {
            update_user_meta($user_id, '_mygo_line_name', $line_name);
        }
        $bind_message = '<div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;">已將 LINE UID 綁定到使用者 ID ' . $user_id . '</div>';
    }
}

// 處理修正商品價格（將舊格式轉換為新格式）
$fix_price_message = '';
if (isset($_POST['mygo_fix_product_prices']) && wp_verify_nonce($_POST['_wpnonce_fix_prices'], 'mygo_fix_product_prices')) {
    global $wpdb;
    $table = $wpdb->prefix . 'fct_product_variations';
    
    // 找出價格小於 10000 的商品（可能是舊格式）
    $updated = $wpdb->query("
        UPDATE {$table} 
        SET item_price = item_price * 100,
            updated_at = NOW()
        WHERE item_price < 10000 AND item_price > 0
    ");
    
    // 同時更新 product_details 表
    $details_table = $wpdb->prefix . 'fct_product_details';
    $wpdb->query("
        UPDATE {$details_table} 
        SET min_price = min_price * 100,
            max_price = max_price * 100,
            updated_at = NOW()
        WHERE min_price < 10000 AND min_price > 0
    ");
    
    $fix_price_message = '<div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;">已修正 ' . $updated . ' 個商品的價格格式</div>';
}

function mygo_create_test_seller_account() {
    $username = 'test_seller';
    $email = 'test_seller@example.com';
    $password = 'test_seller_123';
    $line_uid = 'U' . md5('test_seller_' . time());
    
    $existing_user = get_user_by('login', $username);
    
    if ($existing_user) {
        $user_id = $existing_user->ID;
        $message = "使用者 '{$username}' 已存在 (ID: {$user_id})，已更新角色為賣家";
    } else {
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            return '<div class="mygo-badge mygo-badge-danger">建立使用者失敗: ' . $user_id->get_error_message() . '</div>';
        }
        $message = "已建立新使用者 '{$username}' (ID: {$user_id})";
    }
    
    wp_update_user(['ID' => $user_id, 'display_name' => '測試賣家']);
    update_user_meta($user_id, '_mygo_role', 'seller');
    update_user_meta($user_id, '_mygo_line_uid', $line_uid);
    update_user_meta($user_id, '_mygo_line_name', '測試賣家');
    
    return '<div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;">
        <strong>' . $message . '</strong><br>
        帳號: ' . $username . ' / 密碼: ' . $password . '<br>
        LINE UID: ' . $line_uid . '
    </div>';
}

global $wpdb;
$spaces = [];
$table_name = $wpdb->prefix . 'fcom_spaces';
if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name) {
    $spaces = $wpdb->get_results(
        "SELECT id, title, slug, privacy FROM {$table_name} WHERE type = 'community' AND status = 'published' ORDER BY title ASC",
        ARRAY_A
    );
}

$users = get_users(['number' => 100, 'orderby' => 'display_name']);
?>
<div class="mygo-admin-wrap">
    <?php include MYGO_PLUGIN_DIR . 'admin/views/partials/top-nav.php'; ?>
    
    <div class="mygo-page-header" style="padding-bottom: 20px;">
        <div class="mygo-page-title">
            <h1>設定</h1>
            <p>LINE API 與系統設定</p>
        </div>
    </div>

    <div class="mygo-content">
        <form method="post" action="options.php">
            <?php settings_fields('mygo_settings'); ?>
            
            <div class="mygo-grid mygo-grid-2">
                <div class="mygo-settings-section">
                    <h3>LINE Messaging API</h3>
                    <div class="section-body">
                        <div class="mygo-form-group">
                            <label>Channel Access Token</label>
                            <input type="text" name="mygo_line_channel_access_token" 
                                   value="<?php echo esc_attr(get_option('mygo_line_channel_access_token', '')); ?>">
                        </div>
                        <div class="mygo-form-group">
                            <label>Channel Secret</label>
                            <input type="text" name="mygo_line_channel_secret" 
                                   value="<?php echo esc_attr(get_option('mygo_line_channel_secret', '')); ?>">
                        </div>
                        <div class="mygo-form-group">
                            <label>LIFF ID</label>
                            <input type="text" name="mygo_liff_id" 
                                   value="<?php echo esc_attr(get_option('mygo_liff_id', '')); ?>"
                                   placeholder="1234567890-abcdefgh">
                            <p class="mygo-form-hint">用於分享商品卡片功能，請在 LINE Developers Console 建立 LIFF app</p>
                        </div>
                        <div class="mygo-form-group">
                            <label>Webhook URL</label>
                            <input type="text" value="<?php echo esc_attr(rest_url('mygo/v1/line-webhook')); ?>" readonly style="background: #f3f4f6;">
                            <p class="mygo-form-hint">請將此 URL 設定到 LINE Developers Console</p>
                        </div>
                        <div class="mygo-form-group">
                            <label>LIFF Endpoint URL</label>
                            <input type="text" value="<?php echo esc_attr(home_url('/wp-content/plugins/mygo-plus-one/public/liff-share-product.php')); ?>" readonly style="background: #f3f4f6;">
                            <p class="mygo-form-hint">請將此 URL 設定為 LIFF app 的 Endpoint URL</p>
                        </div>
                    </div>
                </div>

                <div class="mygo-settings-section">
                    <h3>LINE Login</h3>
                    <div class="section-body">
                        <div class="mygo-form-group">
                            <label>Channel ID</label>
                            <input type="text" name="mygo_line_login_channel_id" 
                                   value="<?php echo esc_attr(get_option('mygo_line_login_channel_id', '')); ?>">
                        </div>
                        <div class="mygo-form-group">
                            <label>Channel Secret</label>
                            <input type="text" name="mygo_line_login_channel_secret" 
                                   value="<?php echo esc_attr(get_option('mygo_line_login_channel_secret', '')); ?>">
                        </div>
                        <div class="mygo-form-group">
                            <label>Callback URL</label>
                            <input type="text" value="<?php echo esc_attr(home_url('/mygo-line-callback/')); ?>" readonly style="background: #f3f4f6;">
                            <p class="mygo-form-hint">請將此 URL 設定到 LINE Login 的 Callback URL</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mygo-settings-section">
                <h3>一般設定</h3>
                <div class="section-body">
                    <div class="mygo-grid mygo-grid-2">
                        <div class="mygo-form-group">
                            <label>預設社群頻道</label>
                            <?php if (!empty($spaces)): ?>
                            <select name="mygo_default_space_id" id="mygo_default_space_id" onchange="updateSpaceSlug()">
                                <option value="" data-slug="">請選擇頻道</option>
                                <?php foreach ($spaces as $space): ?>
                                <option value="<?php echo esc_attr($space['id']); ?>" data-slug="<?php echo esc_attr($space['slug']); ?>" <?php selected(get_option('mygo_default_space_id', ''), $space['id']); ?>>
                                    <?php echo esc_html($space['title']); ?> (<?php echo esc_html($space['slug']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="mygo_default_space_slug" id="mygo_default_space_slug" value="<?php echo esc_attr(get_option('mygo_default_space_slug', '')); ?>">
                            <script>
                            function updateSpaceSlug() {
                                var select = document.getElementById('mygo_default_space_id');
                                var slugInput = document.getElementById('mygo_default_space_slug');
                                var selectedOption = select.options[select.selectedIndex];
                                slugInput.value = selectedOption.getAttribute('data-slug') || '';
                            }
                            // 頁面載入時執行一次
                            document.addEventListener('DOMContentLoaded', updateSpaceSlug);
                            </script>
                            <?php else: ?>
                            <input type="number" name="mygo_default_space_id" value="<?php echo esc_attr(get_option('mygo_default_space_id', '')); ?>">
                            <div class="mygo-form-group" style="margin-top: 8px;">
                                <label>頻道 Slug</label>
                                <input type="text" name="mygo_default_space_slug" value="<?php echo esc_attr(get_option('mygo_default_space_slug', '')); ?>" placeholder="general">
                            </div>
                            <?php endif; ?>
                            <p class="mygo-form-hint">商品貼文將發布到此 FluentCommunity 頻道</p>
                        </div>
                        <div class="mygo-form-group">
                            <label>登入後重導向 URL</label>
                            <input type="url" name="mygo_login_redirect_url" 
                                   value="<?php echo esc_attr(get_option('mygo_login_redirect_url', home_url())); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="mygo-btn-primary">儲存設定</button>
        </form>

        <div class="mygo-settings-section" style="margin-top: 24px;">
            <h3>開發工具</h3>
            <div class="section-body">
                <?php echo $test_account_message; ?>
                <?php echo $role_message; ?>
                <?php echo $bind_message; ?>
                
                <div class="mygo-grid mygo-grid-2" style="gap: 24px;">
                    <div style="padding: 20px; background: #f8fafc; border-radius: 8px;">
                        <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600;">建立測試賣家帳號</h4>
                        <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0;">建立一個測試用的賣家帳號，包含模擬的 LINE UID</p>
                        <form method="post">
                            <?php wp_nonce_field('mygo_create_test_seller', '_wpnonce_test_seller'); ?>
                            <button type="submit" name="mygo_create_test_seller" class="mygo-btn mygo-btn-secondary">建立測試賣家</button>
                        </form>
                    </div>
                    
                    <div style="padding: 20px; background: #f8fafc; border-radius: 8px;">
                        <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600;">LINE 登入測試</h4>
                        <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0;">使用 LINE Login 取得真實的 LINE 使用者資料</p>
                        <a href="<?php echo esc_url(home_url('/mygo-line-login/')); ?>" class="mygo-btn-primary" target="_blank">前往 LINE 登入頁面</a>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 20px; background: #f8fafc; border-radius: 8px;">
                    <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600;">設定使用者角色</h4>
                    <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px 0;">手動設定現有使用者的 MYGO 角色</p>
                    <form method="post" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                        <?php wp_nonce_field('mygo_set_user_role', '_wpnonce_set_role'); ?>
                        <div class="mygo-form-group" style="margin: 0; flex: 1; min-width: 200px;">
                            <label style="font-size: 13px;">使用者</label>
                            <select name="mygo_user_id">
                                <?php foreach ($users as $user): ?>
                                <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name . ' (' . $user->user_login . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mygo-form-group" style="margin: 0; min-width: 120px;">
                            <label style="font-size: 13px;">角色</label>
                            <select name="mygo_user_role">
                                <option value="buyer">買家</option>
                                <option value="seller">賣家</option>
                                <option value="helper">小幫手</option>
                                <option value="admin">管理員</option>
                            </select>
                        </div>
                        <button type="submit" name="mygo_set_user_role" class="mygo-btn mygo-btn-secondary">設定角色</button>
                    </form>
                </div>

                <div style="margin-top: 20px; padding: 20px; background: #fef3c7; border-radius: 8px;">
                    <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600;">綁定 LINE UID</h4>
                    <p style="font-size: 13px; color: #92400e; margin: 0 0 12px 0;">手動將 LINE UID 綁定到現有使用者（從 debug.log 取得 LINE UID）</p>
                    <form method="post" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                        <?php wp_nonce_field('mygo_bind_line_uid', '_wpnonce_bind_line'); ?>
                        <div class="mygo-form-group" style="margin: 0; flex: 1; min-width: 200px;">
                            <label style="font-size: 13px;">使用者</label>
                            <select name="mygo_bind_user_id">
                                <?php foreach ($users as $user): ?>
                                <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->display_name . ' (' . $user->user_login . ')'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mygo-form-group" style="margin: 0; flex: 1; min-width: 280px;">
                            <label style="font-size: 13px;">LINE UID</label>
                            <input type="text" name="mygo_line_uid" placeholder="U823e48d899eb99be6fb49d53609048d9" style="font-family: monospace;">
                        </div>
                        <div class="mygo-form-group" style="margin: 0; min-width: 150px;">
                            <label style="font-size: 13px;">LINE 顯示名稱（選填）</label>
                            <input type="text" name="mygo_line_display_name" placeholder="顯示名稱">
                        </div>
                        <button type="submit" name="mygo_bind_line_uid" class="mygo-btn mygo-btn-secondary">綁定</button>
                    </form>
                </div>

                <div style="margin-top: 20px; padding: 20px; background: #fee2e2; border-radius: 8px;">
                    <?php echo $fix_price_message; ?>
                    <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600;">修正商品價格格式</h4>
                    <p style="font-size: 13px; color: #991b1b; margin: 0 0 12px 0;">將舊版本建立的商品價格轉換為正確格式（乘以 100）</p>
                    <form method="post">
                        <?php wp_nonce_field('mygo_fix_product_prices', '_wpnonce_fix_prices'); ?>
                        <button type="submit" name="mygo_fix_product_prices" class="mygo-btn mygo-btn-secondary" onclick="return confirm('確定要修正所有商品的價格格式嗎？');">修正價格格式</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
