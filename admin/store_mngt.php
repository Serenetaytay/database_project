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
        // ★ SweetAlert2 成功提示與跳轉
        echo "<!DOCTYPE html>
        <html lang='zh-TW'>
        <head>
            <meta charset='UTF-8'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: '成功',
                    text: '$msg',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href='store_mngt.php';
                });
            </script>
        </body>
        </html>";
        exit();
    } else {
        echo "SQL Error: " . $conn->error;
    }
}

// --- C. 刪除邏輯 ---
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    try {
        if ($conn->query("DELETE FROM STORE WHERE storeID=$id")) {
            // ★ 修改處：刪除成功後直接重新導向，不顯示動畫 (與商品/預約頁面一致)
            header("Location: store_mngt.php");
            exit;
        } else {
            throw new Exception($conn->error);
        }
    } catch (Exception $e) {
        // 錯誤狀況保留彈窗提示 (例如因為有關聯資料無法刪除)
        $errorMsg = "無法刪除！可能原因：該分店底下還有關聯資料。";
        echo "<!DOCTYPE html>
        <html lang='zh-TW'>
        <head>
            <meta charset='UTF-8'>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        </head>
        <body>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: '刪除失敗',
                    text: '$errorMsg',
                    confirmButtonText: '確定'
                }).then(() => {
                    window.location.href='store_mngt.php';
                });
            </script>
        </body>
        </html>";
        exit;
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // ★ SweetAlert2 刪除確認函式
    function confirmDelete(url) {
        event.preventDefault();
        Swal.fire({
            title: '確定要刪除這間分店嗎？',
            text: "刪除後無法復原！",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '刪除',
            cancelButtonText: '取消'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        })
    }
    </script>
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
        <h3>商店資訊管理</h3>
        <form method="post" enctype="multipart/form-data" class="card p-4 mb-4 bg-white shadow-sm border-secondary-subtle">
            <h5 class="text-secondary mb-3">
                <?php echo $editData ? '編輯分店資料' : '新增分店'; ?>
            </h5>
            
            <input type="hidden" name="storeID" value="<?php echo $editData['storeID'] ?? ''; ?>">
            <input type="hidden" name="old_image" value="<?php echo $editData['storeImage'] ?? ''; ?>">
            
            <div class="row g-3">
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
                            目前圖片：<br>
                            <img src="<?php echo $editData['storeImage']; ?>" style="height: 60px; border-radius: 5px; border: 1px solid #ddd;">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="col-12">
                    <button type="submit" name="save" class="btn <?php echo $editData ? 'btn-dark-custom' : 'btn-dark-custom'; ?> w-100">
                        <?php echo $editData ? '確認修改' : '新增分店'; ?>
                    </button>
                    <?php if($editData): ?>
                        <a href="store_mngt.php" class="btn btn-secondary w-100 mt-2">取消編輯</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

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
                                        <a href='?edit={$row['storeID']}' class='btn btn-sm btn-warning me-1'><i class='fas fa-edit'></i></a>
                                        <a href='?del={$row['storeID']}' class='btn btn-danger btn-sm' onclick='confirmDelete(this.href)'><i class='fas fa-trash'></i></a>
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