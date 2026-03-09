<?php 
$page_title = "Scratch案例";
$iframe_src = "https://turbowarp.org/1105114015/embed";
$show_title = "Scratch案例 - $OJ_NAME"; 
?>
<?php include("template/$OJ_TEMPLATE/header.php");?>

<div class="padding">
    <h2 class="ui header">
        <i class="code icon"></i>
        <div class="content">
            <?php echo $page_title; ?>
        </div>
    </h2>
    
    <div style="margin-bottom: 25px; max-width: 1200px; margin: 0 auto;">
        <div class="ui segment">
            <div style="width: 100%; height: 700px; overflow: hidden; position: relative;">
                <iframe id="scratchFrame" src="<?php echo $iframe_src; ?>" width="100%" height="700" allowtransparency="true" frameborder="0" scrolling="no" allowfullscreen style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
    
    <script>
        function resizeIframe() {
            const iframe = document.getElementById('scratchFrame');
            if (!iframe) return;
            
            const container = iframe.parentElement;
            
            iframe.style.width = `${container.offsetWidth}px`;
            iframe.style.height = `${container.offsetHeight}px`;
            
            iframe.style.transform = 'none';
            iframe.style.transformOrigin = 'top left';
        }
        
        window.addEventListener('load', resizeIframe);
        
        window.addEventListener('resize', resizeIframe);
    </script>
</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>