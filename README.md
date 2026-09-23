# 寵愛 PetShop 系統 - 網頁開啟與執行指南

本專案為一套整合前台展示與後台管理的寵物店系統。
後端採用 PHP 動態組裝 SQL 語法實現四表聯集的複雜查詢，資料庫架構嚴格遵循 3NF/BCNF 正規化設計，並於前端整合 Bootstrap Modal 與 SweetAlert2 提升使用者體驗。
本系統為基於 PHP 與 MySQL 開發的網頁應用程式，請配合 XAMPP 本機伺服器環境執行。

【系統環境確認】
請確認電腦已安裝 XAMPP，並將本專案資料夾 (database_project) 放置於 XAMPP 的預設網頁目錄下。
正確的完整路徑應為：C:\xampp\htdocs\database_project\

【啟動步驟】
1. 啟動伺服器模組：
   開啟「XAMPP Control Panel」控制面板。
   在 Actions 欄位中，將「Apache」與「MySQL」兩個模組點擊 [Start]。
   (當模組名稱的背景亮起綠色，即代表網頁伺服器與資料庫已成功運行)

2. 開啟系統網頁：
   請打開網頁瀏覽器（建議使用 Google Chrome 或 Edge），在上方網址列複製並貼上以下網址，按下 Enter 即可進入系統。

   ▶ [前台 - 使用者首頁]
   http://localhost/database_project/pethouse/index.php

   ▶ [後台 - 管理員介面]
   http://localhost/database_project/admin/pet_mngt.php

【注意事項】
* 執行網頁前，請務必確保 MySQL 服務已呈現綠燈啟動狀態，否則網頁將無法連線，會出現資料庫連線錯誤。
* 若網頁無法載入，請確認 XAMPP 的 Apache 服務是否遭到其他程式 (如 Skype) 佔用 Port 80。


主頁

<img width="2848" height="1302" alt="image" src="https://github.com/user-attachments/assets/5687ac66-4edf-4a30-ba0d-f87f2d599686" />

<img width="2850" height="1442" alt="image" src="https://github.com/user-attachments/assets/273f82f6-4329-456b-9432-4eb94e57a3dd" />

逛逛寵物區

<img width="2848" height="1438" alt="image" src="https://github.com/user-attachments/assets/f054d77c-55ef-458f-b076-4c7cdfd04cb2" />

逛逛商品區

<img width="2848" height="1440" alt="image" src="https://github.com/user-attachments/assets/aa379822-1e85-4d18-ab64-7c73a61f3865" />

店家資訊

<img width="2852" height="1438" alt="image" src="https://github.com/user-attachments/assets/ea110a3e-666a-42f9-9c9a-1c8a41c8ce83" />
