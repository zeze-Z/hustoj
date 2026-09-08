/**
 * 通关烟花效果（依赖 canvas-confetti）
 * 在 CDN 加载失败时静默跳过
 *
 * 礼花 canvas 置于最顶层（z-index 2147483647），盖过通关弹窗；
 * 动画结束后自动清理 canvas，避免残留遮挡页面交互。
 */
function launchConfetti() {
    if (typeof confetti !== 'function' || typeof confetti.create !== 'function') return;

    // 创建独立 canvas 并置于最顶层，pointer-events:none 不影响点击
    var canvas = document.createElement('canvas');
    canvas.style.cssText = 'position:fixed;top:0;left:0;width:100vw;height:100vh;pointer-events:none;z-index:2147483647;';
    document.body.appendChild(canvas);

    var fire = confetti.create(canvas, { resize: true, useWorker: false });

    var duration = 1500;
    var end = Date.now() + duration;
    (function frame() {
        fire({
            particleCount: 5,
            spread: 60,
            origin: { y: 0.7 },
            colors: ['#6366f1', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
            disableForReducedMotion: true
        });
        if (Date.now() < end) {
            requestAnimationFrame(frame);
        }
    }());

    // 动画结束后清理 canvas，避免残留遮挡
    setTimeout(function () {
        try { fire.reset(); } catch (e) {}
        if (canvas.parentNode) canvas.parentNode.removeChild(canvas);
    }, duration + 2000);
}
