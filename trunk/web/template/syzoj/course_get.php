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

  <!-- 成功消息 -->
  <?php if (!empty($view_success)): ?>
    <div class="ui success message" style="border-radius: 12px;">
      <i class="close icon"></i>
      <div class="header"><?php echo $MSG_SUCCESS; ?></div>
      <p><?php echo $view_success; ?></p>
    </div>
  <?php endif; ?>

  <!-- 课程获取表单 -->
  <?php if (!empty($view_course) && empty($view_error)): ?>
    <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
      <h2 class="ui header" style="margin-bottom: 20px; color: #333;">
        <i class="shopping cart icon"></i>
        <div class="content">
          <?php echo htmlspecialchars($view_course['title'], ENT_QUOTES, 'UTF-8'); ?>
          <div class="sub header">
            <?php echo htmlspecialchars($view_course['subject_name'], ENT_QUOTES, 'UTF-8'); ?>
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
            <?php $price = floatval($view_course['price']); $is_free = $price == 0; ?>
            <?php if ($is_free): ?>
              <span class="ui green label" style="font-size: 1.1em;">
                <?php echo $MSG_FREE; ?>
              </span>
            <?php else: ?>
              <span class="ui red label" style="font-size: 1.1em;">
                ¥<?php echo number_format($price, 2); ?>
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
      <?php endif; ?>

      <!-- 获取表单 -->
      <form class="ui form" method="POST" action="course_get.php">
        <?php require_once("./include/set_post_key.php"); ?>
        <input type="hidden" name="course_id" value="<?php echo $view_course['id']; ?>">

        <div class="field" style="margin-bottom: 20px;">
          <label style="font-weight: 600; color: #333;">
            <i class="mail icon"></i> <?php echo $MSG_RECEIVER_EMAIL; ?>
          </label>
          <input type="email" name="email" id="email"
                 value="<?php echo htmlspecialchars($_SESSION[$OJ_NAME . '_' . 'email'], ENT_QUOTES, 'UTF-8'); ?>"
                 placeholder="<?php echo $MSG_EMAIL_PLACEHOLDER; ?>"
                 required>
          <div class="ui pointing blue label">
            <?php echo $MSG_EMAIL_TIP; ?>
          </div>
        </div>

        <!-- 免费课程：显示确认获取按钮 -->
        <?php if (!$view_is_paid): ?>
          <button type="submit" class="ui large green button" style="width: 100%;">
            <i class="gift icon"></i>
            <?php echo $MSG_CONFIRM_GET; ?>
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
            <i class="shopping cart icon"></i>
            <?php echo $MSG_BUY_NOW; ?>
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
