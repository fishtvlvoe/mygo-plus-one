<?php
defined('ABSPATH') or die;

$current_tab = $current_tab ?? 'all';
$tabs = [
    'all' => '全部訂單',
    'pending' => '待處理',
    'arrived' => '已到貨',
    'completed' => '已完成',
];
?>
<div class="mygo-admin-wrap">
    <?php include MYGO_PLUGIN_DIR . 'admin/views/partials/top-nav.php'; ?>
    
    <div class="mygo-page-header">
        <div class="mygo-page-title" style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1>訂單管理</h1>
                <p>管理 +1 下單的訂單</p>
            </div>
            <div style="display: flex; gap: 16px; text-align: right;">
                <div>
                    <div style="font-size: 12px; color: #6b7280;">總金額</div>
                    <div style="font-size: 18px; font-weight: 600; color: #10b981;">NT$<?php echo number_format($total_amount ?? 0); ?></div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #6b7280;">訂單數</div>
                    <div style="font-size: 18px; font-weight: 600; color: #3b82f6;"><?php echo esc_html($total_orders ?? 0); ?></div>
                </div>
            </div>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div class="mygo-page-tabs">
                <?php foreach ($tabs as $tab_key => $tab_label) : ?>
                    <a href="<?php echo admin_url('admin.php?page=mygo-orders&tab=' . $tab_key); ?>" 
                       class="mygo-page-tab <?php echo $current_tab === $tab_key ? 'active' : ''; ?>">
                        <?php echo esc_html($tab_label); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo admin_url('admin.php?page=mygo-orders&action=export&tab=' . $current_tab . '&s=' . urlencode($search ?? '')); ?>" 
               class="mygo-btn mygo-btn-secondary" style="margin-left: 16px;">
                📊 匯出 CSV
            </a>
        </div>
    <div class="mygo-content">
        <div class="mygo-card">
            <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; gap: 12px;">
                <input type="text" id="mygo-order-search" placeholder="搜尋訂單編號、買家名稱或商品..." value="<?php echo esc_attr($search ?? ''); ?>" 
                       style="flex: 1; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px;">
                <button class="mygo-btn-primary" id="mygo-search-btn">搜尋</button>
            </div>
            
            <?php if (!empty($orders)) : ?>
            <div style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb; display: flex; gap: 12px; align-items: center;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" id="mygo-select-all-orders" style="width: 18px; height: 18px; cursor: pointer;">
                    <span style="font-size: 14px; color: #6b7280;">全選</span>
                </label>
                <button type="button" id="mygo-bulk-delete-orders" class="mygo-btn mygo-btn-sm" style="background: #dc2626; color: white;" disabled>
                    批次刪除 (<span id="mygo-selected-count">0</span>)
                </button>
            </div>
            <?php endif; ?>
            
            <table class="mygo-table">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>訂單編號</th>
                        <th>買家</th>
                        <th>商品</th>
                        <th>金額</th>
                        <th>數量</th>
                        <th>下單時間</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)) : ?>
                        <?php foreach ($orders as $order) : ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="mygo-order-checkbox" value="<?php echo esc_attr($order['id'] ?? 0); ?>" style="width: 18px; height: 18px; cursor: pointer;">
                                </td>
                                <td><strong>#<?php echo esc_html($order['id'] ?? ''); ?></strong></td>
                                <td><?php echo esc_html($order['buyer_name'] ?? ''); ?></td>
                                <td><?php echo esc_html($order['product_name'] ?? ''); ?></td>
                                <td>NT$ <?php echo number_format($order['total'] ?? 0); ?></td>
                                <td><?php echo esc_html($order['quantity'] ?? 1); ?></td>
                                <td><?php echo esc_html(date_i18n('Y/m/d H:i', strtotime($order['created_at'] ?? ''))); ?></td>
                                <td>
                                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                        <?php 
                                        $statuses = $order['statuses'] ?? [];
                                        $statusLabels = [
                                            'arrived' => ['到貨', '未到'],
                                            'paid' => ['已付', '未付'],
                                            'shipped' => ['已寄', '未寄'],
                                        ];
                                        foreach ($statusLabels as $key => $labels) :
                                            $isActive = $statuses[$key] ?? false;
                                            $label = $isActive ? $labels[0] : $labels[1];
                                            $badgeClass = $isActive ? 'mygo-badge-success' : 'mygo-badge-warning';
                                        ?>
                                            <span class="mygo-badge <?php echo $badgeClass; ?>"><?php echo esc_html($label); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=mygo-orders&action=view&id=' . ($order['id'] ?? 0)); ?>" class="mygo-btn mygo-btn-sm mygo-btn-secondary">查看</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="9">
                                <div class="mygo-empty">
                                    <div class="mygo-empty-icon">🛒</div>
                                    <h3>目前沒有訂單</h3>
                                    <p>買家在社群貼文下 +1 後會產生訂單</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
