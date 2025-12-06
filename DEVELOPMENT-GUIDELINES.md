# WordPress 外掛開發規範

這些規範適用於 BuyGo (MYGO +1) WordPress 外掛的開發。

## 版本號管理規則

每次修改程式碼並推送到 GitHub 時，必須更新版本號。

### 語義化版本規則

版本號格式：`主版本.次版本.修訂號` (例如：1.0.3)

#### 何時增加修訂號 (.1)

修改 bug 或小修正時，增加修訂號：

- 修正程式錯誤

- 修正顯示問題

- 調整設定

- 優化效能

- 文字修正

範例：1.0.2 → 1.0.3

#### 何時增加次版本號 (.1.0)

新增功能時，增加次版本號：

- 新增功能

- 新增 API

- 新增設定選項

- 擴充現有功能

範例：1.0.3 → 1.1.0

#### 何時增加主版本號 (1.0.0)

重大變更或不相容的修改時，增加主版本號：

- 架構重構

- API 不相容變更

- 移除舊功能

- 重大功能改版

範例：1.1.0 → 2.0.0

### 更新版本號的步驟

1. 修改 `mygo-plus-one/mygo-plus-one.php` 中的兩處版本號：

   - Plugin Header 的 `Version: x.x.x`

   - `MYGO_PLUGIN_VERSION` 常數

2. 提交時使用適當的 commit 前綴：

   - `fix:` - 修正 bug（增加修訂號）

   - `feat:` - 新增功能（增加次版本號）

   - `BREAKING CHANGE:` - 重大變更（增加主版本號）

   - `chore:` - 維護性工作（版本號更新本身）

3. 推送到 GitHub

**重要提醒：每次修改程式碼都要更新版本號！**

---

## WordPress 開發核心原則

### 1. 命名規範（避免衝突）

**必須遵守：**

- 所有函數、類別、變數都要加上唯一的前綴（例如：`mygo_`）

- 選項名稱也要加前綴（例如：`mygo_settings`）

- 避免使用通用名稱如 `init()`、`process()`

**範例：**

```php
// ❌ 錯誤：沒有前綴，容易衝突
function get_user_data() { }
add_option('settings', $data);

// ✅ 正確：有唯一前綴
function mygo_get_user_data() { }
add_option('mygo_settings', $data);
```

### 2. 安全性（防止 XSS 和注入攻擊）

**輸出時必須轉義：**

- `esc_html()` - 轉義 HTML 內容

- `esc_attr()` - 轉義 HTML 屬性

- `esc_url()` - 轉義 URL

- `wp_kses_post()` - 允許安全的 HTML 標籤

**輸入時必須清理：**

- `sanitize_text_field()` - 清理文字欄位

- `sanitize_email()` - 清理 email

- `absint()` - 確保正整數

- `wp_unslash()` - 移除斜線

**範例：**

```php
// ❌ 錯誤：直接輸出未轉義的資料
echo $_POST['user_input'];
echo '<a href="' . $url . '">Link</a>';

// ✅ 正確：轉義所有輸出
echo esc_html($_POST['user_input']);
echo '<a href="' . esc_url($url) . '">Link</a>';

// ❌ 錯誤：直接儲存未清理的輸入
update_option('mygo_email', $_POST['email']);

// ✅ 正確：清理後再儲存
update_option('mygo_email', sanitize_email($_POST['email']));
```

### 3. Hooks 使用規範

**Actions（動作鉤子）：**

用於在特定時機執行程式碼，不需要回傳值。

常用的 Actions：

- `init` - WordPress 初始化時

- `admin_init` - 管理後台初始化時

- `admin_menu` - 註冊管理選單

- `wp_enqueue_scripts` - 載入前端資源

- `admin_enqueue_scripts` - 載入後台資源

- `save_post` - 儲存文章時

- `wp_ajax_{action}` - AJAX 請求（已登入）

- `wp_ajax_nopriv_{action}` - AJAX 請求（未登入）

**Filters（過濾器）：**

用於修改資料，必須回傳修改後的值。

**範例：**

```php
// Action：不需要回傳值
function mygo_init_plugin() {
    // 初始化程式碼
}
add_action('init', 'mygo_init_plugin');

// Filter：必須回傳修改後的值
function mygo_modify_content($content) {
    $content = $content . '<p>額外內容</p>';
    return $content; // ✅ 必須回傳
}
add_filter('the_content', 'mygo_modify_content');
```

### 4. 條件檢查（避免在錯誤的地方執行）

**必須檢查執行環境：**

```php
// 檢查是否在單一文章頁面
if (!is_singular('post')) {
    return $content;
}

// 檢查是否在主迴圈中
if (!in_the_loop() || !is_main_query()) {
    return $content;
}

// 檢查使用者權限
if (!current_user_can('manage_options')) {
    return;
}

// 檢查是否在管理後台
if (is_admin()) {
    // 後台專用程式碼
}
```

### 5. 資源載入（CSS/JS）

**必須使用 WordPress 的 enqueue 系統：**

```php
// ❌ 錯誤：直接在 HTML 中載入
<link rel="stylesheet" href="style.css">
<script src="script.js"></script>

// ✅ 正確：使用 wp_enqueue_* 函數
function mygo_enqueue_assets() {
    // 載入 CSS
    wp_enqueue_style(
        'mygo-style',
        plugin_dir_url(__FILE__) . 'style.css',
        array(),
        '1.0.0'
    );
    
    // 載入 JS
    wp_enqueue_script(
        'mygo-script',
        plugin_dir_url(__FILE__) . 'script.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    // 傳遞資料到 JS
    wp_localize_script('mygo-script', 'mygoData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('mygo_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'mygo_enqueue_assets');
```

### 6. AJAX 處理規範

**後端 PHP：**

```php
function mygo_ajax_save_data() {
    // 1. 驗證 nonce
    check_ajax_referer('mygo_nonce', 'nonce');
    
    // 2. 檢查權限
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('權限不足');
    }
    
    // 3. 清理輸入
    $user_data = sanitize_text_field($_POST['user_data']);
    
    // 4. 處理邏輯
    $result = update_option('mygo_data', $user_data);
    
    // 5. 回傳結果
    if ($result) {
        wp_send_json_success('儲存成功');
    } else {
        wp_send_json_error('儲存失敗');
    }
}
add_action('wp_ajax_mygo_save_data', 'mygo_ajax_save_data');
```

### 7. 除錯最佳實踐

**使用 error_log 記錄：**

```php
// ❌ 錯誤：使用 var_dump 或 print_r
var_dump($data);
print_r($data);

// ✅ 正確：使用 error_log
error_log('MYGO Debug: ' . print_r($data, true));
error_log('MYGO Error: ' . $error_message);
```

### 8. 資料庫操作

**使用 $wpdb 進行查詢：**

```php
global $wpdb;

// ❌ 錯誤：SQL 注入風險
$results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}users WHERE email = '{$email}'");

// ✅ 正確：使用 prepare 防止 SQL 注入
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}users WHERE email = %s",
        $email
    )
);
```

### 9. 國際化（i18n）

**包裝所有使用者可見的文字：**

```php
// 簡單文字
__('Hello World', 'mygo-plus-one');
esc_html__('Hello World', 'mygo-plus-one');

// 輸出文字
_e('Hello World', 'mygo-plus-one');
esc_html_e('Hello World', 'mygo-plus-one');

// 單複數
_n('%s item', '%s items', $count, 'mygo-plus-one');

// 帶變數的文字
sprintf(__('Hello %s', 'mygo-plus-one'), $name);
```

### 10. 避免直接存取檔案

在主要外掛檔案頂部加入：

```php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
```

## 開發檢查清單

在提交程式碼前，確認：

- [ ] 所有函數、類別、變數都有唯一前綴（`mygo_`）

- [ ] 所有輸出都經過轉義（`esc_html()`, `esc_attr()`, `esc_url()`）

- [ ] 所有輸入都經過清理（`sanitize_*()` 函數）

- [ ] 使用 `wp_enqueue_*` 載入資源，不直接在 HTML 中載入

- [ ] AJAX 請求有 nonce 驗證

- [ ] 有適當的權限檢查（`current_user_can()`）

- [ ] 有條件檢查避免在錯誤的地方執行

- [ ] 所有使用者可見文字都包裝在翻譯函數中

- [ ] 資料庫查詢使用 `$wpdb->prepare()` 防止 SQL 注入

- [ ] 版本號已更新

- [ ] 使用 `error_log()` 而非 `var_dump()` 或 `print_r()`

- [ ] 測試過功能正常運作

## 常見錯誤與解決方案

### 錯誤 1：Nonce 驗證失敗

**原因：** Cookie 未正確設定或 nonce 過期

**解決：** 使用 Cookie 儲存資料，在 callback 頁面處理，避免 AJAX nonce 問題

### 錯誤 2：中文使用者名稱導致錯誤

**原因：** WordPress 使用者名稱只允許英數字

**解決：** 使用 email 前綴或 UID 作為使用者名稱

### 錯誤 3：資源載入順序錯誤

**原因：** 直接在 HTML 中載入，或未宣告依賴

**解決：** 使用 `wp_enqueue_*` 並正確宣告依賴項

### 錯誤 4：在錯誤的地方執行程式碼

**原因：** 未檢查執行環境

**解決：** 使用條件函數如 `is_singular()`, `is_admin()`, `in_the_loop()`

