<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>寵愛 PetShop - 首頁</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-white">

    <?php include 'nav_client.php'; ?>

    <div class="container py-4">
        <div class="p-5 mb-4 bg-light rounded-3 text-center">
            <div class="container-fluid py-5">
                <h1 class="display-5 fw-bold text-black">這一刻的凝視，是承諾一輩子的守護！</h1>
                <p class="col-md-8 fs-4 mx-auto text-secondary">每一隻毛孩，都是我們捧在手心的寶貝，只為遇見懂愛的你。</p>
                <a href="animals/index.php" class="btn btn-dark btn-lg px-5 mt-3">開始預約</a>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <h3 class="mb-4 fw-bold">店面相片</h3>
        
        <div class="row g-4">
            <?php
            // 改為查詢 STORE 表格
            $sql = "SELECT * FROM STORE";
            $res = $conn->query($sql);

            if ($res->num_rows > 0) {
                while($row = $res->fetch_assoc()){
                    // 圖片處理 (若無圖片則顯示灰色佔位圖)
                    $img = !empty($row['storeImage']) ? $row['storeImage'] : "https://via.placeholder.com/400x300/e0e0e0/808080?text=Store";
                    
                    echo "
                    <div class='col-md-3 col-sm-6'>
                        <div class='card h-100 border-0 shadow-sm bg-light'>
                            <img src='$img' class='card-img-top' style='height: 200px; object-fit: cover;' alt='{$row['storeName']}'>
                            
                            <div class='card-body'>
                                <h5 class='card-title fw-bold'>{$row['storeName']}</h5>
                                <p class='card-text text-muted small'>
                                     {$row['address']}<br>
                                     {$row['worktime']}
                                </p>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "<div class='col-12 text-center text-muted py-5'>目前沒有店家資料</div>";
            }
            ?>
        </div>
    </div>

    <div class="container mb-5">
        <div class="p-4 bg-light rounded text-muted">
            <small class="fw-bold">鏈接 / FB 社群</small>
        </div>
    </div>

</body>
</html>