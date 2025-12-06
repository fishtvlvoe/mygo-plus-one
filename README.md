# MYGO +1 WordPress Plugin

MYGO +1 是一個 WordPress 外掛，整合 LINE 官方帳號、FluentCart、FluentCommunity 與 FluentCRM，讓賣家能透過 LINE 上傳商品資訊，自動同步到 WordPress 電商系統，並支援社群 +1 關鍵字下單功能。

## 功能特色

- **LINE 商品上傳**: 賣家透過 LINE 官方帳號發送商品資訊，自動建立商品至 FluentCart
- **社群 +1 下單**: 買家在 FluentCommunity 貼文留言 +1 即可快速下單
- **LINE Login 整合**: 使用 LINE 帳號登入並自動同步使用者資料
- **自動發布貼文**: 商品上架後自動發布至 FluentCommunity
- **LINE 卡片分享**: 自動產生可分享的 LINE Flex Message 卡片
- **訂單管理**: 完整的後台訂單管理介面，支援多種訂單狀態追蹤
- **角色權限**: 支援買家、賣家、小幫手、管理員四種角色權限
- **iOS 風格介面**: 採用 Cupertino 設計風格的現代化介面

## 系統需求

- WordPress 5.8 或更高版本
- PHP 7.4 或更高版本
- FluentCart 外掛
- FluentCommunity 外掛
- FluentCRM 外掛（選用）
- LINE Developers 帳號（LINE Messaging API 與 LINE Login）

## 部署流程

本專案採用 Git + GitHub + WP Pusher 的自動化部署流程，確保程式碼有完整的版本歷史、支援多人協作、可快速還原錯誤版本。

### 流程概念

```
本地電腦 (Local) ➡ 推送到 GitHub ➡ 正式站 (透過 WP Pusher 自動拉取)
```

### 優點

- ✅ 完整的版本歷史記錄
- ✅ 改壞了可以隨時還原
- ✅ 支援多人協作開發
- ✅ 自動化部署，減少人為錯誤
- ✅ 程式碼安全儲存在雲端

---

## 📋 目錄

- [本地端設定](#本地端設定)
  - [1. 初始化 Git 倉庫](#1-初始化-git-倉庫)
  - [2. 建立 GitHub 倉庫](#2-建立-github-倉庫)
  - [3. 設定身份驗證](#3-設定身份驗證)
  - [4. 首次推送](#4-首次推送)
- [正式站設定](#正式站設定)
  - [5. 安裝 WP Pusher](#5-安裝-wp-pusher)
  - [6. 連結 GitHub 帳號](#6-連結-github-帳號)
  - [7. 安裝外掛並啟用自動更新](#7-安裝外掛並啟用自動更新)
- [日常開發流程](#日常開發流程)
- [版本還原](#版本還原)
- [多人協作](#多人協作)
- [分支管理](#分支管理)
- [常見問題](#常見問題)

---

## 本地端設定

### 1. 初始化 Git 倉庫

在外掛目錄執行以下指令：

```bash
cd mygo-plus-one
git init
```

設定使用者資訊（請替換成您的名稱與 email）：

```bash
git config user.name "Your Name"
git config user.email "your.email@example.com"
```

### 2. 建立 GitHub 倉庫

1. 登入 [GitHub](https://github.com)
2. 點擊右上角的 **+** → **New repository**
3. 輸入倉庫名稱：`mygo-plus-one`
4. 選擇 **Private**（私人倉庫，保護程式碼安全）
5. **不要**勾選 "Initialize with README"（因為本地已有程式碼）
6. 點擊 **Create repository**

### 3. 設定身份驗證

GitHub 提供兩種身份驗證方式，選擇其中一種即可：

#### 方式 A: Personal Access Token（推薦給初學者）

**產生 Token：**

1. GitHub 右上角 → **Settings**
2. 左側選單 → **Developer settings** → **Personal access tokens** → **Tokens (classic)**
3. 點擊 **Generate new token (classic)**
4. 勾選權限：`repo`（完整倉庫存取權限）
5. 設定過期時間（建議 90 天）
6. 點擊 **Generate token**
7. **複製 token**（只會顯示一次，請妥善保存）

**儲存 Token（避免重複輸入）：**

```bash
# macOS
git config --global credential.helper osxkeychain

# Windows
git config --global credential.helper wincred

# Linux
git config --global credential.helper store
```

#### 方式 B: SSH Key（推薦給長期開發）

**產生 SSH Key：**

```bash
ssh-keygen -t ed25519 -C "your.email@example.com"
# 或使用 RSA（相容性更好）
ssh-keygen -t rsa -b 4096 -C "your.email@example.com"
```

**新增至 SSH Agent：**

```bash
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519
```

**上傳至 GitHub：**

1. 複製公鑰內容：
   ```bash
   cat ~/.ssh/id_ed25519.pub
   ```
2. GitHub → **Settings** → **SSH and GPG keys** → **New SSH key**
3. 貼上公鑰內容並儲存

**測試連線：**

```bash
ssh -T git@github.com
# 成功會顯示：Hi username! You've successfully authenticated...
```

### 4. 首次推送

設定遠端倉庫連結（請替換 `username` 為您的 GitHub 帳號）：

```bash
# HTTPS 方式（使用 Personal Access Token）
git remote add origin https://github.com/username/mygo-plus-one.git

# SSH 方式（使用 SSH Key）
git remote add origin git@github.com:username/mygo-plus-one.git
```

加入所有檔案並建立初始提交：

```bash
git add .
git commit -m "Initial commit: MYGO +1 plugin"
```

設定主分支名稱並推送：

```bash
git branch -M main
git push -u origin main
```

**驗證推送成功：**
- 前往 GitHub 倉庫頁面，應該可以看到所有檔案與提交記錄

---

## 正式站設定

### 5. 安裝 WP Pusher

1. 登入 WordPress 後台
2. 前往 **外掛** → **安裝外掛**
3. 搜尋 **"WP Pusher"**
4. 點擊 **立即安裝** → **啟用**

### 6. 連結 GitHub 帳號

1. 後台左側選單 → **WP Pusher** → **Settings**
2. 點擊 **GitHub** 區塊的 **Obtain a GitHub Token**
3. 授權 WP Pusher 存取您的 GitHub 帳號
4. 複製產生的 token 並貼回 WP Pusher 設定頁面
5. 點擊 **Save Token**

### 7. 安裝外掛並啟用自動更新

1. 後台左側選單 → **WP Pusher** → **Install Plugin**
2. 填寫以下資訊：
   - **Plugin Repository**: `username/mygo-plus-one`（替換 username）
   - **Repository branch**: `main`
   - **Plugin file**: `mygo-plus-one.php`
   - 勾選 **Push-to-Deploy**（啟用自動更新）
3. 點擊 **Install Plugin**

**完成！** 現在當您推送程式碼至 GitHub 時，正式站會自動更新外掛。

---

## 日常開發流程

### 修改程式碼 → 提交 → 推送

```bash
# 1. 查看變更
git status
git diff

# 2. 加入變更至暫存區
git add .
# 或只加入特定檔案
git add includes/Services/LineWebhookHandler.php

# 3. 提交變更
git commit -m "fix: 修正 LINE Webhook 簽章驗證邏輯"

# 4. 推送至 GitHub
git push origin main

# 5. WP Pusher 會自動偵測並更新正式站（約 1 小時內）
```

### 提交訊息規範

建議使用以下格式：

```
類型: 簡短描述（50 字以內）

詳細說明（選填）
```

**類型：**
- `feat`: 新功能
- `fix`: 修正錯誤
- `docs`: 文件更新
- `style`: 程式碼格式調整
- `refactor`: 重構程式碼
- `test`: 測試相關
- `chore`: 建置工具或輔助工具變動

**範例：**
```bash
git commit -m "feat: 新增訂單累加功能"
git commit -m "fix: 修正圖片壓縮失敗問題"
git commit -m "docs: 更新 README 部署說明"
```

---

## 版本還原

### 查看提交歷史

```bash
git log --oneline
```

輸出範例：
```
a1b2c3d (HEAD -> main, origin/main) fix: 修正訂單狀態更新
e4f5g6h feat: 新增商品篩選功能
i7j8k9l Initial commit
```

### 還原方法

#### 方法 1: Revert（建議，保留歷史）

建立新提交來還原指定提交的變更：

```bash
git revert a1b2c3d
git push origin main
```

#### 方法 2: Reset（危險，會刪除歷史）

回到指定版本，刪除之後的所有提交：

```bash
git reset --hard e4f5g6h
git push -f origin main  # 強制推送
```

⚠️ **警告：** 此方法會永久刪除提交歷史，請謹慎使用！

#### 方法 3: 建立修復分支

從舊版本建立新分支：

```bash
git checkout -b hotfix/revert-to-stable e4f5g6h
git push origin hotfix/revert-to-stable
```

然後在 WP Pusher 切換追蹤分支至 `hotfix/revert-to-stable`。

---

## 多人協作

### 新成員加入

**倉庫擁有者：**

1. GitHub 倉庫頁面 → **Settings** → **Collaborators**
2. 點擊 **Add people**
3. 輸入成員的 GitHub 帳號
4. 選擇權限：
   - **Write**：可推送程式碼
   - **Read**：只能拉取程式碼

**新成員：**

```bash
# Clone 倉庫至本地
git clone https://github.com/username/mygo-plus-one.git
cd mygo-plus-one

# 設定使用者資訊
git config user.name "Member Name"
git config user.email "member@example.com"
```

### 協作開發流程

```bash
# 1. 開始工作前先拉取最新版本
git pull origin main

# 2. 修改程式碼並提交
git add .
git commit -m "feat: 新增功能 X"

# 3. 推送前再次拉取（避免衝突）
git pull origin main

# 4. 如果有衝突，解決後再提交
# 編輯衝突檔案，移除衝突標記
git add .
git commit -m "merge: 解決衝突"

# 5. 推送至 GitHub
git push origin main
```

### 衝突解決範例

當檔案出現衝突時，會看到類似標記：

```php
<<<<<<< HEAD
// 你的變更
$order_status = 'pending';
=======
// 其他人的變更
$order_status = 'processing';
>>>>>>> branch-name
```

**解決步驟：**

1. 編輯檔案，移除 `<<<<<<<`, `=======`, `>>>>>>>` 標記
2. 保留正確的程式碼
3. 儲存檔案
4. 執行 `git add .` 和 `git commit`

---

## 分支管理

### 分支策略

- **`main`**: 穩定版本，WP Pusher 追蹤此分支
- **`develop`**: 開發版本，整合所有新功能
- **`feature/*`**: 功能分支，開發單一功能
- **`hotfix/*`**: 緊急修復分支

### 功能開發流程

```bash
# 1. 從 main 建立功能分支
git checkout main
git pull origin main
git checkout -b feature/order-accumulation

# 2. 在功能分支上開發
git add .
git commit -m "feat: 實作訂單累加邏輯"

# 3. 推送功能分支至 GitHub
git push origin feature/order-accumulation

# 4. 功能完成後合併回 main
git checkout main
git pull origin main
git merge feature/order-accumulation

# 5. 推送至 GitHub，觸發正式站更新
git push origin main

# 6. 刪除功能分支（選填）
git branch -d feature/order-accumulation
git push origin --delete feature/order-accumulation
```

### 使用 GitHub Pull Request（建議）

1. 推送功能分支至 GitHub
2. 在 GitHub 建立 **Pull Request**
3. 團隊成員進行 **Code Review**
4. 通過後點擊 **Merge** 合併至 main
5. WP Pusher 自動更新正式站

---

## 常見問題

### Git 相關

#### Q: 推送被拒絕（遠端有新提交）

```bash
# 錯誤訊息
! [rejected] main -> main (fetch first)

# 解決方法
git pull origin main
git push origin main
```

#### Q: 合併衝突

```bash
# 錯誤訊息
CONFLICT (content): Merge conflict in file.php

# 解決方法
# 1. 編輯衝突檔案
# 2. 移除衝突標記 <<<<<<<, =======, >>>>>>>
# 3. 加入並提交
git add file.php
git commit -m "merge: 解決衝突"
```

#### Q: 忘記設定使用者資訊

```bash
# 錯誤訊息
Please tell me who you are

# 解決方法
git config user.name "Your Name"
git config user.email "your@example.com"
```

#### Q: 推送需要身份驗證

```bash
# 錯誤訊息
Authentication failed

# 解決方法（HTTPS）
# 使用 Personal Access Token 而非密碼
git push https://YOUR_TOKEN@github.com/username/repo.git main

# 解決方法（SSH）
# 確認 SSH Key 已上傳至 GitHub
ssh -T git@github.com
```

### WP Pusher 相關

#### Q: 無法連線至 GitHub

**解決方法：**
- 檢查 GitHub Token 是否有效
- 確認倉庫為 Private 時 Token 有 `repo` 權限
- 檢查網路連線

#### Q: 外掛更新失敗

**解決方法：**
- 檢查 Plugin file 路徑是否正確（`mygo-plus-one.php`）
- 確認分支名稱正確（`main`）
- 查看 WP Pusher 錯誤日誌

#### Q: 自動更新未觸發

**解決方法：**
- 確認 Push-to-Deploy 已啟用
- 檢查 GitHub Webhook 設定
- 手動點擊 **Update Plugin** 測試
- WP Pusher 更新頻率約 1 小時，請耐心等待

---

## 開發文件

- [開發規範](DEVELOPMENT-GUIDELINES.md) - WordPress 外掛開發技術規範
- [協作規範](CONTRIBUTING.md) - Git 流程、Code Review 等協作規範
- [部署檢查清單](DEPLOYMENT-CHECKLIST.md) - 部署前的檢查項目
- [Git 指令參考](GIT-COMMANDS.md) - 常用 Git 指令

## 授權

本專案為私有專案，未經授權不得使用或散布。

## 聯絡方式

如有問題或建議，請聯絡開發團隊。
