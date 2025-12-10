<?php 
include '../db_connect.php'; 

if (!isset($_GET['id'])) {
    die("參數錯誤");
}
$id = (int)$_GET['id'];

$sql = "SELECT P.*, S.storeName, S.address 
        FROM PRODUCT P 
        JOIN STORE S ON P.storeID = S.storeID 
        WHERE pID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("查無此商品");
}

$filename = basename($product['pImage']);
$imgSrc = !empty($filename) ? '../../uploads/' . $filename : 'https://via.placeholder.com/400?text=No+Image';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['pName']); ?> - 商品詳情</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .zoom-trigger {
            cursor: zoom-in;
            display: block;
            overflow: hidden;
            border-radius: 0.5rem;
        }
        .zoom-trigger img {
            transition: transform 0.3s ease;
        }
        .zoom-trigger:hover img {
            transform: scale(1.03);
        }
    </style>
</head>
<body class="bg-light">
    
    <?php include '../nav_client.php'; ?>
    
    <div class="container mt-5 mb-5">
        <div class="card p-4 p-md-5 border-0 shadow-sm">
            <div class="row g-4">
                
                <div class="col-md-5">
                     <a href="#" class="zoom-trigger shadow-sm" data-bs-toggle="modal" data-bs-target="#imageModal">
                        <img src="<?php echo $imgSrc; ?>" 
                             class="img-fluid w-100" 
                             alt="<?php echo htmlspecialchars($product['pName']); ?>">
                     </a>
                     <p class="text-center text-muted small mt-2"><i class="bi bi-zoom-in"></i> 點擊圖片可放大</p>
                </div>
                
                <div class="col-md-7">
                    <span class="badge bg-success mb-2"><i class="bi bi-check-circle"></i> 庫存充足</span>
                    
                    <h2 class="fw-bold"><?php echo htmlspecialchars($product['pName']); ?></h2>
                    
                    <h3 class="text-danger mt-3 fw-bold">$100 <span class="fs-6 text-muted fw-normal">(店面價)</span></h3>
                    
                    <div class="alert alert-light border mt-4">
                        <h5 class="fw-bold"><i class="bi bi-geo-alt-fill text-danger"></i> 取貨地點</h5>
                        <p class="mb-1"><strong>分店：</strong><?php echo htmlspecialchars($product['storeName']); ?></p>
                        <p class="mb-0"><strong>地址：</strong><?php echo htmlspecialchars($product['address']); ?></p>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="reserve_product.php?id=<?php echo $product['pID']; ?>" class="btn btn-dark btn-lg">
                            <i class="bi bi-bag-check"></i> 我要預約 (到店取貨付款)
                        </a>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">
                            ← 返回上一頁
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center position-relative">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" 
                            data-bs-dismiss="modal" aria-label="Close"
                            style="z-index: 10; background-color: rgba(0,0,0,0.5);"></button>
                    
                    <img src="<?php echo $imgSrc; ?>" class="img-fluid rounded shadow-lg" style="max-height: 85vh;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>