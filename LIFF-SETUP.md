# LIFF Share Target Picker 設定指南

## 什麼是 LIFF Share Target Picker？

LIFF Share Target Picker 可以讓使用者分享完整的 Flex Message 卡片給朋友或群組，而不是只分享純文字。

## 設定步驟

### 1. 前往 LINE Developers Console

訪問：https://developers.line.biz/console/

### 2. 選擇你的 Provider 和 Channel

選擇 MYGO +1 使用的 Messaging API Channel

### 3. 建立 LIFF app

1. 點擊左側選單的「LIFF」
2. 點擊「Add」按鈕
3. 填寫以下資訊：

#### LIFF app name
```
MYGO +1 商品分享
```

#### Size
選擇：`Full`（全螢幕）

#### Endpoint URL
複製 MYGO +1 設定頁面中的「LIFF Endpoint URL」，例如：
```
https://your-domain.com/wp-content/plugins/mygo-plus-one/public/liff-share-product.php
```

#### Scopes
勾選：
- [x] profile
- [x] openid

#### Bot link feature
選擇：`On (Normal)`

### 4. 取得 LIFF ID

建立完成後，你會看到 LIFF ID，格式類似：
```
1234567890-abcdefgh
```

### 5. 在 MYGO +1 設定中填入 LIFF ID

1. 前往 WordPress 後台
2. 進入「MYGO +1」→「設定」
3. 在「LIFF ID」欄位填入剛才取得的 LIFF ID
4. 點擊「儲存變更」

## 測試

1. 使用 LINE 官方帳號發送商品卡片給自己
2. 點擊卡片上的「分享給朋友」按鈕
3. 應該會開啟 LIFF 頁面，顯示分享介面
4. 選擇要分享的朋友或群組
5. 對方會收到完整的 Flex Message 卡片（不是純文字）

## 注意事項

### HTTPS 需求
LIFF 只能在 HTTPS 環境下運作，本地開發需要使用 ngrok 或類似工具。

### 測試環境
如果你使用 `mygo.local` 等本地網址，需要：
1. 使用 ngrok 建立 HTTPS tunnel
2. 將 ngrok URL 設定為 LIFF Endpoint URL
3. 例如：`https://abc123.ngrok.io/wp-content/plugins/mygo-plus-one/public/liff-share-product.php`

### 備用方案
如果沒有設定 LIFF ID，系統會自動使用純文字分享（`line.me/R/share`）作為備用方案。

## 常見問題

### Q: 點擊「分享給朋友」按鈕後沒有反應？
A: 檢查：
1. LIFF ID 是否正確填入
2. Endpoint URL 是否可以訪問（必須是 HTTPS）
3. 瀏覽器 Console 是否有錯誤訊息

### Q: 分享後對方收到的還是純文字？
A: 這表示 LIFF 沒有正確設定，系統使用了備用的純文字分享方案。

### Q: 本地開發如何測試？
A: 使用 ngrok：
```bash
ngrok http 80
```
然後將 ngrok 提供的 HTTPS URL 設定為 LIFF Endpoint URL。

## 相關連結

- [LINE LIFF 官方文件](https://developers.line.biz/en/docs/liff/overview/)
- [Share Target Picker API](https://developers.line.biz/en/reference/liff/#share-target-picker)
- [LIFF Starter App](https://github.com/line/line-liff-v2-starter)
