# LINE 登入整合設定指南

## 方式一：使用短代碼（推薦）

### 1. 建立登入頁面

在 WordPress 後台建立新頁面：

1. 前往「頁面」→「新增頁面」
2. 標題輸入「登入」
3. 在內容區塊中插入短代碼：`[mygo_line_login type="login"]`
4. 發布頁面，記下頁面 URL（例如：`http://mygo.local/login/`）

### 2. 建立註冊頁面

同樣方式建立註冊頁面：

1. 標題輸入「註冊」
2. 插入短代碼：`[mygo_line_login type="register"]`
3. 發布頁面，記下頁面 URL（例如：`http://mygo.local/register/`）

### 3. 設定 FluentCommunity

前往 FluentCommunity 設定頁面：

1. 找到「Login / Registration URLs」區塊
2. 勾選「Use custom signup URL instead of default signup page」
3. 在「Default Login URL」填入：`http://mygo.local/login/`
4. 在「Default Signup URL」填入：`http://mygo.local/register/`
5. 儲存設定

### 短代碼參數

```
[mygo_line_login type="login" style="full"]
```

參數說明：
- `type`: `login`（登入）或 `register`（註冊）
- `style`: `full`（完整頁面）或 `button`（只顯示按鈕）

範例：
- 完整登入頁面：`[mygo_line_login type="login" style="full"]`
- 只顯示按鈕：`[mygo_line_login type="login" style="button"]`

## 方式二：使用內建頁面

直接使用插件提供的頁面：

1. 登入頁面：`http://mygo.local/mygo-line-login/`
2. 在 FluentCommunity 設定中填入此 URL

## 方式三：自動攔截（已啟用）

插件會自動攔截以下 URL：
- `http://mygo.local/portal/?fcom_action=auth&form=login`
- `http://mygo.local/portal/?fcom_action=auth&form=register`

當用戶訪問這些 URL 時，會自動顯示 LINE 登入頁面。

## 登入流程

1. 用戶點擊「使用 LINE 登入」
2. 導向 LINE 授權頁面
3. 授權後返回網站
4. 自動建立/登入 WordPress 帳號
5. 如果是新用戶或資料不完整，自動彈出個人資料表單
6. 填寫電話、地址、寄送方式
7. 完成後可以在社群貼文下留言 +1 下單

## 設定 FluentCommunity 重導向

在 FluentCommunity 設定中：

1. 找到「Portal Access Settings」
2. 選擇「Only Logged In Users」
3. 在「Redirect URL」填入：`http://mygo.local/login/`

這樣未登入用戶訪問社群時，會自動導向 LINE 登入頁面。

## 測試流程

1. 登出 WordPress
2. 訪問任何社群貼文
3. 應該會自動導向登入頁面
4. 點擊「使用 LINE 登入」
5. 完成 LINE 授權
6. 檢查是否自動彈出個人資料表單
7. 填寫資料後儲存
8. 回到社群貼文，留言 +1 測試下單
