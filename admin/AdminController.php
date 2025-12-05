<?php

namespace Mygo;

defined('ABSPATH') or die;

use Mygo\Services\FluentCartService;
use Mygo\Services\RolePermissionService;

/**
 * Admin Controller
 * 
 * 處理後台頁面邏輯
 */
class AdminController
{
    private FluentCartService $cartService;
    private RolePermissionService $permissionService;

    public function __construct()
    {
        $this->cartService = new FluentCartService();
        $this->permissionService = new RolePermissionService();
    }

    /**
     * 註冊設定
     */
    public static function registerSettings(): void
    {
        register_setting('mygo_settings', 'mygo_line_channel_access_token');
        register_setting('mygo_settings', 'mygo_line_channel_secret');
        register_setting('mygo_settings', 'mygo_line_login_channel_id');
        register_setting('mygo_settings', 'mygo_line_login_channel_secret');
        register_setting('mygo_settings', 'mygo_default_space_id');
        register_setting('mygo_settings', 'mygo_default_space_slug');
        register_setting('mygo_settings', 'mygo_login_redirect_url');
    }

    /**
     * 渲染儀表板
     */
    public function renderDashboard(): void
    {
        $stats = $this->getDashboardStats();
        include MYGO_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * 渲染商品列表
     */
    public function renderProducts(): void
    {
        $action = sanitize_text_field($_GET['action'] ?? '');
        
        if ($action === 'edit') {
            $this->renderProductEdit();
            return;
        }
        
        $search = sanitize_text_field($_GET['s'] ?? '');
        $filter_status = sanitize_text_field($_GET['status'] ?? '');
        $page = max(1, intval($_GET['paged'] ?? 1));
        $per_page = 20;

        $products = $this->getProducts($search, $filter_status, $page, $per_page);
        $pagination = $this->getPagination($page, $per_page, $products['total'] ?? 0);

        $products = $products['items'] ?? [];
        include MYGO_PLUGIN_DIR . 'admin/views/products.php';
    }
    
    /**
     * 渲染商品編輯頁面
     */
    public function renderProductEdit(): void
    {
        $productId = intval($_GET['id'] ?? 0);
        $message = '';
        $messageType = '';
        
        if (!$productId) {
            wp_die(__('商品不存在', 'mygo-plus-one'));
        }
        
        // 處理表單提交
        $redirectUrl = '';
        if (isset($_POST['mygo_save_product']) && wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mygo_edit_product')) {
            $result = $this->saveProduct($productId, $_POST);
            if ($result['success']) {
                // 儲存成功，設定重導向 URL（使用 JavaScript 重導向避免 headers already sent 問題）
                $redirectUrl = admin_url('admin.php?page=mygo-products&updated=1');
            } else {
                $message = '更新失敗：' . $result['error'];
                $messageType = 'error';
            }
        }
        
        $product = $this->getProductDetail($productId);
        
        if (!$product) {
            wp_die(__('商品不存在', 'mygo-plus-one'));
        }
        
        include MYGO_PLUGIN_DIR . 'admin/views/product-edit.php';
    }
    
    /**
     * 取得商品詳情
     */
    private function getProductDetail(int $productId): ?array
    {
        global $wpdb;
        
        $post = get_post($productId);
        if (!$post || $post->post_type !== 'fluent-products') {
            return null;
        }
        
        // 從 FluentCart 表取得價格和庫存
        $variation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}fct_product_variations WHERE post_id = %d LIMIT 1",
            $productId
        ), ARRAY_A);
        
        $details = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}fct_product_details WHERE post_id = %d LIMIT 1",
            $productId
        ), ARRAY_A);
        
        // 取得價格（優先從 variation，否則從 details）
        $priceCents = 0;
        if (!empty($variation['item_price'])) {
            $priceCents = floatval($variation['item_price']);
        } elseif (!empty($details['min_price'])) {
            $priceCents = floatval($details['min_price']);
        }
        
        // 取得庫存（優先從 variation，否則從 details 的 other_info）
        $stockQuantity = 0;
        $stockStatus = 'out-of-stock';
        
        if (!empty($variation)) {
            $stockQuantity = intval($variation['available'] ?? $variation['total_stock'] ?? 0);
            $stockStatus = $variation['stock_status'] ?? 'out-of-stock';
        }
        
        // 如果 variation 沒有庫存資料，從 details 的 other_info 取得
        if ($stockQuantity === 0 && !empty($details['other_info'])) {
            $otherInfo = json_decode($details['other_info'], true);
            if (!empty($otherInfo['stock_quantity'])) {
                $stockQuantity = intval($otherInfo['stock_quantity']);
                $stockStatus = $stockQuantity > 0 ? 'in-stock' : 'out-of-stock';
            }
        }
        
        // 如果還是沒有，用 details 的 stock_availability
        if (empty($stockStatus) || $stockStatus === 'out-of-stock') {
            $stockStatus = $details['stock_availability'] ?? 'out-of-stock';
        }
        
        // 取得圖片 - 先從 post thumbnail，再從 FluentCart media
        $thumbnailId = get_post_thumbnail_id($productId);
        $imageUrl = '';
        
        if ($thumbnailId) {
            $imageUrl = wp_get_attachment_image_url($thumbnailId, 'medium');
        } elseif (!empty($details['default_media'])) {
            // FluentCart 可能把圖片存在 default_media
            $mediaData = json_decode($details['default_media'], true);
            if (!empty($mediaData['url'])) {
                $imageUrl = $mediaData['url'];
            } elseif (is_numeric($details['default_media'])) {
                $imageUrl = wp_get_attachment_image_url(intval($details['default_media']), 'medium');
            }
        }
        
        return [
            'id' => $productId,
            'title' => $post->post_title,
            'description' => $post->post_content,
            'price' => $priceCents / 100,
            'stock_quantity' => $stockQuantity,
            'stock_status' => $stockStatus,
            'image_url' => $imageUrl,
            'thumbnail_id' => $thumbnailId,
            'arrival_date' => get_post_meta($productId, '_mygo_arrival_date', true),
            'preorder_date' => get_post_meta($productId, '_mygo_preorder_date', true),
            'line_user_id' => get_post_meta($productId, '_mygo_line_user_id', true),
            'created_at' => $post->post_date,
            'variation_id' => $variation['id'] ?? null,
            'details_id' => $details['id'] ?? null,
        ];
    }
    
    /**
     * 儲存商品
     */
    private function saveProduct(int $productId, array $data): array
    {
        global $wpdb;
        
        try {
            // 更新 post
            $postData = [
                'ID' => $productId,
                'post_title' => sanitize_text_field($data['title'] ?? ''),
                'post_content' => sanitize_textarea_field($data['description'] ?? ''),
            ];
            
            $result = wp_update_post($postData, true);
            if (is_wp_error($result)) {
                return ['success' => false, 'error' => $result->get_error_message()];
            }
            
            // 更新價格和庫存（FluentCart 使用分為單位）
            $price = intval(floatval($data['price'] ?? 0) * 100);
            $stock = intval($data['stock_quantity'] ?? 0);
            $stockStatus = $stock > 0 ? 'in-stock' : 'out-of-stock';
            
            // 檢查是否有 variation 記錄
            $variationExists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}fct_product_variations WHERE post_id = %d",
                $productId
            ));
            
            if ($variationExists) {
                // 更新 fct_product_variations
                $wpdb->update(
                    $wpdb->prefix . 'fct_product_variations',
                    [
                        'variation_title' => sanitize_text_field($data['title'] ?? ''),
                        'item_price' => $price,
                        'total_stock' => $stock,
                        'available' => $stock,
                        'stock_status' => $stockStatus,
                        'updated_at' => current_time('mysql'),
                    ],
                    ['post_id' => $productId]
                );
            } else {
                // 建立新的 variation 記錄
                $wpdb->insert(
                    $wpdb->prefix . 'fct_product_variations',
                    [
                        'post_id' => $productId,
                        'variation_title' => sanitize_text_field($data['title'] ?? ''),
                        'variation_identifier' => 'MYGO-' . $productId,
                        'manage_stock' => 1,
                        'stock_status' => $stockStatus,
                        'total_stock' => $stock,
                        'available' => $stock,
                        'item_status' => 'active',
                        'item_price' => $price,
                        'created_at' => current_time('mysql'),
                        'updated_at' => current_time('mysql'),
                    ]
                );
            }
            
            // 更新 fct_product_details
            $wpdb->update(
                $wpdb->prefix . 'fct_product_details',
                [
                    'min_price' => $price,
                    'max_price' => $price,
                    'stock_availability' => $stockStatus,
                    'other_info' => json_encode(['stock_quantity' => $stock]),
                    'updated_at' => current_time('mysql'),
                ],
                ['post_id' => $productId]
            );
            
            // 更新 MYGO meta（即使是空值也要更新，允許清除日期）
            update_post_meta($productId, '_mygo_arrival_date', sanitize_text_field($data['arrival_date'] ?? ''));
            update_post_meta($productId, '_mygo_preorder_date', sanitize_text_field($data['preorder_date'] ?? ''));
            
            // 更新商品圖片
            $thumbnailId = isset($data['thumbnail_id']) ? trim($data['thumbnail_id']) : '';
            if (!empty($thumbnailId) && is_numeric($thumbnailId)) {
                set_post_thumbnail($productId, intval($thumbnailId));
            } elseif ($thumbnailId === '') {
                delete_post_thumbnail($productId);
            }
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 渲染訂單列表
     */
    public function renderOrders(): void
    {
        $action = sanitize_text_field($_GET['action'] ?? '');
        
        if ($action === 'view') {
            $this->renderOrderDetail();
            return;
        }
        
        // 匯出功能已移到 admin_init hook 處理
        if ($action === 'export') {
            return;
        }

        $current_tab = sanitize_text_field($_GET['tab'] ?? 'all');
        $search = sanitize_text_field($_GET['s'] ?? '');
        $page = max(1, intval($_GET['paged'] ?? 1));
        $per_page = 20;

        $ordersData = $this->getOrders($current_tab, $search, $page, $per_page);
        $orders = $ordersData['items'] ?? [];
        $total_orders = $ordersData['total'] ?? 0;
        $total_amount = $ordersData['total_amount'] ?? 0;

        include MYGO_PLUGIN_DIR . 'admin/views/orders.php';
    }

    /**
     * 渲染訂單詳情
     */
    public function renderOrderDetail(): void
    {
        $orderId = intval($_GET['id'] ?? 0);
        
        if (!$orderId) {
            wp_die(__('訂單不存在', 'mygo-plus-one'));
        }

        $order = $this->getOrderDetail($orderId);
        
        if (!$order) {
            wp_die(__('訂單不存在', 'mygo-plus-one'));
        }

        include MYGO_PLUGIN_DIR . 'admin/views/order-detail.php';
    }

    /**
     * 渲染設定頁面
     */
    public function renderSettings(): void
    {
        include MYGO_PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * 渲染使用者管理頁面
     */
    public function renderUsers(): void
    {
        // 處理角色變更
        if (isset($_POST['mygo_update_role']) && wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mygo_update_user_role')) {
            $userId = intval($_POST['user_id'] ?? 0);
            $newRole = sanitize_text_field($_POST['new_role'] ?? '');
            if ($userId && in_array($newRole, ['buyer', 'seller', 'helper', 'admin'])) {
                update_user_meta($userId, '_mygo_role', $newRole);
                if ($newRole === 'admin') {
                    $user = new \WP_User($userId);
                    $user->set_role('administrator');
                }
            }
        }

        // 處理聯絡資訊更新
        if (isset($_POST['mygo_update_contact']) && wp_verify_nonce($_POST['_wpnonce'] ?? '', 'mygo_update_contact')) {
            $userId = intval($_POST['user_id'] ?? 0);
            if ($userId) {
                $phone = sanitize_text_field($_POST['phone'] ?? '');
                $address = sanitize_text_field($_POST['address'] ?? '');
                $shippingMethod = sanitize_text_field($_POST['shipping_method'] ?? '');
                
                update_user_meta($userId, '_mygo_phone', $phone);
                update_user_meta($userId, '_mygo_address', $address);
                update_user_meta($userId, '_mygo_shipping_method', $shippingMethod);
            }
        }

        $filterRole = sanitize_text_field($_GET['role'] ?? '');
        $users = $this->getLineUsers($filterRole);
        include MYGO_PLUGIN_DIR . 'admin/views/users.php';
    }

    /**
     * 取得綁定 LINE 的使用者
     */
    private function getLineUsers(string $filterRole = ''): array
    {
        global $wpdb;
        
        $query = "
            SELECT u.ID, u.display_name, u.user_email, u.user_registered,
                   line_uid.meta_value as line_uid,
                   line_name.meta_value as line_name,
                   mygo_role.meta_value as mygo_role,
                   phone.meta_value as phone,
                   address.meta_value as address,
                   shipping.meta_value as shipping_method
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->usermeta} line_uid ON u.ID = line_uid.user_id AND line_uid.meta_key = '_mygo_line_uid'
            LEFT JOIN {$wpdb->usermeta} line_name ON u.ID = line_name.user_id AND line_name.meta_key = '_mygo_line_name'
            LEFT JOIN {$wpdb->usermeta} mygo_role ON u.ID = mygo_role.user_id AND mygo_role.meta_key = '_mygo_role'
            LEFT JOIN {$wpdb->usermeta} phone ON u.ID = phone.user_id AND phone.meta_key = '_mygo_phone'
            LEFT JOIN {$wpdb->usermeta} address ON u.ID = address.user_id AND address.meta_key = '_mygo_address'
            LEFT JOIN {$wpdb->usermeta} shipping ON u.ID = shipping.user_id AND shipping.meta_key = '_mygo_shipping_method'
        ";

        if ($filterRole) {
            $query .= $wpdb->prepare(" WHERE mygo_role.meta_value = %s", $filterRole);
        }

        $query .= " ORDER BY u.user_registered DESC";

        return $wpdb->get_results($query, ARRAY_A) ?: [];
    }

    /**
     * 取得儀表板統計
     */
    private function getDashboardStats(): array
    {
        global $wpdb;

        return [
            'products' => $this->countProducts(),
            'orders' => $this->countOrders(),
            'revenue' => $this->calculateRevenue(),
            'users' => $this->countUsers(),
        ];
    }

    /**
     * 取得商品列表
     */
    private function getProducts(string $search, string $status, int $page, int $perPage): array
    {
        global $wpdb;
        
        $offset = ($page - 1) * $perPage;
        
        // 基本查詢條件
        $where = "p.post_type = 'fluent-products' AND p.post_status = 'publish'";
        $whereParams = [];
        
        // 搜尋
        if (!empty($search)) {
            $where .= " AND p.post_title LIKE %s";
            $whereParams[] = '%' . $wpdb->esc_like($search) . '%';
        }
        
        // 庫存狀態過濾
        if (!empty($status)) {
            $stockStatus = $status === 'in_stock' ? 'in-stock' : 'out-of-stock';
            $where .= " AND v.stock_status = %s";
            $whereParams[] = $stockStatus;
        }
        
        // 查詢商品（從 fct_product_details 和 fct_product_variations 取得價格和庫存）
        $baseQuery = "
            SELECT 
                p.ID as id,
                p.post_title as title,
                p.post_date as created_at,
                COALESCE(v.item_price, d.min_price, 0) as price_cents,
                COALESCE(v.available, v.total_stock, 0) as stock_quantity,
                COALESCE(v.stock_status, d.stock_availability, 'out-of-stock') as stock_status,
                m.meta_value as line_user_id
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} m ON p.ID = m.post_id AND m.meta_key = '_mygo_line_user_id'
            LEFT JOIN {$wpdb->prefix}fct_product_details d ON p.ID = d.post_id
            LEFT JOIN {$wpdb->prefix}fct_product_variations v ON p.ID = v.post_id
            WHERE {$where}
            ORDER BY p.post_date DESC
            LIMIT %d OFFSET %d
        ";
        
        // 合併參數
        $queryParams = array_merge($whereParams, [$perPage, $offset]);
        $items = $wpdb->get_results($wpdb->prepare($baseQuery, $queryParams), ARRAY_A);
        
        // 處理資料格式
        foreach ($items as &$item) {
            // FluentCart 價格以「分」為單位，需要除以 100 轉換為元
            $priceCents = floatval($item['price_cents'] ?? 0);
            $item['price'] = $priceCents / 100;
            
            // 如果庫存為 0，嘗試從 other_info 取得
            if (intval($item['stock_quantity'] ?? 0) === 0) {
                $details = $wpdb->get_row($wpdb->prepare(
                    "SELECT other_info FROM {$wpdb->prefix}fct_product_details WHERE post_id = %d",
                    $item['id']
                ));
                if (!empty($details->other_info)) {
                    $otherInfo = json_decode($details->other_info, true);
                    $item['stock_quantity'] = intval($otherInfo['stock_quantity'] ?? 0);
                }
            }
            
            // 取得商品圖片
            $thumbnailId = get_post_thumbnail_id($item['id']);
            $item['image_url'] = $thumbnailId ? wp_get_attachment_image_url($thumbnailId, 'thumbnail') : '';
        }
        
        // 計算總數
        $countQuery = "
            SELECT COUNT(*)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} m ON p.ID = m.post_id AND m.meta_key = '_mygo_line_user_id'
            LEFT JOIN {$wpdb->prefix}fct_product_details d ON p.ID = d.post_id
            LEFT JOIN {$wpdb->prefix}fct_product_variations v ON p.ID = v.post_id
            WHERE {$where}
        ";
        
        if (!empty($whereParams)) {
            $total = $wpdb->get_var($wpdb->prepare($countQuery, $whereParams));
        } else {
            $total = $wpdb->get_var($countQuery);
        }
        
        return [
            'items' => $items ?: [],
            'total' => (int) $total,
        ];
    }

    /**
     * 取得訂單列表
     */
    private function getOrders(string $tab, string $search, int $page, int $perPage): array
    {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mygo_plus_one_orders';
        
        // 檢查表是否存在
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return ['items' => [], 'total' => 0, 'total_amount' => 0];
        }
        
        $where = '1=1';
        $whereParams = [];
        $statusFilter = [];

        // 根據 tab 過濾
        switch ($tab) {
            case 'all':
                // 全部訂單
                break;
            case 'pending':
                // 待處理：未到貨或未付款或未寄送
                $statusFilter = ['pending' => true];
                break;
            case 'arrived':
                // 已到貨：到貨狀態為 true
                $statusFilter = ['arrived' => true];
                break;
            case 'completed':
                // 已完成：結單狀態為 true
                $statusFilter = ['completed' => true];
                break;
        }

        if (!empty($search)) {
            $where .= ' AND (id LIKE %s OR user_id IN (SELECT user_id FROM ' . $wpdb->usermeta . ' WHERE meta_value LIKE %s))';
            $whereParams[] = '%' . $wpdb->esc_like($search) . '%';
            $whereParams[] = '%' . $wpdb->esc_like($search) . '%';
        }

        $offset = ($page - 1) * $perPage;

        // 查詢訂單
        $query = "SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $queryParams = array_merge($whereParams, [$perPage, $offset]);
        $items = $wpdb->get_results($wpdb->prepare($query, $queryParams), ARRAY_A) ?: [];

        // 補充訂單資料並根據狀態過濾
        $filteredItems = [];
        foreach ($items as $item) {
            $fluentCartOrderId = $item['fluentcart_order_id'] ?? 0;
            $userId = $item['user_id'] ?? 0;
            $item['statuses'] = $this->cartService->getOrderStatuses($fluentCartOrderId);
            
            // 根據狀態過濾
            if (!empty($statusFilter)) {
                $shouldInclude = false;
                
                if (isset($statusFilter['pending'])) {
                    // 待處理：未到貨或未付款或未寄送
                    if (!$item['statuses']['arrived'] || !$item['statuses']['paid'] || !$item['statuses']['shipped']) {
                        $shouldInclude = true;
                    }
                } elseif (isset($statusFilter['arrived'])) {
                    // 已到貨
                    if ($item['statuses']['arrived']) {
                        $shouldInclude = true;
                    }
                } elseif (isset($statusFilter['completed'])) {
                    // 已完成
                    if ($item['statuses']['closed']) {
                        $shouldInclude = true;
                    }
                }
                
                if (!$shouldInclude) {
                    continue;
                }
            }
            
            // 取得買家姓名（優先使用 WordPress 帳號名稱）
            $user = get_userdata($userId);
            $buyerName = get_user_meta($userId, '_mygo_buyer_name', true);
            if (empty($buyerName)) {
                $buyerName = $user ? $user->display_name : get_user_meta($userId, '_mygo_line_name', true);
            }
            $item['buyer_name'] = $buyerName ?: 'LINE User';
            
            // 取得商品資訊
            $productId = $item['product_id'] ?? 0;
            $product = $this->cartService->getProduct($productId);
            $item['product_name'] = $this->extractProductTitle($product);
            
            // 計算金額（從 FluentCart 取得價格，單位是分）
            $unitPrice = $this->extractProductPrice($product);
            $quantity = intval($item['quantity'] ?? 1);
            $item['total'] = $unitPrice * $quantity;
            
            $filteredItems[] = $item;
        }
        
        $items = $filteredItems;

        // 計算總數
        $countQuery = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        if (!empty($whereParams)) {
            $total = $wpdb->get_var($wpdb->prepare($countQuery, $whereParams));
        } else {
            $total = $wpdb->get_var($countQuery);
        }

        return [
            'items' => $items,
            'total' => (int) $total,
            'total_amount' => $this->calculateOrdersTotal($items),
        ];
    }

    /**
     * 取得訂單詳情
     */
    private function getOrderDetail(int $orderId): ?array
    {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mygo_plus_one_orders';
        $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $orderId), ARRAY_A);

        if (!$order) {
            return null;
        }

        // 補充資料
        $fluentCartOrderId = $order['fluentcart_order_id'] ?? 0;
        $userId = $order['user_id'] ?? 0;

        $order['statuses'] = $this->cartService->getOrderStatuses($fluentCartOrderId);
        
        // 取得商品資訊
        $product = $this->cartService->getProduct($order['product_id'] ?? 0);
        $order['product_name'] = $this->extractProductTitle($product);
        
        // 取得使用者資訊
        $user = get_userdata($userId);
        $buyerName = get_user_meta($userId, '_mygo_buyer_name', true);
        if (empty($buyerName)) {
            $buyerName = $user ? $user->display_name : get_user_meta($userId, '_mygo_line_name', true);
        }
        $order['buyer_name'] = $buyerName ?: 'LINE User';
        $order['line_name'] = get_user_meta($userId, '_mygo_line_name', true) ?: 'LINE User';
        $order['line_uid'] = get_user_meta($userId, '_mygo_line_uid', true);
        $order['phone'] = get_user_meta($userId, '_mygo_phone', true);
        $order['address'] = get_user_meta($userId, '_mygo_address', true);
        $shippingMethod = get_user_meta($userId, '_mygo_shipping_preference', true) ?: get_user_meta($userId, '_mygo_shipping_method', true);
        $order['shipping_method'] = $shippingMethod;
        $order['shipping_method_label'] = $this->translateShippingMethod($shippingMethod);
        $order['notes'] = get_post_meta($fluentCartOrderId, '_mygo_notes', true);
        $order['source'] = '於賣場下單 +' . ($order['quantity'] ?? 1);

        // 計算金額（FluentCart 價格是分為單位）
        $order['unit_price'] = $this->extractProductPrice($product);
        $order['total'] = $order['unit_price'] * ($order['quantity'] ?? 1);

        return $order;
    }

    /**
     * 取得商品名稱
     */
    private function getProductName(int $productId): string
    {
        $product = $this->cartService->getProduct($productId);
        return $this->extractProductTitle($product);
    }
    
    /**
     * 從 FluentCart 商品資料中提取標題
     */
    private function extractProductTitle(?array $product): string
    {
        if (!$product) {
            return '';
        }
        
        // FluentCart API 可能回傳不同結構
        if (isset($product['product']['post_title'])) {
            return $product['product']['post_title'];
        }
        
        if (isset($product['product']['title'])) {
            return $product['product']['title'];
        }
        
        if (isset($product['title'])) {
            return $product['title'];
        }
        
        if (isset($product['post_title'])) {
            return $product['post_title'];
        }
        
        return '';
    }
    
    /**
     * 從 FluentCart 商品資料中提取價格（轉換為元）
     */
    private function extractProductPrice(?array $product): float
    {
        if (!$product) {
            return 0;
        }
        
        // FluentCart 價格以「分」為單位，需要除以 100
        $priceCents = 0;
        
        // 嘗試從不同位置取得價格
        if (isset($product['product']['detail']['min_price'])) {
            $priceCents = intval($product['product']['detail']['min_price']);
        } elseif (isset($product['detail']['min_price'])) {
            $priceCents = intval($product['detail']['min_price']);
        } elseif (isset($product['price'])) {
            // 如果已經是元，直接回傳
            return floatval($product['price']);
        }
        
        return $priceCents / 100;
    }

    /**
     * 計算訂單總金額
     */
    private function calculateOrdersTotal(array $orders): float
    {
        $total = 0;
        foreach ($orders as $order) {
            $total += floatval($order['total'] ?? 0);
        }
        return $total;
    }

    /**
     * 計算商品數量
     */
    private function countProducts(): int
    {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} m ON p.ID = m.post_id AND m.meta_key = '_mygo_line_user_id'
            WHERE p.post_type = 'fluent-products' AND p.post_status = 'publish'
        ");
    }

    /**
     * 計算訂單數量
     */
    private function countOrders(): int
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mygo_plus_one_orders';
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    /**
     * 計算總營收
     */
    private function calculateRevenue(): float
    {
        global $wpdb;
        
        $table = $wpdb->prefix . 'mygo_plus_one_orders';
        
        // 檢查表是否存在
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return 0;
        }
        
        // 取得所有訂單
        $orders = $wpdb->get_results("SELECT product_id, quantity FROM {$table}", ARRAY_A);
        
        if (empty($orders)) {
            return 0;
        }
        
        $totalRevenue = 0;
        
        foreach ($orders as $order) {
            $productId = $order['product_id'] ?? 0;
            $quantity = intval($order['quantity'] ?? 1);
            
            // 取得商品價格
            $product = $this->cartService->getProduct($productId);
            $unitPrice = $this->extractProductPrice($product);
            
            $totalRevenue += $unitPrice * $quantity;
        }
        
        return $totalRevenue;
    }

    /**
     * 計算使用者數量
     */
    private function countUsers(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var(
            "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key = '_mygo_line_uid'"
        );
    }

    /**
     * 取得分頁 HTML
     */
    private function getPagination(int $currentPage, int $perPage, int $total): string
    {
        $totalPages = ceil($total / $perPage);
        
        if ($totalPages <= 1) {
            return '';
        }

        $baseUrl = admin_url('admin.php?page=' . ($_GET['page'] ?? 'mygo-products'));
        
        $html = '<div class="mygo-pagination">';
        
        for ($i = 1; $i <= $totalPages; $i++) {
            $class = $i === $currentPage ? 'mygo-page-current' : '';
            $html .= sprintf(
                '<a href="%s&paged=%d" class="mygo-page %s">%d</a>',
                $baseUrl,
                $i,
                $class,
                $i
            );
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * 翻譯寄送方式
     */
    private function translateShippingMethod(string $method): string
    {
        $translations = [
            'self_pickup' => '自取',
            'home_delivery' => '宅配',
            'convenience_store' => '超商取貨',
        ];
        
        return $translations[$method] ?? $method;
    }
    
    /**
     * 匯出訂單為 CSV（公開方法）
     */
    public function exportOrdersPublic(): void
    {
        $this->exportOrders();
    }
    
    /**
     * 匯出訂單為 CSV
     */
    private function exportOrders(): void
    {
        $current_tab = sanitize_text_field($_GET['tab'] ?? 'all');
        $search = sanitize_text_field($_GET['s'] ?? '');
        
        // 取得所有訂單（不分頁）
        $ordersData = $this->getOrders($current_tab, $search, 1, 999999);
        $orders = $ordersData['items'] ?? [];
        
        if (empty($orders)) {
            wp_die('沒有訂單可以匯出');
        }
        
        // 清除所有輸出緩衝
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // 設定 CSV 檔案名稱
        $filename = 'mygo-orders-' . date('Y-m-d-His') . '.csv';
        
        // 設定 HTTP headers
        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        
        // 加入 BOM 讓 Excel 正確識別 UTF-8
        echo chr(0xEF) . chr(0xBB) . chr(0xBF);
        
        // 開啟輸出流
        $output = fopen('php://output', 'w');
        
        // 寫入標題列
        fputcsv($output, [
            '訂單編號',
            '買家姓名',
            'LINE 名稱',
            '電話',
            '地址',
            '寄送方式',
            '商品名稱',
            '單價',
            '數量',
            '金額',
            '到貨狀態',
            '付款狀態',
            '寄送狀態',
            '結單狀態',
            '下單時間',
            '備註',
        ]);
        
        // 寫入訂單資料
        foreach ($orders as $order) {
            $statuses = $order['statuses'] ?? [];
            $userId = $order['user_id'] ?? 0;
            $fluentCartOrderId = $order['fluentcart_order_id'] ?? 0;
            
            // 取得完整的買家資訊
            $user = get_userdata($userId);
            $buyerName = get_user_meta($userId, '_mygo_buyer_name', true);
            if (empty($buyerName)) {
                $buyerName = $user ? $user->display_name : get_user_meta($userId, '_mygo_line_name', true);
            }
            $lineName = get_user_meta($userId, '_mygo_line_name', true);
            $phone = get_user_meta($userId, '_mygo_phone', true);
            $address = get_user_meta($userId, '_mygo_address', true);
            $shippingMethod = get_user_meta($userId, '_mygo_shipping_preference', true) ?: get_user_meta($userId, '_mygo_shipping_method', true);
            $notes = get_post_meta($fluentCartOrderId, '_mygo_notes', true);
            
            fputcsv($output, [
                $order['id'] ?? '',
                $buyerName ?: 'LINE User',
                $lineName ?: '',
                $phone ?: '',
                $address ?: '',
                $this->translateShippingMethod($shippingMethod),
                $order['product_name'] ?? '',
                $order['unit_price'] ?? 0,
                $order['quantity'] ?? 1,
                $order['total'] ?? 0,
                $statuses['arrived'] ? '已到貨' : '未到貨',
                $statuses['paid'] ? '已付款' : '未付款',
                $statuses['shipped'] ? '已寄送' : '未寄送',
                $statuses['closed'] ? '已結單' : '未結單',
                date_i18n('Y/m/d H:i:s', strtotime($order['created_at'] ?? '')),
                $notes ?: '',
            ]);
        }
        
        fclose($output);
        flush();
        exit;
    }
}
