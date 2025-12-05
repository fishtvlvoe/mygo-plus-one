<?php
defined('ABSPATH') or die;

/**
 * Order Detail View
 */
?>
<div class="mygo-admin-wrap">
    <?php include MYGO_PLUGIN_DIR . 'admin/views/partials/top-nav.php'; ?>
    
    <div class="mygo-page-header">
        <div class="mygo-page-title" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1><?php esc_html_e('訂單詳情', 'mygo-plus-one'); ?> #<?php echo esc_html($order['id'] ?? ''); ?></h1>
                <p>查看與管理訂單資訊</p>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="button" class="mygo-btn mygo-btn-secondary" id="mygo-delete-order" data-order-id="<?php echo esc_attr($order['id'] ?? 0); ?>" style="color: #dc2626;">
                    🗑️ <?php esc_html_e('刪除訂單', 'mygo-plus-one'); ?>
                </button>
                <a href="<?php echo admin_url('admin.php?page=mygo-orders'); ?>" class="mygo-btn mygo-btn-secondary">
                    ← <?php esc_html_e('返回列表', 'mygo-plus-one'); ?>
                </a>
            </div>
        </div>
    </div>
    
    <div class="mygo-content">
        <form method="post" id="mygo-order-form">
            <div class="mygo-grid mygo-grid-2" style="gap: 24px;">
                <!-- 左側：訂單資訊 + 買家資訊 -->
                <div>
                    <div class="mygo-card" style="padding: 24px; margin-bottom: 20px;">
                        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600;">訂單資訊</h3>
                        
                        <div class="mygo-form-group">
                            <label>商品名稱</label>
                            <input type="text" value="<?php echo esc_attr($order['product_name'] ?? ''); ?>" disabled style="background: #f3f4f6;">
                        </div>
                        
                        <div class="mygo-grid mygo-grid-2" style="gap: 16px;">
                            <div class="mygo-form-group">
                                <label>單價 (NT$)</label>
                                <input type="number" name="unit_price" value="<?php echo esc_attr($order['unit_price'] ?? 0); ?>" min="0" step="1" required>
                            </div>
                            
                            <div class="mygo-form-group">
                                <label>數量</label>
                                <input type="number" name="quantity" value="<?php echo esc_attr($order['quantity'] ?? 1); ?>" min="1" required>
                            </div>
                        </div>
                        
                        <div class="mygo-form-group">
                            <label>金額</label>
                            <div style="padding: 12px; background: #d1fae5; border-radius: 8px; font-size: 20px; font-weight: 600; color: #065f46;">
                                NT$ <span id="order-total"><?php echo number_format($order['total'] ?? 0); ?></span>
                            </div>
                        </div>
                        
                        <div class="mygo-form-group">
                            <label>下單時間</label>
                            <input type="text" value="<?php echo esc_attr(date_i18n('Y/m/d H:i:s', strtotime($order['created_at'] ?? ''))); ?>" disabled style="background: #f3f4f6;">
                        </div>
                        
                        <div class="mygo-form-group">
                            <label>下單來源</label>
                            <input type="text" value="<?php echo esc_attr($order['source'] ?? '於賣場下單 +1'); ?>" disabled style="background: #f3f4f6;">
                        </div>
                        
                        <input type="hidden" name="order_id" value="<?php echo esc_attr($order['id'] ?? 0); ?>">
                        <input type="hidden" name="product_id" value="<?php echo esc_attr($order['product_id'] ?? 0); ?>">
                        
                        <button type="button" class="mygo-btn-primary" id="mygo-save-order-info">儲存訂單資訊</button>
                    </div>
                    
                    <div class="mygo-card" style="padding: 24px;">
                        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600;">買家資訊</h3>
                        
                        <div class="mygo-form-group">
                            <label>買家姓名</label>
                            <input type="text" name="buyer_name" value="<?php echo esc_attr($order['buyer_name'] ?? ''); ?>" placeholder="請輸入買家姓名">
                        </div>
                        
                        <div class="mygo-form-group">
                            <label>LINE 名稱</label>
                            <input type="text" value="<?php echo esc_attr($order['line_name'] ?? ''); ?>" disabled style="background: #f3f4f6;">
                        </div>
                        
                        <div class="mygo-form-group">
                            <label>電話</label>
                            <input type="tel" name="phone" value="<?php echo esc_attr($order['phone'] ?? ''); ?>" placeholder="請輸入電話">
                        </div>
                        
                        <div class="mygo-form-group">
                            <label>地址</label>
                            <input type="text" name="address" value="<?php echo esc_attr($order['address'] ?? ''); ?>" placeholder="請輸入地址">
                        </div>
                        
                        <div class="mygo-form-group">
                            <label>寄送方式</label>
                            <select name="shipping_method">
                                <option value="">請選擇</option>
                                <option value="self_pickup" <?php selected($order['shipping_method'] ?? '', 'self_pickup'); ?>>自取</option>
                                <option value="home_delivery" <?php selected($order['shipping_method'] ?? '', 'home_delivery'); ?>>宅配</option>
                                <option value="convenience_store" <?php selected($order['shipping_method'] ?? '', 'convenience_store'); ?>>超商取貨</option>
                            </select>
                        </div>
                        
                        <input type="hidden" name="user_id" value="<?php echo esc_attr($order['user_id'] ?? 0); ?>">
                        
                        <button type="button" class="mygo-btn-primary" id="mygo-save-buyer-info">儲存買家資訊</button>
                    </div>
                </div>
                
                <!-- 右側：訂單狀態 + 備註 -->
                <div>
                    <div class="mygo-card" style="padding: 24px; margin-bottom: 20px;">
                        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600;">訂單狀態</h3>
                        
                        <?php 
                        $statuses = $order['statuses'] ?? [];
                        $statusLabels = [
                            'arrived' => '到貨狀態',
                            'paid' => '付款狀態',
                            'shipped' => '寄送狀態',
                            'closed' => '結單狀態',
                        ];
                        foreach ($statusLabels as $key => $label) :
                            $isActive = $statuses[$key] ?? false;
                        ?>
                            <div class="mygo-form-group" style="display: flex; align-items: center; justify-content: space-between;">
                                <label style="margin: 0;"><?php echo esc_html($label); ?></label>
                                <label class="mygo-toggle">
                                    <input type="checkbox" 
                                           name="status_<?php echo $key; ?>" 
                                           data-order-id="<?php echo esc_attr($order['fluentcart_order_id'] ?? 0); ?>"
                                           data-status-type="<?php echo $key; ?>"
                                           <?php checked($isActive); ?>>
                                    <span class="mygo-toggle-slider"></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mygo-card" style="padding: 24px;">
                        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600;">備註</h3>
                        
                        <div class="mygo-form-group">
                            <textarea id="mygo-order-notes" rows="6" placeholder="<?php esc_attr_e('輸入備註...', 'mygo-plus-one'); ?>"><?php echo esc_textarea($order['notes'] ?? ''); ?></textarea>
                        </div>
                        
                        <button type="button" class="mygo-btn-primary" id="mygo-save-notes" data-order-id="<?php echo esc_attr($order['fluentcart_order_id'] ?? 0); ?>">
                            <?php esc_html_e('存檔', 'mygo-plus-one'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // 自動計算訂單金額
    function updateOrderTotal() {
        var unitPrice = parseFloat($('input[name="unit_price"]').val()) || 0;
        var quantity = parseInt($('input[name="quantity"]').val()) || 1;
        var total = unitPrice * quantity;
        $('#order-total').text(total.toLocaleString('zh-TW'));
    }

    $('input[name="unit_price"], input[name="quantity"]').on('input', updateOrderTotal);
});
</script>
