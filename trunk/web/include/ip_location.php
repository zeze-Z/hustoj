<?php
/**
 * IP归属地查询封装类
 * 使用 ip-api.com 免费API（无需Key）
 * 文档：http://ip-api.com/docs/api:json
 */

class IpLocation {
    private static $cache = [];
    private static $api_url = 'http://ip-api.com/json/';

    /**
     * 查询IP归属地
     * @param string $ip IP地址
     * @return array 包含country, regionName, city, isp等信息
     */
    public static function getLocation($ip) {
        // 过滤本地IP、内网IP和无效IP
        if (empty($ip) || $ip === '::1' || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return [
                'country' => '未知',
                'regionName' => '未知',
                'city' => '未知',
                'isp' => '未知'
            ];
        }

        // 内网IP直接返回，不调用API
        if (self::isPrivateIp($ip)) {
            return [
                'country' => '内网',
                'regionName' => '内网',
                'city' => '内网(' . $ip . ')',
                'isp' => '内网'
            ];
        }

        // 检查缓存
        if (isset(self::$cache[$ip])) {
            return self::$cache[$ip];
        }

        $url = self::$api_url . urlencode($ip) . '?lang=zh-CN&fields=country,regionName,city,isp';

        $context = stream_context_create([
            'http' => [
                'timeout' => 2,
                'connect_timeout' => 2,
                'ignore_errors' => true
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return [
                'country' => '查询失败',
                'regionName' => '查询失败',
                'city' => '查询失败',
                'isp' => '查询失败'
            ];
        }

        $data = json_decode($response, true);

        if (!$data || $data['status'] !== 'success') {
            return [
                'country' => '未知',
                'regionName' => '未知',
                'city' => '未知',
                'isp' => '未知'
            ];
        }

        $result = [
            'country' => $data['country'] ?? '未知',
            'regionName' => $data['regionName'] ?? '未知',
            'city' => $data['city'] ?? '未知',
            'isp' => $data['isp'] ?? '未知'
        ];

        // 缓存结果
        self::$cache[$ip] = $result;

        return $result;
    }

    /**
     * 批量查询IP归属地（带精确频率控制）
     * ip-api.com免费版限制45次/分钟，本方法确保不超限
     * 超过45次后自动等待至下一分钟窗口再继续
     * @param array $ips IP地址数组
     * @return array IP => location的映射
     */
    public static function batchGetLocation($ips) {
        $results = [];
        $api_count = 0;        // 本窗口已发起的API调用次数
        $window_start = time(); // 当前窗口起始时间
        $limit = 45;            // 每分钟允许的最大请求数

        foreach ($ips as $ip) {
            // 内网IP不消耗API配额
            if (self::isPrivateIp($ip)) {
                $results[$ip] = self::getLocation($ip);
                continue;
            }

            // 已达本分钟上限，等待到下一窗口
            if ($api_count >= $limit) {
                $elapsed = time() - $window_start;
                if ($elapsed < 60) {
                    sleep(60 - $elapsed + 1); // 多等1秒余量
                }
                // 重置窗口
                $window_start = time();
                $api_count = 0;
            }

            $results[$ip] = self::getLocation($ip);
            $api_count++;
        }

        return $results;
    }

    /**
     * 判断是否为内网IP
     * @param string $ip IP地址
     * @return bool
     */
    private static function isPrivateIp($ip) {
        return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * 获取地点字符串（省-市）
     * @param string $ip IP地址
     * @return string 格式化的地点字符串
     */
    public static function getLocationString($ip) {
        $location = self::getLocation($ip);

        $parts = array_filter([
            $location['regionName'],
            $location['city']
        ], function($v) { return $v !== '未知' && !empty($v); });

        return implode('-', $parts) ?: '未知';
    }
}
