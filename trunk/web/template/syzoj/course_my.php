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
                  <?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>
                </h3>
                <div style="color: #666; font-size: 0.9em;">
                  <span><i class="clock icon"></i> <?php echo $MSG_GET_TIME; ?>: <?php echo htmlspecialchars($course['created_at'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <span style="margin: 0 15px;">|</span>
                  <?php $price = floatval($course['price']); $is_free = $price == 0; ?>
                  <?php if ($is_free): ?>
                    <span style="color: #52c41a; font-weight: 600;"><?php echo $MSG_FREE; ?></span>
                  <?php else: ?>
                    <span style="color: #ff6b6b; font-weight: 600;">¥<?php echo number_format($price, 2); ?></span>
                  <?php endif; ?>
                  <span style="margin: 0 15px;">|</span>
                  <?php if ($course['mail_status'] == 1): ?>
                    <span style="color: #52c41a;"><i class="checkmark icon"></i> <?php echo $MSG_MAIL_SENT; ?></span>
                  <?php else: ?>
                    <span style="color: #ff6b6b;"><i class="exclamation icon"></i> <?php echo $MSG_MAIL_FAILED; ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="four wide column right aligned">
                <form method="POST" action="course_my.php" style="display: inline;">
                  <?php require_once("./include/set_post_key.php"); ?>
                  <input type="hidden" name="order_id" value="<?php echo $course['id']; ?>">
                  <input type="hidden" name="email" value="<?php echo htmlspecialchars($course['email'], ENT_QUOTES, 'UTF-8'); ?>">
                  <button type="submit" class="ui small positive button">
                    <i class="mail icon"></i><?php echo $MSG_RESEND; ?>
                  </button>
                </form>
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
