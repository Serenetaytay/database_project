<?php include '../db_connect.php'; $id = $_GET['id'];
$pet = $conn->query("SELECT PET.*, BREED.bName, STORE.storeName FROM PET JOIN BREED ON PET.bID=BREED.bID JOIN STORE ON PET.storeID=STORE.storeID WHERE petID=$id")->fetch_assoc(); ?>
<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
    <?php include '../nav_client.php'; ?>
    <div class="container mt-5"><div class="card p-4 border-0 shadow">
        <div class="row">
            <div class="col-md-6"><img src="<?php echo $pet['petImage'] ? '../../'.$pet['petImage'] : 'https://via.placeholder.com/500'; ?>" class="img-fluid rounded"></div>
            <div class="col-md-6">
                <h2><?php echo $pet['bName']; ?></h2><h3 class="text-danger">$<?php echo $pet['petprice']; ?></h3>
                <ul class="list-unstyled mt-3"><li> 分店：<?php echo $pet['storeName']; ?></li><li> 生日：<?php echo $pet['birth']; ?></li><li>🚻 性別：<?php echo $pet['sex']; ?></li><li> 個性：<?php echo $pet['personality']; ?></li></ul>
                <hr><a href="reserve_pet.php?pet_id=<?php echo $pet['petID']; ?>" class="btn btn-danger btn-lg w-100"> 預約購買 (帶回家)</a>
            </div>
        </div>
    </div></div>
</body>
</html>