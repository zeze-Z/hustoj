# 课件商城模块单元测试

## 目录结构

```
tests/course/
├── CourseModel.php           # 课程数据模型（Mock）
├── PaymentHelper.php         # 支付辅助类（签名逻辑）
├── MailContentGenerator.php  # 邮件内容生成器
├── test_course_model.php     # 课程数据模型测试
├── test_payment.php          # 支付签名测试
├── test_mail.php             # 邮件内容测试
└── run_all.php               # 运行所有测试
```

## 运行测试

### 单独运行测试

```bash
# 课程数据模型测试
php tests/course/test_course_model.php

# 支付签名测试
php tests/course/test_payment.php

# 邮件内容测试
php tests/course/test_mail.php
```

### 运行所有测试

```bash
php tests/course/run_all.php
```

## 测试覆盖范围

### 1. 课程数据模型测试 (test_course_model.php)

| 测试项 | 说明 |
|--------|------|
| 价格校验 | 价格 >= 0 为合法，< 0 为非法 |
| 标签解析 | 逗号分隔的标签字符串解析为数组 |
| 免费课程判断 | price == 0 返回 true |
| URL 校验 | 合法 URL 返回 true |
| kdocs 域名校验 | 只允许 kdocs.cn 域名 |

### 2. 支付签名测试 (test_payment.php)

| 测试项 | 说明 |
|--------|------|
| 签名生成 | 给定参数和 key，生成正确的 MD5 签名 |
| 签名验证 | 正确/错误签名验证 |
| 空值过滤 | 签名时过滤空值和 sign 参数 |
| 金额校验 | 订单金额与回调金额误差 <= 0.01 |

### 3. 邮件内容测试 (test_mail.php)

| 测试项 | 说明 |
|--------|------|
| 邮件主题 | 包含平台名称和课程名称 |
| 邮件内容 | 包含课件/教案下载链接和提取码 |
| 有效期说明 | 包含有效期至日期 |
| 版权提示 | 包含版权声明 |
| XSS 防护 | 转义特殊字符 |

## 输出格式示例

```
=== 课程数据模型测试 ===

--- 价格校验 ---
[PASS] 价格校验 - 合法价格(0)
[PASS] 价格校验 - 合法价格(19.9)
...

总计: 15 通过, 0 失败
```
