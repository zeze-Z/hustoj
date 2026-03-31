<?php
/**
 * 课程邮件发送函数
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require dirname(__FILE__).'/Exception.php';
require dirname(__FILE__).'/PHPMailer.php';
require dirname(__FILE__).'/SMTP.php';

/**
 * 发送课程下载链接邮件
 * @param string $to_email 收件邮箱
 * @param array $course_data 课程数据（包含课程名称、链接、提取码等）
 * @param int $valid_days 有效期（天）
 * @return bool 发送成功返回true，失败返回false
 */
function send_course_mail($to_email, $course_data, $valid_days = 365) {
    global $OJ_NAME, $SMTP_SERVER, $SMTP_PORT, $SMTP_USER, $SMTP_PASS;

    // 检查SMTP是否配置
    if ($SMTP_USER == "mailer@qq.com") {
        return false;
    }

    // 验证邮箱格式
    if (!filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host = $SMTP_SERVER;
        $mail->SMTPAuth = true;
        $mail->Username = $SMTP_USER;
        $mail->Password = $SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $SMTP_PORT;

        // Recipients
        $mail->setFrom($SMTP_USER, $OJ_NAME);
        $mail->addAddress($to_email);

        // 课程名称
        $course_title = htmlspecialchars($course_data['title'], ENT_QUOTES, 'UTF-8');

        // 计算过期日期
        $expire_date = date('Y-m-d', strtotime("+$valid_days days"));

        // 构建邮件内容
        $mail_body = <<<HTML
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
            <p>此邮件由 {$OJ_NAME} 自动发送，请勿回复。</p>
            <p>如有问题，请联系系统管理员。</p>
        </div>
    </div>
</body>
</html>
HTML;

        $mail->Subject = "[$OJ_NAME] 课程下载链接 - {$course_title}";
        $mail->Body = $mail_body;
        $mail->isHTML(true);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("邮件发送失败: " . $mail->ErrorInfo);
        return false;
    }
}
