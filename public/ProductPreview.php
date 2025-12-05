<?php

namespace Mygo\PublicPages;

defined('ABSPATH') or die;

use Mygo\Services\FluentCartService;

/**
 * Product Preview
 * 
 * 商品公開預覽頁面與 Open Graph 標籤
 */
class ProductPreview
{
    private FluentCartService $cartService;

    public function __construct()
    {
        $this->cartService = new FluentCartService();
    }

    /**
     * 註冊 Hooks
     */
    public static function register(): void
    {
        $instance = new self();
        
        // 註冊 rewrite rule
        add_action('init', [$instance, 'registerRewriteRules']);
        
        // 處理預覽頁面
        add_action('template_redirect', [$instance, 'handlePreviewPage']);
        
        // 加入 Open Graph 標籤
        add_action('wp_head', [$instance, 'addOpenGraphTags']);
        
        // 註冊 query vars
        add_filter('query_vars', [$instance, 'registerQueryVars']);
    }

    /**
     * 註冊 Rewrite Rules
     */
    public function registerRewriteRules(): void
    {
        add_rewrite_rule(
            '^mygo/product/([0-9]+)/?$',
            'index.php?mygo_product_id=$matches[1]',
            'top'
        );
    }

    /**
     * 註冊 Query Vars
     */
    public function registerQueryVars(array $vars): array
    {
        $vars[] = 'mygo_product_id';
        return $vars;
    }

    /**
     * 處理預覽頁面
     */
    public function handlePreviewPage(): void
    {
        $productId = get_query_var('mygo_product_id');
        
        if (!$productId) {
            return;
        }

        $product = $this->cartService->getProduct((int) $productId);
        
        if (!$product) {
            wp_die(__('商品不存在', 'mygo-plus-one'), '', ['response' => 404]);
        }

        $this->renderPreviewPage($product);
        exit;
    }

    /**
     * 渲染預覽頁面
     */
    private function renderPreviewPage(array $product): void
    {
        $imageUrl = $product['featured_image'] ?? '';
        $title = $product['title'] ?? '';
        $price = $product['price'] ?? 0;
        $description = $product['description'] ?? '';
        $stock = $product['stock_quantity'] ?? 0;
        
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php echo esc_html($title); ?> - <?php bloginfo('name'); ?></title>
            
            <!-- Open Graph -->
            <meta property="og:title" content="<?php echo esc_attr($title); ?>">
            <meta property="og:description" content="NT$ <?php echo number_format($price); ?> - <?php echo esc_attr(wp_trim_words($description, 20)); ?>">
            <meta property="og:image" content="<?php echo esc_url($imageUrl); ?>">
            <meta property="og:url" content="<?php echo esc_url(home_url('/mygo/product/' . $product['id'])); ?>">
            <meta property="og:type" content="product">
            
            <style>
                :root {
                    --mygo-bg: #f2f2f7;
                    --mygo-card-bg: #ffffff;
                    --mygo-primary: #007aff;
                    --mygo-text: #1c1c1e;
                    --mygo-text-secondary: #8e8e93;
                }
                
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: var(--mygo-bg);
                    min-height: 100vh;
                    padding: 20px;
                }
                
                .mygo-preview {
                    max-width: 500px;
                    margin: 0 auto;
                }
                
                .mygo-product-card {
                    background: var(--mygo-card-bg);
                    border-radius: 20px;
                    overflow: hidden;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                
                .mygo-product-image {
                    width: 100%;
                    aspect-ratio: 1;
                    object-fit: cover;
                }
                
                .mygo-product-info {
                    padding: 24px;
                }
                
                .mygo-product-title {
                    font-size: 24px;
                    font-weight: 700;
                    color: var(--mygo-text);
                    margin-bottom: 12px;
                }
                
                .mygo-product-price {
                    font-size: 22px;
                    font-weight: 600;
                    color: var(--mygo-primary);
                    margin-bottom: 16px;
                }
                
                .mygo-product-stock {
                    font-size: 14px;
                    color: var(--mygo-text-secondary);
                    margin-bottom: 16px;
                }
                
                .mygo-product-desc {
                    font-size: 15px;
                    color: var(--mygo-text);
                    line-height: 1.6;
                    margin-bottom: 24px;
                }
                
                .mygo-cta {
                    display: block;
                    width: 100%;
                    padding: 16px;
                    background: var(--mygo-primary);
                    color: #fff;
                    text-align: center;
                    text-decoration: none;
                    border-radius: 12px;
                    font-size: 17px;
                    font-weight: 600;
                }
                
                .mygo-login-hint {
                    text-align: center;
                    margin-top: 16px;
                    font-size: 14px;
                    color: var(--mygo-text-secondary);
                }
            </style>
        </head>
        <body>
            <div class="mygo-preview">
                <div class="mygo-product-card">
                    <?php if ($imageUrl) : ?>
                        <img src="<?php echo esc_url($imageUrl); ?>" alt="<?php echo esc_attr($title); ?>" class="mygo-product-image">
                    <?php endif; ?>
                    
                    <div class="mygo-product-info">
                        <h1 class="mygo-product-title"><?php echo esc_html($title); ?></h1>
                        <div class="mygo-product-price">NT$ <?php echo number_format($price); ?></div>
                        <div class="mygo-product-stock">
                            <?php if ($stock > 0) : ?>
                                庫存：<?php echo esc_html($stock); ?> 個
                            <?php else : ?>
                                已售完
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($description) : ?>
                            <div class="mygo-product-desc"><?php echo nl2br(esc_html($description)); ?></div>
                        <?php endif; ?>
                        
                        <?php if (is_user_logged_in()) : ?>
                            <a href="<?php echo esc_url($this->getCommunityPostUrl($product['id'])); ?>" class="mygo-cta">
                                前往下單
                            </a>
                        <?php else : ?>
                            <a href="<?php echo esc_url($this->getLoginUrl($product['id'])); ?>" class="mygo-cta">
                                登入後下單
                            </a>
                            <p class="mygo-login-hint">使用 LINE 帳號快速登入</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <script>
                // LINE 內部瀏覽器偵測
                (function() {
                    var ua = navigator.userAgent || navigator.vendor;
                    if (/Line/i.test(ua)) {
                        var url = window.location.href;
                        if (url.indexOf('openExternalBrowser=1') === -1) {
                            window.location.href = url + (url.indexOf('?') > -1 ? '&' : '?') + 'openExternalBrowser=1';
                        }
                    }
                })();
            </script>
        </body>
        </html>
        <?php
    }

    /**
     * 加入 Open Graph 標籤
     */
    public function addOpenGraphTags(): void
    {
        $productId = get_query_var('mygo_product_id');
        
        if (!$productId) {
            return;
        }

        $product = $this->cartService->getProduct((int) $productId);
        
        if (!$product) {
            return;
        }

        $imageUrl = $product['featured_image'] ?? '';
        $title = $product['title'] ?? '';
        $price = $product['price'] ?? 0;
        $description = $product['description'] ?? '';
        
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta property="og:description" content="NT$ ' . number_format($price) . ' - ' . esc_attr(wp_trim_words($description, 20)) . '">' . "\n";
        echo '<meta property="og:image" content="' . esc_url($imageUrl) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(home_url('/mygo/product/' . $productId)) . '">' . "\n";
        echo '<meta property="og:type" content="product">' . "\n";
    }

    /**
     * 取得社群貼文 URL
     */
    private function getCommunityPostUrl(int $productId): string
    {
        $feedId = get_post_meta($productId, '_mygo_feed_id', true);
        if ($feedId) {
            return home_url('/community/feed/' . $feedId);
        }
        return home_url();
    }

    /**
     * 取得登入 URL
     */
    private function getLoginUrl(int $productId): string
    {
        $authHandler = new \Mygo\Services\LineAuthHandler();
        $redirectUri = rest_url('mygo/v1/line-callback');
        
        // 儲存要返回的頁面
        set_transient('mygo_login_redirect_' . session_id(), home_url('/mygo/product/' . $productId), 600);
        
        return $authHandler->getAuthUrl($redirectUri);
    }
}
