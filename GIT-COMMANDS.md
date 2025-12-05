# Git 常用指令快速參考

本文件提供 Git 常用指令的快速參考，方便開發者查詢。

## 初始化與設定

### 初始化倉庫
```bash
git init
```
在當前目錄建立新的 Git 倉庫。

### 設定使用者資訊
```bash
git config user.name "Your Name"
git config user.email "your@example.com"
```
設定提交時使用的名稱與 email。

### 查看設定
```bash
git config --list
```
顯示所有 Git 設定。

---

## 基本操作

### 查看狀態
```bash
git status
```
顯示工作目錄與暫存區的狀態。

### 查看變更
```bash
git diff
```
顯示尚未暫存的變更。

```bash
git diff --staged
```
顯示已暫存但尚未提交的變更。

### 加入檔案至暫存區
```bash
git add <file>
```
加入特定檔案至暫存區。

```bash
git add .
```
加入所有變更至暫存區。

```bash
git add -p
```
互動式選擇要加入的變更。

### 提交變更
```bash
git commit -m "commit message"
```
提交暫存區的變更。

```bash
git commit -am "commit message"
```
加入所有已追蹤檔案的變更並提交（跳過 git add）。

```bash
git commit --amend
```
修改最後一次提交（可修改訊息或加入遺漏的檔案）。

---

## 遠端操作

### 查看遠端倉庫
```bash
git remote -v
```
顯示所有遠端倉庫的 URL。

### 新增遠端倉庫
```bash
git remote add origin <url>
```
新增名為 origin 的遠端倉庫。

### 推送至遠端
```bash
git push origin main
```
推送 main 分支至 origin 遠端倉庫。

```bash
git push -u origin main
```
推送並設定上游分支（首次推送使用）。

```bash
git push -f origin main
```
強制推送（會覆蓋遠端歷史，請謹慎使用）。

### 拉取遠端變更
```bash
git pull origin main
```
從 origin 拉取 main 分支並合併至當前分支。

```bash
git fetch origin
```
從 origin 拉取所有分支，但不合併。

---

## 分支操作

### 查看分支
```bash
git branch
```
顯示所有本地分支。

```bash
git branch -a
```
顯示所有本地與遠端分支。

### 建立分支
```bash
git branch <branch-name>
```
建立新分支。

```bash
git checkout -b <branch-name>
```
建立並切換至新分支。

### 切換分支
```bash
git checkout <branch-name>
```
切換至指定分支。

```bash
git switch <branch-name>
```
切換至指定分支（較新的指令）。

### 合併分支
```bash
git merge <branch-name>
```
將指定分支合併至當前分支。

### 刪除分支
```bash
git branch -d <branch-name>
```
刪除本地分支（已合併）。

```bash
git branch -D <branch-name>
```
強制刪除本地分支（未合併）。

```bash
git push origin --delete <branch-name>
```
刪除遠端分支。

### 重新命名分支
```bash
git branch -m <new-name>
```
重新命名當前分支。

```bash
git branch -M main
```
強制重新命名當前分支為 main。

---

## 歷史查詢

### 查看提交歷史
```bash
git log
```
顯示完整的提交歷史。

```bash
git log --oneline
```
以簡短格式顯示提交歷史。

```bash
git log --graph --oneline --all
```
以圖形化方式顯示所有分支的提交歷史。

```bash
git log -n 5
```
只顯示最近 5 次提交。

```bash
git log --author="Name"
```
顯示特定作者的提交。

### 查看特定提交
```bash
git show <commit-hash>
```
顯示特定提交的詳細資訊。

### 查看檔案歷史
```bash
git log -- <file>
```
顯示特定檔案的提交歷史。

```bash
git blame <file>
```
顯示檔案每一行的最後修改者與提交。

---

## 還原與重置

### 還原工作目錄的變更
```bash
git checkout -- <file>
```
放棄工作目錄中特定檔案的變更。

```bash
git restore <file>
```
放棄工作目錄中特定檔案的變更（較新的指令）。

### 取消暫存
```bash
git reset HEAD <file>
```
將檔案從暫存區移除，但保留工作目錄的變更。

```bash
git restore --staged <file>
```
將檔案從暫存區移除（較新的指令）。

### 還原提交
```bash
git revert <commit-hash>
```
建立新提交來還原指定提交的變更（保留歷史）。

### 重置提交
```bash
git reset --soft <commit-hash>
```
重置至指定提交，保留暫存區與工作目錄的變更。

```bash
git reset --mixed <commit-hash>
```
重置至指定提交，保留工作目錄的變更（預設）。

```bash
git reset --hard <commit-hash>
```
重置至指定提交，放棄所有變更（危險）。

---

## 暫存變更

### 暫存當前變更
```bash
git stash
```
暫存工作目錄與暫存區的變更。

```bash
git stash save "message"
```
暫存變更並加上說明訊息。

### 查看暫存清單
```bash
git stash list
```
顯示所有暫存的變更。

### 恢復暫存的變更
```bash
git stash pop
```
恢復最近的暫存並從清單中移除。

```bash
git stash apply
```
恢復最近的暫存但保留在清單中。

```bash
git stash apply stash@{n}
```
恢復特定的暫存。

### 刪除暫存
```bash
git stash drop
```
刪除最近的暫存。

```bash
git stash clear
```
刪除所有暫存。

---

## 標籤

### 建立標籤
```bash
git tag <tag-name>
```
建立輕量標籤。

```bash
git tag -a <tag-name> -m "message"
```
建立附註標籤。

### 查看標籤
```bash
git tag
```
顯示所有標籤。

### 推送標籤
```bash
git push origin <tag-name>
```
推送特定標籤至遠端。

```bash
git push origin --tags
```
推送所有標籤至遠端。

### 刪除標籤
```bash
git tag -d <tag-name>
```
刪除本地標籤。

```bash
git push origin --delete <tag-name>
```
刪除遠端標籤。

---

## 其他實用指令

### 清理未追蹤的檔案
```bash
git clean -n
```
預覽會被刪除的未追蹤檔案。

```bash
git clean -f
```
刪除未追蹤的檔案。

```bash
git clean -fd
```
刪除未追蹤的檔案與目錄。

### 搜尋程式碼
```bash
git grep "search-term"
```
在所有追蹤的檔案中搜尋文字。

### 查看簡短狀態
```bash
git status -s
```
以簡短格式顯示狀態。

### 查看遠端分支資訊
```bash
git remote show origin
```
顯示 origin 遠端倉庫的詳細資訊。

---

## 快速參考表

| 指令 | 說明 |
|------|------|
| `git init` | 初始化倉庫 |
| `git status` | 查看狀態 |
| `git add .` | 加入所有變更 |
| `git commit -m "msg"` | 提交變更 |
| `git push origin main` | 推送至遠端 |
| `git pull origin main` | 拉取遠端變更 |
| `git branch` | 查看分支 |
| `git checkout -b <name>` | 建立並切換分支 |
| `git merge <branch>` | 合併分支 |
| `git log --oneline` | 查看提交歷史 |
| `git revert <hash>` | 還原提交 |
| `git stash` | 暫存變更 |

---

## 緊急救援

### 我不小心提交了錯誤的內容
```bash
# 修改最後一次提交
git commit --amend

# 或還原最後一次提交
git reset --soft HEAD~1
```

### 我想放棄所有本地變更
```bash
git reset --hard HEAD
git clean -fd
```

### 我推送了錯誤的內容到遠端
```bash
# 還原並推送新提交（建議）
git revert <commit-hash>
git push origin main

# 或強制重置（危險，會影響其他人）
git reset --hard <commit-hash>
git push -f origin main
```

### 我想找回被刪除的提交
```bash
# 查看所有操作歷史
git reflog

# 恢復至特定提交
git reset --hard <commit-hash>
```

---

## 更多資源

- [Git 官方文件](https://git-scm.com/doc)
- [GitHub Git 速查表](https://training.github.com/downloads/github-git-cheat-sheet/)
- [Pro Git 書籍（繁體中文）](https://git-scm.com/book/zh-tw/v2)
