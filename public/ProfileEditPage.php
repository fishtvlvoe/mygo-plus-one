<?php

namespace Mygo;

defined('ABSPATH') or die;

use Mygo\Services\UserProfileValidator;

/**
 * Profile Edit Page
 * 
 * 獨立的個人資料編輯頁面
 */
class ProfileEditPage
{
    /**
     * 註冊頁面
     */
    public static function register(): void
    {
        add_action('init', [__CLASS__, 'registerRewriteRule']);
        add_action('template_redirect', [__CLASS__, 'handlePage'], 1);
    }

    /**
     * 註冊 URL 規則
     */
    public static function registerRewriteRule(): void
    {
        add_rewrite_rule('^mygo-profile-edit/?$', 'index.php?mygo_profile_edit=1', 'top');
        
        add_filter('query_vars', function($vars) {
            $vars[] = 'mygo_profile_edit';
            return $vars;
        });
    }

    /**
     * 處理頁面請求
     */
    public static function handlePage(): void
    {
        // 方法 1: 使用 query_var
        if (get_query_var('mygo_profile_edit')) {
            self::renderEditPage();
            exit;
        }
        
        // 方法 2: 直接檢查 URL（備用方案）
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($request_uri, '/mygo-profile-edit') !== false) {
            self::renderEditPage();
            exit;
        }
    }
    
    /**
     * 渲染編輯頁面
     */
    private static function renderEditPage(): void
    {

        // 必須登入
        if (!is_user_logged_in()) {
            wp_redirect(home_url('/mygo-line-login/'));
            exit;
        }

        $currentUser = wp_get_current_user();
        $validator = new UserProfileValidator();
        $message = '';
        $messageType = '';

        // 處理表單提交
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mygo_update_profile'])) {
            if (wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mygo_profile_edit')) {
                $phone = sanitize_text_field($_POST['phone'] ?? '');
                $address = sanitize_text_field($_POST['address'] ?? '');
                $shippingMethod = sanitize_text_field($_POST['shipping_method'] ?? '');

                // 驗證資料
                $validation = $validator->validateAndSanitize([
                    'phone' => $phone,
                    'address' => $address,
                    'shipping_method' => $shippingMethod,
                ]);

                if ($validation['valid']) {
                    // 儲存資料
                    $validator->updateUserProfile($currentUser->ID, $validation['sanitized']);
                    $message = __('個人資料已更新', 'mygo-plus-one');
                    $messageType = 'success';
                    
                    // 重新取得資料
                    $profile = $validator->getUserProfile($currentUser->ID);
                } else {
                    $message = __('資料驗證失敗', 'mygo-plus-one') . '：' . implode('、', $validation['errors']);
                    $messageType = 'error';
                    $profile = $validator->getUserProfile($currentUser->ID);
                }
            }
        } else {
            $profile = $validator->getUserProfile($currentUser->ID);
        }

        $shippingMethods = $validator->getAvailableShippingMethods();

        self::render($profile, $shippingMethods, $message, $messageType);
    }

    /**
     * 渲染頁面
     */
    private static function render(array $profile, array $shippingMethods, string $message, string $messageType): void
    {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>編輯個人資料 - <?php bloginfo('name'); ?></title>
            <?php wp_head(); ?>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    padding: 20px; 
                }
                .container { 
                    background: white; 
                    border-radius: 24px; 
                    padding: 40px; 
                    max-width: 500px; 
                    width: 100%; 
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                }
                .header {
                    text-align: center;
                    margin-bottom: 32px;
                }
                .icon {
                    width: 64px;
                    height: 64px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 16px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 16px;
                    font-size: 32px;
                }
                h1 { 
                    font-size: 24px; 
                    font-weight: 700; 
                    margin-bottom: 8px;
                    color: #1a1a1a;
                }
                .subtitle { 
                    color: #666; 
                    font-size: 14px;
                }
                .form-group {
                    margin-bottom: 20px;
                }
                label {
                    display: block;
                    margin-bottom: 8px;
                    font-weight: 500;
                    font-size: 14px;
                    color: #333;
                }
                input[type="text"],
                input[type="tel"],
                select {
                    width: 100%;
                    padding: 12px 16px;
                    border: 1px solid #c6c6c8;
                    border-radius: 10px;
                    font-size: 15px;
                    transition: border-color 0.3s;
                }
                input:focus,
                select:focus {
                    outline: none;
                    border-color: #667eea;
                }
                .btn {
                    width: 100%;
                    background: #667eea;
                    color: white;
                    border: none;
                    padding: 14px;
                    border-radius: 10px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                .btn:hover {
                    background: #5568d3;
                    transform: translateY(-2px);
                }
                .message {
                    padding: 12px 16px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                    font-size: 14px;
                }
                .message.success {
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                    color: #155724;
                }
                .message.error {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                }
                .back-link {
                    display: block;
                    text-align: center;
                    margin-top: 20px;
                    color: #667eea;
                    text-decoration: none;
                    font-size: 14px;
                    font-weight: 500;
                }
                .back-link:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="icon">✏️</div>
                    <h1>編輯個人資料</h1>
                    <p class="subtitle">更新您的聯絡資訊和寄送偏好</p>
                </div>

                <?php if ($message): ?>
                    <div class="message <?php echo esc_attr($messageType); ?>">
                        <?php echo esc_html($message); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <?php wp_nonce_field('mygo_profile_edit'); ?>
                    
                    <div class="form-group">
                        <label for="phone">電話</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo esc_attr($profile['phone']); ?>" placeholder="09xxxxxxxx" required>
                    </div>

                    <div class="form-group">
                        <label for="address">地址</label>
                        <input type="text" id="address" name="address" value="<?php echo esc_attr($profile['address']); ?>" placeholder="請輸入完整地址" required>
                    </div>

                    <div class="form-group">
                        <label for="shipping_method">寄送方式</label>
                        <select id="shipping_method" name="shipping_method" required>
                            <option value="">請選擇配送方式</option>
                            <?php foreach ($shippingMethods as $method): ?>
                                <option value="<?php echo esc_attr($method); ?>" <?php selected($profile['shipping_method'], $method); ?>>
                                    <?php echo esc_html($method); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" name="mygo_update_profile" value="1" class="btn">
                        儲存變更
                    </button>
                </form>

                <a href="<?php echo esc_url(home_url('/portal/')); ?>" class="back-link">← 返回社群</a>
            </div>
            <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    }
}
