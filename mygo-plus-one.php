<?php
defined('ABSPATH') or die;

/**
 * Plugin Name: MYGO +1
 * Description: 整合 LINE 官方帳號、FluentCart、FluentCommunity 與 FluentCRM，支援社群 +1 關鍵字下單功能
 * Version: 1.0.0
 * Author: MYGO Team
 * Author URI: https://mygo.tw
 * Plugin URI: https://mygo.tw
 * License: GPLv2 or later
 * Text Domain: mygo-plus-one
 * Domain Path: /language
 * Requires PHP: 7.4
 * Requires at least: 5.6
 */

if (!defined('MYGO_PLUGIN_VERSION')) {
    define('MYGO_PLUGIN_VERSION', '1.0.0');
    define('MYGO_PLUGIN_DIR', plugin_dir_path(__FILE__));
    define('MYGO_PLUGIN_URL', plugin_dir_url(__FILE__));
    define('MYGO_PLUGIN_FILE', __FILE__);
    define('MYGO_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

// 自動載入
spl_autoload_register(function ($class) {
    $prefix = 'Mygo\\';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    
    // 檢查不同目錄
    $directories = [
        MYGO_PLUGIN_DIR . 'includes/',
        MYGO_PLUGIN_DIR . 'admin/',
        MYGO_PLUGIN_DIR . 'public/',
    ];

    foreach ($directories as $base_dir) {
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// 啟用外掛
register_activation_hook(__FILE__, function () {
    require_once MYGO_PLUGIN_DIR . 'includes/Core/Activator.php';
    \Mygo\Core\Activator::activate();
});

// 停用外掛
register_deactivation_hook(__FILE__, function () {
    require_once MYGO_PLUGIN_DIR . 'includes/Core/Activator.php';
    \Mygo\Core\Activator::deactivate();
});

// 初始化外掛
add_action('plugins_loaded', function () {
    // 檢查相依外掛
    $has_fluentcart = class_exists('FluentCart\App\App');
    
    if (!$has_fluentcart) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning"><p>';
            echo esc_html__('MYGO +1 建議安裝 FluentCart 外掛以獲得完整功能。', 'mygo-plus-one');
            echo '</p></div>';
        });
    }

    // 載入核心類別
    require_once MYGO_PLUGIN_DIR . 'includes/Core/Plugin.php';
    \Mygo\Core\Plugin::getInstance()->init();

    // 註冊公開頁面
    require_once MYGO_PLUGIN_DIR . 'public/ProductPreview.php';
    require_once MYGO_PLUGIN_DIR . 'public/UserProfileModal.php';
    require_once MYGO_PLUGIN_DIR . 'public/LineLoginPage.php';
    require_once MYGO_PLUGIN_DIR . 'public/TestProfilePage.php';
    \Mygo\PublicPages\ProductPreview::register();
    \Mygo\PublicPages\UserProfileModal::register();
    $lineLoginPage = new \Mygo\PublicPages\LineLoginPage();
    $lineLoginPage->register();
    $testProfilePage = new \Mygo\PublicPages\TestProfilePage();
    $testProfilePage->register();
}, 20);
