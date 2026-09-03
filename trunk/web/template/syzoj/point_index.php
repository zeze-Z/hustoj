<?php $show_title = "我的积分 - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>
<div class="padding">

  <h2 class="ui header" style="margin-bottom: 20px;">
    <i class="dollar sign icon"></i>
    <div class="content">
      我的积分
      <div class="sub header">积分用于购买课件，不支持提现，请按需充值。</div>
    </div>
  </h2>

  <?php if (!empty($view_flash)): ?>
    <?php $is_ok = ($view_flash_type === 'success'); ?>
    <div class="ui <?php echo $is_ok ? 'success' : 'error'; ?> message" style="border-radius: 12px;">
      <i class="close icon"></i>
      <div class="header"><?php echo $is_ok ? '兑换成功' : '兑换失败'; ?></div>
      <p><?php echo htmlentities($view_flash, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
  <?php endif; ?>

  <!-- 签到进度卡（常驻入口，后续登录也能看到签到状态） -->
  <?php echo login_reward_streak_card_html($view_streak_info, $view_reward_points); ?>

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

  <!-- 我的积分 + 充值卡兑换：左右并排 -->
  <div class="ui two column stackable grid">
    <div class="column">
      <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); height: 100%; padding: 12px 16px;">
        <h3 class="ui header" style="margin-top:0; margin-bottom: 6px; font-size: 1em;">
          <i class="dollar sign icon"></i>积分余额
        </h3>
        <div style="text-align:center; padding: 6px 0;">
          <div style="font-size: 2em; color: #d4380d; font-weight: 700; line-height: 1;">
            <?php echo intval($view_balance); ?>
          </div>
          <div style="color:#999;font-size:12px;margin-top:4px;">当前可用积分</div>
        </div>
      </div>
    </div>

    <div class="column">
      <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); height: 100%; padding: 12px 16px;">
        <h3 class="ui header" style="margin-top:0; margin-bottom: 6px; font-size: 1em;">
          <i class="key icon"></i>积分余额充值
        </h3>
        <form method="POST" action="point_redeem.php" class="ui form mini" autocomplete="off">
          <input type="hidden" name="postkey" value="<?php echo htmlentities($view_postkey, ENT_QUOTES, 'UTF-8'); ?>">
          <div class="two fields" style="margin-bottom: 6px;">
            <div class="field">
              <input type="text" name="card_no" placeholder="卡号" maxlength="64" autocomplete="off" required>
            </div>
            <div class="field">
              <input type="password" name="card_secret" placeholder="卡密" maxlength="64" autocomplete="off" required>
            </div>
          </div>
          <button type="submit" class="ui primary fluid mini button">
            <i class="check icon"></i>立即兑换
          </button>
          <div style="text-align:center; margin-top:8px;">
            <a href="<?php echo htmlentities($view_faka_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" style="color:#1890ff; font-size:13px; font-weight:600; text-decoration:underline;">
              <i class="shopping cart icon" style="margin-right:4px;"></i>没有充值卡？<u>立即获取充值卡</u> <i class="external alternate icon" style="font-size:10px;"></i>
            </a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- 积分流水 -->
  <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
    <h3 class="ui header" style="margin-top:0;">
      <i class="list icon"></i>积分流水
    </h3>
    <div style="margin-bottom:10px;">
      <select onchange="location.href='point_index.php?type='+this.value" style="padding:4px 8px;border-radius:6px;border:1px solid #ddd;font-size:13px;">
        <option value="0" <?php if ($view_type_filter == 0) echo 'selected'; ?>>全部类型</option>
        <option value="<?php echo POINT_LOG_TYPE_CARD; ?>" <?php if ($view_type_filter == POINT_LOG_TYPE_CARD) echo 'selected'; ?>>充值卡兑换</option>
        <option value="<?php echo POINT_LOG_TYPE_COURSE; ?>" <?php if ($view_type_filter == POINT_LOG_TYPE_COURSE) echo 'selected'; ?>>课件购买</option>
        <option value="<?php echo POINT_LOG_TYPE_ADMIN; ?>" <?php if ($view_type_filter == POINT_LOG_TYPE_ADMIN) echo 'selected'; ?>>管理员调整</option>
        <option value="<?php echo POINT_LOG_TYPE_SYSTEM; ?>" <?php if ($view_type_filter == POINT_LOG_TYPE_SYSTEM) echo 'selected'; ?>>系统操作</option>
        <option value="<?php echo POINT_LOG_TYPE_PROMO; ?>" <?php if ($view_type_filter == POINT_LOG_TYPE_PROMO) echo 'selected'; ?>>推广奖励</option>
      </select>
    </div>
    <table class="ui small table">
      <thead>
        <tr>
          <th>时间</th>
          <th>类型</th>
          <th style="text-align:right;">积分变动</th>
          <th style="text-align:right;">交易后余额</th>
          <th>详情 / 课件</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($view_logs)): ?>
          <tr><td colspan="5" style="text-align:center;color:#999;">暂无积分流水</td></tr>
        <?php else: ?>
          <?php $type_map = [
            POINT_LOG_TYPE_CARD => '充值卡兑换',
            POINT_LOG_TYPE_COURSE => '课件购买',
            POINT_LOG_TYPE_ADMIN => '管理员调整',
            POINT_LOG_TYPE_SYSTEM => '系统操作',
            POINT_LOG_TYPE_PROMO => '推广奖励',
          ];
          $license_map = [1 => '完整预览版', 2 => '原文件版'];
          foreach ($view_logs as $log):
            $change = intval($log['change_point']);
            $type_text = isset($type_map[$log['type']]) ? $type_map[$log['type']] : '未知';
            $sign = $change > 0 ? '+' : '';
            $color = $change > 0 ? '#52c41a' : '#d4380d';
            $is_course = intval($log['type']) === POINT_LOG_TYPE_COURSE;
          ?>
            <tr>
              <td><?php echo htmlentities($log['create_time'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td>
                <?php echo htmlentities($type_text, ENT_QUOTES, 'UTF-8'); ?>
              </td>
              <td style="text-align:right;color:<?php echo $color; ?>;font-weight:600;">
                <?php echo $sign . $change; ?>
              </td>
              <td style="text-align:right;"><?php echo intval($log['balance']); ?></td>
              <td>
                <?php if ($is_course && !empty($log['co_course_id'])): ?>
                  <a href="course_info.php?id=<?php echo intval($log['co_course_id']); ?>">
                    <?php echo htmlentities($log['co_course_title'] ?: '课件#' . $log['co_course_id'], ENT_QUOTES, 'UTF-8'); ?>
                  </a>
                  <?php if (!empty($log['co_license_type'])): ?>
                    <?php $lt = isset($license_map[$log['co_license_type']]) ? $license_map[$log['co_license_type']] : '未知'; ?>
                    <span class="ui tiny label" style="margin-left:6px;<?php echo $log['co_license_type'] == 1 ? 'background:#667eea;color:#fff;' : 'background:#52c41a;color:#fff;'; ?>">
                      <?php echo htmlentities($lt, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                  <?php endif; ?>
                  <small style="color:#999;margin-left:6px;">(<?php echo intval(abs($change)); ?> 积分)</small>
                <?php elseif ($log['type'] == POINT_LOG_TYPE_CARD): ?>
                  <?php echo htmlentities((string)$log['remark'], ENT_QUOTES, 'UTF-8'); ?>
                  <small style="color:#999;"><?php echo htmlentities((string)$log['relation_id'], ENT_QUOTES, 'UTF-8'); ?></small>
                <?php else: ?>
                  <?php echo htmlentities((string)$log['remark'], ENT_QUOTES, 'UTF-8'); ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($view_total_pages > 1): ?>
      <div style="text-align:center;margin-top:15px;">
        <div class="ui pagination menu">
          <?php if ($view_page > 1): ?>
            <a class="item" href="point_index.php?type=<?php echo intval($view_type_filter); ?>&page=<?php echo $view_page - 1; ?>">
              <i class="left chevron icon"></i>上一页
            </a>
          <?php endif; ?>
          <?php
            $start = max(1, $view_page - 2);
            $end = min($view_total_pages, $view_page + 2);
            for ($i = $start; $i <= $end; $i++):
          ?>
            <a class="item <?php echo $i == $view_page ? 'active' : ''; ?>"
               href="point_index.php?type=<?php echo intval($view_type_filter); ?>&page=<?php echo $i; ?>">
              <?php echo $i; ?>
            </a>
          <?php endfor; ?>
          <?php if ($view_page < $view_total_pages): ?>
            <a class="item" href="point_index.php?type=<?php echo intval($view_type_filter); ?>&page=<?php echo $view_page + 1; ?>">
              下一页<i class="right chevron icon"></i>
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- 已购买课件 -->
  <!-- 已合并至上方“积分流水”，type=2 行可直接点击课件名跳转 -->

</div>

<script>
$('.message .close').on('click', function() {
  $(this).closest('.message').transition('fade');
});
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
