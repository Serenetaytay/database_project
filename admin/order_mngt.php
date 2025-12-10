<?php
include 'db_connect.php';
// --- 刪除訂單 ---
if (isset($_GET['del'])) {
    $conn->query("DELETE FROM ORDERS WHERE orderID={$_GET['del']}");
    header("Location: order_mngt.php");
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <h3 class="mb-4 fw-bold"> 商品訂單管理 (到店取貨)</h3>
        
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>訂單ID</th>
                            <th>客戶姓名</th>
                            <th>電話</th>
                            <th>購買商品</th>
                            <th>總金額</th>
                            <th>預約取貨時間</th>
                            <th>下單時間</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // 查詢 ORDERS 表格，按照下單時間倒序排列
                        $sql = "SELECT * FROM ORDERS ORDER BY orderDate DESC";
                        $res = $conn->query($sql);

                        if ($res->num_rows > 0) {
                            while ($row = $res->fetch_assoc()) {
                                echo "<tr>
                                    <td>#{$row['orderID']}</td>
                                    <td class='fw-bold'>{$row['customerName']}</td>
                                    <td>{$row['phone']}</td>
                                    <td class='text-primary'>{$row['productName']}</td>
                                    <td class='text-danger fw-bold'>\${$row['totalAmount']}</td>
                                    <td>{$row['pickupTime']}</td>
                                    <td class='text-muted small'>{$row['orderDate']}</td>
                                    <td>
                                        <a href='?del={$row['orderID']}' class='btn btn-sm btn-outline-danger' onclick='return confirm(\"確定刪除這筆訂單嗎？\");'>刪除</a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center py-4 text-muted'>目前還沒有訂單喔！</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>