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
            <i class="clock icon"></i>
            <span><?php echo $MSG_LESSON_COUNT; ?>: <?php echo intval($view_course['lesson_count']); ?></span>

            <?php if ($view_preview_price > 0 || $view_source_price > 0): ?>
              <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
                  <div style="background:#f0f0ff;padding:12px 16px;border-radius:8px;margin-bottom:10px;">
                      <i class="info circle icon"></i>
                      <a href="javascript:void(0)" onclick="showLoginPrompt()">登录</a> 或 <a href="registerpage.php">注册</a> 后可购买此课件
                  </div>
              <?php endif; ?>
              <span style="margin: 0 15px; color: #ddd;">|</span>
              <?php if ($view_preview_price > 0 && $view_source_price > 0): ?>
                <span style="color: #667eea; font-weight: 600;">预览版：<?php echo intval($view_preview_price); ?> 积分</span>
                <span style="margin: 0 10px; color: #ddd;">/</span>
                <span style="color: #52c41a; font-weight: 600;">原文件版：<?php echo intval($view_source_price); ?> 积分</span>
              <?php elseif ($view_preview_price > 0): ?>
                <span style="color: #667eea; font-weight: 600;">预览版：<?php echo intval($view_preview_price); ?> 积分</span>
              <?php else: ?>
                <span style="color: #52c41a; font-weight: 600;">原文件版：<?php echo intval($view_source_price); ?> 积分</span>
              <?php endif; ?>
            <?php else: ?>
              <span style="margin: 0 15px; color: #ddd;">|</span>
              <span style="color: #52c41a; font-weight: 600; font-size: 1.2em;"><?php echo $MSG_FREE; ?></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="six wide column right aligned">
          <!-- 操作按钮 -->
          <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
            <a class="ui teal button" href="javascript:void(0)" onclick="showLoginPrompt()">登录后购买</a>
          <?php elseif ($view_has_source_license): ?>
            <!-- 已拥有原文件版：显示已拥有完整权限 -->
            <!-- 已拥有原文件版：显示已拥有完整权限 -->
            <div class="ui positive message" style="padding: 15px 20px;">
              <i class="checkmark icon"></i>
              已拥有完整权限（可下载）
            </div>
          <?php elseif ($view_has_preview_license): ?>
            <!-- 仅拥有完整预览版：显示升级或已拥有提示 -->
            <?php if ($view_has_source_resource): ?>
              <?php if ($view_is_source_free): ?>
                <!-- 原文件版免费：显示限时免费获取按钮 -->
                <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2&upgrade=1"
                   class="ui large green button">
                  <i class="gift icon"></i> 限时免费获取原文件版
                </a>
              <?php elseif ($view_upgrade_price > 0): ?>
                <!-- 原文件版付费且可正常升级：显示升级按钮 -->
                <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2&upgrade=1"
                   class="ui large orange button">
                  <i class="arrow up icon"></i> 升级到原文件版 <?php echo intval($view_upgrade_price); ?> 积分
                </a>
              <?php else: ?>
                <!-- 原文件版付费但升级差价为 0（价格配置异常）：不提供升级入口 -->
                <div class="ui orange message" style="padding: 15px 20px;">
                  <i class="warning sign icon"></i>
                  价格配置异常，请联系管理员
                </div>
              <?php endif; ?>
            <?php else: ?>
              <div class="ui positive message" style="padding: 15px 20px;">
                <i class="checkmark icon"></i>
                已拥有完整预览权限
              </div>
            <?php endif; ?>
          <?php else: ?>
            <!-- 未购买任何权限：根据价格显示对应按钮 -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
              <?php if ($view_is_full_preview_free): ?>
                <!-- 完整预览版免费 -->
                <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=1"
                   class="ui large green button">
                  <i class="gift icon"></i>限时免费获取完整预览版
                </a>
              <?php elseif ($view_preview_price > 0): ?>
                <!-- 完整预览版付费 -->
                <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=1"
                   class="ui large primary button">
                  <i class="eye icon"></i> 解锁完整预览 <?php echo intval($view_preview_price); ?> 积分
                </a>
              <?php endif; ?>

              <?php if ($view_has_source_resource): ?>
                <?php if ($view_is_source_free): ?>
                  <!-- 原文件版免费 -->
                  <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2"
                     class="ui large green button">
                    <i class="gift icon"></i>限时免费获取原文件版
                  </a>
                <?php elseif ($view_source_price > 0): ?>
                  <!-- 原文件版付费 -->
                  <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2"
                     class="ui large positive button">
                    <i class="download icon"></i> 解锁原文件下载 <?php echo intval($view_source_price); ?> 积分
                  </a>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>


  <!-- 课程资源准备：判断是否存在资源 -->
  <?php
    $has_courseware = !empty($view_courseware_url) || !empty($view_course['courseware_link']);
    $has_lesson_plan = !empty($view_lesson_plan_url) || !empty($view_course['lesson_plan_link']);
    $has_resource = $has_courseware || $has_lesson_plan;
  ?>

  <!-- 课程内容：版本与购买（左） + 资源访问（右） -->
  <div class="ui segment" style="border-radius: 12px; margin-top: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 16px 18px;">
    <h3 class="ui header" style="color: #333; margin: 0 0 12px 0; font-size: 1.1em;">
      <i class="cube icon"></i> 课程内容
    </h3>

    <div class="ui stackable grid" style="margin: 0;">
      <!-- ===== 左列：版本与购买 ===== -->
      <div class="<?php echo $has_resource ? 'eight' : 'sixteen'; ?> wide column" style="padding: 6px;">
        <div style="font-size: 0.85em; color: #999; margin-bottom: 6px; padding-left: 2px;">
          <i class="key icon"></i> 选择版本
        </div>

        <!-- 完整预览版 -->
        <div class="ui card" style="width: 100%; margin: 0 0 8px 0; border-radius: 8px; border: 1px solid #667eea; box-shadow: none; <?php if ($view_has_preview_license): ?>opacity: 0.7;<?php endif; ?>">
          <div class="content" style="padding: 12px 14px;">
            <div class="header" style="font-size: 1em; margin-bottom: 6px; color: #667eea; display: flex; align-items: center;">
              <i class="eye icon" style="margin-right: 6px;"></i>完整预览版
              <?php if ($view_has_preview_license): ?>
                <div class="ui mini green label" style="margin-left: auto;">已拥有</div>
              <?php else: ?>
                <div class="ui mini grey label" style="margin-left: auto;">未解锁</div>
              <?php endif; ?>
            </div>
            <div style="color: #666; margin-bottom: 8px; line-height: 1.5; font-size: 0.85em;">
              只看不下载：课件与教案完整在线预览
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px;">
              <div style="font-size: 1.3em; font-weight: 700; color: #667eea;">
                <?php if ($view_is_full_preview_free): ?>
                  <span style="color: #21ba45;">限时免费</span>
                <?php else: ?>
                  <?php echo intval($view_preview_price); ?> 积分
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php if (!$view_has_preview_license && !$view_has_source_license): ?>
            <?php if ($view_is_full_preview_free): ?>
              <div class="extra content" style="padding: 10px 14px; background: #f8f9ff; border-radius: 0 0 8px 8px; text-align: center;">
                <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
                  <a class="ui small primary button" href="javascript:void(0)" onclick="showLoginPrompt()">登录后领取</a>
                <?php else: ?>
                  <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=1" class="ui small primary button">
                    <i class="gift icon"></i> 限时免费获取
                  </a>
                <?php endif; ?>
              </div>
            <?php elseif ($view_preview_price > 0): ?>
              <div class="extra content" style="padding: 10px 14px; background: #f8f9ff; border-radius: 0 0 8px 8px; text-align: center;">
                <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
                  <a class="ui small primary button" href="javascript:void(0)" onclick="showLoginPrompt()">登录后购买</a>
                <?php else: ?>
                  <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=1" class="ui small primary button">
                    <i class="shopping cart icon"></i> 立即购买
                  </a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          <?php elseif ($view_has_preview_license && !$view_has_source_license): ?>
            <div class="extra content" style="padding: 10px 14px; background: #f8f9ff; border-radius: 0 0 8px 8px; text-align: center;">
              <span style="color: #52c41a; font-weight: 600; font-size: 0.9em;">
                <i class="checkmark icon"></i> 已拥有
              </span>
            </div>
          <?php endif; ?>
        </div>

        <!-- 原文件版（只有存在资源时才显示） -->
        <?php if ($view_has_source_resource): ?>
        <div class="ui card" style="width: 100%; margin: 0; border-radius: 8px; border: 1px solid #52c41a; box-shadow: none; <?php if ($view_has_source_license): ?>opacity: 0.7;<?php endif; ?>">
          <div class="content" style="padding: 12px 14px;">
            <div class="header" style="font-size: 1em; margin-bottom: 6px; color: #52c41a; display: flex; align-items: center;">
              <i class="download icon" style="margin-right: 6px;"></i>原文件版
              <?php if ($view_has_source_license): ?>
                <div class="ui mini green label" style="margin-left: auto;">已拥有</div>
              <?php else: ?>
                <div class="ui mini grey label" style="margin-left: auto;">未解锁</div>
              <?php endif; ?>
            </div>
            <div style="color: #666; margin-bottom: 8px; line-height: 1.5; font-size: 0.85em;">
              可看可下载可编辑：含完整预览，可下载课件/教案原文件
            </div>
            <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap;">
              <div style="font-size: 1.3em; font-weight: 700; color: #52c41a;">
                <?php if ($view_has_only_preview): ?>
                  <?php if ($view_is_source_free): ?>
                    <span style="color: #21ba45;">限时免费升级</span>
                  <?php elseif ($view_upgrade_price > 0): ?>
                    <span><?php echo intval($view_upgrade_price); ?> 积分</span>
                    <span style="font-size: 0.7em; color: #999; font-weight: 400; margin-left: 6px;">
                      原价 <span style="text-decoration: line-through;"><?php echo intval($view_source_price); ?> 积分</span>
                    </span>
                  <?php else: ?>
                    <span style="color: #d9534f;">价格配置异常，请联系管理员</span>
                  <?php endif; ?>
                <?php else: ?>
                  <?php if ($view_is_source_free): ?>
                    <span style="color: #21ba45;">限时免费</span>
                  <?php else: ?>
                    <?php echo intval($view_source_price); ?> 积分
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
            <?php if ($view_has_only_preview && !$view_has_source_license && $view_upgrade_price > 0): ?>
              <div style="margin-top: 6px; color: #888; font-size: 0.8em; line-height: 1.5;">
                原文件版原价 <?php echo intval($view_source_price); ?> 积分，已抵扣完整预览版 <?php echo intval($view_preview_price); ?> 积分，本次仅补差价 <?php echo intval($view_upgrade_price); ?> 积分
              </div>
            <?php endif; ?>
          </div>
          <?php if (!$view_has_source_license): ?>
            <?php if ($view_has_only_preview): ?>
              <div class="extra content" style="padding: 10px 14px; background: #f0fff4; border-radius: 0 0 8px 8px; text-align: center;">
                <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
                  <a class="ui small positive button" href="javascript:void(0)" onclick="showLoginPrompt()">登录后升级</a>
                <?php else: ?>
                  <?php if ($view_is_source_free): ?>
                    <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2&upgrade=1" class="ui small positive button">
                      <i class="gift icon"></i> 限时免费升级
                    </a>
                  <?php elseif ($view_upgrade_price > 0): ?>
                    <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2&upgrade=1" class="ui small positive button">
                      <i class="arrow up icon"></i> 抵扣后升级 <?php echo intval($view_upgrade_price); ?> 积分
                    </a>
                  <?php else: ?>
                    <span style="color: #d9534f;">价格配置异常，请联系管理员</span>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <?php if ($view_is_source_free): ?>
                <div class="extra content" style="padding: 10px 14px; background: #f0fff4; border-radius: 0 0 8px 8px; text-align: center;">
                  <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
                    <a class="ui small positive button" href="javascript:void(0)" onclick="showLoginPrompt()">登录后领取</a>
                  <?php else: ?>
                    <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2" class="ui small positive button">
                      <i class="gift icon"></i> 限时免费获取
                    </a>
                  <?php endif; ?>
                </div>
              <?php elseif ($view_source_price > 0): ?>
                <div class="extra content" style="padding: 10px 14px; background: #f0fff4; border-radius: 0 0 8px 8px; text-align: center;">
                  <?php if (!isset($_SESSION[$OJ_NAME.'_'.'user_id'])): ?>
                    <a class="ui small positive button" href="javascript:void(0)" onclick="showLoginPrompt()">登录后购买</a>
                  <?php else: ?>
                    <a href="course_get.php?id=<?php echo $view_course['id']; ?>&type=2" class="ui small positive button">
                      <i class="shopping cart icon"></i> 立即购买
                    </a>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          <?php elseif ($view_has_source_license): ?>
            <div class="extra content" style="padding: 10px 14px; background: #f0fff4; border-radius: 0 0 8px 8px; text-align: center;">
              <span style="color: #52c41a; font-weight: 600; font-size: 0.9em;">
                <i class="checkmark icon"></i> 已拥有
              </span>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- ===== 右列：资源访问（中性卡片 + 状态小标签，不与左侧定价卡抢色） ===== -->
      <?php if ($has_resource): ?>
      <div class="eight wide column" style="padding: 6px;">
        <div style="font-size: 0.85em; color: #999; margin-bottom: 6px; padding-left: 2px;">
          <i class="file text icon"></i> 课程资源 · 点击查看/下载
        </div>

        <!-- 课件资源行 -->
        <?php if ($has_courseware): ?>
        <div class="ui card" style="width: 100%; margin: 0 0 8px 0; border-radius: 8px; box-shadow: none; border: 1px solid #eaeaea;">
          <div class="content" style="padding: 12px 14px;">
            <div style="display: flex; align-items: center; margin-bottom: 6px;">
              <i class="file alternate outline icon" style="color: #667eea; margin-right: 6px;"></i>
              <span style="font-weight: 600; font-size: 1em;"><?php echo $MSG_COURSEWARE; ?></span>
              <?php if ($view_has_source_license): ?>
                <div class="ui mini green label" style="margin-left: auto;"><i class="checkmark icon"></i>已解锁 · 可下载</div>
              <?php elseif ($view_has_preview_license): ?>
                <div class="ui mini blue label" style="margin-left: auto;"><i class="checkmark icon"></i>已解锁 · 仅预览</div>
              <?php elseif (!empty($view_courseware_url)): ?>
                <div class="ui mini orange label" style="margin-left: auto;">试看版</div>
              <?php else: ?>
                <div class="ui mini grey label" style="margin-left: auto;"><i class="lock icon"></i>未解锁</div>
              <?php endif; ?>
            </div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
              <?php if ($view_has_source_license): ?>
                <!-- 已购原文件版：完整预览 + 下载 -->
                <?php if (!empty($view_courseware_full_preview_url)): ?>
                  <a href="<?php echo htmlspecialchars($view_courseware_full_preview_url, ENT_QUOTES, 'UTF-8'); ?>"
                     target="_blank" rel="noopener noreferrer" class="ui small primary button">
                    <i class="eye icon"></i> ✓ 查看完整预览
                  </a>
                <?php endif; ?>
                <?php if (!empty($view_course['courseware_link'])): ?>
                  <a href="<?php echo htmlspecialchars($view_course['courseware_link'], ENT_QUOTES, 'UTF-8'); ?>"
                     target="_blank" rel="noopener noreferrer" class="ui small positive button">
                    <i class="download icon"></i> ✓ 下载原文件
                  </a>
                <?php endif; ?>
              <?php elseif ($view_has_preview_license): ?>
                <!-- 已购预览版：仅完整预览 -->
                <?php if (!empty($view_courseware_full_preview_url)): ?>
                  <a href="<?php echo htmlspecialchars($view_courseware_full_preview_url, ENT_QUOTES, 'UTF-8'); ?>"
                     target="_blank" rel="noopener noreferrer" class="ui small primary button">
                    <i class="eye icon"></i> ✓ 查看完整预览
                  </a>
                <?php else: ?>
                  <span style="color: #52c41a; font-size: 0.9em;"><i class="checkmark icon"></i> 已拥有完整预览</span>
                <?php endif; ?>
                <?php if (!empty($view_course['courseware_link'])): ?>
                  <div style="width: 100%; margin-top: 4px; color: #999; font-size: 0.8em;">
                    <i class="lock icon"></i> 下载原文件需升级原文件版
                  </div>
                <?php endif; ?>
              <?php else: ?>
                <!-- 未购：仅部分内容预览 -->
                <?php if (!empty($view_courseware_url)): ?>
                  <a href="<?php echo htmlspecialchars($view_courseware_url, ENT_QUOTES, 'UTF-8'); ?>"
                     target="_blank" rel="noopener noreferrer" class="ui small basic primary button">
                    <i class="external alternate icon"></i> 查看免费试看
                  </a>
                  <div style="width: 100%; margin-top: 4px; color: #999; font-size: 0.8em;">
                    <i class="lock icon"></i> 完整内容需在左侧解锁
                  </div>
                <?php else: ?>
                  <span style="color: #999; font-size: 0.9em;"><i class="lock icon"></i> 购买后可访问</span>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- 教案资源行 -->
        <?php if ($has_lesson_plan): ?>
        <div class="ui card" style="width: 100%; margin: 0; border-radius: 8px; box-shadow: none; border: 1px solid #eaeaea;">
          <div class="content" style="padding: 12px 14px;">
            <div style="display: flex; align-items: center; margin-bottom: 6px;">
              <i class="book outline icon" style="color: #52c41a; margin-right: 6px;"></i>
              <span style="font-weight: 600; font-size: 1em;"><?php echo $MSG_LESSON_PLAN; ?></span>
              <?php if ($view_has_source_license): ?>
                <div class="ui mini green label" style="margin-left: auto;"><i class="checkmark icon"></i>已解锁 · 可下载</div>
              <?php elseif ($view_has_preview_license): ?>
                <div class="ui mini blue label" style="margin-left: auto;"><i class="checkmark icon"></i>已解锁 · 仅预览</div>
              <?php elseif (!empty($view_lesson_plan_url)): ?>
                <div class="ui mini orange label" style="margin-left: auto;">试看版</div>
              <?php else: ?>
                <div class="ui mini grey label" style="margin-left: auto;"><i class="lock icon"></i>未解锁</div>
              <?php endif; ?>
            </div>
            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
              <?php if ($view_has_source_license): ?>
                <?php if (!empty($view_lesson_plan_full_preview_url)): ?>
                  <a href="<?php echo htmlspecialchars($view_lesson_plan_full_preview_url, ENT_QUOTES, 'UTF-8'); ?>"
                     target="_blank" rel="noopener noreferrer" class="ui small primary button">
                    <i class="eye icon"></i> ✓ 查看完整预览
                  </a>
                <?php endif; ?>
                <?php if (!empty($view_course['lesson_plan_link'])): ?>
                  <a href="<?php echo htmlspecialchars($view_course['lesson_plan_link'], ENT_QUOTES, 'UTF-8'); ?>"
                     target="_blank" rel="noopener noreferrer" class="ui small positive button">
                    <i class="download icon"></i> ✓ 下载原文件
                  </a>
                <?php endif; ?>
              <?php elseif ($view_has_preview_license): ?>
                <?php if (!empty($view_lesson_plan_full_preview_url)): ?>
                  <a href="<?php echo htmlspecialchars($view_lesson_plan_full_preview_url, ENT_QUOTES, 'UTF-8'); ?>"
                     target="_blank" rel="noopener noreferrer" class="ui small primary button">
                    <i class="eye icon"></i> ✓ 查看完整预览
                  </a>
                <?php else: ?>
                  <span style="color: #52c41a; font-size: 0.9em;"><i class="checkmark icon"></i> 已拥有完整预览</span>
                <?php endif; ?>
                <?php if (!empty($view_course['lesson_plan_link'])): ?>
                  <div style="width: 100%; margin-top: 4px; color: #999; font-size: 0.8em;">
                    <i class="lock icon"></i> 下载原文件需升级原文件版
                  </div>
                <?php endif; ?>
              <?php else: ?>
                <?php if (!empty($view_lesson_plan_url)): ?>
                  <a href="<?php echo htmlspecialchars($view_lesson_plan_url, ENT_QUOTES, 'UTF-8'); ?>"
                     target="_blank" rel="noopener noreferrer" class="ui small basic primary button">
                    <i class="external alternate icon"></i> 查看免费试看
                  </a>
                  <div style="width: 100%; margin-top: 4px; color: #999; font-size: 0.8em;">
                    <i class="lock icon"></i> 完整内容需在左侧解锁
                  </div>
                <?php else: ?>
                  <span style="color: #999; font-size: 0.9em;"><i class="lock icon"></i> 购买后可访问</span>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- 底部统一提示 -->
    <div style="margin-top: 10px; padding: 8px 12px; background: #fffbeb; border-radius: 6px; border-left: 3px solid #f59e0b; font-size: 0.85em; color: #92400e;">
      💡 仅参考学习选完整预览版；需下载修改选原文件版。积分解锁后永久使用。
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

</div>

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
        咨询客服QQ：
        <a href="tencent://message/?uin=2326077585" style="color: #12b7f5; font-weight: 600;">
          2326077585
        </a>
      </div>
    </div>
  </div>


<script>
function showLoginPrompt() {
    // 与「注册」按钮一致，直接跳转登录页（不再弹确认框）
    // 登录成功后回到当前课件详情页继续购买
    var returnUrl = window.location.pathname + window.location.search;
    window.location.href = 'loginpage.php?redirect=' + encodeURIComponent(returnUrl);
}
</script>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
