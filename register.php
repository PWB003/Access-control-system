<?php
include 'sqlconfig.php';
global $host,$user,$password,$dbname;

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $link = new mysqli('localhost', 'cms', '123456', 'cmsdemo');
    if ($link->connect_error) {
        die("连接数据库失败 " . $link->connect_error);
    }
    $isset = $link->prepare("SELECT * FROM user WHERE username = ?");
    $isset->bind_param('s',$username);
    $isset->execute();
    $setre = $isset->get_result();
    if($setre->num_rows > 0){
        echo "用户名已被占用";
        $isset->close();
        die;
    }

    $set = $link->prepare("INSERT INTO user (username,password) VALUES (?, ?)");
    $set->bind_param('ss',$username,$password);
    $set->execute();
    $set->close();

    $check = $link->prepare("SELECT * FROM user WHERE username = ? AND password = ?");
    $check->bind_param('ss',$username,$password);
    $check->execute();
    $result = $check->get_result();
    if($result->num_rows >0){
        echo "注册成功！";
        header('Location: login.html');
    }
    else{
        echo "注册失败！";
    }
    $check->close();
    $link->close();

}
