# HUSTOJ SQL 归档守护（PreToolUse: Bash, 仅 git commit）
# CLAUDE.md 强制规范：DDL（ADD/MODIFY/DROP COLUMN、CREATE/DROP TABLE 等）必须先归档到
# db/V{版本}_{日期}_{描述}.sql（含回滚SQL）并更新 db/RELEASE_STEPS.md，再提交。
# stdin: {"tool_name":"Bash","tool_input":{"command":"git commit ..."}}
# 检测将提交内容里含 DDL 而 db/ 无新增/改动归档时，deny 并说明。
import json
import re
import subprocess
import sys

DDL_RE = re.compile(
    r"ADD\s+COLUMN|MODIFY\s+COLUMN|DROP\s+COLUMN|CHANGE\s+COLUMN"
    r"|CREATE\s+TABLE|DROP\s+TABLE|RENAME\s+TABLE",
    re.IGNORECASE,
)


def deny(reason):
    print(json.dumps({
        "hookSpecificOutput": {
            "hookEventName": "PreToolUse",
            "permissionDecision": "deny",
            "permissionDecisionReason": reason,
        }
    }, ensure_ascii=True))
    sys.exit(0)


def added_lines(diff_text):
    return "\n".join(
        line[1:]
        for line in diff_text.splitlines()
        if line.startswith("+") and not line.startswith("+++")
    )


def main():
    try:
        data = json.load(sys.stdin)
    except Exception:
        sys.exit(0)

    cmd = (data.get("tool_input") or {}).get("command") or ""
    if not re.match(r"\s*git\s+commit\b", cmd):
        sys.exit(0)

    # 将提交的内容：staged diff；commit -a / --all 时补上已跟踪文件的工作区改动
    text = subprocess.run(
        ["git", "diff", "--cached", "--unified=0"],
        capture_output=True, text=True,
    ).stdout
    if re.search(r"(^|\s)(-a|--all)\b", cmd):
        text += subprocess.run(
            ["git", "diff", "--unified=0"],
            capture_output=True, text=True,
        ).stdout

    if not DDL_RE.search(added_lines(text)):
        sys.exit(0)

    # 有 DDL：db/ 目录须有新增或改动的归档（含未跟踪的新 .sql）
    st = subprocess.run(
        ["git", "status", "--porcelain", "--", "db/"],
        capture_output=True, text=True,
    ).stdout
    if not st.strip():
        deny(
            "检测到将提交的 DDL（ADD/MODIFY/DROP COLUMN、CREATE/DROP TABLE 等），"
            "但 db/ 目录没有新的 SQL 归档。按 CLAUDE.md 规范：先创建 "
            "db/V{版本号}_{日期}_{功能描述}.sql（文件末尾附回滚SQL）并更新 "
            "db/RELEASE_STEPS.md，再提交。"
        )


main()
