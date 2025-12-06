# 多 IDE 協作指南

本指南說明同時使用 Cursor 和 Kiro（或其他 IDE）進行開發時需要注意的事項。

## 可能遇到的問題

### 1. 檔案編輯衝突

**問題：** 兩個 IDE 同時編輯同一個檔案時，可能導致：
- 檔案鎖定衝突
- 自動儲存覆蓋彼此的變更
- 未儲存的變更遺失

**解決方案：**
- ✅ 避免同時在兩個 IDE 中開啟同一個檔案
- ✅ 在切換 IDE 前，先儲存並關閉檔案
- ✅ 使用 Git 分支策略，在不同 IDE 中處理不同的功能

### 2. Git 操作衝突

**問題：** 兩個 IDE 同時執行 Git 操作可能導致：
- 提交衝突
- 分支操作衝突
- 遠端推送衝突

**解決方案：**
- ✅ 在一個 IDE 中完成 Git 操作後，再在另一個 IDE 中使用
- ✅ 切換 IDE 前，先執行 `git pull` 同步最新版本
- ✅ 使用 Git 分支，在不同 IDE 中處理不同的功能分支

### 3. IDE 設定檔衝突

**問題：** 不同 IDE 可能有不同的設定檔：
- Cursor 使用 `.cursorrules`
- Kiro 可能有自己的設定檔
- 可能產生設定不一致

**解決方案：**
- ✅ `.cursorrules` 只適用於 Cursor，不影響 Kiro
- ✅ 確保 `.gitignore` 已排除 IDE 特定設定檔（`.vscode/`, `.idea/` 等）
- ✅ 開發規範文件（`DEVELOPMENT-GUIDELINES.md`）是共用的，兩個 IDE 都應該遵循

### 4. 自動格式化衝突

**問題：** 不同 IDE 可能有不同的格式化設定：
- 縮排方式不同
- 程式碼風格不同
- 自動格式化可能覆蓋彼此的變更

**解決方案：**
- ✅ 統一使用專案的程式碼風格規範（參考 `DEVELOPMENT-GUIDELINES.md`）
- ✅ 在提交前統一格式化（使用相同的工具）
- ✅ 避免在兩個 IDE 中同時開啟自動格式化

## 最佳實踐

### 工作流程建議

**方式 1：功能分工**
- Cursor 處理特定功能（例如：AI 輔助開發）
- Kiro 處理其他功能（例如：除錯、測試）
- 使用 Git 分支分離工作

**方式 2：時間分工**
- 在 Cursor 中完成一個功能並提交後，再切換到 Kiro
- 切換前先執行 `git pull` 同步

**方式 3：檔案分工**
- Cursor 處理特定檔案或目錄
- Kiro 處理其他檔案或目錄
- 避免同時編輯相同的檔案

### 切換 IDE 前的檢查清單

在從一個 IDE 切換到另一個 IDE 前，確認：

- [ ] 所有變更已儲存
- [ ] 已執行 `git status` 確認檔案狀態
- [ ] 已執行 `git add` 和 `git commit`（如果需要）
- [ ] 已執行 `git pull` 同步最新版本
- [ ] 已關閉不需要的檔案

### 切換 IDE 後的檢查清單

在開啟另一個 IDE 後，確認：

- [ ] 執行 `git status` 確認檔案狀態
- [ ] 執行 `git pull` 確保是最新版本
- [ ] 檢查是否有未提交的變更
- [ ] 確認開發環境正常運作

## Git 操作建議

### 避免衝突的操作順序

```bash
# 1. 在 Cursor 中完成工作
git add .
git commit -m "feat: 新增功能 X"
git push origin feature/x

# 2. 切換到 Kiro 前
git pull origin main  # 同步最新版本

# 3. 在 Kiro 中工作
# ... 開發工作 ...

# 4. 切換回 Cursor 前
git add .
git commit -m "fix: 修正問題 Y"
git pull origin main  # 同步最新版本
git push origin feature/x
```

### 使用分支策略

```bash
# Cursor 使用 feature/cursor-work 分支
git checkout -b feature/cursor-work
# ... 在 Cursor 中開發 ...

# Kiro 使用 feature/kiro-work 分支
git checkout -b feature/kiro-work
# ... 在 Kiro 中開發 ...

# 完成後合併到 main
git checkout main
git merge feature/cursor-work
git merge feature/kiro-work
```

## IDE 特定設定

### Cursor 設定

- `.cursorrules` - 自動載入開發規範
- 使用 Cursor 的 AI 功能時，確保遵循開發規範

### Kiro 設定

- 參考 `DEVELOPMENT-GUIDELINES.md` 手動遵循開發規範
- 確保 Kiro 的格式化設定與專案規範一致

## 檔案鎖定機制

某些 IDE 可能會鎖定檔案（例如 `.swp` 檔案）。如果遇到檔案鎖定問題：

1. 檢查是否有 `.swp` 或 `.swo` 檔案（已在 `.gitignore` 中排除）
2. 關閉所有 IDE，刪除鎖定檔案
3. 重新開啟 IDE

## 常見問題

### Q: 兩個 IDE 可以同時開啟同一個專案嗎？

**A:** 可以，但建議：
- 避免同時編輯同一個檔案
- 使用 Git 分支分離工作
- 定期同步變更

### Q: `.cursorrules` 會影響 Kiro 嗎？

**A:** 不會。`.cursorrules` 只適用於 Cursor。Kiro 需要手動參考 `DEVELOPMENT-GUIDELINES.md`。

### Q: 如果兩個 IDE 同時提交會怎樣？

**A:** 可能會產生 Git 衝突。建議：
- 在一個 IDE 中完成提交後，再在另一個 IDE 中工作
- 使用分支策略分離工作

### Q: 如何確保兩個 IDE 使用相同的程式碼風格？

**A:** 
- 參考 `DEVELOPMENT-GUIDELINES.md` 中的程式碼風格規範
- 使用相同的格式化工具（例如：PHP_CodeSniffer）
- 在提交前統一格式化

## 總結

同時使用多個 IDE 是可行的，但需要：

1. ✅ 遵循 Git 最佳實踐
2. ✅ 避免同時編輯相同檔案
3. ✅ 使用分支策略分離工作
4. ✅ 定期同步變更
5. ✅ 遵循統一的開發規範

只要遵循這些原則，多 IDE 協作不會有問題。

