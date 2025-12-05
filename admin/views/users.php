<?php defined('ABSPATH') or die; ?>

<div class="mygo-admin-wrap">
    <?php include MYGO_PLUGIN_DIR . 'admin/views/partials/top-nav.php'; ?>
    
    <div class="mygo-page-header">
        <div class="mygo-page-title">
            <h1>使用者管理</h1>
            <p>管理已綁定 LINE 的使用者與角色</p>
        </div>
        <div class="mygo-page-tabs">
            <a href="?page=mygo-users" class="mygo-page-tab <?php echo empty($_GET['role']) ? 'active' : ''; ?>">全部</a>
            <a href="?page=mygo-users&role=buyer" class="mygo-page-tab <?php echo ($_GET['role'] ?? '') === 'buyer' ? 'active' : ''; ?>">買家</a>
            <a href="?page=mygo-users&role=seller" class="mygo-page-tab <?php echo ($_GET['role'] ?? '') === 'seller' ? 'active' : ''; ?>">賣家</a>
            <a href="?page=mygo-users&role=helper" class="mygo-page-tab <?php echo ($_GET['role'] ?? '') === 'helper' ? 'active' : ''; ?>">小幫手</a>
            <a href="?page=mygo-users&role=admin" class="mygo-page-tab <?php echo ($_GET['role'] ?? '') === 'admin' ? 'active' : ''; ?>">管理員</a>
        </div>
    </div>

    <div class="mygo-content">
        <div class="mygo-card">
            <table class="mygo-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>LINE 名稱</th>
                        <th>WordPress 帳號</th>
                        <th>聯絡資訊</th>
                        <th>BuyGo 角色</th>
                        <th>註冊時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="mygo-empty">
                                <div class="mygo-empty-icon">👥</div>
                                <h3>目前沒有綁定 LINE 的使用者</h3>
                                <p>使用者透過 LINE 登入後會自動出現在這裡</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php 
                    $roleNames = ['buyer' => '買家', 'seller' => '賣家', 'helper' => '小幫手', 'admin' => '管理員'];
                    $roleBadges = ['buyer' => 'info', 'seller' => 'success', 'helper' => 'warning', 'admin' => 'purple'];
                    $shippingNames = ['home_delivery' => '宅配', 'self_pickup' => '自取'];
                    foreach ($users as $user): 
                        $role = $user['mygo_role'] ?: 'buyer';
                    ?>
                    <tr>
                        <td><?php echo esc_html($user['ID']); ?></td>
                        <td>
                            <strong><?php echo esc_html($user['line_name'] ?: '-'); ?></strong><br>
                            <small style="color: #6b7280; font-size: 10px;">
                                <?php echo esc_html(substr($user['line_uid'], 0, 15)); ?>...
                            </small>
                        </td>
                        <td>
                            <?php echo esc_html($user['display_name']); ?><br>
                            <small style="color: #6b7280;"><?php echo esc_html($user['user_email']); ?></small>
                        </td>
                        <td>
                            <div id="contact-display-<?php echo $user['ID']; ?>">
                                <?php if ($user['phone'] || $user['address'] || $user['shipping_method']): ?>
                                    <?php if ($user['phone']): ?>
                                        <div style="margin-bottom: 4px;">
                                            📱 <?php echo esc_html($user['phone']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($user['address']): ?>
                                        <div style="margin-bottom: 4px; color: #6b7280; font-size: 13px;">
                                            📍 <?php echo esc_html($user['address']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($user['shipping_method']): ?>
                                        <div style="font-size: 12px; margin-bottom: 4px;">
                                            <span class="mygo-badge mygo-badge-secondary">
                                                <?php echo esc_html($shippingNames[$user['shipping_method']] ?? $user['shipping_method']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <button type="button" onclick="editContact(<?php echo $user['ID']; ?>)" class="mygo-btn mygo-btn-sm" style="font-size: 11px; padding: 2px 8px;">編輯</button>
                                <?php else: ?>
                                    <small style="color: #9ca3af;">尚未填寫</small><br>
                                    <button type="button" onclick="editContact(<?php echo $user['ID']; ?>)" class="mygo-btn mygo-btn-sm" style="font-size: 11px; padding: 2px 8px; margin-top: 4px;">新增</button>
                                <?php endif; ?>
                            </div>
                            <div id="contact-edit-<?php echo $user['ID']; ?>" style="display: none;">
                                <form method="post" style="display: flex; flex-direction: column; gap: 8px;">
                                    <?php wp_nonce_field('mygo_update_contact'); ?>
                                    <input type="hidden" name="user_id" value="<?php echo esc_attr($user['ID']); ?>">
                                    <input type="text" name="phone" value="<?php echo esc_attr($user['phone']); ?>" placeholder="電話" style="padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px;">
                                    <input type="text" name="address" value="<?php echo esc_attr($user['address']); ?>" placeholder="地址" style="padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px;">
                                    <select name="shipping_method" style="padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px;">
                                        <option value="">選擇配送方式</option>
                                        <option value="home_delivery" <?php selected($user['shipping_method'], 'home_delivery'); ?>>宅配</option>
                                        <option value="self_pickup" <?php selected($user['shipping_method'], 'self_pickup'); ?>>自取</option>
                                    </select>
                                    <div style="display: flex; gap: 4px;">
                                        <button type="submit" name="mygo_update_contact" value="1" class="mygo-btn mygo-btn-sm mygo-btn-primary" style="font-size: 11px;">儲存</button>
                                        <button type="button" onclick="cancelEditContact(<?php echo $user['ID']; ?>)" class="mygo-btn mygo-btn-sm" style="font-size: 11px;">取消</button>
                                    </div>
                                </form>
                            </div>
                        </td>
                        <td>
                            <span class="mygo-badge mygo-badge-<?php echo $roleBadges[$role]; ?>">
                                <?php echo esc_html($roleNames[$role] ?? $role); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date('Y-m-d H:i', strtotime($user['user_registered']))); ?></td>
                        <td>
                            <form method="post" style="display: flex; gap: 8px; align-items: center;">
                                <?php wp_nonce_field('mygo_update_user_role'); ?>
                                <input type="hidden" name="user_id" value="<?php echo esc_attr($user['ID']); ?>">
                                <select name="new_role" style="padding: 6px 10px; border-radius: 6px; border: 1px solid #d1d5db; font-size: 13px;">
                                    <option value="buyer" <?php selected($role, 'buyer'); ?>>買家</option>
                                    <option value="seller" <?php selected($role, 'seller'); ?>>賣家</option>
                                    <option value="helper" <?php selected($role, 'helper'); ?>>小幫手</option>
                                    <option value="admin" <?php selected($role, 'admin'); ?>>管理員</option>
                                </select>
                                <button type="submit" name="mygo_update_role" value="1" class="mygo-btn mygo-btn-sm mygo-btn-primary">變更</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mygo-card" style="margin-top: 24px;">
            <div class="mygo-card-header">
                <h3>角色說明</h3>
            </div>
            <div class="mygo-card-body">
                <div class="mygo-grid mygo-grid-4">
                    <div style="padding: 16px; background: #f8fafc; border-radius: 8px;">
                        <span class="mygo-badge mygo-badge-info" style="margin-bottom: 8px;">買家</span>
                        <p style="font-size: 13px; color: #6b7280; margin: 8px 0 0 0;">可以瀏覽商品、在社群貼文下 +1 下單、查看自己的訂單</p>
                    </div>
                    <div style="padding: 16px; background: #f8fafc; border-radius: 8px;">
                        <span class="mygo-badge mygo-badge-success" style="margin-bottom: 8px;">賣家</span>
                        <p style="font-size: 13px; color: #6b7280; margin: 8px 0 0 0;">可以透過 LINE 上傳商品、管理自己的商品和訂單</p>
                    </div>
                    <div style="padding: 16px; background: #f8fafc; border-radius: 8px;">
                        <span class="mygo-badge mygo-badge-warning" style="margin-bottom: 8px;">小幫手</span>
                        <p style="font-size: 13px; color: #6b7280; margin: 8px 0 0 0;">可以更新訂單狀態（到貨、已付款、已取貨）</p>
                    </div>
                    <div style="padding: 16px; background: #f8fafc; border-radius: 8px;">
                        <span class="mygo-badge mygo-badge-purple" style="margin-bottom: 8px;">管理員</span>
                        <p style="font-size: 13px; color: #6b7280; margin: 8px 0 0 0;">可以存取 WordPress 後台、管理所有商品、訂單和使用者</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editContact(userId) {
    document.getElementById('contact-display-' + userId).style.display = 'none';
    document.getElementById('contact-edit-' + userId).style.display = 'block';
}

function cancelEditContact(userId) {
    document.getElementById('contact-display-' + userId).style.display = 'block';
    document.getElementById('contact-edit-' + userId).style.display = 'none';
}
</script>
