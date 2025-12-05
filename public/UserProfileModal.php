<?php

namespace Mygo\PublicPages;

defined('ABSPATH') or die;

use Mygo\Services\UserProfileValidator;

/**
 * User Profile Modal
 * 
 * 使用者資料補充表單
 */
class UserProfileModal
{
    /**
     * 註冊 Hooks
     */
    public static function register(): void
    {
        $instance = new self();
        
        // 加入前台 Modal HTML
        add_action('wp_footer', [$instance, 'renderModal']);
        
        // 註冊 AJAX 處理
        add_action('wp_ajax_mygo_save_profile', [$instance, 'handleSaveProfile']);
        add_action('wp_ajax_mygo_select_variant', [$instance, 'handleSelectVariant']);
        
        // 加入前台腳本
        add_action('wp_enqueue_scripts', [$instance, 'enqueueScripts']);
    }

    /**
     * 載入腳本
     */
    public function enqueueScripts(): void
    {
        wp_localize_script('mygo-public', 'mygoAjax', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mygo_ajax'),
            'shippingMethods' => (new UserProfileValidator())->getAvailableShippingMethods(),
        ]);
    }

    /**
     * 渲染 Modal
     */
    public function renderModal(): void
    {
        if (!is_user_logged_in()) {
            return;
        }

        $validator = new UserProfileValidator();
        $profile = $validator->getUserProfile(get_current_user_id());
        $shippingMethods = $validator->getAvailableShippingMethods();
        ?>
        
        <!-- 資料補充 Modal -->
        <div id="mygo-profile-modal" class="mygo-modal" style="display: none;">
            <div class="mygo-modal-content">
                <div class="mygo-modal-header">
                    <h2><?php esc_html_e('完善個人資料', 'mygo-plus-one'); ?></h2>
                    <button type="button" class="mygo-modal-close">&times;</button>
                </div>
                <form id="mygo-profile-form">
                    <div class="mygo-form-group">
                        <label for="mygo-phone"><?php esc_html_e('電話', 'mygo-plus-one'); ?></label>
                        <input type="tel" id="mygo-phone" name="phone" value="<?php echo esc_attr($profile['phone']); ?>" placeholder="09xxxxxxxx" required>
                        <span class="mygo-error" id="mygo-phone-error"></span>
                    </div>
                    
                    <div class="mygo-form-group">
                        <label for="mygo-address"><?php esc_html_e('地址', 'mygo-plus-one'); ?></label>
                        <input type="text" id="mygo-address" name="address" value="<?php echo esc_attr($profile['address']); ?>" placeholder="請輸入完整地址" required>
                        <span class="mygo-error" id="mygo-address-error"></span>
                    </div>
                    
                    <div class="mygo-form-group">
                        <label for="mygo-shipping"><?php esc_html_e('寄送方式', 'mygo-plus-one'); ?></label>
                        <select id="mygo-shipping" name="shipping_method" required>
                            <option value=""><?php esc_html_e('請選擇', 'mygo-plus-one'); ?></option>
                            <?php foreach ($shippingMethods as $method) : ?>
                                <option value="<?php echo esc_attr($method); ?>" <?php selected($profile['shipping_method'], $method); ?>>
                                    <?php echo esc_html($method); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="mygo-error" id="mygo-shipping-error"></span>
                    </div>
                    
                    <input type="hidden" id="mygo-pending-feed" name="pending_feed_id" value="">
                    <input type="hidden" id="mygo-pending-comment" name="pending_comment_id" value="">
                    
                    <button type="submit" class="mygo-btn mygo-btn-primary mygo-btn-full">
                        <?php esc_html_e('儲存並下單', 'mygo-plus-one'); ?>
                    </button>
                </form>
            </div>
        </div>
        
        <!-- 規格選擇 Modal -->
        <div id="mygo-variant-modal" class="mygo-modal" style="display: none;">
            <div class="mygo-modal-content">
                <div class="mygo-modal-header">
                    <h2><?php esc_html_e('選擇規格', 'mygo-plus-one'); ?></h2>
                    <button type="button" class="mygo-modal-close">&times;</button>
                </div>
                <div id="mygo-variant-options" class="mygo-variant-grid">
                    <!-- 動態填入 -->
                </div>
                <input type="hidden" id="mygo-variant-feed" value="">
                <input type="hidden" id="mygo-variant-product" value="">
                <input type="hidden" id="mygo-variant-quantity" value="1">
            </div>
        </div>
        
        <style>
            .mygo-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: flex-end;
                justify-content: center;
                z-index: 99999;
            }
            
            .mygo-modal-content {
                background: #fff;
                border-radius: 20px 20px 0 0;
                width: 100%;
                max-width: 500px;
                padding: 24px;
                max-height: 80vh;
                overflow-y: auto;
            }
            
            .mygo-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 24px;
            }
            
            .mygo-modal-header h2 {
                font-size: 20px;
                font-weight: 700;
                margin: 0;
            }
            
            .mygo-modal-close {
                background: none;
                border: none;
                font-size: 28px;
                cursor: pointer;
                color: #8e8e93;
            }
            
            .mygo-form-group {
                margin-bottom: 20px;
            }
            
            .mygo-form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
            }
            
            .mygo-form-group input,
            .mygo-form-group select {
                width: 100%;
                padding: 14px 16px;
                border: 1px solid #c6c6c8;
                border-radius: 12px;
                font-size: 16px;
            }
            
            .mygo-error {
                display: block;
                color: #ff3b30;
                font-size: 13px;
                margin-top: 6px;
            }
            
            .mygo-btn-full {
                width: 100%;
            }
            
            .mygo-variant-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 12px;
            }
            
            .mygo-variant-btn {
                padding: 16px;
                border: 2px solid #c6c6c8;
                border-radius: 12px;
                background: #fff;
                cursor: pointer;
                text-align: center;
                font-size: 15px;
                transition: all 0.2s;
            }
            
            .mygo-variant-btn:hover {
                border-color: #007aff;
            }
            
            .mygo-variant-btn.selected {
                border-color: #007aff;
                background: #007aff;
                color: #fff;
            }
        </style>
        <?php
    }

    /**
     * 處理儲存個人資料
     */
    public function handleSaveProfile(): void
    {
        check_ajax_referer('mygo_ajax', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => '請先登入']);
        }

        $userId = get_current_user_id();
        $validator = new UserProfileValidator();

        $data = [
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'address' => sanitize_text_field($_POST['address'] ?? ''),
            'shipping_method' => sanitize_text_field($_POST['shipping_method'] ?? ''),
        ];

        $validation = $validator->validateAndSanitize($data);

        if (!$validation['valid']) {
            wp_send_json_error([
                'message' => '資料驗證失敗',
                'errors' => $validation['errors'],
            ]);
        }

        $validator->updateUserProfile($userId, $validation['sanitized']);

        wp_send_json_success([
            'message' => '資料已儲存',
        ]);
    }

    /**
     * 處理規格選擇
     */
    public function handleSelectVariant(): void
    {
        check_ajax_referer('mygo_ajax', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => '請先登入']);
        }

        $feedId = intval($_POST['feed_id'] ?? 0);
        $productId = intval($_POST['product_id'] ?? 0);
        $variant = sanitize_text_field($_POST['variant'] ?? '');
        $quantity = max(1, intval($_POST['quantity'] ?? 1));

        if (!$feedId || !$productId || !$variant) {
            wp_send_json_error(['message' => '參數錯誤']);
        }

        // 建立訂單
        $orderService = new \Mygo\Services\PlusOneOrderService();
        $result = $orderService->createOrder(
            get_current_user_id(),
            $productId,
            $quantity,
            $variant
        );

        if ($result['success']) {
            wp_send_json_success([
                'message' => '訂單已建立',
                'order_id' => $result['order_id'],
            ]);
        } else {
            wp_send_json_error([
                'message' => $result['error'] ?? '訂單建立失敗',
            ]);
        }
    }
}
