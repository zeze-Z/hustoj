<?php
        require_once(dirname(__FILE__)."/db_info.inc.php");
        //cache head start
        $page_start_time=microtime(true);
        if(!isset($cache_time)) $cache_time=10;
        $OJ_APCU_OK = ( extension_loaded('apcu') && apcu_enabled() );
        $sid=$OJ_NAME.$_SERVER["HTTP_HOST"];
        $OJ_CACHE_SHARE=(isset($OJ_CACHE_SHARE)&&$OJ_CACHE_SHARE)&&!isset($_SESSION[$OJ_NAME.'_'.'administrator']);
        if (!$OJ_CACHE_SHARE&&isset($_SESSION[$OJ_NAME.'_'.'user_id'])){
                $ip = ($_SERVER['REMOTE_ADDR']);
                if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
                    $REMOTE_ADDR = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    $tmp_ip=explode(',',$REMOTE_ADDR);
                    $ip =(htmlentities($tmp_ip[0],ENT_QUOTES,"UTF-8"));
                }
                $sid.=session_id().$ip.$_SESSION[$OJ_NAME.'_'.'user_id'];
        }
        if (isset($_SERVER["REQUEST_URI"])){
                $sid.=$_SERVER["REQUEST_URI"];
        }

        $sid=md5($sid);
        $cache_file = "cache/cache_$sid.html";
        // ========== Phase3 安全防护（游客限频，在缓存判断前）==========
        if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])) {
            $guest_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $rate_limit_key = 'oj_rate_' . md5($guest_ip);
            $OJ_MEMCACHE_ENABLED = (isset($OJ_MEMCACHE) && $OJ_MEMCACHE);
            
            if ($OJ_MEMCACHE_ENABLED) {
                try {
                    $mem = new Memcached;
                    $mem->addServer($OJ_MEMSERVER, $OJ_MEMPORT);
                    $count = $mem->get($rate_limit_key);
                    if ($count === false) {
                        $mem->set($rate_limit_key, 1, 60);
                    } elseif ($count > 15) {
                        http_response_code(429);
                        echo '<html><body><h1>429 Too Many Requests</h1><p>访问过于频繁，请稍后再试。</p></body></html>';
                        exit;
                    } else {
                        $mem->increment($rate_limit_key);
                    }
                } catch (Exception $e) {}
            }
            
            // 爬虫检测
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $bad_ua_patterns = ['python-requests', 'curl/', 'wget/', 'scrapy', 'httpclient', 'java/', 'node-fetch'];
            foreach ($bad_ua_patterns as $pattern) {
                if (stripos($ua, $pattern) !== false) {
                    http_response_code(403);
                    echo '<html><body><h1>403 Forbidden</h1><p>疑似自动化访问，请使用浏览器访问。</p></body></html>';
                    exit;
                }
            }
        }
        
        // 页面内容依赖实时用户状态（如购买后权限变化）时，设置 $OJ_SKIP_PAGE_CACHE 跳过页面缓存
        $skip_page_cache = isset($OJ_SKIP_PAGE_CACHE) && $OJ_SKIP_PAGE_CACHE;
        if ($skip_page_cache) {
                $use_cache = false;
        } elseif ($OJ_MEMCACHE || $OJ_APCU_OK) {
                    $success = false;
                    if( $OJ_APCU_OK ){
                            $content = apcu_fetch($cache_file, $success);
                    }else{
                            $mem = new Memcache;
                            $mem->connect($OJ_MEMSERVER,  $OJ_MEMPORT);
                            $content=$mem->get($cache_file);
                            $success=!empty($content);
                    }
                    if ($success) {
                        echo $content;
                        echo "<!-- cached -->";
                        exit();
                    } else {
                        $use_cache = false;
                        $write_cache = true;
                    }
        }else{

                if (file_exists ( $cache_file ))
                        $last = filemtime ( $cache_file );
                else
                        $last =0;
                $use_cache=(time () - $last < $cache_time);

        }
        if ($use_cache) {
                //header ( "Location: $file" );
                echo file_get_contents($cache_file);
                exit ();
        } else {
                ob_start ();
        }
