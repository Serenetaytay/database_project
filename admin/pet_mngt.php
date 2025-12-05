<?php
include 'db_connect.php';

// --- 新增 ---
if (isset($_POST['add'])) {
    $bID = $_POST['bID'];
    $storeID = $_POST['storeID'];
    $birth = $_POST['birth'];
    $sex = $_POST['sex'];
    $personality = $_POST['personality'];
    $petprice = $_POST['petprice'];

    $sql = "INSERT INTO pet (bID, storeID, birth, sex, personality, status, petprice) 
            VALUES ('$bID', '$storeID', '$birth', '$sex', '$personality', '在店', '$petprice')";
    $conn->query($sql);
    header("Location: pet_mngt.php");
}

// --- 刪除 ---
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM pet WHERE petID=" . $_GET['del']);
    header("Location: pet_mngt.php");
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>寵物管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">回首頁</a>
    <h2 class="mb-4">寵物管理 (Pet)</h2>

    <form method="post" class="card p-4 mb-4 bg-light">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">品種</label>
                <select name="bID" class="form-select" required>
                    <option value="">請選擇...</option>
                    <?php
                    $res = $conn->query("SELECT * FROM breed");
                    while ($r = $res->fetch_assoc()) { echo "<option value='{$r['bID']}'>{$r['bName']}</option>"; }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">所在分店</label>
                <select name="storeID" class="form-select" required>
                    <option value="">請選擇...</option>
                    <?php
                    // 這裡會去讀取 Person B 建立的 store 資料表
                    $res = $conn->query("SELECT * FROM store");
                    while ($r = $res->fetch_assoc()) { echo "<option value='{$r['storeID']}'>{$r['storeName']}</option>"; }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">生日</label>
                <input type="date" name="birth" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">性別</label>
                <select name="sex" class="form-select">
                    <option value="公">公</option>
                    <option value="母">母</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">個性描述</label>
                <input type="text" name="personality" class="form-control" placeholder="例如：活潑、親人">
            </div>
            <div class="col-md-4">
                <label class="form-label">價格</label>
                <input type="number" name="petprice" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" name="add" class="btn btn-primary w-100">新增寵物</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>分店</th>
                <th>品種</th>
                <th>性別</th>
                <th>狀態</th>
                <th>價格</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 使用 JOIN 連結三個表
            $sql = "SELECT pet.*, breed.bName, store.storeName 
                    FROM pet 
                    LEFT JOIN breed ON pet.bID = breed.bID 
                    LEFT JOIN store ON pet.storeID = store.storeID";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['petID']}</td>
                        <td>{$row['storeName']}</td>
                        <td>{$row['bName']}</td>
                        <td>{$row['sex']}</td>
                        <td><span class='badge bg-info text-dark'>{$row['status']}</span></td>
                        <td>{$row['petprice']}</td>
                        <td><a href='?del={$row['petID']}' class='btn btn-danger btn-sm' onclick='return confirm(\"確認刪除？\")'>刪除</a></td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>