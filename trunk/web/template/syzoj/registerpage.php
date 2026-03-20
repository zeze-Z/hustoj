<?php $show_title="$MSG_REG_INFO - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">
  <h1><?php echo $MSG_REG_INFO?></h1>
  <div class="ui error message" id="error" data-am-alert hidden>
    <p id="error_info"></p>
  </div>
          <form action="register.php" method="post" role="form" class="ui form">
                <div class="field">
                    <label for="username"><?php echo $MSG_USER_ID?>*</label>
                    <input name="user_id" class="form-control" placeholder="学生注册请用学号，老师注册请用手机号" type="text">
                </div>
                <div class="field">
                    <label for="username"><?php echo $MSG_NICK?>*</label>
                    <input name="nick" placeholder="教学系统建议用真名" type="text">
                </div>
                <div class="two fields">
                    <div class="field">
                    <label class="ui header"><?php echo $MSG_PASSWORD?>*</label>
                      <input name="password" placeholder="" type="password">
                    </div>
                    <div class="field">
                      <label class="ui header"><?php echo $MSG_REPEAT_PASSWORD?>*</label>
                      <input name="rptpassword" placeholder="" type="password">
                    </div>
                </div>
                <div class="field">
                    <label for="school"><?php echo $MSG_SCHOOL?>*</label>
                    <?php 
                    // 引入学校函数库
                    if (file_exists("./include/school.php")) {
                        require_once("./include/school.php");
                        $school_list = getSchoolList(true);
                    }
                    ?>
                    <select name="school_id" class="ui dropdown" required>
                        <option value="">请选择学校</option>
                        <?php if (!empty($school_list)): ?>
                            <?php foreach ($school_list as $school): ?>
                                <option value="<?php echo $school['id']; ?>"><?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="email"><?php echo $MSG_EMAIL?>*</label>
                    <input name="email" placeholder="用QQ邮箱可得QQ头像" type="text">
                </div>
                <?php if($OJ_VCODE){?>
                  <div class="field">
                    <label for="email"><?php echo $MSG_VCODE?>*</label>
                    <input name="vcode" class="form-control" placeholder="" type="text" autocomplete=off >
                    <img alt="click to change" src="vcode.php" onclick="this.src='vcode.php?'+Math.random()" height="30px">
                  </div>
                <?php }?>
                <button name="submit" type="submit" class="ui button"><?php echo $MSG_REGISTER; ?></button>
                <button name="submit" type="reset" class="ui button"><?php echo $MSG_RESET; ?></button>
            </form>
</div>
<?php include("template/$OJ_TEMPLATE/footer.php");?>