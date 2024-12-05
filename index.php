<?php

// Include the cookie check script
include ('check.php');

// Check if the session is valid
if (check()) {
    // If the session is valid, display the welcome message
    echo "欢迎";
    header("location: index.html");
} else {
    echo "请重新登陆";
    header("location: login.html");
    exit();
    // If the session is not valid, redirect to fail.php
}
?>