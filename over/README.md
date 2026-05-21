# 学校管理系统 - 项目文档

## 📋 项目概述

这是一个基于 PHP 开发的学校管理系统，提供用户管理、留言管理和文件上传功能。系统采用三层权限架构（超级管理员、普通管理员、普通用户），支持用户注册、登录、留言发布与管理等功能。

**项目名称**: 学校管理系统  
**版本**: 1.0  
**开发语言**: PHP  
**数据库**: MySQL  
**最后更新**: 2026-05-21

---

## 🏗️ 系统架构

### 技术栈

- **后端**: PHP (使用 PDO 进行数据库操作)
- **数据库**: MySQL
- **前端**: HTML5 + CSS3
- **服务器**: Apache/Nginx (支持 PHP)
- **会话管理**: PHP Session + Cookie

### 架构模式

采用传统的 MVC 架构变体：
- **Model**: `sql.php` (数据访问层)
- **View**: 所有 `.php` 页面 (表现层)
- **Controller**: 各页面中的业务逻辑处理

---

## 📁 项目结构

```
over/
├── sql.php                 # 数据库连接配置
├── text.php                # 输入过滤工具函数
├── style.css               # 全局样式表
├── login.php               # 登录页面
├── zc.php                  # 注册页面
├── forget.php              # 密码找回页面
├── update.php              # 密码修改页面
├── admin.php               # 超级管理员后台
├── adminly.php             # 超级管理员留言管理
├── adminuser.php           # 普通管理员后台
├── adminuserly.php         # 普通管理员留言管理
├── wl.php                  # 普通用户留言管理
├── ping.php                # 评论/评分功能
├── table.php               # 数据表格展示
├── uploads/                # 文件上传目录
│   ├── *.jpg              # 上传的图片文件
│   └── ...                # 其他格式文件
└── .idea/                  # IDE 配置文件
```

---

## 💾 数据库设计

### 数据库信息

- **数据库名**: `school`
- **主机**: `localhost`
- **字符集**: `utf8mb4`
- **默认用户**: `root`

### 数据表结构

#### 1. users 表 - 用户信息表

| 字段名 | 类型 | 说明 | 约束 |
|--------|------|------|------|
| id | INT | 用户ID | PRIMARY KEY, AUTO_INCREMENT |
| name | VARCHAR | 用户名 | UNIQUE, NOT NULL |
| password | VARCHAR | 密码(加密存储) | NOT NULL |
| age | INT | 年龄 | - |
| email | VARCHAR | 邮箱 | UNIQUE, NOT NULL |
| privilege | VARCHAR | 权限级别 | 'admin' 或 'user' |

#### 2. students 表 - 留言表

| 字段名 | 类型 | 说明 | 约束 |
|--------|------|------|------|
| id | INT | 留言ID | PRIMARY KEY, AUTO_INCREMENT |
| name | VARCHAR | 留言者姓名 | NOT NULL |
| content | TEXT | 留言内容 | NOT NULL |
| time | DATETIME | 留言时间 | DEFAULT CURRENT_TIMESTAMP |

---

## 🔐 用户权限系统

### 权限级别

系统采用三级权限架构：

1. **超级管理员 (admin/root)**
   - 访问 `admin.php`
   - 管理所有用户（修改权限、删除用户）
   - 上传和管理图片文件
   - 查看所有留言

2. **普通管理员 (privilege='admin')**
   - 访问 `adminuser.php`
   - 查看用户列表
   - 删除普通用户
   - 查看所有留言和图片
   - 修改密码

3. **普通用户 (privilege='user')**
   - 访问 `wl.php`
   - 发布留言
   - 删除自己的留言
   - 修改密码

### 安全机制

- ✅ 密码使用 `password_hash()` 加密存储
- ✅ 使用 PDO 预处理语句防止 SQL 注入
- ✅ 输入数据通过 `htmlspecialchars()` 过滤防止 XSS
- ✅ Session 验证保护受保护页面
- ✅ 文件上传类型和大小限制（最大 5MB）
- ✅ MIME 类型验证
- ✅ 安全的 Session 销毁机制

---

## 📄 核心功能模块

### 1. 用户认证模块

#### 登录 (`login.php`)
- 用户名和密码验证
- 记住我功能（Cookie 保存 7 天）
- 自动识别用户权限并跳转到对应页面
- 特殊账号 admin/root 直接进入超级管理员界面

#### 注册 (`zc.php`)
- 用户名唯一性检查
- 邮箱格式验证
- 年龄合法性验证（0-120）
- 禁止注册 admin 用户名
- 密码加密存储

#### 密码找回 (`forget.php`)
- 通过邮箱验证身份
- 重置密码功能

#### 密码修改 (`update.php`)
- 需要登录状态
- 验证旧密码后更新新密码

### 2. 留言管理模块

#### 普通用户留言 (`wl.php`)
- 发布新留言
- 查看留言列表
- 删除自己的留言
- 显示留言时间和作者

#### 管理员留言管理 (`adminly.php`, `adminuserly.php`)
- 查看所有用户留言
- 删除任意留言
- 留言统计信息

### 3. 用户管理模块

#### 超级管理员 (`admin.php`)
- 查看所有用户列表
- 修改用户权限（admin/user）
- 删除任意用户
- 批量上传图片
- 管理已上传图片

#### 普通管理员 (`adminuser.php`)
- 查看用户列表
- 删除普通用户
- 查看上传图片
- 查看留言

### 4. 文件上传模块

**支持的文件类型**:
- 图片: jpg, jpeg, png, gif, webp
- 文档: pdf

**上传限制**:
- 单个文件最大 5MB
- 自动创建上传目录
- 生成随机文件名（防止冲突和安全问题）
- MIME 类型双重验证

**功能**:
- 多文件批量上传
- 图片预览
- 删除已上传图片
- 显示上传时间

---

## 🔧 工具函数

### text.php - 输入过滤

```php
text_insde($string)
```

**功能**:
1. `htmlspecialchars()` - 转义 HTML 特殊字符
2. `trim()` - 去除首尾空格
3. `stripslashes()` - 移除反斜杠

**用途**: 所有用户输入都必须经过此函数处理

---

## 🎨 前端设计

### 样式特点

- 响应式布局
- 统一的配色方案
- 卡片式设计风格
- 平滑过渡动画
- 友好的用户提示

### CSS 文件 (`style.css`)

包含以下样式组件：
- 表单样式
- 表格样式
- 按钮样式
- 消息提示样式
- 图片网格布局

---

## 🚀 部署指南

### 环境要求

- PHP >= 7.0
- MySQL >= 5.6
- Web 服务器 (Apache/Nginx)
- 启用 PDO 扩展
- 启用 fileinfo 扩展（用于 MIME 类型检测）

### 安装步骤

1. **克隆项目**
   ```bash
   cd /var/www/html
   git clone <repository-url>
   ```

2. **配置数据库**
   ```sql
   CREATE DATABASE school CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(50) UNIQUE NOT NULL,
       password VARCHAR(255) NOT NULL,
       age INT,
       email VARCHAR(100) UNIQUE NOT NULL,
       privilege VARCHAR(10) DEFAULT 'user'
   );
   
   CREATE TABLE students (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(50) NOT NULL,
       content TEXT NOT NULL,
       time DATETIME DEFAULT CURRENT_TIMESTAMP
   );
   ```

3. **修改数据库配置**
   编辑 `sql.php` 文件：
   ```php
   $host = "localhost";
   $dbname = "school";
   $user = "root";
   $password = "your_password";
   ```

4. **设置上传目录权限**
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

5. **访问系统**
   浏览器访问: `http://localhost/over/login.php`

### 初始管理员账号

首次使用后，需要在数据库中手动创建管理员账号：

```sql
INSERT INTO users (name, password, age, email, privilege) 
VALUES ('admin', '$2y$10$...', 25, 'admin@example.com', 'admin');
```

*注意: 密码需要使用 password_hash() 生成*

---

## 🔒 安全建议

### 当前安全措施

✅ SQL 注入防护（PDO 预处理）  
✅ XSS 防护（htmlspecialchars）  
✅ 密码加密存储  
✅ Session 安全销毁  
✅ 文件上传验证  
✅ CSRF 基础防护（Session 验证）  

### 改进建议

⚠️ **建议添加的功能**:

1. **CSRF Token 保护**
   - 为所有表单添加 CSRF token

2. **密码强度验证**
   - 要求最小长度 8 位
   - 包含大小写字母、数字、特殊字符

3. **登录失败限制**
   - IP 地址锁定机制
   - 验证码功能

4. **HTTPS 支持**
   - 强制 HTTPS 连接
   - Secure Cookie 标志

5. **文件上传增强**
   - 图片压缩和优化
   - 病毒扫描
   - 存储配额管理

6. **审计日志**
   - 记录用户操作
   - 登录历史追踪

7. **数据备份**
   - 定期数据库备份
   - 文件备份机制

---

## 📊 API 参考

### 页面路由

| 路径 | 方法 | 说明 | 权限 |
|------|------|------|------|
| `/login.php` | GET/POST | 登录页面 | 公开 |
| `/zc.php` | GET/POST | 注册页面 | 公开 |
| `/forget.php` | GET/POST | 密码找回 | 公开 |
| `/update.php` | GET/POST | 修改密码 | 登录用户 |
| `/admin.php` | GET/POST | 超级管理员后台 | admin/root |
| `/adminly.php` | GET | 超级管理员留言管理 | admin/root |
| `/adminuser.php` | GET | 普通管理员后台 | privilege='admin' |
| `/adminuserly.php` | GET | 普通管理员留言管理 | privilege='admin' |
| `/wl.php` | GET/POST | 用户留言管理 | 登录用户 |
| `/ping.php` | GET/POST | 评论功能 | 登录用户 |

---

## 🐛 常见问题

### Q1: 无法连接数据库
**解决方案**:
- 检查 MySQL 服务是否运行
- 验证 `sql.php` 中的配置信息
- 确认数据库 `school` 已创建

### Q2: 文件上传失败
**解决方案**:
- 检查 `uploads` 目录是否存在且有写权限
- 验证 `php.ini` 中的上传限制：
  ```ini
  upload_max_filesize = 10M
  post_max_size = 10M
  ```

### Q3: Session 丢失
**解决方案**:
- 检查 `session_start()` 是否在页面顶部调用
- 确认浏览器启用了 Cookie
- 检查 Session 存储目录权限

### Q4: 中文乱码
**解决方案**:
- 确保数据库字符集为 `utf8mb4`
- 检查 HTML 页面包含 `<meta charset="utf-8">`
- 验证 PHP 文件编码为 UTF-8

---

## 📝 更新日志

### Version 1.0 (2026-05-21)
- ✨ 初始版本发布
- ✅ 用户注册/登录系统
- ✅ 三级权限管理
- ✅ 留言发布和管理
- ✅ 文件上传功能
- ✅ 密码找回和修改
- ✅ 用户管理后台

---

## 👥 维护与支持

### 代码维护
- 保持代码注释清晰
- 遵循 PSR 编码规范
- 定期更新依赖和安全补丁

### 联系方式
如有问题或建议，请联系开发团队。

---

## 📄 许可证

本项目仅供学习和内部使用。

---

## 🙏 致谢

感谢所有参与本项目开发和测试的人员。

---

**文档生成日期**: 2026-05-21  
**文档版本**: 1.0