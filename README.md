# HMT Admissions API (Phase 1)

## 項目簡介
本專案為 HMT Admissions 系統的後端 API，提供學習包（Lesson Packs）、練習嘗試（Attempts）、學習進度（Progress）及複習隊列（Review Queue）等核心功能。Phase 1 階段採用記憶體（Memory）作為資料儲存方式，方便快速開發與測試。

## 前置需求
- **PHP**: 8.3 或以上版本
- **Composer**: 用於依賴管理（目前階段尚未大量使用外部套件，但建議安裝）

## 安裝與設定

1. **複製環境變數設定檔**：
   ```bash
   cp .env.example .env
   ```

2. **啟動開發伺服器**：
   請在專案根目錄下執行以下指令：
   ```bash
   php -S localhost:8000 -t public
   ```

## 測試範例 (Test Examples)

以下使用 `curl` 指令進行 API 測試。

### 1. 匿名登入 (Authentication)
獲取 JWT Token，用於後續請求的驗證。

```bash
curl -X POST http://localhost:8000/v1/auth/anonymous \
  -H "Content-Type: application/json" \
  -d '{"device_id": "test_device_001"}'
```
> **注意**：請將回應中的 `token` 保存，並在後續請求的 Header 中帶上 `Authorization: Bearer <YOUR_TOKEN>`。

### 2. 獲取今日學習包 (Lesson Pack)
查詢特定考試路徑的今日學習內容。

```bash
curl -X GET "http://localhost:8000/v1/lesson-packs/today?exam_path=undergrad_joint" \
  -H "Authorization: Bearer <YOUR_TOKEN>"
```

### 3. 提交練習嘗試 (Submit Attempt)
提交使用者對某題的作答結果。

```bash
curl -X POST http://localhost:8000/v1/attempts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <YOUR_TOKEN>" \
  -d '{
    "question_id": "q-001",
    "answer": "B",
    "elapsed_ms": 12000
  }'
```

### 4. 查詢學習進度 (Progress)
查看使用者的當前學習數據與學科掌握度。

```bash
curl -X GET http://localhost:8000/v1/progress \
  -H "Authorization: Bearer <YOUR_TOKEN>"
```

### 5. 獲取複習隊列 (Review Queue)
獲取需要進行間隔複習的題目列表。

```bash
curl -X GET http://localhost:8000/v1/review/queue \
  -H "Authorization: Bearer <YOUR_TOKEN>"
```

## 開發者工具
- `tools/import_questions.php`: 用於匯入題目的工具（Phase 2 預定功能）。
