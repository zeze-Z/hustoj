# HUSTOJ 模板范围守护（PreToolUse: Edit|Write）
# 规范：只允许修改 syzoj 模板（template/syzoj/），其他模板绝对不动。
# stdin: {"tool_name":"Edit","tool_input":{"file_path":"..."}}
# 拦截时输出 permissionDecision=deny 的 JSON，exit 0；其余情况静默放行。
import json
import sys


def deny(reason):
    print(json.dumps({
        "hookSpecificOutput": {
            "hookEventName": "PreToolUse",
            "permissionDecision": "deny",
            "permissionDecisionReason": reason,
        }
    }, ensure_ascii=True))
    sys.exit(0)


def main():
    try:
        data = json.load(sys.stdin)
    except Exception:
        sys.exit(0)  # 输入异常时放行，不阻塞正常工作

    path = (data.get("tool_input") or {}).get("file_path") or ""
    if not path:
        sys.exit(0)
    p = path.replace("\\", "/").lower()

    # 命中 template/ 的两种形态：绝对路径含 /template/，或相对路径以 template/ 开头
    after = None
    if "/template/" in p:
        after = p.split("/template/", 1)[1]
    elif p.startswith("template/"):
        after = p[len("template/"):]
    if after is None:
        sys.exit(0)

    top = after.split("/", 1)[0]
    if top != "syzoj":
        deny(
            "项目规范：只允许修改 syzoj 模板（template/syzoj/），其他模板绝对不动：%s。"
            "如确需修改该模板，请先征得用户同意。" % path
        )


main()
