<?php
        require_once(dirname(__FILE__)."/../../include/memcache.php");
        function checkmail(){  // check if has mail
          global $OJ_NAME;
          $sql="select count(1) cnt FROM `mail` WHERE new_mail=1 AND `to_user`=?";
          $result=pdo_query($sql,$_SESSION[$OJ_NAME.'_'.'user_id']);
          if(empty($result)) return false;
          $row=$result[0];
          //if(intval($row[0])==0) return false;
          $retmsg="<span id=red>(".$row['cnt'].")</span>";
          return $retmsg;
        }

        function get_menu_news() {
            $result = "";
            $sql_news_menu = "select `news_id`,`title` FROM `news` WHERE `menu`=1 AND `title`!='faqs.cn' ORDER BY `importance` ASC,`time` DESC LIMIT 10";
            $sql_news_menu_result = mysql_query_cache( $sql_news_menu );
            if ( $sql_news_menu_result ) {
                foreach ( $sql_news_menu_result as $row ) {
                    $result .= '<a class="item" href="/viewnews.php?id=' . $row['news_id'] . '">' ."<i class='star icon'></i>" . $row['title'] . '</a>';
                }
            }
            return $result;
        }
        $url=basename($_SERVER['REQUEST_URI']);
        $dir=basename(getcwd());
        if($dir=="discuss3") $path_fix="../";
        else $path_fix="";

        // 强制登录检查
        if(isset($OJ_NEED_LOGIN)&&$OJ_NEED_LOGIN&&(
                  $url!='loginpage.php'&&
                  $url!='lostpassword.php'&&
                  $url!='lostpassword2.php'&&
                  $url!='registerpage.php'
                  ) && !isset($_SESSION[$OJ_NAME.'_'.'user_id'])){

           header("location:".$path_fix."loginpage.php");
           exit();
        }

        // 游客访问白名单 - 只允许访问只读页面
        $guest_whitelist = [
            'index.php',          // 首页
            'problemset.php',     // 题目列表
            'problem.php',        // 题目详情（只读）
            'category.php',       // 题目分类
            'contest.php',        // 比赛列表
            'viewnews.php',       // 新闻详情
            'faqs.php',          // 常见问题
            'registerpage.php',   // 注册页
            'loginpage.php',      // 登录页
            'lostpassword.php',    // 找回密码
            'lostpassword2.php'   // 找回密码第二步
        ];

        // 游客模式访问限制
        if(isset($OJ_GUEST) && $OJ_GUEST && !isset($_SESSION[$OJ_NAME.'_'.'user_id']) && !in_array($url, $guest_whitelist)) {
            // 游客尝试访问非白名单页面，引导到登录页
            header("location:".$path_fix."loginpage.php");
            exit();
        }

        if($OJ_ONLINE){
                require_once($path_fix.'include/online.php');
                $on = new online();
        }

        $sql_news_menu_result_html = "";

        if ($OJ_MENU_NEWS) {
            if ($OJ_REDIS) {
                $redis = new Redis();
                $redis->connect($OJ_REDISSERVER, $OJ_REDISPORT);

                if (isset($OJ_REDISAUTH)) {
                  $redis->auth($OJ_REDISAUTH);
                }
                $redisDataKey = $OJ_REDISQNAME . '_MENU_NEWS_CACHE';
                if ($redis->exists($redisDataKey)) {
                    $sql_news_menu_result_html = $redis->get($redisDataKey);
                } else {
                    $sql_news_menu_result_html = get_menu_news();
                    $redis->set($redisDataKey, $sql_news_menu_result_html);
                    $redis->expire($redisDataKey, 300);
                }

                $redis->close();
            } else {
                $sessionDataKey = $OJ_NAME.'_'."_MENU_NEWS_CACHE";
                if (isset($_SESSION[$sessionDataKey])) {
                    $sql_news_menu_result_html = $_SESSION[$sessionDataKey];
                } else {
                    $sql_news_menu_result_html = get_menu_news();
                    $_SESSION[$sessionDataKey] = $sql_news_menu_result_html;
                }
            }
        }
?>

<!DOCTYPE html>
<html lang="cn" style="position:fixed; width: 100%; overflow: hidden; ">

<head>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=0.5">
    <title><?php echo $show_title ?></title>
    <?php include(dirname(__FILE__)."/css.php");?>
        <style>
/* 简洁菜单栏样式 */
#page-header {
    border-bottom: none !important;
}
#page-header .item {
    color: #fff !important;
    padding: 0 16px !important;
    line-height: 60px !important;
    transition: all 0.3s ease !important;
    position: relative !important;
}
#page-header .item:hover {
    color: #fff !important;
    opacity: 1 !important;
    background: transparent !important;
}
#page-header .item:hover::after {
    content: '' !important;
    position: absolute !important;
    bottom: 0 !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: 20px !important;
    height: 3px !important;
    background: #fff !important;
    border-radius: 2px !important;
}
#page-header .item.active {
    color: #fff !important;
    font-weight: 600 !important;
    opacity: 1 !important;
}
#page-header .item.active::after {
    content: '' !important;
    position: absolute !important;
    bottom: 0 !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: 20px !important;
    height: 3px !important;
    background: #fff !important;
    border-radius: 2px !important;
}
#page-header .header.item {
    border-right: none !important;
    color: #fff !important;
}
#page-header .header.item:hover {
    color: #fff !important;
    opacity: 0.8 !important;
}
#page-header .header.item:hover::after {
    display: none !important;
}
/* 下拉菜单样式 */
#page-header .dropdown .menu {
    margin-top: 0 !important;
    border-radius: 4px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    border: 1px solid #e8e8e8 !important;
}
#page-header .dropdown .menu .item {
    line-height: 40px !important;
    padding: 0 20px !important;
    color: #333 !important;
    font-size: 14px !important;
}
#page-header .dropdown .menu .item:hover {
    background: #f5f5f5 !important;
    color: #1890ff !important;
}
#page-header .dropdown .menu .divider {
    margin: 8px 0 !important;
    height: 1px !important;
    background: #e0e0e0 !important;
    border: none !important;
}
/* 右侧按钮样式 */
#page-header .right.menu .button {
    border-radius: 4px !important;
    font-weight: 500 !important;
    padding: 8px 20px !important;
}
#page-header .right.menu .ui.primary.button {
    background: #1890ff !important;
}
#page-header .right.menu .ui.primary.button:hover {
    background: #40a9ff !important;
}
/* 响应式调整 */
@media (max-width: 991px) {
    .mobile-only {
        display:block !important;
    }
    .desktop-only {
        display:none !important;
    }
}
</style>

    <script src="<?php echo "$OJ_CDN_URL/include/"?>jquery-latest.js"></script>

<!-- Scripts -->
<script>
    // console.log('\n %c HUSTOJ %c https://github.com/zhblue/hustoj %c\n', 'color: #fadfa3; background: #000000; padding:5px 0;', 'background: #fadfa3; padding:5px 0;', '');
    // console.log('\n %c Theme By %c Baoshuo ( @renbaoshuo ) %c https://baoshuo.ren %c\n', 'color: #fadfa3; background: #000000; padding:5px 0;', 'background: #fadfa3; padding:5px 0;', 'background: #ffbf33; padding:5px 0;', '');
    // console.log('\n GitHub Homepage: https://github.com/zhblue/hustoj \n Document: https://zhblue.github.io/hustoj \n Bug report URL: https://github.com/zhblue/hustoj/issues \n \n%c ★ Please give us a star on GitHub! ★ %c \n', 'color: red;', '')
    
    // AI体验弹窗函数
    function openAIExperience() {
        // 直接在新标签页打开文心一言，解决iframe无法上传图片的问题
        window.open('https://yiyan.baidu.com/', '_blank');
    }
    
    // 手势识别弹窗函数
    function openGestureRecognition() {
        // 创建弹窗容器
        var gestureModal = document.createElement('div');
        gestureModal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999999; display: flex; justify-content: center; align-items: center;';
        
        // 创建弹窗内容
        var gestureContent = document.createElement('div');
        gestureContent.style.cssText = 'width: 90%; height: 90%; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 0 20px rgba(0,0,0,0.3);';
        
        // 创建关闭按钮
        var closeBtn = document.createElement('button');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = 'position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; width: 30px; height: 30px; font-size: 20px; cursor: pointer; z-index: 1000000;';
        closeBtn.onclick = function() {
            document.body.removeChild(gestureModal);
        };
        
        // 创建iframe
        var gestureIframe = document.createElement('iframe');
        gestureIframe.src = 'https://aitools.techsong.cn/gesture-recognition.php';
        gestureIframe.style.cssText = 'width: 100%; height: 100%; border: none;';
        
        // 组装弹窗
        gestureContent.appendChild(gestureIframe);
        gestureModal.appendChild(gestureContent);
        gestureModal.appendChild(closeBtn);
        
        // 添加到页面
        document.body.appendChild(gestureModal);
        
        // 点击弹窗外部关闭
        gestureModal.onclick = function(e) {
            if (e.target === gestureModal) {
                document.body.removeChild(gestureModal);
            }
        };
    }
    
</script>
</head>

<?php
        if(!isset($_GET['spa'])){
?>
   
<body id="MainBg-C" style="position: relative; margin-top: 60px; height: calc(100% - 60px); overflow-y: overlay;">
    <div id="page-header" class="ui fixed borderless menu" style="position: fixed; height: 60px; z-index:99999; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
        <div id="menu" class="ui stackable mobile ui container computer" style="margin-left:auto;margin-right:auto; max-width: 1200px;">
            <a class="header item" href="/" style="font-size: 1.3em; font-weight: 600; color: #fff !important; padding: 0 20px;">
                <?php echo $domain==$DOMAIN?$OJ_NAME:ucwords($OJ_NAME)."'s OJ"?>
            </a>
            
          <?php if(isset($_SESSION[$OJ_NAME.'_'.'user_id'])) { ?>
            <?php
            if(isset($OJ_AI_HTML)&&$OJ_AI_HTML && !isset($OJ_ON_SITE_CONTEST_ID) ) echo $OJ_AI_HTML;
            else echo '<a class="desktop-only item" href="index.php" style="font-weight: 500;">'.$MSG_HOME.'</a>';
            if(file_exists("moodle")){  // 如果存在moodle目录，自动添加链接
              echo '<a class="item" href="moodle" style="font-weight: 500;">Moodle</a>';
            }
         //     if(file_exists("hello")){  // 如果存在hello目录，自动添加链接
        //       echo '<a class="item" onclick=\'window.open("/hello/index.html", "_blank",
        // "width=600,height=850,left=" + (window.screen.width-600)  + ",top=0,toolbar=no,menubar=no,location=no,status=no,resizable=yes");\'><span class="desktop-only">Hello算法</span></a>';
        //     }

                
             if( !isset($OJ_ON_SITE_CONTEST_ID) && (!isset($_GET['cid'])||$cid==0) ){
          ?>
            <a class="item <?php if ($url=="problemset.php") echo "active";?>" href="<?php echo $path_fix?>problemset.php" style="font-weight: 500;"><?php echo $MSG_PROBLEMS?></a>
            <a class="item <?php if ($url=="category.php") echo "active";?>" href="<?php echo $path_fix?>category.php" style="font-weight: 500;"><?php echo $MSG_SOURCE?></a>
            <a class="item <?php if ($url=="contest.php") echo "active";?>" href="<?php echo $path_fix?>contest.php<?php if(isset($_SESSION[$OJ_NAME."_user_id"])) echo "?my" ?>" style="font-weight: 500;"><?php echo $MSG_CONTEST?></a>
            <a class="item <?php if ($url=="status.php") echo "active";?>" href="<?php echo $path_fix?>status.php" style="font-weight: 500;"><?php echo $MSG_STATUS?></a>
            <a class="item <?php if ($url=="ranklist.php") echo "active";?>" href="<?php echo $path_fix?>ranklist.php" style="font-weight: 500;"><?php echo $MSG_RANKLIST?></a>
<?php if(isset($OJ_RECENT_CONTEST)&&$OJ_RECENT_CONTEST){    ?>
            <a class="item <?php if ($url=="recent-contest.php") echo "active";?>" href="<?php echo $path_fix?>recent-contest.php" style="font-weight: 500;"><?php echo $MSG_RECENT_CONTEST?></a>
<?php } ?>
            <a class="item <?php if ($url=="faqs.php") echo "active";?>" href="<?php echo $path_fix?>faqs.php" style="font-weight: 500;"><?php echo $MSG_FAQ?></a>
            <?php if (isset($OJ_BBS)&& $OJ_BBS){ ?>
                <a class='item' href="discuss.php" style="font-weight: 500;"><?php echo $MSG_BBS?></a>
            <?php } ?>
            <!-- 更多功能 -->
            <a class="item <?php if ($url=="more.php") echo "active";?>" href="<?php echo $path_fix?>more.php" style="font-weight: 500;">更多</a>
            <?php }
            if( isset($_GET['cid']) && intval($_GET['cid'])>0 ){
                     $cid=intval($_GET['cid']);
                     if(!isset($OJ_ON_SITE_CONTEST_ID)){   ?>
                            <a id="" class="item" href="<?php echo $path_fix?>contest.php" ><i class="arrow left icon"></i><span class="desktop-only"><?php echo $MSG_CONTEST.$MSG_LIST?></span></a>
            <?php    }      ?>
            <a id="" class="item active" href="<?php echo $path_fix?>contest.php?cid=<?php echo $cid?>" ><i class="list icon"></i><span class="desktop-only"><?php echo $MSG_PROBLEMS.$MSG_LIST?></span></a>
            <a id="" class="item active" href="<?php echo $path_fix?>status.php?cid=<?php echo $cid?>" ><i class="tasks icon"></i><span class="desktop-only"><?php echo $MSG_STATUS.$MSG_LIST?></span></a>
            <a id="" class="item active" href="<?php echo $path_fix?>contestrank.php?cid=<?php echo $cid?>" ><i class="numbered list icon"></i><span class="desktop-only"><?php echo $MSG_RANKLIST?></span></a>
            <a id="" class="item active" href="<?php echo $path_fix?>contestrank-oi.php?cid=<?php echo $cid?>" ><i class="child icon"></i><span class="desktop-only">OI-<?php echo $MSG_RANKLIST?></span></a>
            <?php if (isset($OJ_BBS)&& $OJ_BBS){ ?>
                  <a class='item active' href="discuss.php?cid=<?php echo $cid?>"><i class="clipboard icon"></i> <span class="desktop-only"><?php echo $MSG_BBS?></span></a>
             <?php } ?>

                    <?php if(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])||isset($_SESSION[$OJ_NAME.'_'.'problem_editor'])){ ?>
                            <a id="" class="item active" href="<?php echo $path_fix?>conteststatistics.php?cid=<?php echo $cid?>" ><i class="eye icon"></i><span class="desktop-only"><?php echo $MSG_STATISTICS?></span></a>
                    <?php }  ?>
            <?php }  ?>
            <?php
                if($OJ_MENU_DROPDOWN){
            ?>
            <div class="ui simple dropdown item">
                        <i class="book icon"></i><span class='desktop-only'>学习资料</span><i class="dropdown icon"></i>
                        <div class="menu">
            <?php  } ?>
            <?php echo $sql_news_menu_result_html; ?>
            <?php
            if($OJ_MENU_DROPDOWN){
                ?>
                        </div>
            </div>
            <?php } ?>
            <?php } ?>

            <div class="right menu">
                <?php if(isset($_SESSION[$OJ_NAME.'_'.'user_id'])) { ?>
                <a href="<?php echo $path_fix?>/userinfo.php?user=<?php echo $_SESSION[$OJ_NAME.'_'.'user_id']?>"
                    style="color: inherit; ">
                    <div class="ui simple dropdown item">
                        <?php echo $_SESSION[$OJ_NAME.'_'.'user_id']; 
                              if(!empty($_SESSION[$OJ_NAME.'_nick'])) echo "(".$_SESSION[$OJ_NAME.'_nick'].")";
                              if(!empty($_SESSION[$OJ_NAME.'_group_name'])) echo "[".$_SESSION[$OJ_NAME.'_group_name']."]";
                                      
                        ?>
                        <i class="dropdown icon"></i>
                        <div class="menu">
                                <a class="item" href="modifypage.php"><i class="edit icon"></i><?php echo $MSG_REG_INFO;?></a>
                                <a class="item" href="portal.php"><i class="tasks icon"></i><?php echo $MSG_TODO;?></a>
                                <?php if ($OJ_SaaS_ENABLE){ ?>
                                <?php if($_SERVER['HTTP_HOST']==$DOMAIN)
                                        echo  "<a class='item' href='http://".  $_SESSION[$OJ_NAME.'_'.'user_id'].".$DOMAIN'><i class='globe icon' ></i>MyOJ</a>";?>
                                <?php } ?>
                            <?php if(isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])||isset($_SESSION[$OJ_NAME.'_'.'user_adder'])||isset($_SESSION[$OJ_NAME.'_'.'password_setter'])||isset($_SESSION[$OJ_NAME.'_'.'problem_editor'])){ ?>
                            <a class="item" href="admin/"><i class="settings icon"></i><?php echo $MSG_ADMIN;?></a>
                            <?php }
if(isset($_SESSION[$OJ_NAME.'_'.'balloon'])){
  echo "<a class=item href='balloon.php'><i class='golf ball icon'></i>$MSG_BALLOON</a>";
}
                              if((isset($OJ_EXAM_CONTEST_ID)&&$OJ_EXAM_CONTEST_ID>0)||
                                     (isset($OJ_ON_SITE_CONTEST_ID)&&$OJ_ON_SITE_CONTEST_ID>0)||
                                     (isset($OJ_MAIL)&&!$OJ_MAIL)){
                                      // mail can not use in contest or mail is turned off
                              }else{
                                    $mail=checkmail();
                                    if($mail) echo "<a class='item mail' href=".$path_fix."mail.php><i class='mail icon'></i>$MSG_MAIL$mail</a>";
                              }




                            ?>
        <?php
        if(isset($OJ_PRINTER) && $OJ_PRINTER)
        {
        ?>
          <a  class="item"  href="printer.php">
            <i class="print icon"></i> <?php echo $MSG_PRINTER?>
          </a>
        <?php
        }
        ?>
                            <a class="item" href="logout.php"><i class="power icon"></i><?php echo $MSG_LOGOUT;?></a>
                        </div>
                    </div>
                </a>
                <?php } else { ?>


                <div class="item">
                    <a class="ui button" style="margin-right: 0.5em; " href="loginpage.php">
                       <?php echo $MSG_LOGIN?>
                    </a>
                    <?php if(isset($OJ_REGISTER)&&$OJ_REGISTER ){ ?>
                    <a class="ui primary button" href="registerpage.php">
                       <?php echo $MSG_REGISTER?>
                    </a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div style="margin-top: 0px; ">
        <div id="main" class="ui main container">
<?php } ?>
