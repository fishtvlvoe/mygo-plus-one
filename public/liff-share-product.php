<?php
/**
 * LIFF Share Product Page
 * 
 * 用途：使用 LIFF Share Target Picker 分享商品 Flex Message
 * 網址：/wp-content/plugins/mygo-plus-one/public/liff-share-product.php
 */

require_once __DIR__ . '/../../../../wp-load.php';

use Mygo\Services\FluentCartService;

// 取得商品 ID
$productId = intval($_GET['productId'] ?? 0);

if (!$productId) {
    wp_die('缺少商品 ID');
}

// 取得商品資料
$cartService = new FluentCartService();
$product = $cartService->getProduct($productId);

if (!$product) {
    wp_die('商品不存在');
}

// 取得 LIFF ID
$liffId = get_option('mygo_liff_id', '');

if (empty($liffId)) {
    wp_die('請先在設定中填入 LIFF ID');
}

// 準備商品資料
$productData = [
    'id' => $productId,
    'name' => $product['title'] ?? $product['post_title'] ?? '',
    'code' => get_post_meta($productId, '_mygo_product_code', true),
    'price' => $product['price'] ?? 0,
    'quantity' => $product['stock_quantity'] ?? 0,
    'arrival_date' => get_post_meta($productId, '_mygo_arrival_date', true),
    'preorder_date' => get_post_meta($productId, '_mygo_preorder_date', true),
];

// 取得圖片
$imageId = get_post_thumbnail_id($productId);
if ($imageId) {
    $productData['image_url'] = wp_get_attachment_url($imageId);
}

// 取得社群貼文連結
$feedId = get_post_meta($productId, '_mygo_feed_id', true);
if ($feedId) {
    if (class_exists('\FluentCommunity\App\Models\Feed')) {
        $feed = \FluentCommunity\App\Models\Feed::find($feedId);
        if ($feed) {
            $productData['community_url'] = $feed->getPermalink();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分享商品 - MYGO +1</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .loading {
            display: block;
        }
        
        .success {
            display: none;
        }
        
        .error {
            display: none;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .message {
            color: #666;
            line-height: 1.6;
            margin-top: 20px;
        }
        
        .product-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: left;
        }
        
        .product-info h2 {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .product-info p {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin-top: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="loading">
            <h1>🎉 準備分享</h1>
            <div class="spinner"></div>
            <p class="message">正在載入 LIFF...</p>
        </div>
        
        <div class="success">
            <h1>✅ 分享成功</h1>
            <p class="message">商品已成功分享給朋友！</p>
            <a href="#" class="btn" onclick="liff.closeWindow()">關閉視窗</a>
        </div>
        
        <div class="error">
            <h1>❌ 分享失敗</h1>
            <p class="message" id="error-message">發生錯誤，請稍後再試</p>
            <a href="#" class="btn" onclick="liff.closeWindow()">關閉視窗</a>
        </div>
        
        <div class="product-info">
            <h2><?php echo esc_html($productData['name']); ?></h2>
            <p>💰 NT$ <?php echo number_format($productData['price']); ?></p>
            <p>📦 剩 <?php echo $productData['quantity']; ?> 件</p>
        </div>
    </div>

    <!-- LIFF SDK -->
    <script charset="utf-8" src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
    
    <script>
        const productData = <?php echo json_encode($productData, JSON_UNESCAPED_UNICODE); ?>;
        const liffId = '<?php echo esc_js($liffId); ?>';
        
        // 建立 Flex Message
        function buildFlexMessage(product) {
            const price = parseInt(product.price || 0);
            const quantity = parseInt(product.quantity || 0);
            const statusText = quantity > 0 ? '預購中' : '已售完';
            const statusColor = quantity > 0 ? '#34C759' : '#FF3B30';
            
            const bodyContents = [
                {
                    type: 'text',
                    text: '🎉 新商品上架',
                    size: 'sm',
                    color: '#8E8E93',
                    margin: 'none'
                },
                {
                    type: 'text',
                    text: product.name || '商品名稱',
                    weight: 'bold',
                    size: 'xl',
                    wrap: true,
                    margin: 'md'
                },
                {
                    type: 'box',
                    layout: 'baseline',
                    margin: 'md',
                    contents: [
                        {
                            type: 'text',
                            text: 'NT$ ' + price.toLocaleString(),
                            size: 'xxl',
                            color: '#FF3B30',
                            weight: 'bold',
                            flex: 0
                        }
                    ]
                },
                {
                    type: 'box',
                    layout: 'vertical',
                    margin: 'lg',
                    spacing: 'sm',
                    contents: [
                        {
                            type: 'box',
                            layout: 'baseline',
                            spacing: 'sm',
                            contents: [
                                {
                                    type: 'text',
                                    text: '狀態',
                                    color: '#8E8E93',
                                    size: 'sm',
                                    flex: 2
                                },
                                {
                                    type: 'text',
                                    text: statusText,
                                    wrap: true,
                                    color: statusColor,
                                    size: 'sm',
                                    flex: 3,
                                    weight: 'bold'
                                }
                            ]
                        },
                        {
                            type: 'box',
                            layout: 'baseline',
                            spacing: 'sm',
                            contents: [
                                {
                                    type: 'text',
                                    text: '庫存',
                                    color: '#8E8E93',
                                    size: 'sm',
                                    flex: 2
                                },
                                {
                                    type: 'text',
                                    text: '剩 ' + quantity + ' 件',
                                    wrap: true,
                                    color: '#1C1C1E',
                                    size: 'sm',
                                    flex: 3
                                }
                            ]
                        }
                    ]
                }
            ];
            
            const orderUrl = product.community_url || product.url || '';
            
            return {
                type: 'flex',
                altText: '🎉 新商品：' + (product.name || '商品') + ' NT$ ' + price.toLocaleString(),
                contents: {
                    type: 'bubble',
                    hero: product.image_url ? {
                        type: 'image',
                        url: product.image_url,
                        size: 'full',
                        aspectRatio: '1:1',
                        aspectMode: 'cover',
                        action: {
                            type: 'uri',
                            uri: orderUrl
                        }
                    } : undefined,
                    body: {
                        type: 'box',
                        layout: 'vertical',
                        contents: bodyContents
                    },
                    footer: {
                        type: 'box',
                        layout: 'vertical',
                        spacing: 'sm',
                        contents: [
                            {
                                type: 'button',
                                style: 'primary',
                                height: 'sm',
                                action: {
                                    type: 'uri',
                                    label: '點擊下單',
                                    uri: orderUrl
                                },
                                color: '#007AFF'
                            }
                        ],
                        flex: 0
                    }
                }
            };
        }
        
        // 初始化 LIFF
        async function initializeLiff() {
            try {
                await liff.init({ liffId: liffId });
                
                if (!liff.isLoggedIn()) {
                    liff.login();
                    return;
                }
                
                // 建立 Flex Message
                const flexMessage = buildFlexMessage(productData);
                
                // 使用 Share Target Picker
                const result = await liff.shareTargetPicker([flexMessage]);
                
                if (result) {
                    // 分享成功
                    document.querySelector('.loading').style.display = 'none';
                    document.querySelector('.success').style.display = 'block';
                    
                    // 3 秒後自動關閉
                    setTimeout(() => {
                        liff.closeWindow();
                    }, 3000);
                } else {
                    // 使用者取消分享
                    showError('已取消分享');
                }
                
            } catch (error) {
                console.error('LIFF Error:', error);
                showError(error.message || '發生錯誤');
            }
        }
        
        function showError(message) {
            document.querySelector('.loading').style.display = 'none';
            document.querySelector('.error').style.display = 'block';
            document.getElementById('error-message').textContent = message;
        }
        
        // 啟動
        initializeLiff();
    </script>
</body>
</html>
