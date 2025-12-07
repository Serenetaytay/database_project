<?php
session_start();
session_destroy(); // 毀滅 Session (登出)
header("Location: login.php"); // 踢回登入頁
exit();
?>