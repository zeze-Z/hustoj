/**
 * 课程表生成器 渲染引擎 v2
 * 依赖：qrcode.min.js（本地 vendor，生成二维码 canvas；缺失时角标只显示文案）
 * 主题配置的 grid 为百分比坐标（相对底图宽高）。新增主题流程：
 *   1. 两张底图放 image/course_schedule/themeN_{h,s}.jpg
 *   2. 在 TIMETABLE_THEMES 加草稿条目（坐标随意占位，行列数按实际填）
 *   3. 页面加 ?calibrate=1 拖拽校准 → 复制配置代码 → 整段贴回 TIMETABLE_THEMES
 */
(function () {
    'use strict';

    // ===== 主题配置 =====
    // cols/amRows/pmRows 为主题级行列数（不同主题可不同）；grid 为百分比坐标：
    // x0/x1 课程列区左右边界；amY0/amY1 上午区上下边界；pmY0/pmY1 下午区上下边界
    // 可选 font: { palette: [...] } 主题级色板覆盖（默认用下方 PALETTE）
    var TIMETABLE_THEMES = [
        {
            id: 'theme1', name: '手绘童趣', cols: 5, amRows: 4, pmRows: 4,
            h: {
                img: 'image/course_schedule/theme1_h.jpg',
                grid: { x0: 0.2036, x1: 0.96, amY0: 0.32, amY1: 0.5824, pmY0: 0.66, pmY1: 0.9328 }
            },
            s: {
                img: 'image/course_schedule/theme1_s.jpg',
                grid: { x0: 0.2219, x1: 0.9649, amY0: 0.299, amY1: 0.5597, pmY0: 0.6488, pmY1: 0.8954 }
            }
        },
        {
            id: 'theme2', name: '森系绿意', cols: 5, amRows: 4, pmRows: 4,
            h: {
                img: 'image/course_schedule/theme2_h.jpg',
                grid: { x0: 0.2528, x1: 0.9409, amY0: 0.3735, amY1: 0.629, pmY0: 0.629, pmY1: 0.8782 }
            },
            s: {
                img: 'image/course_schedule/theme2_s.jpg',
                grid: { x0: 0.1994, x1: 0.9649, amY0: 0.3845, amY1: 0.6087, pmY0: 0.6087, pmY1: 0.8292 }
            }
        },
        {
            id: 'theme3', name: '太空旅行', cols: 5, amRows: 4, pmRows: 4,
            h: {
                img: 'image/course_schedule/theme3_h.jpg',
                grid: { x0: 0.2374, x1: 0.9325, amY0: 0.3693, amY1: 0.6354, pmY0: 0.6354, pmY1: 0.9014 }
            },
            s: {
                img: 'image/course_schedule/theme3_s.jpg',
                grid: { x0: 0.2008, x1: 0.9452, amY0: 0.4033, amY1: 0.6191, pmY0: 0.6191, pmY1: 0.8376 }
            }
        },
        {
            id: 'theme4', name: '恐龙乐园', cols: 5, amRows: 4, pmRows: 4,
            h: {
                img: 'image/course_schedule/theme4_h.jpg',
                grid: { x0: 0.2472, x1: 0.9494, amY0: 0.3503, amY1: 0.6375, pmY0: 0.6375, pmY1: 0.9078 }
            },
            s: {
                img: 'image/course_schedule/theme4_s.jpg',
                grid: { x0: 0.2331, x1: 0.9733, amY0: 0.3404, amY1: 0.5731, pmY0: 0.5731, pmY1: 0.8198 }
            }
        },
        {
            id: 'theme5', name: '彩虹蜡笔', cols: 5, amRows: 4, pmRows: 4,
            h: {
                img: 'image/course_schedule/theme5_h.jpg',
                grid: { x0: 0.2148, x1: 0.9381, amY0: 0.3482, amY1: 0.61, pmY0: 0.61, pmY1: 0.8845 }
            },
            s: {
                img: 'image/course_schedule/theme5_s.jpg',
                grid: { x0: 0.2402, x1: 0.9649, amY0: 0.362, amY1: 0.5862, pmY0: 0.5862, pmY1: 0.8179 }
            }
        },
        {
            id: 'theme6', name: '海洋世界', cols: 5, amRows: 4, pmRows: 4,
            h: {
                img: 'image/course_schedule/theme6_h.jpg',
                grid: { x0: 0.2402, x1: 0.8804, amY0: 0.3727, amY1: 0.6324, pmY0: 0.6324, pmY1: 0.8943 }
            },
            s: {
                img: 'image/course_schedule/theme6_s.jpg',
                grid: { x0: 0.2388, x1: 0.9395, amY0: 0.3489, amY1: 0.5768, pmY0: 0.5768, pmY1: 0.8095 }
            }
        }
    ];

    var SUBJECT_CHIPS = ['信息', '编程', '信奥']; // 默认仅 3 项（信息技术老师），其余靠自定义输入沉淀
    var DAYS = ['周一', '周二', '周三', '周四', '周五', '周六', '周日'];
    // 圆体优先（可爱贴纸感），无圆体回退楷体；白描边托底可读性
    // 站酷快乐体（本站同源 woff2 分片）置于栈首；未加载/不支持时回退各系统圆体
    var FONT_STACK = '"ZCOOL KuaiLe","Yuanti SC","幼圆","YouYuan","Hiragino Maru Gothic ProN","楷体","KaiTi","PingFang SC","Microsoft YaHei",sans-serif';
    var WEBFONT_NAME = 'ZCOOL KuaiLe';
    // 预热常用学科/年级用字：首帧预览即用圆体，不必等用户输入触发分片下载
    var WEBFONT_PRELOAD = '语文数学英语信息编程信奥物理化学生物政治历史地理体育音乐美术科学道德法治自习班会阅读写字班队校本劳动班年级老师课程表上午下午节一二三四五六七八九十0123456789' + '免费生成课表';
    // 汇总 canvas 实际会绘制的全部文字（格子内容 + 角标文案），去重后作为字体加载样本
    function webfontSample() {
        var s = WEBFONT_PRELOAD + SUBJECT_CHIPS.join('') + (typeof TT_SITE_NAME === 'string' ? TT_SITE_NAME : '');
        ['am', 'pm'].forEach(function (block) {
            var rows = (state.cells && state.cells[block]) || [];
            rows.forEach(function (row) {
                row.forEach(function (v) { if (v) s += v; });
            });
        });
        var seen = {}, out = '';
        for (var i = 0; i < s.length; i++) {
            var ch = s[i];
            if (ch && !seen[ch]) { seen[ch] = 1; out += ch; }
        }
        return out;
    }
    // canvas 绘制前必须等待字体就绪，否则首帧/导出 JPEG 会固化回退字体；2 秒超时兜底不阻塞功能
    function ensureWebFont() {
        if (!document.fonts || !document.fonts.load) return Promise.resolve();
        var sample = webfontSample();
        var p = Promise.all([
            document.fonts.load('400 20px "' + WEBFONT_NAME + '"', sample),
            document.fonts.load('700 20px "' + WEBFONT_NAME + '"', sample)
        ]).then(function () { return document.fonts.ready; }).catch(function () {});
        return Promise.race([p, new Promise(function (resolve) { setTimeout(resolve, 2000); })]);
    }
    // 词条色板：中深色系适配米黄底图；预置词条固定取前 N 色（互异），自定义词条 hash 取色（同词必同色）
    var PALETTE = ['#d81b60', '#1565c0', '#6a1b9a', '#2e7d32', '#ef6c00', '#c2185b', '#0277bd', '#c62828', '#558b2f', '#7b1fa2', '#8d6e63', '#00838f'];
    // 游客导出 (长边px, JPEG质量) 阶梯：逐档尝试，取首个 ≤100KB 的组合
    // 参数经虚机 php-gd 实测校准（2026-09-12）：1200px 长边 q0.42≈99KB/q0.38≈95KB，
    // q0.62 超标 23%；浏览器 canvas 与 GD 同为 libjpeg 误差 ±5%，超档自动落到下一档
    var LADDER = [[1200, 0.42], [1200, 0.38], [1080, 0.5], [1080, 0.45], [960, 0.5], [960, 0.45], [880, 0.45], [880, 0.4]];
    var GUEST_MAX_BYTES = 100 * 1024;
    var DRAFT_KEY = 'oj_timetable_draft_v1';
    var DEBUG = /debug=1/.test(window.location.search);
    var CALIBRATE = /calibrate=1/.test(window.location.search);

    function emptyRows(rows, cols) {
        var arr = [];
        for (var r = 0; r < rows; r++) {
            var row = [];
            for (var c = 0; c < cols; c++) row.push('');
            arr.push(row);
        }
        return arr;
    }

    var state = { themeId: 'theme1', orient: 'h', cells: { am: [], pm: [] }, customChips: [] };
    var imgCache = {};
    var editing = null; // 当前编辑的格子 {block, r, c}
    var calib = null;   // ?calibrate=1 校准态（仅内存，不进草稿）

    // ===== 工具 =====
    function $(id) { return document.getElementById(id); }

    function currentTheme() {
        for (var i = 0; i < TIMETABLE_THEMES.length; i++) {
            if (TIMETABLE_THEMES[i].id === state.themeId) return TIMETABLE_THEMES[i];
        }
        return TIMETABLE_THEMES[0];
    }

    // 当前生效几何：校准模式用 calib，否则用主题配置
    function activeGeom() {
        var t = currentTheme();
        if (CALIBRATE && calib) {
            return { grid: calib.grids[state.orient], cols: calib.cols, amRows: calib.amRows, pmRows: calib.pmRows };
        }
        return { grid: t[state.orient].grid, cols: t.cols, amRows: t.amRows, pmRows: t.pmRows };
    }

    // cells 尺寸随主题行列数重排（切主题维度变化时左上角保留已填内容）
    function ensureCells() {
        var t = currentTheme();
        ['am', 'pm'].forEach(function (bk) {
            var rows = bk === 'am' ? t.amRows : t.pmRows;
            var old = Array.isArray(state.cells[bk]) ? state.cells[bk] : [];
            var next = [];
            for (var r = 0; r < rows; r++) {
                var orow = Array.isArray(old[r]) ? old[r] : [];
                var row = [];
                for (var c = 0; c < t.cols; c++) {
                    row.push(typeof orow[c] === 'string' ? orow[c].slice(0, 10) : '');
                }
                next.push(row);
            }
            state.cells[bk] = next;
        });
    }

    function termColor(text) {
        var t = currentTheme();
        var palette = (t.font && t.font.palette) || PALETTE;
        var idx = SUBJECT_CHIPS.indexOf(text);
        if (idx >= 0) return palette[idx % palette.length];
        var h = 0;
        for (var i = 0; i < text.length; i++) h = (h * 31 + text.charCodeAt(i)) % 997;
        return palette[h % palette.length];
    }

    function loadImg(src) {
        if (imgCache[src]) return imgCache[src];
        imgCache[src] = new Promise(function (resolve, reject) {
            var img = new Image();
            img.onload = function () { resolve(img); };
            img.onerror = function () { delete imgCache[src]; reject(new Error('模板图加载失败: ' + src)); };
            img.src = src;
        });
        return imgCache[src];
    }

    function toast(msg) {
        var t = $('tt-toast');
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(t._timer);
        t._timer = setTimeout(function () { t.classList.remove('show'); }, 2200);
    }

    // ===== 草稿（localStorage）=====
    function saveDraft() {
        try { localStorage.setItem(DRAFT_KEY, JSON.stringify(state)); } catch (e) { /* 隐私模式等场景忽略 */ }
    }

    function loadDraft() {
        try {
            var raw = localStorage.getItem(DRAFT_KEY);
            if (!raw) return;
            var s = JSON.parse(raw);
            if (!s || typeof s !== 'object') return;
            var i;
            for (i = 0; i < TIMETABLE_THEMES.length; i++) {
                if (TIMETABLE_THEMES[i].id === s.themeId) { state.themeId = s.themeId; break; }
            }
            if (s.orient === 'h' || s.orient === 's') state.orient = s.orient;
            if (s.cells && typeof s.cells === 'object') {
                ['am', 'pm'].forEach(function (bk) {
                    var rows = s.cells[bk];
                    if (!Array.isArray(rows)) return;
                    state.cells[bk] = rows.map(function (row) {
                        if (!Array.isArray(row)) return [];
                        return row.slice(0, 10).map(function (v) {
                            return typeof v === 'string' ? v.slice(0, 10) : '';
                        });
                    });
                });
            }
            if (Array.isArray(s.customChips)) {
                state.customChips = s.customChips.filter(function (x) { return typeof x === 'string'; }).slice(0, 20);
            }
        } catch (e) { /* 脏数据忽略 */ }
    }

    // ===== 渲染 =====
    function drawCellText(ctx, text, cx, cy, cellW, cellH, color) {
        var size = Math.floor(cellH * 0.5);
        var minSize = Math.max(8, Math.floor(cellH * 0.3));
        var maxW = cellW * 0.9;
        function setFont(px) { ctx.font = '700 ' + px + 'px ' + FONT_STACK; }
        setFont(size);
        var w = ctx.measureText(text).width;
        while (w > maxW && size > minSize) {
            size -= 1;
            setFont(size);
            w = ctx.measureText(text).width;
        }
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.lineJoin = 'round'; // 白描边圆角 → 贴纸感
        ctx.miterLimit = 2;
        // 先描边后填充，任何底色上均可读
        function paint(t, x, y) {
            ctx.strokeStyle = 'rgba(255,252,245,0.95)';
            ctx.lineWidth = Math.max(2, Math.round(size / 7));
            ctx.strokeText(t, x, y);
            ctx.fillStyle = color;
            ctx.fillText(t, x, y);
        }
        if (w <= maxW) {
            paint(text, cx, cy);
            return;
        }
        // 最小字号仍超宽：拆两行居中
        var parts = splitTwoLines(ctx, text, maxW);
        var lineH = size * 1.18;
        paint(parts[0], cx, cy - lineH * 0.55);
        paint(parts[1], cx, cy + lineH * 0.55);
    }

    function splitTwoLines(ctx, text, maxW) {
        var mid = Math.ceil(text.length / 2);
        var i;
        for (i = mid; i >= 1; i--) {
            if (ctx.measureText(text.slice(0, i)).width <= maxW * 1.02) {
                if (ctx.measureText(text.slice(i)).width <= maxW * 1.02) return [text.slice(0, i), text.slice(i)];
            }
        }
        for (i = 1; i < text.length; i++) {
            if (ctx.measureText(text.slice(i)).width <= maxW * 1.02) return [text.slice(0, i), text.slice(i)];
        }
        return [text.slice(0, mid), text.slice(mid)];
    }

    function roundRect(ctx, x, y, w, h, r) {
        r = Math.min(r, w / 2, h / 2);
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.arcTo(x + w, y, x + w, y + h, r);
        ctx.arcTo(x + w, y + h, x, y + h, r);
        ctx.arcTo(x, y + h, x, y, r);
        ctx.arcTo(x, y, x + w, y, r);
        ctx.closePath();
    }

    // 底部居中分享角标：白底圆角卡片（文案 + 二维码）嵌在模板图内部，
    // canvas 保持模板原始比例，导出图保存/转发不会被裁掉二维码
    function drawBadge(ctx, W, H, qrMinPx) {
        // 横屏二维码缩小 60%（×0.4），竖屏缩小 10%（×0.9）；对最终尺寸整体缩放，避免 px 下限抵消缩小
        var qrScale = W >= H ? 0.4 : 0.9;
        var qrSize = Math.round(Math.max(qrMinPx, Math.round(Math.min(W, H) * 0.11)) * qrScale); // 下限保证游客压缩档可扫
        var qr = makeQrCanvas(shareUrl(), qrSize); // 按最终显示尺寸生成，避免缩放模糊
        var pad = Math.max(4, Math.round(qrSize * 0.09));
        var gap = Math.max(4, Math.round(qrSize * 0.12));
        var fontSize = Math.max(9, Math.floor(qrSize * 0.26));
        var text = TT_SITE_NAME + '免费生成课表';
        ctx.font = '700 ' + fontSize + 'px ' + FONT_STACK;
        var tw = ctx.measureText(text).width;
        var maxW = W * 0.92;
        var leadW = qr ? qrSize + gap : 0;
        // 小画布放不下整组：先缩字号，实在放不下就丢文案只留二维码
        if (leadW + tw + pad * 2 > maxW) {
            var availText = maxW - leadW - pad * 2;
            if (availText > qrSize * 0.5 && fontSize > 9) {
                fontSize = Math.max(9, Math.floor(fontSize * availText / tw));
                ctx.font = '700 ' + fontSize + 'px ' + FONT_STACK;
                tw = ctx.measureText(text).width;
            } else {
                text = '';
            }
        }
        if (!qr && !text) return;
        var cardH = qrSize + pad * 2;
        var cardW = pad + (text ? tw + gap : 0) + qrSize + pad;
        var margin = Math.max(3, Math.round(Math.min(W, H) * 0.015)); // 贴近图片底部
        var x0 = Math.round((W - cardW) / 2); // 整组水平居中
        var x1 = x0 + cardW;
        var y0 = H - margin - cardH, y1 = H - margin;
        roundRect(ctx, x0, y0, cardW, cardH, Math.round(cardH * 0.18));
        ctx.fillStyle = 'rgba(255,255,255,0.96)';
        ctx.fill();
        ctx.strokeStyle = 'rgba(107,83,38,0.25)';
        ctx.lineWidth = Math.max(1, Math.round(cardH * 0.02));
        ctx.stroke();
        if (qr) {
            ctx.imageSmoothingEnabled = false; // QR 模块按像素对齐，避免缩放糊边影响扫码
            ctx.drawImage(qr, x1 - pad - qrSize, y0 + pad, qrSize, qrSize);
        }
        if (text) {
            ctx.fillStyle = '#6b5326';
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(text, x0 + pad, y0 + cardH / 2 + 1);
        }
    }

    function makeQrCanvas(text, size) {
        try {
            if (typeof QRCode === 'undefined') return null; // qrcode.min.js 未加载时跳过二维码
            var holder = document.createElement('div');
            new QRCode(holder, {
                text: text, width: size, height: size,
                colorDark: '#2f2413', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            return holder.querySelector('canvas');
        } catch (e) { return null; }
    }

    /**
     * 核心渲染：底图 + 网格文字（+底部居中角标/debug 网格线）
     * @param canvas 目标 canvas
     * @param img 已加载的底图 Image
     * @param geom {grid, cols, amRows, pmRows}（activeGeom() 的结果）
     * @param data {am:[rows][cols], pm:[rows][cols]}
     * @param targetW 目标像素宽（预览=容器宽×DPR；导出=高清2倍/标清阶梯档）
     * @param opts {badge:bool, qrMin:px, debug:bool}
     */
    function render(canvas, img, geom, data, targetW, opts) {
        var natW = img.naturalWidth, natH = img.naturalHeight;
        var cw = Math.max(1, Math.round(targetW));
        var ch = Math.max(1, Math.round(targetW * natH / natW)); // 模板原始比例，不追加页脚
        canvas.width = cw;
        canvas.height = ch;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, cw, ch);

        var g = geom.grid;
        var W = cw, H = ch;
        var x0 = g.x0 * W, x1 = g.x1 * W, colW = (x1 - x0) / geom.cols;
        var blocks = {
            am: { y0: g.amY0 * H, y1: g.amY1 * H, rows: geom.amRows },
            pm: { y0: g.pmY0 * H, y1: g.pmY1 * H, rows: geom.pmRows }
        };
        Object.keys(blocks).forEach(function (bk) {
            var b = blocks[bk];
            if (!b.rows) return; // pmRows=0：无下午
            var rowH = (b.y1 - b.y0) / b.rows;
            var rows = data[bk] || [];
            for (var r = 0; r < b.rows; r++) {
                for (var c = 0; c < geom.cols; c++) {
                    if (opts.debug) {
                        ctx.strokeStyle = 'rgba(255,0,0,0.55)';
                        ctx.lineWidth = 1;
                        ctx.strokeRect(x0 + c * colW, b.y0 + r * rowH, colW, rowH);
                    }
                    var text = (rows[r] || [])[c];
                    if (!text) continue;
                    drawCellText(ctx, text, x0 + (c + 0.5) * colW, b.y0 + (r + 0.5) * rowH, colW, rowH, termColor(text));
                }
            }
            if (opts.debug) {
                ctx.strokeStyle = 'rgba(0,120,255,0.7)';
                ctx.lineWidth = 2;
                ctx.strokeRect(x0, b.y0, x1 - x0, b.y1 - b.y0);
            }
        });
        if (opts.badge) drawBadge(ctx, cw, ch, opts.qrMin || 88);
    }

    // ===== 预览 =====
    var resizeTimer = null;
    var previewSeq = 0; // 渲染序号：快速切换方向/主题时丢弃过期回调，避免旧图覆盖新预览

    function renderPreview() {
        var my = ++previewSeq;
        var t = currentTheme();
        var cfg = t[state.orient];
        Promise.all([loadImg(cfg.img), ensureWebFont()]).then(function (res) {
            var img = res[0];
            if (my !== previewSeq) return;
            var canvas = $('tt-preview');
            var wrap = canvas.parentElement;
            var cssW = wrap.clientWidth;
            if (!cssW) return;
            var dpr = Math.min(window.devicePixelRatio || 1, 2);
            // 内部画布至少 1400 宽（CSS 仍按容器等比缩小显示）：drawBadge 的尺寸下限是绝对 px，
            // 低分辨率预览会让角标占比偏大、遮挡表格；≥1400 时比例项主导，与导出图几何完全一致
            var targetW = Math.max(Math.round(cssW * dpr), 1400);
            render(canvas, img, activeGeom(), state.cells, targetW, { badge: true, qrMin: 88, debug: DEBUG || CALIBRATE });
        }).catch(function (err) {
            toast(err && err.message ? err.message : '模板图加载失败');
        });
    }

    // ===== 导出 =====
    function pad2(n) { return (n < 10 ? '0' : '') + n; }

    /**
     * 渲染导出画布并转 JPEG。
     * @param badgeOn true=带底部二维码角标（免费路径）；false=无码（付费去码路径，仅登录会走到）
     * @return Promise<{url,px,tag}>；渲染失败（内部已 toast）时 resolve(null)
     */
    function renderExportCanvas(badgeOn) {
        var t = currentTheme();
        var cfg = t[state.orient];
        return Promise.all([loadImg(cfg.img), ensureWebFont()]).then(function (res) {
            var img = res[0];
            var natW = img.naturalWidth, natH = img.naturalHeight;
            var cv = document.createElement('canvas');
            var geom = activeGeom();
            var url, tag, px;
            if (TT_LOGGED_IN) {
                // 登录：2 倍分辨率高清版（q0.92，角标随画布等比放大）
                render(cv, img, geom, state.cells, natW * 2, { badge: badgeOn, qrMin: 88, debug: DEBUG });
                try {
                    url = cv.toDataURL('image/jpeg', 0.92);
                } catch (e) { toast('图片生成失败，请重试'); return null; }
                tag = badgeOn ? '高清' : '无码';
                px = cv.width + '×' + cv.height;
            } else {
                // 游客：阶梯 (长边, 质量) 压缩到 ~100KB；QR 88px 下限保证各档可扫
                tag = '标清';
                var chosen = null;
                for (var i = 0; i < LADDER.length; i++) {
                    var longSide = LADDER[i][0], q = LADDER[i][1];
                    var scale = longSide / Math.max(natW, natH);
                    var dataUrl;
                    try {
                        render(cv, img, geom, state.cells, Math.round(natW * scale), { badge: badgeOn, qrMin: 88, debug: DEBUG });
                        dataUrl = cv.toDataURL('image/jpeg', q);
                    } catch (e) { toast('图片生成失败，请重试'); return null; }
                    var bytes = Math.round(dataUrl.length * 3 / 4) - 23; // base64 反推字节数
                    var cand = { url: dataUrl, px: cv.width + '×' + cv.height, bytes: bytes };
                    if (!chosen || cand.bytes < chosen.bytes) chosen = cand; // 全档超标时兜底取最小档
                    if (bytes <= GUEST_MAX_BYTES) { chosen = cand; break; }
                }
                url = chosen.url;
                px = chosen.px;
            }
            return { url: url, px: px, tag: tag };
        });
    }

    // 触发下载（部分微信内核不支持 download，靠弹窗长按保存兜底）
    function downloadCanvas(url, tag) {
        var t = currentTheme();
        var d = new Date();
        var stamp = '' + d.getFullYear() + pad2(d.getMonth() + 1) + pad2(d.getDate());
        var link = document.createElement('a');
        link.download = '课程表_' + t.name + '_' + tag + '_' + stamp + '.jpg';
        link.href = url;
        link.click();
    }

    function exportImage() {
        renderExportCanvas(true).then(function (out) {
            if (!out) return; // 渲染失败已在内部 toast
            downloadCanvas(out.url, out.tag);
            showModal(out.url, out.px, out.tag, { qrfree: true });
        }).catch(function (err) {
            toast(err && err.message ? err.message : '模板图加载失败');
        });
    }

    // 去码失败 / 网络异常后恢复按钮可用态与余额文案
    function restoreQrFreeBtn() {
        var btn = $('tt-qrfree-btn');
        if (!btn) return;
        btn.disabled = false;
        btn.textContent = '✨ 去码导出（余额 ' + TT_BALANCE + ' 积分）';
    }

    // 付费去码导出：先扣 1 积分（timetable_qr_free.php），成功后以无角标重新渲染下载
    function exportQrFree() {
        if (!TT_LOGGED_IN) {
            // 游客：登录引导（登录后返回本页）
            window.location.href = 'loginpage.php?redirect=' + encodeURIComponent(window.location.href);
            return;
        }
        if (typeof TT_POSTKEY !== 'string' || !TT_POSTKEY || typeof TT_BALANCE !== 'number') {
            toast('页面数据异常，请刷新后重试');
            return;
        }
        if (!window.confirm('将消耗 1 积分导出无二维码版本（按次计费），当前余额 ' + TT_BALANCE + ' 积分，继续吗？')) return;
        var btn = $('tt-qrfree-btn');
        if (btn) { btn.disabled = true; btn.textContent = '正在扣除积分...'; }
        var formData = new FormData();
        formData.append('postkey', TT_POSTKEY);
        fetch('timetable_qr_free.php', { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.code === 0) {
                    TT_BALANCE = data.data.balance; // 服务端权威余额，即时刷新
                    renderExportCanvas(false).then(function (out) {
                        if (!out) { restoreQrFreeBtn(); return; }
                        downloadCanvas(out.url, out.tag);
                        showModal(out.url, out.px, out.tag, { qrfree: false }); // CTA 切换为"本张为无码版"
                        toast('已扣除 1 积分');
                    }).catch(function () { restoreQrFreeBtn(); });
                } else {
                    // -3 余额不足 / -1 未登录、页面过期、限频等：msg 已含具体原因
                    toast(data.msg || '操作失败，请重试');
                    restoreQrFreeBtn();
                }
            })
            .catch(function (err) {
                console.error('exportQrFree error:', err);
                toast('网络异常，请重试');
                restoreQrFreeBtn();
            });
    }

    function showModal(url, px, tag, opts) {
        $('tt-modal-img').src = url;
        var extra = $('tt-modal-extra');
        // 去码 CTA 区：登录用户展示（无码导出后切换为"本张为无码版"）；游客隐藏（走 extra 登录引导）
        var qrfree = $('tt-modal-qrfree');
        qrfree.innerHTML = '';
        if (TT_LOGGED_IN) {
            qrfree.style.display = 'block';
            if (opts && opts.qrfree === false) {
                var doneTip = document.createElement('div');
                doneTip.textContent = '✅ 本张为无码版，可直接保存转发';
                qrfree.appendChild(doneTip);
            } else {
                var tip = document.createElement('div');
                tip.textContent = '底部二维码碍事？✨ 1积分/次 重新导出无码版';
                var qbtn = document.createElement('button');
                qbtn.type = 'button';
                qbtn.id = 'tt-qrfree-btn';
                qbtn.textContent = '✨ 去码导出（余额 ' + TT_BALANCE + ' 积分）';
                qbtn.addEventListener('click', exportQrFree);
                qrfree.appendChild(tip);
                qrfree.appendChild(qbtn);
            }
        } else {
            qrfree.style.display = 'none';
        }
        if (!TT_LOGGED_IN) {
            extra.style.display = 'block';
            extra.innerHTML = '';
            var p1 = document.createElement('div');
            p1.textContent = '当前导出为标清版（' + px + ' 像素）';
            var p2 = document.createElement('div');
            p2.textContent = '登录后免费导出高清打印版（2 倍分辨率，适合 A4 打印）';
            var p3 = document.createElement('div');
            p3.textContent = '登录后还可 1积分/次 去除底部二维码';
            var btn = document.createElement('a');
            btn.href = 'loginpage.php?redirect=' + encodeURIComponent(window.location.href);
            btn.textContent = '登录导高清';
            extra.appendChild(p1);
            extra.appendChild(p2);
            extra.appendChild(p3);
            extra.appendChild(btn);
        } else {
            extra.style.display = 'none';
        }
        $('tt-modal-mask').classList.add('show');
        $('tt-modal').classList.add('show');
    }

    function hideModal() {
        $('tt-modal-mask').classList.remove('show');
        $('tt-modal').classList.remove('show');
    }

    // ===== UI 构建 =====
    function buildThemes() {
        var box = $('tt-theme-list');
        box.innerHTML = '';
        TIMETABLE_THEMES.forEach(function (t) {
            var d = document.createElement('div');
            d.className = 'tt-theme-item' + (t.id === state.themeId ? ' active' : '');
            d.setAttribute('data-theme', t.id);
            var img = document.createElement('img');
            img.src = t.h.img;
            img.alt = t.name;
            img.loading = 'lazy';
            var nm = document.createElement('div');
            nm.className = 'tt-theme-name';
            nm.textContent = t.name;
            d.appendChild(img);
            d.appendChild(nm);
            d.addEventListener('click', function () {
                state.themeId = t.id;
                ensureCells(); // 行列数可能不同，重排已填内容
                if (CALIBRATE && calib) { resetCalib(); updateCalibOverlay(); }
                saveDraft();
                markActiveTheme();
                buildGrid();
                renderPreview();
            });
            box.appendChild(d);
        });
    }

    function markActiveTheme() {
        var items = document.querySelectorAll('#tt-theme-list .tt-theme-item');
        for (var i = 0; i < items.length; i++) {
            items[i].classList.toggle('active', items[i].getAttribute('data-theme') === state.themeId);
        }
    }

    function bindOrient() {
        $('tt-orient-h').addEventListener('click', function () { setOrient('h'); });
        $('tt-orient-s').addEventListener('click', function () { setOrient('s'); });
        setOrient(state.orient);
    }

    function setOrient(o) {
        state.orient = o;
        $('tt-orient-h').classList.toggle('active', o === 'h');
        $('tt-orient-s').classList.toggle('active', o === 's');
        saveDraft();
        if (CALIBRATE && calib) updateCalibOverlay();
        renderPreview();
    }

    function buildGrid() {
        var t = currentTheme();
        var g = $('tt-grid');
        g.innerHTML = '';
        g.style.gridTemplateColumns = '34px repeat(' + t.cols + ', minmax(52px, 1fr))';
        var i, el;
        el = document.createElement('div');
        el.className = 'tt-grid-head';
        el.textContent = '节次';
        g.appendChild(el);
        for (i = 0; i < t.cols; i++) {
            el = document.createElement('div');
            el.className = 'tt-grid-head';
            el.textContent = DAYS[i];
            g.appendChild(el);
        }
        ['am', 'pm'].forEach(function (bk) {
            var rows = bk === 'am' ? t.amRows : t.pmRows;
            if (!rows) return;
            var label = document.createElement('div');
            label.className = 'tt-group-label';
            if (bk === 'am') {
                label.textContent = '☀️ 上午（第 1-' + rows + ' 节）';
            } else {
                label.textContent = '🌤 下午（第 ' + (t.amRows + 1) + '-' + (t.amRows + rows) + ' 节）';
            }
            g.appendChild(label);
            for (var r = 0; r < rows; r++) {
                var rl = document.createElement('div');
                rl.className = 'tt-row-label';
                rl.textContent = bk === 'am' ? (r + 1) : (r + t.amRows + 1);
                g.appendChild(rl);
                for (var c = 0; c < t.cols; c++) {
                    var cell = document.createElement('div');
                    cell.className = 'tt-cell';
                    cell.setAttribute('data-block', bk);
                    cell.setAttribute('data-r', r);
                    cell.setAttribute('data-c', c);
                    cell.addEventListener('click', onCellClick);
                    g.appendChild(cell);
                }
            }
        });
        refreshGrid();
    }

    function refreshGrid() {
        var cells = document.querySelectorAll('#tt-grid .tt-cell');
        for (var i = 0; i < cells.length; i++) {
            var el = cells[i];
            var v = state.cells[el.getAttribute('data-block')][+el.getAttribute('data-r')][+el.getAttribute('data-c')];
            el.textContent = '';
            el.classList.toggle('filled', !!v);
            if (v) {
                el.textContent = v;
            } else {
                var s = document.createElement('span');
                s.className = 'tt-plus';
                s.textContent = '+';
                el.appendChild(s);
            }
        }
    }

    // ===== 单元格编辑面板 =====
    function onCellClick(e) {
        var el = e.currentTarget;
        editing = { block: el.getAttribute('data-block'), r: +el.getAttribute('data-r'), c: +el.getAttribute('data-c') };
        var period = editing.block === 'am' ? '上午' : '下午';
        var no = editing.block === 'am' ? editing.r + 1 : editing.r + currentTheme().amRows + 1;
        $('tt-editor-pos').textContent = DAYS[editing.c] + ' · ' + period + '第' + no + '节';
        $('tt-editor-input').value = state.cells[editing.block][editing.r][editing.c] || '';
        buildChips();
        showSheet(true);
    }

    function buildChips() {
        var box = $('tt-editor-chips');
        box.innerHTML = '';
        SUBJECT_CHIPS.concat(state.customChips).forEach(function (s) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'tt-chip';
            b.textContent = s;
            b.addEventListener('click', function () { applyEdit(s); });
            box.appendChild(b);
        });
    }

    function applyEdit(v) {
        if (!editing || !v) return;
        v = v.slice(0, 10); // 与 input maxlength 一致的防御性截断
        state.cells[editing.block][editing.r][editing.c] = v;
        if (SUBJECT_CHIPS.indexOf(v) < 0 && state.customChips.indexOf(v) < 0) {
            if (state.customChips.length >= 20) state.customChips.shift(); // 与 loadDraft 载入上限保持一致
            state.customChips.push(v);
        }
        afterEdit();
    }

    function afterEdit() {
        saveDraft();
        refreshGrid();
        renderPreview();
        showSheet(false);
        editing = null;
    }

    function showSheet(show) {
        $('tt-editor-mask').classList.toggle('show', show);
        $('tt-editor').classList.toggle('show', show);
    }

    // ===== 分享/清空 =====
    function shareUrl() { return window.location.origin + window.location.pathname; }

    function fallbackCopy(text, done, fail) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        var ok = false;
        try { ok = document.execCommand('copy'); } catch (e) { }
        document.body.removeChild(ta);
        if (ok) done(); else fail();
    }

    function copyLink() {
        var url = shareUrl();
        var done = function () { toast('链接已复制，快分享给同事吧'); };
        var fail = function () { toast('复制失败，请手动复制地址栏链接'); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(done).catch(function () { fallbackCopy(url, done, fail); });
        } else {
            fallbackCopy(url, done, fail);
        }
    }

    function clearAll() {
        if (!window.confirm('确定清空全部课表内容吗？')) return;
        var t = currentTheme();
        state.cells = { am: emptyRows(t.amRows, t.cols), pm: emptyRows(t.pmRows, t.cols) };
        saveDraft();
        refreshGrid();
        renderPreview();
        toast('已清空');
    }

    // ===== ?calibrate=1 网格校准工具（开发者用，仅内存不进草稿）=====
    function resetCalib() {
        var t = currentTheme();
        calib = {
            cols: t.cols, amRows: t.amRows, pmRows: t.pmRows,
            grids: {
                h: JSON.parse(JSON.stringify(t.h.grid)),
                s: JSON.parse(JSON.stringify(t.s.grid))
            }
        };
    }

    function initCalibrate() {
        resetCalib();
        buildCalibPanel();
        buildCalibOverlay();
        updateCalibOverlay();
    }

    function buildCalibPanel() {
        var col = document.querySelector('.tt-preview-col');
        if (!col) return;
        var panel = document.createElement('div');
        panel.className = 'tt-card';
        panel.style.marginTop = '16px';
        var title = document.createElement('div');
        title.className = 'tt-sec-title';
        title.textContent = '🛠 网格校准工具';
        panel.appendChild(title);

        var row = document.createElement('div');
        row.style.cssText = 'display:flex;gap:14px;flex-wrap:wrap;margin-bottom:12px;align-items:center;';
        row.appendChild(makeStepper('每天列数', 'cols', 4, 7));
        row.appendChild(makeStepper('上午节数', 'amRows', 1, 6));
        row.appendChild(makeStepper('下午节数', 'pmRows', 0, 6));
        panel.appendChild(row);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'tt-btn tt-btn-ghost';
        btn.textContent = '📋 复制主题配置代码';
        btn.addEventListener('click', copyCalib);
        panel.appendChild(btn);

        var out = document.createElement('textarea');
        out.id = 'tt-calib-out';
        out.readOnly = true;
        out.style.cssText = 'width:100%;box-sizing:border-box;margin-top:10px;min-height:120px;font-size:0.78rem;border:1px solid #e2e2e2;border-radius:8px;padding:8px;color:#555;';
        panel.appendChild(out);

        var hint = document.createElement('p');
        hint.style.cssText = 'font-size:0.82rem;color:#999;margin:8px 0 0;line-height:1.5;';
        hint.textContent = '拖动预览图上的圆形手柄对齐模板网格（红竖线=列区左右边界，蓝横线=上下午边界），调好行列数后点复制，整段替换 timetable.js 中 TIMETABLE_THEMES 对应主题条目。中间两根蓝线可拖到重合（蓝点=上午下界、橙点=下午上界，贴近时自动左右错开），用于上下午之间无间隔的模板。横屏/竖屏分别切换校准，两份坐标都会包含。';
        panel.appendChild(hint);

        col.appendChild(panel);
    }

    function makeStepper(labelText, key, min, max) {
        var box = document.createElement('div');
        box.style.cssText = 'display:flex;align-items:center;gap:6px;font-size:0.88rem;color:#555;';
        var lb = document.createElement('span');
        lb.textContent = labelText;
        var val = document.createElement('b');
        val.style.minWidth = '18px';
        val.style.textAlign = 'center';
        function renderVal() { val.textContent = calib[key]; }
        var btns = [-1, 1].map(function (delta) {
            var b = document.createElement('button');
            b.type = 'button';
            b.textContent = delta < 0 ? '−' : '+';
            b.style.cssText = 'width:26px;height:26px;border:1.5px solid #e2e2e2;border-radius:6px;background:#fff;cursor:pointer;font-size:1rem;line-height:1;color:#555;';
            b.addEventListener('click', function () {
                if (calib[key] + delta >= min && calib[key] + delta <= max) {
                    calib[key] += delta;
                    renderVal();
                    updateCalibOverlay();
                    renderPreview();
                }
            });
            return b;
        });
        renderVal();
        box.appendChild(lb);
        box.appendChild(btns[0]);
        box.appendChild(val);
        box.appendChild(btns[1]);
        return box;
    }

    var calibHandles = [];

    function buildCalibOverlay() {
        var wrap = $('tt-preview').parentElement;
        wrap.style.position = 'relative';
        var ov = document.createElement('div');
        ov.id = 'tt-calib-overlay';
        ov.style.cssText = 'position:absolute;left:0;top:0;right:0;bottom:0;';
        wrap.appendChild(ov);
        var defs = [
            { key: 'x0', axis: 'x', label: '列区左边界' }, { key: 'x1', axis: 'x', label: '列区右边界' },
            { key: 'amY0', axis: 'y', label: '上午区上界' }, { key: 'amY1', axis: 'y', label: '上午区下界' },
            { key: 'pmY0', axis: 'y', label: '下午区上界' }, { key: 'pmY1', axis: 'y', label: '下午区下界' }
        ];
        defs.forEach(function (d) {
            var line = document.createElement('div');
            line.style.position = 'absolute';
            line.style.pointerEvents = 'none';
            if (d.axis === 'x') line.style.borderLeft = '2px dashed rgba(230,60,60,.85)';
            else line.style.borderTop = '2px dashed rgba(30,110,230,.85)';
            var handle = document.createElement('div');
            handle.style.cssText = 'position:absolute;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.35);transform:translate(-50%,-50%);touch-action:none;z-index:5;';
            // 中间两根蓝线可拖到重合：给两个圆点不同描边，重合后仍能区分（蓝=上午下界，橙=下午上界）
            if (d.key === 'amY1') handle.style.border = '2.5px solid rgb(30,110,230)';
            if (d.key === 'pmY0') handle.style.border = '2.5px solid #f59e0b';
            handle.title = d.label;
            handle.style.cursor = d.axis === 'x' ? 'ew-resize' : 'ns-resize';
            handle.addEventListener('pointerdown', function (e) {
                e.preventDefault();
                handle.setPointerCapture(e.pointerId);
                function onMove(ev) {
                    var rect = ov.getBoundingClientRect();
                    var pct = d.axis === 'x'
                        ? (ev.clientX - rect.left) / rect.width
                        : (ev.clientY - rect.top) / rect.height;
                    applyCalibDrag(d.key, pct);
                }
                function onUp() {
                    handle.removeEventListener('pointermove', onMove);
                    handle.removeEventListener('pointerup', onUp);
                    handle.removeEventListener('pointercancel', onUp);
                }
                handle.addEventListener('pointermove', onMove);
                handle.addEventListener('pointerup', onUp);
                handle.addEventListener('pointercancel', onUp);
            });
            ov.appendChild(line);
            ov.appendChild(handle);
            calibHandles.push({ key: d.key, axis: d.axis, line: line, handle: handle });
        });
    }

    function applyCalibDrag(key, pct) {
        var g = calib.grids[state.orient];
        pct = Math.max(0.02, Math.min(0.98, pct));
        // 保持边界顺序合法
        if (key === 'x0') pct = Math.min(pct, g.x1 - 0.04);
        else if (key === 'x1') pct = Math.max(pct, g.x0 + 0.04);
        else if (key === 'amY0') pct = Math.min(pct, g.amY1 - 0.02);
        else if (key === 'amY1') {
            pct = Math.max(pct, g.amY0 + 0.02);
            if (calib.pmRows > 0) pct = Math.min(pct, g.pmY0); // 可与下午区上界重合，但不越过
        }
        else if (key === 'pmY0') {
            pct = Math.min(pct, g.pmY1 - 0.02);
            pct = Math.max(pct, g.amY1); // 可与上午区下界重合，但不越过
        }
        else if (key === 'pmY1') pct = Math.max(pct, g.pmY0 + 0.02);
        g[key] = pct;
        updateCalibOverlay();
        renderPreview();
    }

    function updateCalibOverlay() {
        if (!calib) return;
        var g = calib.grids[state.orient];
        calibHandles.forEach(function (h) {
            var show = h.key.indexOf('pm') !== 0 || calib.pmRows > 0; // 无下午时隐藏 pm 边界线
            h.line.style.display = show ? '' : 'none';
            h.handle.style.display = show ? '' : 'none';
            if (!show) return;
            if (h.axis === 'x') {
                var left = (g[h.key] * 100).toFixed(3) + '%';
                h.line.style.left = left;
                h.line.style.top = '0';
                h.line.style.height = '100%';
                h.handle.style.left = left;
                h.handle.style.top = '50%';
            } else {
                var top = (g[h.key] * 100).toFixed(3) + '%';
                h.line.style.top = top;
                h.line.style.left = '0';
                h.line.style.width = '100%';
                // 中间两线重合（或贴近 ≤3%）时，圆点自动左右错开，保证两个手柄都能抓住
                var hx = '50%';
                if (calib.pmRows > 0 && Math.abs(g.amY1 - g.pmY0) <= 0.03) {
                    if (h.key === 'amY1') hx = '28%';
                    else if (h.key === 'pmY0') hx = '72%';
                }
                h.handle.style.left = hx;
                h.handle.style.top = top;
            }
        });
    }

    // 输出与 TIMETABLE_THEMES 条目同格式的代码（坐标舍入 4 位小数），
    // 复制后可整段替换对应主题条目，与手写条目风格一致
    function calibJson() {
        var t = currentTheme();
        function rnd(v) { return Math.round(v * 10000) / 10000; } // 0.0001×1536px≈0.15px，远高于拖拽精度
        function gridLine(g) {
            return ['x0', 'x1', 'amY0', 'amY1', 'pmY0', 'pmY1']
                .map(function (k) { return k + ': ' + rnd(g[k]); }).join(', ');
        }
        return '{\n' +
            "    id: '" + t.id + "', name: '" + t.name + "', cols: " + calib.cols +
            ', amRows: ' + calib.amRows + ', pmRows: ' + calib.pmRows + ',\n' +
            '    h: {\n' +
            "        img: '" + t.h.img + "',\n" +
            '        grid: { ' + gridLine(calib.grids.h) + ' }\n' +
            '    },\n' +
            '    s: {\n' +
            "        img: '" + t.s.img + "',\n" +
            '        grid: { ' + gridLine(calib.grids.s) + ' }\n' +
            '    }\n' +
            '}';
    }

    function copyCalib() {
        var json = calibJson();
        $('tt-calib-out').value = json;
        var done = function () { toast('配置代码已生成，可从文本框复制'); };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(json).then(done, done);
        } else {
            fallbackCopy(json, done, done);
        }
    }

    // ===== 初始化 =====
    function debounceResize() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(renderPreview, 250);
    }

    function init() {
        loadDraft();
        ensureCells();
        if (CALIBRATE) initCalibrate();
        buildThemes();
        bindOrient();
        buildGrid();

        $('tt-btn-export').addEventListener('click', exportImage);
        $('tt-btn-copy').addEventListener('click', copyLink);
        $('tt-btn-clear').addEventListener('click', clearAll);

        $('tt-editor-mask').addEventListener('click', function () { showSheet(false); editing = null; });
        $('tt-editor-close').addEventListener('click', function () { showSheet(false); editing = null; });
        $('tt-editor-ok').addEventListener('click', function () {
            var v = $('tt-editor-input').value.trim();
            if (v) applyEdit(v); else { showSheet(false); editing = null; }
        });
        $('tt-editor-clear').addEventListener('click', function () {
            if (!editing) return;
            state.cells[editing.block][editing.r][editing.c] = '';
            afterEdit();
        });
        $('tt-editor-input').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                var v = $('tt-editor-input').value.trim();
                if (v) applyEdit(v); else { showSheet(false); editing = null; }
            }
        });

        $('tt-modal-mask').addEventListener('click', hideModal);
        $('tt-modal-close').addEventListener('click', hideModal);

        window.addEventListener('resize', debounceResize);
        renderPreview();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
