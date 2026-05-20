<?php
include "sql.php";
require_once "text.php";
session_start();
$x="";
if ($_SERVER["REQUEST_METHOD"]=="POST") {
    $name = text_insde($_POST["name"]);
    $password = text_insde($_POST["password"]);
    $remember = isset($_POST["remember"])?1:0;
    if ($remember){
        setcookie("name",$name,time()+3600*24*7);
        setcookie("password",$password,time()+3600*24*7);
    }else{
        setcookie("name","",time()-3600);
        setcookie("password","",time()-3600);
    }
    if (empty($name)||empty($password)){
        $x="请输入完整";
    }else{
        $sql="SELECT * FROM users WHERE name=:name;";
        $stmt=$pdo->prepare($sql);
        $stmt->execute([":name"=>$name]);
        $row=$stmt->fetch();
        if ($name==="admin"||$name==="root"){
            $_SESSION["name"]="admin";
            header("Location:admin.php");
        }
        elseif(!$row){
            $x="用户不存在!";
        }elseif (!password_verify($password,$row["password"])){
            $x="密码错误!";
        }else{
            $_SESSION["name"]=$row["name"];
            $_SESSION['id']=$row['id'];
            $_SESSION['privilege']=$row['privilege'];
            if ($row['privilege']==="admin"){
                header("Location:adminuser.php");
            }else{
                header("Location:wl.php");
            }
        }

    }
}
?>
<!DOCTYPE html>
<html lang="cn">
<head>
    <meta charset="utf-8">
    <title>登录</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form action="" method="post">
    <label>用户名：</label>
    <input type="text" name="name" value="<?php echo $_COOKIE["name"]?? "";?>"><br>
    <label>密码：</label>
    <input type="password" name="password" value="<?php echo $_COOKIE["password"]?? "";?>"><br>
    <label>记住账号密码<input type="checkbox" name="remember"></label>
    <input type="submit" value="登录"><br>
    <a href="zc.php">没有账号？点此注册</a><br>
    <a href="forget.php">忘记密码？点此找回</a>
</form>

</body>
</html>
