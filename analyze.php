<?php
$file = "hit_rotation.csv";
$rows = [];

if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $hits = str_getcsv($line);
        $rows[] = $hits;
    }
}

// ---------- 全体集計 ----------
$rangeSize = 50;
$rangeCounts = [];
$totalCount = 0;

foreach ($rows as $row) {
    foreach ($row as $hit) {
        $hit = (int)$hit;
        $totalCount++;
        $bucket = floor($hit / $rangeSize);
        if (!isset($rangeCounts[$bucket])) $rangeCounts[$bucket] = 0;
        $rangeCounts[$bucket]++;
    }
}

ksort($rangeCounts);

$rangePercentages = [];
foreach ($rangeCounts as $bucket => $count) {
    $rangeStart = $bucket * $rangeSize;
    $rangeEnd = ($bucket + 1) * $rangeSize - 1;
    $label = $rangeStart . '〜' . $rangeEnd;
    $percent = round(($count / $totalCount) * 100, 2);
    $rangePercentages[] = [
        'range' => $label,
        'count' => $count,
        'percent' => $percent
    ];
}

// ---------- 100以下が出る前の数値集計 ＋ ハイライト準備 ----------
$beforeThresholdCounts = [];
$beforeThresholdTotal = 0;
$threshold = 100;
$highlightMap = []; // 行番号 => [ハイライトする列番号の配列]

foreach ($rows as $rowIndex => $row) {
    foreach ($row as $i => $hit) {
        $hit = (int)$hit;
        if ($hit <= $threshold && $i > 0) {
            $prevHit = (int)$row[$i - 1];
            $bucket = floor($prevHit / $rangeSize);
            if (!isset($beforeThresholdCounts[$bucket])) {
                $beforeThresholdCounts[$bucket] = 0;
            }
            $beforeThresholdCounts[$bucket]++;
            $beforeThresholdTotal++;

            if (!isset($highlightMap[$rowIndex])) {
                $highlightMap[$rowIndex] = [];
            }
            $highlightMap[$rowIndex][] = $i - 1;
        }
    }
}

ksort($beforeThresholdCounts);

$beforeThresholdPercentages = [];
foreach ($beforeThresholdCounts as $bucket => $count) {
    $rangeStart = $bucket * $rangeSize;
    $rangeEnd = ($bucket + 1) * $rangeSize - 1;
    $label = $rangeStart . '〜' . $rangeEnd;
    $percent = round(($count / $beforeThresholdTotal) * 100, 2);
    $beforeThresholdPercentages[] = [
        'range' => $label,
        'count' => $count,
        'percent' => $percent
    ];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>解析結果</title>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load('current', {packages:['corechart']});
google.charts.setOnLoadCallback(drawCharts);

function drawCharts() {
    drawPieChart(
        <?php echo json_encode($rangePercentages); ?>,
        '大当たりが出る回転数の割合（円グラフ）',
        'chart_div'
    );

    drawPieChart(
        <?php echo json_encode($beforeThresholdPercentages); ?>,
        '100回転以内で当たる直前の回転数の割合（円グラフ）',
        'before_threshold_chart_div'
    );
}

function drawPieChart(rangeData, title, elementId) {
    var data = new google.visualization.DataTable();
    data.addColumn('string', '回転数範囲');
    data.addColumn('number', '件数');

    rangeData.forEach(function(row) {
        data.addRow([row.range, row.count]);
    });

    var options = {
        title: title,
        pieHole: 0.4,
        width: '100%',
        height: 400,
        legend: { position: 'right' }
    };

    var chart = new google.visualization.PieChart(document.getElementById(elementId));
    chart.draw(data, options);
}

window.addEventListener('resize', drawCharts);
</script>
<style>
body { font-family: Arial; padding:10px; text-align:center; }
h2 { font-size:20px; }
table { margin:auto; border-collapse: collapse; margin-bottom:40px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
a { display:inline-block; margin-top:20px; padding:10px 20px; background-color:#007BFF; color:white; text-decoration:none; border-radius:8px; }
a:hover { background-color:#0056b3; }
.highlight { background-color: #FFCCCC; font-weight: bold; }
</style>
</head>
<body>

<?php if (!empty($rows)): ?>

<h2>大当たり回転数の割合</h2>
<div id="chart_div" style="width:100%; height:400px;"></div>
<table>
    <tr>
        <th>回転数範囲</th>
        <th>件数</th>
        <th>割合 (%)</th>
    </tr>
    <?php foreach ($rangePercentages as $row): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['range']); ?></td>
        <td><?php echo $row['count']; ?></td>
        <td><?php echo $row['percent']; ?>%</td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>100回転以内で当たる直前の回転数の割合</h2>
<div id="before_threshold_chart_div" style="width:100%; height:400px;"></div>
<table>
    <tr>
        <th>回転数範囲</th>
        <th>件数</th>
        <th>割合 (%)</th>
    </tr>
    <?php foreach ($beforeThresholdPercentages as $row): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['range']); ?></td>
        <td><?php echo $row['count']; ?></td>
        <td><?php echo $row['percent']; ?>%</td>
    </tr>
    <?php endforeach; ?>
</table>

<h2>生データ（100回転以内で当たる直前の数値を赤で表示）</h2>
<table>
    <tr>
        <?php for ($i = 1; $i <= max(array_map('count', $rows)); $i++): ?>
            <th>回<?php echo $i; ?></th>
        <?php endfor; ?>
    </tr>
    <?php foreach ($rows as $rowIndex => $row): ?>
    <tr>
        <?php foreach ($row as $i => $hit): ?>
            <?php
                $class = (isset($highlightMap[$rowIndex]) && in_array($i, $highlightMap[$rowIndex])) ? 'highlight' : '';
            ?>
            <td class="<?php echo $class; ?>"><?php echo htmlspecialchars($hit); ?></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</table>

<?php else: ?>
<p>データがありません。</p>
<?php endif; ?>

<p><a href="list.php">元のグラフに戻る</a></p>
</body>
</html>
