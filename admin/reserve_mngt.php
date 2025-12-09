<?php
session_start();
// 檢查是否有登入 Session
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';

// ==========================================
//  1. 編輯模式：讀取舊資料
// ==========================================
$editData = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']); // 安全轉型
    $stmt = $conn->prepare("SELECT * FROM reserve WHERE rID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $editData = $res->fetch_assoc();
}

// ==========================================
//  2. 處理資料儲存 (新增 或 修改)
// ==========================================
if (isset($_POST['save'])) {
    $petID = $_POST['petID'];
    $rName = $_POST['rName'];
    $rPhone = $_POST['rPhone'];
    $time = $_POST['time'];
    $status = $_POST['status']; // 接收狀態
    
    if (!empty($_POST['rID'])) {
        // [修改 Update]
        $id = $_POST['rID'];
        $sql = "UPDATE reserve SET petID='$petID', rName='$rName', rPhone='$rPhone', time='$time', status='$status' WHERE rID=$id";
        $msg = "預約資料修改成功！";
    } else {
        // [新增 Insert]
        $sql = "INSERT INTO reserve (petID, rName, rPhone, time, status) 
                VALUES ('$petID', '$rName', '$rPhone', '$time', '$status')";
        $msg = "新增預約成功！";
    }

    if ($conn->query($sql) === TRUE) {
        // ★ 修正：輸出完整 HTML 頁面來顯示 SweetAlert2，然後跳轉
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
                    window.location.href='reserve_mngt.php';
                });
            </script>
        </body>
        </html>";
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// ==========================================
//  3. 處理刪除
// ==========================================
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM reserve WHERE rID=$id");
    header("Location: reserve_mngt.php");
    exit;
}

// ==========================================
//  4. 處理搜尋與查詢 SQL
// ==========================================
$searchKeyword = '';

$sql_query = "SELECT reserve.*, pet.petID, breed.bName, store.storeName 
              FROM reserve 
              LEFT JOIN pet ON reserve.petID = pet.petID 
              LEFT JOIN breed ON pet.bID = breed.bID 
              LEFT JOIN store ON pet.storeID = store.storeID
              WHERE 1=1"; 

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    // 簡單防注入
    $safeKey = $conn->real_escape_string($searchKeyword);
    $sql_query .= " AND (reserve.rName LIKE '%$safeKey%' 
                     OR reserve.rPhone LIKE '%$safeKey%'
                     OR breed.bName LIKE '%$safeKey%')";
}

$sql_query .= " ORDER BY reserve.time DESC";

// 執行查詢
$result = $conn->query($sql_query);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>預約管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // ★ SweetAlert2 刪除確認函式
    function confirmDelete(url) {
        event.preventDefault(); // 阻止連結直接跳轉
        Swal.fire({
            title: '確定刪除此預約？',
            text: "刪除後無法復原！",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '刪除',
            cancelButtonText: '取消'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url; // 確認後才跳轉
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
        <h3>預約管理</h3>

        <form method="get" class="row mb-4 align-items-center">
            <div class="col-auto">
                <label class="col-form-label fw-bold">🔍 搜尋：</label>
            </div>
            <div class="col-auto">
                <input type="text" name="search" class="form-control" placeholder="姓名、電話或品種..." 
                       value="<?php echo htmlspecialchars($searchKeyword); ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">查詢</button>
                <?php if(!empty($searchKeyword)): ?>
                    <a href="reserve_mngt.php" class="btn btn-outline-secondary">清除</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white fw-bold">
                <?php echo $editData ? '編輯預約' : '手動新增預約'; ?>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="rID" value="<?php echo $editData['rID'] ?? ''; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="col-form-label fw-bold">選擇寵物 (僅顯示在店)</label>
                            <select name="petID" class="form-select" required>
                                <option value="">請選擇...</option>
                                <?php
                                // 撈取寵物：顯示「在店」的，或者「目前編輯選中」的那一隻
                                $p_sql = "SELECT pet.petID, breed.bName, store.storeName, pet.status 
                                          FROM pet 
                                          JOIN breed ON pet.bID = breed.bID 
                                          JOIN store ON pet.storeID = store.storeID 
                                          WHERE pet.status = '在店'";
                                
                                if ($editData) {
                                    $curPetID = $editData['petID'];
                                    $p_sql .= " OR pet.petID = $curPetID";
                                }

                                $p_res = $conn->query($p_sql);
                                while($p = $p_res->fetch_assoc()){
                                    $sel = ($editData && $editData['petID'] == $p['petID']) ? 'selected' : '';
                                    echo "<option value='{$p['petID']}' $sel>ID:{$p['petID']} - {$p['bName']} ({$p['storeName']}) [{$p['status']}]</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="col-form-label fw-bold">預約人姓名</label>
                            <input type="text" name="rName" class="form-control" required value="<?php echo $editData['rName'] ?? ''; ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="col-form-label fw-bold">電話</label>
                            <input type="text" name="rPhone" class="form-control" required value="<?php echo $editData['rPhone'] ?? ''; ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="col-form-label fw-bold">預約時間</label>
                            <input type="datetime-local" name="time" class="form-control" required 
                                   value="<?php echo $editData ? date('Y-m-d\TH:i', strtotime($editData['time'])) : ''; ?>">
                        </div>

                        <div class="col-md-2">
                            <label class="col-form-label fw-bold">狀態</label>
                            <select name="status" class="form-control">
                                <option value="申請購買" <?php echo ($editData && $editData['status']=='申請購買') ? 'selected' : ''; ?>>申請購買</option>
                                <option value="待確認" <?php echo ($editData && $editData['status']=='待確認') ? 'selected' : ''; ?>>待確認</option>
                                <option value="已確認" <?php echo ($editData && $editData['status']=='已確認') ? 'selected' : ''; ?>>已確認</option>
                                <option value="已完成" <?php echo ($editData && $editData['status']=='已完成') ? 'selected' : ''; ?>>已完成</option>
                                <option value="已取消" <?php echo ($editData && $editData['status']=='已取消') ? 'selected' : ''; ?>>已取消</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="save" class="btn btn-dark-custom w-100">
                                <?php echo $editData ? '確認修改' : '新增預約'; ?>
                            </button>
                            <?php if($editData): ?>
                                <a href="reserve_mngt.php" class="btn btn-secondary w-100 mt-2">取消修改</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>寵物 (品種/店名)</th>
                        <th>姓名</th>
                        <th>電話</th>
                        <th>預約時間</th>
                        <th>狀態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // 狀態顏色邏輯
                            $statusColor = 'text-muted'; 
                            if ($row['status'] == '申請購買' || $row['status'] == '待確認') $statusColor = 'text-danger fw-bold';
                            if ($row['status'] == '已確認') $statusColor = 'text-success fw-bold'; 
                            if ($row['status'] == '已完成') $statusColor = 'text-primary fw-bold'; 

                            // 處理搜尋關鍵字高亮
                            $showName = $row['rName'];
                            $showPhone = $row['rPhone'];
                            if (!empty($searchKeyword)) {
                                $showName = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showName);
                                $showPhone = str_replace($searchKeyword, "<span class='bg-warning'>$searchKeyword</span>", $showPhone);
                            }

                            echo "<tr>
                                <td>{$row['rID']}</td>
                                <td>
                                    <span class='badge bg-info text-dark'>ID:{$row['petID']}</span> 
                                    {$row['bName']} <br> 
                                    <small class='text-muted'>{$row['storeName']}</small>
                                </td>
                                <td>{$showName}</td>
                                <td>{$showPhone}</td>
                                <td>{$row['time']}</td>
                                <td class='{$statusColor}'>{$row['status']}</td>
                                <td>
                                    <a href='?edit={$row['rID']}' class='btn btn-warning btn-sm me-1'><i class='fas fa-edit'></i></a>
                                    <a href='?del={$row['rID']}' onclick='confirmDelete(this.href)' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-4 text-muted'>查無預約資料</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>