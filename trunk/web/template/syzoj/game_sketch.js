/**
 * game_sketch.js - 手绘童趣质感共享封装（依赖自托管 rough.js，window.rough）
 * 与 game_confetti.js 同模式：rough.js 加载失败时静默降级，调用方无感（GameSketch.available === false）。
 *
 * 复用方式（任意游戏页引入两行脚本即可）：
 *   <script src="template/<?php echo $OJ_TEMPLATE?>/js/rough.min.js"></script>
 *   <script src="template/<?php echo $OJ_TEMPLATE?>/game_sketch.js"></script>
 *
 * 设计原则（保持"精致"不糊）：
 *   - rough.js 只画【游戏道具/棋盘/装饰边框】，文字与 UI chrome（统计卡/按钮/弹窗）保持玻璃拟态，不手绘
 *   - 所有形状接受 seed（确定性种子）：重绘/重开图形不抖动；不传 seed 每次会随机歪
 *   - 画进 SVG（非 canvas）：命中/transform/主题改色都走 DOM，CSS 可覆盖 rough 路径的 fill/stroke
 *
 * API：
 *   GameSketch.available          boolean，false 时下面全是 no-op
 *   GameSketch.seedFrom(str)      任意字符串 -> 稳定数字种子
 *   GameSketch.matchstick(el,{})  火柴棒（杆+头，horiz/ghost），塞进 el 内部
 *   GameSketch.frame(el,{})       手绘描边框，叠加在 el 上（el 需 position:relative）
 *   GameSketch.litMatch(el,{})    点燃的火柴图标（杆+头+三层火焰），标题/奖章用
 *   GameSketch.flameInto(el,{})   大号三层火焰（外/中/芯），通关弹窗/篝火用
 *   GameSketch.flame(svg,x,y,w,h,style,cls)  单层火焰路径
 *   GameSketch.rect/circle/ellipse/polygon/path/line(svg, geom, style)  通用图形（tangram/藏宝图/天平等复用）
 */
(function (global) {
    "use strict";

    var available = !!(global.rough && typeof global.rough.svg === "function");
    var NS = "http://www.w3.org/2000/svg";

    function seedFrom(str) {
        str = String(str);
        var h = 2166136261;
        for (var i = 0; i < str.length; i++) {
            h ^= str.charCodeAt(i);
            h = (h * 16777619) >>> 0;
        }
        return (h % 2147483647) || 1;
    }

    /* 在 el 内建一个 100%x100% 的 SVG 画布（viewBox=像素尺寸，1 单位=1px，图形不被拉伸） */
    function svgInto(el, cls) {
        var w = el.offsetWidth, h = el.offsetHeight;
        if (!w || !h) return null;
        var svg = document.createElementNS(NS, "svg");
        svg.setAttribute("viewBox", "0 0 " + w + " " + h);
        svg.setAttribute("width", "100%");
        svg.setAttribute("height", "100%");
        svg.classList.add("gs-draw");
        if (cls) svg.classList.add(cls);
        el.appendChild(svg);
        return svg;
    }

    /* 火柴棒：横/竖杆 + 圆头。ghost=true 只画空位虚线轮廓（候选落点） */
    function matchstick(el, opts) {
        if (!available || !el) return;
        opts = opts || {};
        var svg = svgInto(el);
        if (!svg) return;
        var w = el.offsetWidth, h = el.offsetHeight;
        var horiz = opts.horiz !== undefined ? !!opts.horiz : (w >= h);
        var seed = opts.seed || 1;
        var rc = global.rough.svg(svg);

        if (opts.ghost) {
            svg.appendChild(rc.rectangle(2, 2, Math.max(2, w - 4), Math.max(2, h - 4), {
                stroke: opts.stroke || "rgba(155,175,235,.55)",
                strokeWidth: 1.5,
                strokeLineDash: [3, 3],
                roughness: 1.1,
                bowing: 0.6,
                seed: seed
            }));
            return;
        }

        var thick = (horiz ? h : w) * 0.55;          /* 杆粗 = 短边的 55% */
        var headD = (horiz ? h : w) * 1.22;           /* 头径固定比例，杆再长也不拉伸 */
        var wood = opts.wood || "#D9A05B";
        var woodDark = opts.woodDark || "#A5733F";
        var head = opts.head || "#E34A33";
        var headDark = opts.headDark || "#B83220";
        var shapeOpts = { fillStyle: "solid", roughness: 1.25, bowing: 0.7 };

        var stick, cap;
        if (horiz) {
            var x1 = headD * 0.32, x2 = Math.max(x1 + 2, w - thick * 0.15);
            stick = rc.rectangle(x1, h / 2 - thick / 2, x2 - x1, thick, Object.assign({}, shapeOpts, {
                fill: wood, stroke: woodDark, strokeWidth: 1.3, seed: seed
            }));
            cap = rc.circle(headD * 0.52, h / 2, headD, Object.assign({}, shapeOpts, {
                fill: head, stroke: headDark, strokeWidth: 1.3, seed: seed + 1
            }));
        } else {
            var y1 = headD * 0.32, y2 = Math.max(y1 + 2, h - thick * 0.15);
            stick = rc.rectangle(w / 2 - thick / 2, y1, thick, y2 - y1, Object.assign({}, shapeOpts, {
                fill: wood, stroke: woodDark, strokeWidth: 1.3, seed: seed
            }));
            cap = rc.circle(w / 2, headD * 0.52, headD, Object.assign({}, shapeOpts, {
                fill: head, stroke: headDark, strokeWidth: 1.3, seed: seed + 1
            }));
        }
        /* 填色路径打 gs-fill 标：CSS 换色只打到 fill 路径，不碰 stroke 路径的 fill="none" */
        tagFill(stick); tagFill(cap);
        stick.classList.add("gs-stick");
        cap.classList.add("gs-head");
        svg.appendChild(stick);
        svg.appendChild(cap);
    }

    /* 手绘边框：叠加式（absolute），不占布局。同一元素重复调用会替换旧框 */
    function frame(el, opts) {
        if (!available || !el) return;
        opts = opts || {};
        var old = el.querySelector(":scope > .gs-frame");
        if (old) old.parentNode.removeChild(old);
        var w = el.offsetWidth, h = el.offsetHeight;
        if (!w || !h) return;
        var svg = document.createElementNS(NS, "svg");
        svg.setAttribute("viewBox", "0 0 " + w + " " + h);
        svg.setAttribute("width", "100%");
        svg.setAttribute("height", "100%");
        svg.classList.add("gs-draw", "gs-frame");
        el.appendChild(svg);
        var inset = opts.inset || 6;
        svg.appendChild(global.rough.svg(svg).rectangle(inset, inset, Math.max(2, w - inset * 2), Math.max(2, h - inset * 2), {
            stroke: opts.color || "rgba(255,217,138,.35)",
            strokeWidth: opts.strokeWidth || 2,
            roughness: opts.roughness || 1.4,
            bowing: 1,
            seed: opts.seed || 3
        }));
    }

    /* 通用图形（供 tangram/坐标寻宝/天平等复用） */
    /* ===== 火焰：泪滴路径（尖顶圆底），rough 描边自带手绘歪扭 ===== */
    function flameColors(opts) {
        opts = opts || {};
        return {
            outer: opts.outer || "#F59E0B", outerLine: opts.outerLine || "#E34A33",
            mid: opts.mid || "#FFB347", midLine: opts.midLine || "#F59E0B",
            core: opts.core || "#FFE387", coreLine: opts.coreLine || "#FFB347"
        };
    }
    function flamePathD(x, y, w, h) {
        return "M" + (x + w / 2) + "," + y +
            " C" + (x + w * 0.96) + "," + (y + h * 0.36) + " " + (x + w * 0.86) + "," + (y + h * 0.8) + " " + (x + w / 2) + "," + (y + h) +
            " C" + (x + w * 0.14) + "," + (y + h * 0.8) + " " + (x + w * 0.04) + "," + (y + h * 0.36) + " " + (x + w / 2) + "," + y + " Z";
    }
    function flame(svg, x, y, w, h, style, cls) {
        if (!available || !svg) return null;
        var node = global.rough.svg(svg).path(flamePathD(x, y, w, h), style || {});
        tagFill(node);
        if (cls) node.classList.add(cls);
        svg.appendChild(node);
        return node;
    }
    /* 三层焰（外/中/芯），底对齐中心轴 cx，底边 baseY；cls: gs-flame-out/mid/core */
    function drawFlame(svg, cx, baseY, w, h, seed, colors) {
        var L = [
            { fy: 0, fh: 1, fw: 1, c: colors.outer, line: colors.outerLine, cls: "gs-flame-out" },
            { fy: 0.3, fh: 0.62, fw: 0.58, c: colors.mid, line: colors.midLine, cls: "gs-flame-mid" },
            { fy: 0.52, fh: 0.34, fw: 0.3, c: colors.core, line: colors.coreLine, cls: "gs-flame-core" }
        ];
        for (var i = 0; i < L.length; i++) {
            var it = L[i], lw = w * it.fw, lh = h * it.fh;
            flame(svg, cx - lw / 2, baseY - h + h * it.fy, lw, lh, {
                fill: it.c, fillStyle: "solid", stroke: it.line, strokeWidth: 1.6,
                roughness: 1.35, bowing: 0.9, seed: (seed || 5) + i * 17
            }, it.cls);
        }
    }
    /* 大火焰进 el（通关弹窗/篝火）；opts: seed/spread/fill/base/配色覆盖 */
    function flameInto(el, opts) {
        if (!available || !el) return null;
        opts = opts || {};
        var svg = svgInto(el, "gs-flame");
        if (!svg) return null;
        var w = el.offsetWidth, h = el.offsetHeight;
        drawFlame(svg, w / 2, h * (opts.base !== undefined ? opts.base : 0.94),
            w * (opts.spread || 0.9), h * (opts.fill || 0.9), opts.seed || 5, flameColors(opts));
        return svg;
    }
    /* 点燃的火柴图标（杆+头+焰）：标题/奖章用。opts: seed/配色覆盖 */
    function litMatch(el, opts) {
        if (!available || !el) return null;
        opts = opts || {};
        var svg = svgInto(el, "gs-litmatch");
        if (!svg) return null;
        var w = el.offsetWidth, h = el.offsetHeight;
        var seed = opts.seed || 9;
        var rc = global.rough.svg(svg);
        var stickW = Math.max(3, w * 0.15), stickTop = h * 0.52, stickH = h * 0.46;
        var stick = rc.rectangle((w - stickW) / 2, stickTop, stickW, stickH, {
            fill: opts.wood || "#D9A05B", fillStyle: "solid", stroke: opts.woodDark || "#A5733F",
            strokeWidth: 1.2, roughness: 1.2, bowing: 0.6, seed: seed
        });
        var headD = Math.max(4, w * 0.36);
        var cap = rc.circle(w / 2, stickTop + headD * 0.12, headD, {
            fill: opts.head || "#E34A33", fillStyle: "solid", stroke: opts.headDark || "#B83220",
            strokeWidth: 1.2, roughness: 1.2, bowing: 0.6, seed: seed + 1
        });
        tagFill(stick); tagFill(cap);
        stick.classList.add("gs-lm-stick"); cap.classList.add("gs-lm-head");
        svg.appendChild(stick);
        svg.appendChild(cap);
        drawFlame(svg, w / 2, stickTop + headD * 0.16, w * 0.82, h * 0.56, seed + 2, flameColors(opts));
        return svg;
    }
    function tagFill(node) {
        if (!node || !node.querySelectorAll) return;
        var ps = node.querySelectorAll("path");
        for (var i = 0; i < ps.length; i++) {
            var f = ps[i].getAttribute("fill");
            if (f && f !== "none") ps[i].classList.add("gs-fill");
        }
    }
    function push(svg, node, cls) {
        if (!available || !svg || !node) return null;
        if (cls) node.classList.add(cls);
        tagFill(node);
        svg.appendChild(node);
        return node;
    }
    function rect(svg, x, y, w, h, style, cls) {
        if (!available || !svg) return null;
        return push(svg, global.rough.svg(svg).rectangle(x, y, w, h, style || {}), cls);
    }
    function circle(svg, cx, cy, d, style, cls) {
        if (!available || !svg) return null;
        return push(svg, global.rough.svg(svg).circle(cx, cy, d, style || {}), cls);
    }
    /* 椭圆：w/h 为全宽全高（非半径） */
    function ellipse(svg, cx, cy, w, h, style, cls) {
        if (!available || !svg) return null;
        return push(svg, global.rough.svg(svg).ellipse(cx, cy, w, h, style || {}), cls);
    }
    /* 线段：吊绳/支架用（stroke-only，无 gs-fill） */
    function line(svg, x1, y1, x2, y2, style, cls) {
        if (!available || !svg) return null;
        return push(svg, global.rough.svg(svg).line(x1, y1, x2, y2, style || {}), cls);
    }
    function polygon(svg, points, style, cls) {
        if (!available || !svg) return null;
        return push(svg, global.rough.svg(svg).polygon(points, style || {}), cls);
    }
    function path(svg, d, style, cls) {
        if (!available || !svg) return null;
        return push(svg, global.rough.svg(svg).path(d, style || {}), cls);
    }

    global.GameSketch = {
        available: available,
        seedFrom: seedFrom,
        svgInto: svgInto,
        matchstick: matchstick,
        frame: frame,
        flame: flame,
        flameInto: flameInto,
        litMatch: litMatch,
        rect: rect,
        circle: circle,
        ellipse: ellipse,
        polygon: polygon,
        path: path,
        line: line
    };
})(window);
