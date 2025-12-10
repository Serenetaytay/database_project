<?php
include '../db_connect.php';

$id = $_GET['id'];
// 撈出商品資訊
$sql = "SELECT P.*, S.storeName FROM PRODUCT P JOIN STORE S ON P.storeID = S.storeID WHERE pID = $id";
$product = $conn->query($sql)->fetch_assoc();
$price = 100; // 假設固定價格

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $pickupTime = $_POST['pickupTime'];
    $qty = $_POST['qty'];
    
    // 組合訂單資訊
    $productInfo = $product['pName'] . " x " . $qty; 
    $total = $price * $qty;

    // 直接寫入訂單，取貨方式強制設為 '店面自取'
    $sql = "INSERT INTO ORDERS (customerName, phone, totalAmount, deliveryMethod, pickupTime, productName) 
            VALUES ('$name', '$phone', '$total', '店面自取', '$pickupTime', '$productInfo')";
    
    if ($conn->query($sql)) {
        echo "<script>alert('預約成功！請於指定時間前往 [{$product['storeName']}] 取貨付款。'); location.href='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>預約取貨</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php 
   
    include '../nav_client.php'; 
    ?>
    
    <div class="container mt-5" style="max-width: 600px;">
        <div class="card p-4 border-0 shadow-sm">
            <h3 class="fw-bold mb-4"> 預約取貨單</h3>
            
            <div class="d-flex align-items-center mb-4 p-3 bg-white border rounded">
                <img src="<?php echo !empty($product['pImage']) ? '../../'.$product['pImage'] : 'https://via.placeholder.com/100'; ?>" 
                     style="width: 70px; height: 70px; object-fit: cover; margin-right: 15px; border-radius: 5px;">
                <div>
                    <h5 class="mb-0 fw-bold"><?php echo $product['pName']; ?></h5>
                    <small class="text-muted">取貨分店：<?php echo $product['storeName']; ?></small>
                </div>
            </div>

            <form method="post">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">您的姓名</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">手機號碼</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">購買數量</label>
                    <input type="number" name="qty" class="form-control" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-primary">預計取貨時間</label>
                    <input type="datetime-local" name="pickupTime" class="form-control" required>
                    <div class="form-text">請選擇您方便來店的時間，我們將為您預留商品。</div>
                </div>

                <div class="alert alert-warning small">
                    <i class="bi bi-info-circle"></i> 此交易為 <strong>到店付款</strong>，現場確認商品沒問題再付錢即可。
                </div>
                
                <div class="d-grid">
                    <button type="submit" name="submit" class="btn btn-dark btn-lg">確認預約</button>
                    <a href="product_detail.php?id=<?php echo $id; ?>" class="btn btn-link text-muted mt-2">取消</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>