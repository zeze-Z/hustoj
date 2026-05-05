<?php $show_title="$MSG_COURSE - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">

  <!-- 返回按钮 -->
  <div style="margin-bottom: 15px;">
    <a href="course.php" class="ui small grey button">
      <i class="left arrow icon"></i><?php echo $MSG_BACK; ?>
    </a>
  </div>

  <!-- 错误消息 -->
  <?php if (!empty($view_error)): ?>
    <div class="ui error message" style="border-radius: 12px;">
      <i class="close icon"></i>
      <div class="header"><?php echo $MSG_ERROR; ?></div>
      <p><?php echo $view_error; ?></p>
    </div>
  <?php endif; ?>

  <!-- 成功消息 + 自动跳转 -->
  <?php if (!empty($view_success) && !empty($view_redirect_url)): ?>
    <div class="ui success message" style="border-radius: 12px;">
      <i class="close icon"></i>
      <div class="header"><?php echo $MSG_SUCCESS; ?></div>
      <p><?php echo $view_success; ?></p>
      <p style="margin-top: 10px; color: #666;">
        <i class="spinner loading icon"></i>
        <span id="countdown">3</span> 秒后自动跳转...
      </p>
      <a href="<?php echo $view_redirect_url; ?>" class="ui large blue button" style="width: 100%; margin-top: 15px;">
        <i class="arrow right icon"></i> 立即查看课程
      </a>
    </div>

    <script>
      var countdown = 3;
      var redirectUrl = '<?php echo $view_redirect_url; ?>';
      var timer = setInterval(function() {
        countdown--;
        var el = document.getElementById('countdown');
        if (el) el.textContent = countdown;
        if (countdown <= 0) {
          clearInterval(timer);
          window.location.href = redirectUrl;
        }
      }, 1000);
    </script>
  <?php endif; ?>

  <!-- 课程获取表单 -->
  <?php if (!empty($view_course) && empty($view_error) && empty($view_success)): ?>
    <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
      <h2 class="ui header" style="margin-bottom: 20px; color: #333;">
        <i class="shopping cart icon"></i>
        <div class="content">
          <?php echo htmlspecialchars($view_course['title'], ENT_QUOTES, 'UTF-8'); ?>
          <div class="sub header">
            <?php echo htmlspecialchars($view_course['subject_name'], ENT_QUOTES, 'UTF-8'); ?>
            <?php if (!empty($view_license_name)): ?>
              <span class="ui label" style="margin-left: 10px; <?php if ($view_license_type == 1): ?>background: #667eea; color: white;<?php elseif ($view_license_type == 2): ?>background: #52c41a; color: white;<?php else: ?>background: #f59e0b; color: white;<?php endif; ?>">
                <?php echo $view_license_name; ?>
              </span>
            <?php endif; ?>
          </div>
        </div>
      </h2>

      <!-- 课程信息 -->
      <div style="margin-bottom: 25px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <span style="color: #666;"><i class="clock icon"></i> <?php echo $MSG_LESSON_COUNT; ?>: <?php echo intval($view_course['lesson_count']); ?></span>
          </div>
          <div>
            <?php if ($view_amount == 0): ?>
              <span class="ui green label" style="font-size: 1.1em;">
                <?php echo $MSG_FREE; ?>
              </span>
            <?php else: ?>
              <span class="ui red label" style="font-size: 1.1em;">
                ¥<?php echo number_format($view_amount, 2); ?>
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- 已获取提示 -->
      <?php if ($view_is_acquired): ?>
        <div class="ui positive message" style="padding: 15px; margin-bottom: 20px;">
          <i class="checkmark icon"></i>
          <?php echo $MSG_ALREADY_ACQUIRED_HINT; ?>
        </div>

        <a href="course_info.php?id=<?php echo $view_course['id']; ?>" class="ui large blue button" style="width: 100%; margin-bottom: 20px;">
          <i class="eye icon"></i><?php echo $MSG_VIEW_FULL_COURSEWARE; ?>
        </a>
      <?php endif; ?>

      <!-- 获取表单 -->
      <form class="ui form" method="POST" action="course_get.php">
        <input type="hidden" name="postkey" value="<?php echo isset($_SESSION[$OJ_NAME.'_'.'postkey']) ? $_SESSION[$OJ_NAME.'_'.'postkey'] : ''; ?>">
        <input type="hidden" name="course_id" value="<?php echo $view_course['id']; ?>">
        <input type="hidden" name="license_type" value="<?php echo $view_license_type; ?>">
        <?php if ($view_is_upgrade): ?>
        <input type="hidden" name="upgrade" value="1">
        <?php endif; ?>

        <!-- 免费课程：显示确认获取按钮 -->
        <?php if (!$view_is_paid): ?>
          <button type="submit" class="ui large green button" style="width: 100%;">
            <i class="gift icon"></i>
            确认获取<?php echo str_replace("(升级)", "", $view_license_name); ?>
          </button>
        <?php else: ?>
          <!-- 付费课程：支付方式选择 -->
          <div class="field" style="margin-bottom: 20px;">
            <label style="font-weight: 600; color: #333;">
              <i class="credit card icon"></i> <?php echo $MSG_PAYMENT_METHOD; ?>
            </label>
            <div class="ui form" style="display: flex; gap: 15px; margin-top: 10px;">
              <div class="field" style="flex: 1;">
                <div class="ui radio checkbox">
                <input type="radio" name="pay_channel" value="alipay" id="alipay" checked>
                <label for="alipay">
                    <i class="paypal icon" style="color: #1678ff;"></i> 支付宝
                </label>
                </div>
              </div>
            </div>
          </div>

          <button type="submit" class="ui large primary button" style="width: 100%;">
            <i class="<?php echo $view_is_upgrade ? 'arrow up' : 'shopping cart'; ?> icon"></i>
            <?php echo $view_is_upgrade ? '立即支付升级费用' : '立即购买' . $view_license_name; ?>
          </button>
        <?php endif; ?>
      </form>
    </div>
  <?php endif; ?>

</div>

<script>
// 关闭消息提示
$('.message .close').on('click', function() {
  $(this).closest('.message').transition('fade');
});
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
