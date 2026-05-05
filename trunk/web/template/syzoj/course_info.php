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
            <?php 
              $source_price = floatval($view_course['source_price']);
              $is_free = $source_price == 0;
            ?>
            <i class="clock icon"></i>
            <span><?php echo $MSG_LESSON_COUNT; ?>: <?php echo intval($view_course['lesson_count']); ?></span>
            <?php if ($source_price > 0): ?>
              <span style="margin: 0 15px; color: #ddd;">|</span>
              <span style="color: #ff6b6b; font-weight: 600;">价格：¥<?php echo number_format($source_price, 2); ?></span>
            <?php elseif ($is_free): ?>
              <span style="margin: 0 15px; color: #ddd;">|</span>
              <span style="color: #52c41a; font-weight: 600; font-size: 1.2em;"><?php echo $MSG_FREE; ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="six wide column right aligned">
          <!-- 操作按钮 -->
          <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
            <a href="loginpage.php" class="ui large primary button">
              <i class="user icon"></i><?php echo $MSG_LOGIN; ?>
            </a>
          <?php elseif ($view_has_source_license): ?>
            <div class="ui positive message" style="padding: 10px 15px; margin-bottom: 10px;">
              <i class="checkmark icon"></i>
              已购买原文件权限
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
               class="ui large orange button">
              <i class="shopping cart icon"></i> 购买原文件权限
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>


  <!-- 付费课程价格说明（单一定价） -->
<?php if ($source_price > 0 && !$is_free): ?>
  <div class="ui segment" style="border-radius: 12px; margin-top: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;">
      <div>
        <h3 class="ui header" style="color: #333; margin: 0;">
          <i class="download icon"></i> 原文件下载权限
        </h3>
        <p style="color: #666; margin: 8px 0 0 0; font-size: 0.95em;">可下载课件和教案的可编辑原文件，适合需要直接使用和修改课件进行授课的教师</p>
      </div>
      <div style="text-align: right;">
        <div style="font-size: 1.8em; font-weight: 700; color: #ff6b6b;">¥<?php echo number_format($source_price, 2); ?></div>
        <div style="color: #999; font-size: 0.85em;">一次性买断</div>
      </div>
    </div>
  </div>
<?php endif; ?>

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

  <!-- 课程资源区域 -->
  <?php
    $has_courseware = !empty($view_courseware_url) || !empty($view_course['courseware_link']);
    $has_lesson_plan = !empty($view_lesson_plan_url) || !empty($view_course['lesson_plan_link']);
    $has_resource = $has_courseware || $has_lesson_plan;
  ?>
  <?php if ($has_resource): ?>
  <div class="ui segment" style="border-radius: 12px; margin-top: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
    <h3 class="ui header" style="color: #333; margin-bottom: 20px;">
      <i class="file text icon"></i>
      课程资源
    </h3>

    <div class="ui grid">
      <!-- 课件卡片 -->
      <?php if ($has_courseware): ?>
      <div class="eight wide column">
        <div class="ui card" style="width: 100%; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
          <div class="content" style="padding: 20px;">
            <div class="ui grid">
              <div class="twelve wide column">
                <div class="header" style="font-size: 1.3em; margin-bottom: 10px; display: flex; align-items: center;">
                  <i class="file alternate outline icon" style="color: #667eea; margin-right: 8px; font-size: 1.2em;"></i>
                  <?php echo $MSG_COURSEWARE; ?>
                </div>
                <div class="meta" style="color: #999; font-size: 0.9em;">
                  <?php if (!empty($view_courseware_url)): ?>
                    <i class="eye icon"></i> 
                    <?php echo $view_has_preview_license ? '完整预览' : '免费预览'; ?>
                  <?php else: ?>
                    <i class="download icon"></i> 仅支持下载
                  <?php endif; ?>
                </div>
              </div>
              <div class="four wide column right aligned">
                <?php 
                  $show_lock = false;
                  $lock_text = '';
                  if (!$view_has_preview_license && $view_has_full_courseware) {
                    $show_lock = true;
                    $lock_text = '预览需购买';
                  }
                  if (!$view_has_source_license && !empty($view_course['courseware_link'])) {
                    $show_lock = true;
                    $lock_text = '下载需购买';
                  }
                ?>
                <?php if ($show_lock): ?>
                  <div class="ui tiny orange label" style="margin-top: 5px;">
                    <i class="lock icon"></i> <?php echo $lock_text; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="extra content" style="padding: 15px 20px; background: #fafbfc; border-radius: 0 0 10px 10px;">
            <div class="ui two buttons">
              <?php if (!empty($view_courseware_url)): ?>
                <a href="<?php echo htmlspecialchars($view_courseware_url, ENT_QUOTES, 'UTF-8'); ?>"
                   target="_blank" rel="noopener noreferrer" class="ui primary button">
                  <i class="external alternate icon"></i> 预览
                </a>
              <?php endif; ?>
              <?php if ($view_has_source_license && !empty($view_course['courseware_link'])): ?>
                <a href="<?php echo htmlspecialchars($view_course['courseware_link'], ENT_QUOTES, 'UTF-8'); ?>"
                   target="_blank" rel="noopener noreferrer" class="ui positive button">
                  <i class="download icon"></i> 下载
                </a>
              <?php elseif (!$view_has_source_license && !empty($view_course['courseware_link']) && $source_price > 0): ?>
                <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2"
                   class="ui orange button">
                  <i class="lock icon"></i> 解锁下载
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- 教案卡片 -->
      <?php if ($has_lesson_plan): ?>
      <div class="eight wide column">
        <div class="ui card" style="width: 100%; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);">
          <div class="content" style="padding: 20px;">
            <div class="ui grid">
              <div class="twelve wide column">
                <div class="header" style="font-size: 1.3em; margin-bottom: 10px; display: flex; align-items: center;">
                  <i class="book outline icon" style="color: #52c41a; margin-right: 8px; font-size: 1.2em;"></i>
                  <?php echo $MSG_LESSON_PLAN; ?>
                </div>
                <div class="meta" style="color: #999; font-size: 0.9em;">
                  <?php if (!empty($view_lesson_plan_url)): ?>
                    <i class="eye icon"></i> 
                    <?php echo $view_has_preview_license ? '完整预览' : '免费预览'; ?>
                  <?php else: ?>
                    <i class="download icon"></i> 仅支持下载
                  <?php endif; ?>
                </div>
              </div>
              <div class="four wide column right aligned">
                <?php 
                  $show_lock = false;
                  $lock_text = '';
                  if (!$view_has_preview_license && $view_has_full_lesson_plan) {
                    $show_lock = true;
                    $lock_text = '预览需购买';
                  }
                  if (!$view_has_source_license && !empty($view_course['lesson_plan_link'])) {
                    $show_lock = true;
                    $lock_text = '下载需购买';
                  }
                ?>
                <?php if ($show_lock): ?>
                  <div class="ui tiny orange label" style="margin-top: 5px;">
                    <i class="lock icon"></i> <?php echo $lock_text; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="extra content" style="padding: 15px 20px; background: #fafbfc; border-radius: 0 0 10px 10px;">
            <div class="ui two buttons">
              <?php if (!empty($view_lesson_plan_url)): ?>
                <a href="<?php echo htmlspecialchars($view_lesson_plan_url, ENT_QUOTES, 'UTF-8'); ?>"
                   target="_blank" rel="noopener noreferrer" class="ui primary button">
                  <i class="external alternate icon"></i> 预览
                </a>
              <?php endif; ?>
              <?php if ($view_has_source_license && !empty($view_course['lesson_plan_link'])): ?>
                <a href="<?php echo htmlspecialchars($view_course['lesson_plan_link'], ENT_QUOTES, 'UTF-8'); ?>"
                   target="_blank" rel="noopener noreferrer" class="ui positive button">
                  <i class="download icon"></i> 下载
                </a>
              <?php elseif (!$view_has_source_license && !empty($view_course['lesson_plan_link']) && $source_price > 0): ?>
                <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2"
                   class="ui orange button">
                  <i class="lock icon"></i> 解锁下载
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

  <!-- 创作者入驻引导 -->
  <div class="ui segment" style="border-radius: 12px; margin-top: 20px; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border: 1px solid #667eea30;">
    <div style="text-align: center; padding: 20px;">
      <div style="font-size: 1.3em; margin-bottom: 10px;">
        🎁 有优质课件资源想分享售卖？
      </div>
      <div style="color: #555; margin-bottom: 15px;">
        欢迎教师入驻！我们提供平台支持，收益分成。
      </div>
      <div style="font-size: 1.1em;">
        <i class="qq icon" style="color: #12b7f5;"></i>
        咨询客服QQ：
        <a href="tencent://message/?uin=326234108" style="color: #12b7f5; font-weight: 600;">
          326234108
        </a>
      </div>
    </div>
  </div>


<?php include("template/$OJ_TEMPLATE/footer.php");?>
