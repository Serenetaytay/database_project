<?php
include 'db_connect.php';
if (isset($_POST['add'])) {
    $conn->query("INSERT INTO STORE (storeName, address, Phone, worktime) VALUES ('{$_POST['storeName']}', '{$_POST['address']}', '{$_POST['Phone']}', '{$_POST['worktime']}')");
    header("Location: store_mngt.php");
}
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM STORE WHERE storeID={$_GET['del']}");
    header("Location: store_mngt.php");
}
?>
<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h3>商店管理 (Store)</h3>
        <form method="post" class="row g-3 mb-4 bg-white p-3 rounded shadow-sm">
            <div class="col-md-3"><input type="text" name="storeName" class="form-control" placeholder="店名" required></div>
            <div class="col-md-3"><input type="text" name="address" class="form-control" placeholder="地址"></div>
            <div class="col-md-2"><input type="text" name="Phone" class="form-control" placeholder="電話"></div>
            <div class="col-md-2"><input type="text" name="worktime" class="form-control" placeholder="時間"></div>
            <div class="col-md-2"><button type="submit" name="add" class="btn btn-success w-100">新增</button></div>
        </form>
        <table class="table table-hover bg-white shadow-sm">
            <thead class="table-success"><tr><th>ID</th><th>店名</th><th>地址</th><th>電話</th><th>操作</th></tr></thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM STORE");
                while ($row = $res->fetch_assoc()) {
                    echo "<tr><td>{$row['storeID']}</td><td>{$row['storeName']}</td><td>{$row['address']}</td><td>{$row['Phone']}</td>
                          <td><a href='?del={$row['storeID']}' class='btn btn-sm btn-outline-danger'>刪除</a></td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>