# 部署檢查清單

使用本檢查清單確保部署流程的每個步驟都正確完成。

---

## 📋 本地 Git 設定檢查

### 初始化檢查

- [ ] Git 已安裝（執行 `git --version` 確認）
- [ ] 已在外掛目錄執行 `git init`
- [ ] `.git` 目錄已建立
- [ ] `.gitignore` 檔案已建立且包含必要的排除規則

### 使用者資訊設定

- [ ] 已設定 `user.name`（執行 `git config user.name` 確認）
- [ ] 已設定 `user.email`（執行 `git config user.email` 確認）
- [ ] 使用者資訊正確無誤

### 檔案追蹤檢查

- [ ] 執行 `git status` 確認檔案狀態
- [ ] 不需版控的檔案已被 `.gitignore` 排除
- [ ] 所有必要的檔案都已加入追蹤
- [ ] 沒有敏感資訊（密碼、token）被加入版控

### 初始提交

- [ ] 已執行 `git add .` 加入所有檔案
- [ ] 已執行 `git commit` 建立初始提交
- [ ] 提交訊息清楚明確
- [ ] 執行 `git log` 確認提交記錄存在

---

## 🔐 GitHub 身份驗證設定檢查

### Personal Access Token 方式

- [ ] 已登入 GitHub 帳號
- [ ] 已產生 Personal Access Token
- [ ] Token 權限包含 `repo`（完整倉庫存取）
- [ ] Token 已妥善保存（只會顯示一次）
- [ ] 已設定 credential helper 儲存 token
- [ ] 測試推送時不需重複輸入密碼

### SSH Key 方式

- [ ] 已產生 SSH Key（`~/.ssh/id_ed25519` 或 `~/.ssh/id_rsa`）
- [ ] SSH Key 已加入 SSH Agent
- [ ] 公鑰已上傳至 GitHub
- [ ] 執行 `ssh -T git@github.com` 測試連線成功
- [ ] 顯示 "Hi username! You've successfully authenticated..."

---

## 📦 GitHub 倉庫設定檢查

### 倉庫建立

- [ ] 已在 GitHub 建立新倉庫
- [ ] 倉庫名稱為 `mygo-plus-one`
- [ ] 倉庫設定為 **Private**（私人倉庫）
- [ ] 未勾選 "Initialize with README"
- [ ] 倉庫已成功建立

### 遠端連結

- [ ] 已執行 `git remote add origin <url>`
- [ ] 執行 `git remote -v` 確認遠端 URL 正確
- [ ] URL 格式正確（HTTPS 或 SSH）
- [ ] 遠端名稱為 `origin`

---

## 🚀 GitHub 推送檢查

### 首次推送

- [ ] 已執行 `git branch -M main` 設定主分支名稱
- [ ] 已執行 `git push -u origin main` 推送至 GitHub
- [ ] 推送過程沒有錯誤訊息
- [ ] 身份驗證成功（token 或 SSH key）

### 推送驗證

- [ ] 前往 GitHub 倉庫頁面
- [ ] 可以看到所有檔案
- [ ] 可以看到提交歷史
- [ ] 提交訊息正確顯示
- [ ] 提交作者資訊正確
- [ ] 檔案數量與本地一致

---

## 🔌 WP Pusher 安裝檢查

### 外掛安裝

- [ ] 已登入 WordPress 後台
- [ ] 前往「外掛」→「安裝外掛」
- [ ] 搜尋到 "WP Pusher" 外掛
- [ ] 已點擊「立即安裝」
- [ ] 已點擊「啟用」
- [ ] 後台左側選單出現 "WP Pusher" 項目

### GitHub 連結

- [ ] 前往 WP Pusher → Settings
- [ ] 點擊 "Obtain a GitHub Token"
- [ ] 成功授權 WP Pusher 存取 GitHub
- [ ] 已複製產生的 token
- [ ] 已將 token 貼回 WP Pusher 設定頁面
- [ ] 點擊 "Save Token" 儲存
- [ ] 顯示連結成功訊息

---

## ⚙️ WP Pusher 外掛設定檢查

### 外掛安裝設定

- [ ] 前往 WP Pusher → Install Plugin
- [ ] **Plugin Repository** 填寫正確（`username/mygo-plus-one`）
- [ ] **Repository branch** 設定為 `main`
- [ ] **Plugin file** 設定為 `mygo-plus-one.php`
- [ ] 已勾選 **Push-to-Deploy**（啟用自動更新）
- [ ] 點擊 "Install Plugin"

### 安裝驗證

- [ ] 外掛安裝成功，沒有錯誤訊息
- [ ] 前往「外掛」頁面
- [ ] 可以看到 "MYGO +1" 外掛
- [ ] 外掛狀態為「已啟用」
- [ ] 外掛來源顯示為 GitHub

---

## 🔄 自動更新測試

### 本地修改與推送

- [ ] 在本地修改一個檔案（例如在 README 加入測試文字）
- [ ] 執行 `git add .`
- [ ] 執行 `git commit -m "test: 測試自動更新"`
- [ ] 執行 `git push origin main`
- [ ] 推送成功，沒有錯誤訊息

### GitHub 更新確認

- [ ] 前往 GitHub 倉庫頁面
- [ ] 可以看到最新的提交記錄
- [ ] 提交訊息為 "test: 測試自動更新"
- [ ] 檔案內容已更新

### 正式站更新確認

- [ ] 等待約 5-60 分鐘（WP Pusher 更新頻率）
- [ ] 或手動點擊 WP Pusher 的 "Update Plugin" 按鈕
- [ ] 前往正式站檢查檔案內容
- [ ] 確認檔案已更新為最新版本
- [ ] 外掛功能正常運作

---

## 🧪 完整流程測試

### 端對端測試

- [ ] 本地修改程式碼
- [ ] 提交並推送至 GitHub
- [ ] GitHub 顯示最新提交
- [ ] 正式站自動更新（或手動觸發）
- [ ] 正式站程式碼與 GitHub 一致
- [ ] 外掛功能正常運作

### 版本還原測試

- [ ] 執行 `git log --oneline` 查看歷史
- [ ] 選擇一個舊版本進行還原
- [ ] 執行 `git revert <commit-hash>`
- [ ] 推送至 GitHub
- [ ] 正式站自動更新至還原版本
- [ ] 確認還原成功

---

## 👥 多人協作測試（選用）

### 新成員加入

- [ ] 在 GitHub 倉庫新增協作者
- [ ] 新成員收到邀請 email
- [ ] 新成員接受邀請
- [ ] 新成員可以 clone 倉庫
- [ ] 新成員可以推送程式碼

### 協作流程

- [ ] 成員 A 推送變更
- [ ] 成員 B 執行 `git pull` 拉取變更
- [ ] 成員 B 可以看到成員 A 的變更
- [ ] 兩人同時修改不同檔案可以正常合併
- [ ] 衝突可以正確解決

---

## 📝 文件完整性檢查

### 必要文件

- [ ] `README.md` 存在且內容完整
- [ ] `.gitignore` 存在且規則正確
- [ ] `GIT-COMMANDS.md` 存在（選用）
- [ ] `DEPLOYMENT-CHECKLIST.md` 存在（本檔案）
- [ ] `CONTRIBUTING.md` 存在（選用）

### 文件內容

- [ ] README 包含專案簡介
- [ ] README 包含完整的部署流程說明
- [ ] README 包含日常開發流程
- [ ] README 包含常見問題解答
- [ ] 所有指令範例正確可執行
- [ ] 所有連結正常運作

---

## ⚠️ 安全性檢查

### 敏感資訊

- [ ] 沒有密碼被提交至 Git
- [ ] 沒有 API Token 被提交至 Git
- [ ] 沒有資料庫連線資訊被提交至 Git
- [ ] `.env` 檔案已加入 `.gitignore`
- [ ] `wp-config.php` 已加入 `.gitignore`

### 倉庫權限

- [ ] GitHub 倉庫設定為 Private
- [ ] 只有授權人員可以存取倉庫
- [ ] 協作者權限設定正確
- [ ] WP Pusher Token 權限最小化

---

## ✅ 最終確認

### 部署完成確認

- [ ] 所有上述檢查項目都已完成
- [ ] 本地、GitHub、正式站三方程式碼一致
- [ ] 自動更新機制正常運作
- [ ] 團隊成員都了解開發流程
- [ ] 文件齊全且易於理解
- [ ] 備份與還原機制已測試

### 後續維護

- [ ] 定期檢查 WP Pusher 運作狀態
- [ ] 定期更新 Personal Access Token（到期前）
- [ ] 定期檢查 GitHub 倉庫權限
- [ ] 保持文件更新
- [ ] 記錄重要的部署變更

---

## 🆘 遇到問題？

如果任何檢查項目失敗，請參考以下資源：

1. **README.md** - 完整的部署流程說明
2. **GIT-COMMANDS.md** - Git 指令快速參考
3. **常見問題** - README 中的常見問題章節
4. **GitHub 文件** - https://docs.github.com
5. **WP Pusher 文件** - https://wppusher.com/documentation

---

## 📊 檢查清單統計

完成項目：_____ / 總項目

完成率：_____%

檢查日期：__________

檢查人員：__________

備註：
