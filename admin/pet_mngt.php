<?php
include 'db_connect.php';

// --- 新增邏輯 (含圖片) ---
if (isset($_POST['add'])) {
    $bID = $_POST['bID'];
    $storeID = $_POST['storeID'];
    $birth = $_POST['birth'];
    $sex = $_POST['sex'];
    $personality = $_POST['personality'];
    $petprice = $_POST['petprice'];
    $imagePath = ''; // 預設空字串

    // 1. 圖片上傳處理
    if (isset($_FILES['petImage']) && $_FILES['petImage']['error'] === 0) {
        $uploadDir = 'uploads/'; // 共用 uploads 資料夾
        
        // 確保資料夾存在
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 檔名加上時間戳記
        $fileName = time() . '_' . basename($_FILES['petImage']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['petImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    // 2. 寫入資料庫 (加入 petImage)
    $sql = "INSERT INTO PET (bID, storeID, birth, sex, personality, status, petprice, petImage) 
            VALUES ('$bID', '$storeID', '$birth', '$sex', '$personality', '在店', '$petprice', '$imagePath')";
    
    if($conn->query($sql)){
        header("Location: pet_mngt.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- 刪除邏輯 ---
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM PET WHERE petID=" . $_GET['del']);
    header("Location: pet_mngt.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>寵物管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <?php include 'navbar.php';  ?>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>   寵物管理 (Pet)</h2>
        <a href="index.php" class="btn btn-secondary">回首頁</a>
    </div>

    <form method="post" enctype="multipart/form-data" class="card p-4 mb-4 bg-light shadow-sm">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">品種</label>
                <select name="bID" class="form-select" required>
                    <option value="">請選擇...</option>
                    <?php
                    $res = $conn->query("SELECT * FROM BREED");
                    while ($r = $res->fetch_assoc()) { echo "<option value='{$r['bID']}'>{$r['bName']}</option>"; }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">所在分店</label>
                <select name="storeID" class="form-select" required>
                    <option value="">請選擇...</option>
                    <?php
                    $res = $conn->query("SELECT * FROM STORE");
                    while ($r = $res->fetch_assoc()) { echo "<option value='{$r['storeID']}'>{$r['storeName']}</option>"; }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">生日</label>
                <input type="date" name="birth" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">性別</label>
                <select name="sex" class="form-select">
                    <option value="公">公</option>
                    <option value="母">母</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">個性描述</label>
                <input type="text" name="personality" class="form-control" placeholder="例如：活潑、親人">
            </div>
            <div class="col-md-3">
                <label class="form-label">價格</label>
                <input type="number" name="petprice" class="form-control" required>
            </div>
            
            <div class="col-md-4">
                <label class="form-label">寵物照片</label>
                <input type="file" name="petImage" class="form-control" accept="image/*">
            </div>

            <div class="col-12">
                <button type="submit" name="add" class="btn btn-primary w-100">新增寵物</button>
            </div>
        </div>
    </form>

    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>照片</th> <th>分店</th>
                <th>品種</th>
                <th>性別</th>
                <th>狀態</th>
                <th>價格</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 使用 JOIN 連結三個表
            $sql = "SELECT PET.*, BREED.bName, STORE.storeName 
                    FROM PET 
                    LEFT JOIN BREED ON PET.bID = BREED.bID 
                    LEFT JOIN STORE ON PET.storeID = STORE.storeID
                    ORDER BY petID DESC"; // 新增的在最上面
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                // 圖片處理邏輯
                $imgHtml = "<span class='text-muted'>無圖片</span>";
                if (!empty($row['petImage'])) {
                    $imgHtml = "<img src='{$row['petImage']}' style='width: 80px; height: 80px; object-fit: cover; border-radius: 8px;'>";
                }

                echo "<tr>
                        <td>{$row['petID']}</td>
                        <td>{$imgHtml}</td>
                        <td>{$row['storeName']}</td>
                        <td>{$row['bName']}</td>
                        <td>{$row['sex']}</td>
                        <td><span class='badge bg-info text-dark'>{$row['status']}</span></td>
                        <td>{$row['petprice']}</td>
                        <td><a href='?del={$row['petID']}' class='btn btn-danger btn-sm' onclick='return confirm(\"確認刪除？\")'>刪除</a></td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>