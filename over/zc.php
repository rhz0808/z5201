<?php
include "sql.php";
require_once "text.php";
$z="";
if($_SERVER["REQUEST_METHOD"]=="POST"){
    $name=text_insde($_POST["name"]);
    $pwd=text_insde($_POST["pwd"]);
    $age=text_insde($_POST["age"]);
    $email=text_insde($_POST["email"]);
    $sql="SELECT * FROM users WHERE name=:name OR email=:email;";
    $stmt_find=$pdo->prepare($sql);
    $stmt_find->execute(array(":name"=>$name,":email"=>$email));
    if (empty($name)||empty($pwd)||empty($age)||empty($email)){
        $z= "请填写完整信息";
    }elseif($name=="admin") {
        $z = "用户名不能为 admin！";
    }elseif($stmt_find->rowCount()>0){
        $z= "用户名或邮箱已存在！";
    }elseif($age<0||$age>120){
        $z= "年龄不合法！";
    }elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){
        $z= "邮箱格式错误！";
    }else{
        $password=password_hash($pwd,PASSWORD_DEFAULT);
        $sql="INSERT INTO users (name,password,age,email) VALUES (:name,:password,:age,:email);";
        $stmt=$pdo->prepare($sql);
        $result=$stmt->execute(array(":name"=>$name,":password"=>$password,":age"=>$age,":email"=>$email));
        if($result){
            $z="注册成功";
            echo "<script> alert('注册成功，请返回登录');</script>";
        }else{
            $error=$stmt->errorInfo();
            $z="注册失败".$error[2];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cn">
<head>
    <meta charset="UTF-8">
    <title>注册</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<form action="zc.php" method="post">
    <label>用户名</label>
    <input type="text" name="name"><br>
    <label>密码</label>
    <input type="password" name="pwd"><br>
    <label>年龄</label>
    <input type="text" name="age"><br>
    <label>email</label>
    <input type="text" name="email"><br>
    <input type="submit" value="注册">
</form>
<?php
if(!empty($z)){
    echo "<p style='color: red'>".$z."</p>";
}
?>
<input type="button" value="返回" onclick="window.location.href='login.php'">
</body>
</html>
