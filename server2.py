import socket
import pymysql

# 服务器地址和端口
SERVER_IP = "0.0.0.0"  # 监听所有 IP 地址
SERVER_PORT = 8888

# 数据库配置
db_config = {
    "host": "localhost",
    "user": "card",
    "password": "123456",
    "database": "card"
}


# 数据库连接函数
def create_sqlconnection():
    try:
        connection = pymysql.connect(
            host=db_config["host"],
            user=db_config["user"],
            password=db_config["password"],
            database=db_config["database"],
            charset="utf8mb4",
            cursorclass=pymysql.cursors.DictCursor
        )
        print("数据库连接成功")
        return connection
    except pymysql.MySQLError as e:
        print(f"数据库连接失败: {e}")
        return None


# 查询 UID 是否存在
def check_uid(connection, uid):
    query = "SELECT UID FROM idcard WHERE UID = %s"
    try:
        with connection.cursor() as cursor:
            cursor.execute(query, (uid,))
            result = cursor.fetchone()  # 获取第一条记录
            return result is not None  # 如果找到记录返回 True
    except pymysql.MySQLError as e:
        print(f"数据库查询错误: {e}")
        return False


# 主函数
def main():
    try:
        # 创建服务器套接字
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as server_socket:
            server_socket.bind((SERVER_IP, SERVER_PORT))
            server_socket.listen(5)
            print(f"服务器正在监听端口 {SERVER_PORT}...")

            while True:
                # 接收连接
                client_socket, addr = server_socket.accept()
                print(f"来自 {addr} 的连接")

                # 接收 UID 数据
                with client_socket:
                    data = client_socket.recv(1024).decode().strip()  # 接收并去掉空白符
                    if data:
                        print(f"接收到 UID: {data}")

                        # 连接数据库
                        connection = create_sqlconnection()
                        if connection:
                            # 查询 UID 是否存在
                            uid_exists = check_uid(connection, data)

                            # 根据结果发送响应
                            response = "1" if uid_exists else "0"
                            client_socket.sendall(response.encode())
                            print(f"发送响应: {response}")

                            # 关闭数据库连接
                            connection.close()
                        else:
                            print("数据库连接失败，无法处理请求")
    except Exception as e:
        print(f"服务器错误: {e}")


if __name__ == "__main__":
    main()
