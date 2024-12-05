import socket
from pynfc import Nfc
import time

SERVER_IP = "10.36.9.2"  # 替换为服务器 IP
SERVER_PORT = 8888            # 替换为服务器端口

def send_uid_to_server(uid):
    """发送 UID 到服务器并接收响应"""
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as client_socket:
            client_socket.connect((SERVER_IP, SERVER_PORT))
            client_socket.sendall(uid.encode())  # 发送 UID
            print(f"UID {uid} sent to server.")

            # 接收服务器响应
            response = client_socket.recv(1024).decode()
            print(f"Server response: {response}")

            # 根据响应显示结果
            if response == "1":
                print("UID exists in the database.")
            elif response == "0":
                print("UID does not exist in the database.")
            else:
                print(f"Unexpected server response: {response}")
    except Exception as e:
        print(f"Error sending UID to server: {e}")

def main():
    try:
        # 初始化 NFC 设备
        nfc = Nfc("pn532_uart:/dev/ttyUSB0")
        print("NFC reader initialized. Waiting for card...")

        while True:
            try:
                time.sleep(1)
                # 使用 next 获取目标
                target = next(nfc.poll())
                if target:
                    uid = target.uid.hex()
                    print(f"Card detected! UID: {uid}")
                    send_uid_to_server(uid)
                else:
                    print("No card detected.")
            except Exception as e:
                print(f"Error during NFC polling: {e}")

    except Exception as e:
        print(f"Error initializing NFC device: {e}")
    finally:
        # 删除 close 调用，确保程序退出时无误
        pass

if __name__ == "__main__":
    main()
