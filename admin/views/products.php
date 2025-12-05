<?php defined('ABSPATH') or die; ?>

<div class="mygo-admin-wrap">
    <?php include MYGO_PLUGIN_DIR . 'admin/views/partials/top-nav.php'; ?>
    
    <div class="mygo-page-header">
        <div class="mygo-page-title" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>商品管理</h1>
                <p>管理透過 LINE 上傳的商品</p>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <input type="text" placeholder="搜尋商品..." value="<?php echo esc_attr($search ?? ''); ?>" 
                       style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; width: 200px;">
                <select style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                    <option value="">所有狀態</option>
                    <option value="in_stock" <?php selected($filter_status ?? '', 'in_stock'); ?>>有庫存</option>
                    <option value="out_of_stock" <?php selected($filter_status ?? '', 'out_of_stock'); ?>>已售完</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mygo-content">
        <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;">
            商品已更新成功
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
        <div style="background: #d1fae5; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;">
            已成功刪除 <?php echo intval($_GET['count'] ?? 1); ?> 個商品
        </div>
        <?php endif; ?>
        
        <div class="mygo-card">
            <?php if (!empty($products)) : ?>
            <div style="padding: 16px; border-bottom: 1px solid #e5e7eb; display: flex; gap: 12px; align-items: center;">
                <button type="button" id="mygo-bulk-delete-btn" class="mygo-btn mygo-btn-sm" style="background: #ef4444; color: white;" disabled>
                    刪除選取的商品
                </button>
                <span id="mygo-selected-count" style="color: #6b7280; font-size: 14px;">已選取 0 個商品</span>
            </div>
            <?php endif; ?>
            
            <table class="mygo-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="mygo-select-all" style="cursor: pointer;">
                        </th>
                        <th>圖片</th>
                        <th>商品名稱</th>
                        <th>價格</th>
                        <th>庫存</th>
                        <th>到貨日期</th>
                        <th>預購截止</th>
                        <th>上架時間</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)) : ?>
                        <?php foreach ($products as $product) : 
                            $arrivalDate = get_post_meta($product['id'], '_mygo_arrival_date', true);
                            $preorderDate = get_post_meta($product['id'], '_mygo_preorder_date', true);
                        ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="mygo-product-checkbox" value="<?php echo esc_attr($product['id'] ?? 0); ?>" style="cursor: pointer;">
                                </td>
                                <td>
                                    <?php if (!empty($product['image_url'])) : ?>
                                        <img src="<?php echo esc_url($product['image_url']); ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                    <?php else : ?>
                                        <div style="width: 50px; height: 50px; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">📷</div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo esc_html($product['title'] ?? ''); ?></strong></td>
                                <td>NT$ <?php echo number_format($product['price'] ?? 0); ?></td>
                                <td>
                                    <?php 
                                    $stock = $product['stock_quantity'] ?? 0;
                                    $badgeClass = $stock > 0 ? 'mygo-badge-success' : 'mygo-badge-danger';
                                    ?>
                                    <span class="mygo-badge <?php echo $badgeClass; ?>"><?php echo esc_html($stock); ?></span>
                                </td>
                                <td><?php echo $arrivalDate ? esc_html(date_i18n('m/d', strtotime($arrivalDate))) : '-'; ?></td>
                                <td><?php echo $preorderDate ? esc_html(date_i18n('m/d', strtotime($preorderDate))) : '-'; ?></td>
                                <td><?php echo esc_html(date_i18n('Y/m/d', strtotime($product['created_at'] ?? ''))); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=mygo-products&action=edit&id=' . ($product['id'] ?? 0)); ?>" class="mygo-btn mygo-btn-sm mygo-btn-secondary">編輯</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9">
                                <div class="mygo-empty">
                                    <div class="mygo-empty-icon">📦</div>
                                    <h3>目前沒有商品</h3>
                                    <p>賣家透過 LINE 上傳商品後會顯示在這裡</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // 全選/取消全選
    $('#mygo-select-all').on('change', function() {
        $('.mygo-product-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });
    
    // 單個 checkbox 變更
    $('.mygo-product-checkbox').on('change', function() {
        updateSelectedCount();
        
        // 更新全選狀態
        const total = $('.mygo-product-checkbox').length;
        const checked = $('.mygo-product-checkbox:checked').length;
        $('#mygo-select-all').prop('checked', total === checked);
    });
    
    // 更新選取數量
    function updateSelectedCount() {
        const count = $('.mygo-product-checkbox:checked').length;
        $('#mygo-selected-count').text('已選取 ' + count + ' 個商品');
        $('#mygo-bulk-delete-btn').prop('disabled', count === 0);
    }
    
    // 批次刪除
    $('#mygo-bulk-delete-btn').on('click', function() {
        const selected = $('.mygo-product-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        if (selected.length === 0) {
            return;
        }
        
        if (!confirm('確定要刪除選取的 ' + selected.length + ' 個商品嗎？\n\n這將同時刪除：\n• FluentCart 商品資料\n• FluentCommunity 貼文\n• 商品圖片\n\n此操作無法復原！')) {
            return;
        }
        
        const $btn = $(this);
        $btn.prop('disabled', true).text('刪除中...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mygo_bulk_delete_products',
                product_ids: selected,
                nonce: '<?php echo wp_create_nonce('mygo_bulk_delete'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = '<?php echo admin_url('admin.php?page=mygo-products'); ?>&deleted=1&count=' + selected.length;
                } else {
                    alert('刪除失敗：' + (response.data || '未知錯誤'));
                    $btn.prop('disabled', false).text('刪除選取的商品');
                }
            },
            error: function() {
                alert('刪除失敗，請稍後再試');
                $btn.prop('disabled', false).text('刪除選取的商品');
            }
        });
    });
});
</script>
