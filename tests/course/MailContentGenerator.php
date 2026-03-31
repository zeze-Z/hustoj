<?php
/**
 * 邮件内容生成器（不依赖 PHPMailer，仅生成内容用于测试）
 */

/**
 * 生成课程邮件内容
 * @param array $course_data 课程数据
 * @param string $oj_name 平台名称
 * @param int $valid_days 有效期（天）
 * @return string 邮件内容HTML
 */
function generate_course_mail_content($course_data, $oj_name = 'OJ平台', $valid_days = 365) {
    $course_title = htmlspecialchars($course_data['title'], ENT_QUOTES, 'UTF-8');
    $expire_date = date('Y-m-d', strtotime("+$valid_days days"));

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 20px; }
        .info-box { background: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #667eea; }
        .link-item { margin: 15px 0; padding: 15px; background: #fff; border-radius: 5px; border: 1px solid #e0e0e0; }
        .link-label { font-weight: bold; color: #667eea; margin-bottom: 8px; }
        .link-url { word-break: break-all; color: #666; }
        .code { display: inline-block; padding: 5px 10px; background: #ff6b6b; color: white; border-radius: 3px; font-weight: bold; }
        .footer { background: #f0f0f0; padding: 15px; text-align: center; font-size: 12px; color: #999; border-radius: 0 0 8px 8px; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; margin: 15px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">课程下载链接</h2>
        </div>
        <div class="content">
            <div class="info-box">
                <h3 style="margin-top: 0;">{$course_title}</h3>
                <p>感谢您获取本课程！以下是课程的下载链接和相关信息。</p>
            </div>

            <div class="warning">
                <strong>注意：</strong>下载链接有效期至 {$expire_date}，请尽快下载保存。
            </div>

            <!-- 课件下载链接 -->
            <div class="link-item">
                <div class="link-label">📚 课件下载链接</div>
                <div class="link-url">
                    <a href="{$course_data['courseware_link']}" target="_blank">{$course_data['courseware_link']}</a>
                </div>
                <div style="margin-top: 10px;">提取码：<span class="code">{$course_data['courseware_code']}</span></div>
            </div>

            <!-- 教案下载链接 -->
            <div class="link-item">
                <div class="link-label">📖 教案下载链接</div>
                <div class="link-url">
                    <a href="{$course_data['lesson_plan_link']}" target="_blank">{$course_data['lesson_plan_link']}</a>
                </div>
                <div style="margin-top: 10px;">提取码：<span class="code">{$course_data['lesson_plan_code']}</span></div>
            </div>

            <div class="warning">
                <strong>版权声明：</strong>本课件仅供购买用户个人使用，请勿传播。
            </div>
        </div>
        <div class="footer">
            <p>此邮件由 {$oj_name} 自动发送，请勿回复。</p>
            <p>如有问题，请联系系统管理员。</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * 生成课程邮件主题
 * @param string $course_title 课程标题
 * @param string $oj_name 平台名称
 * @return string 邮件主题
 */
function generate_course_mail_subject($course_title, $oj_name = 'OJ平台') {
    return "[$oj_name] 课程下载链接 - " . htmlspecialchars($course_title, ENT_QUOTES, 'UTF-8');
}
