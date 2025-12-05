<?php
include 'db_connect.php';

// --- A. 處理新增物種 (Specie) ---
if (isset($_POST['add_specie'])) {
    $sName = $_POST['sName'];
    $conn->query("INSERT INTO SPECIE (sName) VALUES ('$sName')");
    echo "<script>alert('物種新增成功！'); window.location.href='pet_mngt.php';</script>";
}

// --- B. 處理新增品種 (Breed) ---
if (isset($_POST['add_breed'])) {
    $sID = $_POST['sID'];
    $bName = $_POST['bName'];
    $conn->query("INSERT INTO BREED (sID, bName) VALUES ('$sID', '$bName')");
    echo "<script>alert('品種新增成功！'); window.location.href='pet_mngt.php';</script>";
}

// --- C. 編輯模式：讀取舊資料 ---
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM PET WHERE petID = $id");
    $editData = $result->fetch_assoc();
}

// --- D. 處理寵物資料儲存 (新增 或 修改) ---
if (isset($_POST['save_pet'])) {
    $bID = $_POST['bID'];
    $storeID = $_POST['storeID'];
    $birth = $_POST['birth'];
    $sex = $_POST['sex'];
    $personality = $_POST['personality'];
    $petprice = $_POST['petprice'];
    $status = $_POST['status'] ?? '在店'; // 編輯模式才有 status 欄位
    
    // 預設圖片路徑 (新增=空; 修改=舊圖)
    $imagePath = $_POST['old_image'] ?? '';

    // --- 圖片上傳邏輯 ---
    if (isset($_FILES['petImage']) && $_FILES['petImage']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['petImage']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['petImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    // --- 判斷新增或修改 ---
    if (!empty($_POST['petID'])) {
        // [修改 Update]
        $id = $_POST['petID'];
        $sql = "UPDATE PET SET bID='$bID', storeID='$storeID', birth='$birth', sex='$sex', 
                personality='$personality', status='$status', petprice='$petprice', petImage='$imagePath' 
                WHERE petID=$id";
        $msg = "寵物資料修改成功！";
    } else {
        // [新增 Insert]
        $sql = "INSERT INTO PET (bID, storeID, birth, sex, personality, status, petprice, petImage) 
                VALUES ('$bID', '$storeID', '$birth', '$sex', '$personality', '在店', '$petprice', '$imagePath')";
        $msg = "寵物新增成功！";
    }
    
    if ($conn->query($sql)) {
        echo "<script>alert('$msg'); window.location.href='pet_mngt.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- E. 處理刪除 ---
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM PET WHERE petID=" . $_GET['del']);
    header("Location: pet_mngt.php");
    exit();
}

// --- F. 處理搜尋邏輯 ---
$searchKeyword = '';
$sql_query = "SELECT PET.*, BREED.bName, STORE.storeName 
              FROM PET 
              LEFT JOIN BREED ON PET.bID = BREED.bID 
              LEFT JOIN STORE ON PET.storeID = STORE.storeID";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    // 搜尋：品種名稱、分店名稱 或 個性描述
    $sql_query .= " WHERE BREED.bName LIKE '%$searchKeyword%' 
                    OR STORE.storeName LIKE '%$searchKeyword%' 
                    OR PET.personality LIKE '%$searchKeyword%'";
}

$sql_query .= " ORDER BY PET.petID DESC";
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>寵物管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>🐶 寵物管理 (Pet)</h3>
            
            <div>
                <button class="btn btn-outline-info btn-sm me-2" type="button" data-bs-toggle="collapse" data-bs-target="#addSpecieBox">
                    <i class="fas fa-plus"></i> 新增物種
                </button>
                <button class="btn btn-outline-warning btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#addBreedBox">
                    <i class="fas fa-plus"></i> 新增品種
                </button>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 collapse" id="addSpecieBox">
                <div class="card card-body bg-info bg-opacity-10 border-info">
                    <form method="post" class="row g-2 align-items-center">
                        <div class="col-auto"><label>新物種名稱：</label></div>
                        <div class="col-auto"><input type="text" name="sName" class="form-control form-control-sm" placeholder="如：鳥、魚" required></div>
                        <div class="col-auto"><button type="submit" name="add_specie" class="btn btn-sm btn-info">新增</button></div>
                    </form>
                </div>
            </div>
            <div class="col-md-6 collapse" id="addBreedBox">
                <div class="card card-body bg-warning bg-opacity-10 border-warning">
                    <form method="post" class="row g-2 align-items-center">
                        <div class="col-auto"><label>所屬物種：</label></div>
                        <div class="col-auto">
                            <select name="sID" class="form-select form-select-sm" required>
                                <?php
                                $s_res = $conn->query("SELECT * FROM SPECIE");
                                while($s = $s_res->fetch_assoc()) echo "<option value='{$s['sID']}'>{$s['sName']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="col-auto"><input type="text" name="bName" class="form-control form-control-sm" placeholder="如：鸚鵡" required></div>
                        <div class="col-auto"><button type="submit" name="add_breed" class="btn btn-sm btn-warning">新增</button></div>
                    </form>
                </div>
            </div>
        </div>

        <form method="get" class="row mb-4 align-items-center">
            <div class="col-auto"><label class="col-form-label fw-bold">🔍 搜尋：</label></div>
            <div class="col-auto">
                <input type="text" name="search" class="form-control" placeholder="品種、分店或特徵..." value="<?php echo htmlspecialchars($searchKeyword); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">查詢</button>
                <?php if(!empty($searchKeyword)): ?>
                    <a href="pet_mngt.php" class="btn btn-outline-secondary">清除</a>
                <?php endif; ?>
            </div>
        </form>

        <form method="post" enctype="multipart/form-data" class="card p-4 mb-4 bg-white shadow-sm border border-primary-subtle">
            <h5 class="text-primary mb-3"><?php echo $editData ? '✏️ 編輯寵物資料' : '➕ 新增寵物'; ?></h5>
            
            <input type="hidden" name="petID" value="<?php echo $editData['petID'] ?? ''; ?>">
            <input type="hidden" name="old_image" value="<?php echo $editData['petImage'] ?? ''; ?>">

            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted">品種</label>
                    <select name="bID" class="form-select" required>
                        <option value="">請選擇...</option>
                        <?php
                        $res = $conn->query("SELECT * FROM BREED");
                        while ($r = $res->fetch_assoc()) { 
                            $selected = ($editData && $r['bID'] == $editData['bID']) ? 'selected' : '';
                            echo "<option value='{$r['bID']}' $selected>{$r['bName']}</option>"; 
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">所在分店</label>
                    <select name="storeID" class="form-select" required>
                        <option value="">請選擇...</option>
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
                    <label class="form-label small text-muted">生日</label>
                    <input type="date" name="birth" class="form-control" required value="<?php echo $editData['birth'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">性別</label>
                    <select name="sex" class="form-select">
                        <option value="公" <?php echo ($editData && $editData['sex']=='公')?'selected':''; ?>>公</option>
                        <option value="母" <?php echo ($editData && $editData['sex']=='母')?'selected':''; ?>>母</option>
                    </select>
                </div>
                
                <div class="col-md-5">
                    <label class="form-label small text-muted">個性描述</label>
                    <input type="text" name="personality" class="form-control" placeholder="例如：活潑、親人" value="<?php echo $editData['personality'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">價格</label>
                    <input type="number" name="petprice" class="form-control" required value="<?php echo $editData['petprice'] ?? ''; ?>">
                </div>

                <?php if($editData): ?>
                <div class="col-md-4">
                    <label class="form-label small text-danger">狀態 (修改)</label>
                    <select name="status" class="form-select">
                        <option value="在店" <?php echo ($editData['status']=='在店')?'selected':''; ?>>在店</option>
                        <option value="已預約" <?php echo ($editData['status']=='已預約')?'selected':''; ?>>已預約</option>
                        <option value="已售出" <?php echo ($editData['status']=='已售出')?'selected':''; ?>>已售出</option>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="col-md-12">
                    <label class="form-label small text-muted">寵物照片</label>
                    <input type="file" name="petImage" class="form-control" accept="image/*">
                    <?php if ($editData && !empty($editData['petImage'])): ?>
                        <div class="mt-2 text-muted small">
                            目前圖片：<br>
                            <img src="<?php echo $editData['petImage']; ?>" style="height: 80px; border-radius: 5px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12">
                    <button type="submit" name="save_pet" class="btn <?php echo $editData ? 'btn-warning' : 'btn-primary'; ?> w-100">
                        <?php echo $editData ? '確認修改' : '新增寵物'; ?>
                    </button>
                    <?php if($editData): ?>
                        <a href="pet_mngt.php" class="btn btn-secondary w-100 mt-2">取消修改</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <table class="table table-hover align-middle bg-white shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>照片</th> 
                    <th>分店</th>
                    <th>品種</th>
                    <th>性別</th>
                    <th>個性</th>
                    <th>狀態</th>
                    <th>價格</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query($sql_query);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // 圖片顯示
                        $imgHtml = "<span class='text-muted small'>無圖片</span>";
                        if (!empty($row['petImage'])) {
                            $imgHtml = "<img src='{$row['petImage']}' style='width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;'>";
                        }

                        // 搜尋高亮
                        $showBreed = $row['bName'];
                        $showStore = $row['storeName'];
                        $showPers = $row['personality'];
                        if (!empty($searchKeyword)) {
                            $showBreed = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showBreed);
                            $showStore = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showStore);
                            $showPers = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showPers);
                        }

                        echo "<tr>
                                <td>{$row['petID']}</td>
                                <td>{$imgHtml}</td>
                                <td>{$showStore}</td>
                                <td>{$showBreed}</td>
                                <td>{$row['sex']}</td>
                                <td>{$showPers}</td>
                                <td><span class='badge bg-info text-dark'>{$row['status']}</span></td>
                                <td>{$row['petprice']}</td>
                                <td>
                                    <a href='?edit={$row['petID']}' class='btn btn-warning btn-sm mb-1'><i class='fas fa-edit'></i></a>
                                    <a href='?del={$row['petID']}' class='btn btn-danger btn-sm mb-1' onclick='return confirm(\"確認刪除？\")'><i class='fas fa-trash'></i></a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='text-center p-4 text-muted'>查無資料</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>