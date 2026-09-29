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
            <div>欢迎广大师生使用，问题咨询，商务合作，请联系客服QQ：<span onclick="copyCustomerQQ(this)" title="点击复制QQ号，长按可手动选择复制" style="color: inherit; text-decoration: underline; cursor: pointer; -webkit-user-select: text; user-select: text;"><?php echo htmlentities($OJ_CUSTOMER_QQ, ENT_QUOTES, 'UTF-8');?></span> ｜ <a href="<?php echo $path_fix?>teacher_guide.php" style="color: inherit; text-decoration: underline;">学生账号开通指南</a></div>
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
    // ---------- 复制工具（全站公共） ----------
    // 所有同步策略必须在用户手势内同步调用：异步回调（promise.then/catch、setTimeout）
    // 执行时手势已过期，移动端 execCommand('copy') 与 Clipboard API 都会失败。
    // 同步策略 1：clipboard.js 同款 textarea 配方（移动端久经考验）：
    // - fixed + left:-9999px 移出屏幕但保留布局（display:none 选不中）；
    // - font-size:16px 防 iOS 聚焦缩放，readonly 防弹软键盘；
    // - select()+setSelectionRange() 建立选区（对 readonly textarea 两端都有效）。
    function ojCopyViaTextarea(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', '');
        ta.style.cssText = 'position:fixed;left:-9999px;top:0;font-size:16px;border:0;padding:0;margin:0;';
        document.body.appendChild(ta);
        var ok = false;
        try {
            ta.focus();
            ta.select();
            ta.setSelectionRange(0, text.length);
            if (ta.selectionEnd - ta.selectionStart !== text.length) {
                throw new Error('selection mismatch');
            }
            ok = document.execCommand('copy');
        } catch (err) {
            ok = false;
        }
        try { ta.blur(); } catch (e) {}
        document.body.removeChild(ta);
        return ok;
    }
    // 同步策略 2：<span> + Range 选区（textarea 选不中时的备选）：
    // - all:unset 清继承样式；fixed+clip 隐藏但保持可选中（display:none 会选不中）；
    // - -webkit-user-select 会继承祖先的 user-select:none，需显式置 text。
    // 复制前校验选区内容：选不中直接判失败，杜绝「提示成功实际没复制」。
    function ojCopyViaSpan(text) {
        var mark = document.createElement('span');
        mark.textContent = text;
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
    // 同步复制（true=成功）：textarea 配方 → span 选区 双策略链，先成功先赢
    function ojSyncCopy(text) {
        if (ojCopyViaTextarea(text)) { return true; }
        return ojCopyViaSpan(text);
    }
    // 复制入口（done/fail 回调），必须同步优先：
    // 1) 同步 execCommand 在手势内最可靠。绝不能把 Clipboard API 放第一位——
    //    移动端（微信内置浏览器/X5/部分 iOS WebView）无剪贴板写权限会被 reject，
    //    reject 后再兜底时已是异步回调，手势过期必失败（上一版失败根因）。
    //    选区逐字校验保证同步路径「返回 true 即真复制」。
    // 2) 异步 Clipboard API 仅作兜底：execCommand 被禁用的环境（部分加固内核/桌面），
    //    resolve 才算真成功。
    function ojCopyText(text, done, fail) {
        if (ojSyncCopy(text)) { done(); return; }
        if (navigator.clipboard && navigator.clipboard.writeText && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done).catch(fail);
            return;
        }
        fail();
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
            function () {
                restore('请长按号码复制', 2000);
                // 终极兜底：prompt 输入框内长按可全选/复制（移动端系统级支持）
                window.prompt('自动复制未成功，请长按下方内容选择「复制」：', qq);
            });
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
