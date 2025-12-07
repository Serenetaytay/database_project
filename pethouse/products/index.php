<?php include '../db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include '../nav_client.php'; ?>
    <div class="container">
        <h3> 商品列表</h3>
        <div class="row">
            <?php
            $res = $conn->query("SELECT * FROM PRODUCT");
            while($row = $res->fetch_assoc()){
                
               
                if (isset($row['pImage']) && !empty($row['pImage'])) {
                   
                    $img = "../" . $row['pImage'];
                } else {
                    $img = "https://via.placeholder.com/300?text=No+Image";
                }
                // --- 修改重點結束 ---
                
                echo "<div class='col-md-3 mb-4'>
                        <div class='card border-0 shadow-sm'>
                            <img src='$img' class='card-img-top' style='height:200px;object-fit:cover;'>
                            <div class='card-body'>
                                <h6>{$row['pName']}</h6>
                                <div class='d-flex justify-content-between'>
                                    <span class='text-muted'>庫存: {$row['stock']}</span>
                                    <a href='product_detail.php?id={$row['pID']}' class='btn btn-sm btn-outline-dark'>查看</a>
                                </div>
                            </div>
                        </div>
                      </div>";
            }
            ?>
        </div>
    </div>
</body>
</html>