<?php
defined('ABSPATH') or die;

$current_page = $_GET['page'] ?? 'mygo-plus-one';
$menu_items = [
    'mygo-plus-one' => ['label' => '總覽', 'icon' => '📊'],
    'mygo-products' => ['label' => '商品', 'icon' => '📦'],
    'mygo-orders' => ['label' => '訂單', 'icon' => '🛒'],
    'mygo-users' => ['label' => '使用者', 'icon' => '👥'],
    'mygo-settings' => ['label' => '設定', 'icon' => '⚙️'],
];
?>
<div class="mygo-top-nav">
    <a href="<?php echo admin_url('admin.php?page=mygo-plus-one'); ?>" class="mygo-top-nav-logo">
        <div class="mygo-top-nav-logo-icon">🛍️</div>
        <span class="mygo-top-nav-logo-text">MYGO +1</span>
    </a>
    
    <nav class="mygo-top-nav-menu">
        <?php foreach ($menu_items as $page_slug => $item): ?>
        <div class="mygo-top-nav-item">
            <a href="<?php echo admin_url('admin.php?page=' . $page_slug); ?>" 
               class="mygo-top-nav-link <?php echo $current_page === $page_slug ? 'active' : ''; ?>">
                <span><?php echo $item['icon']; ?></span>
                <span><?php echo esc_html($item['label']); ?></span>
            </a>
        </div>
        <?php endforeach; ?>
    </nav>
    
    <div class="mygo-top-nav-actions">
        <a href="<?php echo esc_url(home_url('/mygo-line-login/')); ?>" class="mygo-btn mygo-btn-sm mygo-btn-secondary" target="_blank">
            LINE 登入測試
        </a>
    </div>
</div>
