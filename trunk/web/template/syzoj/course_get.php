<?php $show_title="$MSG_COURSE - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">

  <!-- 返回按钮 -->
  <div style="margin-bottom: 15px;">
    <a href="<?php echo !empty($view_course) ? 'course_info.php?id=' . intval($view_course['id']) : 'course.php'; ?>" class="ui small grey button">
      <i class="left arrow icon"></i><?php echo $MSG_BACK; ?>
    </a>
  </div>

  <!-- 错误消息 -->
  <?php if (!empty($view_error)): ?>
    <div class="ui error message" style="border-radius: 12px;">
      <i class="close icon"></i>
      <div class="header"><?php echo $MSG_ERROR; ?></div>
      <p><?php echo htmlentities($view_error, ENT_QUOTES, 'UTF-8'); ?></p>
      <?php if (strpos($view_error, '积分') !== false): ?>
        <p style="margin-top:10px;">
          <a href="point_index.php" class="ui small orange button">
            <i class="dollar sign icon"></i>去充值
          </a>
        </p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- 成功消息 + 自动跳转 -->
  <?php if (!empty($view_success) && !empty($view_redirect_url)): ?>
    <div class="ui success message" style="border-radius: 12px;">
      <i class="close icon"></i>
      <div class="header"><?php echo $MSG_SUCCESS; ?></div>
      <p><?php echo htmlentities($view_success, ENT_QUOTES, 'UTF-8'); ?></p>
      <p style="margin-top: 10px; color: #666;">
        <i class="spinner loading icon"></i>
        <span id="countdown">3</span> 秒后自动跳转...
      </p>
      <a href="<?php echo htmlentities($view_redirect_url, ENT_QUOTES, 'UTF-8'); ?>" class="ui large blue button" style="width: 100%; margin-top: 15px;">
        <i class="arrow right icon"></i> 立即查看课程
      </a>
    </div>

    <script>
      var countdown = 3;
      var redirectUrl = '<?php echo htmlentities($view_redirect_url, ENT_QUOTES, 'UTF-8'); ?>';
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
          <?php echo htmlentities($view_course['title'], ENT_QUOTES, 'UTF-8'); ?>
          <div class="sub header">
            <?php echo htmlentities($view_course['subject_name'], ENT_QUOTES, 'UTF-8'); ?>
            <?php if (!empty($view_license_name)): ?>
              <span class="ui label" style="margin-left: 10px; <?php if ($view_license_type == 1): ?>background: #667eea; color: white;<?php elseif ($view_license_type == 2): ?>background: #52c41a; color: white;<?php else: ?>background: #f59e0b; color: white;<?php endif; ?>">
                <?php echo htmlentities($view_license_name, ENT_QUOTES, 'UTF-8'); ?>
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
            <?php if (!$view_is_paid): ?>
              <span class="ui green label" style="font-size: 1.1em;">限时免费</span>
            <?php else: ?>
              <?php if ($view_is_upgrade): ?>
                <span class="ui red label" style="font-size: 1.1em;">
                  补差价 <?php echo intval($view_required_points); ?> 积分
                </span>
                <small style="color:#999;margin-left:6px;">原价 <?php echo intval($view_source_price); ?>，已抵扣 <?php echo intval($view_upgrade_deduct_points); ?></small>
              <?php else: ?>
                <span class="ui red label" style="font-size: 1.1em;">
                  <?php echo intval($view_required_points); ?> 积分
                </span>
                <small style="color:#999;margin-left:6px;">1积分=1元</small>
              <?php endif; ?>
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

        <a href="course_info.php?id=<?php echo intval($view_course['id']); ?>" class="ui large blue button" style="width: 100%; margin-bottom: 20px;">
          <i class="eye icon"></i><?php echo $MSG_VIEW_FULL_COURSEWARE; ?>
        </a>
      <?php else: ?>

      <!-- 积分余额信息（仅付费时显示） -->
      <?php if ($view_is_paid): ?>
        <div style="margin-bottom: 20px; padding: 15px; background: #fff7e6; border-radius: 8px; border:1px solid #ffe1a8;">
          <?php if ($view_is_upgrade): ?>
            <div style="margin-bottom: 12px; padding: 10px 12px; background: #fff; border-radius: 6px; border: 1px solid #ffe1a8;">
              <div style="font-weight: 600; color: #92400e; margin-bottom: 8px;">
                <i class="info circle icon"></i> 升级抵扣明细
              </div>
              <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;color:#666;font-size:0.95em;">
                <div>原文件版原价：<strong><?php echo intval($view_source_price); ?></strong> 积分</div>
                <div>已拥有完整预览版抵扣：<strong style="color:#21ba45;">-<?php echo intval($view_upgrade_deduct_points); ?></strong> 积分</div>
                <div>本次补差价：<strong style="color:#d4380d;"><?php echo intval($view_required_points); ?></strong> 积分</div>
              </div>
            </div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>当前积分余额：<strong><?php echo intval($view_user_balance); ?></strong> 积分</div>
            <div><?php echo $view_is_upgrade ? '本次补差价' : '本次需消耗'; ?>：<strong style="color:#d4380d;"><?php echo intval($view_required_points); ?></strong> 积分</div>
            <div>支付后余额：<strong><?php echo intval($view_balance_after); ?></strong> 积分</div>
          </div>
          <?php if (!$view_enough_balance): ?>
            <div style="margin-top:10px;color:#d4380d;">
              <i class="warning circle icon"></i>积分不足，请先充值后再购买。
              <a href="point_index.php" class="ui small orange button" style="margin-left:10px;">
                <i class="dollar sign icon"></i>去充值
              </a>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- 获取表单 -->
      <form class="ui form" method="POST" action="course_get.php">
        <input type="hidden" name="postkey" value="<?php echo isset($_SESSION[$OJ_NAME.'_'.'postkey']) ? htmlentities($_SESSION[$OJ_NAME.'_'.'postkey'], ENT_QUOTES, 'UTF-8') : ''; ?>">
        <input type="hidden" name="course_id" value="<?php echo intval($view_course['id']); ?>">
        <input type="hidden" name="license_type" value="<?php echo intval($view_license_type); ?>">
        <?php if ($view_is_upgrade): ?>
        <input type="hidden" name="upgrade" value="1">
        <?php endif; ?>

        <?php if (!$view_is_paid): ?>
          <!-- 免费课程：显示确认获取按钮 -->
          <button type="submit" class="ui large green button" style="width: 100%;">
            <i class="gift icon"></i>
            <?php if ($view_is_upgrade): ?>
              确认免费升级到原文件版
            <?php else: ?>
              确认免费获取<?php echo htmlentities(str_replace("(升级)", "", $view_license_name), ENT_QUOTES, 'UTF-8'); ?>
            <?php endif; ?>
          </button>
        <?php else: ?>
          <!-- 积分支付：确认消耗按钮 -->
          <button type="submit" class="ui large primary button" style="width: 100%;" <?php if (!$view_enough_balance): ?>disabled<?php endif; ?>>
            <i class="<?php echo $view_is_upgrade ? 'arrow up' : 'shopping cart'; ?> icon"></i>
            <?php if ($view_is_upgrade): ?>
              确认补差价 <?php echo intval($view_required_points); ?> 积分升级到原文件版
            <?php else: ?>
              确认消耗 <?php echo intval($view_required_points); ?> 积分获取<?php echo htmlentities($view_license_name, ENT_QUOTES, 'UTF-8'); ?>
            <?php endif; ?>
          </button>
          <p style="text-align:center;margin-top:10px;color:#999;font-size:12px;">1积分 = 1元；积分通过<a href="point_index.php">我的积分</a>页面兑换。</p>
        <?php endif; ?>
      </form>
      <?php endif; ?>
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
