# 協作開發規範

歡迎參與 MYGO +1 專案的開發！本文件說明團隊協作的規範與最佳實踐。

---

## 📋 目錄

- [開發流程](#開發流程)
- [提交訊息規範](#提交訊息規範)
- [分支命名規範](#分支命名規範)
- [Code Review 流程](#code-review-流程)
- [衝突解決原則](#衝突解決原則)
- [程式碼風格](#程式碼風格)
- [測試要求](#測試要求)

---

## 開發流程

### 1. 開始新功能開發

```bash
# 1. 確保 main 分支是最新的
git checkout main
git pull origin main

# 2. 建立功能分支
git checkout -b feature/your-feature-name

# 3. 開發功能
# 編輯程式碼...

# 4. 提交變更
git add .
git commit -m "feat: 新增功能描述"

# 5. 推送至 GitHub
git push origin feature/your-feature-name
```

### 2. 建立 Pull Request

1. 前往 GitHub 倉庫頁面
2. 點擊 **Pull requests** → **New pull request**
3. 選擇 `base: main` ← `compare: feature/your-feature-name`
4. 填寫 PR 標題與描述
5. 指定 Reviewer（至少一位團隊成員）
6. 點擊 **Create pull request**

### 3. Code Review

- Reviewer 檢視程式碼並提供意見
- 開發者根據意見修改程式碼
- 修改後推送至同一分支（PR 會自動更新）
- 通過 Review 後，Reviewer 點擊 **Approve**

### 4. 合併至 Main

- 確認所有 Review 意見都已處理
- 確認沒有衝突
- 點擊 **Merge pull request**
- 選擇合併方式（建議使用 **Squash and merge**）
- 刪除功能分支（GitHub 會提示）

### 5. 本地清理

```bash
# 切換回 main 並更新
git checkout main
git pull origin main

# 刪除本地功能分支
git branch -d feature/your-feature-name
```

---

## 提交訊息規範

### 格式

```
<類型>: <簡短描述>

<詳細說明>（選填）

<相關 Issue>（選填）
```

### 類型

| 類型 | 說明 | 範例 |
|------|------|------|
| `feat` | 新功能 | `feat: 新增訂單累加功能` |
| `fix` | 修正錯誤 | `fix: 修正圖片壓縮失敗問題` |
| `docs` | 文件更新 | `docs: 更新 README 部署說明` |
| `style` | 程式碼格式調整（不影響功能） | `style: 調整縮排與空格` |
| `refactor` | 重構程式碼（不新增功能也不修正錯誤） | `refactor: 重構訂單服務類別` |
| `perf` | 效能優化 | `perf: 優化圖片處理速度` |
| `test` | 測試相關 | `test: 新增訂單服務單元測試` |
| `chore` | 建置工具或輔助工具變動 | `chore: 更新 composer 依賴` |
| `revert` | 還原先前的提交 | `revert: 還原 commit abc123` |

### 範例

**好的提交訊息：**

```
feat: 新增 LINE 內部瀏覽器偵測功能

- 實作 detectLineInAppBrowser() 方法
- 偵測 User-Agent 中的 Line 關鍵字
- 自動導向外部瀏覽器

Closes #123
```

```
fix: 修正訂單狀態更新失敗問題

當訂單狀態從「未付款」更新為「已付款」時，
資料庫更新失敗。原因是 SQL 語法錯誤。

已修正 SQL 語法並新增錯誤處理。
```

**不好的提交訊息：**

```
update code
```

```
fix bug
```

```
修改了一些東西
```

### 規則

1. **簡短描述**：
   - 不超過 50 字
   - 使用祈使句（例如：「新增」而非「新增了」）
   - 不加句號

2. **詳細說明**：
   - 與簡短描述之間空一行
   - 說明「為什麼」而非「做了什麼」
   - 每行不超過 72 字

3. **相關 Issue**：
   - 使用 `Closes #123` 自動關閉 Issue
   - 使用 `Refs #123` 參考 Issue

---

## 分支命名規範

### 格式

```
<類型>/<簡短描述>
```

### 類型

| 類型 | 說明 | 範例 |
|------|------|------|
| `feature/` | 新功能開發 | `feature/order-accumulation` |
| `fix/` | 錯誤修正 | `fix/image-compression` |
| `hotfix/` | 緊急修復（直接從 main 分支） | `hotfix/critical-security-issue` |
| `refactor/` | 程式碼重構 | `refactor/order-service` |
| `docs/` | 文件更新 | `docs/update-readme` |
| `test/` | 測試相關 | `test/add-unit-tests` |

### 命名規則

1. 使用小寫字母
2. 使用連字號 `-` 分隔單字
3. 簡短且具描述性
4. 避免使用特殊字元

### 範例

**好的分支名稱：**

```
feature/line-browser-detection
fix/order-status-update
hotfix/security-vulnerability
refactor/image-processor
```

**不好的分支名稱：**

```
new-feature
fix
my-branch
test123
```

---

## Code Review 流程

### Reviewer 責任

1. **及時 Review**：
   - 在 24 小時內完成 Review
   - 如果無法及時 Review，請通知團隊

2. **仔細檢查**：
   - 程式碼邏輯是否正確
   - 是否符合需求規格
   - 是否有潛在的錯誤或安全問題
   - 程式碼風格是否一致
   - 是否有適當的註解
   - 是否有測試

3. **建設性意見**：
   - 說明「為什麼」需要修改
   - 提供具體的改進建議
   - 使用友善的語氣

4. **批准標準**：
   - 程式碼功能正確
   - 沒有明顯的錯誤或安全問題
   - 符合團隊的程式碼風格
   - 有適當的測試（如果需要）

### 開發者責任

1. **清楚的 PR 描述**：
   - 說明這個 PR 做了什麼
   - 為什麼需要這個變更
   - 如何測試這個變更
   - 相關的 Issue 或文件

2. **回應 Review 意見**：
   - 及時回應 Reviewer 的意見
   - 如果不同意，請說明理由
   - 修改後推送至同一分支

3. **保持 PR 小而專注**：
   - 一個 PR 只做一件事
   - 避免混合多個不相關的變更
   - 大功能可以拆分成多個 PR

### Review 意見類型

使用以下標籤標示意見的重要性：

- **[必須]**：必須修改才能合併
- **[建議]**：建議修改，但不強制
- **[問題]**：需要討論或澄清
- **[讚]**：做得好的地方

### 範例

**好的 Review 意見：**

```
[必須] 這裡應該檢查 $user_id 是否為 null，
避免後續的 SQL 查詢失敗。

建議加入：
if (empty($user_id)) {
    throw new InvalidArgumentException('User ID is required');
}
```

```
[建議] 這個函數有點長（超過 50 行），
建議拆分成多個小函數，提高可讀性。
```

```
[讚] 錯誤處理做得很好，考慮到了各種邊界情況！
```

**不好的 Review 意見：**

```
這裡有問題
```

```
改一下
```

```
不行
```

---

## 衝突解決原則

### 預防衝突

1. **經常同步**：
   - 每天開始工作前執行 `git pull origin main`
   - 推送前再次執行 `git pull origin main`

2. **小步提交**：
   - 頻繁提交小的變更
   - 避免累積大量變更才提交

3. **溝通協調**：
   - 如果多人要修改同一個檔案，先溝通
   - 使用 Issue 或 PR 說明正在進行的工作

### 解決衝突

當發生衝突時：

```bash
# 1. 拉取最新的 main 分支
git pull origin main

# 2. Git 會提示哪些檔案有衝突
# 編輯衝突檔案，尋找衝突標記：
# <<<<<<< HEAD
# 你的變更
# =======
# 其他人的變更
# >>>>>>> branch-name

# 3. 決定保留哪個版本（或合併兩者）
# 移除衝突標記 <<<<<<<, =======, >>>>>>>

# 4. 加入解決後的檔案
git add <file>

# 5. 完成合併
git commit -m "merge: 解決與 main 分支的衝突"

# 6. 推送
git push origin feature/your-branch
```

### 衝突解決原則

1. **保留功能完整性**：
   - 確保合併後的程式碼功能正常
   - 不要隨意刪除他人的程式碼

2. **測試合併結果**：
   - 解決衝突後務必測試
   - 確認沒有破壞現有功能

3. **尋求協助**：
   - 如果不確定如何解決，請詢問相關開發者
   - 可以在 PR 中 @ 相關人員討論

4. **記錄決策**：
   - 在提交訊息中說明如何解決衝突
   - 如果有特殊考量，請在 PR 中說明

---

## 程式碼風格

### PHP 程式碼風格

遵循 [PSR-12](https://www.php-fig.org/psr/psr-12/) 標準：

1. **縮排**：使用 4 個空格（不使用 Tab）
2. **大括號**：開始大括號在同一行，結束大括號獨立一行
3. **命名**：
   - 類別名稱使用 PascalCase：`OrderService`
   - 方法名稱使用 camelCase：`createOrder()`
   - 常數使用 UPPER_CASE：`MAX_RETRY_COUNT`

### JavaScript 程式碼風格

1. **縮排**：使用 2 個空格
2. **引號**：使用單引號 `'`
3. **分號**：每個語句結尾加分號
4. **命名**：使用 camelCase

### 註解

1. **函數註解**：
   ```php
   /**
    * 建立新訂單
    *
    * @param int $user_id 使用者 ID
    * @param int $product_id 商品 ID
    * @param int $quantity 數量
    * @return array 訂單資料
    * @throws InvalidArgumentException 當參數無效時
    */
   public function createOrder($user_id, $product_id, $quantity) {
       // ...
   }
   ```

2. **複雜邏輯註解**：
   ```php
   // 檢查庫存是否足夠
   // 如果庫存不足，回傳可購買的最大數量
   if ($quantity > $stock) {
       return ['max_quantity' => $stock];
   }
   ```

3. **TODO 註解**：
   ```php
   // TODO: 實作訂單通知功能
   // TODO(username): 優化查詢效能
   ```

---

## 測試要求

### 單元測試

1. **何時需要測試**：
   - 新增核心業務邏輯
   - 修改現有功能
   - 修正錯誤

2. **測試覆蓋率**：
   - 核心功能應有 80% 以上的測試覆蓋率
   - 關鍵路徑應有 100% 的測試覆蓋率

3. **測試命名**：
   ```php
   public function test_create_order_with_valid_data() {
       // ...
   }
   
   public function test_create_order_throws_exception_when_out_of_stock() {
       // ...
   }
   ```

### 執行測試

```bash
# 執行所有測試
composer test

# 執行特定測試檔案
composer test tests/OrderServiceTest.php

# 查看測試覆蓋率
composer test-coverage
```

### Pull Request 要求

- 所有測試必須通過才能合併
- 新功能必須包含對應的測試
- 修正錯誤應包含重現錯誤的測試

---

## 其他規範

### 安全性

1. **不要提交敏感資訊**：
   - 密碼、API Token、金鑰
   - 資料庫連線資訊
   - 個人資料

2. **使用環境變數**：
   - 敏感設定使用 `.env` 檔案
   - `.env` 已加入 `.gitignore`

3. **輸入驗證**：
   - 所有使用者輸入都要驗證
   - 使用參數化查詢防止 SQL Injection
   - 過濾 XSS 攻擊

### 效能

1. **資料庫查詢**：
   - 避免 N+1 查詢問題
   - 使用適當的索引
   - 避免在迴圈中執行查詢

2. **快取**：
   - 適當使用快取減少重複計算
   - 設定合理的快取過期時間

### 文件

1. **更新文件**：
   - 新增功能時更新 README
   - API 變更時更新 API 文件
   - 保持文件與程式碼同步

2. **註解程式碼**：
   - 複雜邏輯加上註解說明
   - 公開 API 加上完整的 PHPDoc

---

## 問題與建議

如果對協作規範有任何問題或建議，請：

1. 在團隊會議中提出討論
2. 建立 Issue 說明問題
3. 提交 PR 修改本文件

---

## 參考資源

- [Git 官方文件](https://git-scm.com/doc)
- [GitHub Flow](https://guides.github.com/introduction/flow/)
- [PSR-12 程式碼風格](https://www.php-fig.org/psr/psr-12/)
- [Conventional Commits](https://www.conventionalcommits.org/)

---

感謝您遵循這些規範，讓我們的協作更加順暢！🎉
