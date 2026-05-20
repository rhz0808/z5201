<?php
include "sql.php";
require_once "text.php";
session_start();
$z="";
if(!isset($_SESSION['name'])||$_SESSION['name']!=="admin"){
    header("Location: login.php");
    exit();
}


$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$new_privilege = $_POST['new_privilege']??'';
if (in_array($new_privilege,["admin","user"])){
    $sql = "UPDATE users SET privilege = :new_privilege WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["new_privilege"=>$new_privilege,"id"=>$user_id]);
    if ($stmt->rowCount() > 0){
        $z= "修改成功";
    }else{
        $z= "修改失败或无变更";
    }
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

if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_FILES['files'])){
    $files = $_FILES['files'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'webp'];
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'image/webp'];
    foreach ($files['name'] as $index=>$fileName){
        $fileName = $files['name'][$index];
        $fileType = $files['type'][$index];
        $fileTmpName = $files['tmp_name'][$index];
        $fileError = $files['error'][$index];
        if ($fileError==UPLOAD_ERR_OK){
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)){
                mkdir($upload_dir, 0755, true);
            }
            $extension = strtolower(pathinfo($fileName,PATHINFO_EXTENSION));

            if(!in_array($extension,$allowed_extensions)){
                $z = "文件格式错误";
                continue;
            }
            $mime_type = mime_content_type($fileTmpName);
            if (!in_array($mime_type,$allowed_mime_types)){
                $z = "文件格式错误";
                continue;
            }
            if ($files['size'][$index]>5*1024*1024){
                $z = "文件不能超过5MB";
                continue;
            }
            $new_filename = bin2hex(random_bytes(16)).'.'.$extension;
            $destination= $upload_dir.$new_filename;
            if (move_uploaded_file($fileTmpName,$destination)){
                $z="上传成功";
            }else{
                $z="上传失败".$fileError;
            }
    }
    }
}
if (isset($_GET['delete_image'])){
    $image_path = $_GET['delete_image'];
    if (file_exists($image_path) && is_file($image_path)){
        if (unlink($image_path)){
            $z = "图片删除成功";
        }else{
            $z = "图片删除失败";
        }
    }
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
<!DOCTYPE html>
<html lang="cn">
<head>
    <meta charset="UTF-8">
    <title>管理员</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<p style="color: #e33ff4">欢迎admin！</p>
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
                    <form  method="post" style="display: inline">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <select name="new_privilege">
                            <option value="admin" <?php if ($user['privilege']=='admin') echo 'selected'; ?>>管理员</option>
                            <option value="user" <?php if ($user['privilege']=='user') echo 'selected'; ?>>用户</option>
                        </select>
                        <input type="submit" value="修改">
                    </form>
                    <a href="?delete=<?php echo intval($user['id']); ?>" onclick="return confirm('确定要删除该用户吗')">删除</a>
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

<div>
    <form method="post" enctype="multipart/form-data">
        <p>上传图片</p>
        <input type="file" name="files[]" multiple required>
        <p>支持的图片格式：jpg、png、gif、jpeg</p>
        <input type="submit" value="上传">
    </form>
    <h2>已上传图片</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
            <?php if (count($uploaded_images)>0):?>
            <?php foreach ($uploaded_images as $file): ?>
                    <div style="border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                        <img src="<?php echo text_insde($file['path']); ?>"
                             alt="<?php echo text_insde($file['name']); ?>"
                             style="display: block; width: 100%; height: 150px; object-fit: cover; margin-bottom: 8px;">
                        <p >上传时间<?php echo $file['time']; ?></p>
                        <a href="?delete_image=<?php echo urlencode($file['path']);?>" onclick="return confirm('确定要删除该图片吗？')">删除</a>
                    </div>
        <?php endforeach; ?>
        <?php else: ?>
            <p>暂无图片</p>
        <?php endif; ?>

</div>
<input type="button" value="查看留言" onclick="window.location.href='adminly.php'">
<input type="button" value="退出登录" onclick="window.location.href='?logout=1'">
</body>
</html>
