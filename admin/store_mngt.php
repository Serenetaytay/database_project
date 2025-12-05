<?php
include 'db_connect.php';

// 處理新增邏輯
if (isset($_POST['add'])) {
    $storeName = $_POST['storeName'];
    $address = $_POST['address'];
    $Phone = $_POST['Phone'];
    $worktime = $_POST['worktime'];
    $imagePath = ''; // 預設空字串

    // --- 圖片上傳邏輯開始 ---
    // 檢查是否有選擇檔案，且沒有錯誤
    if (isset($_FILES['storeImage']) && $_FILES['storeImage']['error'] === 0) {
        $uploadDir = 'uploads/'; // 設定存檔資料夾
        
        // 如果資料夾不存在，自動建立
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 為了避免檔名重複，加上時間戳記
        $fileName = time() . '_' . basename($_FILES['storeImage']['name']);
        $targetFile = $uploadDir . $fileName;

        // 將檔案從暫存區搬移到 upload 資料夾
        if (move_uploaded_file($_FILES['storeImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile; // 成功的話，將路徑存入變數
        }
    }
    // --- 圖片上傳邏輯結束 ---

    // 將資料寫入資料庫 (包含圖片路徑)
    $sql = "INSERT INTO STORE (storeName, address, Phone, worktime, storeImage) 
            VALUES ('$storeName', '$address', '$Phone', '$worktime', '$imagePath')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: store_mngt.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// 處理刪除邏輯
if (isset($_GET['del'])) {
    // (進階) 刪除資料前，可以順便把舊照片刪掉，這裡先做基本刪除資料庫
    $conn->query("DELETE FROM STORE WHERE storeID={$_GET['del']}");
    header("Location: store_mngt.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h3>  商店管理 (Store)</h3>
        
        <form method="post" enctype="multipart/form-data" class="row g-3 mb-4 bg-white p-3 rounded shadow-sm">
            <div class="col-md-3">
                <label class="form-label small text-muted">店名</label>
                <input type="text" name="storeName" class="form-control" placeholder="店名" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">地址</label>
                <input type="text" name="address" class="form-control" placeholder="地址">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">電話</label>
                <input type="text" name="Phone" class="form-control" placeholder="電話">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">營業時間</label>
                <input type="text" name="worktime" class="form-control" placeholder="時間">
            </div>
            
            <div class="col-md-12">
                <label class="form-label small text-muted">門市照片</label>
                <input type="file" name="storeImage" class="form-control" accept="image/*">
            </div>

            <div class="col-12">
                <button type="submit" name="add" class="btn btn-success w-100">新增商店</button>
            </div>
        </form>

        <table class="table table-hover bg-white shadow-sm align-middle">
            <thead class="table-success">
                <tr>
                    <th>ID</th>
                    <th>照片</th> <th>店名</th>
                    <th>地址</th>
                    <th>電話</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM STORE");
                while ($row = $res->fetch_assoc()) {
                    // 判斷是否有圖片，若無則顯示預設文字
                    $imgHtml = "無圖片";
                    if (!empty($row['storeImage'])) {
                        $imgHtml = "<img src='{$row['storeImage']}' alt='Store Img' style='width: 80px; height: 60px; object-fit: cover; border-radius: 5px;'>";
                    }

                    echo "<tr>
                            <td>{$row['storeID']}</td>
                            <td>{$imgHtml}</td>
                            <td>{$row['storeName']}</td>
                            <td>{$row['address']}</td>
                            <td>{$row['Phone']}</td>
                            <td><a href='?del={$row['storeID']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"確定刪除嗎？\")'>刪除</a></td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>