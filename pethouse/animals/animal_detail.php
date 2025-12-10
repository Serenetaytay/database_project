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
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pet['bName']); ?> - 詳細資料</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .pet-detail-img {
            max-height: 400px;
            object-fit: cover;
            width: 100%;
        }
    </style>
</head>
<body class="bg-light">
    
    <?php include '../nav_client.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="card border-0 shadow-lg overflow-hidden">
            <div class="row g-0">
                <div class="col-md-6">
                    <img src="<?php echo !empty($pet['petImage']) ? '../../'.htmlspecialchars($pet['petImage']) : 'https://via.placeholder.com/500?text=No+Image'; ?>" 
                         class="img-fluid pet-detail-img" 
                         alt="<?php echo htmlspecialchars($pet['bName']); ?>">
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
                                <i class="bi bi-cart-check"></i> 預約購買 (帶回家)
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>