<?php

include 'sqlconfig.php';
session_start();


global $host,$user,$password,$dbname;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password']; // This is already MD5 hashed from the front-end
    $link = new mysqli('localhost', 'cms', '123456', 'cmsdemo');
    if ($link->connect_error) {
        die("连接数据库失败" . $link->connect_error);
        header('Location: fali.php');
    }


    $stmt = $link->prepare("SELECT * FROM user WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $username;
        $session_id = session_id();
        $save_session = $link->prepare("INSERT INTO session (session_id,username) VALUES (?, ?)");
        $save_session->bind_param('ss',$session_id,$username);
        $save_session->execute();
        echo "Login successful!";
        header('Location: index.html');
        exit();

    } else {
        // Login failed
        echo "Invalid username or password.";
    }

    $stmt->close();
}

$link->close();
?>
