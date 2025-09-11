<?php
$file = "hit_rotation.csv";

if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!empty($lines)) {
        array_pop($lines); // 最終行を削除
        file_put_contents($file, implode("\n", $lines) . "\n", LOCK_EX);
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>削除完了</title>
<style>
    body { font-family: Arial; padding:20px; text-align:center; }
    h2 { color:#f44336; }
    a { display:inline-block; margin-top:20px; padding:12px 24px; background-color:#007BFF; color:white; text-decoration:none; border-radius:8px; font-size:16px; }
    a:hover { background-color:#0056b3; }
</style>
</head>
<body>
<h2>最終行を削除しました！</h2>
<a href="index.php">戻る</a>
</body>
</html>
