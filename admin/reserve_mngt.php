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
    $result = $conn->query("SELECT * FROM reserve WHERE rID = $id");
    $editData = $result->fetch_assoc();
}

// --- 處理資料儲存 (新增 或 修改) ---
if (isset($_POST['save'])) {
    $petID = $_POST['petID'];
    $rName = $_POST['rName'];
    $rPhone = $_POST['rPhone'];
    $time = $_POST['time'];
    
    if (!empty($_POST['rID'])) {
        // [修改 Update]
        $id = $_POST['rID'];
        // 注意：這裡只修改預約資訊，不處理狀態改變 (狀態由確認按鈕處理)
        $sql = "UPDATE reserve SET petID='$petID', rName='$rName', rPhone='$rPhone', time='$time' WHERE rID=$id";
        $msg = "預約資料修改成功！";
    } else {
        // [新增 Insert]
        $sql = "INSERT INTO reserve (petID, rName, rPhone, time, status) 
                VALUES ('$petID', '$rName', '$rPhone', '$time', '待確認')";
        $msg = "新增預約成功！";
    }

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('$msg'); window.location.href='reserve_mngt.php';</script>";
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}

// --- 處理刪除 ---
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM reserve WHERE rID=" . $_GET['del']);
    header("Location: reserve_mngt.php");
    exit;
}

// --- 確認預約 ---
if (isset($_GET['confirm'])) {
    $rID = $_GET['confirm'];
    $petID = $_GET['petID']; 

    $conn->begin_transaction();
    try {
        // 更新預約狀態
        $conn->query("UPDATE reserve SET status='已確認' WHERE rID=$rID");
        // 更新寵物狀態 (鎖定寵物)
        $conn->query("UPDATE pet SET status='已預約' WHERE petID=$petID");
        
        // 兩者都成功才提交
        $conn->commit();
        echo "<script>alert('預約已確認，寵物已鎖定！'); window.location.href='reserve_mngt.php';</script>";
    } catch (Exception $e) {
        // 失敗就復原
        $conn->rollback();
        echo "操作失敗：" . $e->getMessage();
    }
}

// --- 處理搜尋邏輯 ---
$searchKeyword = '';
$sql_query = "SELECT reserve.*, pet.petID, breed.bName, store.storeName 
              FROM reserve 
              JOIN pet ON reserve.petID = pet.petID 
              JOIN breed ON pet.bID = breed.bID 
              JOIN store ON pet.storeID = store.storeID";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    // 搜尋：預約人姓名、電話 或 寵物品種名
    $sql_query .= " WHERE reserve.rName LIKE '%$searchKeyword%' 
                    OR reserve.rPhone LIKE '%$searchKeyword%'
                    OR breed.bName LIKE '%$searchKeyword%'";
}

$sql_query .= " ORDER BY reserve.time DESC";
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>預約管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h3>預約管理</h3>

        <!-- 搜尋欄位 -->
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

        <!-- 表單區域 -->
        <form method="post" class="card p-4 mb-4 bg-white shadow-sm border-secondary">
            <h5 class="card-title text-dark"><?php echo $editData ? '編輯預約' : '新增預約'; ?></h5>
            <input type="hidden" name="rID" value="<?php echo $editData['rID'] ?? ''; ?>">

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">選擇寵物 (僅顯示在店)</label>
                    <select name="petID" class="form-select" required>
                        <option value="">請選擇...</option>
                        <?php
                        $pet_sql = "SELECT pet.petID, breed.bName, store.storeName, pet.status 
                                    FROM pet 
                                    JOIN breed ON pet.bID = breed.bID 
                                    JOIN store ON pet.storeID = store.storeID 
                                    WHERE pet.status = '在店'";
                        
                        if ($editData) {
                            $currentPetID = $editData['petID'];
                            $pet_sql .= " OR pet.petID = $currentPetID";
                        }

                        $res = $conn->query($pet_sql);
                        while ($r = $res->fetch_assoc()) { 
                            $selected = ($editData && $r['petID'] == $editData['petID']) ? 'selected' : '';
                            echo "<option value='{$r['petID']}' $selected>{$r['petID']}號 - {$r['bName']} ({$r['storeName']}) [{$r['status']}]</option>"; 
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">預約人姓名</label>
                    <input type="text" name="rName" class="form-control" required
                           value="<?php echo $editData['rName'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">電話</label>
                    <input type="text" name="rPhone" class="form-control" required
                           value="<?php echo $editData['rPhone'] ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">預約時間</label>
                    <?php 
                        $timeValue = '';
                        if ($editData) {
                            $timeValue = date('Y-m-d\TH:i', strtotime($editData['time']));
                        }
                    ?>
                    <input type="datetime-local" name="time" class="form-control" required
                           value="<?php echo $timeValue; ?>">
                </div>
                <div class="col-12">
                    <button type="submit" name="save" class="btn <?php echo $editData ? 'btn-dark-custom' : 'btn-dark-custom'; ?> w-100">
                        <?php echo $editData ? '確認修改' : '新增預約'; ?>
                    </button>
                    <?php if($editData): ?>
                        <a href="reserve_mngt.php" class="btn btn-secondary w-100 mt-2">取消修改</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <!-- 列表區域 -->
        <table class="table table-hover bg-white shadow-sm align-middle">
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
                // 執行搜尋 SQL
                $result = $conn->query($sql_query);
                
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $statusClass = ($row['status']=='已確認') ? 'text-success fw-bold' : 'text-danger';
                        
                        // 產生確認按鈕 (僅待確認時顯示)
                        $confirmBtn = "";
                        if ($row['status'] == '待確認') {
                            $confirmBtn = "<a href='?confirm={$row['rID']}&petID={$row['petID']}' class='btn btn-outline-success btn-sm' title='確認並鎖定寵物'><i class='fas fa-check'></i> 確認</a>";
                        } else {
                            $confirmBtn = "<span class='badge bg-secondary'>已處理</span>";
                        }

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
                                    {$row['bName']}<br>
                                    <small class='text-muted'>{$row['storeName']}</small>
                                </td>
                                <td>{$showName}</td>
                                <td>{$showPhone}</td>
                                <td>{$row['time']}</td>
                                <td class='$statusClass'>{$row['status']}</td>
                                <td>
                                    $confirmBtn
                                    <a href='?edit={$row['rID']}' class='btn btn-warning btn-sm'><i class='fas fa-edit'></i></a>
                                    <a href='?del={$row['rID']}' class='btn btn-danger btn-sm' onclick='return confirm(\"確定刪除此預約？\")'><i class='fas fa-trash'></i></a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center p-4 text-muted'>查無資料</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>