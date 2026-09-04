# 老师登录IP归属地统计功能

## 目标
统计老师账号的登录IP归属地，并以柱状图形式展示

## 需求分析
1. 查询 `loginlog` 表中老师（`users.role = 'teacher'`）的登录记录
2. 获取IP地址并查询其归属地信息
3. 使用 ECharts 展示柱状图统计结果

## IP归属地查询方案调研

### 免费无需Key的API方案

| API | 地址 | 限频 | 数据质量 | 推荐度 |
|-----|------|------|---------|--------|
| **ip-api.com** | http://ip-api.com/json/{ip} | 45次/分钟 | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| **ip.sb** | https://api.ip.sb/geoip/{ip} | 较宽松 | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| **ipwho.is** | https://ipwho.is/{ip} | 无限制 | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| **ipinfo.io** | https://ipinfo.io/{ip}/json | 10万次/月 | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |

### 推荐方案：ip-api.com
**优势：**
- ✅ 完全免费，无需API Key
- ✅ 支持中文（`?lang=zh-CN`）
- ✅ 返回国家、城市、省份、ISP等完整信息
- ✅ 响应速度快
- ✅ 数据质量较好

**PHP示例：**
```php
$ip = '8.8.8.8';
$response = file_get_contents("http://ip-api.com/json/{$ip}?lang=zh-CN");
$data = json_decode($response, true);
echo $data['country'];  // 国家
echo $data['regionName']; // 省份
echo $data['city'];     // 城市
```

**注意事项：**
- 限频45次/分钟，批量查询时需要控制频率
- 非商业用途免费
- 中国IP归属地精度较好

### 备选方案：ip.sb
```php
$response = file_get_contents("https://api.ip.sb/geoip/{$ip}");
$data = json_decode($response, true);
```

## 实现方案

### 1. 数据库查询
```sql
-- 查询老师的登录IP统计
SELECT 
    l.ip,
    COUNT(*) as login_count,
    u.user_id,
    u.nick
FROM loginlog l
JOIN users u ON l.user_id = u.user_id
WHERE u.role = 'teacher' 
  AND l.password = 'login ok'
  AND l.time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY l.ip, u.user_id
ORDER BY login_count DESC
```

### 2. 页面设计
- 管理后台新页面：`admin/teacher_ip_stats.php`
- 支持时间范围筛选（默认最近30天）
- 支持学校筛选（学校隔离）
- ECharts 柱状图展示：
  - X轴：IP归属地（城市/省份）
  - Y轴：登录次数
  - 支持按城市或省份统计

### 3. 文件变更清单

| 文件 | 变更类型 | 说明 |
|------|---------|------|
| `admin/teacher_ip_stats.php` | 新增 | 老师登录IP统计页面 |
| `admin/menu2.php` | 修改 | 添加菜单项 |
| `include/ip_location.php` | 新增 | IP归属地查询封装类 |

### 4. 权限控制
- 仅管理员可访问（`$_SESSION[$OJ_NAME.'_'.'administrator']`）
- 支持学校隔离（学校管理员只能查看本校数据）

### 5. 验证步骤
1. 访问 `admin/teacher_ip_stats.php`
2. 验证权限控制（非管理员无法访问）
3. 验证时间范围筛选功能
4. 验证柱状图展示效果
5. 验证学校隔离功能

### 6. SQL归档
- 无数据库表结构变更，无需SQL归档
