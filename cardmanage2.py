import socket
from pynfc import Nfc
import time
import subprocess
import os
import signal
import sys

Client_server = "0.0.0.0"  # 监听所有地址
Client_port = 7777  # 嵌入式设备监听的端口


# 停止 client3.py 进程
def stop_client3():
    try:
        # 查找 client3.py 的进程 ID
        output = subprocess.check_output(["pgrep", "-f", "client3.py"])
        pids = output.decode().splitlines()
        for pid in pids:
            os.kill(int(pid), signal.SIGTERM)  # 终止进程
            print(f"已停止 client3.py 进程 PID: {pid}")
    except subprocess.CalledProcessError:
        print("client3.py 未运行，无需停止")


# 启动 client3.py
def start_client3():
    try:
        subprocess.Popen(["python3", "client3.py"])
        print("client3.py 已重新启动")
    except Exception as e:
        print(f"启动 client3.py 失败: {e}")


# 发送卡号到主机
def send_uid_to_server(server_socket, uid):
    try:
        if uid:
            server_socket.sendall(uid.encode() + b"\n")
            print(f"卡号 {uid} 已发送到主机")
        else:
            server_socket.sendall(b"error\n")
            print("未读取到卡号，已发送错误信息")
    except Exception as e:
        print(f"发送数据失败: {e}")


# 主函数
def main():
    subprocess.Popen(["python3", "client3.py"])
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as Client_socket:
            Client_socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
            Client_socket.bind((Client_server, Client_port))
            Client_socket.listen(5)
            print(f"正在监听来自主机的请求，端口: {Client_port}")

            # 接收主机连接
            server_socket, addr = Client_socket.accept()
            print(f"来自 {addr} 的连接")

            with server_socket:
                # 接收主机发送的命令
                data = server_socket.recv(1024).decode().strip()
                if data:
                    print(f"接收到命令: {data}")

                    if data == "addcard" or data == "removecard":
                        # 停止 client3.py
                        stop_client3()
                        nfc = Nfc("pn532_uart:/dev/ttyUSB0")  # 使用您提供的 NFC 初始化方式
                        print("NFC 读取器已初始化，等待命令...")

                        print(f"执行命令: {data}，等待卡片刷卡...")
                        while True:
                            try:
                                # 使用 next 轮询 NFC 卡片
                                target = next(nfc.poll())
                                if target:
                                    uid = target.uid.hex()  # 获取卡片 UID
                                    print(f"检测到卡片，UID: {uid}")
                                    send_uid_to_server(server_socket, uid)
                                    break  # 成功获取卡片后退出轮询
                            except Exception as e:
                                print(f"NFC 轮询错误: {e}")
                                break

                        # 重新启动 client3.py
                        start_client3()
                    else:
                        print("收到无效的命令")
                        server_socket.sendall(b"invalid_command\n")

            # 处理完成后退出
            print("任务完成，服务器即将退出")
            subprocess.Popen(["python3", "managepy.py"])
            sys.exit(0)

    except Exception as e:
        print(f"服务器错误: {e}")
    finally:
        print("服务器已停止运行")


if __name__ == "__main__":
    main()
