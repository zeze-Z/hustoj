</div>
</div>
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
            <div>欢迎广大师生使用，问题咨询，商务合作，请联系客服QQ：<a href="https://wpa.qq.com/msgrd?v=3&uin=<?php echo htmlentities($OJ_CUSTOMER_QQ, ENT_QUOTES, 'UTF-8');?>&site=qq&menu=yes" target="_blank" style="color: inherit; text-decoration: underline;"><?php echo htmlentities($OJ_CUSTOMER_QQ, ENT_QUOTES, 'UTF-8');?></a> <a href="javascript:void(0);" onclick="copyFooterQQ(event)" style="color: inherit;">[复制]</a> ｜ <a href="<?php echo $path_fix?>teacher_guide.php" style="color: inherit;">学生账号开通指南</a></div>
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
    // 一键复制客服QQ号
    function copyFooterQQ(e) {
        if (e) e.preventDefault();
        var qq = "<?php echo htmlentities($OJ_CUSTOMER_QQ, ENT_QUOTES, 'UTF-8');?>";
        var done = function () { alert("客服QQ已复制：" + qq); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(qq).then(done).catch(function () { fallbackCopy(qq, done); });
        } else {
            fallbackCopy(qq, done);
        }
    }
    function fallbackCopy(text, done) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (err) {}
        document.body.removeChild(ta);
        done();
    }
</script>
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
