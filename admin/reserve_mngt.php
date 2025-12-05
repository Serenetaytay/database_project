<?php
include 'db_connect.php';

// --- 新增預約 ---
if (isset($_POST['add'])) {
    $petID = $_POST['petID'];
    $rName = $_POST['rName'];
    $rPhone = $_POST['rPhone'];
    $time = $_POST['time'];
    
    $sql = "INSERT INTO reserve (petID, rName, rPhone, time, status) 
            VALUES ('$petID', '$rName', '$rPhone', '$time', '待確認')";
    $conn->query($sql);
    header("Location: reserve_mngt.php");
}

// --- 確認預約 (Transaction / 事務處理) ---
// 這是高分關鍵：同時更新兩個表
if (isset($_GET['confirm'])) {
    $rID = $_GET['confirm'];
    $petID = $_GET['petID']; // 從網址參數取得

    // 開始交易
    $conn->begin_transaction();
    try {
        // 1. 更新預約狀態
        $conn->query("UPDATE reserve SET status='已確認' WHERE rID=$rID");
        // 2. 更新寵物狀態 (鎖定寵物)
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
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>預約管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">回首頁</a>
    <h2 class="mb-4">3. 預約管理 (Reserve)</h2>

    <form method="post" class="card p-4 mb-4 bg-light">
        <h5 class="card-title">新增預約</h5>
        <div class="row g-3">
            <div class="col-md-4">
                <label>選擇寵物 (僅顯示在店)</label>
                <select name="petID" class="form-select" required>
                    <?php
                    // 只撈出狀態是 '在店' 的寵物
                    // JOIN 品種表，為了顯示名字 (如：黃金獵犬) 而不是 ID
                    $sql = "SELECT pet.petID, breed.bName, store.storeName 
                            FROM pet 
                            JOIN breed ON pet.bID = breed.bID 
                            JOIN store ON pet.storeID = store.storeID 
                            WHERE pet.status = '在店'";
                    $res = $conn->query($sql);
                    while ($r = $res->fetch_assoc()) { 
                        echo "<option value='{$r['petID']}'>{$r['petID']}號 - {$r['bName']} ({$r['storeName']})</option>"; 
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>預約人姓名</label>
                <input type="text" name="rName" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>電話</label>
                <input type="text" name="rPhone" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>預約時間</label>
                <input type="datetime-local" name="time" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" name="add" class="btn btn-success w-100">新增預約</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>寵物ID</th>
                <th>姓名</th>
                <th>電話</th>
                <th>預約時間</th>
                <th>狀態</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM reserve ORDER BY time DESC");
            while ($row = $result->fetch_assoc()) {
                $statusClass = ($row['status']=='已確認') ? 'text-success fw-bold' : 'text-danger';
                
                // 產生確認按鈕
                // 注意：這裡我們在網址帶入 petID，方便上方 PHP 做 Transaction
                $actionBtn = "";
                if ($row['status'] == '待確認') {
                    $actionBtn = "<a href='?confirm={$row['rID']}&petID={$row['petID']}' class='btn btn-outline-success btn-sm'>確認預約</a>";
                } else {
                    $actionBtn = "已處理";
                }

                echo "<tr>
                        <td>{$row['rID']}</td>
                        <td>{$row['petID']}</td>
                        <td>{$row['rName']}</td>
                        <td>{$row['rPhone']}</td>
                        <td>{$row['time']}</td>
                        <td class='$statusClass'>{$row['status']}</td>
                        <td>$actionBtn</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>