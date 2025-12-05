<?php
/**
 * 測試發送買家訂單確認卡片
 * 
 * 使用方式：
 * 1. 在瀏覽器訪問：https://你的網址/wp-content/plugins/mygo-plus-one/test-buyer-notification.php
 * 2. 輸入你的 LINE UID
 * 3. 點擊「發送測試通知」
 */

// 載入 WordPress
require_once('../../../wp-load.php');

// 檢查是否已登入且有管理員權限
if (!current_user_can('manage_options')) {
    wp_die('您沒有權限訪問此頁面');
}

use Mygo\Services\LineMessageService;

$message = '';
$messageType = '';

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    $lineUid = sanitize_text_field($_POST['line_uid'] ?? '');
    
    if (empty($lineUid)) {
        $message = '請輸入 LINE UID';
        $messageType = 'error';
    } else {
        $lineService = new LineMessageService();
        
        // 建立測試訂單資料
        $testOrder = [
            'order_number' => '999',
            'product_name' => '測試商品 0588',
            'quantity' => 1,
            'total' => 350,
        ];
        
        $result = $lineService->sendOrderConfirmCard($lineUid, $testOrder);
        
        if ($result) {
            $message = '測試通知已發送！請檢查你的 LINE';
            $messageType = 'success';
        } else {
            $message = '發送失敗，請檢查 LINE Channel Access Token 是否正確設定';
            $messageType = 'error';
        }
    }
}

// 取得目前登入使用者的 LINE UID（如果有的話）
$currentUserId = get_current_user_id();
$defaultLineUid = get_user_meta($currentUserId, '_mygo_line_uid', true);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>測試買家訂單確認</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #f87171;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.15s ease;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #007aff;
            box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.1);
        }
        
        .hint {
            font-size: 12px;
            color: #6b7280;
            margin-top: 6px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007aff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        
        .btn:hover {
            background: #0051d5;
            transform: translateY(-1px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .card-preview {
            margin-top: 32px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        
        .card-preview h3 {
            font-size: 16px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 12px;
        }
        
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }
        
        .preview-item:last-child {
            border-bottom: none;
        }
        
        .preview-label {
            color: #6b7280;
        }
        
        .preview-value {
            color: #1f2937;
            font-weight: 500;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007aff;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .test-links {
            margin-top: 24px;
            padding: 16px;
            background: #eff6ff;
            border-radius: 8px;
            border: 1px solid #bfdbfe;
        }
        
        .test-links h4 {
            font-size: 14px;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 8px;
        }
        
        .test-links a {
            display: inline-block;
            margin-right: 12px;
            color: #2563eb;
            text-decoration: none;
            font-size: 13px;
        }
        
        .test-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ 測試買家訂單確認</h1>
        <p class="subtitle">發送測試的 LINE 訂單確認卡片（給買家）</p>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo esc_html($message); ?>
        </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="line_uid">LINE UID</label>
                <input type="text" 
                       id="line_uid" 
                       name="line_uid" 
                       value="<?php echo esc_attr($defaultLineUid); ?>" 
                       placeholder="例如：U823e48d899eb99be6fb49d53609048d9"
                       required>
                <p class="hint">輸入要接收測試通知的 LINE UID</p>
            </div>
            
            <button type="submit" name="send_test" class="btn">
                📤 發送測試通知
            </button>
        </form>
        
        <div class="card-preview">
            <h3>📋 測試卡片內容預覽</h3>
            <div class="preview-item">
                <span class="preview-label">標題</span>
                <span class="preview-value" style="color: #007aff;">訂單確認</span>
            </div>
            <div class="preview-item">
                <span class="preview-label">訂單編號</span>
                <span class="preview-value">#999</span>
            </div>
            <div class="preview-item">
                <span class="preview-label">商品</span>
                <span class="preview-value">測試商品 0588</span>
            </div>
            <div class="preview-item">
                <span class="preview-label">數量</span>
                <span class="preview-value">1 個</span>
            </div>
            <div class="preview-item">
                <span class="preview-label">金額</span>
                <span class="preview-value" style="color: #007aff; font-weight: 700;">NT$ 350</span>
            </div>
        </div>
        
        <div class="test-links">
            <h4>🔗 其他測試頁面</h4>
            <a href="test-seller-notification.php">測試賣家通知</a>
            <a href="test-line-bot-status.php">LINE Bot 狀態</a>
        </div>
        
        <a href="<?php echo admin_url('admin.php?page=mygo-orders'); ?>" class="back-link">
            ← 返回訂單管理
        </a>
    </div>
</body>
</html>
