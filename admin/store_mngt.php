<?php
include 'db_connect.php';
// --- 處理讀取舊資料 (編輯模式) ---
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM STORE WHERE storeID = $id");
    $editData = $result->fetch_assoc();
}

// --- 處理表單送出 (新增 或 修改) ---
if (isset($_POST['save'])) {
    $name = $_POST['storeName'];
    $addr = $_POST['address'];
    $phone = $_POST['Phone'];
    $time = $_POST['worktime'];
    
    if (!empty($_POST['storeID'])) {
        // [修改操作] SQL Update
        $id = $_POST['storeID'];
        $sql = "UPDATE STORE SET storeName='$name', address='$addr', Phone='$phone', worktime='$time' WHERE storeID=$id";
    } else {
        // [新增操作] SQL Insert
        $sql = "INSERT INTO STORE (storeName, address, Phone, worktime) VALUES ('$name', '$addr', '$phone', '$time')";
    }

    // 執行 SQL
    if ($conn->query($sql)) {
        header("Location: store_mngt.php"); // 成功後重新導向清空表單
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- 處理資料儲存 (新增 或 修改) ---
if (isset($_POST['save'])) {
    $storeName = $_POST['storeName'];
    $address = $_POST['address'];
    $Phone = $_POST['Phone'];
    $worktime = $_POST['worktime'];
    
    // 預設使用舊圖片路徑 (如果是新增，這會是空值; 如果是修改，這會是舊路徑)
    $imagePath = $_POST['old_image'] ?? ''; 

    // --- 圖片上傳邏輯 ---
    // 檢查是否有選擇新檔案
    if (isset($_FILES['storeImage']) && $_FILES['storeImage']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        // 加上時間戳記防檔名重複
        $fileName = time() . '_' . basename($_FILES['storeImage']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['storeImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile; // ★如果有上傳成功，就覆蓋掉舊路徑
        }
    }

    // --- 判斷是新增還是修改 ---
    if (!empty($_POST['storeID'])) {
        // [修改 Update]
        $id = $_POST['storeID'];
        $sql = "UPDATE STORE SET storeName='$storeName', address='$address', Phone='$Phone', worktime='$worktime', storeImage='$imagePath' WHERE storeID=$id";
        $msg = "修改成功！";
    } else {
        // [新增 Insert]
        $sql = "INSERT INTO STORE (storeName, address, Phone, worktime, storeImage) 
                VALUES ('$storeName', '$address', '$Phone', '$worktime', '$imagePath')";
        $msg = "新增成功！";
    }

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('$msg'); window.location.href='store_mngt.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- 處理刪除 ---
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM STORE WHERE storeID={$_GET['del']}");
    header("Location: store_mngt.php");
    exit();
}

// --- 處理搜尋 ---
$searchKeyword = '';
$sql_query = "SELECT * FROM STORE"; // 預設查全部

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    // 搜尋店名或地址
    $sql_query = "SELECT * FROM STORE WHERE storeName LIKE '%$searchKeyword%' OR address LIKE '%$searchKeyword%'";
}
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container mt-4">
        <h3>商店管理</h3>

        <form method="get" class="row mb-4 align-items-center">
            <div class="col-auto">
                <label class="col-form-label fw-bold">🔍 搜尋：</label>
            </div>
            <div class="col-auto">
                <input type="text" name="search" class="form-control" placeholder="輸入店名或地址..." 
                       value="<?php echo htmlspecialchars($searchKeyword); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">查詢</button>
                <?php if(!empty($searchKeyword)): ?>
                    <a href="store_mngt.php" class="btn btn-outline-secondary">清除</a>
                <?php endif; ?>
            </div>
        </form>
        
        <form method="post" enctype="multipart/form-data" class="row g-3 mb-4 bg-white p-3 rounded shadow-sm border border-primary-subtle">
            <h5 class="text-primary mb-3"><?php echo $editData ? '✏️ 編輯商店資料' : '➕ 新增商店'; ?></h5>
            
            <input type="hidden" name="storeID" value="<?php echo $editData['storeID'] ?? ''; ?>">
            <input type="hidden" name="old_image" value="<?php echo $editData['storeImage'] ?? ''; ?>">

            <div class="col-md-3">
                <label class="form-label small text-muted">店名</label>
                <input type="text" name="storeName" class="form-control" placeholder="店名" required
                       value="<?php echo $editData['storeName'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">地址</label>
                <input type="text" name="address" class="form-control" placeholder="地址"
                       value="<?php echo $editData['address'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">電話</label>
                <input type="text" name="Phone" class="form-control" placeholder="電話"
                       value="<?php echo $editData['Phone'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">營業時間</label>
                <input type="text" name="worktime" class="form-control" placeholder="時間"
                       value="<?php echo $editData['worktime'] ?? ''; ?>">
            </div>
            
            <div class="col-md-12">
                <label class="form-label small text-muted">門市照片 (若不修改請留空)</label>
                <input type="file" name="storeImage" class="form-control" accept="image/*">
                
                <?php if ($editData && !empty($editData['storeImage'])): ?>
                    <div class="mt-2 text-muted small">
                        目前圖片：<br>
                        <img src="<?php echo $editData['storeImage']; ?>" style="height: 80px; border-radius: 5px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <button type="submit" name="save" class="btn <?php echo $editData ? 'btn-warning' : 'btn-success'; ?> w-100">
                    <?php echo $editData ? '確認修改' : '新增商店'; ?>
                </button>
                <?php if($editData): ?>
                    <a href="store_mngt.php" class="btn btn-secondary w-100 mt-2">取消修改</a>
                <?php endif; ?>
            </div>
        </form>

        <table class="table table-hover bg-white shadow-sm align-middle">
            <thead class="table-success">
                <tr>
                    <th>ID</th>
                    <th>照片</th> 
                    <th>店名</th>
                    <th>地址</th>
                    <th>電話</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 使用上方定義的 $sql_query 進行查詢
                $res = $conn->query($sql_query);
                
                if ($res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        // 處理圖片顯示
                        $imgHtml = "<span class='text-muted small'>無圖片</span>";
                        if (!empty($row['storeImage'])) {
                            $imgHtml = "<img src='{$row['storeImage']}' alt='Store' style='width: 80px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;'>";
                        }
                        $showName = $row['storeName'];
                        $showAddr = $row['address'];
                        if (!empty($searchKeyword)) {
                            $showName = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showName);
                            $showAddr = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showAddr);
                        }

                        echo "<tr>
                                <td>{$row['storeID']}</td>
                                <td>{$imgHtml}</td>
                                <td>{$showName}</td>
                                <td>{$showAddr}</td>
                                <td>{$row['Phone']}</td>
                                <td>
                                    <a href='?edit={$row['storeID']}' class='btn btn-sm btn-warning mb-1'>編輯</a>
                                    <a href='?del={$row['storeID']}' class='btn btn-sm btn-outline-danger mb-1' onclick='return confirm(\"確定刪除嗎？\")'>刪除</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center text-muted p-4'>沒有找到相關資料</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>