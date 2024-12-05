<?php
include 'check.php';
include 'sqlconfig.php';
error_reporting(0);

$dbhost = $host;
$dbuser = $user;
$dbpasswd = $password;
$name = $dbname;
if(check()){
    $id = $_COOKIE('PHPSESSID');
    $link = new mysqli($dbhost,$dbuser,$dbpasswd,$name);
    if ($link->connect_error) {
        echo "数据库连接失败";
    }
    $check = $link->prepare("SELECT username FROM session WHERE session_id = ?");
    $check->bind_param('s',$id);
    $check->execute();
    $result = $check->get_result();
    $row = $result->fetch_assoc();
    $check->close();
    if(isset($_POST['newusername']) && isset($_POST['newpassword'])){
        $change = $link->prepare("");

    }
    else{
        echo "请输修改后的用户名和密码";
    }
}
else{
    echo "请重新登陆";
}