<?php
/**
 * IP归属地查询工具类
 * 基于免费API，无需API Key，多源fallback
 * 支持批量查询，自动处理内网IP
 */
class IpLocation
{
    /**
     * 判断是否为内网IP
     */
    public static function isPrivateIp($ip)
    {
        return preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.|127\.)/', $ip);
    }

    /**
     * 发起HTTP请求：优先curl，fallback file_get_contents
     */
    private static function httpGet($url)
    {
        // 优先curl
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($result !== false && $httpCode === 200) {
                return $result;
            }
        }
        // fallback file_get_contents
        $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
        $result = @file_get_contents($url, false, $ctx);
        return ($result !== false) ? $result : false;
    }

    /**
     * 通过ip-api.com查询IP归属地
     */
    private static function queryIpApi($ip)
    {
        // 注意：ip-api.com 的 HTTPS 仅对付费(Pro)用户开放，免费版会返回 403，
        // 因此该源置于 fallback 链末尾；Pro 用户可在 URL 中追加 &key=YOUR_API_KEY
        $url = "https://ip-api.com/json/{$ip}?lang=zh-CN&fields=status,country,regionName,city";
        $raw = self::httpGet($url);
        if ($raw === false) return null;
        $data = json_decode($raw, true);
        if (!is_array($data) || $data['status'] !== 'success') return null;

        $country = $data['country'] ?? '';
        $region  = $data['regionName'] ?? '';
        $city    = $data['city'] ?? '';

        // 直辖市/特别行政区：regionName已包含城市信息（如"北京市"），不再重复拼接city
        if ($region && preg_match('/(?:市|省|自治区|特别行政区)$/u', $region)) {
            return $country . $region;
        }
        // 普通省份：拼接 country + region + city
        $parts = array_filter([$country, $region, $city]);
        return implode('', $parts) ?: null;
    }

    /**
     * 通过ip.sb查询IP归属地
     */
    private static function queryIpSb($ip)
    {
        $url = "https://api.ip.sb/geoip/{$ip}";
        $raw = self::httpGet($url);
        if ($raw === false) return null;
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['country'])) return null;
        $parts = array_filter([$data['country'], $data['region'], $data['city']]);
        return implode('', $parts) ?: null;
    }

    /**
     * 通过ipwho.is查询IP归属地
     */
    private static function queryIpWhoIs($ip)
    {
        $url = "https://ipwho.is/{$ip}?lang=zh-CN";
        $raw = self::httpGet($url);
        if ($raw === false) return null;
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['success']) || empty($data['country'])) return null;

        $country = $data['country'] ?? '';
        $region  = $data['region'] ?? '';
        $city    = $data['city'] ?? '';

        // 直辖市/特别行政区：region 已包含城市信息（如 region="北京市" city="北京"），不重复拼接 city
        if ($region && (empty($city) || strpos($region, $city) !== false || preg_match('/(?:市|特别行政区)$/u', $region))) {
            $parts = array_filter([$country, $region]);
        } else {
            $parts = array_filter([$country, $region, $city]);
        }
        return implode('', $parts) ?: null;
    }

    /**
     * 查询单个IP归属地（多源fallback + 内存缓存）
     * @param string $ip
     * @return string 归属地，失败返回 "未知"
     */
    public static function getLocation($ip)
    {
        if (self::isPrivateIp($ip)) {
            return '内网';
        }

        static $cache = [];
        if (isset($cache[$ip])) {
            return $cache[$ip];
        }

        // 多源fallback：全部使用HTTPS，优先免费且返回中文的源
        $methods = ['queryIpWhoIs', 'queryIpSb', 'queryIpApi'];
        foreach ($methods as $method) {
            $location = self::$method($ip);
            if ($location) {
                $cache[$ip] = $location;
                return $location;
            }
        }

        $cache[$ip] = '未知';
        return '未知';
    }

    /**
     * 批量查询IP归属地（带速率限制）
     * 各免费接口均有频率限制，保守按45次/分钟限流
     * @param array $ips
     * @return array [ip => location]
     */
    public static function batchGetLocation($ips)
    {
        $results = [];
        $api_count = 0;
        $window_start = time();
        $limit = 45;

        foreach ($ips as $ip) {
            if (self::isPrivateIp($ip)) {
                $results[$ip] = self::getLocation($ip);
                continue;
            }

            if ($api_count >= $limit) {
                $elapsed = time() - $window_start;
                if ($elapsed < 60) {
                    sleep(60 - $elapsed + 1);
                }
                $window_start = time();
                $api_count = 0;
            }

            $results[$ip] = self::getLocation($ip);
            $api_count++;
        }

        return $results;
    }
}
