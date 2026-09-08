#!/bin/bash
# AI-OJ离线游戏打包脚本
# 用法:
#   通用模式(无需授权): bash build_package.sh --generic
#   授权模式: bash build_package.sh --school "XX小学" --room "机房1" --expire 2027-09-01
#   私钥不在默认位置时: 追加 --private-key /path/to/private_key.pem

set -e

# 默认参数
SCHOOL=""
ROOM=""
EXPIRE=""
OUTPUT_DIR="./dist"
# 私钥默认位于 web 根目录之外（与 generate_license.py 的默认值保持一致）
PRIVATE_KEY="/home/judge/etc/offline_games/private_key.pem"
GENERIC_MODE=false
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# 解析参数
while [[ $# -gt 0 ]]; do
    case $1 in
        --school|-s)
            SCHOOL="$2"
            shift 2
            ;;
        --room|-r)
            ROOM="$2"
            shift 2
            ;;
        --expire|-e)
            EXPIRE="$2"
            shift 2
            ;;
        --output|-o)
            OUTPUT_DIR="$2"
            shift 2
            ;;
        --private-key)
            PRIVATE_KEY="$2"
            shift 2
            ;;
        --generic|-g)
            GENERIC_MODE=true
            shift
            ;;
        *)
            echo "[ERROR] 未知参数: $1"
            exit 1
            ;;
    esac
done

# 0. 目录防护：确保 .htaccess 存在（Apache 下拒绝 web 直接访问 offline-games 源目录，
#    防止包内容/授权文件被公网下载；nginx 需在 server 配置中对 /offline-games/ 返回 404）
if [ ! -f "$SCRIPT_DIR/.htaccess" ]; then
    echo "[ERROR] 缺少保护文件: $SCRIPT_DIR/.htaccess"
    echo "       该文件用于禁止 web 直接访问离线包源目录，请先创建（内容: Require all denied）"
    exit 1
fi

# 验证参数
if [ "$GENERIC_MODE" = true ]; then
    echo "========================================="
    echo "AI-OJ离线游戏打包工具 (通用版)"
    echo "========================================="
    echo "模式: 通用离线包（用户需输入授权码激活）"
    echo ""
else
    # 授权模式需要验证必要参数
    if [ -z "$SCHOOL" ]; then
        echo "[ERROR] 请指定学校名称: --school \"XX小学\""
        echo "       或使用通用模式: --generic"
        exit 1
    fi

    if [ -z "$ROOM" ]; then
        echo "[ERROR] 请指定机房名称: --room \"计算机教室1\""
        exit 1
    fi

    if [ -z "$EXPIRE" ]; then
        echo "[ERROR] 请指定有效期: --expire 2027-09-01"
        exit 1
    fi

    if [ ! -f "$PRIVATE_KEY" ]; then
        echo "[ERROR] 私钥不存在: $PRIVATE_KEY"
        echo "       请用 --private-key 指定私钥路径，或将私钥部署到 /home/judge/etc/offline_games/private_key.pem"
        exit 1
    fi

    echo "========================================="
    echo "AI-OJ离线游戏打包工具 (授权版)"
    echo "========================================="
    echo "学校: $SCHOOL"
    echo "机房: $ROOM"
    echo "有效期: $EXPIRE"
    echo ""
fi

# 1. 创建打包目录
echo "[1/4] 准备打包目录..."
if [ "$GENERIC_MODE" = true ]; then
    PACKAGE_NAME="aioj.top离线游戏包"
else
    PACKAGE_NAME="aioj.top离线游戏包-${SCHOOL}-$(date +%Y%m%d)"
fi
PACKAGE_DIR="$OUTPUT_DIR/$PACKAGE_NAME"
rm -rf "$PACKAGE_DIR"
mkdir -p "$PACKAGE_DIR"

# 2. 生成授权文件（仅授权模式）
#    直接写入包根（随 zip 分发），不落 offline-games 源目录——
#    真实签名的 license.dat 残留在 web 目录 = 任何人可下载即免费激活
if [ "$GENERIC_MODE" = false ]; then
    echo "[2/4] 生成授权文件..."
    # Ubuntu 22.04 等环境默认无 python 命令，优先 python3 并回退
    PY_BIN="python3"
    command -v python3 &> /dev/null || PY_BIN="python"
    "$PY_BIN" "$SCRIPT_DIR/admin/generate_license.py" \
        --private-key "$PRIVATE_KEY" \
        --school "$SCHOOL" \
        --room "$ROOM" \
        --expire "$EXPIRE" \
        -o "$PACKAGE_DIR/license.dat"
    if [ ! -f "$PACKAGE_DIR/license.dat" ]; then
        echo "[ERROR] license.dat 生成失败，请检查私钥配置"
        exit 1
    fi
    # 包装为 license.js（页面经 <script src> 加载本地文件不受 file:// 限制，
    # 学生机双击 index.html 即可自动验签，零操作进入游戏）；license.dat 保留在包根作 Firefox fetch/XHR 兜底
    mkdir -p "$PACKAGE_DIR/js"
    { echo "window.OG_LICENSE_DATA ="; cat "$PACKAGE_DIR/license.dat"; echo ";"; } > "$PACKAGE_DIR/js/license.js"
else
    echo "[2/4] 通用模式（不预生成授权文件，用户激活后生成）..."
fi

# 3. 复制文件并混淆JS
echo "[3/4] 复制文件并混淆JS..."
cp -r "$SCRIPT_DIR/index.html" "$PACKAGE_DIR/"
cp -r "$SCRIPT_DIR/js" "$PACKAGE_DIR/"
cp -r "$SCRIPT_DIR/games" "$PACKAGE_DIR/"

# 授权模式的 license.dat（包根）与 js/license.js 已在 [2/4] 直接生成
# 通用模式：不生成授权文件，用户在 games/activate.html 激活后自行下载 license.js 放入 js 目录
# activate.html 位于源 games/ 目录，随下方 games/ 整体拷贝进包

# 混淆JS文件
echo "   混淆JS文件..."
if command -v terser &> /dev/null; then
    # 混淆 js/ 目录下的JS文件（跳过 auth.js，避免属性名混淆导致授权失效）
    for js_file in "$PACKAGE_DIR"/js/*.js; do
        if [ -f "$js_file" ]; then
            filename=$(basename "$js_file")
            if [ "$filename" = "auth.js" ] || [ "$filename" = "license.js" ]; then
                echo "   - $filename (跳过混淆，保持原样)"
                continue
            fi
            echo "   - $filename"
            terser "$js_file" \
                --compress \
                --mangle \
                --output "$js_file" 2>/dev/null || true
        fi
    done

    # 混淆游戏HTML中的内联JS（提取并混淆）
    for html_file in "$PACKAGE_DIR"/games/*.html; do
        if [ -f "$html_file" ]; then
            filename=$(basename "$html_file")
            echo "   - $filename (inline JS)"
            # 使用sed提取内联JS，混淆后替换
            # 简单处理：混淆整个HTML文件中的<script>块
            temp_file="${html_file}.tmp"
            # 保留HTML结构，混淆JS部分
            python3 -c "
import re
import subprocess
import sys

with open('$html_file', 'r', encoding='utf-8') as f:
    content = f.read()

def obfuscate_js(match):
    js_code = match.group(1)
    if len(js_code.strip()) < 50:  # 太短的不混淆
        return match.group(0)
    try:
        result = subprocess.run(
            ['terser', '--compress', '--mangle'],
            input=js_code,
            capture_output=True,
            text=True,
            timeout=5
        )
        if result.returncode == 0:
            return '<script>' + result.stdout + '</script>'
    except:
        pass
    return match.group(0)

content = re.sub(r'<script(?:\s[^>]*)?>(.+?)</script>', obfuscate_js, content, flags=re.DOTALL)

with open('$html_file', 'w', encoding='utf-8') as f:
    f.write(content)
" 2>/dev/null || true
        fi
    done
    echo "   混淆完成"
else
    echo "   [WARNING] terser未安装，跳过混淆"
fi

# 4. 生成ZIP包
echo "[4/4] 生成ZIP包..."
cd "$OUTPUT_DIR"

# 检测可用的压缩工具
if command -v zip &> /dev/null; then
    zip -r "$PACKAGE_NAME.zip" "$PACKAGE_NAME"
elif command -v powershell &> /dev/null; then
    # 使用PowerShell压缩
    powershell -Command "Compress-Archive -Path '$PACKAGE_NAME' -DestinationPath '$PACKAGE_NAME.zip' -Force"
else
    echo "[WARNING] 未找到压缩工具，请手动压缩: $PACKAGE_DIR"
    echo "  Windows: 右键文件夹 -> 发送到 -> 压缩(zipped)文件夹"
    echo "  Mac/Linux: zip -r $PACKAGE_NAME.zip $PACKAGE_NAME"
fi

cd "$SCRIPT_DIR"

# 清理临时目录（如果ZIP已生成）
if [ -f "$OUTPUT_DIR/$PACKAGE_NAME.zip" ]; then
    rm -rf "$PACKAGE_DIR"
fi

echo ""
echo "========================================="
echo "打包完成！"
echo "========================================="
echo "输出文件: $OUTPUT_DIR/$PACKAGE_NAME.zip"
echo ""
if [ "$GENERIC_MODE" = true ]; then
    echo "【通用版使用说明】"
    echo "1. 将ZIP包发送给教师"
    echo "2. 教师解压后打开 games/activate.html 输入授权码验证（授权码在网站「更多」页面用积分兑换）"
    echo "3. 验证通过后点击「下载 license.js」，将文件放入离线包 js 目录（与 auth.js 同级）"
    echo "4. 将整个离线包拷贝到学生机，双击 index.html 即可直接进入游戏，无需逐台激活"
    echo "   （备用：单机换浏览器时，在 games/activate.html 重新粘贴同一授权码验证即可）"
else
    echo "【授权版使用说明】"
    echo "1. 将ZIP包发送给教师"
    echo "2. 教师解压后双击 index.html 即可进入游戏（包内 js 目录已内置授权文件 license.js）"
    echo "3. 授权过期后：登录网站续费兑换新授权码，在 games/activate.html 验证并下载新的"
    echo "   license.js，替换包内 js 目录下的旧文件即可"
fi
echo ""
