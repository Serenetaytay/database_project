<?php
include 'db_connect.php';

// --- 新增 ---
if (isset($_POST['add'])) {
    $sID = $_POST['sID'];
    $bName = $_POST['bName'];
    $sql = "INSERT INTO breed (sID, bName) VALUES ('$sID', '$bName')";
    if($conn->query($sql)) {
        echo "<script>alert('新增成功'); window.location.href='breed_mngt.php';</script>";
    }
}

// --- 刪除 ---
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM breed WHERE bID=" . $_GET['del']);
    header("Location: breed_mngt.php");
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>品種管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">回首頁</a>
    <h2 class="mb-4">品種管理</h2>

    <form method="post" class="card p-4 mb-4 bg-light">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">所屬物種</label>
                <select name="sID" class="form-select" required>
                    <option value="">請選擇...</option>
                    <?php
                    $result = $conn->query("SELECT * FROM specie");
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['sID']}'>{$row['sName']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">品種名稱</label>
                <input type="text" name="bName" class="form-control" placeholder="例如：黃金獵犬" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="add" class="btn btn-primary w-100">新增</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>物種</th>
                <th>品種名稱</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT breed.bID, breed.bName, specie.sName 
                    FROM breed 
                    JOIN specie ON breed.sID = specie.sID";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['bID']}</td>
                        <td>{$row['sName']}</td>
                        <td>{$row['bName']}</td>
                        <td><a href='?del={$row['bID']}' class='btn btn-danger btn-sm' onclick='return confirm(\"確認刪除？\")'>刪除</a></td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>