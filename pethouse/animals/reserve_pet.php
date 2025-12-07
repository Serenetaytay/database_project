<?php include '../db_connect.php';
$pet_id = $_GET['pet_id'];
if (isset($_POST['submit'])) {
    $conn->query("INSERT INTO RESERVE (petID, rName, rPhone, time, status) VALUES ('$pet_id', '{$_POST['rName']}', '{$_POST['rPhone']}', '{$_POST['time']}', '申請購買')");
    echo "<script>alert('已申請購買！'); location.href='index.php';</script>";
}
?>
<!DOCTYPE html>
<html>
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
    <?php include '../nav_client.php'; ?>
    <div class="container mt-5" style="max-width:500px">
        <div class="card p-4 shadow-sm">
            <h3>  申請購買</h3>
            <form method="post">
                <div class="mb-3"><label>姓名</label><input type="text" name="rName" class="form-control" required></div>
                <div class="mb-3"><label>電話</label><input type="text" name="rPhone" class="form-control" required></div>
                <div class="mb-3"><label>取寵物時間</label><input type="datetime-local" name="time" class="form-control" required></div>
                <button type="submit" name="submit" class="btn btn-danger w-100">送出申請</button>
            </form>
        </div>
    </div>
</body>
</html>