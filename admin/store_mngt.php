<?php
include 'db_connect.php';
// --- 處理讀取舊資料 (編輯模式) ---
$editData = null;
$open_val = '';  // 預設開店時間變數
$close_val = ''; // 預設打烊時間變數
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM STORE WHERE storeID = $id");
    $editData = $result->fetch_assoc();
    if (!empty($editData['worktime']) && strpos($editData['worktime'], '~') !== false) {
        $times = explode('~', $editData['worktime']);
        $open_val = $times[0] ?? ''; // 取前半段
        $close_val = $times[1] ?? ''; // 取後半段
    } else {
        $open_val = $editData['worktime'] ?? '';
    }
}

// --- 處理表單送出 (新增 或 修改) ---
if (isset($_POST['save'])) {
    $name = $_POST['storeName'];
    $addr = $_POST['address'];
    $phone = $_POST['Phone'];
    $open_time = $_POST['open_time'];
    $close_time = $_POST['close_time'];
    $worktime = $open_time . '~' . $close_time;
    $imagePath = $_POST['old_image'] ?? '';
    // --- 圖片上傳處理 ---
    if (isset($_FILES['storeImage']) && $_FILES['storeImage']['error'] === 0) {
        $uploadDir = 'uploads/';
        // 檢查資料夾是否存在
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        // 加上時間戳記防檔名重複
        $fileName = time() . '_' . basename($_FILES['storeImage']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['storeImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile; // 更新路徑
        }
    }

    // --- 判斷是 Update 還是 Insert ---
    if (!empty($_POST['storeID'])) {
        // [修改 Update]
        $id = $_POST['storeID'];
        $sql = "UPDATE STORE SET storeName='$storeName', address='$address', Phone='$Phone', worktime='$worktime', storeImage='$imagePath' WHERE storeID=$id";
        $msg = "商店資料修改成功！";
    } else {
        // [新增 Insert]
        $sql = "INSERT INTO STORE (storeName, address, Phone, worktime, storeImage) 
                VALUES ('$storeName', '$address', '$Phone', '$worktime', '$imagePath')";
        $msg = "新增商店成功！";
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
$searchKeyword = '';
$sql_query = "SELECT * FROM STORE"; // 預設查全部

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    // 搜尋店名或地址
    $sql_query = "SELECT * FROM STORE WHERE storeName LIKE '%$searchKeyword%' OR address LIKE '%$searchKeyword%'";
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>商店管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .btn-dark-custom {
            background-color: #212529;
            color: white;
            border-color: #212529;
        }
        .btn-dark-custom:hover {
            background-color: #424649;
            border-color: #373b3e;
            color: white;
        }
    </style>
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
                <button type="submit" class="btn btn-secondary">查詢</button>
                <?php if(!empty($searchKeyword)): ?>
                    <a href="store_mngt.php" class="btn btn-outline-secondary">清除</a>
                <?php endif; ?>
            </div>
        </form>
        
        <form method="post" enctype="multipart/form-data" class="row g-3 mb-4 bg-white p-3 rounded shadow-sm border border-dark border-2">
            <h5 class="text-dark mb-3">
                <?php echo $editData ? '<i class="fas fa-edit"></i> 編輯商店資料' : '<i class="fas fa-plus-circle"></i> 新增商店'; ?>
            </h5>
            
            <input type="hidden" name="storeID" value="<?php echo $editData['storeID'] ?? ''; ?>">
            <input type="hidden" name="old_image" value="<?php echo $editData['storeImage'] ?? ''; ?>">

            <div class="col-md-3">
                <label class="col-form-label fw-bold">店名</label>
                <input type="text" name="storeName" class="form-control" placeholder="店名" required
                       value="<?php echo $editData['storeName'] ?? ''; ?>">
            </div>
            <div class="col-md-3">
                <label class="col-form-label fw-bold">地址</label>
                <input type="text" name="address" class="form-control" placeholder="地址"
                       value="<?php echo $editData['address'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <label class="col-form-label fw-bold">電話</label>
                <input type="text" name="Phone" class="form-control" placeholder="電話"
                       value="<?php echo $editData['Phone'] ?? ''; ?>">
            </div>
            <div class="col-md-4">
                <label class="col-form-label fw-bold">營業時間</label>
                <div class="input-group">
                    <input type="time" name="open_time" class="form-control" required 
                           value="<?php echo $open_val; ?>">
                    <span class="input-group-text">~</span>
                    <input type="time" name="close_time" class="form-control" required 
                           value="<?php echo $close_val; ?>">
                </div>
            </div>
            <div class="col-md-12">
                <label class="col-form-label fw-bold">門市照片</label>
                <input type="file" name="storeImage" class="form-control" accept="image/*">
                <?php if ($editData && !empty($editData['storeImage'])): ?>
                    <div class="mt-2 text-muted small">
                        目前圖片：<br>
                        <img src="<?php echo $editData['storeImage']; ?>" style="height: 80px; border-radius: 5px; border: 1px solid #ddd; padding: 2px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12 mt-3">
                <button type="submit" name="save" class="btn <?php echo $editData ? 'btn-dark-custom' : 'btn-dark-custom'; ?> w-100">
                    <?php echo $editData ? '<i class="fas fa-check"></i> 確認修改' : '<i class="fas fa-plus"></i> 新增商店'; ?>
                </button>
                <?php if($editData): ?>
                    <a href="store_mngt.php" class="btn btn-secondary w-100 mt-2">取消修改</a>
                <?php endif; ?>
            </div>
        </form>

        <table class="table table-hover bg-white shadow-sm align-middle rounded overflow-hidden">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>照片</th> 
                    <th>店名</th>
                    <th>地址</th>
                    <th>電話</th>
                    <th>營業時間</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query($sql_query);
                if ($res && $res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        $imgHtml = "<span class='text-muted small'>無</span>";
                        if (!empty($row['storeImage'])) {
                            $imgHtml = "<img src='{$row['storeImage']}' style='width: 80px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;'>";
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
                                <td><span class='badge bg-info text-dark'>{$row['worktime']}</span></td>
                                <td>
                                    <a href='?edit={$row['storeID']}' class='btn btn-warning btn-sm mb-1'><i class='fas fa-edit'></i></a>
                                    <a href='?del={$row['storeID']}' class='btn btn-danger btn-sm mb-1' onclick='return confirm(\"確定刪除嗎？\")'><i class='fas fa-trash'></i></a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center text-muted p-4'>沒有找到相關資料</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>