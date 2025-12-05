<?php
include 'db_connect.php';
if (isset($_POST['add'])) {
    $conn->query("INSERT INTO PRODUCT (pName, storeID, stock) VALUES ('{$_POST['pName']}', '{$_POST['storeID']}', '{$_POST['stock']}')");
    header("Location: product_mngt.php");
}
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM PRODUCT WHERE pID={$_GET['del']}");
    header("Location: product_mngt.php");
}
?>
<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h3>商品管理</h3>
        <form method="post" class="row g-3 mb-4 bg-white p-3 rounded shadow-sm">
            <div class="col-md-3">
                <select name="storeID" class="form-select" required>
                    <option value="">選擇分店...</option>
                    <?php
                    $res = $conn->query("SELECT * FROM STORE");
                    while($r=$res->fetch_assoc()) echo "<option value='{$r['storeID']}'>{$r['storeName']}</option>";
                    ?>
                </select>
            </div>
            <div class="col-md-4"><input type="text" name="pName" class="form-control" placeholder="商品名稱" required></div>
            <div class="col-md-2"><input type="number" name="stock" class="form-control" placeholder="庫存" required></div>
            <div class="col-md-3"><button type="submit" name="add" class="btn btn-success w-100">新增</button></div>
        </form>
        <table class="table table-hover bg-white shadow-sm">
            <thead class="table-success"><tr><th>品名</th><th>分店</th><th>庫存</th><th>操作</th></tr></thead>
            <tbody>
                <?php
                $sql = "SELECT P.*, S.storeName FROM PRODUCT P JOIN STORE S ON P.storeID = S.storeID";
                $res = $conn->query($sql);
                while ($row = $res->fetch_assoc()) {
                    echo "<tr><td>{$row['pName']}</td><td>{$row['storeName']}</td><td>{$row['stock']}</td>
                          <td><a href='?del={$row['pID']}' class='btn btn-sm btn-outline-danger'>刪除</a></td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>