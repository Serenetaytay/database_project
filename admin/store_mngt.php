<?php
session_start();
// 1. 檢查管理員權限
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

// --- 變數初始化 (防止編輯時報錯) ---
$editData = null;
$open_default = '';  
$close_default = '';

// --- A. 編輯模式：讀取舊資料 ---
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']); // 轉成數字防呆
    $result = $conn->query("SELECT * FROM STORE WHERE storeID = $id");
    
    if ($result->num_rows > 0) {
        $editData = $result->fetch_assoc();

        
        if (!empty($editData['worktime'])) {
            // 使用 explode 拆分字串
            $times = explode(' - ', $editData['worktime']);
            
            // 確保拆出來有兩個時間才填入
            if (count($times) >= 2) {
                $open_default = $times[0];  // 09:00
                $close_default = $times[1]; // 18:00
            } else {
                // 如果舊格式不對，至少把前面的填進去，避免全空
                $open_default = $editData['worktime'];
            }
        }
    }
}

// --- B. 資料儲存 (新增 或 修改) ---
if (isset($_POST['save'])) {
    // 1. 接收表單資料 (加上 real_escape_string 防止資料庫錯誤)
    $name = $conn->real_escape_string($_POST['storeName']);
    $addr = $conn->real_escape_string($_POST['address']);
    $tel  = $conn->real_escape_string($_POST['Phone']);
    
    // 2. 處理時間：把兩個時間欄位接起來
    // 結果會變成 "09:00 - 18:00"
    $open = $_POST['open_time'];
    $close = $_POST['close_time'];
    $time = $conn->real_escape_string($open . ' - ' . $close);

    // 3. 處理圖片上傳
    $imagePath = $_POST['old_image'] ?? ''; // 預設用舊圖
    
    if (isset($_FILES['storeImage']) && $_FILES['storeImage']['error'] === 0) {
        $uploadDir = '../uploads/'; 
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = time() . '_s_' . basename($_FILES['storeImage']['name']);
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['storeImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    // 4. 判斷是 Update 還是 Insert
    if (!empty($_POST['storeID'])) {
        // [修改]
        $id = intval($_POST['storeID']);
        $sql = "UPDATE STORE SET 
                storeName='$name', 
                address='$addr', 
                Phone='$tel', 
                worktime='$time', 
                storeImage='$imagePath' 
                WHERE storeID=$id";
        $msg = "修改成功！";
    } else {
        // [新增]
        $sql = "INSERT INTO STORE (storeName, address, Phone, worktime, storeImage) 
                VALUES ('$name', '$addr', '$tel', '$time', '$imagePath')";
        $msg = "新增成功！";
    }

    if ($conn->query($sql)) {
        echo "<script>alert('$msg'); location.href='store_mngt.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- C. 刪除邏輯 ---
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    // 使用 try-catch 避免刪除失敗時報錯
    try {
        if ($conn->query("DELETE FROM STORE WHERE storeID=$id")) {
            echo "<script>alert('刪除成功！'); location.href='store_mngt.php';</script>";
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        echo "<script>
                alert('無法刪除！\\n可能原因：該分店底下還有商品或寵物資料。\\n請先清空該店關聯資料。'); 
                location.href='store_mngt.php';
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>商店管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <h3 class="fw-bold mb-4">  商店資訊管理</h3>
        
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary">
                    <i class="fas fa-edit me-2"></i><?php echo $editData ? '編輯分店' : '新增分店'; ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" class="row g-3">
                    <input type="hidden" name="storeID" value="<?php echo $editData['storeID'] ?? ''; ?>">
                    <input type="hidden" name="old_image" value="<?php echo $editData['storeImage'] ?? ''; ?>">
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">分店名稱</label>
                        <input type="text" name="storeName" class="form-control" placeholder="例：台北信義店" required 
                               value="<?php echo $editData['storeName'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">電話</label>
                        <input type="text" name="Phone" class="form-control" required 
                               value="<?php echo $editData['Phone'] ?? ''; ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">營業時間</label>
                        <div class="input-group">
                            <span class="input-group-text">從</span>
                            <input type="time" name="open_time" class="form-control" required 
                                   value="<?php echo $open_default; ?>">
                            <span class="input-group-text">到</span>
                            <input type="time" name="close_time" class="form-control" required 
                                   value="<?php echo $close_default; ?>">
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-bold">地址</label>
                        <input type="text" name="address" class="form-control" required 
                               value="<?php echo $editData['address'] ?? ''; ?>">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">門市照片</label>
                        <input type="file" name="storeImage" class="form-control">
                        <?php if(!empty($editData['storeImage'])): ?>
                            <div class="mt-2 text-muted small">
                                目前圖片：<a href="<?php echo $editData['storeImage']; ?>" target="_blank">查看</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-12 text-end">
                        <?php if($editData): ?>
                            <a href="store_mngt.php" class="btn btn-secondary me-2">取消編輯</a>
                        <?php endif; ?>
                        <button type="submit" name="save" class="btn btn-primary px-4">儲存設定</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white py-3">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>分店列表</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-secondary">
                        <tr>
                            <th>圖片</th>
                            <th>店名</th>
                            <th>電話 / 地址</th>
                            <th>營業時間</th>
                            <th class="text-end">管理操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $res = $conn->query("SELECT * FROM STORE");
                        if($res->num_rows > 0){
                            while($row = $res->fetch_assoc()){
                                $img = !empty($row['storeImage']) ? $row['storeImage'] : "https://via.placeholder.com/100?text=No+Img";
                                echo "<tr>
                                    <td width='100'>
                                        <img src='$img' class='rounded border' style='width: 80px; height: 60px; object-fit: cover;'>
                                    </td>
                                    <td class='fw-bold'>{$row['storeName']}</td>
                                    <td>
                                        <div class='small text-muted'>{$row['Phone']}</div>
                                        <div>{$row['address']}</div>
                                    </td>
                                    <td><span class='badge bg-info text-dark'>{$row['worktime']}</span></td>
                                    <td class='text-end'>
                                        <a href='?edit={$row['storeID']}' class='btn btn-sm btn-warning me-1'><i class='fas fa-edit'></i> 編輯</a>
                                        <a href='?del={$row['storeID']}' class='btn btn-sm btn-danger' onclick='return confirm(\"確定要刪除嗎？\");'><i class='fas fa-trash-alt'></i> 刪除</a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-4 text-muted'>目前沒有分店資料</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div style="height: 50px;"></div>
</body>
</html>