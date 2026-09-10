#!/bin/bash
# HUSTOJ 模板范围守护入口：优先 python（本机 python3 是坏存根），macOS 回退 python3；
# Windows 原生 python 不认 MSYS 的 /d/... 路径，须 cygpath 转 Windows 路径
PY=python; command -v python >/dev/null 2>&1 || PY=python3
SCRIPT="$(cd "$(dirname "$BASH_SOURCE")" && pwd)/guard_template.py"
command -v cygpath >/dev/null 2>&1 && SCRIPT="$(cygpath -w "$SCRIPT")"
exec "$PY" "$SCRIPT"
