<?php $show_title="$MSG_COURSE_LIST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">

  <!-- 搜索框和学科 Tab -->
  <div style="margin-bottom: 20px;">
    <!-- 搜索框 -->
    <div style="margin-bottom: 15px;">
      <form action="course.php" method="get" class="ui form" style="max-width: 400px;">
        <?php if ($view_current_subject > 0): ?>
        <input type="hidden" name="subject" value="<?php echo $view_current_subject; ?>">
        <?php endif; ?>
        <div class="ui action input" style="width: 100%; border-radius: 8px;">
          <input type="text" name="search" placeholder="搜索课程名称、标签..." value="<?php echo htmlspecialchars($view_search_keyword ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          <button class="ui button" type="submit"><i class="search icon"></i>搜索</button>
        </div>
      </form>
    </div>

    <div class="ui top attached tabular menu" style="border-radius: 12px 12px 0 0;">
      <a class="item <?php echo $view_current_subject == 0 ? 'active' : ''; ?>" href="course.php">
        <?php echo $MSG_ALL; ?>
      </a>
      <?php foreach ($view_subjects as $subject): ?>
        <a class="item <?php echo $view_current_subject == $subject['id'] ? 'active' : ''; ?>"
           href="course.php?subject=<?php echo $subject['id']; ?>">
          <?php echo htmlspecialchars($subject['name'], ENT_QUOTES, 'UTF-8'); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 当前标签筛选显示 -->
  <?php if (!empty($view_current_tag)): ?>
  <div style="margin-bottom: 20px; padding: 10px 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
    <i class="tags icon" style="color: #856404;"></i>
    <span style="color: #856404;">标签筛选：</span>
    <span style="font-weight: 600;"><?php echo htmlspecialchars($view_current_tag, ENT_QUOTES, 'UTF-8'); ?></span>
    <a href="course.php<?php echo $view_current_subject > 0 ? '?subject=' . $view_current_subject : ''; ?>"
       style="margin-left: 10px; color: #856404; text-decoration: underline;">
      <i class="remove icon"></i>清除筛选
    </a>
  </div>
  <?php endif; ?>

  <!-- 课程卡片列表 -->
  <?php if (empty($view_courses)): ?>
    <div style="text-align: center; padding: 60px 20px; color: #999;">
      <i class="book icon" style="font-size: 4em; margin-bottom: 15px; display: block;"></i>
      <p>海量优质课件正在赶来</p>
    </div>
  <?php else: ?>
    <div class="ui four column stackable grid" style="margin-bottom: 20px;">
      <?php foreach ($view_courses as $course):
        $is_purchased = isset($view_purchased[$course['id']]);
        $preview_price = floatval($course['preview_price']);
        $source_price = floatval($course['source_price']);
        $min_price = min($preview_price, $source_price);
        $is_free = $preview_price == 0 && $source_price == 0;
      ?>
        <div class="column">
          <div class="ui card" style="height: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; border-radius: 12px; overflow: hidden;"
               onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.12)';"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
            <!-- 课程封面（使用图标占位） -->
            <div class="image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 140px; display: flex; align-items: center; justify-content: center;">
              <i class="book icon" style="font-size: 4em; color: rgba(255,255,255,0.9);"></i>
            </div>

            <div class="content" style="padding: 15px;">
              <!-- 学科标签 -->
              <div style="margin-bottom: 8px;">
                <span class="ui tiny label" style="background: #667eea15; color: #667eea; border: 1px solid #667eea30;">
                  <?php echo htmlspecialchars($course['subject_name'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
              </div>

              <!-- 课程标题 -->
              <a href="course_info.php?id=<?php echo $course['id']; ?>"
                 class="header"
                 style="font-size: 1.1em; font-weight: 600; color: #333; margin-bottom: 8px; display: block;">
                <?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>
              </a>

              <!-- 标签 -->
              <?php if (!empty($course['tags'])):
                $tags = explode(',', $course['tags']);
              ?>
                <div style="margin-bottom: 10px;">
                  <?php foreach ($tags as $tag):
                    $tag = trim($tag);
                    if (empty($tag)) continue;
                  ?>
                    <a href="course.php<?php echo $view_current_subject > 0 ? '?subject=' . $view_current_subject . '&' : '?'; ?>tag=<?php echo urlencode($tag); ?>"
                       class="ui tiny label"
                       style="background: #f0f0f0; color: #666; font-size: 0.75em; margin-right: 4px;">
                      <?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <!-- 课时数和价格 -->
              <div class="meta" style="color: #666; font-size: 0.9em;">
                <i class="clock icon"></i>
                <?php echo $MSG_LESSON_COUNT; ?>: <?php echo intval($course['lesson_count']); ?>
                <span style="margin: 0 8px; color: #ddd;">|</span>
                <?php if ($is_free): ?>
                  <span style="color: #52c41a; font-weight: 600;"><?php echo $MSG_FREE; ?></span>
                <?php elseif ($preview_price > 0 && $source_price > 0): ?>
                  <span style="color: #ff6b6b; font-weight: 600;">¥<?php echo number_format($min_price, 2); ?>起</span>
                <?php else: ?>
                  <span style="color: #ff6b6b; font-weight: 600;">¥<?php echo number_format(max($preview_price, $source_price), 2); ?></span>
                <?php endif; ?>
              </div>

              <!-- 下载次数 -->
              <div class="meta" style="color: #999; font-size: 0.85em; margin-top: 5px;">
                <i class="download icon"></i>
                已下载 <?php echo intval($course['download_count']); ?> 次
              </div>

              <!-- 购买状态 -->
              <div style="margin-top: 12px;">
                <?php if ($is_purchased): ?>
                  <span class="ui tiny green label">
                    <i class="checkmark icon"></i><?php echo $MSG_ACQUIRED; ?>
                  </span>
                <?php else: ?>
                  <span class="ui tiny grey label">
                    <?php echo $MSG_NOT_ACQUIRED; ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>

            <div class="extra content" style="padding: 12px 15px; border-top: 1px solid #eee;">
              <div class="right aligned">
                <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
                  <a href="loginpage.php" class="ui small primary button">
                    <?php echo $MSG_LOGIN; ?>
                  </a>
                <?php elseif ($is_purchased): ?>
                  <a href="course_info.php?id=<?php echo $course['id']; ?>" class="ui small positive button">
                    <i class="check icon"></i>已拥有
                  </a>
                <?php elseif ($is_free): ?>
                  <a href="course_info.php?id=<?php echo $course['id']; ?>" class="ui small green button">
                    <i class="gift icon"></i>限时免费获取
                  </a>
                <?php else: ?>
                  <a href="course_info.php?id=<?php echo $course['id']; ?>" class="ui small primary button">
                    <i class="shopping cart icon"></i>购买
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
