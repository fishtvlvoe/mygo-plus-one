<?php defined('ABSPATH') or die; ?>

<div class="mygo-admin-wrap">
    <?php include MYGO_PLUGIN_DIR . 'admin/views/partials/top-nav.php'; ?>
    
    <div class="mygo-welcome">
        <div class="mygo-welcome-avatar">
            <?php echo mb_substr(wp_get_current_user()->display_name, 0, 1); ?>
        </div>
        <div class="mygo-welcome-text">
            <h2>歡迎回來，<?php echo esc_html(wp_get_current_user()->display_name); ?>！</h2>
            <p>MYGO +1 管理後台</p>
        </div>
    </div>

    <div class="mygo-content">
        <div class="mygo-stats-grid">
            <div class="mygo-stat-card">
                <div class="stat-icon blue">📦</div>
                <div class="stat-label">商品總數</div>
                <div class="stat-value"><?php echo esc_html($stats['products'] ?? 0); ?></div>
            </div>
            <div class="mygo-stat-card">
                <div class="stat-icon green">🛒</div>
                <div class="stat-label">訂單數量</div>
                <div class="stat-value"><?php echo esc_html($stats['orders'] ?? 0); ?></div>
            </div>
            <div class="mygo-stat-card">
                <div class="stat-icon purple">💰</div>
                <div class="stat-label">總營收</div>
                <div class="stat-value">NT$<?php echo number_format($stats['revenue'] ?? 0); ?></div>
            </div>
            <div class="mygo-stat-card">
                <div class="stat-icon orange">👥</div>
                <div class="stat-label">LINE 使用者</div>
                <div class="stat-value"><?php echo esc_html($stats['users'] ?? 0); ?></div>
            </div>
        </div>

        <div class="mygo-grid mygo-grid-2">
            <div class="mygo-card">
                <div class="mygo-card-header">
                    <h3>快速操作</h3>
                </div>
                <div class="mygo-card-body">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                        <a href="<?php echo admin_url('admin.php?page=mygo-products'); ?>" class="mygo-btn mygo-btn-secondary" style="justify-content: center;">
                            📦 商品管理
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=mygo-orders'); ?>" class="mygo-btn mygo-btn-secondary" style="justify-content: center;">
                            🛒 訂單管理
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=mygo-users'); ?>" class="mygo-btn mygo-btn-secondary" style="justify-content: center;">
                            👥 使用者管理
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=mygo-settings'); ?>" class="mygo-btn mygo-btn-secondary" style="justify-content: center;">
                            ⚙️ 設定
                        </a>
                    </div>
                </div>
            </div>

            <div class="mygo-card">
                <div class="mygo-card-header">
                    <h3>系統狀態</h3>
                </div>
                <div class="mygo-card-body">
                    <?php
                    $lineToken = get_option('mygo_line_channel_access_token', '');
                    $lineLoginId = get_option('mygo_line_login_channel_id', '');
                    ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #6b7280;">LINE Messaging API</span>
                            <?php if ($lineToken): ?>
                            <span class="mygo-badge mygo-badge-success">已設定</span>
                            <?php else: ?>
                            <span class="mygo-badge mygo-badge-warning">未設定</span>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #6b7280;">LINE Login</span>
                            <?php if ($lineLoginId): ?>
                            <span class="mygo-badge mygo-badge-success">已設定</span>
                            <?php else: ?>
                            <span class="mygo-badge mygo-badge-warning">未設定</span>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #6b7280;">FluentCart</span>
                            <?php if (class_exists('FluentCart\App\App')): ?>
                            <span class="mygo-badge mygo-badge-success">已啟用</span>
                            <?php else: ?>
                            <span class="mygo-badge mygo-badge-danger">未安裝</span>
                            <?php endif; ?>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #6b7280;">FluentCommunity</span>
                            <?php if (defined('FLUENT_COMMUNITY_PLUGIN_VERSION')): ?>
                            <span class="mygo-badge mygo-badge-success">已啟用</span>
                            <?php else: ?>
                            <span class="mygo-badge mygo-badge-danger">未安裝</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
