</div>
</div>
<?php if(empty($hide_chrome)){ // 传播型 H5（$hide_chrome）跳过站点脚本与页脚主体，输出末尾极简署名行 ?>
<script src="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE"?>/css/semantic.min.js"></script>
<script src="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE"?>/css/Chart.min.js"></script>
    <style>
    .footer {
        line-height: 1.4285em;
        font-family: "Lato", "Noto Sans CJK SC", "Source Han Sans SC", "PingFang SC", "Hiragino Sans GB", "Microsoft Yahei", "WenQuanYi Micro Hei", "Droid Sans Fallback", "sans-serif";
        box-sizing: inherit;
        padding: 0 !important;
        border: none !important;
        color: #888;
        font-size: 1rem;
        margin: 35px 0 14px !important;
        position: relative;
        width: 100%;
        bottom: 0;
        background: none transparent;
        border-radius: 0;
        box-shadow: none;
    }
    </style>
    <?php include(dirname(__FILE__)."/js.php");?>
    <div class="footer">
        <div class="ui center aligned container">
            <div>欢迎广大师生使用，问题咨询，商务合作，请联系客服QQ：<a href="javascript:void(0);" onclick="copyCustomerQQ(this)" title="点击复制QQ号" style="color: inherit; text-decoration: underline; cursor: pointer;"><?php echo htmlentities($OJ_CUSTOMER_QQ, ENT_QUOTES, 'UTF-8');?></a> ｜ <a href="<?php echo $path_fix?>teacher_guide.php" style="color: inherit; text-decoration: underline;">学生账号开通指南</a></div>
            <div style="margin-top: 8px; color: #aaa;">📱 关注小红书：@子昂老师 ITlizhi888 ｜ 获取更多教学资源动态</div>
            <!-- <div><?php echo $domain==$DOMAIN?$OJ_NAME:ucwords($OJ_NAME)."'s OJ"?> is powered by <a style="color: inherit !important;" class=" " title="GitHub"
                    target="_blank" rel="noreferrer noopener" href="https://github.com/zhblue/hustoj">HUSTOJ</a>, Theme
                by <a style="color: inherit !important;" href="https://github.com/syzoj">SYZOJ</a></div> -->
         <!--   <div> Running on <a href='https://debian.org' target='_blank'>Debian11</a> / <a href='https://www.loongson.cn' target='_blank'>Loongson 3A3000</a> </div> -->
            <?php if ($OJ_BEIAN) { ?>
            <div>
            <img src="image/icp.png">
                <a href="https://beian.miit.gov.cn/" style="text-decoration: none; color: #444444;"
                    target="_blank"><?php echo $OJ_BEIAN; ?></a>
            </div>
            <?php } ?>
        </div>
    </div>
    </div>
<script>
    // 同步复制（true=成功）。必须在点击手势内同步调用（异步回调里手势过期，移动端必失败）。
    // 用 <span> + Range 选区而非临时 textarea（copy-to-clipboard 同款方案）：
    // - iOS 对 readonly 输入框的 setSelectionRange 是空操作，textarea 方案选不中；
    // - opacity:0 / 1px 的不可见 textarea 在部分移动内核（X5/WebView）选不中；
    // - -webkit-user-select 会继承祖先的 user-select:none，需显式置 text。
    // 复制前校验选区内容：选不中直接判失败，杜绝「提示成功实际没复制」。
    function ojSyncCopy(text) {
        var mark = document.createElement('span');
        mark.textContent = text;
        // all:unset 清继承样式；fixed+clip 隐藏但保持可选中（display:none 会选不中）
        mark.style.cssText = 'all:unset;position:fixed;top:0;left:0;clip:rect(0,0,0,0);white-space:pre;font-size:16px;-webkit-user-select:text;user-select:text;';
        document.body.appendChild(mark);
        var ok = false;
        try {
            var sel = window.getSelection();
            var prev = sel.rangeCount > 0 ? sel.getRangeAt(0) : null;
            var range = document.createRange();
            range.selectNodeContents(mark);
            sel.removeAllRanges();
            sel.addRange(range);
            if (sel.toString().trim() !== text) {
                throw new Error('selection mismatch');
            }
            ok = document.execCommand('copy');
        } catch (err) {
            ok = false;
        }
        try {
            if (window.getSelection) {
                var s = window.getSelection();
                s.removeAllRanges();
                if (prev) { try { s.addRange(prev); } catch (e) {} }
            }
        } catch (e) {}
        document.body.removeChild(mark);
        return ok;
    }
    // 复制入口（done/fail 回调），信任度排序：
    // 1) Clipboard API 优先：resolve 才算真成功（execCommand 在部分移动端会假返回 true）；
    // 2) 同步 execCommand 兜底：需用户手势，catch 回调里手势可能已过期，尽力而为。
    function ojCopyText(text, done, fail) {
        if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done).catch(function () {
                if (ojSyncCopy(text)) { done(); } else { fail(); }
            });
            return;
        }
        if (ojSyncCopy(text)) { done(); } else { fail(); }
    }
    // 点击复制客服QQ号（公共方法，全站复用；QQ号取被点击元素自身文本）
    function copyCustomerQQ(el) {
        var qq = el.textContent.trim();
        var original = el.textContent;
        var restore = function (msg, delay) {
            el.textContent = msg;
            setTimeout(function () { el.textContent = original; }, delay);
        };
        ojCopyText(qq,
            function () { restore('已复制 ✓', 1500); },
            function () { restore('复制失败，请长按号码复制', 2500); });
    }
</script>
<?php } else { ?>
    <!-- 极简壳署名行：保留备案展示与回流入口，不带客服/推广文案 -->
    <div style="margin: 18px 0 12px; text-align: center; line-height: 1.5; font-size: 0.78rem; color: #9a9488; font-family: 'PingFang SC','Microsoft YaHei',sans-serif;">
        <span>© <?php echo htmlentities($OJ_NAME, ENT_QUOTES, 'UTF-8');?></span><?php if (!empty($OJ_BEIAN)) { ?><span> · </span><a href="https://beian.miit.gov.cn/" style="color: inherit; text-decoration: none;" target="_blank" rel="noreferrer noopener"><?php echo htmlentities($OJ_BEIAN, ENT_QUOTES, 'UTF-8');?></a><?php } ?><span> · </span><a href="<?php echo $path_fix?>index.php" style="color: inherit; text-decoration: underline;">返回首页</a>
    </div>
<?php } ?>
<?php if (isset($_SESSION[$OJ_NAME.'_user_id'])){ ?>
        <iframe id="sk" src="session.php" height=0px width=0px ></iframe>
        <script>
        $(document).ready(function(){
                window.setTimeout("$('#sk').attr('src','session.php');",1200000);
        });
        </script>

<?php } ?>


</body>

</html>
