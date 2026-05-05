# 课程多权限体系升级需求文档
## 需求背景
将原有的单一购买权限拆分为预览版和原文件两种独立权限，吸引不同需求的用户购买，同时增加教师入驻引导，拓展平台内容供给。

## 开发任务清单
### 任务1：教师端课件详情页 - 权限对比说明模块
#### 文件路径：trunk/web/template/syzoj/course_info.php
#### 开发要求：
1. 插入位置：课程基本信息段下方，课程描述段上方
2. 显示规则：仅当preview_price > 0 或 source_price > 0 时显示，免费课程隐藏
3. 内容结构：
   - 标题：「权限说明」
   - 两栏卡片布局：
     - 左侧蓝色卡片：预览版权限
       - 图标：eye
       - 权限点：可查看课件和教案的完整在线预览内容
       - 适用人群：适合仅需要参考内容、不需要编辑修改的用户
       - 价格显示：¥xx.xx
     - 右侧绿色卡片：原文件权限
       - 图标：download
       - 权限点：可以下载课件和教案的可编辑原文件
       - 适用人群：适合需要直接使用和修改课件进行授课的教师
       - 价格显示：¥xx.xx
   - 底部购买提示（精简版）：
     > 💡 购买建议：仅需查看参考选预览版，需要下载修改选原文件，全套购买更划算。
4. 样式要求：圆角卡片、阴影效果、适配移动端

---
### 任务2：教师端课件详情页 - 创作者入驻引导模块
#### 文件路径：trunk/web/template/syzoj/course_info.php
#### 开发要求：
1. 插入位置：页面最底部（所有内容之后）
2. 显示规则：所有课程详情页都显示（包括免费课程）
3. 内容结构：
   - 图标：gift
   - 文案：「🎁 有优质课件资源想分享售卖？欢迎教师入驻！我们提供平台支持，收益分成。」
   - 联系方式：「咨询客服QQ：326234108」
   - 样式：友好的提示卡片，背景浅灰，不突兀
4. 交互：QQ号可直接点击唤起QQ会话（tencent://message/?uin=326234108）

---
### 任务3：管理端课件添加页 - 字段完善
#### 文件路径：trunk/web/admin/course_add.php
#### 开发要求：
1. 已完成字段：
   - ✅ 「预览版价格」输入框（数值类型，min=0）
   - ✅ 「原文件价格」输入框（数值类型，min=0）
   - ✅ 所有「下载链接」字样改为「原文件链接」
   - ✅ 每个链接字段添加帮助说明，明确权限可见范围
2. 确认表单提交逻辑已同步新增字段，SQL插入语句包含preview_price和source_price字段

---
### 任务4：管理端课件编辑页 - 字段完善
#### 文件路径：trunk/web/admin/course_edit.php
#### 开发要求：
1. 已完成字段：
   - ✅ 「预览版价格」输入框，回填现有值
   - ✅ 「原文件价格」输入框，回填现有值
   - ✅ 所有「下载链接」字样改为「原文件链接」
2. 确认表单提交逻辑已同步更新字段，SQL更新语句包含preview_price和source_price字段

---
### 任务5：数据库SQL执行（已完成）
#### 文件路径：db/V1.4_20260505_multi_license.sql
#### SQL内容：
```sql
-- 新增字段
ALTER TABLE course 
ADD COLUMN preview_price DECIMAL(10,2) DEFAULT 0 COMMENT '预览版价格',
ADD COLUMN source_price DECIMAL(10,2) DEFAULT 0 COMMENT '原文件价格';

ALTER TABLE course_order
ADD COLUMN license_type TINYINT DEFAULT 1 COMMENT '权限类型：1=预览版 2=原文件 3=全套',
DROP INDEX user_course,
ADD UNIQUE KEY user_course_license (user_id, course_id, license_type);

-- 回滚SQL
-- ALTER TABLE course DROP COLUMN preview_price, DROP COLUMN source_price;
-- ALTER TABLE course_order DROP COLUMN license_type, DROP INDEX user_course_license, ADD UNIQUE KEY user_course (user_id, course_id);
```
#### 执行要求：
- 测试环境已执行完成，生产环境发布时执行
- 无历史数据兼容问题

---
### 任务6：逻辑层权限校验完善（已完成）
#### 文件路径：trunk/web/course_info.php
#### 开发要求：
- ✅ 已完成权限判断逻辑，区分未购买/已购买预览版/已购买原文件/已购买全套四种状态
- ✅ 已完成三重安全防护：前端不输出无权限链接、后端逻辑层校验、下载入口二次校验
- ✅ 已适配不同权限返回对应的URL，原文件链接仅对有权限用户输出

---
## 验收标准
1. 管理端添加/编辑课件时，可正常输入两种价格，提交后数据正确保存
2. 课件详情页根据价格自动显示/隐藏权限说明模块
3. 权限对比说明内容正确，与实际价格一致
4. 底部入驻引导模块显示正常，QQ号可点击跳转
5. 不同权限的用户看到的操作按钮与权限一致
6. 所有页面适配移动端，无样式错乱

## 部署要求
- 先部署SQL到测试环境
- 再部署所有PHP文件
- 清除OP缓存：`php -r 'opcache_reset();'`
- 测试地址：http://192.168.64.4/course_info.php?id=8
