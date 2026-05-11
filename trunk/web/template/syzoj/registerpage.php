<?php $show_title="$MSG_REG_INFO - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">
  <h1><?php echo $MSG_REG_INFO?></h1>
  <!-- 平台简介引导文案 -->
  <div class="ui info message">
    <div class="header">欢迎加入<?php echo $OJ_NAME?>教学平台</div>
    <ul class="list">
      <li>📚 教师专属：课件中心、作业系统、学生管理、收益分成</li>
      <li>🎮 学生专属：海量题库、趣味编程、在线评测</li>
      <li>📧 请填写真实邮箱，用于接收账号激活通知</li>
    </ul>
  </div>
  <div class="ui error message" id="error" data-am-alert hidden>
    <p id="error_info"></p>
  </div>
          <form action="register.php" method="post" role="form" class="ui form">
                <!-- 角色选择 -->
                <div class="field">
                    <label>注册角色*</label>
                    <div class="inline fields">
                        <div class="field">
                            <div class="ui radio checkbox">
                                <input type="radio" name="role" value="teacher" id="role_teacher" checked>
                                <label for="role_teacher">我是一名教师</label>
                            </div>
                        </div>
                        <div class="field">
                            <div class="ui radio checkbox">
                                <input type="radio" name="role" value="student" id="role_student">
                                <label for="role_student">我是一名学生</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="field">
                    <label for="username"><?php echo $MSG_USER_ID?>*</label>
                    <input name="user_id" class="form-control" placeholder="学生注册请用学号，老师注册请用手机号" type="text" required>
                </div>
                <div class="field">
                    <label for="username"><?php echo $MSG_NICK?>*</label>
                    <input name="nick" placeholder="教学系统建议用真名" type="text" required>
                </div>
                <div class="two fields">
                    <div class="field">
                    <label class="ui header"><?php echo $MSG_PASSWORD?>*</label>
                      <input name="password" placeholder="" type="password" required>
                    </div>
                    <div class="field">
                      <label class="ui header"><?php echo $MSG_REPEAT_PASSWORD?>*</label>
                      <input name="rptpassword" placeholder="" type="password" required>
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
                        <option value="">请选择学校，如果没有，请联系客服</option>
                        <?php if (!empty($school_list)): ?>
                            <?php foreach ($school_list as $school): ?>
                                <option value="<?php echo $school['id']; ?>"><?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="email"><?php echo $MSG_EMAIL?>*</label>
                    <input name="email" placeholder="请填写真实邮箱，用于接收账号激活通知" type="text" required>
                </div>
                <?php if($OJ_VCODE){?>
                  <div class="field">
                    <label for="email"><?php echo $MSG_VCODE?>*</label>
                    <input name="vcode" class="form-control" placeholder="" type="text" autocomplete=off required>
                    <img alt="click to change" src="vcode.php" onclick="this.src='vcode.php?'+Math.random()" height="30px">
                  </div>
                <?php }?>
                <button name="submit" type="submit" class="ui button"><?php echo $MSG_REGISTER; ?></button>
                <button name="submit" type="reset" class="ui button"><?php echo $MSG_RESET; ?></button>
            </form>
</div>
<script>
$(document).ready(function(){
    // 初始化单选按钮
    $('.ui.radio.checkbox').checkbox();
});
</script>
<?php include("template/$OJ_TEMPLATE/footer.php");?>