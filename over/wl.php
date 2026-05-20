<?php
include "sql.php";
session_start();
$z="";
if(!isset($_SESSION['name'])){
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
if (isset($_GET['logout'])){
    session_destroy();
    session_unset();
    if (ini_get("session.use_cookies")){
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }
    session_regenerate_id( true);
    header("Location: login.php");
    exit();
}
?>
<html lang="cn">
<head>
    <meta charset="utf-8">
    <title>留言管理</title>
</head>
<body>
<div>
    <form action="" method="post">
        <textarea name="content" rows="5" cols="50"></textarea>
        <input type="submit" value="留言">
        <link rel="stylesheet" href="style.css">
    </form>
</div><br>
<p><?php if (!empty($z)){
        echo $z;
    }?></p>
<h2>留言列表</h2>
<div style='padding: 10px;margin: 2px;background: #fdebeb;border-radius: 6px'>
    <?php  if (count($row)>0) :?>
        <?php
        foreach ($row as $value){
            $user=$value['name'];
            echo "<div style='
    padding: 6px 9px;
    margin-bottom: 15px;
    border: 2px solid #ffbfbf;
    border-radius: 5px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #ffffff'>" ."<p style='color: #e33ff4'>".$value['name'].": ".$value['content']."<br>";
            echo "时间：".$value['time'];
            if ($value['name']===$name){
                echo " <a onclick='return confirm(\"确定要删除吗？\")' style='float: right;' href='?delete=".$value['id']."'>删除</a></p>";
            }
            echo "</div>";
        }
        ?>

    <?php else: ?>
        <p>暂无留言</p>
    <?php endif;?>
</div>
<input type="button" value="修改密码" onclick="window.location.href='update.php'">
<input type="button" value="退出登录" onclick="window.location.href='?logout=1'">
</body>
</html>


