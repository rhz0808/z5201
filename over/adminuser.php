<?php
include "sql.php";
require_once "text.php";
session_start();
$name=$_SESSION['name'];
$z="";
if(!isset($_SESSION['name'])||$_SESSION['privilege']!=="admin"){
    header("Location: login.php");
    exit();
}
if (isset($_GET['delete'])){
    $sql = "DELETE FROM users WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id"=>$_GET['delete']]);
    if ($stmt->rowCount() > 0){
        $z= "删除成功";
    }else{
        $z= "删除失败";
    }
}
$sql = "SELECT id,name,email,privilege FROM users ORDER BY id DESC ;";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();
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
$uploaded_images = [];
$upload_dir = 'uploads';
if (is_dir($upload_dir)) {
    $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $files = scandir($upload_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $file_path = $upload_dir ."/". $file;
            if (is_file($file_path)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $allowed_image_extensions)) {
                    $uploaded_images[] = [
                            'name' => $file,
                            'path' => $file_path,
                            'size' => filesize($file_path),
                            'time' => date('Y-m-d H:i:s', filemtime($file_path))
                    ];
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cn">
<head>
    <meta charset="UTF-8">
    <title>用户管理</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<p style="color: #e33ff4"><?php echo "欢迎管理员".$name;?></p>
<div>
    <h2>用户管理</h2>
    <?php if (isset($z)): ?>
        <p style="color: red"><?php echo $z; ?></p>
    <?php endif; ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
        <tr>
            <th>ID</th>
            <th>用户名</th>
            <th>邮箱</th>
            <th>权限</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($users)>0):?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo $user['name']; ?></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><?php echo $user['privilege']; ?></td>
                    <td>
                        <?php if($user['privilege']=='user'):?>
                        <a href="?delete=<?php echo intval($user['id']); ?>" onclick="return confirm('确定要删除该用户吗')">删除</a>
                        <?php endif;?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">暂无数据</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<h2>admin图片</h2>
<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
    <?php if (count($uploaded_images)>0):?>

        <?php foreach ($uploaded_images as $file): ?>
            <div style="border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                <img src="<?php echo text_insde($file['path']); ?>"
                     alt="<?php echo text_insde($file['name']); ?>"
                     style="display: block; width: 100%; height: 150px; object-fit: cover; margin-bottom: 8px;">
                <p >上传时间<?php echo $file['time']; ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>暂无图片</p>
    <?php endif; ?>

</div>
<input type="button" value="查看留言" onclick="window.location.href='adminuserly.php'">
<input type="button" value="修改密码" onclick="window.location.href='update.php'">
<input type="button" value="返回登录" onclick="window.location.href='?logout=1'">
</body>
</html>

