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
      <p><strong>奖励规则：</strong>联系客服导入班级学生后，每周若有 <strong>60%</strong> 学生登录平台，您即可获得 <strong>5 积分</strong> 奖励。</p>
      <p><strong>奖励周期：</strong>连续 <strong>4 周</strong>，每周达标每周发放。最多可获 <strong>20 积分</strong>。</p>
      <p><strong>参与方式：</strong>联系客服批量开通学生账号即可参与。</p>
      <p><strong>积分用途：</strong>购买课件、课前游戏离线包等平台服务。</p>
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

  <!-- 课件订单区块 -->
  <h3 style="margin: 28px 0 12px 0; font-size: 1.15em; color: #333;"><span style="color: #667eea;">▌</span>课件订单</h3>
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

  <!-- 积分商品订单区块 -->
  <h3 style="margin: 32px 0 12px 0; font-size: 1.15em; color: #333;"><span style="color: #667eea;">▌</span>积分商品订单</h3>
  <?php if (empty($view_og_orders)): ?>
    <div class="ui info message" style="text-align: center; padding: 40px 20px;">
      <i class="inbox icon" style="font-size: 3em; margin-bottom: 15px;"></i>
      <p>暂无积分商品订单</p>
      <a href="more.php#games" class="ui primary button" style="margin-top: 15px;">
        <i class="gamepad icon"></i>去兑换离线游戏安装包
      </a>
    </div>
  <?php else: ?>
    <div class="ui segments">
      <?php foreach ($view_og_orders as $og_index => $og_order): ?>
        <?php $og_expired = $og_order['expire_date'] < date('Y-m-d'); ?>
        <div class="ui segment" style="border-radius: 0; margin-bottom: 0;">
          <div class="ui grid">
            <div class="row">
              <div class="twelve wide column">
                <h3 style="margin: 0 0 10px 0; color: #333; font-size: 1em;">
                  <?php echo htmlentities($view_goods_titles[$og_order['product_key']] ?? $og_order['product_key'], ENT_QUOTES, 'UTF-8'); ?>
                  <span style="color: #999; font-weight: normal; font-size: 0.9em;">订单号: <?php echo htmlentities($og_order['order_no'], ENT_QUOTES, 'UTF-8'); ?></span>
                </h3>
                <div style="color: #666; font-size: 0.9em;">
                  <?php if ($og_order['school_name'] !== ''): ?><span><i class="building icon"></i> <?php echo htmlentities($og_order['school_name'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlentities($og_order['room_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <span style="margin: 0 15px;">|</span><?php endif; ?>
                  <?php if ($og_expired): ?>
                    <span style="color: #999;"><i class="calendar times icon"></i>已过期</span>
                  <?php else: ?>
                    <span style="color: #52c41a;"><i class="calendar check icon"></i>有效期至 <?php echo htmlentities($og_order['expire_date'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>
                  <span style="margin: 0 15px;">|</span>
                  <span style="color: #ff6b6b; font-weight: 600;"><?php echo intval($og_order['point_amount']); ?> 积分</span>
                </div>
              </div>
              <div class="four wide column right aligned">
                <button type="button" class="ui small primary button" onclick="ogdShowDetail(<?php echo $og_index; ?>)">
                  <i class="eye icon"></i>查看详情
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>

<script>
// 关闭消息提示
$('.message .close').on('click', function() {
  $(this).closest('.message').transition('fade');
});
</script>

<!-- 离线游戏安装包订单详情弹窗 -->
<style>
/* ===== 订单详情弹窗（参照 more.php og-modal 简化版） ===== */
.ogd-mask {
    display: none;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 99999 !important;
    padding: 20px;
    box-sizing: border-box !important;
    margin: 0 !important;
    animation: ogd-fade-in 0.25s ease;
}

@keyframes ogd-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

.ogd-mask.show {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.ogd-modal {
    background: #fff;
    border-radius: 20px;
    max-width: 480px !important;
    width: 100% !important;
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    box-shadow: 0 24px 64px rgba(15, 23, 42, 0.35);
    position: relative !important;
    top: auto !important;
    left: auto !important;
    right: auto !important;
    transform: none !important;
    margin: 0 !important;
    float: none !important;
    animation: ogd-modal-in 0.35s cubic-bezier(0.34, 1.4, 0.64, 1);
}

@keyframes ogd-modal-in {
    from { opacity: 0; transform: scale(0.92) translateY(16px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.ogd-close {
    position: absolute;
    top: 14px; right: 14px;
    z-index: 5;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.28);
    color: #fff;
    font-size: 18px;
    line-height: 1;
    cursor: pointer;
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s;
    padding: 0;
}

.ogd-close:hover {
    background: rgba(255, 255, 255, 0.32);
    transform: rotate(90deg);
}

.ogd-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px 20px 0 0;
    padding: 22px 28px;
    padding-right: 52px;
    color: #fff;
}

.ogd-header h3 {
    margin: 0 0 4px 0;
    font-size: 1.1rem;
    font-weight: 700;
}

.ogd-header p {
    margin: 0;
    font-size: 0.8rem;
    opacity: 0.85;
}

.ogd-body {
    padding: 20px 24px 24px;
}

.ogd-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 16px;
}

.ogd-field {
    background: #f8f9fc;
    border: 1px solid #e6eaff;
    border-radius: 10px;
    padding: 8px 12px;
    min-width: 0;
}

.ogd-field.ogd-field-full {
    grid-column: 1 / -1;
}

.ogd-field-label {
    display: block;
    font-size: 0.72rem;
    color: #888;
    margin-bottom: 2px;
}

.ogd-field-value {
    display: block;
    font-size: 0.88rem;
    font-weight: 600;
    color: #333;
    word-break: break-all;
}

.ogd-field-value.ogd-mono {
    font-family: Consolas, Monaco, 'Courier New', monospace;
    font-weight: 500;
}

.ogd-license-label {
    font-size: 0.8rem;
    font-weight: 700;
    color: #374151;
    margin-bottom: 8px;
}

.ogd-license {
    font-family: Consolas, Monaco, 'Courier New', monospace;
    font-size: 0.8rem;
    word-break: break-all;
    white-space: pre-wrap;
    color: #333;
    line-height: 1.6;
    background: #f8f9fc;
    border: 1.5px dashed #c7d2fe;
    border-radius: 12px;
    padding: 12px 14px;
    max-height: 130px;
    overflow-y: auto;
    margin: 0;
}

.ogd-copy-btn {
    display: block;
    width: 100%;
    background: #eef2ff;
    color: #4f46e5;
    border: 1px solid #e0e7ff;
    padding: 10px;
    border-radius: 10px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    margin-top: 10px;
}

.ogd-copy-btn:hover {
    background: #e0e7ff;
}

.ogd-copy-btn.ogd-copied {
    background: #ecfdf5;
    color: #059669;
    border-color: #a7f3d0;
}

.ogd-download {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    padding: 13px;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 700;
    text-decoration: none;
    text-align: center;
    margin: 14px 0 16px;
    transition: all 0.25s;
    box-shadow: 0 6px 18px rgba(34, 197, 94, 0.3);
}

.ogd-download:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(34, 197, 94, 0.42);
    text-decoration: none;
    color: #fff;
}

.ogd-steps {
    background: linear-gradient(135deg, #f8f9ff, #f1f4ff);
    border: 1px solid #e6eaff;
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 0;
}

.ogd-steps-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
}

.ogd-step {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.ogd-step + .ogd-step {
    margin-top: 10px;
}

.ogd-step-num {
    flex-shrink: 0;
    width: 20px; height: 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.68rem;
    font-weight: 700;
}

.ogd-step-text {
    font-size: 0.8rem;
    color: #666;
    line-height: 1.5;
}

@media (max-width: 480px) {
    .ogd-fields {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="ogd-mask" id="ogd-mask">
    <div class="ogd-modal" role="dialog" aria-modal="true" aria-labelledby="ogd-title">
        <button type="button" class="ogd-close" onclick="ogdCloseModal()" aria-label="关闭">&times;</button>
        <div class="ogd-header">
            <h3 id="ogd-title">离线游戏安装包订单</h3>
            <p>授权详情与激活指引</p>
        </div>
        <div class="ogd-body">
            <!-- 订单字段 -->
            <div class="ogd-fields">
                <div class="ogd-field ogd-field-full">
                    <span class="ogd-field-label">订单号</span>
                    <span class="ogd-field-value ogd-mono" id="ogd-f-order-no"></span>
                </div>
                <div class="ogd-field">
                    <span class="ogd-field-label">学校</span>
                    <span class="ogd-field-value" id="ogd-f-school"></span>
                </div>
                <div class="ogd-field">
                    <span class="ogd-field-label">机房</span>
                    <span class="ogd-field-value" id="ogd-f-room"></span>
                </div>
                <div class="ogd-field ogd-field-full">
                    <span class="ogd-field-label">有效期</span>
                    <span class="ogd-field-value" id="ogd-f-expire"></span>
                </div>
                <div class="ogd-field ogd-field-full">
                    <span class="ogd-field-label">消耗积分</span>
                    <span class="ogd-field-value" id="ogd-f-point"></span>
                </div>
            </div>

            <!-- 授权码（无授权码的商品订单隐藏此区） -->
            <div id="ogd-license-area">
                <div class="ogd-license-label">授权码</div>
                <pre class="ogd-license" id="ogd-license"></pre>
                <button type="button" class="ogd-copy-btn" id="ogd-copy-btn" onclick="ogdCopyLicense()">📋 复制授权码</button>
            </div>

            <!-- 下载按钮（href 由 ogdShowDetail 按商品配置注入；无链接商品隐藏） -->
            <a class="ogd-download" id="ogd-download" href="#" target="_blank" rel="noopener">⬇️ 离线包下载</a>

            <!-- 激活步骤 -->
            <div class="ogd-steps">
                <div class="ogd-steps-title">激活步骤</div>
                <div class="ogd-step">
                    <span class="ogd-step-num">1</span>
                    <span class="ogd-step-text">离线包下载并解压</span>
                </div>
                <div class="ogd-step">
                    <span class="ogd-step-num">2</span>
                    <span class="ogd-step-text">复制上述授权码</span>
                </div>
                <div class="ogd-step">
                    <span class="ogd-step-num">3</span>
                    <span class="ogd-step-text">解压后的离线包中，双击打开 index.html，按照引导激活离线包</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ===== 离线游戏安装包订单详情弹窗 =====
var ogOrders = <?php echo json_encode($view_og_orders, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var ogGoodsUrls = <?php echo json_encode(isset($view_goods_urls) ? $view_goods_urls : [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var ogToday = '<?php echo date('Y-m-d'); ?>';

// 打开详情：按索引填充弹窗（全部用 textContent，license_code 不进 HTML/属性）
function ogdShowDetail(idx) {
    var o = ogOrders[idx];
    if (!o) return;
    var expired = String(o.expire_date) < ogToday;
    document.getElementById('ogd-f-order-no').textContent = o.order_no;
    document.getElementById('ogd-f-school').textContent = o.school_name;
    document.getElementById('ogd-f-room').textContent = o.room_name;
    // 学校/机房为空（非机房履约商品）时隐藏对应字段容器
    document.getElementById('ogd-f-school').parentNode.style.display = (o.school_name && String(o.school_name) !== '') ? '' : 'none';
    document.getElementById('ogd-f-room').parentNode.style.display = (o.room_name && String(o.room_name) !== '') ? '' : 'none';
    document.getElementById('ogd-f-expire').textContent = o.expire_date + (expired ? '（已过期）' : '（有效）');
    document.getElementById('ogd-f-point').textContent = o.point_amount + ' 积分';
    document.getElementById('ogd-license').textContent = o.license_code;
    // 授权码为空时隐藏整个授权码区（含复制按钮）
    document.getElementById('ogd-license-area').style.display = (o.license_code && String(o.license_code) !== '') ? '' : 'none';
    // 下载链接跟随商品表配置（换链不再改代码）
    var dl = document.getElementById('ogd-download');
    var dlUrl = ogGoodsUrls[o.product_key] || '';
    dl.style.display = dlUrl ? '' : 'none';
    dl.href = dlUrl;
    // 重置复制按钮状态
    var btn = document.getElementById('ogd-copy-btn');
    btn.classList.remove('ogd-copied');
    btn.textContent = '📋 复制授权码';
    ogdOpenModal();
}

function ogdOpenModal() {
    document.getElementById('ogd-mask').classList.add('show');
    // 锁定背景滚动
    document.body.style.overflow = 'hidden';
}

function ogdCloseModal() {
    document.getElementById('ogd-mask').classList.remove('show');
    // 恢复背景滚动
    document.body.style.overflow = '';
}

// 点击遮罩关闭
document.getElementById('ogd-mask').addEventListener('click', function(e) {
    if (e.target === this) ogdCloseModal();
});

// ESC 键关闭弹窗
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('ogd-mask').classList.contains('show')) {
        ogdCloseModal();
    }
});

function ogdCopyLicense() {
    var text = document.getElementById('ogd-license').textContent;
    var btn = document.getElementById('ogd-copy-btn');

    function ogdCopied() {
        // 按钮内联反馈，避免 alert 打断
        btn.classList.add('ogd-copied');
        btn.textContent = '✓ 已复制到剪贴板';
        setTimeout(function() {
            btn.classList.remove('ogd-copied');
            btn.textContent = '📋 复制授权码';
        }, 2000);
    }

    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(ogdCopied).catch(function() {
            alert('复制失败，请手动选择授权码复制');
        });
    } else {
        // 降级方案
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            ogdCopied();
        } catch (err) {
            alert('复制失败，请手动选择授权码复制');
        }
        document.body.removeChild(ta);
    }
}
</script>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
