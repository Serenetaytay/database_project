<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>後台首頁</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    
    <div class="container">
        <div class="row text-center mb-4">
            <h1>營運儀表板 (Dashboard)</h1>
            <p class="text-muted">即時掌握店內狀況</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary h-100">
                    <div class="card-body">
                        <h5 class="card-title">待確認預約</h5>
                        <?php
                        $sql = "SELECT COUNT(*) as cnt FROM RESERVE WHERE status='待確認'";
                        $res = $conn->query($sql)->fetch_assoc();
                        ?>
                        <h2 class="display-4 fw-bold"><?php echo $res['cnt']; ?></h2>
                        <p class="card-text">筆預約等待審核</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success h-100">
                    <div class="card-body">
                        <h5 class="card-title">在店寵物總數</h5>
                        <?php
                        $sql = "SELECT COUNT(*) as cnt FROM PET WHERE status='在店'";
                        $res = $conn->query($sql)->fetch_assoc();
                        ?>
                        <h2 class="display-4 fw-bold"><?php echo $res['cnt']; ?></h2>
                        <p class="card-text">隻寵物等待新家</p>
                    </div>
                </div>
            </div>
            
         <div class="col-md-4">
        <div class="card text-white bg-warning h-100">
        <div class="card-body">
            <h5 class="card-title">低庫存商品</h5>
            <?php
            $sql = "SELECT COUNT(*) as cnt FROM PRODUCT WHERE stock < 10";
            $result = $conn->query($sql);
            
            $low_stock_count = 0;
            
            if ($result && $row = $result->fetch_assoc()) {
                $low_stock_count = $row['cnt'];
            }
            ?>
            <h2 class="display-4 fw-bold text-dark"><?php echo $low_stock_count; ?></h2>
            <p class="card-text text-dark">項商品庫存緊張</p>
        </div>
    </div>
</div>
        </div>
    </div>
</body>
</html>