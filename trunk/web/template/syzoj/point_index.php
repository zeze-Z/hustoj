<?php $show_title = "我的积分 - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php"); ?>
<div class="padding">

  <h2 class="ui header" style="margin-bottom: 20px;">
    <i class="dollar sign icon"></i>
    <div class="content">
      我的积分
      <div class="sub header">通过发卡网购买充值卡，在此兑换积分；积分用于购买课件，1积分=1元。</div>
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

  <!-- 积分概览 -->
  <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
    <div class="ui grid">
      <div class="row">
        <div class="six wide column">
          <div style="color:#666;">当前积分余额</div>
          <div style="font-size: 2.4em; color: #d4380d; font-weight: 700;">
            <?php echo intval($view_balance); ?> <small style="font-size:0.4em;color:#999;">积分</small>
          </div>
          <div style="color:#999;font-size:12px;">1积分 = 1元，可用于课件购买</div>
        </div>
        <div class="ten wide column">
          <div style="background:#fff7e6;border:1px solid #ffe1a8;border-radius:8px;padding:12px;">
            <div style="font-weight:600;color:#ad4e00;margin-bottom:6px;">
              <i class="info circle icon"></i>如何充值积分？
            </div>
            <ol style="margin:0 0 0 18px;color:#666;">
              <li>点击下方“发卡网商品链接”购买充值卡（每张固定兑换 <strong><?php echo intval($view_card_value); ?></strong> 积分）。</li>
              <li>支付完成后从发卡网获取卡号与卡密。</li>
              <li>在下方“充值卡兑换”表单中输入卡号、卡密，点击“立即兑换”。</li>
            </ol>
            <div style="margin-top:10px;">
              <a href="<?php echo htmlentities($view_faka_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="ui orange button">
                <i class="external alternate icon"></i>前往发卡网购买充值卡
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 充值卡兑换 -->
  <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
    <h3 class="ui header" style="margin-top:0;">
      <i class="key icon"></i>充值卡兑换
    </h3>
    <form method="POST" action="point_redeem.php" class="ui form" autocomplete="off">
      <input type="hidden" name="postkey" value="<?php echo htmlentities($view_postkey, ENT_QUOTES, 'UTF-8'); ?>">
      <div class="two fields">
        <div class="field">
          <label>卡号</label>
          <input type="text" name="card_no" placeholder="请输入发卡网获取的卡号" maxlength="64" autocomplete="off" required>
        </div>
        <div class="field">
          <label>卡密</label>
          <input type="password" name="card_secret" placeholder="请输入卡密" maxlength="64" autocomplete="off" required>
        </div>
      </div>
      <button type="submit" class="ui primary button">
        <i class="check icon"></i>立即兑换
      </button>
      <span style="margin-left:12px;color:#999;font-size:12px;">每张卡固定兑换 <?php echo intval($view_card_value); ?> 积分。</span>
    </form>
  </div>

  <!-- 积分流水 -->
  <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
    <h3 class="ui header" style="margin-top:0;">
      <i class="list icon"></i>积分流水
    </h3>
    <div style="margin-bottom:10px;">
      <?php
        $type_links = [
          0 => '全部',
          POINT_LOG_TYPE_CARD => '充值卡兑换',
          POINT_LOG_TYPE_COURSE => '课件购买',
          POINT_LOG_TYPE_ADMIN => '管理员调整',
          POINT_LOG_TYPE_SYSTEM => '系统操作',
        ];
        foreach ($type_links as $k => $label):
          $active = ($view_type_filter == $k);
      ?>
        <a class="ui small <?php echo $active ? 'primary' : 'basic'; ?> button"
           href="point_index.php?type=<?php echo intval($k); ?>">
          <?php echo htmlentities($label, ENT_QUOTES, 'UTF-8'); ?>
        </a>
      <?php endforeach; ?>
    </div>
    <table class="ui small table">
      <thead>
        <tr>
          <th>时间</th>
          <th>类型</th>
          <th style="text-align:right;">积分变动</th>
          <th style="text-align:right;">交易后余额</th>
          <th>关联</th>
          <th>备注</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($view_logs)): ?>
          <tr><td colspan="6" style="text-align:center;color:#999;">暂无积分流水</td></tr>
        <?php else: ?>
          <?php $type_map = [
            POINT_LOG_TYPE_CARD => '充值卡兑换',
            POINT_LOG_TYPE_COURSE => '课件购买',
            POINT_LOG_TYPE_ADMIN => '管理员调整',
            POINT_LOG_TYPE_SYSTEM => '系统操作',
          ]; ?>
          <?php foreach ($view_logs as $log): ?>
            <?php
              $change = intval($log['change_point']);
              $type_text = isset($type_map[$log['type']]) ? $type_map[$log['type']] : '未知';
              $sign = $change > 0 ? '+' : '';
              $color = $change > 0 ? '#52c41a' : '#d4380d';
            ?>
            <tr>
              <td><?php echo htmlentities($log['create_time'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities($type_text, ENT_QUOTES, 'UTF-8'); ?></td>
              <td style="text-align:right;color:<?php echo $color; ?>;font-weight:600;">
                <?php echo $sign . $change; ?>
              </td>
              <td style="text-align:right;"><?php echo intval($log['balance']); ?></td>
              <td><?php echo htmlentities((string)$log['relation_id'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities((string)$log['remark'], ENT_QUOTES, 'UTF-8'); ?></td>
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
  <div class="ui segment" style="border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
    <h3 class="ui header" style="margin-top:0;">
      <i class="book icon"></i>我的课件
    </h3>
    <table class="ui small table">
      <thead>
        <tr>
          <th>订单号</th>
          <th>课件名称</th>
          <th>权限类型</th>
          <th style="text-align:right;">消耗积分</th>
          <th>支付方式</th>
          <th>获取时间</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($view_courses)): ?>
          <tr><td colspan="7" style="text-align:center;color:#999;">暂无购买记录</td></tr>
        <?php else: ?>
          <?php
            $license_map = [1 => '完整预览版', 2 => '原文件版'];
            $pay_map = [
              'point' => '积分支付',
              'free' => '免费获取',
              'alipay' => '支付宝(历史)',
              'wxpay' => '微信(历史)',
            ];
            foreach ($view_courses as $row):
              $license_text = isset($license_map[$row['license_type']]) ? $license_map[$row['license_type']] : '历史/未知';
              $pay_text = isset($pay_map[$row['pay_channel']]) ? $pay_map[$row['pay_channel']] : ($row['pay_channel'] ?: '未知');
              $point_amount = intval(round(floatval($row['amount'])));
              $time_text = !empty($row['pay_time']) ? $row['pay_time'] : $row['created_at'];
          ?>
            <tr>
              <td style="font-family:monospace;"><?php echo htmlentities($row['order_no'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities($row['course_title'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities($license_text, ENT_QUOTES, 'UTF-8'); ?></td>
              <td style="text-align:right;"><?php echo $point_amount; ?> 积分</td>
              <td><?php echo htmlentities($pay_text, ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlentities((string)$time_text, ENT_QUOTES, 'UTF-8'); ?></td>
              <td>
                <a class="ui small basic button"
                   href="course_info.php?id=<?php echo intval($row['course_id']); ?>">查看</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<script>
$('.message .close').on('click', function() {
  $(this).closest('.message').transition('fade');
});
</script>

<?php include("template/$OJ_TEMPLATE/footer.php"); ?>
