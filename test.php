<?php
include 'sqlconfig.php';
$link = new mysqli('localhost','cms','123456','cmsdemo');
if ($link->connect_error) {
    echo "数据库连接失败";
}
$var = "fo9oih9ct85jct9i8ktbsgflgt";
$select = $link->prepare("SELECT username FROM session WHERE session_id = ?");
$select->bind_param('s', $var);
$select->execute();
$result = $select->get_result();
$row = $result->fetch_assoc();
echo $row['username'];