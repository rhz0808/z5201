<?php
include "sql.php";
require_once "text.php";
$z="";
if ($_SERVER["REQUEST_METHOD"]=="POST"){
    $name=text_insde($_POST["name"]);
    $email=text_insde($_POST["email"]);
    $password=text_insde($_POST["password"]);
    $password2=text_insde($_POST["password2"]);
    $sql="SELECT * FROM users WHERE name=:name;";
    $stmt=$pdo->prepare($sql);
    $stmt->execute(array(":name"=>$name));
    $result=$stmt->fetch();
    if(!$result){
        $z="用户名不存在";
    }elseif($result["email"]!==$email){
        $z="邮箱错误";
    }elseif(empty($password)||empty($password2)){
        $z="密码不能为空";
    } elseif($password!==$password2){
        $z="密码不一致";
    }else{
        $new_password=password_hash($password,PASSWORD_DEFAULT);
        $sql="UPDATE users SET password=:new_password WHERE name=:name;";
        $stmt=$pdo->prepare($sql);
        $stmt->execute(array(":new_password"=>$new_password,":name"=>$name));
        if ($stmt->rowCount()>0){
            $z="修改成功";
            echo "<script>alert('$z')</script>";
        }else{
            $z="修改失败";
        }

    }
}
?>
<!DOCTYPE html>
<html lang="cn">
<head>
    <meta charset="utf-8">
    <title>忘记密码</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form action="forget.php" method="post">
    <label>用户名：</label>
    <input type="text" name="name"><br>
    <label>邮箱：</label>
    <input type="text" name="email"><br>
    <label>新密码：</label>
    <input type="password" name="password"><br>
    <label>确认密码：</label>
    <input type="password" name="password2"><br>
    <input type="submit" value="修改密码">
</form>
<input type="button" value="返回登录" onclick="window.location.href='login.php';">
</body>
</html>
