<?php
include "check.php";

$client_ip = "192.168.109.152"; // 嵌入式设备的 IP
$client_port = 7777; // 嵌入式设备的端口

// 数据库配置
$DB_HOST = "localhost";
$DB_USER = "card";
$DB_PASS = "123456";
$DB_NAME = "card";

// 检查用户登录状态
if (check()) {
    if (isset($_GET['select'])) {
        $select = $_GET['select'];
        $socket = fsockopen($client_ip, $client_port, $errno, $errstr);
        if (!$socket) {
            echo "无法连接：$errstr ($errno)\n";
        } else {
            if ($select == "1") {
                add_card($socket);
            } elseif ($select == "0") {
                remove_card($socket);
            }
        }
    }
} else {
    echo "请重新登录";
}

// 添加卡 ID 到数据库
function add_card($socket) {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;

    if (isset($_POST['username'])) {
        $username = $_POST['username'];
        $data_to_send = 'addcard';
        fwrite($socket, $data_to_send . "\n");

        // 接收嵌入式设备返回的卡 ID
        $card_id = trim(fgets($socket, 1024)); // 假设嵌入式设备返回卡 ID

        if ($card_id) {
            // 将卡 ID 写入数据库
            $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
            if ($conn->connect_error) {
                die("数据库连接失败: " . $conn->connect_error);
            }
            echo $card_id;
            echo $username;

            $stmt = $conn->prepare("INSERT INTO idcard (UID, username) VALUES (?, ?)");
            $stmt->bind_param("ss", $card_id, $username);

            if ($stmt->execute()) {
                echo "卡 ID 添加成功：$card_id\n";
            } else {
                echo "卡 ID 添加失败：" . $stmt->error . "\n";
            }

            $stmt->close();
            $conn->close();
        } else {
            echo "未收到卡 ID\n";
        }
    }
}

// 从数据库中删除卡 ID
function remove_card($socket) {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;

    $data_to_send = 'removecard';
    fwrite($socket, $data_to_send . "\n");

    // 接收嵌入式设备返回的卡 ID
    $card_id = trim(fgets($socket, 1024)); // 假设嵌入式设备返回卡 ID

    if ($card_id) {
        // 从数据库中删除卡 ID
        $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        if ($conn->connect_error) {
            die("数据库连接失败: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("DELETE FROM idcard WHERE UID = ?");
        $stmt->bind_param("s", $card_id);

        if ($stmt->execute()) {
            echo "卡 ID 删除成功：$card_id\n";
        } else {
            echo "卡 ID 删除失败：" . $stmt->error . "\n";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "未收到卡 ID\n";
    }
}
?>
