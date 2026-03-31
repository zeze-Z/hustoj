<?php
// MailHelper.php - 邮件内容生成

function build_mail_body($course, $expire_days = 365) {
    $title        = htmlspecialchars($course['title'] ?? '', ENT_QUOTES, 'UTF-8');
    $cw_link      = htmlspecialchars($course['courseware_link'] ?? '', ENT_QUOTES, 'UTF-8');
    $cw_code      = htmlspecialchars($course['courseware_code'] ?? '', ENT_QUOTES, 'UTF-8');
    $lp_link      = htmlspecialchars($course['lesson_plan_link'] ?? '', ENT_QUOTES, 'UTF-8');
    $lp_code      = htmlspecialchars($course['lesson_plan_code'] ?? '', ENT_QUOTES, 'UTF-8');
    $expire_days  = intval($expire_days);

    return <<<HTML
<html><body>
<h2>您好，感谢购买课程：{$title}</h2>
<p><strong>课件下载链接：</strong><a href="{$cw_link}">{$cw_link}</a></p>
<p><strong>课件提取码：</strong>{$cw_code}</p>
<p><strong>教案下载链接：</strong><a href="{$lp_link}">{$lp_link}</a></p>
<p><strong>教案提取码：</strong>{$lp_code}</p>
<p><strong>链接有效期：</strong>{$expire_days} 天，请尽快下载。</p>
<p style="color:#999;">本课件仅供购买用户个人使用，请勿传播。</p>
</body></html>
HTML;
}
