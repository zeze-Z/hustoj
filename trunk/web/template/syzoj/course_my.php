<?php $show_title="$MSG_MY_COURSE - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">

  <!-- 页面标题 -->
  <h2 class="ui header" style="margin-bottom: 20px;">
    <i class="shopping bag icon"></i>
    <div class="content">
      <?php echo $MSG_MY_COURSE; ?>
      <div class="sub header">
        <?php echo $MSG_MY_COURSE_DESC; ?>
      </div>
    </div>
  </h2>

  <!-- 教师推广奖励说明（V2.7，仅教师身份显示） -->
  <?php if (!empty($view_is_teacher)): ?>
  <div style="max-width:700px;margin:0 auto 24px;background:#fff;border-radius:16px;padding:24px 28px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border:1px solid #f0f0f0;">
    <div style="font-size:17px;font-weight:600;color:#667eea;margin-bottom:12px;">📢 教师推广奖励说明</div>
    <div style="font-size:14px;color:#555;line-height:1.8;">
      <p><strong>奖励规则：</strong>联系客服导入班级学生后，每周若有 <strong>60%</strong> 关联学生登录平台，您即可获得 <strong>5 积分</strong> 奖励。</p>
      <p><strong>奖励周期：</strong>连续 <strong>4 周</strong>，每周达标每周发放。若某周未达标则奖励终止，已发放积分不回收。最多可获 <strong>20 积分</strong>。</p>
      <p><strong>参与方式：</strong>联系客服批量开通学生账号，学生将自动关联到您的教师账号。</p>
      <p><strong>积分用途：</strong>购买课件等平台服务。</p>
    </div>
  </div>
  <?php endif; ?>

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

  <!-- 课程列表 -->
  <?php if (empty($view_courses)): ?>
    <div class="ui info message" style="text-align: center; padding: 40px 20px;">
      <i class="inbox icon" style="font-size: 3em; margin-bottom: 15px;"></i>
      <p><?php echo $MSG_NO_COURSE_YET; ?></p>
      <a href="course.php" class="ui primary button" style="margin-top: 15px;">
        <i class="shopping cart icon"></i><?php echo $MSG_BROWSE_COURSE; ?>
      </a>
    </div>
  <?php else: ?>
    <div class="ui segments">
      <?php foreach ($view_courses as $course): ?>
        <div class="ui segment" style="border-radius: 0; margin-bottom: 0;">
          <div class="ui grid">
            <div class="row">
              <div class="twelve wide column">
                <h3 style="margin: 0 0 10px 0; color: #333;">
                  <?php echo htmlentities($course['title'], ENT_QUOTES, 'UTF-8'); ?>
                </h3>
                <div style="color: #666; font-size: 0.9em;">
                  <span><i class="clock icon"></i> <?php echo $MSG_GET_TIME; ?>: <?php echo htmlentities($course['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <span style="margin: 0 15px;">|</span>
                  <?php
                    $license_map = array(1 => '完整预览版', 2 => '原文件版');
                    $license_text = isset($license_map[$course['license_type']]) ? $license_map[$course['license_type']] : '历史/未知';
                    $pay_channel = isset($course['pay_channel']) ? $course['pay_channel'] : '';
                    $pay_map = array(
                        'point'  => '积分支付',
                        'free'   => '免费获取',
                        'alipay' => '支付宝(历史)',
                        'wxpay'  => '微信(历史)',
                    );
                    $pay_text = isset($pay_map[$pay_channel]) ? $pay_map[$pay_channel] : ($pay_channel ?: '未知');
                    $point_amount = intval(round(floatval($course['amount'])));
                    $is_free = $point_amount == 0 || $pay_channel === 'free';
                  ?>
                  <span style="color:#1677ff;">
                    <i class="key icon"></i><?php echo htmlentities($license_text, ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                  <span style="margin: 0 15px;">|</span>
                  <?php if ($is_free): ?>
                    <span style="color: #52c41a; font-weight: 600;"><?php echo $MSG_FREE; ?></span>
                  <?php else: ?>
                    <span style="color: #ff6b6b; font-weight: 600;"><?php echo $point_amount; ?> 积分</span>
                  <?php endif; ?>
                  <span style="margin: 0 15px;">|</span>
                  <span style="color:#666;">
                    <i class="credit card icon"></i><?php echo htmlentities($pay_text, ENT_QUOTES, 'UTF-8'); ?>
                  </span>
                </div>
              </div>
              <div class="four wide column right aligned">
                <a href="course_info.php?id=<?php echo $course['course_id']; ?>" class="ui small positive button">
                  <i class="link icon"></i><?php echo $MSG_REDOWNLOAD; ?>
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- 分页 -->
    <?php if ($view_total_pages > 1): ?>
      <div style="margin-top: 20px; text-align: center;">
        <div class="ui pagination menu">
          <?php if ($view_page > 1): ?>
            <a class="item" href="course_my.php?page=<?php echo $view_page - 1; ?>">
              <i class="left chevron icon"></i> <?php echo $MSG_PREV; ?>
            </a>
          <?php endif; ?>

          <?php
            $start = max(1, $view_page - 2);
            $end = min($view_total_pages, $view_page + 2);
            for ($i = $start; $i <= $end; $i++):
          ?>
            <a class="item <?php echo $i == $view_page ? 'active' : ''; ?>"
               href="course_my.php?page=<?php echo $i; ?>">
              <?php echo $i; ?>
            </a>
          <?php endfor; ?>

          <?php if ($view_page < $view_total_pages): ?>
            <a class="item" href="course_my.php?page=<?php echo $view_page + 1; ?>">
              <?php echo $MSG_NEXT; ?> <i class="right chevron icon"></i>
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endif; ?>

</div>

<script>
// 关闭消息提示
$('.message .close').on('click', function() {
  $(this).closest('.message').transition('fade');
});
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
