#!/usr/bin/env python3
"""
# 安装依赖
  pip3 install cryptography>=3.0 或 apt-get install python3-cryptography

AI-OJ离线游戏授权码生成工具
用法: python generate_license.py --school "XX小学" --room "机房1" --expire 2027-09-01
     python generate_license.py --private-key /home/judge/etc/offline_games/private_key.pem ...
     python generate_license.py --school "XX小学" --room "机房1" --expire 2027-09-01 \
            --games snake,math_game,minesweeper   # 任选3款促销包
"""

import argparse
import json
import base64
import os
from datetime import datetime

# RSA私钥默认路径：web 根目录之外（私钥绝不能部署在 web 可访问目录下）
# 如私钥存放在其他位置，用 --private-key 参数指定
DEFAULT_PRIVATE_KEY_PATH = '/home/judge/etc/offline_games/private_key.pem'

# 可选游戏白名单：id 必须与离线包 games/{id}.html、auth.js 的 OG_GAME_NAMES、
# 网站端 my_func.inc.php 的 point_offline_game_catalog() 保持一致。
# 促销包（任选3款）授权时用 --games 传入；不传 = 全套授权（兼容旧授权码）。
GAME_WHITELIST = {
    'puzzle_game', 'clock_reading', 'math_game', 'color_match',
    'guess_number', 'memory_game', 'sequence_memory', 'snake',
    'bead_game', 'number_puzzle', 'idiom_chain', 'minesweeper',
    'keyboard_game', 'balloon_typing', 'frog_typing',
    'coding_game', 'ai_drawing_game',
}


def parse_games(games_str):
    """解析并校验 --games 参数：去重后必须恰好 3 个合法 id，返回排序后的列表。"""
    if games_str is None or games_str.strip() == '':
        return None
    ids = []
    seen = set()
    for raw in games_str.split(','):
        gid = raw.strip()
        if gid == '':
            continue
        if gid not in GAME_WHITELIST:
            print(f"[ERROR] 未知游戏 id: {gid}")
            exit(1)
        if gid not in seen:
            seen.add(gid)
            ids.append(gid)
    if len(ids) != 3:
        print(f"[ERROR] 促销包必须任选恰好 3 款游戏（去重后当前为 {len(ids)} 款）")
        exit(1)
    return sorted(ids)

def load_private_key(key_path):
    """加载RSA私钥"""
    try:
        from cryptography.hazmat.primitives import serialization
        from cryptography.hazmat.backends import default_backend

        with open(key_path, 'rb') as f:
            private_key = serialization.load_pem_private_key(
                f.read(),
                password=None,
                backend=default_backend()
            )
        return private_key
    except ImportError:
        print("[ERROR] 需要安装 cryptography 库: pip install cryptography")
        exit(1)
    except FileNotFoundError:
        print(f"[ERROR] 私钥文件不存在: {key_path}")
        print("       请将私钥部署到默认路径，或使用 --private-key 指定正确路径")
        exit(1)

def sign_data(private_key, data):
    """使用RSA-PSS签名"""
    from cryptography.hazmat.primitives import hashes
    from cryptography.hazmat.primitives.asymmetric import padding

    data_bytes = data.encode('utf-8')
    signature = private_key.sign(
        data_bytes,
        padding.PSS(
            mgf=padding.MGF1(hashes.SHA256()),
            salt_length=32  # 与前端 auth.js 验证保持一致
        ),
        hashes.SHA256()
    )
    return base64.b64encode(signature).decode('utf-8')

def generate_license(args):
    """生成授权文件"""
    # 加载私钥
    private_key = load_private_key(args.private_key)

    # 解析促销包游戏白名单（None = 全套；否则为排序后的 3 个 id）
    games = parse_games(args.games)

    # 构建签名数据
    # 全套（旧格式兼容）：school|room|expire
    # 促销包：school|room|expire|id1,id2,id3（排序后拼接，顺序无关；验签端据此判定授权范围）
    sign_data_str = f"{args.school}|{args.room}|{args.expire}"
    if games is not None:
        sign_data_str += "|" + ",".join(games)

    # 生成签名
    signature = sign_data(private_key, sign_data_str)

    # 构建license数据
    license_data = {
        "school": args.school,
        "room": args.room,
        "expire": args.expire,
        "created": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "version": "1.1",
        "signature": signature
    }
    if games is not None:
        license_data["games"] = games

    # 输出license文件
    output_file = args.output or f"license_{args.school}_{args.room}.dat"

    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(license_data, f, ensure_ascii=False, indent=2)

    print(f"[OK] 授权文件已生成: {output_file}")
    print(f"   学校: {args.school}")
    print(f"   机房: {args.room}")
    print(f"   有效期: {args.expire}")
    if games is not None:
        print(f"   授权游戏(任选3款促销包): {', '.join(games)}")
    else:
        print(f"   授权游戏: 全套（17款）")
    print(f"   签名: {signature[:20]}...")

    return True

def main():
    parser = argparse.ArgumentParser(
        description='AI-OJ离线游戏授权码生成工具',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
示例:
  # 生成授权文件（私钥从默认路径 /home/judge/etc/offline_games/private_key.pem 读取）
  python generate_license.py --school "XX小学" --room "计算机教室1" --expire 2027-09-01

  # 指定私钥路径
  python generate_license.py --private-key /path/to/private_key.pem --school "XX小学" --room "机房1" --expire 2027-09-01

  # 指定输出路径
  python generate_license.py --school "XX小学" --room "机房1" --expire 2027-09-01 -o /path/to/license.dat
        """
    )

    parser.add_argument('--school', '-s', required=True,
                       help='学校名称')
    parser.add_argument('--room', '-r', required=True,
                       help='机房名称（如"计算机教室1"）')
    parser.add_argument('--expire', '-e', required=True,
                       help='有效期 (格式: YYYY-MM-DD)')
    parser.add_argument('--output', '-o',
                       help='输出文件路径')
    parser.add_argument('--games',
                       default=None,
                       help='任选3款促销包授权游戏，逗号分隔的3个游戏id（如 snake,math_game,minesweeper）；'
                            '不传则生成全套授权')
    parser.add_argument('--private-key',
                       default=DEFAULT_PRIVATE_KEY_PATH,
                       help='RSA私钥路径（默认: %(default)s，须位于 web 根目录之外）')

    args = parser.parse_args()

    # 验证日期格式
    try:
        datetime.strptime(args.expire, '%Y-%m-%d')
    except ValueError:
        print(f"[ERROR] 日期格式错误: {args.expire}")
        print("   正确格式: YYYY-MM-DD")
        return

    # 生成授权
    generate_license(args)

if __name__ == '__main__':
    main()
