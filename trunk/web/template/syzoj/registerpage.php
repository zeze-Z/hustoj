<?php $show_title="$MSG_REG_INFO - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<style>
/* ===== 注册页统一样式 ===== */
.register-page {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    background: #f0f2f5;
}

.register-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    width: 100%;
    max-width: 520px;
    overflow: hidden;
}

/* 顶部渐变条 */
.register-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    padding: 2rem 2rem 1.8rem;
    text-align: center;
    color: #fff;
}

.register-header h1 {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 0.4rem;
    color: #fff;
}

.register-header p {
    font-size: 0.95rem;
    opacity: 0.9;
}

/* 表单区域 */
.register-body {
    padding: 1.8rem 2rem 2rem;
}

/* 欢迎提示 */
.welcome-tip {
    background: linear-gradient(135deg, #EEF2FF, #F3E8FF);
    border-radius: 12px;
    padding: 1rem 1.2rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.welcome-tip .tip-icon {
    font-size: 1.8rem;
    flex-shrink: 0;
}

.welcome-tip .tip-text {
    font-size: 0.9rem;
    color: #555;
    line-height: 1.5;
}

.welcome-tip .tip-text strong {
    color: #667eea;
}

/* 错误提示 */
.error-msg {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: 10px;
    padding: 0.8rem 1rem;
    margin-bottom: 1.2rem;
    color: #DC2626;
    font-size: 0.9rem;
    display: none;
}

.error-msg.show {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* 表单字段 */
.form-group {
    margin-bottom: 1.2rem;
}

.form-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.4rem;
}

.form-group label .required {
    color: #EF4444;
    margin-left: 2px;
}

.form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 10px;
    font-size: 0.95rem;
    font-family: inherit;
    color: #333;
    background: #fff;
    transition: all 0.3s ease;
    outline: none;
}

.form-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input::placeholder {
    color: #9CA3AF;
}

/* 角色选择卡片 */
.role-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 1.2rem;
}

.role-card {
    position: relative;
    border: 2px solid #E5E7EB;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fff;
}

.role-card:hover {
    border-color: #C7D2FE;
    background: #FAFAFE;
}

.role-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.role-card input[type="radio"]:checked + .role-content {
    color: #667eea;
}

.role-card:has(input[type="radio"]:checked) {
    border-color: #667eea;
    background: linear-gradient(135deg, #EEF2FF, #F3E8FF);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
}

.role-content .role-icon {
    font-size: 1.8rem;
    margin-bottom: 0.3rem;
}

.role-content .role-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: inherit;
}

.role-content .role-desc {
    font-size: 0.75rem;
    color: #9CA3AF;
    margin-top: 0.2rem;
}

/* 下拉框 */
.form-select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #E5E7EB;
    border-radius: 10px;
    font-size: 0.95rem;
    font-family: inherit;
    color: #333;
    background: #fff;
    transition: all 0.3s ease;
    outline: none;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239CA3AF' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
}

.form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* 验证码行 */
.vcode-row {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

.vcode-row .form-input {
    flex: 1;
}

.vcode-row img {
    height: 44px;
    border-radius: 10px;
    cursor: pointer;
    border: 2px solid #E5E7EB;
    transition: border-color 0.3s;
}

.vcode-row img:hover {
    border-color: #667eea;
}

/* 提交按钮 */
.btn-register {
    width: 100%;
    padding: 0.85rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    margin-top: 0.5rem;
}

.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-register:active {
    transform: translateY(0);
}

/* 底部链接 */
.register-footer {
    text-align: center;
    padding: 0 2rem 1.5rem;
    font-size: 0.9rem;
    color: #6B7280;
}

.register-footer a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

.register-footer a:hover {
    text-decoration: underline;
}

/* 响应式 */
@media (max-width: 768px) {
    .register-page {
        padding: 1rem;
        align-items: flex-start;
    }
    .register-card {
        border-radius: 16px;
    }
    .register-header {
        padding: 1.5rem;
    }
    .register-body {
        padding: 1.5rem;
    }
    .role-selector {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="register-page">
  <div class="register-card">
    <!-- 顶部渐变头部 -->
    <div class="register-header">
      <h1><?php echo $MSG_REG_INFO?></h1>
      <p>创建您的 <?php echo $OJ_NAME?> 账号</p>
    </div>

    <!-- 表单区域 -->
    <div class="register-body">
      <!-- 欢迎提示 -->
      <div class="welcome-tip">
        <span class="tip-icon">🎁</span>
        <div class="tip-text">注册即送 <strong>20 积分</strong>，开启您的编程教学之旅。</div>
      </div>

      <!-- 错误提示 -->
      <div class="error-msg" id="error">
        <span>⚠️</span>
        <span id="error_info"></span>
      </div>

      <form action="register.php" method="post" role="form" id="registerForm">
        <!-- 角色选择 -->
        <div class="form-group">
          <label>注册角色 <span class="required">*</span></label>
          <div class="role-selector">
            <label class="role-card">
              <input type="radio" name="role" value="teacher" checked>
              <div class="role-content">
                <div class="role-icon">👨‍🏫</div>
                <div class="role-name">我是教师</div>
                <div class="role-desc">管理班级与题目</div>
              </div>
            </label>
            <label class="role-card">
              <input type="radio" name="role" value="student">
              <div class="role-content">
                <div class="role-icon">👨‍🎓</div>
                <div class="role-name">我是学生</div>
                <div class="role-desc">练习与参加考试</div>
              </div>
            </label>
          </div>
        </div>

        <!-- 用户名 -->
        <div class="form-group">
          <label for="username"><?php echo $MSG_USER_ID?> <span class="required">*</span></label>
          <input name="user_id" class="form-input" placeholder="建议使用手机号" type="text" required>
        </div>

        <!-- 密码 -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <div class="form-group">
            <label><?php echo $MSG_PASSWORD?> <span class="required">*</span></label>
            <input name="password" class="form-input" placeholder="请输入密码" type="password" required>
          </div>
          <div class="form-group">
            <label><?php echo $MSG_REPEAT_PASSWORD?> <span class="required">*</span></label>
            <input name="rptpassword" class="form-input" placeholder="再次输入密码" type="password" required>
          </div>
        </div>

        <!-- 学校 -->
        <div class="form-group">
          <label for="school"><?php echo $MSG_SCHOOL?></label>
          <?php
          if (file_exists("./include/school.php")) {
              require_once("./include/school.php");
              $school_list = getSchoolList(true);
          }
          ?>
          <select name="school_id" class="form-select">
            <option value="0">暂不选择</option>
            <?php if (!empty($school_list)): ?>
              <?php foreach ($school_list as $school): ?>
                <option value="<?php echo $school['id']; ?>"><?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8'); ?></option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <!-- 邮箱 -->
        <div class="form-group">
          <label for="email"><?php echo $MSG_EMAIL?> <span class="required">*</span></label>
          <input name="email" class="form-input" placeholder="请填写真实邮箱，用于接收账号激活通知" type="email" required>
        </div>

        <!-- 验证码 -->
        <?php if($OJ_VCODE){?>
          <div class="form-group">
            <label><?php echo $MSG_VCODE?> <span class="required">*</span></label>
            <div class="vcode-row">
              <input name="vcode" class="form-input" placeholder="请输入验证码" type="text" autocomplete="off" required>
              <img alt="点击刷新验证码" src="vcode.php" onclick="this.src='vcode.php?'+Math.random()" height="44">
            </div>
          </div>
        <?php }?>

        <!-- 提交按钮 -->
        <button name="submit" type="submit" class="btn-register"><?php echo $MSG_REGISTER; ?></button>
      </form>
    </div>

    <!-- 底部链接 -->
    <div class="register-footer">
      已有账号？<a href="loginpage.php">立即登录</a>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
    // 表单提交前的验证
    $('#registerForm').on('submit', function(e){
        var pwd = $('input[name="password"]').val();
        var rpt = $('input[name="rptpassword"]').val();
        if(pwd !== rpt) {
            e.preventDefault();
            $('#error').addClass('show');
            $('#error_info').text('两次输入的密码不一致');
            return false;
        }
    });

    // 输入时清除错误提示
    $('input').on('input', function(){
        $('#error').removeClass('show');
    });
});
</script>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
