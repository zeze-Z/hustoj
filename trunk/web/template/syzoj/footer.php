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
    // 同步复制（true=成功）。必须在点击手势内同步调用：
    // 放进 writeText().catch() 异步回调里手势已过期，移动端 execCommand 必失败。
    // iOS/iPadOS：readonly 防弹键盘 + Range 选区 + setSelectionRange 才能选中临时 textarea。
    function ojSyncCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.setAttribute('readonly', 'readonly');
        ta.style.cssText = 'position:fixed;top:0;left:0;width:1px;height:1px;padding:0;border:0;opacity:0;font-size:16px;';
        document.body.appendChild(ta);
        var ok = false;
        try {
            var isIOS = /iP(hone|ad|od)/.test(navigator.userAgent) ||
                (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            if (isIOS) {
                var range = document.createRange();
                range.selectNodeContents(ta);
                var sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(range);
            } else {
                ta.focus();
                ta.select();
            }
            ta.setSelectionRange(0, text.length);
            ok = document.execCommand('copy');
        } catch (err) {
            ok = false;
        }
        try { if (window.getSelection) { window.getSelection().removeAllRanges(); } } catch (e) {}
        document.body.removeChild(ta);
        return ok;
    }
    // 点击复制客服QQ号（公共方法，全站复用；QQ号取被点击元素自身文本）
    function copyCustomerQQ(el) {
        var qq = el.textContent.trim();
        var original = el.textContent;
        var restore = function (msg, delay) {
            el.textContent = msg;
            setTimeout(function () { el.textContent = original; }, delay);
        };
        var done = function () { restore('已复制 ✓', 1500); };
        var fail = function () { restore('复制失败，请长按号码复制', 2500); };
        // 1) 同步 execCommand 优先（手势未过期，移动端唯一可靠路径）
        if (ojSyncCopy(qq)) {
            done();
            return;
        }
        // 2) Clipboard API 作为最后一搏（异步可能被拒，但别无他法）
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(qq).then(done).catch(function () { fail(); });
        } else {
            fail();
        }
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
