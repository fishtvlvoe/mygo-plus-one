<?php

namespace Mygo\PublicPages;

defined('ABSPATH') or die;

use Mygo\Services\LineAuthHandler;

class LineLoginPage
{
    private LineAuthHandler $authHandler;

    public function __construct()
    {
        $this->authHandler = new LineAuthHandler();
    }

    public function register(): void
    {
        add_action('init', [$this, 'addRewriteRules']);
        add_filter('query_vars', [$this, 'addQueryVars']);
        add_action('template_redirect', [$this, 'handleRequest']);
    }

    public function addRewriteRules(): void
    {
        add_rewrite_rule('^mygo-line-login/?$', 'index.php?mygo_line_login=1', 'top');
        add_rewrite_rule('^mygo-line-callback/?$', 'index.php?mygo_line_callback=1', 'top');
    }

    public function addQueryVars(array $vars): array
    {
        $vars[] = 'mygo_line_login';
        $vars[] = 'mygo_line_callback';
        return $vars;
    }

    public function handleRequest(): void
    {
        if (get_query_var('mygo_line_login')) {
            // 處理角色切換
            if (isset($_POST['mygo_change_role']) && is_user_logged_in()) {
                $this->handleRoleChange();
            }
            $this->renderLoginPage();
            exit;
        }
        if (get_query_var('mygo_line_callback')) {
            $this->handleCallback();
            exit;
        }
    }

    private function handleRoleChange(): void
    {
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mygo_change_role')) {
            return;
        }
        $newRole = sanitize_text_field($_POST['mygo_new_role'] ?? '');
        $validRoles = ['buyer', 'seller', 'helper', 'admin'];
        if (in_array($newRole, $validRoles)) {
            $userId = get_current_user_id();
            update_user_meta($userId, '_mygo_role', $newRole);
            // 如果是 admin，給予 WordPress 管理員權限
            if ($newRole === 'admin') {
                $user = new \WP_User($userId);
                $user->set_role('administrator');
            }
        }
    }

    private function renderLoginPage(): void
    {
        // 如果已登入，導向到社群頁面（除非是管理員且帶有 debug 參數）
        if (is_user_logged_in()) {
            $isAdmin = current_user_can('manage_options');
            $isDebug = isset($_GET['debug']) && $_GET['debug'] === '1';
            
            // 只有管理員在 debug 模式下才顯示管理頁面
            if ($isAdmin && $isDebug) {
                $redirectUri = home_url('/mygo-line-callback/');
                $authUrl = $this->authHandler->getAuthUrl($redirectUri);
                $currentUser = wp_get_current_user();
                $lineUid = get_user_meta($currentUser->ID, '_mygo_line_uid', true);
                $lineName = get_user_meta($currentUser->ID, '_mygo_line_name', true);
                $mygoRole = get_user_meta($currentUser->ID, '_mygo_role', true);
                $roleNames = ['buyer' => '買家', 'seller' => '賣家', 'helper' => '小幫手', 'admin' => '管理員'];
                $this->renderPage($authUrl, $redirectUri, true, $currentUser, $lineUid, $lineName, $mygoRole, $roleNames);
                return;
            }
            
            // 一般用戶直接導向社群頁面
            wp_redirect(home_url('/portal/'));
            exit;
        }
        
        // 未登入，顯示登入表單
        $redirectUri = home_url('/mygo-line-callback/');
        $authUrl = $this->authHandler->getAuthUrl($redirectUri);
        $validator = new \Mygo\Services\UserProfileValidator();
        $shippingMethods = $validator->getAvailableShippingMethods();
        $this->renderLoginFormPage($authUrl, $shippingMethods, false);
    }
    
    private function renderLoginFormPage(string $authUrl, array $shippingMethods, bool $isRegister): void
    {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo $isRegister ? '註冊' : '登入'; ?> - <?php bloginfo('name'); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
                .container { background: white; border-radius: 24px; padding: 40px; max-width: 480px; width: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
                .logo { width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 36px; }
                h1 { font-size: 28px; font-weight: 700; margin-bottom: 12px; color: #1a1a1a; text-align: center; }
                .subtitle { color: #666; margin-bottom: 32px; font-size: 15px; line-height: 1.6; text-align: center; }
                .form-group { margin-bottom: 20px; }
                .form-group label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; color: #333; }
                .form-group input, .form-group select { width: 100%; padding: 14px 16px; border: 1px solid #c6c6c8; border-radius: 12px; font-size: 16px; }
                .error-msg { display: none; color: #ff3b30; font-size: 13px; margin-top: 6px; }
                .btn-line { width: 100%; display: flex; align-items: center; justify-content: center; gap: 12px; background: #06C755; color: white; padding: 16px 32px; border-radius: 14px; font-size: 17px; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(6, 199, 85, 0.3); }
                .line-icon { width: 24px; height: 24px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #06C755; font-size: 16px; }
                .features { padding-top: 24px; border-top: 1px solid #e5e5e5; margin-top: 24px; }
                .feature-item { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
                .feature-icon { width: 32px; height: 32px; background: #f5f5f7; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
                .feature-text { font-size: 13px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="logo">🛒</div>
                <h1><?php echo $isRegister ? '歡迎加入 BuyGo' : '歡迎回來'; ?></h1>
                <p class="subtitle"><?php echo $isRegister ? '請先填寫基本資料，再使用 LINE 帳號完成註冊' : '請先填寫基本資料，再使用 LINE 帳號登入'; ?></p>
                
                <!-- Debug 資訊 -->
                <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 12px;">
                    <strong>Debug Info (測試用):</strong><br>
                    Auth URL: <code style="word-break: break-all; display: block; margin-top: 4px;"><?php echo esc_html($authUrl); ?></code>
                </div>
                
                <div id="mygo-pre-login-form">
                    <div class="form-group">
                        <label>Email <span style="color: #ff3b30;">*</span></label>
                        <input type="email" name="email" placeholder="your@email.com" required>
                        <span class="error-msg"></span>
                        <small style="display: block; color: #666; font-size: 12px; margin-top: 4px;">如果 LINE 未提供 email，將使用此 email 建立帳號</small>
                    </div>
                    
                    <div class="form-group">
                        <label>電話</label>
                        <input type="tel" name="phone" placeholder="09xxxxxxxx" required>
                        <span class="error-msg"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>地址</label>
                        <input type="text" name="address" placeholder="請輸入完整地址" required>
                        <span class="error-msg"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>寄送方式</label>
                        <select name="shipping_method" required>
                            <option value="">請選擇</option>
                            <?php foreach ($shippingMethods as $method): ?>
                                <option value="<?php echo esc_attr($method); ?>"><?php echo esc_html($method); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="error-msg"></span>
                    </div>
                    
                    <form id="mygo-line-form" method="GET" style="width: 100%; margin-top: 24px;">
                        <button type="submit" id="mygo-continue-to-line" class="btn-line">
                            <span class="line-icon">L</span>
                            繼續使用 LINE <?php echo $isRegister ? '註冊' : '登入'; ?>
                        </button>
                    </form>
                </div>
                
                <script>
                // 設定 LINE form 的 action 和參數
                (function() {
                    var authUrl = <?php echo json_encode($authUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
                    var lineForm = document.getElementById('mygo-line-form');
                    
                    if (!lineForm) {
                        console.error('MYGO: mygo-line-form not found');
                        return;
                    }
                    
                    // 手動分割 URL
                    var parts = authUrl.split('?');
                    if (parts.length === 2) {
                        lineForm.action = parts[0];
                        
                        // 手動解析查詢參數
                        var params = parts[1].split('&');
                        params.forEach(function(param) {
                            var keyValue = param.split('=');
                            if (keyValue.length === 2) {
                                var input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = decodeURIComponent(keyValue[0]);
                                input.value = decodeURIComponent(keyValue[1]);
                                lineForm.appendChild(input);
                            }
                        });
                        
                        console.log('MYGO: Form action set to:', lineForm.action);
                    }
                })();
                </script>
                
                <div class="features">
                    <div class="feature-item">
                        <div class="feature-icon">⚡</div>
                        <div class="feature-text">快速登入，無需記憶密碼</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🔒</div>
                        <div class="feature-text">安全可靠的 LINE 官方認證</div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🎁</div>
                        <div class="feature-text">一鍵下單，輕鬆購物</div>
                    </div>
                </div>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const form = document.getElementById('mygo-pre-login-form');
                const lineForm = document.getElementById('mygo-line-form');
                
                if (!lineForm) {
                    console.error('MYGO: mygo-line-form not found in DOM');
                    return;
                }
                
                lineForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const email = form.querySelector('[name="email"]').value.trim();
                    const phone = form.querySelector('[name="phone"]').value.trim();
                    const address = form.querySelector('[name="address"]').value.trim();
                    const shipping = form.querySelector('[name="shipping_method"]').value;
                    
                    let hasError = false;
                    form.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');
                    
                    // 驗證 email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        showError(form.querySelector('[name="email"]'), '請輸入有效的 email 地址');
                        hasError = true;
                    }
                    
                    const phoneClean = phone.replace(/[^\d]/g, '');
                    if (!/^09\d{8}$/.test(phoneClean)) {
                        showError(form.querySelector('[name="phone"]'), '請輸入有效的手機號碼（09xxxxxxxx）');
                        hasError = true;
                    }
                    
                    if (address.length < 10) {
                        showError(form.querySelector('[name="address"]'), '請輸入完整的地址（至少 10 個字元）');
                        hasError = true;
                    }
                    
                    if (!shipping) {
                        showError(form.querySelector('[name="shipping_method"]'), '請選擇寄送方式');
                        hasError = true;
                    }
                    
                    if (hasError) return false;
                    
                    sessionStorage.setItem('mygo_pre_login_data', JSON.stringify({
                        email: email,
                        phone: phoneClean,
                        address: address,
                        shipping_method: shipping
                    }));
                    
                    const redirectTo = '<?php echo esc_js(home_url('/portal/')); ?>';
                    sessionStorage.setItem('mygo_redirect_after_login', redirectTo);
                    
                    // 提交 form 導向 LINE
                    lineForm.submit();
                    return false;
                });
                
                function showError(input, message) {
                    const errorEl = input.parentElement.querySelector('.error-msg');
                    if (errorEl) {
                        errorEl.textContent = message;
                        errorEl.style.display = 'block';
                    }
                    input.style.borderColor = '#ff3b30';
                }
            });
            </script>
        </body>
        </html>
        <?php
    }


    private function handleCallback(): void
    {
        $code = sanitize_text_field($_GET['code'] ?? '');
        $state = sanitize_text_field($_GET['state'] ?? '');
        $error = sanitize_text_field($_GET['error'] ?? '');
        $errorDescription = sanitize_text_field($_GET['error_description'] ?? '');
        
        error_log('MYGO LineCallback: code = ' . $code);
        error_log('MYGO LineCallback: state = ' . $state);
        error_log('MYGO LineCallback: error = ' . $error);
        error_log('MYGO LineCallback: error_description = ' . $errorDescription);
        
        if ($error) {
            $errorMsg = 'LINE 登入發生錯誤: ' . $error;
            if ($errorDescription) {
                $errorMsg .= ' (' . $errorDescription . ')';
            }
            $this->renderError($errorMsg);
            return;
        }
        if (empty($code)) {
            $this->renderError('未收到授權碼');
            return;
        }
        if (!get_transient('mygo_line_state_' . $state)) {
            $this->renderError('無效的 state 參數');
            return;
        }
        delete_transient('mygo_line_state_' . $state);
        
        $redirectUri = home_url('/mygo-line-callback/');
        $result = $this->authHandler->handleCallback($code, $redirectUri);
        
        if (!$result['success']) {
            error_log('MYGO LineCallback: handleCallback failed - ' . ($result['error'] ?? 'unknown'));
            $this->renderError($result['error'] ?? '登入失敗');
            return;
        }
        $loginResult = $this->authHandler->loginOrRegister($result['profile']);
        if (!$loginResult['success']) {
            error_log('MYGO LineCallback: loginOrRegister failed - ' . ($loginResult['error'] ?? 'unknown'));
            $this->renderError($loginResult['error'] ?? '建立帳號失敗');
            return;
        }
        $this->renderSuccess($result['profile'], $loginResult);
    }

    private function renderPage(string $authUrl, string $redirectUri, bool $isLoggedIn, $currentUser, string $lineUid, string $lineName, string $mygoRole, array $roleNames): void
    {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>LINE 登入 - <?php bloginfo('name'); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f7; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
                .container { background: white; border-radius: 20px; padding: 40px; max-width: 500px; width: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
                h1 { font-size: 24px; font-weight: 600; text-align: center; margin-bottom: 10px; }
                .subtitle { color: #666; text-align: center; margin-bottom: 30px; }
                .line-btn { display: flex; align-items: center; justify-content: center; gap: 10px; background: #06C755; color: white; text-decoration: none; padding: 14px 24px; border-radius: 12px; font-size: 16px; font-weight: 500; }
                .line-btn:hover { background: #05b34d; }
                .user-info, .debug-info { background: #f5f5f7; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
                .user-info h3, .debug-info h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
                .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e5e5; }
                .info-row:last-child { border-bottom: none; }
                .info-label { color: #666; font-size: 14px; }
                .info-value { font-weight: 500; font-size: 14px; word-break: break-all; max-width: 280px; text-align: right; }
                .status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
                .status-linked { background: #d4edda; color: #155724; }
                .status-not-linked { background: #fff3cd; color: #856404; }
                .back-link { display: block; text-align: center; margin-top: 20px; color: #007aff; text-decoration: none; }
                .copy-btn { background: #007aff; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px; cursor: pointer; margin-left: 8px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>LINE 登入</h1>
                <p class="subtitle">使用 LINE 帳號登入並綁定</p>
                
                <div class="debug-info">
                    <h3>LINE Developers 設定資訊</h3>
                    <div class="info-row">
                        <span class="info-label">Callback URL</span>
                        <span class="info-value">
                            <code id="callback-url"><?php echo esc_html($redirectUri); ?></code>
                            <button class="copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('callback-url').textContent)">複製</button>
                        </span>
                    </div>
                    <p style="font-size: 12px; color: #999; margin-top: 10px;">請將上方 URL 完整複製到 LINE Developers Console 的 Callback URL 欄位</p>
                </div>
                
                <?php if ($isLoggedIn): ?>
                <div class="debug-info" style="margin-top: 15px;">
                    <h3>開發測試：角色切換</h3>
                    <form method="post" style="margin-top: 10px;">
                        <?php wp_nonce_field('mygo_change_role'); ?>
                        <select name="mygo_new_role" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #ddd; width: 100%; margin-bottom: 10px;">
                            <option value="buyer" <?php selected($mygoRole, 'buyer'); ?>>買家 (Buyer)</option>
                            <option value="seller" <?php selected($mygoRole, 'seller'); ?>>賣家 (Seller)</option>
                            <option value="helper" <?php selected($mygoRole, 'helper'); ?>>小幫手 (Helper)</option>
                            <option value="admin" <?php selected($mygoRole, 'admin'); ?>>管理員 (Admin)</option>
                        </select>
                        <button type="submit" name="mygo_change_role" value="1" style="width: 100%; padding: 10px; background: #007aff; color: white; border: none; border-radius: 8px; cursor: pointer;">切換角色</button>
                    </form>
                    <p style="font-size: 12px; color: #999; margin-top: 10px;">切換為 Admin 後可存取 WordPress 後台</p>
                </div>
                <?php endif; ?>
                
                <?php if ($isLoggedIn): ?>
                <div class="user-info">
                    <h3>目前登入狀態</h3>
                    <div class="info-row">
                        <span class="info-label">WordPress 帳號</span>
                        <span class="info-value"><?php echo esc_html($currentUser->display_name); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">LINE 綁定</span>
                        <span class="info-value">
                            <?php if ($lineUid): ?>
                            <span class="status-badge status-linked">已綁定</span>
                            <?php else: ?>
                            <span class="status-badge status-not-linked">未綁定</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($lineUid): ?>
                    <div class="info-row">
                        <span class="info-label">LINE 名稱</span>
                        <span class="info-value"><?php echo esc_html($lineName); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span class="info-label">MYGO 角色</span>
                        <span class="info-value"><?php echo esc_html($roleNames[$mygoRole] ?? '未設定'); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <a href="<?php echo esc_url($authUrl); ?>" class="line-btn">
                    <?php echo $lineUid ? '重新綁定 LINE' : '使用 LINE 登入'; ?>
                </a>
                
                <a href="<?php echo esc_url(home_url()); ?>" class="back-link">返回首頁</a>
            </div>
        </body>
        </html>
        <?php
    }


    private function renderSuccess(array $profile, array $loginResult): void
    {
        $currentUser = wp_get_current_user();
        $mygoRole = get_user_meta($currentUser->ID, '_mygo_role', true);
        $roleNames = ['buyer' => '買家', 'seller' => '賣家', 'helper' => '小幫手', 'admin' => '管理員'];
        $needsProfile = $loginResult['needs_profile'] ?? false;
        
        // 如果需要完善資料，載入 Modal 相關資源
        if ($needsProfile) {
            wp_enqueue_script('mygo-public', plugin_dir_url(dirname(__DIR__)) . 'assets/js/public.js', ['jquery'], '1.0.0', true);
            wp_localize_script('mygo-public', 'mygoAjax', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mygo_ajax'),
                'shippingMethods' => (new \Mygo\Services\UserProfileValidator())->getAvailableShippingMethods(),
            ]);
        }
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>登入成功 - <?php bloginfo('name'); ?></title>
            <?php if ($needsProfile): ?>
            <?php wp_print_scripts('mygo-public'); ?>
            <?php endif; ?>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f7; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
                .container { background: white; border-radius: 20px; padding: 40px; max-width: 450px; width: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
                .success-icon { width: 60px; height: 60px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #28a745; font-size: 30px; }
                h1 { font-size: 24px; font-weight: 600; text-align: center; margin-bottom: 10px; color: #28a745; }
                .subtitle { color: #666; text-align: center; margin-bottom: 30px; }
                .profile-card { display: flex; align-items: center; gap: 15px; background: #f5f5f7; border-radius: 12px; padding: 15px; margin-bottom: 20px; }
                .profile-avatar { width: 50px; height: 50px; border-radius: 50%; background: #ddd; object-fit: cover; }
                .profile-name { font-weight: 600; font-size: 16px; }
                .profile-role { color: #666; font-size: 14px; }
                .info-section { background: #f5f5f7; border-radius: 12px; padding: 15px; margin-bottom: 20px; }
                .info-section h3 { font-size: 14px; color: #666; margin-bottom: 10px; }
                .info-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e5e5; }
                .info-row:last-child { border-bottom: none; }
                .info-label { color: #666; font-size: 14px; }
                .info-value { font-weight: 500; font-size: 14px; word-break: break-all; max-width: 200px; text-align: right; }
                .btn { display: block; text-align: center; padding: 14px 24px; border-radius: 12px; font-size: 16px; font-weight: 500; text-decoration: none; margin-bottom: 10px; }
                .btn-primary { background: #007aff; color: white; }
                .btn-secondary { background: #f5f5f7; color: #333; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="success-icon">✓</div>
                <h1><?php echo $loginResult['is_new'] ? '註冊成功！' : '登入成功！'; ?></h1>
                <p class="subtitle">LINE 帳號已成功綁定</p>
                
                <div class="profile-card">
                    <?php if (!empty($profile['pictureUrl'])): ?>
                    <img src="<?php echo esc_url($profile['pictureUrl']); ?>" alt="" class="profile-avatar">
                    <?php else: ?>
                    <div class="profile-avatar"></div>
                    <?php endif; ?>
                    <div>
                        <div class="profile-name"><?php echo esc_html($profile['displayName']); ?></div>
                        <div class="profile-role"><?php echo esc_html($roleNames[$mygoRole] ?? '買家'); ?></div>
                    </div>
                </div>
                
                <div class="info-section">
                    <h3>LINE 帳號資訊</h3>
                    <div class="info-row">
                        <span class="info-label">LINE UID</span>
                        <span class="info-value" style="font-size: 11px;"><?php echo esc_html($profile['userId']); ?></span>
                    </div>
                    <?php if (!empty($profile['email'])): ?>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo esc_html($profile['email']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <span class="info-label">WordPress ID</span>
                        <span class="info-value"><?php echo esc_html($loginResult['user_id']); ?></span>
                    </div>
                </div>
                
                <a href="<?php echo esc_url(home_url()); ?>" class="btn btn-primary">前往首頁</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mygo-settings')); ?>" class="btn btn-secondary">前往後台設定</a>
            </div>
            
            <script>
            // 自動儲存預先填寫的資料並導向
            document.addEventListener('DOMContentLoaded', function() {
                // 從 sessionStorage 讀取資料
                const preLoginData = sessionStorage.getItem('mygo_pre_login_data');
                const redirectUrl = sessionStorage.getItem('mygo_redirect_after_login') || '<?php echo esc_js(home_url('/portal/')); ?>';
                
                if (preLoginData) {
                    try {
                        const data = JSON.parse(preLoginData);
                        
                        // 自動儲存資料
                        const formData = new FormData();
                        formData.append('action', 'mygo_save_profile');
                        formData.append('nonce', '<?php echo wp_create_nonce('mygo_ajax'); ?>');
                        formData.append('email', data.email || '');
                        formData.append('phone', data.phone);
                        formData.append('address', data.address);
                        formData.append('shipping_method', data.shipping_method);
                        
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                console.log('個人資料已自動儲存');
                                // 清除 sessionStorage
                                sessionStorage.removeItem('mygo_pre_login_data');
                                sessionStorage.removeItem('mygo_redirect_after_login');
                                
                                // 延遲 1 秒後導向
                                setTimeout(function() {
                                    window.location.href = redirectUrl;
                                }, 1000);
                            } else {
                                console.error('儲存失敗:', result.data);
                                // 即使儲存失敗也導向
                                setTimeout(function() {
                                    window.location.href = redirectUrl;
                                }, 1500);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // 發生錯誤也導向
                            setTimeout(function() {
                                window.location.href = redirectUrl;
                            }, 1500);
                        });
                    } catch (e) {
                        console.error('解析資料失敗:', e);
                        // 解析失敗也導向
                        window.location.href = redirectUrl;
                    }
                } else {
                    // 沒有預先填寫的資料，直接導向
                    setTimeout(function() {
                        window.location.href = redirectUrl;
                    }, 1500);
                }
            });
            </script>
        </body>
        </html>
        <?php
    }

    private function renderError(string $message): void
    {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>登入失敗 - <?php bloginfo('name'); ?></title>
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f7; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
                .container { background: white; border-radius: 20px; padding: 40px; max-width: 400px; width: 100%; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center; }
                .error-icon { width: 60px; height: 60px; background: #f8d7da; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #dc3545; font-size: 30px; }
                h1 { font-size: 24px; font-weight: 600; margin-bottom: 10px; color: #dc3545; }
                .message { color: #666; margin-bottom: 30px; }
                .btn { display: inline-block; padding: 14px 24px; border-radius: 12px; font-size: 16px; font-weight: 500; text-decoration: none; background: #007aff; color: white; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="error-icon">✕</div>
                <h1>登入失敗</h1>
                <p class="message"><?php echo esc_html($message); ?></p>
                <a href="<?php echo esc_url(home_url('/mygo-line-login/')); ?>" class="btn">重新嘗試</a>
            </div>
        </body>
        </html>
        <?php
    }
}
