<?php

include '../db_connect.php';
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>店家資訊 - 寵愛 PetShop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <?php include '../nav_client.php'; ?>

    <div class="container py-5">
        <div class="mb-5">
            <h2 class="fw-bold">店家資訊</h2>
            <p class="text-muted">找到離您最近的門市服務據點。</p>
        </div>

        <div class="row">
            <div class="col-lg-10 mx-auto"> <?php
                // 查詢資料庫中的 STORE 表格
                $sql = "SELECT * FROM STORE";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        // 圖片路徑處理：
                        // 如果資料庫有圖片，路徑前面加 ../ (因為圖片通常存在 uploads/)
                        // 如果沒圖片，顯示灰色的預設圖
                        $img = !empty($row['storeImage']) ? "../".$row['storeImage'] : "https://via.placeholder.com/400x300?text=No+Image";
                        
                        echo '
                        <div class="card mb-4 border-0 shadow-sm overflow-hidden">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <img src="'.$img.'" class="img-fluid h-100 w-100" style="object-fit: cover; min-height: 250px;" alt="'.$row['storeName'].'">
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="card-body h-100 d-flex flex-column justify-content-center p-4">
                                        <h3 class="card-title fw-bold mb-3">'.$row['storeName'].'</h3>
                                        
                                        <p class="card-text mb-2 fs-5">
                                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                            <span class="text-muted">地址：</span>'.$row['address'].'
                                        </p>
                                        
                                        <p class="card-text mb-2 fs-5">
                                            <i class="bi bi-telephone-fill text-success me-2"></i>
                                            <span class="text-muted">電話：</span>'.$row['Phone'].'
                                        </p>
                                        
                                        <p class="card-text text-muted mt-2">
                                            <small><i class="bi bi-clock me-1"></i> 營業時間：'.$row['worktime'].'</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<div class="alert alert-warning text-center">目前沒有任何店家資訊。</div>';
                }
                ?>

            </div>
        </div>
    </div>

    <div class="container-fluid bg-white py-4 mt-5 border-top">
        <div class="container text-center text-muted">
            <small>版權所有 © 2025 寵愛寵物之家 | 聯絡我們 | 隨時關注我們的社群媒體</small>
        </div>
    </div>

</body>
</html>