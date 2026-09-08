#!/bin/bash
# 将OJ游戏HTML转换为离线自包含版本
# 用法: bash transform_games.sh

SRC_DIR="../template/syzoj"
DST_DIR="./games"

# 游戏列表（源文件名 → 目标文件名）
declare -A GAMES=(
    ["puzzle_game.html"]="puzzle_game.html"
    ["clock_reading.html"]="clock_reading.html"
    ["math_game.html"]="math_game.html"
    ["color_match.html"]="color_match.html"
    ["guess_number.html"]="guess_number.html"
    ["memory_game.html"]="memory_game.html"
    ["sequence_memory.html"]="sequence_memory.html"
    ["snake.html"]="snake.html"
    ["bead_game.html"]="bead_game.html"
    ["number_puzzle.html"]="number_puzzle.html"
    ["idiom_chain.html"]="idiom_chain.html"
    ["minesweeper.html"]="minesweeper.html"
    ["keyboard_game.html"]="keyboard_game.html"
    ["balloon_typing.html"]="balloon_typing.html"
    ["frog_typing.html"]="frog_typing.html"
    ["coding_game.html"]="coding_game.html"
    ["ai_drawing_game.html"]="ai_drawing_game.html"
)

# 获取游戏中文名
get_title() {
    local file="$1"
    # 匹配 show_title="中文名 - xxx" 格式
    local title=""
    title=$(grep 'show_title=' "$file" 2>/dev/null | head -1 | sed 's/.*show_title="//;s/".*//;s/ - .*//')
    if [ -z "$title" ]; then
        title=$(grep '<title>' "$file" 2>/dev/null | head -1 | sed 's/.*<title>//;s/<\/title>.*//')
    fi
    echo "$title"
}

# 检查是否使用confetti
has_confetti() {
    local file="$1"
    grep -q "game_confetti" "$file" 2>/dev/null
}

echo "开始转换游戏文件..."

for src_name in "${!GAMES[@]}"; do
    dst_name="${GAMES[$src_name]}"
    src_file="$SRC_DIR/$src_name"
    dst_file="$DST_DIR/$dst_name"

    if [ ! -f "$src_file" ]; then
        echo "⚠️  跳过 $src_name: 文件不存在"
        continue
    fi

    # bead_game.html 需要手动维护（PHP代码需转为静态HTML，图片路径需特殊处理）
    if [ "$src_name" = "bead_game.html" ]; then
        echo "⏭️  跳过 $src_name: 包含PHP代码需手动转换为静态HTML，且图片路径需特殊处理"
        continue
    fi

    title=$(get_title "$src_file")
    echo "📦 转换: $src_name → $dst_name ($title)"

    # 提取游戏内容（去掉PHP标签行）
    # 1. 去掉前2行（PHP show_title 和 header include）
    # 2. 去掉最后1行（PHP footer include）
    # 3. 替换CDN引用
    # 4. 替换game_confetti路径
    # 5. 包装成完整HTML

    # 提取中间内容（跳过PHP行）
    content=$(sed '1,2d' "$src_file" | sed '$d')

    # 替换CDN引用为本地路径
    content=$(echo "$content" | sed 's|https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js|../js/confetti.min.js|g')

    # 替换game_confetti.js路径
    content=$(echo "$content" | sed "s|template/<?php echo \$OJ_TEMPLATE?>/game_confetti.js|../js/game_confetti.js|g")
    content=$(echo "$content" | sed 's|template/syzoj/game_confetti.js|../js/game_confetti.js|g')

    # 替换内嵌PHP的session注入（如frog_typing的USER_ID）；离线包无session，统一为guest
    content=$(echo "$content" | sed 's|var USER_ID = "<?php.*?>";|var USER_ID = "guest";|')

    # 移除body中的重复script标签（已经在head中添加了）
    content=$(echo "$content" | grep -v '<script src="../js/confetti.min.js"></script>')
    content=$(echo "$content" | grep -v '<script src="../js/game_confetti.js"></script>')

    # 写入目标文件
    cat > "$dst_file" << 'HEADER'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
HEADER

    # 添加游戏标题
    echo "    <title>${title}</title>" >> "$dst_file"

    # 如果使用confetti，添加confetti脚本
    if has_confetti "$src_file"; then
        echo '    <script src="../js/confetti.min.js"></script>' >> "$dst_file"
        echo '    <script src="../js/game_confetti.js"></script>' >> "$dst_file"
    fi

    # 添加返回按钮样式和内容
    cat >> "$dst_file" << 'STYLE'
    <style>
    .offline-back-btn {
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 9999;
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 14px;
        color: #333;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    .offline-back-btn:hover {
        background: #667eea;
        color: #fff;
        transform: translateY(-2px);
    }
    #auth-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.8);
        color: white;
        text-align: center;
        padding: 8px;
        font-size: 12px;
        z-index: 9999;
    }
    </style>
</head>
<body>
    <a href="../index.html" class="offline-back-btn">← 返回游戏大厅</a>
    <div id="auth-footer"></div>
    <!-- 未授权提示遮罩 -->
    <div id="no-auth-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:10000; align-items:center; justify-content:center;">
        <div style="background:white; border-radius:20px; padding:40px; max-width:400px; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <div style="font-size:50px; margin-bottom:15px;">🔐</div>
            <h2 style="color:#333; margin-bottom:15px;">需要激活授权</h2>
            <p style="color:#666; margin-bottom:20px; line-height:1.6;">检测到未授权或授权文件无效。<br>请先激活后再使用游戏。</p>
            <a href="activate.html" style="display:inline-block; background:linear-gradient(135deg, #667eea, #764ba2); color:white; text-decoration:none; padding:12px 25px; border-radius:10px; font-weight:600;">前往激活</a>
            <p style="margin-top:15px; font-size:0.8rem; color:#888;">推荐：在本页同目录的 activate.html 验证授权码后下载 license.js，放入离线包 js 文件夹，即可直接使用</p>
        </div>
    </div>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/license.js"></script>
    <script src="../js/rsa_verify.js"></script>
    <script src="../js/auth.js"></script>
    <script>
    $(document).ready(function() {
        Auth.checkAuth().then(function(result) {
            if (!result.valid) {
                // 显示未授权提示遮罩
                $('#no-auth-overlay').css('display', 'flex');
                // 隐藏游戏内容
                $('body > *:not(#no-auth-overlay):not(.offline-back-btn):not(#auth-footer)').hide();
                return;
            }
            if (result.license) {
                Auth.displayAuthInfo(result.license);
            }
        });
    });
    </script>
STYLE

    # 插入游戏内容
    echo "$content" >> "$dst_file"

    # 确保以</html>结尾
    if ! tail -1 "$dst_file" | grep -q "</html>"; then
        echo "" >> "$dst_file"
        echo "</html>" >> "$dst_file"
    fi

    # 兜底检查：静态HTML不解析PHP，残留的<?php会原样显示在页面上
    if grep -q '<?php' "$dst_file"; then
        echo "⚠️  警告: $dst_name 仍残留PHP代码，需手动处理（参考bead_game.html的做法）:"
        grep -n '<?php' "$dst_file"
    fi

    echo "✅ 完成: $dst_name"
done

echo ""
echo "🎉 所有游戏转换完成！"
echo "📁 输出目录: $DST_DIR"
ls -la "$DST_DIR"/*.html 2>/dev/null | wc -l
echo "个HTML文件已生成"
