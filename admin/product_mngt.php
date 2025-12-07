<?php
session_start();
// 檢查是否有登入 Session，沒有就踢回 login.php
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

// --- 編輯模式：讀取舊資料 ---
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM PRODUCT WHERE pID = $id");
    $editData = $result->fetch_assoc();
}

// --- 處理資料儲存 (新增 或 修改) ---
if (isset($_POST['save'])) {
    $pName = $_POST['pName'];
    $storeID = $_POST['storeID'];
    $stock = $_POST['stock'];
    
    // 預設圖片路徑 (如果是新增=空; 如果是修改=舊路徑)
    $imagePath = $_POST['old_image'] ?? '';

    // --- 圖片上傳邏輯 ---
    if (isset($_FILES['pImage']) && $_FILES['pImage']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['pImage']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['pImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile; // 若上傳成功，更新路徑
        }
    }

    // --- 判斷是新增還是修改 ---
    if (!empty($_POST['pID'])) {
        // [修改 Update]
        $id = $_POST['pID'];
        $sql = "UPDATE PRODUCT SET pName='$pName', storeID='$storeID', stock='$stock', pImage='$imagePath' WHERE pID=$id";
        $msg = "修改成功！";
    } else {
        // [新增 Insert]
        $sql = "INSERT INTO PRODUCT (pName, storeID, stock, pImage) 
                VALUES ('$pName', '$storeID', '$stock', '$imagePath')";
        $msg = "新增成功！";
    }
    
    if ($conn->query($sql)) {
        echo "<script>alert('$msg'); window.location.href='product_mngt.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- 處理刪除 ---
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM PRODUCT WHERE pID={$_GET['del']}");
    header("Location: product_mngt.php");
    exit();
}

// --- 處理搜尋邏輯 ---
$searchKeyword = '';
// 預設 SQL：查詢所有商品並 JOIN 商店名稱
$sql_query = "SELECT P.*, S.storeName 
              FROM PRODUCT P 
              JOIN STORE S ON P.storeID = S.storeID";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    // 搜尋條件：商品名稱 或 分店名稱
    $sql_query .= " WHERE P.pName LIKE '%$searchKeyword%' OR S.storeName LIKE '%$searchKeyword%'";
}

$sql_query .= " ORDER BY P.pID ASC";
?>

<!DOCTYPE html>
<html>
<head>
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
        <h3>商品管理</h3>
        
        <form method="get" class="row mb-4 align-items-center">
            <div class="col-auto">
                <label class="col-form-label fw-bold">🔍 搜尋：</label>
            </div>
            <div class="col-auto">
                <input type="text" name="search" class="form-control" placeholder="商品名稱或分店..." 
                       value="<?php echo htmlspecialchars($searchKeyword); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">查詢</button>
                <?php if(!empty($searchKeyword)): ?>
                    <a href="product_mngt.php" class="btn btn-outline-secondary">清除</a>
                <?php endif; ?>
            </div>
        </form>

        <form method="post" enctype="multipart/form-data" class="row g-3 mb-4 bg-white p-3 rounded shadow-sm border border-secondary-subtle">
            <h5 class="text-secondary mb-3"><?php echo $editData ? '編輯商品資料' : '新增商品'; ?></h5>
            
            <input type="hidden" name="pID" value="<?php echo $editData['pID'] ?? ''; ?>">
            <input type="hidden" name="old_image" value="<?php echo $editData['pImage'] ?? ''; ?>">

            <div class="col-md-3">
                <label class="col-form-label fw-bold">所屬分店</label>
                <select name="storeID" class="form-select" required>
                    <option value="">選擇分店...</option>
                    <?php
                    $res = $conn->query("SELECT * FROM STORE");
                    while ($r = $res->fetch_assoc()) {
                        $selected = ($editData && $r['storeID'] == $editData['storeID']) ? 'selected' : '';
                        echo "<option value='{$r['storeID']}' $selected>{$r['storeName']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="col-form-label fw-bold">商品名稱</label>
                <input type="text" name="pName" class="form-control" placeholder="商品名稱" required
                       value="<?php echo $editData['pName'] ?? ''; ?>">
            </div>
            <div class="col-md-2">
                <label class="col-form-label fw-bold">庫存數量</label>
                <input type="number" name="stock" class="form-control" placeholder="庫存" required
                       value="<?php echo $editData['stock'] ?? ''; ?>">
            </div>
            
            <div class="col-md-4">
                <label class="col-form-label fw-bold">商品照片</label>
                <input type="file" name="pImage" class="form-control" accept="image/*">
                <?php if ($editData && !empty($editData['pImage'])): ?>
                    <div class="mt-2 text-muted small">
                        目前圖片：<br>
                        <img src="<?php echo $editData['pImage']; ?>" style="height: 60px; border-radius: 5px; border: 1px solid #ddd;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <button type="submit" name="save" class="btn <?php echo $editData ? 'btn-dark-custom' : 'btn-dark-custom'; ?> w-100">
                    <?php echo $editData ? '確認修改' : '新增商品'; ?>
                </button>
                <?php if($editData): ?>
                    <a href="product_mngt.php" class="btn btn-secondary w-100 mt-2">取消修改</a>
                <?php endif; ?>
            </div>
        </form>

        <table class="table table-hover bg-white shadow-sm align-middle">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>圖片</th> 
                    <th>品名</th>
                    <th>分店</th>
                    <th>庫存</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 執行搜尋 SQL
                $res = $conn->query($sql_query);
                
                if ($res->num_rows > 0) {
                    while ($row = $res->fetch_assoc()) {
                        // 圖片顯示邏輯
                        $imgHtml = "<span class='text-muted small'>無圖片</span>";
                        if (!empty($row['pImage'])) {
                            $imgHtml = "<img src='{$row['pImage']}' style='width: 60px; height: 60px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;'>";
                        }

                        // 搜尋關鍵字高亮 (UX 優化)
                        $showPName = $row['pName'];
                        $showStore = $row['storeName'];
                        if (!empty($searchKeyword)) {
                            $showPName = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showPName);
                            $showStore = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showStore);
                        }

                        echo "<tr>
                                <td>{$row['pID']}</td>
                                <td>{$imgHtml}</td>
                                <td>{$showPName}</td>
                                <td>{$showStore}</td>
                                <td>{$row['stock']}</td>
                                <td>
                                    <a href='?edit={$row['pID']}' class='btn btn-warning btn-sm mb-1'><i class='fas fa-edit'></i></a>
                                    <a href='?del={$row['pID']}' class='btn btn-danger btn-sm mb-1' onclick='return confirm(\"確認刪除？\")'><i class='fas fa-trash'></i></a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center p-4 text-muted'>查無資料</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>