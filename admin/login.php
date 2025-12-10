<?php
session_start();

// 如果已經登入了，直接送去首頁，不用再登入
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header("Location: index.php");
    exit();
}

// 處理登入送出的資料
if (isset($_POST['submit'])) {
    $password = $_POST['password'];
    
    // --- 目前密碼是 1234 ---
    if ($password === '1234') {
        $_SESSION['is_admin'] = true; // 在 Session 記住身分
        header("Location: index.php"); // 跳轉回後台首頁
        exit();
    } else {
        $error = "密碼錯誤！請重新輸入。";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>管理員登入</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #212529; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; padding: 30px; background: white; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="login-card shadow-lg text-center">
        <img src="./assets/logo1.png" style="width: 80px; height: 80px; object-fit: cover;" class="rounded-circle border mb-3">
        
        <h3 class="fw-bold mb-4">寵愛後台登入</h3>
        
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="post">
            <div class="mb-3 text-start">
                <label class="form-label fw-bold">管理員密碼</label>
                <input type="password" name="password" class="form-control form-control-lg" placeholder="請輸入密碼" required>
            </div>
            <button type="submit" name="submit" class="btn btn-dark w-100 btn-lg">確認登入</button>
        </form>
        
       
    </div>
</body>
</html>