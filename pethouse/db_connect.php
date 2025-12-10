<?php
$servername = "localhost";
$username = "root";  // XAMPP 預設帳號
$password = "";      // XAMPP 預設密碼通常為空
$dbname = "petstore"; // 資料庫名稱


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>