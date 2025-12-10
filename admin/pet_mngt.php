<?php
session_start();
// 檢查是否有登入 Session，沒有就踢回 login.php
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
// 新增物種
if (isset($_POST['add_specie'])) {
    $sName = $_POST['sName'];
    if ($stmt->execute()) {
        // ★ 修改：SweetAlert2 成功提示
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
                    title: '物種新增成功！',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href='pet_mngt.php';
                });
            </script>
        </body>
        </html>";
        exit;
    } else {
        echo "<script>alert('新增失敗: " . $conn->error . "');</script>";
    }
}

// 刪除物種
if (isset($_GET['del_specie'])) {
    $id = intval($_GET['del_specie']);
    $sql = "DELETE FROM SPECIE WHERE sID = $id";
    if ($conn->query($sql)) {
        // ★ 修改：刪除成功直接跳轉 (與其他頁面一致)
        header("Location: pet_mngt.php");
        exit;
    } else {
        // ★ 修改：刪除失敗提示
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
                    text: '可能原因：該物種下還有品種資料，請先清空品種。',
                    confirmButtonText: '確定'
                }).then(() => {
                    window.location.href='pet_mngt.php';
                });
            </script>
        </body>
        </html>";
        exit;
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
            // ★ 修改：SweetAlert2 成功提示
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
                        title: '品種新增成功！',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href='pet_mngt.php';
                    });
                </script>
            </body>
            </html>";
            exit;
        }
    }
}

// 刪除品種
if (isset($_GET['del_breed'])) {
    $id = intval($_GET['del_breed']);
    $sql = "DELETE FROM BREED WHERE bID = $id";
    if ($conn->query($sql)) {
        // ★ 修改：刪除成功直接跳轉
        header("Location: pet_mngt.php");
        exit;
    } else {
        // ★ 修改：刪除失敗提示
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
                    text: '可能原因：還有寵物屬於此品種，請先刪除寵物或修改其品種。',
                    confirmButtonText: '確定'
                }).then(() => {
                    window.location.href='pet_mngt.php';
                });
            </script>
        </body>
        </html>";
        exit;
    }
}
// 編輯模式
$editData = null;
$showCollapse = ""; 
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM PET WHERE petID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editData = $result->fetch_assoc();
    $showCollapse = "show"; 
}

// 儲存寵物資料
if (isset($_POST['save_pet'])) {
    $manualID = $_POST['petID']; 
    $originalID = $_POST['original_petID'];

    $bID = $_POST['bID'];
    $storeID = $_POST['storeID'];
    $birth = $_POST['birth'];
    $sex = $_POST['sex'];
    $personality = $_POST['personality'];
    $petprice = $_POST['petprice'];
    $status = $_POST['status'] ?? '在店';
    $imagePath = $_POST['old_image'] ?? '';

    if (isset($_FILES['petImage']) && $_FILES['petImage']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $ext = pathinfo($_FILES['petImage']['name'], PATHINFO_EXTENSION);
        $fileName = time() . '_' . uniqid() . '.' . $ext;
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['petImage']['tmp_name'], $targetFile)) {
            $imagePath = $targetFile;
        }
    }

    try {
        if (!empty($originalID)) {
            // Update
            $sql = "UPDATE PET SET petID=?, bID=?, storeID=?, birth=?, sex=?, personality=?, status=?, petprice=?, petImage=? WHERE petID=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiissssssi", $manualID, $bID, $storeID, $birth, $sex, $personality, $status, $petprice, $imagePath, $originalID);
            $msg = "修改 ID: $manualID成功 ！";
        } else {
            // Insert
            $check = $conn->query("SELECT petID FROM PET WHERE petID = $manualID");
            if ($check->num_rows > 0) {
                throw new Exception("Duplicate entry '{$manualID}' for key 'PRIMARY'");
            }

            $sql = "INSERT INTO PET (petID, bID, storeID, birth, sex, personality, status, petprice, petImage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiissssss", $manualID, $bID, $storeID, $birth, $sex, $personality, $status, $petprice, $imagePath);
            $msg = "新增 ID: $manualID成功 ！";
        }
        
        if ($stmt->execute()) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',       // 圖示：success, error, warning, info
                        title: '成功',
                        text: '$msg',          // PHP 的變數訊息
                        timer: 2000,           // 2秒後自動關閉
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href='pet_mngt.php'; // 彈窗關閉後跳轉
                    });
                });
            </script>";
        } else {
            throw new Exception($stmt->error);
        }

    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo "<script>alert('錯誤：ID {$manualID} 已經存在！請使用其他號碼。'); window.history.back();</script>";
        } else {
            echo "<script>alert('資料庫錯誤：" . addslashes($e->getMessage()) . "'); window.history.back();</script>";
        }
        exit();
    }
}

// 刪除
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

$filter_sID = $_GET['filter_sID'] ?? '';
$filter_bID = $_GET['filter_bID'] ?? '';
$filter_min = $_GET['filter_min'] ?? '';
$filter_max = $_GET['filter_max'] ?? '';
$searchKeyword = $_GET['search'] ?? '';

if (!empty($filter_sID)) { $sql_query .= " AND SPECIE.sID = '$filter_sID'"; }
if (!empty($filter_bID)) { $sql_query .= " AND BREED.bID = '$filter_bID'"; }
if (!empty($filter_min)) { $sql_query .= " AND PET.petprice >= $filter_min"; }
if (!empty($filter_max)) { $sql_query .= " AND PET.petprice <= $filter_max"; }
if (!empty($searchKeyword)) {
    $sql_query .= " AND (BREED.bName LIKE '%$searchKeyword%' 
                     OR STORE.storeName LIKE '%$searchKeyword%' 
                     OR PET.personality LIKE '%$searchKeyword%')";
}

$sql_query .= " ORDER BY PET.petID ASC";
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>寵物管理系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .manage-list {
            max-height: 200px;
            overflow-y: auto;
        }
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
<script>
function confirmDelete(url) {
    event.preventDefault(); 
    
    Swal.fire({
        title: '確定要刪除嗎？',
        text: "刪除後將無法復原這筆資料！",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '是的，刪除！',
        cancelButtonText: '取消'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}
</script>
<body class="bg-light">
    
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>寵物管理</h3>
            <div>
                <button class="btn btn-outline-secondary btn-sm me-1" type="button" data-bs-toggle="collapse" data-bs-target="#addSpecieBox">
                    <i class="fas fa-list-ul"></i> 管理物種
                </button>
                <button class="btn btn-outline-secondary btn-sm me-1" type="button" data-bs-toggle="collapse" data-bs-target="#addBreedBox">
                    <i class="fas fa-list-ul"></i> 管理品種
                </button>
                <button class="btn btn-dark-custom btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#addPetBox">
                    <i class="fas fa-paw"></i> <?php echo $editData ? '編輯寵物' : '新增寵物'; ?>
                </button>
            </div>
        </div>

        <form method="get" class="card p-3 mb-3 bg-white shadow-sm border-secondary">
            <div class="row g-2 align-items-center">
                <div class="col-md-2">
                    <select name="filter_sID" id="search_sID" class="form-select form-select-sm">
                        <option value="">-- 物種 --</option>
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
                    <select name="filter_bID" id="search_bID" class="form-select form-select-sm">
                        <option value="">-- 品種 --</option>
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
                    <div class="input-group input-group-sm">
                        <input type="number" name="filter_min" class="form-control" placeholder="最低價" value="<?php echo $filter_min; ?>">
                        <span class="input-group-text">~</span>
                        <input type="number" name="filter_max" class="form-control" placeholder="最高價" value="<?php echo $filter_max; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="關鍵字..." value="<?php echo htmlspecialchars($searchKeyword); ?>">
                </div>
                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-secondary btn-sm w-100 me-1"><i class="fas fa-search"></i> 查詢</button>
                    <?php if(!empty($searchKeyword) || !empty($filter_sID) || !empty($filter_min)): ?>
                        <a href="pet_mngt.php" class="btn btn-outline-secondary btn-sm w-50">清除</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <div class="mb-3">
            <div class="collapse mb-2" id="addSpecieBox">
                <div class="card bg-light border-secondary">
                    <div class="card-header bg-secondary text-white py-1">管理物種</div>
                    <div class="card-body">
                        <form method="post" class="row g-2 align-items-center mb-3">
                            <div class="col-auto"><label class="fw-bold">新名稱：</label></div>
                            <div class="col-auto"><input type="text" name="sName" class="form-control form-control-sm" required></div>
                            <div class="col-auto"><button type="submit" name="add_specie" class="btn btn-sm btn-secondary">新增</button></div>
                        </form>
                        <hr>
                        <div class="manage-list bg-white p-2 border rounded">
                            <table class="table table-sm table-borderless mb-0">
                                <?php
                                $all_s = $conn->query("SELECT * FROM SPECIE");
                                while ($row = $all_s->fetch_assoc()) {
                                    echo "<tr>
                                    <td>{$row['sName']}</td>
                                    <td class='text-end'>
                                        <a href='?del_specie={$row['sID']}' 
                                           onclick='confirmDelete(this.href); return false;' 
                                           class='btn btn-danger btn-sm'>
                                           <i class='fas fa-trash'></i>
                                        </a>
                                    </td>
                                    </tr>";
                                }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse mb-2" id="addBreedBox">
                <div class="card bg-light border-dark">
                    <div class="card-header bg-dark text-white py-1">管理品種</div>
                    <div class="card-body">
                        <form method="post" class="row g-2 align-items-center mb-3">
                            <div class="col-auto"><label class="fw-bold">所屬物種：</label></div>
                            <div class="col-auto">
                                <select name="sID" class="form-select form-select-sm" required>
                                    <option value="">請選擇</option>
                                    <?php
                                    $s_res2 = $conn->query("SELECT * FROM SPECIE");
                                    while($s = $s_res2->fetch_assoc()) echo "<option value='{$s['sID']}'>{$s['sName']}</option>";
                                    ?>
                                </select>
                            </div>
                            <div class="col-auto"><input type="text" name="bName" class="form-control form-control-sm" placeholder="新品種名" required></div>
                            <div class="col-auto"><button type="submit" name="add_breed" class="btn btn-sm btn-dark">新增</button></div>
                        </form>
                        <hr>
                        <div class="manage-list bg-white p-2 border rounded">
                            <table class="table table-sm table-striped mb-0">
                                <thead><tr><th>物種</th><th>品種</th><th>操作</th></tr></thead>
                                <tbody>
                                <?php
                                $all_b = $conn->query("SELECT BREED.*, SPECIE.sName FROM BREED JOIN SPECIE ON BREED.sID = SPECIE.sID ORDER BY SPECIE.sID");
                                while ($row = $all_b->fetch_assoc()) {
                                    echo "<tr>
                                            <td><span class='badge bg-secondary'>{$row['sName']}</span></td>
                                            <td>{$row['bName']}</td>
                                            <td class='text-end'>
                                                <a href='?del_breed={$row['bID']}' 
                                                   class='text-danger text-decoration-none' 
                                                   onclick='confirmDelete(this.href); return false;'>[刪除]</a>
                                            </td>
                                          </tr>";
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="collapse <?php echo $showCollapse; ?>" id="addPetBox">
                <form method="post" enctype="multipart/form-data" class="card p-4 bg-white shadow-sm border border-secondary border-2">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="text-dark m-0">
                            <?php echo $editData ? '<i class="fas fa-edit"></i> 編輯寵物資料' : '<i class="fas fa-plus-circle"></i> 新增寵物資料'; ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#addPetBox"></button>
                    </div>
                    
                    <input type="hidden" name="original_petID" value="<?php echo $editData['petID'] ?? ''; ?>">
                    <input type="hidden" name="old_image" value="<?php echo $editData['petImage'] ?? ''; ?>">

                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-bold text-dark">自訂 ID</label>
                            <input type="number" name="petID" class="form-control border-dark" 
                                   placeholder="輸入號碼" required 
                                   value="<?php echo $editData['petID'] ?? ''; ?>">
                        </div>

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
                            <label class="form-label small text-muted">分店</label>
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
                        <div class="col-md-4">
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
                            <label class="form-label small text-muted">個性</label>
                            <input type="text" name="personality" class="form-control" value="<?php echo $editData['personality'] ?? ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">價格</label>
                            <input type="number" name="petprice" class="form-control" required value="<?php echo $editData['petprice'] ?? ''; ?>">
                        </div>

                        <?php if($editData): ?>
                        <div class="col-md-12">
                            <label class="form-label small text-danger fw-bold">狀態</label>
                            <select name="status" class="form-select border-danger">
                                <option value="在店" <?php echo ($editData['status']=='在店')?'selected':''; ?>>在店</option>
                                <option value="已預約" <?php echo ($editData['status']=='已預約')?'selected':''; ?>>已預約</option>
                                <option value="已售出" <?php echo ($editData['status']=='已售出')?'selected':''; ?>>已售出</option>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-12">
                            <label class="form-label small text-muted">照片</label>
                            <input type="file" name="petImage" class="form-control" accept="image/*">
                            <?php if ($editData && !empty($editData['petImage'])): ?>
                                <div class="mt-2">
                                    <img src="<?php echo $editData['petImage']; ?>" style="height: 100px; border-radius: 5px; border: 1px solid #ddd;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 mt-3 text-end">
                            <?php if($editData): ?>
                                <a href="pet_mngt.php" class="btn btn-secondary me-2">取消</a>
                            <?php endif; ?>
                            <button type="submit" name="save_pet" class="btn btn-dark w-100">
                                <?php echo $editData ? '<i class="fas fa-check"></i> 確認修改' : '<i class="fas fa-plus"></i> 確認新增'; ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <table class="table table-hover align-middle bg-white shadow-sm rounded overflow-hidden">
            <thead class="table-dark">
                <tr>
                    <th>編號 (分店-ID)</th> 
                    <th>照片</th>
                    <th>物種</th>
                    <th>品種</th>
                    <th>個性</th>
                    <th>性別</th>
                    <th>狀態</th>
                    <th>分店</th>
                    <th>價格</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query($sql_query);
                
                $colorMap = [
                    'text-primary', 'text-success', 'text-danger', 'text-info', 'text-dark', 'text-secondary', 'text-warning'
                ];
                $colorCount = count($colorMap); 

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $imgHtml = "<span class='text-muted small'>無</span>";
                        if (!empty($row['petImage'])) {
                            $imgHtml = "<img src='{$row['petImage']}' style='width: 60px; height: 60px; object-fit: cover; border-radius: 5px;'>";
                        }

                        $visualID = $row['storeID'] . "-" . str_pad($row['petID'], 3, '0', STR_PAD_LEFT);

                        $showBreed = $row['bName'];
                        $showStore = $row['storeName'];
                        $showPers = $row['personality']; 
                        if (!empty($searchKeyword)) {
                            $showBreed = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showBreed);
                            $showStore = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showStore);
                            $showPers = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showPers);
                        }

                        $colorIndex = $row['storeID'] % $colorCount;
                        $idColorClass = $colorMap[$colorIndex];

                        $sName = $row['sName'];
                        $speciesBadgeClass = 'bg-dark';
                        if ($sName == '貓') {
                            $speciesBadgeClass = 'bg-secondary';
                        } elseif ($sName == '狗') {
                            $speciesBadgeClass = 'bg-primary';
                        }

                        $status = $row['status'];
                        $statusBadgeClass = 'bg-info text-dark';
                        if ($status == '已預約') {
                            $statusBadgeClass = 'bg-success';
                        } elseif ($status == '已售出') {
                            $statusBadgeClass = 'bg-danger';
                        }

                        echo "<tr>
                                <td class='fw-bold {$idColorClass}'>{$visualID}</td> 
                                <td>{$imgHtml}</td>
                                <td><span class='badge {$speciesBadgeClass}'>{$sName}</span></td>
                                <td>{$showBreed}</td>
                                <td><small class='text-muted'>{$showPers}</small></td> 
                                <td>{$row['sex']}</td> 
                                <td><span class='badge {$statusBadgeClass}'>{$status}</span></td>
                                <td>{$showStore}</td>
                                <td class='text-success fw-bold'>$ {$row['petprice']}</td>
                                <td>
                                    <a href='?edit={$row['petID']}' class='btn btn-warning btn-sm mb-1'><i class='fas fa-edit'></i></a>
                                    <a href='?del={$row['petID']}' 
                                       class='btn btn-danger btn-sm mb-1' 
                                       onclick='confirmDelete(this.href); return false;'>
                                       <i class='fas fa-trash'></i>
                                    </a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='10' class='text-center p-5 text-muted'>
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
                if (selectedSpecieID === "" || sid === selectedSpecieID || val === "") {
                    breedSelect.appendChild(option.cloneNode(true));
                }
            });
            breedSelect.value = currentSelectedBreed;
            if (breedSelect.selectedIndex === -1) breedSelect.value = "";
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