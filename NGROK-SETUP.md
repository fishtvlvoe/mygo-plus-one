# ngrok 設定指南（本地開發用）

## 步驟 1：啟動 ngrok

在終端機執行：

```bash
ngrok http 80
```

你會看到類似這樣的畫面：

```
ngrok                                                                    

Session Status                online
Account                       你的帳號
Version                       3.x.x
Region                        Asia Pacific (ap)
Latency                       -
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123def456.ngrok-free.app -> http://localhost:80

Connections                   ttl     opn     rt1     rt5     p50     p90
                              0       0       0.00    0.00    0.00    0.00
```

**重要**：記下 `Forwarding` 那一行的 HTTPS URL，例如：`https://abc123def456.ngrok-free.app`

## 步驟 2：更新 WordPress 網站 URL

1. 前往 WordPress 後台 → 設定 → 一般
2. 將「WordPress 位址 (URL)」和「網站位址 (URL)」都改為 ngrok 的 URL
   - 例如：`https://abc123def456.ngrok-free.app`
3. 儲存變更

**或者**使用 wp-cli 快速修改：

```bash
cd "/Users/fishtv/Local Sites/mygo/app/public"
wp option update home 'https://你的ngrok網址'
wp option update siteurl 'https://你的ngrok網址'
```

## 步驟 3：更新 LINE Developers Console

1. 前往 [LINE Developers Console](https://developers.line.biz/console/)
2. 選擇你的 LINE Login Channel
3. 找到「Callback URL」設定
4. 加入新的 Callback URL：
   ```
   https://你的ngrok網址/mygo-line-callback/
   ```
   例如：`https://abc123def456.ngrok-free.app/mygo-line-callback/`
5. 儲存設定

## 步驟 4：測試登入

1. 使用 ngrok URL 訪問你的網站：`https://你的ngrok網址/portal/`
2. 點擊登入/註冊
3. 填寫個人資料
4. 點擊「繼續使用 LINE 登入」
5. 完成 LINE 授權
6. 應該會成功返回並自動儲存資料

## 步驟 5：完成後恢復設定

測試完成後，記得：

1. 停止 ngrok（按 Ctrl+C）
2. 將 WordPress 網站 URL 改回 `http://mygo.local`
3. 從 LINE Developers Console 移除 ngrok 的 Callback URL（或保留供下次測試用）

## 注意事項

- ngrok 免費版每次啟動會產生不同的 URL，所以每次都需要更新設定
- ngrok 免費版有連線數限制
- 如果需要固定的 URL，可以升級到 ngrok 付費版
- 保持 ngrok 終端機視窗開啟，關閉就會斷線

## 除錯

如果遇到問題，可以：

1. 檢查 ngrok 的 Web Interface：http://127.0.0.1:4040
2. 查看所有 HTTP 請求和回應
3. 檢查 WordPress debug.log：`/Users/fishtv/Local Sites/mygo/app/public/wp-content/debug.log`

## 替代方案：使用正式網域

如果你有正式網域，可以：

1. 將網站部署到正式環境
2. 使用正式網域的 HTTPS URL
3. 在 LINE Developers Console 設定正式的 Callback URL
4. 就不需要 ngrok 了
