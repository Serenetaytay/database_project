<?php
include 'db_connect.php';

// --- 新增商品邏輯 (含圖片) ---
if (isset($_POST['add'])) {
    $pName = $_POST['pName'];
    $storeID = $_POST['storeID'];
    $stock = $_POST['stock'];
    $imagePath = ''; // 預設空圖片

    // 1. 處理圖片上傳
    if (isset($_FILES['pImage']) && $_FILES['pImage']['error'] === 0) {
        $uploadDir = 'uploads/'; // 共用之前的 uploads 資料夾
        
        // 確保資料夾存在
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 檔名加上時間戳記防止重複
        $fileName = time() . '_' . basename($_FILES['pImage']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['pImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    // 2. 寫入資料庫 (加入 pImage)
    $sql = "INSERT INTO PRODUCT (pName, storeID, stock, pImage) 
            VALUES ('$pName', '$storeID', '$stock', '$imagePath')";
    
    if($conn->query($sql)){
        header("Location: product_mngt.php");
        exit(); // 記得加 exit
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- 刪除商品邏輯 ---
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM PRODUCT WHERE pID={$_GET['del']}");
    header("Location: product_mngt.php");
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
        <h3>  商品管理 (Product)</h3>
        
        <form method="post" enctype="multipart/form-data" class="row g-3 mb-4 bg-white p-3 rounded shadow-sm">
            <div class="col-md-3">
                <label class="form-label small text-muted">所屬分店</label>
                <select name="storeID" class="form-select" required>
                    <option value="">選擇分店...</option>
                    <?php
                    $res = $conn->query("SELECT * FROM STORE");
                    while($r=$res->fetch_assoc()) echo "<option value='{$r['storeID']}'>{$r['storeName']}</option>";
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">商品名稱</label>
                <input type="text" name="pName" class="form-control" placeholder="商品名稱" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">庫存數量</label>
                <input type="number" name="stock" class="form-control" placeholder="庫存" required>
            </div>
            
            <div class="col-md-4">
                <label class="form-label small text-muted">商品照片</label>
                <input type="file" name="pImage" class="form-control" accept="image/*">
            </div>

            <div class="col-12">
                <button type="submit" name="add" class="btn btn-success w-100">新增商品</button>
            </div>
        </form>

        <table class="table table-hover bg-white shadow-sm align-middle">
            <thead class="table-success">
                <tr>
                    <th>圖片</th> <th>品名</th>
                    <th>分店</th>
                    <th>庫存</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT P.*, S.storeName FROM PRODUCT P JOIN STORE S ON P.storeID = S.storeID";
                $res = $conn->query($sql);
                while ($row = $res->fetch_assoc()) {
                    // 圖片顯示邏輯
                    $imgHtml = "無圖片";
                    if (!empty($row['pImage'])) {
                        $imgHtml = "<img src='{$row['pImage']}' style='width: 60px; height: 60px; object-fit: cover; border-radius: 5px;'>";
                    }

                    echo "<tr>
                            <td>{$imgHtml}</td>
                            <td>{$row['pName']}</td>
                            <td>{$row['storeName']}</td>
                            <td>{$row['stock']}</td>
                            <td><a href='?del={$row['pID']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"確定刪除？\")'>刪除</a></td>
                          </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>