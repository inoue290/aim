<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>アイムジャグラー当たり回転数記録</title>
<style>
    body { font-family: Arial; padding:10px; }
    h2 { text-align:center; }
    form { max-width: 500px; margin:auto; display:flex; flex-direction:column; }
    label { margin-top:10px; margin-bottom:5px; font-weight:bold; }
    input[type="number"] { padding:10px; font-size:16px; width:100%; box-sizing:border-box; }
    button { margin-top:20px; padding:12px; font-size:16px; border:none; border-radius:8px; cursor:pointer; }
    .save { background-color:#4CAF50; color:white; }
    .delete { background-color:#f44336; color:white; margin-top:10px; }
    button:hover { opacity:0.9; }
    p { text-align:center; margin-top:20px; }
    a { color:#007BFF; text-decoration:none; }
    a:hover { text-decoration:underline; }
</style>
</head>
<body>
<h2>アイムジャグラー当たり回転数入力（10回分まとめて）</h2>

<form action="save.php" method="post">
    <label>ボーナス1回目:</label>
    <input type="number" name="hit[]" required>
    <label>ボーナス2回目:</label>
    <input type="number" name="hit[]" required>
    <label>ボーナス3回目:</label>
    <input type="number" name="hit[]" required>
    <label>ボーナス4回目:</label>
    <input type="number" name="hit[]" required>
    <label>ボーナス5回目:</label>
    <input type="number" name="hit[]" required>
    <label>ボーナス6回目:</label>
    <input type="number" name="hit[]" required>
    <label>ボーナス7回目:</label>
    <input type="number" name="hit[]" required>
    <label>ボーナス8回目:</label>
    <input type="number" name="hit[]" required>
    <label>ボーナス9回目:</label>
    <input type="number" name="hit[]" required>
    <label>ボーナス10回目:</label>
    <input type="number" name="hit[]" required>
    <button type="submit" class="save">保存</button>
</form>

<form action="delete.php" method="post">
    <button type="submit" class="delete">1台のデータ消去</button>
</form>

<p><a href="list.php">台記録を見る</a></p>
</body>
</html>

