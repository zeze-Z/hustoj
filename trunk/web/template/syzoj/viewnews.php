 <?php $show_title="$MSG_NEWS - $OJ_NAME"; ?>
<?php include("template/$OJ_TEMPLATE/header.php");?>
<script src="<?php echo "template/bs3/"?>marked.min.js"></script>
<style>
.news-page-bg { background: #ededed; border-radius: 10px; padding: 16px; }
.news-article { max-width: 680px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 28px 24px; }
.news-article h1 { font-size: 24px; font-weight: 700; color: #333; line-height: 1.4; margin: 0 0 10px; }
.news-article .news-back { display: inline-block; font-size: 13px; font-weight: 600; color: #fff !important; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 20px; padding: 8px 18px; text-decoration: none !important; margin-bottom: 18px; box-shadow: 0 2px 6px rgba(102,126,234,0.35); transition: all 0.25s ease; }
.news-article .news-back:hover { color: #fff !important; text-decoration: none !important; transform: translateX(-3px); box-shadow: 0 4px 12px rgba(102,126,234,0.45); }
.news-article .news-meta { font-size: 13px; color: #999; margin: 0 0 12px; }
.news-article .news-meta a { color: #7b8ba5; }
.news-article .news-divider { border: none; border-top: 1px solid #eee; margin: 0 0 18px; }
</style>
<div class="news-page-bg">
    <div class="news-article">
        <a class="news-back" href="news.php">← 返回动态</a>
        <h1><?php echo htmlentities($news_title, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="news-meta">
            <i class="calendar icon"></i> <?php echo $news_date ?>
        </p>
        <hr class="news-divider">
        <?php /* 正文双模式：KindEditor 富文本(HTML)原样渲染+RemoveXSS 防护；
                 含 [plist 题单短代码的旧格式公告仍走 bbcode 管线(bbcode_to_html 会
                 把标签间文本 HTML 转义, 不能用于富文本) */ ?>
        <div id="content" class="font-content"><?php echo (strpos($news_content,'[plist')!==false) ? bbcode_to_html($news_content) : RemoveXSS($news_content) ?></div>
    </div>
</div>
<script>
  $(document).ready(function(){
                marked.use({
                  // 开启异步渲染
                  async: true,
                  pedantic: false,
                  gfm: true,
                  mangle: false,
                  headerIds: false
                });
                $(".md").each(function(){
                        $(this).html(marked.parse($(this).text()));
                });
                // adding note for ```input1  ```output1 in description
                for(let i=1;i<10;i++){
                        $(".language-input"+i).parent().before("<div><?php echo $MSG_Input?>"+i+":</div>");
                        $(".language-output"+i).parent().before("<div><?php echo $MSG_Output?>"+i+":</div>");
                }
        $(".md table tr td").css({
            "border": "1px solid grey",
            "text-align": "center",
            "width": "200px",
            "height": "30px"
        });

        $(".md table th").css({
            "border": "1px solid grey",
            "width": "200px",
            "height": "30px",
            "background-color": "#9e9e9ea1",
            "text-align": "center"
        });

        $(".panel.panel-success.panel-heading.panel-title").each(function(){
                let pname=$(this).attr("control");
                console.log(pname);
                $(this).click(function(){
                        $("#"+pname).toggle();
                });
        });

  });
</script>
<?php if (isset($OJ_MATHJAX)&&$OJ_MATHJAX){?>
    <!--以下为了加载公式的使用而既加入-->
<script>
  MathJax = {
    tex: {inlineMath: [['$', '$'], ['\\(', '\\)']]}
  };
</script>

<script id="MathJax-script" async src="template/bs3/tex-chtml.js"></script>
<style>
.jumbotron1{
  font-size: 18px;
}
</style>

<?php } ?>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
