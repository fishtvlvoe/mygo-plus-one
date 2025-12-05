<?php

namespace Mygo\PublicPages;

defined('ABSPATH') or die;

use Mygo\Services\UserProfileValidator;

/**
 * Test Profile Page
 * 
 * 測試用：手動填寫個人資料並儲存
 */
class TestProfilePage
{
    public function register(): void
    {
        add_action('init', [$this, 'addRewriteRules']);
        add_filter('query_vars', [$this, 'addQueryVars']);
        add_action('template_redirect', [$this, 'handleRequest']);
    }

    public function addRewriteRules(): void
    {
        add_rewrite_rule('^mygo-test-profile/?$', 'index.php?mygo_test_profile=1', 'top');
    }

    public function addQueryVars(array $vars): array
    {
        $vars[] = 'mygo_test_profile';
        return $vars;
    }

    public function handleRequest(): void
    {
        if (get_query_var('mygo_test_profile')) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $action = $_POST['action'] ?? 'save_profile';
                if ($action === 'test_order') {
                    $this->handleTestOrder();
                } else {
                    $this->handleSave();
                }
            } else {
                $this->renderPage();
            }
            exit;
        }
    }

    private function handleSave(): void
    {
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
            'redirect' => home_url('/portal/'),
        ]);
    }

    private function handleTestOrder(): void
    {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => '請先登入']);
        }

        $userId = get_current_user_id();
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);

        if (!$productId) {
            wp_send_json_error(['message' => '請輸入商品 ID']);
        }

        // 使用 FluentCartService 建立訂單
        $cartService = new \Mygo\Services\FluentCartService();
        $result = $cartService->createOrder($userId, $productId, $quantity);

        if ($result['success']) {
            wp_send_json_success([
                'message' => '訂單建立成功！',
                'order_id' => $result['order_id'],
                'total' => $result['total'] ?? 0,
            ]);
        } else {
            wp_send_json_error([
                'message' => '訂單建立失敗：' . $result['error'],
            ]);
        }
    }

    private function renderPage(): void
    {
        $currentUser = wp_get_current_user();
        $isLoggedIn = $currentUser->ID > 0;
        
        if (!$isLoggedIn) {
            wp_redirect(wp_login_url(home_url('/mygo-test-profile/')));
            exit;
        }

        $validator = new UserProfileValidator();
        $profile = $validator->getUserProfile($currentUser->ID);
        $shippingMethods = $validator->getAvailableShippingMethods();
        $profileCheck = $validator->validateForOrder($currentUser->ID);
        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>測試：個人資料設定 - <?php bloginfo('name'); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f7; min-height: 100vh; padding: 40px 20px; }
                .container { background: white; border-radius: 20px; padding: 40px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
                h1 { font-size: 28px; font-weight: 700; margin-bottom: 12px; color: #1a1a1a; }
                .subtitle { color: #666; margin-bottom: 32px; font-size: 15px; line-height: 1.6; }
                .status-box { background: <?php echo $profileCheck['valid'] ? '#d4edda' : '#fff3cd'; ?>; border: 1px solid <?php echo $profileCheck['valid'] ? '#c3e6cb' : '#ffeaa7'; ?>; border-radius: 12px; padding: 16px; margin-bottom: 24px; }
                .status-box h3 { font-size: 16px; margin-bottom: 8px; color: <?php echo $profileCheck['valid'] ? '#155724' : '#856404'; ?>; }
                .status-box p { font-size: 14px; color: <?php echo $profileCheck['valid'] ? '#155724' : '#856404'; ?>; }
                .user-info { background: #f5f5f7; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
                .user-info h3 { font-size: 14px; color: #666; margin-bottom: 12px; }
                .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e5e5; }
                .info-row:last-child { border-bottom: none; }
                .info-label { color: #666; font-size: 14px; }
                .info-value { font-weight: 500; font-size: 14px; }
                .form-group { margin-bottom: 20px; }
                .form-group label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; color: #333; }
                .form-group input, .form-group select { width: 100%; padding: 14px 16px; border: 1px solid #c6c6c8; border-radius: 12px; font-size: 16px; }
                .error-msg { display: none; color: #ff3b30; font-size: 13px; margin-top: 6px; }
                .btn { width: 100%; padding: 16px 32px; border-radius: 14px; font-size: 17px; font-weight: 600; border: none; cursor: pointer; }
                .btn-primary { background: #007aff; color: white; }
                .btn-success { background: #28a745; color: white; margin-top: 12px; }
                .success-msg { display: none; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 12px; padding: 16px; margin-bottom: 20px; color: #155724; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🧪 測試：個人資料設定</h1>
                <p class="subtitle">這是測試頁面，用於模擬 LINE 登入後的資料儲存流程</p>
                
                <div class="status-box">
                    <h3><?php echo $profileCheck['valid'] ? '✓ 資料完整' : '⚠ 資料不完整'; ?></h3>
                    <p><?php echo $profileCheck['valid'] ? '您的個人資料已完整，可以正常下單' : '請填寫完整的個人資料才能下單'; ?></p>
                </div>
                
                <div class="user-info">
                    <h3>目前登入帳號</h3>
                    <div class="info-row">
                        <span class="info-label">使用者名稱</span>
                        <span class="info-value"><?php echo esc_html($currentUser->display_name); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo esc_html($currentUser->user_email); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">角色</span>
                        <span class="info-value"><?php echo esc_html($profile['role']); ?></span>
                    </div>
                </div>
                
                <div class="success-msg" id="success-msg"></div>
                
                <form id="profile-form">
                    <div class="form-group">
                        <label>電話</label>
                        <input type="tel" name="phone" value="<?php echo esc_attr($profile['phone']); ?>" placeholder="09xxxxxxxx" required>
                        <span class="error-msg"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>地址</label>
                        <input type="text" name="address" value="<?php echo esc_attr($profile['address']); ?>" placeholder="請輸入完整地址" required>
                        <span class="error-msg"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>寄送方式</label>
                        <select name="shipping_method" required>
                            <option value="">請選擇</option>
                            <?php foreach ($shippingMethods as $method): ?>
                                <option value="<?php echo esc_attr($method); ?>" <?php selected($profile['shipping_method'], $method); ?>>
                                    <?php echo esc_html($method); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="error-msg"></span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">儲存個人資料</button>
                    <button type="button" class="btn btn-success" onclick="window.location.href='<?php echo esc_url(home_url('/portal/')); ?>'">前往社群</button>
                </form>
                
                <?php if ($profileCheck['valid']): ?>
                <div style="margin-top: 40px; padding-top: 40px; border-top: 2px solid #e5e5e5;">
                    <h2 style="font-size: 20px; margin-bottom: 16px;">🧪 測試訂單建立</h2>
                    <p style="color: #666; margin-bottom: 20px; font-size: 14px;">資料已完整，可以測試建立訂單</p>
                    
                    <form id="test-order-form">
                        <div class="form-group">
                            <label>商品 ID</label>
                            <input type="number" name="product_id" value="32" placeholder="輸入商品 ID" required>
                            <span class="error-msg"></span>
                        </div>
                        
                        <div class="form-group">
                            <label>數量</label>
                            <input type="number" name="quantity" value="1" min="1" required>
                            <span class="error-msg"></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">測試建立訂單</button>
                    </form>
                    
                    <div id="order-result" style="display: none; margin-top: 20px; padding: 16px; border-radius: 12px;"></div>
                </div>
                <?php endif; ?>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('profile-form');
                const testOrderForm = document.getElementById('test-order-form');
                
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    
                    fetch('<?php echo home_url('/mygo-test-profile/'); ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const successMsg = document.getElementById('success-msg');
                            successMsg.textContent = data.data.message;
                            successMsg.style.display = 'block';
                            
                            setTimeout(function() {
                                if (data.data.redirect) {
                                    window.location.href = data.data.redirect;
                                } else {
                                    window.location.reload();
                                }
                            }, 1500);
                        } else {
                            alert(data.data.message || '儲存失敗');
                            if (data.data.errors) {
                                console.error('驗證錯誤:', data.data.errors);
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('發生錯誤，請稍後再試');
                    });
                });
                
                if (testOrderForm) {
                    testOrderForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const formData = new FormData(testOrderForm);
                        formData.append('action', 'test_order');
                        
                        const resultDiv = document.getElementById('order-result');
                        resultDiv.style.display = 'none';
                        
                        fetch('<?php echo home_url('/mygo-test-profile/'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            resultDiv.style.display = 'block';
                            
                            if (data.success) {
                                resultDiv.style.background = '#d4edda';
                                resultDiv.style.border = '1px solid #c3e6cb';
                                resultDiv.style.color = '#155724';
                                resultDiv.innerHTML = `
                                    <strong>✓ ${data.data.message}</strong><br>
                                    訂單 ID: ${data.data.order_id}<br>
                                    金額: NT$ ${data.data.total.toLocaleString()}
                                `;
                            } else {
                                resultDiv.style.background = '#f8d7da';
                                resultDiv.style.border = '1px solid #f5c6cb';
                                resultDiv.style.color = '#721c24';
                                resultDiv.innerHTML = `<strong>✗ ${data.data.message}</strong>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            resultDiv.style.display = 'block';
                            resultDiv.style.background = '#f8d7da';
                            resultDiv.style.border = '1px solid #f5c6cb';
                            resultDiv.style.color = '#721c24';
                            resultDiv.innerHTML = '<strong>✗ 發生錯誤，請稍後再試</strong>';
                        });
                    });
                }
            });
            </script>
        </body>
        </html>
        <?php
    }
}
