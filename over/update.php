<?php
include "sql.php";
session_start();
$z="";
if(!isset($_SESSION['name'])){
    header("Location: login.php");
    exit();
}
$name=$_SESSION['name'];
$z = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = text_insde($_POST["password"]);
    $password2 = text_insde($_POST["password2"]);
    $sql = "SELECT * FROM users WHERE name=:name;";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(":name" => $name));
    $result = $stmt->fetch();
    if (empty($password)||empty($password2)){
        $z = "密码不能为空";
    }elseif ($password !== $password2) {
        $z = "密码不一致";
    } else {
        $new_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password=:new_password WHERE name=:name;";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(":new_password" => $new_password, ":name" => $name));
        if ($stmt->rowCount() > 0) {
            echo "<script>alert('修改成功,请重新登录');window.location.href='login.php'</script>";
        } else {
            $z = "修改失败";
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
    <label>用户名:<?php echo $name;?></label>
    <label>新密码：</label>
    <input type="password" name="password"><br>
    <label>确认密码：</label>
    <input type="password" name="password2"><br>
    <input type="submit" value="修改密码">
</form>
<input type="button" value="返回" onclick="window.history.back()">
</body>
</html>
