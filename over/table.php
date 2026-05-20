<?php
include "sql.php";

$sql="CREATE TABLE IF NOT EXISTS students(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    content VARCHAR(100) NOT NULL,
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$result=$pdo->exec($sql);
if ($result!== false){
    echo "students创建表成功";
}
$sql2="CREATE TABLE IF NOT EXISTS users(
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    age INT UNSIGNED NOT NULL,
    email VARCHAR(50) NOT NULL,
    password VARCHAR(200) NOT NULL,
    privilege ENUM('admin','user') NOT NULL DEFAULT 'user'
    )ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$row=$pdo->exec($sql2);
if ($row!== false){
    echo "users创建表成功";
}
?>
