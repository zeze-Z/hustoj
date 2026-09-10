---
name: gesp-import
description: 把 GESP/竞赛真题 PDF 转换成 OJ 可导入的选择题 CSV 和编程题 FPS XML。当用户提供真题 PDF，要求导入题库、录入真题、转换成导入文件时使用。
---

# GESP 真题 PDF → OJ 导入文件

格式规范的权威文件（每次必读，勿凭记忆写格式）：
- `docs/竞赛真题/题目导入模板格式规范.md` — CSV 13 列定义、转义、缩进、检查清单
- `docs/竞赛真题/选择题批量导入模板.csv` / `docs/竞赛真题/编程题批量导入模板(FPS格式).xml`
- `docs/竞赛真题/编程题导入说明.md` — FPS 字段与测试数据写法

输出命名：`docs/竞赛真题/GESP_{年}_{月}_L{等级}_选择题.csv` 与 `_编程题.xml`（参照既有 GESP_2025_12 / GESP_2026_06 文件）。

## 流程

### 1. 提取 —— 多源交叉，任何单一来源都不可信

GESP 试卷 PDF 的数学公式是 OMML 图形对象（无文本层），且代码块脱离原文位置。三种工具各有盲区：

| 工具 | 能拿到 | 拿不到/坑 |
|---|---|---|
| pypdf/PyMuPDF 文本提取 | 题干、选项、代码、单选答案表 | **公式全丢**（留空格）；判断题答案表 √× 全丢；代码块被挪到页尾、行号穿插 |
| MinerU flash-extract（`~/.local/bin/mineru-open-api`，不在 PATH） | 文本 + **判断题答案表 √×** | 公式同样丢；**可能整段丢选项**（实测丢过一道题的 B/C 选项）；表格单元格误读（行号拼进数值，如 9 读成 119） |
| PyMuPDF 渲染 PNG → Read（得 CDN URL）→ `analyze_image` 视觉读取 | **公式、答案符号、代码排版全部可读** | 有误读风险，关键数字需二次确认 |

标准做法：
1. pypdf 提全文（`p.extract_text()`），拿到主体结构和单选答案
2. MinerU flash-extract 跑一遍，交叉确认答案表
3. 用 PyMuPDF 把缺公式的页面渲染成 PNG（`page.get_pixmap(dpi=150)`），视觉读取补全公式和判断题答案
4. 不确定的细节（数字、公式）：`page.search_for(锚文本)` 定位 → `get_pixmap(dpi=300, clip=Rect)` 裁局部高清图 → 再视觉读取复核

### 2. 验证 —— 答案必须三方一致

- **答案表 vs 逐题逻辑推导**：自己把每道题算一遍，与 PDF 答案表比对。不一致说明题面提取有误（大概率是公式/代码抄错），回头重查
- **编程题**：PDF 末尾的参考程序是 ground truth（阈值、循环次数、输出格式都从这里确认）；样例输入输出必须手工验算；判断题 √× 映射为 A=正确 / B=错误

### 3. 生成

- 选择题 CSV：13 列；代码入题干（多行字段用双引号包裹，内部 `"` 写成 `""`）；`<` `>` 转义 `&lt;` `&gt;`；4 空格缩进；删行号；判断题固定 选项A=正确、选项B=错误
- 编程题 XML：`<item>` 含 title/score/time_limit/memory_limit/description(CDATA)/input/output/sample/hint(CDATA)/source/difficulty；测试数据写成多个同名配对 `test_input`/`test_output`
- 测试数据设计：覆盖两个样例 + 数据范围边界（上下限、规则阈值两侧如 800/800.1）+ 全同值；全部用参考程序逻辑模拟验证后再写入

### 4. 检查（生成后必跑）

```bash
# CSV 结构：25 行数据、每行 13 列、答案序列正确
python -X utf8 -c "import csv; rows=list(csv.reader(open('docs/竞赛真题/GESP_*_L1_选择题.csv',encoding='utf-8'))); print(len(rows), {len(r) for r in rows})"

# 规范第四节检查：裸 < >、重复转义、行号残留、选项全空
grep -nE '[<>]' docs/竞赛真题/GESP_*.csv        # 应无输出
grep -n '&amp;' docs/竞赛真题/GESP_*.csv        # 应无输出
grep -nE '^[0-9]+ ' docs/竞赛真题/GESP_*.csv    # 应无输出

# XML：test_input/test_output name 配对 + 用参考程序逻辑模拟全部测试数据
```

**CSV 引号是最大坑**（实测踩过两次）：多行字段漏收尾引号会把后面几行全部吞掉；含 ASCII 逗号的字段（如解析里的 `printf("%c", '\n')`、输出序列 `1,3,4,`）不加引号会被拆列。靠 python csv 解析验证列数，不要目测。

## 导入后

- 管理后台 → 题目导入：CSV 走选择题入口，XML 走 FPS 入口
- 导入后到「问题管理」把题目状态切为「启用」，否则普通用户看不到
