<?php 
include '../db_connect.php'; 
$id = $_GET['id'];
// 撈出商品資料 (包含分店資訊，讓客人知道要去哪間店拿)
$sql = "SELECT P.*, S.storeName, S.address FROM PRODUCT P JOIN STORE S ON P.storeID = S.storeID WHERE pID = $id";
$product = $conn->query($sql)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title><?php echo $product['pName']; ?> - 商品詳情</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../nav_client.php'; ?>
    
    <div class="container mt-5">
        <div class="card p-5 border-0 shadow-sm">
            <div class="row">
                <div class="col-md-5">
                     <img src="<?php echo !empty($product['pImage']) ? '../'.$product['pImage'] : 'https://via.placeholder.com/400'; ?>" class="img-fluid rounded shadow-sm">
                </div>
                
                <div class="col-md-7">
                    <span class="badge bg-success mb-2">庫存充足</span>
                    <h2 class="fw-bold"><?php echo $product['pName']; ?></h2>
                    <h3 class="text-danger mt-3 fw-bold">$100 (店面價)</h3>
                    
                    <div class="alert alert-light border mt-4">
                        <h5 class="fw-bold"> 取貨地點</h5>
                        <p class="mb-1"><strong>分店：</strong><?php echo $product['storeName']; ?></p>
                        <p class="mb-0"><strong>地址：</strong><?php echo $product['address']; ?></p>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="reserve_product.php?id=<?php echo $product['pID']; ?>" class="btn btn-dark btn-lg">
                             我要預約 (到店取貨付款)
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary">
                            ← 返回列表
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>