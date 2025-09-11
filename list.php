<?php
$file = "hit_rotation.csv";
$rows = [];

// CSV読み込み
if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $rows[] = str_getcsv($line);
    }
}

// JavaScript用に JSON に変換
$jsonRows = json_encode($rows);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>当たり回転数グラフ</title>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load('current', {packages:['corechart']});
google.charts.setOnLoadCallback(drawChart);

function drawChart() {
    var rows = <?php echo $jsonRows; ?>; 
    if (rows.length === 0) return;

    var data = new google.visualization.DataTable();
    data.addColumn('number', '回');

    // 列名を行ごとに追加
    for (var i = 0; i < rows.length; i++) {
        data.addColumn('number', '行' + (i+1));
    }

    // データ作成（X軸 = 1～10）
    var chartData = [];
    for (var x = 0; x < 10; x++) {
        var row = [x + 1];
        for (var i = 0; i < rows.length; i++) {
            row.push(rows[i][x] ? parseInt(rows[i][x]) : null);
        }
        chartData.push(row);
    }

    data.addRows(chartData);

    var options = {
        title: '当たり回転数の重ね合わせ',
        curveType: 'function',
        legend: { position: 'bottom' },
        pointSize: 5,
        width: '100%',
        height: 400
    };

    var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
    chart.draw(data, options);
}

// 画面リサイズ時に再描画
window.addEventListener('resize', drawChart);
</script>
<style>
body {
    font-family: Arial, sans-serif;
    padding: 10px;
    text-align: center;
}
h2 {
    font-size: 20px;
}
</style>
</head>
<body>
<h2>当たり回転数の重ね合わせ</h2>
<div id="curve_chart" style="width:100%; height:400px;"></div>
<p><a href="index.php" style="font-size:16px; color:#007BFF; text-decoration:none;">入力画面に戻る</a></p>
<form action="analyze.php" method="post" style="text-align:center; margin-top:20px;">
    <button type="submit" 
        style="padding:12px 24px; font-size:16px; border:none; border-radius:8px; background-color:#FFA500; color:white; cursor:pointer;">
        大当たり解析
    </button>
</form>
</body>
</html>


