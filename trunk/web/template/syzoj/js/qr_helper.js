/**
 * Canvas / 二维码共享工具（qr_helper.js）
 * 2026-09-28 自 timetable.js / teacher_style_test.php / career_test.php 三份重复拷贝收编。
 * 依赖：qrcode.min.js（全局 QRCode）须先于本文件加载；缺失时 makeQrCanvas 静默返回 null。
 * 全局导出：makeQrCanvas(text, size, colorDark) / roundRect(g, x, y, w, h, r) / wrapLines(g, text, maxW)
 * 新测评统一引入本文件，不要再拷贝第四份。
 */
(function (global) {
    'use strict';

    function makeQrCanvas(text, size, colorDark) {
        try {
            if (typeof QRCode === 'undefined') return null; // qrcode.min.js 未加载时跳过二维码
            var holder = document.createElement('div');
            new QRCode(holder, {
                text: text, width: size, height: size,
                colorDark: colorDark || '#28282A', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            return holder.querySelector('canvas');
        } catch (e) { return null; }
    }

    function roundRect(g, x, y, w, h, r) {
        r = Math.min(r, w / 2, h / 2);
        g.beginPath();
        g.moveTo(x + r, y);
        g.arcTo(x + w, y, x + w, y + h, r);
        g.arcTo(x + w, y + h, x, y + h, r);
        g.arcTo(x, y + h, x, y, r);
        g.arcTo(x, y, x + w, y, r);
        g.closePath();
    }

    function wrapLines(g, text, maxW) {
        var lines = [], line = '';
        for (var i = 0; i < text.length; i++) {
            var t = line + text[i];
            if (g.measureText(t).width > maxW && line) { lines.push(line); line = text[i]; }
            else line = t;
        }
        if (line) lines.push(line);
        return lines;
    }

    global.makeQrCanvas = makeQrCanvas;
    global.roundRect = roundRect;
    global.wrapLines = wrapLines;
})(typeof window !== 'undefined' ? window : globalThis);
