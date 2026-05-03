<?php $show_title="$MSG_COURSE - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">

  <!-- 返回按钮 -->
  <div style="margin-bottom: 15px;">
    <a href="course.php" class="ui small grey button">
      <i class="left arrow icon"></i><?php echo $MSG_BACK; ?>
    </a>
  </div>

  <!-- 课程基本信息 -->
  <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <div class="ui grid">
      <div class="row">
        <div class="ten wide column">
          <h1 class="ui header" style="margin-bottom: 10px;">
            <?php echo htmlspecialchars($view_course['title'], ENT_QUOTES, 'UTF-8'); ?>
          </h1>

          <!-- 学科和标签 -->
          <div style="margin-bottom: 15px;">
            <span class="ui label" style="background: #667eea15; color: #667eea; border: 1px solid #667eea30;">
              <i class="book icon"></i><?php echo htmlspecialchars($view_course['subject_name'], ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <?php if (!empty($view_course['tags'])):
              $tags = explode(',', $view_course['tags']);
              foreach ($tags as $tag):
                $tag = trim($tag);
                if (empty($tag)) continue;
            ?>
              <span class="ui tiny label" style="background: #f0f0f0; color: #666; margin-left: 5px;">
                <?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?>
              </span>
            <?php endforeach; endif; ?>
          </div>

          <!-- 价格和课时数 -->
          <div style="margin-bottom: 15px; font-size: 1.1em;">
            <?php $price = floatval($view_course['price']); $is_free = $price == 0; ?>
            <i class="clock icon"></i>
            <span><?php echo $MSG_LESSON_COUNT; ?>: <?php echo intval($view_course['lesson_count']); ?></span>
            <span style="margin: 0 15px; color: #ddd;">|</span>
            <?php if ($is_free): ?>
              <span style="color: #52c41a; font-weight: 600; font-size: 1.2em;"><?php echo $MSG_FREE; ?></span>
            <?php else: ?>
              <span style="color: #ff6b6b; font-weight: 600; font-size: 1.2em;">¥<?php echo number_format($price, 2); ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="six wide column right aligned">
          <!-- 操作按钮 -->
          <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
            <a href="loginpage.php" class="ui large primary button">
              <i class="user icon"></i><?php echo $MSG_LOGIN; ?>
            </a>
          <?php elseif ($view_is_purchased): ?>
            <div class="ui positive message" style="padding: 10px 15px; margin-bottom: 10px;">
              <i class="checkmark icon"></i>
              <?php echo $MSG_ACQUIRED; ?>
            </div>
            <a href="course_get.php?id=<?php echo $view_course['id']; ?>"
               class="ui large positive button">
              <i class="download icon"></i><?php echo $MSG_REDOWNLOAD; ?>
            </a>
          <?php elseif ($is_free): ?>
            <a href="course_get.php?id=<?php echo $view_course['id']; ?>"
               class="ui large green button">
              <i class="gift icon"></i><?php echo $MSG_FREE_GET; ?>
            </a>
          <?php else: ?>
            <a href="course_get.php?id=<?php echo $view_course['id']; ?>"
               class="ui large primary button">
              <i class="shopping cart icon"></i><?php echo $MSG_BUY_NOW; ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- 课程描述 -->
  <?php if (!empty($view_course['description'])): ?>
  <div class="ui segment" style="border-radius: 12px; margin-top: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <h3 class="ui header" style="color: #333;">
      <i class="info circle icon"></i>
      <?php echo $MSG_DESCRIPTION; ?>
    </h3>
    <div style="color: #555; line-height: 1.8; white-space: pre-wrap;">
      <?php echo htmlspecialchars($view_course['description'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- 预览区域 -->
  <?php
    $has_preview = !empty($view_courseware_url) || !empty($view_lesson_plan_url);
    // 未购买且存在完整版时显示购买遮罩
    $show_purchase_banner = !$view_is_purchased && ($view_has_full_courseware || $view_has_full_lesson_plan);
  ?>
  <?php if ($has_preview): ?>
  <div class="ui segment" style="border-radius: 12px; margin-top: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 0;">
    <div class="ui top attached tabular menu" style="border-radius: 12px 12px 0 0; margin-bottom: 0;">
      <a class="item <?php echo !empty($view_courseware_url) ? 'active' : ''; ?>"
         data-tab="courseware"
         onclick="switchPreview('courseware');">
        <i class="file alternate outline icon"></i>
        <?php echo $MSG_COURSEWARE; ?>
      </a>
      <a class="item <?php echo empty($view_courseware_url) && !empty($view_lesson_plan_url) ? 'active' : ''; ?>"
         data-tab="lesson_plan"
         onclick="switchPreview('lesson_plan');">
        <i class="book outline icon"></i>
        <?php echo $MSG_LESSON_PLAN; ?>
      </a>
    </div>

    <div class="ui bottom attached tab segment active" style="border-radius: 0 0 12px 12px; min-height: 400px; padding: 0; position: relative;">
      <!-- 课件预览 -->
      <div id="preview-courseware" class="ui tab <?php echo !empty($view_courseware_url) ? 'active' : ''; ?>" style="display: <?php echo !empty($view_courseware_url) ? 'block' : 'none'; ?>;">
        <?php if (!empty($view_courseware_url)): ?>
          <iframe src="<?php echo $view_courseware_url; ?>"
                  style="width: 100%; height: 500px; border: none;"
                  sandbox="allow-scripts allow-same-origin allow-popups">
          </iframe>
        <?php else: ?>
          <div style="text-align: center; padding: 80px 20px; color: #999;">
            <i class="file alternate outline icon" style="font-size: 3em; margin-bottom: 10px; display: block;"></i>
            <p>暂无课件预览</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- 教案预览 -->
      <div id="preview-lesson_plan" class="ui tab <?php echo empty($view_courseware_url) && !empty($view_lesson_plan_url) ? 'active' : ''; ?>" style="display: <?php echo empty($view_courseware_url) && !empty($view_lesson_plan_url) ? 'block' : 'none'; ?>;">
        <?php if (!empty($view_lesson_plan_url)): ?>
          <iframe src="<?php echo $view_lesson_plan_url; ?>"
                  style="width: 100%; height: 500px; border: none;"
                  sandbox="allow-scripts allow-same-origin allow-popups">
          </iframe>
        <?php else: ?>
          <div style="text-align: center; padding: 80px 20px; color: #999;">
            <i class="book outline icon" style="font-size: 3em; margin-bottom: 10px; display: block;"></i>
            <p>暂无教案预览</p>
          </div>
        <?php endif; ?>
      </div>

      <!-- 购买提示遮罩 -->
      <?php if ($show_purchase_banner): ?>
      <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 120px; background: linear-gradient(to bottom, transparent, white 40%); display: flex; align-items: flex-end; justify-content: center; padding-bottom: 20px; z-index: 10;">
        <div style="text-align: center;">
          <p style="color: #667eea; font-weight: 600; margin-bottom: 10px;"><?php echo $MSG_BUY_TO_VIEW_FULL; ?></p>
          <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
            <a href="loginpage.php" class="ui primary button"><i class="user icon"></i><?php echo $MSG_LOGIN; ?></a>
          <?php elseif ($is_free): ?>
            <a href="course_get.php?id=<?php echo $view_course['id']; ?>" class="ui green button"><i class="gift icon"></i><?php echo $MSG_FREE_GET; ?></a>
          <?php else: ?>
            <a href="course_get.php?id=<?php echo $view_course['id']; ?>" class="ui primary button"><i class="shopping cart icon"></i><?php echo $MSG_BUY_NOW; ?></a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- 已购买用户的下载源文件入口 -->
  <?php if ($view_is_purchased && (!empty($view_course['courseware_link']) || !empty($view_course['lesson_plan_link']))): ?>
  <div class="ui segment" style="border-radius: 12px; margin-top: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: center; padding: 20px;">
    <a href="course_get.php?id=<?php echo $view_course['id']; ?>" class="ui large positive button">
      <i class="download icon"></i><?php echo $MSG_DOWNLOAD_SOURCE; ?>
    </a>
  </div>
  <?php endif; ?>

  <?php endif; ?>

</div>

<script>
function switchPreview(tab) {
  // 更新 Tab 样式
  $('.ui.menu .item').removeClass('active');
  $('.ui.menu .item[data-tab="' + tab + '"]').addClass('active');

  // 切换显示内容
  $('.ui.tab').hide();
  $('#preview-' + tab).show();
}
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
