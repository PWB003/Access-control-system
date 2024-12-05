<?php
include 'check.php';
include 'sqlconfig.php';
error_reporting(0);

if (check()) {
    $id = $_COOKIE['PHPSESSID'];
    $link = new mysqli('localhost','cms','123456','cmsdemo');
    if ($link->connect_error) {
        echo "数据库连接失败";
    }
    $select = $link->prepare("SELECT username FROM session WHERE session_id = ?");
    $select->bind_param('s', $id);
    $select->execute();
    $result = $select->get_result();
    $row = $result->fetch_assoc();
    if($row['username'] == 'admin'){
        if($_GET['select'] == 1){
            adduser();
        }
        elseif($_GET['select'] == 2){
            deleteuser();
        }
    }
    else{
        echo '无权管理用户';
    }
    $link->close();

}
else {
    echo "请重新登陆";
}

function  adduser(): void
{
    if(isset($_POST['newusername'])&&isset($_POST['newpassword'])) {
        $newusername = $_POST['newusername'];
        $newpassword = md5($_POST['newpassword']);
        if ($newusername == 'admin') {
            echo "无法注册新管理员";
        } else {
            //检测用户名是否已存在
            $link = new mysqli('localhost', 'cms', '123456', 'cmsdemo');
            $isset = $link->prepare("SELECT * FROM user WHERE username = ?");
            $isset->bind_param('s', $newusername);
            $isset->execute();
            $result = $isset->get_result();
            if ($result->num_rows > 0) {
                echo "已存在该用户";
                $link->close();
            }
            else {
                //将新用户写入数据库
                $link->close();
                $link = new mysqli('localhost','cms','123456','cmsdemo');
                $creat = $link->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
                $creat->bind_param('ss', $newusername, $newpassword);
                $creat->execute();
                $creat->close();
                echo "创建成功";
            }
        }
    }
}
function deleteuser(): void{
    if(isset($_POST['dusername'])) {
        $dusername = $_POST['dusername'];
        if ($dusername == 'admin') {
            echo "无法删除管理员";
        } else {
            //检测用户名是否已存在
            $link = new mysqli('localhost', 'cms', '123456', 'cmsdemo');
            $check = $link->prepare("SELECT * FROM user WHERE username = ?");
            $check->bind_param('s', $dusername);
            $check->execute();
            $result = $check->get_result();
            if ($result->num_rows == 0) {
                echo "不存在该用户";
            }
            else {
                $creat = $link->prepare("DELETE FROM user WHERE username = ?");
                $creat->bind_param('s', $dusername);
                $creat->execute();
                $dresult = $creat->get_result();
                if ($dresult->num_rows == 0) {
                    echo "删除成功";
                }
            }
        }
    }
}