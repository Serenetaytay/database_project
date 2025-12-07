<?php
include 'db_connect.php';
// 新增物種
if (isset($_POST['add_specie'])) {
    $sName = $_POST['sName'];
    if (!empty($sName)) {
        $stmt = $conn->prepare("INSERT INTO SPECIE (sName) VALUES (?)");
        $stmt->bind_param("s", $sName);
        if ($stmt->execute()) {
            echo "<script>alert('物種新增成功！'); window.location.href='pet_mngt.php';</script>";
        } else {
            echo "<script>alert('新增失敗: " . $conn->error . "');</script>";
        }
    }
}

// 新增品種
if (isset($_POST['add_breed'])) {
    $sID = $_POST['sID'];
    $bName = $_POST['bName'];
    if (!empty($sID) && !empty($bName)) {
        $stmt = $conn->prepare("INSERT INTO BREED (sID, bName) VALUES (?, ?)");
        $stmt->bind_param("is", $sID, $bName);
        if ($stmt->execute()) {
            echo "<script>alert('品種新增成功！'); window.location.href='pet_mngt.php';</script>";
        }
    }
}

// 編輯模式：讀取舊資料
$editData = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM PET WHERE petID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
}

// 儲存寵物資料 (新增 或 修改)
if (isset($_POST['save_pet'])) {
    $bID = $_POST['bID'];
    $storeID = $_POST['storeID'];
    $birth = $_POST['birth'];
    $sex = $_POST['sex'];
    $personality = $_POST['personality'];
    $petprice = $_POST['petprice'];
    $status = $_POST['status'] ?? '在店'; // 預設狀態
    
    // 圖片路徑處理：預設為舊路徑 (修改時) 或 空字串 (新增時)
    $imagePath = $_POST['old_image'] ?? '';

    // --- 圖片上傳邏輯 ---
    if (isset($_FILES['petImage']) && $_FILES['petImage']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // 檔名加上時間戳記，防止檔名重複覆蓋
        $ext = pathinfo($_FILES['petImage']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['petImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile; // 更新為新圖片路徑
        }
    }

    // --- 判斷是 Update 還是 Insert ---
    if (!empty($_POST['petID'])) {
        // [修改 Update]
        $id = $_POST['petID'];
        $sql = "UPDATE PET SET bID=?, storeID=?, birth=?, sex=?, personality=?, status=?, petprice=?, petImage=? WHERE petID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissssssi", $bID, $storeID, $birth, $sex, $personality, $status, $petprice, $imagePath, $id);
        $msg = "寵物資料修改成功！";
    } else {
        // [新增 Insert]
        $sql = "INSERT INTO PET (bID, storeID, birth, sex, personality, status, petprice, petImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissssss", $bID, $storeID, $birth, $sex, $personality, $status, $petprice, $imagePath);
        $msg = "寵物新增成功！";
    }
    
    if ($stmt->execute()) {
        echo "<script>alert('$msg'); window.location.href='pet_mngt.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

// 3. 刪除寵物
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $conn->query("DELETE FROM PET WHERE petID=$id");
    header("Location: pet_mngt.php");
    exit();
}

$sql_query = "SELECT PET.*, BREED.bName, STORE.storeName, SPECIE.sName 
              FROM PET 
              LEFT JOIN BREED ON PET.bID = BREED.bID 
              LEFT JOIN SPECIE ON BREED.sID = SPECIE.sID 
              LEFT JOIN STORE ON PET.storeID = STORE.storeID 
              WHERE 1=1";

// 接收參數
$filter_sID = $_GET['filter_sID'] ?? '';
$filter_bID = $_GET['filter_bID'] ?? '';
$filter_min = $_GET['filter_min'] ?? '';
$filter_max = $_GET['filter_max'] ?? '';
$searchKeyword = $_GET['search'] ?? '';

// 動態加入條件
if (!empty($filter_sID)) {
    $sql_query .= " AND SPECIE.sID = '$filter_sID'";
}
if (!empty($filter_bID)) {
    $sql_query .= " AND BREED.bID = '$filter_bID'";
}
if (!empty($filter_min)) {
    $sql_query .= " AND PET.petprice >= $filter_min";
}
if (!empty($filter_max)) {
    $sql_query .= " AND PET.petprice <= $filter_max";
}
if (!empty($searchKeyword)) {
    $sql_query .= " AND (BREED.bName LIKE '%$searchKeyword%' 
                     OR STORE.storeName LIKE '%$searchKeyword%' 
                     OR PET.personality LIKE '%$searchKeyword%')";
}

$sql_query .= " ORDER BY PET.petID DESC";
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>寵物管理系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>🐶 寵物管理 (Pet Management)</h3>
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
                        <div class="col-auto"><label class="fw-bold">新物種名稱：</label></div>
                        <div class="col-auto">
                            <input type="text" name="sName" class="form-control form-control-sm" placeholder="如：鳥、魚" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_specie" class="btn btn-sm btn-info text-white">新增</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-6 collapse" id="addBreedBox">
                <div class="card card-body bg-warning bg-opacity-10 border-warning">
                    <form method="post" class="row g-2 align-items-center">
                        <div class="col-auto"><label class="fw-bold">所屬物種：</label></div>
                        <div class="col-auto">
                            <select name="sID" class="form-select form-select-sm" required>
                                <option value="">選物種...</option>
                                <?php
                                $s_res = $conn->query("SELECT * FROM SPECIE");
                                while($s = $s_res->fetch_assoc()) echo "<option value='{$s['sID']}'>{$s['sName']}</option>";
                                ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <input type="text" name="bName" class="form-control form-control-sm" placeholder="新品種名" required>
                        </div>
                        <div class="col-auto">
                            <button type="submit" name="add_breed" class="btn btn-sm btn-warning text-dark">新增</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <form method="get" class="card p-3 mb-4 bg-white shadow-sm border-secondary">
            <h6 class="text-muted mb-2"><i class="fas fa-filter"></i> 條件篩選與搜尋</h6>
            <div class="row g-2 align-items-center">
                
                <div class="col-md-2">
                    <label class="small text-muted">物種 (Specie)</label>
                    <select name="filter_sID" id="search_sID" class="form-select form-select-sm">
                        <option value="">-- 全部 --</option>
                        <?php
                        $s_res = $conn->query("SELECT * FROM SPECIE");
                        while ($s = $s_res->fetch_assoc()) {
                            $sel = ($filter_sID == $s['sID']) ? 'selected' : '';
                            echo "<option value='{$s['sID']}' $sel>{$s['sName']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="small text-muted">品種 (Breed)</label>
                    <select name="filter_bID" id="search_bID" class="form-select form-select-sm">
                        <option value="">-- 全部 --</option>
                        <?php
                        $b_res = $conn->query("SELECT * FROM BREED");
                        while ($b = $b_res->fetch_assoc()) {
                            $sel = ($filter_bID == $b['bID']) ? 'selected' : '';
                            echo "<option value='{$b['bID']}' data-sid='{$b['sID']}' $sel>{$b['bName']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="small text-muted">價格範圍 (Price)</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="filter_min" class="form-control" placeholder="最低" value="<?php echo $filter_min; ?>">
                        <span class="input-group-text">~</span>
                        <input type="number" name="filter_max" class="form-control" placeholder="最高" value="<?php echo $filter_max; ?>">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="small text-muted">關鍵字</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="輸入特徵、品種或店名..." value="<?php echo htmlspecialchars($searchKeyword); ?>">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search"></i> 查詢
                    </button>
                </div>
            </div>
            <?php if(!empty($searchKeyword) || !empty($filter_sID) || !empty($filter_bID) || !empty($filter_min)): ?>
            <div class="mt-2 text-end">
                <a href="pet_mngt.php" class="text-secondary small text-decoration-none"><i class="fas fa-times"></i> 清除所有搜尋條件</a>
            </div>
            <?php endif; ?>
        </form>

        <form method="post" enctype="multipart/form-data" class="card p-4 mb-4 bg-white shadow-sm border border-primary-subtle">
            <h5 class="text-primary mb-3">
                <?php echo $editData ? '<i class="fas fa-edit"></i> 編輯寵物資料 (ID: '.$editData['petID'].')' : '<i class="fas fa-plus-circle"></i> 新增寵物'; ?>
            </h5>
            
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
                    <input type="text" name="personality" class="form-control" placeholder="" value="<?php echo $editData['personality'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">價格</label>
                    <input type="number" name="petprice" class="form-control" required value="<?php echo $editData['petprice'] ?? ''; ?>">
                </div>

                <?php if($editData): ?>
                <div class="col-md-4">
                    <label class="form-label small text-danger fw-bold">狀態 (Status)</label>
                    <select name="status" class="form-select border-danger">
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
                            <img src="<?php echo $editData['petImage']; ?>" style="height: 100px; border-radius: 5px; border: 1px solid #ddd; padding: 2px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" name="save_pet" class="btn <?php echo $editData ? 'btn-warning' : 'btn-primary'; ?> w-100">
                        <?php echo $editData ? '<i class="fas fa-check"></i> 確認修改' : '<i class="fas fa-plus"></i> 新增寵物'; ?>
                    </button>
                    <?php if($editData): ?>
                        <a href="pet_mngt.php" class="btn btn-outline-secondary w-100 mt-2">取消修改</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <table class="table table-hover align-middle bg-white shadow-sm rounded overflow-hidden">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>照片</th> 
                    <th>物種</th>
                    <th>品種</th>
                    <th>分店</th>
                    <th>狀態</th>
                    <th>價格</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query($sql_query);
                
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // 圖片顯示
                        $imgHtml = "<span class='text-muted small'>無</span>";
                        if (!empty($row['petImage'])) {
                            $imgHtml = "<img src='{$row['petImage']}' style='width: 60px; height: 60px; object-fit: cover; border-radius: 5px;'>";
                        }

                        // 搜尋關鍵字高亮 (UX)
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
                                <td><span class='badge bg-secondary'>{$row['sName']}</span></td>
                                <td>{$showBreed}</td>
                                <td>{$showStore}</td>
                                <td><span class='badge bg-info text-dark'>{$row['status']}</span></td>
                                <td class='text-success fw-bold'>$ {$row['petprice']}</td>
                                <td>
                                    <a href='?edit={$row['petID']}' class='btn btn-warning btn-sm mb-1' title='編輯'><i class='fas fa-edit'></i></a>
                                    <a href='?del={$row['petID']}' class='btn btn-danger btn-sm mb-1' onclick='return confirm(\"確認刪除此寵物資料？\")' title='刪除'><i class='fas fa-trash'></i></a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center p-5 text-muted'>
                            <i class='fas fa-box-open fa-3x mb-3'></i><br>查無符合條件的資料
                          </td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const specieSelect = document.getElementById('search_sID');
        const breedSelect = document.getElementById('search_bID');
        const allBreeds = Array.from(breedSelect.querySelectorAll('option')); 

        function filterBreeds() {
            const selectedSpecieID = specieSelect.value;
            const currentSelectedBreed = "<?php echo $filter_bID; ?>";
            breedSelect.innerHTML = '';
            allBreeds.forEach(option => {
                const sid = option.getAttribute('data-sid');
                const val = option.value;

                // 條件：(選全部) 或 (該選項屬於選中的物種) 或 (該選項是"全部"這個標籤)
                if (selectedSpecieID === "" || sid === selectedSpecieID || val === "") {
                    const newOption = option.cloneNode(true);
                    breedSelect.appendChild(newOption);
                }
            });

            breedSelect.value = currentSelectedBreed;
            if (breedSelect.selectedIndex === -1) {
                breedSelect.value = "";
            }
        }

        specieSelect.addEventListener('change', function() {
            filterBreeds();
            breedSelect.value = ""; 
        });

        filterBreeds();
    });
    </script>

</body>
</html>