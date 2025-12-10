<?php 
session_start();
// 檢查登入狀態
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php'; 

// --- 圖表資料：平均價格 (Bar Chart) ---
$bar_sql = "SELECT B.bName, ROUND(AVG(P.petprice)) as avg_price 
            FROM PET P 
            JOIN BREED B ON P.bID = B.bID 
            WHERE P.status = '在店' 
            GROUP BY B.bID 
            ORDER BY avg_price DESC";
$bar_res = $conn->query($bar_sql);
$bar_labels = [];
$bar_data = [];
while($row = $bar_res->fetch_assoc()){
    $bar_labels[] = $row['bName'];
    $bar_data[] = $row['avg_price'];
}

// --- 圖表資料：分店商品數量 (Pie Chart) ---
// 查詢每個分店有多少「種」商品 (COUNT pID)
$pie_sql = "SELECT S.storeName, COUNT(P.pID) as p_count 
            FROM STORE S 
            LEFT JOIN PRODUCT P ON S.storeID = P.storeID 
            GROUP BY S.storeID";
$pie_res = $conn->query($pie_sql);
$pie_labels = [];
$pie_data = [];
while($row = $pie_res->fetch_assoc()){
    $pie_labels[] = $row['storeName'];
    $pie_data[] = $row['p_count'];
}

// 轉成 JSON 給 JS 用
$json_bar_labels = json_encode($bar_labels);
$json_bar_data   = json_encode($bar_data);
$json_pie_labels = json_encode($pie_labels);
$json_pie_data   = json_encode($pie_data);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>後台營運儀表板</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    
    <div class="container py-4">
        <div class="row text-center mb-5">
            <h1 class="fw-bold"> 營運儀表板</h1>
            <p class="text-muted">即時掌握店內狀況</p>
        </div>

        <h5 class="text-secondary border-bottom pb-2 mb-3">數量監控</h5>
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card text-white bg-primary h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-clipboard-list me-2"></i>待確認預約</h5>
                        <?php
                        $sql = "SELECT COUNT(*) as cnt FROM RESERVE WHERE status='待確認' OR status='申請購買'";
                        $res = $conn->query($sql)->fetch_assoc();
                        ?>
                        <h2 class="display-4 fw-bold"><?php echo $res['cnt']; ?></h2>
                        <p class="card-text">筆申請等待審核</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-paw me-2"></i>在店寵物總數</h5>
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
                <div class="card text-dark bg-warning h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>低庫存商品</h5>
                        <?php
                        $sql = "SELECT COUNT(*) as cnt FROM PRODUCT WHERE stock < 10";
                        $result = $conn->query($sql);
                        $low_stock_count = ($result && $row = $result->fetch_assoc()) ? $row['cnt'] : 0;
                        ?>
                        <h2 class="display-4 fw-bold text-dark"><?php echo $low_stock_count; ?></h2>
                        <p class="card-text text-dark">項商品庫存緊張</p>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="text-secondary border-bottom pb-2 mb-3">價值分析</h5>
        <div class="row g-4 mb-5">
            <div class="col-md-8">
                <div class="card border-info border-2 h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="text-info">
                                <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i>寵物平均售價分析</h5>
                                <?php
                                $sql = "SELECT ROUND(AVG(petprice)) as avg_price FROM PET WHERE status='在店'";
                                $res = $conn->query($sql)->fetch_assoc();
                                $avg = $res['avg_price'] ? $res['avg_price'] : 0;
                                ?>
                                <h3 class="fw-bold">$<?php echo number_format($avg); ?> <small class="text-muted fs-6">(總平均)</small></h3>
                            </div>
                        </div>
                        <div style="height: 250px;">
                            <canvas id="avgPriceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-primary border-2 h-100 shadow-sm">
                    <div class="card-body text-primary d-flex flex-column justify-content-center text-center">
                        <h5 class="card-title mb-4"><i class="fas fa-crown me-2"></i>鎮店之寶</h5>
                        <?php
                        $sql_max = "SELECT MAX(petprice) as max_price FROM PET WHERE status='在店'";
                        $query_max = $conn->query($sql_max);
                        $max_price = 0;
                        if ($query_max && $query_max->num_rows > 0) {
                            $res_max = $query_max->fetch_assoc();
                            if ($res_max['max_price'] !== null) {
                                $max_price = $res_max['max_price'];
                            }
                        }
                        
                        $info_html = "目前無在店寵物";

                        if ($max_price > 0) {
                            // 2. 再找出所有「價格等於最高價」的寵物
                            $sql_list = "SELECT P.petID, P.storeID, B.bName 
                                         FROM PET P 
                                         LEFT JOIN BREED B ON P.bID = B.bID 
                                         WHERE P.status='在店' AND P.petprice = $max_price";
                            $res_list = $conn->query($sql_list);
                            
                            $pets = [];
                            if ($res_list && $res_list->num_rows > 0) {
                                while ($row = $res_list->fetch_assoc()) {
                                    // 格式化 ID：分店ID - 補零後的寵物ID
                                    $visualID = $row['storeID'] . "-" . str_pad($row['petID'], 3, '0', STR_PAD_LEFT);
                                    
                                    // 顯示格式： 1-004 德文貓
                                    $pets[] = "<span class='badge bg-primary text-white me-1'>{$visualID}</span>{$row['bName']}";
                                }
                                // 用 <div class='mb-1'> 換行
                                $info_html = implode("<div class='mb-1'></div>", $pets);
                            }
                        }
                        ?>
                        <h1 class="display-3 fw-bold mb-3">$<?php echo number_format($max_price); ?></h1>
                        
                        <div class="text-muted fw-bold" style="max-height: 120px; overflow-y: auto;">
                            <?php echo $info_html; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="text-secondary border-bottom pb-2 mb-3">分店商品概況 </h5>
        <div class="row g-4">
            
            <div class="col-md-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white fw-bold">
                        <i class="fas fa-chart-pie me-2 text-warning"></i>各分店商品種類佔比
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div style="width: 100%; max-width: 300px;">
                            <canvas id="storeProductPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white fw-bold">
                        <i class="fas fa-list me-2 text-secondary"></i>詳細商品清單
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th width="25%">分店名稱</th>
                                    <th>販售商品</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT S.storeName, GROUP_CONCAT(P.pName SEPARATOR '、') as products 
                                        FROM STORE S 
                                        LEFT JOIN PRODUCT P ON S.storeID = P.storeID 
                                        GROUP BY S.storeID";
                                $res = $conn->query($sql);
                                
                                if($res->num_rows > 0) {
                                    while($row = $res->fetch_assoc()){
                                        $pList = $row['products'] ? $row['products'] : "<span class='text-muted'>無商品</span>";
                                        echo "<tr>
                                                <td class='fw-bold'>{$row['storeName']}</td>
                                                <td>{$pList}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='2' class='text-center'>無資料</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>

    <script>
        // --- 圖表 1: 長條圖 (平均價格) ---
        const barLabels = <?php echo $json_bar_labels; ?>;
        const barData = <?php echo $json_bar_data; ?>;

        const ctxBar = document.getElementById('avgPriceChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [{
                    label: '平均價格',
                    data: barData,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // --- 圖表 2: 圓餅圖 (分店商品數量) ---
        const pieLabels = <?php echo $json_pie_labels; ?>;
        const pieData = <?php echo $json_pie_data; ?>;

        const ctxPie = document.getElementById('storeProductPieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie', // 指定為圓餅圖
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieData,
                    // 設定分店的顏色 (如果不夠會自動循環)
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)', // 紅
                        'rgba(255, 205, 86, 0.7)', // 黃
                        'rgba(75, 192, 192, 0.7)', // 綠
                        'rgba(54, 162, 235, 0.7)', // 藍
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom', 
                    }
                }
            }
        });
    </script>
</body>
</html>