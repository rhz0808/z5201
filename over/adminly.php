<?php
include "sql.php";
session_start();
$z="";
if(!isset($_SESSION['name'])||$_SESSION['name']!=="admin"){
    header("Location: login.php");
    exit();
}
$name=$_SESSION['name'];
if (isset($_GET['delete'])){
    $delete = intval($_GET['delete']);
    $sql = "DELETE FROM students WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id"=>$delete]);
    if ($stmt->rowCount() > 0){
        $z= "删除成功";
    }else{
        $z= "删除失败";
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $content = $_POST['content'];
    if (empty($content)){
        $z = "请填写内容";
    }else{
        $sql = "INSERT INTO students (name, content) VALUES (:name, :content)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["name"=>$name, "content"=>$content]);
        if ($stmt->rowCount() > 0){
            $z = "留言成功";
        }else{
            $z = "留言失败";
        }
    }
}
$sql = "SELECT * FROM students";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$row = $stmt->fetchAll();
?>
<html lang="cn">
<head>
    <meta charset="utf-8">
    <title>留言管理</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div>
    <form action="" method="post">
        <textarea name="content" rows="5" cols="50"></textarea>
        <input type="submit" value="留言">
    </form>
</div><br>
<p><?php if (!empty($z)){
    echo $z;
    }?></p>
<input type="button" value="返回" onclick="window.location.href='admin.php'">
<h2>留言列表</h2>
<div class="lyk">
    <?php  if (count($row)>0) :?>
        <?php
        foreach ($row as $value){
            echo "<div class='ly'>" ."<p style='color: #e33ff4'>".$value['name'].": ".$value['content']."<br>";
            echo "时间：".$value['time'];
            echo " <a onclick='return confirm(\"确定要删除吗？\")' style='float: right;' href='?delete=".$value['id']."'>删除</a></p></div>";
        }
        ?>

    <?php else: ?>
        <p>暂无留言</p>
    <?php endif;?>
</div>
</body>
</html>
