<?php include("template/$OJ_TEMPLATE/header.php");?>
<style>
.news-page-bg { background: #ededed; border-radius: 10px; padding: 16px; }
.news-column { max-width: 680px; margin: 0 auto; }
.news-card { display: flex; align-items: center; background: #fff; border-radius: 8px; padding: 16px; margin-bottom: 12px; position: relative; overflow: hidden; text-decoration: none; transition: box-shadow .2s ease, transform .2s ease; }
.news-card:last-child { margin-bottom: 0; }
.news-card:hover { box-shadow: 0 6px 16px rgba(0,0,0,.12); transform: translateY(-2px); text-decoration: none; }
.news-card.news-top { padding-top: 30px; }
.news-card .news-text { flex: 1 1 auto; min-width: 0; padding-right: 12px; }
.news-card .news-title { font-weight: 700; color: #333; font-size: 16px; line-height: 1.4; margin: 0 0 6px; }
.news-card .news-date { font-size: 12px; color: #999; margin: 0 0 6px; }
.news-card .news-summary { font-size: 13px; color: #888; line-height: 1.5; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.news-card .news-cover { flex: 0 0 auto; width: 96px; height: 76px; object-fit: cover; border-radius: 6px; }
.news-top-flag { position: absolute; top: 0; left: 0; background: #e64340; color: #fff; font-size: 12px; line-height: 1; padding: 4px 8px; border-radius: 0 0 8px 0; }
</style>
<div class="news-page-bg">
    <div class="news-column">
<?php if (empty($view_news)) { ?>
        <div style="text-align:center;color:#999;padding:60px 0;background:#fff;border-radius:8px;">暂无公告</div>
<?php } else { ?>
        <?php foreach ($view_news as $item) { ?>
        <a class="news-card<?php if ($item['top']) echo " news-top"; ?>" href="viewnews.php?id=<?php echo $item['news_id']; ?>">
            <?php if ($item['top']) { ?><span class="news-top-flag">置顶</span><?php } ?>
            <div class="news-text">
                <p class="news-title"><?php echo $item['title']; ?></p>
                <p class="news-date"><?php echo $item['date']; ?></p>
                <p class="news-summary"><?php echo $item['summary']; ?></p>
            </div>
            <?php if ($item['cover'] != "") { ?><img class="news-cover" src="<?php echo $item['cover']; ?>" alt="cover"/><?php } ?>
        </a>
        <?php } ?>
        <?php if ($pages > 1) {
            echo "<div style='display:inline;'>";
            echo "<nav class='center'>";
            echo "<ul class='pagination pagination-sm'>";
            echo "<li class='page-item'><a href='news.php?page=".(strval(1))."'>&lt;&lt;</a></li>";
            echo "<li class='page-item'><a href='news.php?page=".($page==1?strval(1):strval($page-1))."'>&lt;</a></li>";
            for($i=$spage; $i<=$epage; $i++){
                echo "<li class='".($page==$i?"active ":"")."page-item'><a title='go to page' href='news.php?page=".$i."'>".$i."</a></li>";
            }
            echo "<li class='page-item'><a href='news.php?page=".($page==$pages?strval($page):strval($page+1))."'>&gt;</a></li>";
            echo "<li class='page-item'><a href='news.php?page=".(strval($pages))."'>&gt;&gt;</a></li>";
            echo "</ul>";
            echo "</nav>";
            echo "</div>";
        } ?>
<?php } ?>
    </div>
</div>
<?php include("template/$OJ_TEMPLATE/footer.php");?>
