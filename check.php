<?php
error_reporting(0);
include 'sqlconfig.php';
function check(){
    if (isset($_COOKIE['PHPSESSID'])) {
        $id = $_COOKIE['PHPSESSID'];
            $link = new mysqli('localhost', 'cms', '123456', 'cmsdemo');
            if ($link->connect_error) {
                echo "数据库连接失败";
                return false;
            }
            $select = $link->prepare("SELECT * FROM session WHERE session_id = ?");
            $select->bind_param('s', $id);
            $select->execute();
            $resoult = $select->get_result();

        if ($resoult->num_rows > 0) {
            //echo 'true';
            return true;
        } else {
            //echo 'false';
            return false;
        }
        $link->close();


    } else {
        //echo 'false';
        return false;
    }
}
?>