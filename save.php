<?php
$file = "hit_rotation.csv";
$hits = $_POST['hit'] ?? [];

// 入力値をカンマ区切りの1行に
if (!empty($hits)) {
    $line = implode(",", $hits) . "\n";
    file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>保存完了</title>
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        text-align: center;
    }
    h2 {
        color: #4CAF50;
    }
    a {
        display: inline-block;
        margin-top: 20px;
        padding: 12px 24px;
        background-color: #007BFF;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-size: 16px;
    }
    a:hover {
        background-color: #0056b3;
    }
</style>
</head>
<body>
    <h2>保存しました！</h2>
    <a href="index.php">戻る</a>
</body>
</html>
