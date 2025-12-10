<?php include '../db_connect.php'; ?>
<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
    <?php include '../nav_client.php'; ?>
    <div class="container">
        <h3 class="fw-bold">所有寵物</h3>
        <div class="row">
            <?php
            $res = $conn->query("SELECT PET.*, BREED.bName, STORE.storeName FROM PET JOIN BREED ON PET.bID=BREED.bID JOIN STORE ON PET.storeID=STORE.storeID WHERE status='在店'");
            while($row = $res->fetch_assoc()){
                $filename = basename($row['petImage']);
                $img = !empty($filename) ? "../../uploads/" . $filename : "https://via.placeholder.com/300";
                $link = "animal_detail.php?id={$row['petID']}";
                echo "
                <div class='col-md-3 mb-4'>
                    <div class='card border-0 shadow-sm'>
                        <img src='$img' class='card-img-top' style='height:200px; object-fit:cover;' alt='Pet'>
                        <div class='card-body'>
                            <h5>{$row['bName']}</h5>
                            <p class='text-muted small'>{$row['storeName']}</p>
                            <p class='text-danger fw-bold'>\${$row['petprice']}</p>
                            <a href='$link' class='btn btn-dark w-100 btn-sm'>查看詳情</a>
                        </div>
                    </div>
                </div>";
            }
            ?>
        </div>
    </div>
</body>
</html>