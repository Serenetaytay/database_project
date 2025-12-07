<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "petstore";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("連線失敗: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>