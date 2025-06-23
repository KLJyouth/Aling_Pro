# MongoDB 安装指南

## Windows 系统安装

1. 下载MongoDB Community Edition：
   https://www.mongodb.com/try/download/community

2. 运行安装程序，选择：
   - Complete 完全安装
   - 勾选"Install MongoD as a Service"
   - 使用默认数据目录 `C:\data\db`

3. 添加MongoDB到系统PATH：
   - 安装时勾选"Add MongoDB to System PATH"
   - 或手动添加：`C:\Program Files\MongoDB\Server\{version}\bin`

4. 验证安装：
   ```bash
   mongod --version
   mongo --version
   ```

## macOS 系统安装

1. 使用Homebrew安装：
   ```bash
   brew tap mongodb/brew
   brew install mongodb-community
   ```

2. 启动服务：
   ```bash
   brew services start mongodb-community
   ```

3. 验证安装：
   ```bash
   mongod --version
   ```

## 使用MongoDB Atlas (云服务)

1. 注册免费账号：
   https://www.mongodb.com/cloud/atlas

2. 创建集群后获取连接字符串：
   ```env
   MONGODB_URI=mongodb+srv://<username>:<password>@cluster0.xxxxx.mongodb.net/alingai
   ```

3. 更新.env文件：
   ```env
   MONGODB_URI=你的连接字符串
   ```

## 开发建议

1. 本地开发可使用SQLite回退(已配置)
2. 生产环境建议使用MongoDB Atlas
3. 确保/data/db目录存在且有写入权限