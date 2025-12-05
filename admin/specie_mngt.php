<?php
include 'db_connect.php';

// --- 新增功能 ---
if (isset($_POST['add'])) {
    $sName = $_POST['sName'];
    $sql = "INSERT INTO specie (sName) VALUES ('$sName')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('新增成功'); window.location.href='specie_mngt.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// --- 刪除功能 ---
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $conn->query("DELETE FROM specie WHERE sID=$id");
    header("Location: specie_mngt.php");
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>物種管理</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">回首頁</a>
    <h2 class="mb-4">1. 物種管理 (Specie)</h2>

    <form method="post" class="card p-4 mb-4 bg-light">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label class="col-form-label">物種名稱：</label>
            </div>
            <div class="col-auto">
                <input type="text" name="sName" class="form-control" placeholder="例如：狗、貓" required>
            </div>
            <div class="col-auto">
                <button type="submit" name="add" class="btn btn-primary">新增</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>物種名稱</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM specie");
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['sID']}</td>
                        <td>{$row['sName']}</td>
                        <td><a href='?del={$row['sID']}' class='btn btn-danger btn-sm' onclick='return confirm(\"確認刪除？\")'>刪除</a></td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>