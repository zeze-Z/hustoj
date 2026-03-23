<?php require_once("admin-header.php");

  if(isset($OJ_LANG)){
    require_once("../lang/$OJ_LANG.php");
  }
  $path_fix="../";
  $OJ_TP=$OJ_TEMPLATE;
  $OJ_TEMPLATE="bs3";
?>
<html>
<head>
<title><?php echo $MSG_ADMIN?></title>
<link rel="stylesheet" href="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE/"?>bootstrap-theme.min.css">
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE/"?>jquery.min.js"></script>

<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE/"?>bootstrap.min.js"></script>

<style>
    /* 现代化样式优化 */
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
    }
    
    /* 侧边栏样式 */
    #sidebar {
        position: fixed;
        top: 0;
        left: 0;
        width: 240px;
        height: 100vh;
        background-color: #ffffff;
        border-right: 1px solid #eaecef;
        padding: 20px 0;
        overflow-y: auto;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }
    
    .sidebar-content {
        padding: 0 20px;
    }
    
    h1 {
        color: #24292e;
        font-size: 18px;
        margin-bottom: 20px;
        text-align: center;
        padding-bottom: 10px;
        border-bottom: 1px solid #eaecef;
    }
    
    .btn {
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .btn-sm {
        padding: 8px 16px;
        font-size: 13px;
    }
    
    .btn-secondary {
        background-color: #f6f8fa;
        border: 1px solid #d1d5da;
        color: #24292e;
        width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: left;
        margin-bottom: 8px;
    }
    
    .btn-secondary:hover {
        background-color: #e1e4e8;
        color: #24292e;
    }
    
    .btn-group-vertical {
        width: 100%;
    }
    
    .btn-group {
        width: 100%;
        margin-bottom: 8px;
    }
    
    .dropdown-menu {
        width: 100%;
        background-color: #ffffff;
        border: 1px solid #d1d5da;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .dropdown-item {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #24292e;
        padding: 8px 16px;
        transition: all 0.3s ease;
    }
    
    .dropdown-item:hover {
        background-color: #f6f8fa;
        color: #0366d6;
    }
    
    .divider {
        margin: 15px 0;
        border-top: 1px solid #eaecef;
    }
    
    .external-links {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eaecef;
    }
    
    .external-links .btn {
        display: block;
        margin-bottom: 8px;
        background-color: #f6f8fa;
        color: #24292e;
        border: 1px solid #d1d5da;
    }
    
    .external-links .btn:hover {
        background-color: #e1e4e8;
        color: #24292e;
    }
    
    .admin-links {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eaecef;
    }
    
    .admin-links a {
        display: block;
        color: #586069;
        text-decoration: none;
        padding: 5px 0;
        font-size: 12px;
        transition: all 0.3s ease;
    }
    
    .admin-links a:hover {
        color: #0366d6;
    }
    
    /* 主内容区域 */
    .main-content {
        margin-left: 240px;
        min-height: 100vh;
        padding: 20px;
    }
    
    /* 响应式设计 */
    @media (max-width: 768px) {
        #sidebar {
            width: 200px;
        }
        .main-content {
            margin-left: 200px;
        }
    }
    
    @media (max-width: 480px) {
        #sidebar {
            width: 100%;
            height: auto;
            position: relative;
            border-right: none;
            border-bottom: 1px solid #eaecef;
        }
        .main-content {
            margin-left: 0;
        }
    }
</style>
</head>

<body>
    <div id="sidebar">
        <div class="sidebar-content">
            <h1><i class="glyphicon glyphicon-star-empty"></i> <?php echo $MSG_ADMIN?></h1>
            
            <a class='btn btn-sm btn-secondary' href="../status.php" target="_top" title="<?php echo $MSG_HELP_SEEOJ?>"><i class="glyphicon glyphicon-eye"></i> <?php echo $MSG_SEEOJ?></a>
            
            <div class="divider"></div>
            
            <div class="btn-group-vertical" role="menu">

                <div class="btn-group" role="menu">
                    <button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="glyphicon glyphicon-volume-up"></i> <?php echo $MSG_NEWS."-".$MSG_ADMIN ?> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">
    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])){?>
                        <?php if ($OJ_TP=="bs3"){?>
                            <a class="dropdown-item btn-sm" href="setmsg.php" target="main" title="<?php echo $MSG_HELP_SETMESSAGE?>"><i class="glyphicon glyphicon-edit"></i> <?php echo $MSG_NEWS."-".$MSG_SETMESSAGE?></a>
                        <?php }?>
                        <a class="dropdown-item btn-sm" href="news_list.php" target="main" title="<?php echo $MSG_HELP_NEWS_LIST?>"><i class="glyphicon glyphicon-list"></i> <?php echo $MSG_NEWS."-".$MSG_LIST?></a>
                        <a class="dropdown-item btn-sm" href="news_add_page.php" target="main" title="<?php echo $MSG_HELP_ADD_NEWS?>"><i class="glyphicon glyphicon-plus"></i> <?php echo $MSG_NEWS."-".$MSG_ADD?></a>
    <?php }?>
                    </div>
                </div>
                <div class="btn-group" role="menu">
                    <button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="glyphicon glyphicon-user"></i> <?php echo $MSG_USER."-".$MSG_ADMIN ?> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">
    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset( $_SESSION[$OJ_NAME.'_'.'password_setter'])){?>
                        <a class="dropdown-item btn-sm" href="user_list.php" target="main" title="<?php echo $MSG_HELP_USER_LIST?>"><i class="glyphicon glyphicon-list"></i> <?php echo $MSG_USER."-".$MSG_LIST?></a>
    <?php }?>
    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'user_adder'])){?>
                        <a class="dropdown-item btn-sm" href="user_add.php" target="main" title="<?php echo $MSG_HELP_USER_ADD?>"><i class="glyphicon glyphicon-plus"></i> <?php echo $MSG_USER."-".$MSG_ADD?></a>
                        <a class="dropdown-item btn-sm" href="user_import.php" target="main" title="<?php echo $MSG_HELP_USER_IMPORT ?>"><i class="glyphicon glyphicon-upload"></i> <?php echo $MSG_USER."-".$MSG_IMPORT?></a>

    <?php }?>
    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset( $_SESSION[$OJ_NAME.'_'.'password_setter'])){?>
                        <a class="dropdown-item btn-sm" href="changepass.php" target="main" title="<?php echo $MSG_HELP_SETPASSWORD?>"><i class="glyphicon glyphicon-lock"></i> <?php echo $MSG_USER."-".$MSG_SETPASSWORD?></a>
    <?php }?>
    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])){?>
                        <a class="dropdown-item btn-sm" href="privilege_list.php" target="main" title="<?php echo $MSG_HELP_PRIVILEGE_LIST?>"><i class="glyphicon glyphicon-list-alt"></i> <?php echo $MSG_USER."-".$MSG_PRIVILEGE."-".$MSG_LIST?></a>
                        <a class="dropdown-item btn-sm" href="privilege_add.php" target="main" title="<?php echo $MSG_HELP_ADD_PRIVILEGE?>"><i class="glyphicon glyphicon-plus-sign"></i> <?php echo $MSG_USER."-".$MSG_PRIVILEGE."-".$MSG_ADD?></a>
    <?php }?>
                    </div>
                </div>

                <div class="btn-group" role="menu">
                    <button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="glyphicon glyphicon-education"></i> <?php echo $MSG_SCHOOL."-".$MSG_ADMIN ?> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">
    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])){?>
                        <a class="dropdown-item btn-sm" href="school_list.php" target="main" title="<?php echo $MSG_SCHOOL."-".$MSG_LIST?>"><i class="glyphicon glyphicon-list"></i> <?php echo $MSG_SCHOOL."-".$MSG_LIST?></a>
                        <a class="dropdown-item btn-sm" href="school_add.php" target="main" title="<?php echo $MSG_ADD." ".$MSG_SCHOOL?>"><i class="glyphicon glyphicon-plus"></i> <?php echo $MSG_ADD." ".$MSG_SCHOOL?></a>
    <?php }?>
                    </div>
                </div>

                <div class="btn-group" role="menu">
                    <button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="glyphicon glyphicon-question-sign"></i> <?php echo $MSG_PROBLEM."-".$MSG_ADMIN ?> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">
    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'problem_editor'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])) {?>
                        <a class="dropdown-item btn-sm" href="problem_list.php" target="main" title="<?php echo $MSG_HELP_PROBLEM_LIST?>"><i class="glyphicon glyphicon-list"></i> <?php echo $MSG_PROBLEM."-".$MSG_LIST?></a>
    <?php }
          if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'problem_editor'])) {?>
                        <a class="dropdown-item btn-sm" href="problem_add_page.php" target="main" title="<?php echo html_entity_decode($MSG_HELP_ADD_PROBLEM)?>"><i class="glyphicon glyphicon-plus"></i> <?php echo $MSG_PROBLEM."-".$MSG_ADD?></a>
    <?php }
          if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'problem_importer'])) {?>
                        <a class="dropdown-item btn-sm" href="problem_import.php" target="main" title="<?php echo $MSG_HELP_IMPORT_PROBLEM?>"><i class="glyphicon glyphicon-import"></i> <?php echo $MSG_PROBLEM."-".$MSG_IMPORT?></a>
                        <a class="dropdown-item btn-sm" href="problem_export.php" target="main" title="<?php echo $MSG_HELP_EXPORT_PROBLEM?>"><i class="glyphicon glyphicon-export"></i> <?php echo $MSG_PROBLEM."-".$MSG_EXPORT?></a>
    <?php }?>
                    </div>
                </div>


                <div class="btn-group" role="menu">
                    <button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="glyphicon glyphicon-flag"></i> <?php echo $MSG_CONTEST."-".$MSG_ADMIN ?> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">
    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])||isset($_SESSION[$OJ_NAME.'_'.'contest_creator'])){?>
                        <a class="dropdown-item btn-sm" href="contest_list.php" target="main"  title="<?php echo $MSG_HELP_CONTEST_LIST?>"><i class="glyphicon glyphicon-list"></i> <?php echo $MSG_CONTEST."-".$MSG_LIST?></a>
                        <a class="dropdown-item btn-sm" href="contest_add.php" target="main"  title="<?php echo $MSG_HELP_ADD_CONTEST?>"><i class="glyphicon glyphicon-plus"></i> <?php echo $MSG_CONTEST."-".$MSG_ADD?></a>
                        <a class="dropdown-item btn-sm" href="user_set_ip.php" target="main" title="<?php echo $MSG_HELP_SET_LOGIN_IP?>"><i class="glyphicon glyphicon-check"></i> <?php echo $MSG_CONTEST."-".$MSG_SET_LOGIN_IP?></a>
                        <a class="dropdown-item btn-sm" href="team_generate.php" target="main" title="<?php echo $MSG_HELP_TEAMGENERATOR?>"><i class="glyphicon glyphicon-share"></i> <?php echo $MSG_CONTEST."-".$MSG_TEAMGENERATOR?></a>
                        <a class="dropdown-item btn-sm" href="team_generate2.php" target="main" title="<?php echo $MSG_HELP_TEAMGENERATOR?>"><i class="glyphicon glyphicon-share"></i> <?php echo $MSG_CONTEST."-".$MSG_TEAMGENERATOR?></a>
                        <a class="dropdown-item btn-sm" href="offline_import.php" target="main" title="<?php echo $MSG_IMPORT.$MSG_CONTEST ?>"><i class="glyphicon glyphicon-import"></i> <?php echo $MSG_CONTEST."-".$MSG_IMPORT ?></a>
    <?php }?>
                    </div>
                </div>

    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])){?>
                <div class="btn-group" role="menu">
                    <button type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="glyphicon glyphicon-leaf"></i> <?php echo $MSG_SYSTEM."-".$MSG_ADMIN ?> <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu">
                        <a class="dropdown-item btn-sm" href="rejudge.php" target="main" title="<?php echo $MSG_HELP_REJUDGE?>"><i class="glyphicon glyphicon-repeat"></i> <?php echo $MSG_SYSTEM."-".$MSG_REJUDGE?></a>      
                        <a class="dropdown-item btn-sm" href="source_give.php" target="main" title="<?php echo $MSG_HELP_GIVESOURCE?>"><i class="glyphicon glyphicon-random"></i> <?php echo $MSG_SYSTEM."-".$MSG_GIVESOURCE?></a>
                        <a class="dropdown-item btn-sm" href="../online.php" target="main"><i class="glyphicon glyphicon-globe"></i> <?php echo $MSG_SYSTEM."-".$MSG_HELP_ONLINE?></a>      
                        <a class="dropdown-item btn-sm" href="update_db.php" target="main" title="<?php echo $MSG_HELP_UPDATE_DATABASE?>"><i class="glyphicon glyphicon-hdd"></i> <?php echo $MSG_SYSTEM."-".$MSG_UPDATE_DATABASE?></a>
                        <a class="dropdown-item btn-sm" href="backup.php" target="main" title="<?php echo $MSG_HELP_BACKUP_DATABASE?>"><i class="glyphicon glyphicon-folder-close"></i> <?php echo $MSG_SYSTEM."-".$MSG_BACKUP_DATABASE?></a>
                        <a class="dropdown-item btn-sm" href="ranklist_export.php" target="main" title="<?php echo $MSG_EXPORT.$MSG_RANKLIST ?>"><i class="glyphicon glyphicon-export"></i> <?php echo  $MSG_EXPORT.$MSG_RANKLIST ?></a>
                    
                    </div>
                </div>
    <?php }?>

            </div>

    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])){?>
            <div class="external-links">
<!--                 <a class='btn btn-sm' href="https://github.com/zhblue/hustoj/" target="_blank"><i class="fab glyphicon-github"></i> HUSTOJ</a> -->
<!--                 <a class='btn btn-sm' href="https://yuanqi.tencent.com/agent/jADpOEWqLvTv" target="_blank"><i class="glyphicon glyphicon-robot"></i> 小张老师(AI-help)</a> -->
                <?php if(isset($OJ_REMOTE_JUDGE)&&$OJ_REMOTE_JUDGE){ ?>
                      <a class='btn btn-sm' href="https://www.ssoier.cn/api/" target="_blank"><i class="glyphicon glyphicon-link"></i> 一本通远程账户管理</a>
                <?php } ?>
<!--                 <a class='btn btn-sm' href="https://mp.weixin.qq.com/s?__biz=MzI1MTAwMTI2NA==&mid=2656403287&idx=1&sn=2b1b9a5cd0b271aa4a050c349981e715" target="_blank"><i class="glyphicon glyphicon-book-open"></i> 二次开发教程</a> -->
            </div>
    <?php }?>

    <?php if (isset($_SESSION[$OJ_NAME.'_'.'administrator'])&&!$OJ_SAE){?>
            <div class="admin-links">
<!--                 <a href="solution_statistics.php" target="main" title="Create your own data">SS Report</a> -->
<!--                 <a href="problem_copy.php" target="main" title="Create your own data">CopyProblem</a> -->
<!--                 <a href="problem_changeid.php" target="main" title="Danger,Use it on your own risk">ReOrderProblem</a> -->
            </div>
    <?php }?>
        </div>
    </div>
    
    <!-- 主内容区域 -->
    <div class="main-content">
        <!-- 这里可以添加主内容，但通常管理界面会在iframe中显示内容 -->
    </div>
</body>
</html>
