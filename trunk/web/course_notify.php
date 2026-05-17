<?php
/**
 * 旧支付宝回调已下线。
 * 平台课件支付已切换为积分支付（V1.9），不再处理支付宝异步回调。
 * 仅保留此文件以避免历史 URL 抛出 404 与抓取告警。
 */

http_response_code(410);
header('Content-Type: text/plain; charset=utf-8');
echo "alipay payment is disabled. course payment has been migrated to platform points.\n";
exit();
