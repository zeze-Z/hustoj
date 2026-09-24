<?php $show_title="$MSG_COURSE_LIST - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<div class="padding">

  <!-- 搜索框和学科 Tab -->
  <div style="margin-bottom: 20px;">
    <!-- 搜索框 -->
    <div style="margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
      <form action="course.php" method="get" class="ui form" style="margin: 0; width: 400px; max-width: 100%; flex-shrink: 0;">
        <?php if ($view_current_subject > 0): ?>
        <input type="hidden" name="subject" value="<?php echo $view_current_subject; ?>">
        <?php endif; ?>
        <div class="ui action input" style="width: 100%; border-radius: 8px;">
          <input type="text" name="search" placeholder="搜索课程名称、标签..." value="<?php echo htmlspecialchars($view_search_keyword ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          <button class="ui button" type="submit"><i class="search icon"></i>搜索</button>
        </div>
      </form>
      <?php if (isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
      <a href="course_my.php" class="ui positive button" style="white-space: nowrap;">
        <i class="shopping bag icon"></i>我的订单
      </a>
      <?php endif; ?>
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
  <style>
    /* 响应式列数：宽屏 5 列、中屏 4 列、手机 3 列 */
    .course-grid > .column { width: 20% !important; }
    .series-card:focus { outline: 3px solid #667eea; outline-offset: 5px; }
    .series-card { overflow: visible !important; isolation: isolate; margin: 0 12px 16px 0; background: #fff; border: 2px solid #8f83df !important; border-radius: 18px !important; height: calc(100% - 16px) !important; }
    .series-card .image { border-radius: 16px 16px 0 0; overflow: hidden; }
    .series-card::after { content: ''; position: absolute; inset: 0; pointer-events: none; z-index: 3; border: 2px solid #8f83df; border-radius: 18px; box-sizing: border-box; }
    .series-card .series-offset { pointer-events: none; position: absolute; display: block; box-sizing: border-box; overflow: hidden; border-radius: 18px !important; clip-path: inset(0 round 18px); z-index: 0; border: 2px solid; transition: transform .28s ease, box-shadow .28s ease; }
    .series-card .series-offset-back { inset: 16px -14px -16px 14px; background: #c8bdf0; border-color: #a99ada; transform: rotate(3deg); box-shadow: none; filter: drop-shadow(0 10px 8px rgba(63, 48, 137, 0.22)); }
    .series-card .series-offset-middle { inset: 8px -7px -9px 7px; background: #ded7fa; border-color: #c0b5eb; transform: rotate(1.6deg); box-shadow: none; filter: drop-shadow(0 7px 7px rgba(63, 48, 137, 0.18)); }
    .series-card .series-offset-front { inset: 0; background: #f8f7ff; border-color: transparent; transform: rotate(0); box-shadow: 0 5px 14px rgba(63, 48, 137, 0.16); }
    .series-card > *:not(.series-offset) { position: relative; z-index: 1; }
    .series-card .series-copy { padding: 12px 14px 10px; }
    .series-card .series-subject { margin-bottom: 5px; }
    .series-card .series-title { margin: 0 0 8px; font-size: 1.08em; line-height: 1.3; }
    .series-card .series-tags { margin: 0 0 10px; }
    .series-card .series-meta { color: #5d568a; font-size: .84em; }
    .series-card .series-stat { display: block; width: fit-content; margin-top: 5px; padding: 3px 8px; border-radius: 8px; background: #ede9ff; }
    .series-card .series-action { padding: 8px 14px 10px; border-top: 1px solid #e3def8; text-align: left; }
    .series-card .series-action .button { margin: 0; }
    .series-card .series-badge { position: absolute; top: 10px; left: 10px; display: inline-flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #ffcf4a 0%, #ff8a3d 52%, #f0528d 100%); color: #3b215f; font-size: .84em; font-weight: 800; letter-spacing: .03em; padding: 6px 9px; border: 2px solid rgba(255,255,255,.9); border-radius: 10px 14px 14px 10px; box-shadow: 0 4px 12px rgba(112, 43, 112, .38); text-shadow: 0 1px 0 rgba(255,255,255,.35); z-index: 1; }
    .series-card .series-badge .stacked.icon { display: none; }
    .series-card:hover .series-badge, .series-card:focus-within .series-badge { box-shadow: 0 5px 15px rgba(112, 43, 112, .48); }
    .series-card:hover .series-offset-back { transform: translate(3px, 3px) rotate(3deg); filter: drop-shadow(0 13px 10px rgba(63, 48, 137, 0.25)); }
    .series-card:hover .series-offset-middle { transform: translate(2px, 2px) rotate(1.6deg); filter: drop-shadow(0 9px 9px rgba(63, 48, 137, 0.21)); }
    @media (prefers-reduced-motion: reduce) {
      .series-card .series-offset { transition: none; }
      .series-card:hover .series-offset-back,
      .series-card:hover .series-offset-middle { transform: none; }
    }
    @media (max-width: 1199px) {
      .course-grid > .column { width: 25% !important; }
    }
    @media (max-width: 767px) {
      .course-grid > .column { width: 33.3333% !important; }
      .series-card .series-offset-back { inset: 9px -8px -10px 8px; }
      .series-card .series-offset-middle { inset: 5px -4px -5px 4px; }
      .series-card { margin-right: 6px; height: calc(100% - 10px) !important; }
    }
  </style>
  <?php if (empty($view_courses)): ?>
    <div style="text-align: center; padding: 60px 20px; color: #999;">
      <i class="book icon" style="font-size: 4em; margin-bottom: 15px; display: block;"></i>
      <p>海量优质课件正在赶来</p>
    </div>
  <?php else: ?>
    <div class="ui five column grid course-grid" style="margin-bottom: 20px;">
      <?php foreach ($view_courses as $course):
        if (!empty($course['is_series'])):
          $series_query = array('tag' => '系列课程:' . $course['series_name']);
          if ($view_current_subject > 0) $series_query['subject'] = $view_current_subject;
          $series_link = 'course.php?' . http_build_query($series_query);
      ?>
        <div class="column">
          <div class="ui card series-card" tabindex="0" role="link" onclick="if (event.target.tagName !== 'A') window.location.href='<?php echo htmlspecialchars($series_link, ENT_QUOTES, 'UTF-8'); ?>';" onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); window.location.href='<?php echo htmlspecialchars($series_link, ENT_QUOTES, 'UTF-8'); ?>'; }" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(102,126,234,0.25)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(102,126,234,0.18)';" style="position: relative; height: 100%; box-shadow: 0 2px 8px rgba(102,126,234,0.18); transition: all 0.3s; border-radius: 12px; border: 1px solid #667eea40;">
            <span class="series-offset series-offset-back" aria-hidden="true"></span>
            <span class="series-offset series-offset-middle" aria-hidden="true"></span>
            <span class="series-offset series-offset-front" aria-hidden="true"></span>
            <div class="image" style="position: relative; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 140px; display: flex; align-items: center; justify-content: center;">
              <span class="series-badge">
                <i class="stacked icon"></i>系列课程
              </span>
              <i class="book icon" style="font-size: 4em; color: rgba(255,255,255,0.9);"></i>
              <?php if (!empty($course['cover_url'])): ?>
              <img src="<?php echo htmlspecialchars($course['cover_url'], ENT_QUOTES, 'UTF-8'); ?>"
                   alt="<?php echo htmlspecialchars($course['series_name'], ENT_QUOTES, 'UTF-8'); ?>"
                   loading="lazy" decoding="async"
                   style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;"
                   onerror="this.style.display='none'">
              <?php endif; ?>
            </div>

            <div class="content series-copy">
              <div class="series-subject">
                <span class="ui tiny label" style="background: #667eea15; color: #667eea; border: 1px solid #667eea30;">
                  <?php echo htmlspecialchars($course['subject_name'], ENT_QUOTES, 'UTF-8'); ?>
                </span>
              </div>

              <a href="<?php echo htmlspecialchars($series_link, ENT_QUOTES, 'UTF-8'); ?>"
                 class="header series-title"
                 style="color: #333; display: block;">
                <?php echo htmlspecialchars($course['series_name'], ENT_QUOTES, 'UTF-8'); ?>
              </a>

              <?php
                $series_first_tags = array();
                if (!empty($course['tags'])) {
                    foreach (explode(',', $course['tags']) as $series_tag) {
                        $series_tag = trim($series_tag);
                        if ($series_tag === '' || strpos($series_tag, '系列课程:') === 0) continue;
                        $series_first_tags[] = $series_tag;
                    }
                }
              ?>
              <?php if (!empty($series_first_tags)): ?>
              <div class="series-tags" aria-label="系列第一课标签">
                <?php foreach (array_slice($series_first_tags, 0, 4) as $series_tag): ?>
                <span class="ui tiny label" style="background: #f0f0f0; color: #666; font-size: 0.75em; margin-right: 4px;">
                  <?php echo htmlspecialchars($series_tag, ENT_QUOTES, 'UTF-8'); ?>
                </span>
                <?php endforeach; ?>
              </div>
              <?php endif; ?>

              <div class="series-meta">
                <span class="series-stat"><i class="book icon"></i><?php echo intval($course['series_course_count']); ?> 门课程</span>
                <span class="series-stat"><i class="clock icon"></i><?php echo intval($course['series_lesson_count']); ?> 课时</span>
              </div>
            </div>

            <div class="extra content series-action">
              <a href="<?php echo htmlspecialchars($series_link, ENT_QUOTES, 'UTF-8'); ?>" class="ui small primary button">
                <i class="list icon"></i>查看系列课程
              </a>
            </div>
          </div>
        </div>
      <?php else:
        $is_purchased = isset($view_purchased[$course['id']]);
        $preview_price = floatval($course['preview_price']);
        $source_price = floatval($course['source_price']);
        $min_price = min($preview_price, $source_price);
        $is_free = $preview_price == 0 && $source_price == 0;
        // 是否存在完整预览版（有完整预览链接才算有预览版，与详情页 view_has_full_preview 口径一致）
        $has_preview = !empty($course['courseware_full_preview_url']) || !empty($course['lesson_plan_full_preview_url']);
      ?>
        <div class="column">
          <div class="ui card" style="height: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; border-radius: 12px; overflow: hidden;"
               onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.12)';"
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)';">
            <!-- 课程封面（使用图标占位） -->
            <div class="image" style="position: relative; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 140px; display: flex; align-items: center; justify-content: center;">
              <?php if (!empty($course['is_new'])): ?>
              <span style="position: absolute; top: 10px; right: 10px; background: #ff4d4f; color: #fff; font-size: 0.75em; font-weight: 600; padding: 2px 10px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); z-index: 1;">
                上新
              </span>
              <?php endif; ?>
              <i class="book icon" style="font-size: 4em; color: rgba(255,255,255,0.9);"></i>
              <?php if (!empty($course['cover_url'])): ?>
              <img src="<?php echo htmlspecialchars($course['cover_url'], ENT_QUOTES, 'UTF-8'); ?>"
                   alt="<?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>"
                   loading="lazy" decoding="async"
                   style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;"
                   onerror="this.style.display='none'">
              <?php endif; ?>
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
                    if (empty($tag) || strpos($tag, '系列课程:') === 0) continue;
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
                <?php elseif ($has_preview && $preview_price == 0 && $source_price > 0): ?>
                  <span style="color: #52c41a; font-weight: 600;">预览免费</span>
                <?php elseif ($has_preview && $preview_price > 0 && $source_price > 0): ?>
                  <span style="color: #ff6b6b; font-weight: 600;"><?php echo intval($min_price); ?> 积分起</span>
                <?php else: ?>
                  <span style="color: #ff6b6b; font-weight: 600;"><?php echo intval(max($preview_price, $source_price)); ?> 积分</span>
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
                  <a href="course_info.php?id=<?php echo $course['id']; ?>" class="ui small primary button">
                    查看详情
                  </a>
                <?php elseif ($is_purchased): ?>
                  <a href="course_info.php?id=<?php echo $course['id']; ?>" class="ui small positive button">
                    <i class="check icon"></i>已拥有
                  </a>
                <?php else: ?>
                  <a href="course_info.php?id=<?php echo $course['id']; ?>" class="ui small primary button">
                    去查看
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- 分页导航 -->
  <?php if ($view_total_pages > 1): ?>
    <?php
      // 保留当前筛选参数，只替换 page
      $base_params = array();
      if ($view_current_subject > 0) $base_params['subject'] = $view_current_subject;
      if (!empty($view_current_tag)) $base_params['tag'] = $view_current_tag;
      if (!empty($view_search_keyword)) $base_params['search'] = $view_search_keyword;
      $page_link = function($p) use ($base_params) {
        $params = array_merge($base_params, array('page' => $p));
        return 'course.php?' . http_build_query($params);
      };
      // 显示页码范围：当前页前后各 2 页，首尾必现
      $start = max(1, $view_page - 2);
      $end = min($view_total_pages, $view_page + 2);
      if ($start > 1) $start = min($start, $end - 4 >= 1 ? $end - 4 : $start);
      if ($end < $view_total_pages) $end = max($end, $start + 4 <= $view_total_pages ? $start + 4 : $end);
      $start = max(1, $start);
      $end = min($view_total_pages, $end);
    ?>
    <div style="text-align: center; margin: 20px 0;">
      <div class="ui pagination menu" style="box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px;">
        <?php if ($view_page > 1): ?>
          <a class="item" href="<?php echo $page_link($view_page - 1); ?>">
            <i class="angle left icon"></i>
          </a>
        <?php else: ?>
          <a class="disabled item"><i class="angle left icon"></i></a>
        <?php endif; ?>

        <?php if ($start > 1): ?>
          <a class="item" href="<?php echo $page_link(1); ?>">1</a>
          <?php if ($start > 2): ?>
            <div class="disabled item">...</div>
          <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $start; $i <= $end; $i++): ?>
          <a class="item <?php echo $i == $view_page ? 'active' : ''; ?>"
             href="<?php echo $page_link($i); ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($end < $view_total_pages): ?>
          <?php if ($end < $view_total_pages - 1): ?>
            <div class="disabled item">...</div>
          <?php endif; ?>
          <a class="item" href="<?php echo $page_link($view_total_pages); ?>"><?php echo $view_total_pages; ?></a>
        <?php endif; ?>

        <?php if ($view_page < $view_total_pages): ?>
          <a class="item" href="<?php echo $page_link($view_page + 1); ?>">
            <i class="angle right icon"></i>
          </a>
        <?php else: ?>
          <a class="disabled item"><i class="angle right icon"></i></a>
        <?php endif; ?>
      </div>
      <div style="margin-top: 8px; color: #999; font-size: 0.85em;">
        <?php if (!empty($view_aggregate)): ?>
          共 <?php echo $view_total_courses; ?> 个课程卡片（系列已聚合），第 <?php echo $view_page; ?>/<?php echo $view_total_pages; ?> 页
        <?php else: ?>
          共 <?php echo $view_total_courses; ?> 个课件，第 <?php echo $view_page; ?>/<?php echo $view_total_pages; ?> 页
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- 创作者入驻引导 -->
  <div class="ui segment" style="border-radius: 12px; margin-top: 20px; padding: 10px !important; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border: 1px solid #667eea30;">
    <div style="text-align: center;">
      <div style="font-size: 1.1em; margin-bottom: 4px; font-weight: 600; color: #333;">
        🎁 有优质课件资源，欢迎联系我们，助您变现！
      </div>
      <div style="color: #555; margin-bottom: 6px; font-size: 0.9em;">
        平台优质流量 + 垂直精准客群 = 课件精准投放。
      </div>
      <div style="font-size: 1em;">
        <i class="qq icon" style="color: #12b7f5;"></i>
        咨询客服QQ：<strong onclick="copyCustomerQQ(this)" title="点击复制QQ号" style="color: #12b7f5; cursor: pointer; text-decoration: underline dotted #12b7f5; text-underline-offset: 3px;"><?php echo htmlentities($OJ_CUSTOMER_QQ, ENT_QUOTES, 'UTF-8');?></strong>
      </div>
    </div>
  </div>

</div>
<?php /* 复制客服QQ的公共方法 copyCustomerQQ() 定义在 footer.php，全站复用 */ ?>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
