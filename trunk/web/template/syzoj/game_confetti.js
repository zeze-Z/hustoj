/**
 * 通关烟花效果（依赖 canvas-confetti）
 * 在 CDN 加载失败时静默跳过
 */
function launchConfetti() {
    if (typeof confetti !== 'function') return;
    var duration = 1500;
    var end = Date.now() + duration;
    (function frame() {
        confetti({
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
}
