<?php $show_title="$MSG_SOURCE - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">
    <div style="margin-top: 0px; margin-bottom: 14px; padding-bottom: 0px; " >
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <div style="display: flex; align-items: center;">
                <i class="tags icon" style="font-size: 2em; color: #667eea; margin-right: 15px;"></i>
                <div>
                    <h1 style="margin: 0;"><?php echo $MSG_SOURCE?></h1>
                    <p style="margin: 5px 0 0 0; color: #888;">点击标签筛选相关题目</p>
                </div>
            </div>
            <a href="problemset.php" class="ui button primary">
                <i class="book icon"></i> 浏览全部题目
            </a>
        </div>
        <div class="ui segment" style="border-radius: 12px; padding: 25px;">
        <?php echo $view_category?>
        </div>
    </div>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
