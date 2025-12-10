<?php
include '../db_connect.php';

// 1. 檢查是否有傳入 ID
if (!isset($_GET['id'])) {
    echo "<script>alert('無效的參數！'); window.location.href='index.php';</script>"; // 假設列表頁是 index.php
    exit;
}

$id = $_GET['id'];

// 2. 使用 Prepared Statement 防止 SQL Injection
$sql = "SELECT PET.*, BREED.bName, STORE.storeName 
        FROM PET 
        JOIN BREED ON PET.bID = BREED.bID 
        JOIN STORE ON PET.storeID = STORE.storeID 
        WHERE petID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id); // "i" 表示整數
$stmt->execute();
$result = $stmt->get_result();

// 3. 檢查是否找到資料
if ($result->num_rows > 0) {
    $pet = $result->fetch_assoc();
} else {
    echo "<div class='container mt-5 alert alert-danger'>查無此寵物資料。</div>";
    exit;
}
$dbImage = $pet['petImage'];
$imgSrc = !empty($dbImage) ? '../../'.htmlspecialchars($dbImage) : 'https://via.placeholder.com/500?text=No+Image';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pet['bName']); ?> - 詳細資料</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* 設定圖片區塊樣式 */
        .pet-detail-img-container {
            overflow: hidden;
            border-radius: 8px;
        }
        .pet-detail-img {
            max-height: 450px;
            object-fit: cover; /* 保持比例填滿 */
            width: 100%;
            transition: transform 0.3s ease;
        }
        /* 滑鼠移過去時的放大鏡游標與微放大效果 */
        .zoom-trigger:hover .pet-detail-img {
            transform: scale(1.03);
        }
        .zoom-trigger {
            cursor: zoom-in;
            display: block;
        }
    </style>
</head>
<body class="bg-light">
    
    <?php include '../nav_client.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="card border-0 shadow-lg overflow-hidden">
            <div class="row g-0">
                
                <div class="col-md-6 bg-white d-flex align-items-center justify-content-center p-3">
                    <a href="#" class="zoom-trigger w-100" data-bs-toggle="modal" data-bs-target="#imageModal" title="點擊放大圖片">
                        <div class="pet-detail-img-container">
                            <img src="<?php echo $imgSrc; ?>" 
                                 class="img-fluid pet-detail-img" 
                                 alt="<?php echo htmlspecialchars($pet['bName']); ?>">
                        </div>
                    </a>
                </div>
                
                <div class="col-md-6">
                    <div class="card-body p-4 p-md-5 d-flex flex-column h-100">
                        <div class="mb-auto">
                            <h2 class="card-title display-5 fw-bold mb-3"><?php echo htmlspecialchars($pet['bName']); ?></h2>
                            <h3 class="text-danger fw-bold mb-4">$<?php echo number_format($pet['petprice']); ?></h3>
                            
                            <ul class="list-group list-group-flush mb-4">
                                <li class="list-group-item bg-transparent px-0">
                                    <strong class="text-muted">分店位置：</strong> 
                                    <span class="badge bg-info text-dark"><?php echo htmlspecialchars($pet['storeName']); ?></span>
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <strong class="text-muted">生日：</strong> 
                                    <?php echo htmlspecialchars($pet['birth']); ?>
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <strong class="text-muted">性別：</strong> 
                                    <?php echo htmlspecialchars($pet['sex']); ?>
                                </li>
                                <li class="list-group-item bg-transparent px-0">
                                    <strong class="text-muted">個性描述：</strong><br>
                                    <p class="mt-2 text-secondary"><?php echo nl2br(htmlspecialchars($pet['personality'])); ?></p>
                                </li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <a href="reserve_pet.php?pet_id=<?php echo $pet['petID']; ?>" class="btn btn-danger btn-lg shadow-sm">
                                <i class="bi bi-cart-check"></i> 預約服務
                            </a>
                            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                返回上一頁
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0">
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="<?php echo $imgSrc; ?>" class="img-fluid rounded shadow-lg" style="max-height: 85vh;">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>