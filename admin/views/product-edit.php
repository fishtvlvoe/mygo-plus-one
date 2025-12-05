<?php defined('ABSPATH') or die; ?>

<?php if (!empty($redirectUrl)): ?>
<script>window.location.href = '<?php echo esc_js($redirectUrl); ?>';</script>
<?php endif; ?>

<div class="mygo-admin-wrap">
    <?php include MYGO_PLUGIN_DIR . 'admin/views/partials/top-nav.php'; ?>
    
    <div class="mygo-page-header">
        <div class="mygo-page-title" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>編輯商品</h1>
                <p>修改商品資訊</p>
            </div>
            <a href="<?php echo admin_url('admin.php?page=mygo-products'); ?>" class="mygo-btn mygo-btn-secondary">← 返回商品列表</a>
        </div>
    </div>

    <div class="mygo-content">
        <?php if (!empty($message)): ?>
        <div class="mygo-alert mygo-alert-<?php echo esc_attr($messageType); ?>" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 8px; <?php echo $messageType === 'success' ? 'background: #d1fae5; color: #065f46;' : 'background: #fee2e2; color: #991b1b;'; ?>">
            <?php echo esc_html($message); ?>
        </div>
        <?php endif; ?>
        
        <form method="post" class="mygo-form">
            <?php wp_nonce_field('mygo_edit_product'); ?>
            
            <div class="mygo-grid mygo-grid-2" style="gap: 24px;">
                <div class="mygo-card" style="padding: 24px;">
                    <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600;">基本資訊</h3>
                    
                    <div class="mygo-form-group">
                        <label>商品名稱</label>
                        <input type="text" name="title" value="<?php echo esc_attr($product['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="mygo-form-group">
                        <label>商品描述</label>
                        <textarea name="description" rows="4"><?php echo esc_textarea($product['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mygo-grid mygo-grid-2" style="gap: 16px;">
                        <div class="mygo-form-group">
                            <label>價格 (NT$)</label>
                            <input type="number" name="price" value="<?php echo esc_attr($product['price'] ?? 0); ?>" min="0" step="1" required>
                        </div>
                        
                        <div class="mygo-form-group">
                            <label>庫存數量</label>
                            <input type="number" name="stock_quantity" value="<?php echo esc_attr($product['stock_quantity'] ?? 0); ?>" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mygo-grid mygo-grid-2" style="gap: 16px;">
                        <div class="mygo-form-group">
                            <label>到貨日期</label>
                            <input type="date" name="arrival_date" value="<?php echo esc_attr($product['arrival_date'] ?? ''); ?>">
                        </div>
                        
                        <div class="mygo-form-group">
                            <label>預購截止日期</label>
                            <input type="date" name="preorder_date" value="<?php echo esc_attr($product['preorder_date'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div>
                    <div class="mygo-card" style="padding: 24px; margin-bottom: 20px;">
                        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600;">商品圖片</h3>
                        
                        <div id="mygo-product-image-preview" style="margin-bottom: 16px;">
                            <?php if (!empty($product['image_url'])): ?>
                            <img src="<?php echo esc_url($product['image_url']); ?>" alt="" style="max-width: 100%; height: auto; border-radius: 8px;">
                            <?php else: ?>
                            <div style="padding: 40px; background: #f3f4f6; border-radius: 8px; text-align: center; color: #6b7280;">
                                <div style="font-size: 48px; margin-bottom: 8px;">📷</div>
                                <p style="margin: 0;">尚未上傳圖片</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <input type="hidden" name="thumbnail_id" id="mygo-thumbnail-id" value="<?php echo esc_attr($product['thumbnail_id'] ?? ''); ?>">
                        
                        <div style="display: flex; gap: 8px;">
                            <button type="button" id="mygo-upload-image-btn" class="mygo-btn mygo-btn-secondary">
                                <?php echo !empty($product['image_url']) ? '更換圖片' : '上傳圖片'; ?>
                            </button>
                            <?php if (!empty($product['image_url'])): ?>
                            <button type="button" id="mygo-remove-image-btn" class="mygo-btn mygo-btn-secondary" style="color: #dc2626;">移除圖片</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mygo-card" style="padding: 24px;">
                        <h3 style="margin: 0 0 20px 0; font-size: 16px; font-weight: 600;">商品資訊</h3>
                        
                        <div style="font-size: 13px; color: #6b7280;">
                            <p style="margin: 0 0 8px 0;"><strong>商品 ID：</strong><?php echo esc_html($product['id']); ?></p>
                            <p style="margin: 0 0 8px 0;"><strong>上架時間：</strong><?php echo esc_html(date_i18n('Y/m/d H:i', strtotime($product['created_at']))); ?></p>
                            <p style="margin: 0 0 8px 0;"><strong>庫存狀態：</strong>
                                <?php if ($product['stock_status'] === 'in-stock'): ?>
                                <span class="mygo-badge mygo-badge-success">有庫存</span>
                                <?php else: ?>
                                <span class="mygo-badge mygo-badge-danger">已售完</span>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($product['line_user_id'])): ?>
                            <p style="margin: 0;"><strong>上傳者 LINE UID：</strong><br><code style="font-size: 11px;"><?php echo esc_html($product['line_user_id']); ?></code></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 24px; display: flex; gap: 12px;">
                <button type="submit" name="mygo_save_product" class="mygo-btn-primary">儲存變更</button>
                <a href="<?php echo admin_url('admin.php?page=mygo-products'); ?>" class="mygo-btn mygo-btn-secondary">取消</a>
            </div>
        </form>
    </div>
</div>

<?php
// 載入 WordPress 媒體庫
wp_enqueue_media();
?>
<script>
jQuery(document).ready(function($) {
    var mediaUploader;
    
    $('#mygo-upload-image-btn').on('click', function(e) {
        e.preventDefault();
        
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        
        mediaUploader = wp.media({
            title: '選擇商品圖片',
            button: {
                text: '使用此圖片'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            
            $('#mygo-thumbnail-id').val(attachment.id);
            $('#mygo-product-image-preview').html(
                '<img src="' + attachment.url + '" alt="" style="max-width: 100%; height: auto; border-radius: 8px;">'
            );
            $('#mygo-upload-image-btn').text('更換圖片');
            
            // 顯示移除按鈕
            if ($('#mygo-remove-image-btn').length === 0) {
                $('#mygo-upload-image-btn').after(
                    '<button type="button" id="mygo-remove-image-btn" class="mygo-btn mygo-btn-secondary" style="color: #dc2626;">移除圖片</button>'
                );
                bindRemoveButton();
            }
        });
        
        mediaUploader.open();
    });
    
    function bindRemoveButton() {
        $('#mygo-remove-image-btn').on('click', function(e) {
            e.preventDefault();
            
            $('#mygo-thumbnail-id').val('');
            $('#mygo-product-image-preview').html(
                '<div style="padding: 40px; background: #f3f4f6; border-radius: 8px; text-align: center; color: #6b7280;">' +
                '<div style="font-size: 48px; margin-bottom: 8px;">📷</div>' +
                '<p style="margin: 0;">尚未上傳圖片</p>' +
                '</div>'
            );
            $('#mygo-upload-image-btn').text('上傳圖片');
            $(this).remove();
        });
    }
    
    bindRemoveButton();
});
</script>
