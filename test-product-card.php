<?php
/**
 * 測試商品卡片
 * 
 * 用途：測試發送商品上架通知卡片
 * 網址：/wp-content/plugins/mygo-plus-one/test-product-card.php
 */

require_once __DIR__ . '/../../../wp-load.php';

use Mygo\Services\LineMessageService;

// 檢查是否為管理員
if (!current_user_can('manage_options')) {
    wp_die('需要管理員權限');
}

$message = '';
$error = '';

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_card'])) {
    $lineUid = sanitize_text_field($_POST['line_uid'] ?? '');
    
    if (empty($lineUid)) {
        $error = '請輸入 LINE User ID';
    } else {
        // 準備測試商品資料
        $testProduct = [
            'id' => 999,
            'name' => '日本薯條三兄弟',
            'code' => 'MYGO-999',
            'price' => 350,
            'quantity' => 20,
            'arrival_date' => date('Y-m-d', strtotime('+7 days')),
            'preorder_date' => date('Y-m-d', strtotime('+3 days')),
            'description' => '超人氣日本零食，限量供應！香脆可口，送禮自用兩相宜。',
            // 使用真實可訪問的圖片 URL
            'image_url' => 'https://scdn.line-apps.com/n/channel_devcenter/img/fx/01_1_cafe.png',
            'url' => home_url('/product/999'),
            'community_url' => home_url('/portal/post/123'),
        ];
        
        $lineService = new LineMessageService();
        
        // 發送卡片
        $result1 = $lineService->sendProductCard($lineUid, $testProduct);
        
        // 發送純文字訊息
        $textMessage = "✅ 商品「MYGO-999」已成功上架！\n\n";
        $textMessage .= "💰 價格：NT$ 350\n";
        $textMessage .= "📦 數量：20 個\n";
        $textMessage .= "\n📱 社群貼文連結：\n" . $testProduct['community_url'];
        $textMessage .= "\n\n👉 點擊留言 +1 立刻下單";
        
        $result2 = $lineService->sendTextMessage($lineUid, $textMessage);
        
        if ($result1 && $result2) {
            $message = '✅ 商品卡片和文字訊息已發送！';
        } else {
            $error = '❌ 發送失敗，請檢查 LINE Channel Access Token 設定';
        }
    }
}

// 取得當前使用者的 LINE UID（如果有綁定）
$currentUser = wp_get_current_user();
$defaultLineUid = get_user_meta($currentUser->ID, '_mygo_line_uid', true);

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>測試商品卡片 - MYGO +1</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .info-box p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 8px;
        }
        
        .info-box ul {
            margin-left: 20px;
            color: #666;
            font-size: 14px;
            line-height: 1.8;
        }
        
        .product-preview {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .product-preview h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .product-preview table {
            width: 100%;
            font-size: 14px;
        }
        
        .product-preview td {
            padding: 8px 0;
            color: #666;
        }
        
        .product-preview td:first-child {
            font-weight: 500;
            color: #333;
            width: 120px;
        }
        
        .links {
            margin-top: 20px;
            text-align: center;
        }
        
        .links a {
            display: inline-block;
            margin: 0 10px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }
        
        .links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>🎉 測試商品卡片</h1>
            <p class="subtitle">測試發送商品上架通知的 LINE Flex Message 卡片</p>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo esc_html($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo esc_html($error); ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>📱 關於 LINE 社群分享</h3>
                <p><strong>好消息！</strong>LINE Flex Message 卡片可以轉發到 LINE 社群（OpenChat）中，而且會保持完整的卡片格式。</p>
                <p><strong>如何分享到社群：</strong></p>
                <ul>
                    <li>收到卡片後，長按訊息</li>
                    <li>選擇「轉發」</li>
                    <li>選擇要轉發的社群或朋友</li>
                    <li>卡片會保持完整格式（圖片、按鈕、排版）</li>
                </ul>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="line_uid">LINE User ID</label>
                    <input 
                        type="text" 
                        id="line_uid" 
                        name="line_uid" 
                        value="<?php echo esc_attr($defaultLineUid); ?>"
                        placeholder="輸入 LINE User ID（例如：U1234567890abcdef...）"
                        required
                    >
                </div>
                
                <button type="submit" name="send_card" class="btn">
                    📤 發送商品卡片
                </button>
            </form>
            
            <div class="product-preview">
                <h3>📦 測試商品資料</h3>
                <table>
                    <tr>
                        <td>商品名稱</td>
                        <td>日本薯條三兄弟</td>
                    </tr>
                    <tr>
                        <td>商品代碼</td>
                        <td>MYGO-999</td>
                    </tr>
                    <tr>
                        <td>價格</td>
                        <td>NT$ 350</td>
                    </tr>
                    <tr>
                        <td>庫存</td>
                        <td>20 件</td>
                    </tr>
                    <tr>
                        <td>到貨日期</td>
                        <td><?php echo date('Y/m/d', strtotime('+7 days')); ?></td>
                    </tr>
                    <tr>
                        <td>預購截止</td>
                        <td><?php echo date('Y/m/d', strtotime('+3 days')); ?></td>
                    </tr>
                    <tr>
                        <td>狀態</td>
                        <td>預購中</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="links">
            <a href="test-seller-notification.php">← 賣家訂單通知</a>
            <a href="test-buyer-notification.php">買家訂單確認 →</a>
        </div>
    </div>
</body>
</html>
