<?php
if (!isset($show_title) || $show_title === '') $show_title = $MSG_REG_INFO . ' - ' . $OJ_NAME;
include("template/$OJ_TEMPLATE/header.php");
?>
<link rel="stylesheet" href="<?php echo $OJ_CDN_URL.$path_fix."template/$OJ_TEMPLATE/fonts/zcool-kuaile.css?v=1"?>">
<style>
/* ===== 修改资料页 · 与顶部天空蓝菜单统一的贴纸卡片风格 ===== */
.modify-page {
    --m-ink: #3d4f6b;
    --m-ink-soft: #7a8ba3;
    --m-sky: #67a0d5;
    --m-sky-deep: #5b90cd;
    --m-sky-dark: #3a6da8;
    --m-sky-light: #78b0d9;
    --m-cream: #fff8dd;
    --m-gold: #f4cd75;
    --m-coral: #e2726d;
    --m-shadow: rgba(61, 79, 107, 0.16);

    max-width: 720px;
    margin: 0 auto 24px;
    padding: 22px 14px 30px;
    color: var(--m-ink);
    font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", "Helvetica Neue", sans-serif;
}

@keyframes m-pop {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}

/* === 顶部横幅（菜单同款天空蓝渐变） === */
.modify-banner {
    position: relative;
    text-align: center;
    padding: 26px 18px 22px;
    background: linear-gradient(100deg, var(--m-sky-deep) 0%, var(--m-sky) 52%, var(--m-sky-light) 100%);
    border: 2.5px solid var(--m-ink);
    border-radius: 20px;
    box-shadow: 4px 4px 0 var(--m-shadow);
    overflow: hidden;
    animation: m-pop 0.45s ease both;
    margin-bottom: 18px;
}
.modify-banner::before,
.modify-banner::after {
    content: '✦';
    position: absolute;
    color: var(--m-cream);
    pointer-events: none;
}
.modify-banner::before { top: 12px; left: 24px; font-size: 1.1rem; opacity: 0.7; }
.modify-banner::after { bottom: 14px; right: 26px; font-size: 0.85rem; opacity: 0.55; }
.modify-banner-title {
    font-family: 'ZCOOL KuaiLe', -apple-system, sans-serif;
    font-weight: 400;
    color: #fff;
    font-size: 2rem;
    margin: 0 0 6px;
    text-shadow: 0 2px 0 rgba(0, 0, 0, 0.12);
    letter-spacing: 1px;
}
.modify-banner-sub {
    color: rgba(255, 255, 255, 0.92);
    font-size: 0.88rem;
    margin: 0;
}

/* === 表单卡片 === */
.modify-card {
    background: #fff;
    border: 2.5px solid var(--m-ink);
    border-radius: 20px;
    box-shadow: 4px 4px 0 var(--m-shadow);
    padding: 22px 24px 24px;
    animation: m-pop 0.45s ease 0.08s both;
}

/* 错误提示占位（保留原 ID，默认隐藏） */
#error.ui.error.message {
    margin: 0 0 16px;
    border-radius: 12px;
}

/* === 表单元素 === */
.modify-page .ui.form .field { margin: 0 0 16px; }
.modify-page .ui.form .field > label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--m-ink);
    margin: 0 0 7px;
    letter-spacing: 0.3px;
}
.modify-page .ui.form .field > label .req { color: var(--m-coral); }
.modify-page .ui.form .field > label .opt {
    font-weight: 500;
    color: var(--m-ink-soft);
    font-size: 0.78rem;
}
.modify-page input[type="text"],
.modify-page input[type="password"],
.modify-page input:not([type]),
.modify-page select {
    width: 100%;
    box-sizing: border-box;
    border: 1.8px solid #cdd8e4 !important;
    border-radius: 12px !important;
    padding: 11px 14px !important;
    font-size: 0.95rem;
    line-height: 1.4;
    height: auto !important;
    color: var(--m-ink) !important;
    background: #fbfdff !important;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    box-shadow: none !important;
    outline: none;
}
.modify-page input::placeholder { color: #a5b3c4; }
.modify-page input:focus,
.modify-page select:focus {
    border-color: var(--m-sky) !important;
    background: #fff !important;
    box-shadow: 0 0 0 3px rgba(103, 160, 213, 0.18) !important;
}
.modify-page input:disabled {
    background: #eef2f7 !important;
    color: var(--m-ink-soft) !important;
    border-color: #dde5ee !important;
    cursor: not-allowed;
}

/* 双列密码字段 */
.modify-page .two.fields { margin: 0; gap: 14px; }
.modify-page .two.fields .field { margin-bottom: 16px; }

/* 下拉选择（保持原生 select，自定义箭头） */
.modify-page select.ui.dropdown {
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a8ba3' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 14px center !important;
    padding-right: 38px !important;
    cursor: pointer;
}

/* 验证码 */
.vcode-row {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.vcode-row input { flex: 1; min-width: 150px; }
.vcode-img {
    height: 42px;
    border-radius: 10px;
    border: 1.8px solid #cdd8e4;
    cursor: pointer;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
}
.vcode-img:hover {
    transform: translateY(-2px);
    box-shadow: 0 3px 8px rgba(61, 79, 107, 0.18);
}

/* === 按钮组 === */
.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 6px;
}
.modify-page .form-actions .ui.button {
    flex: 1;
    border-radius: 999px !important;
    padding: 12px 20px !important;
    font-size: 0.98rem;
    font-weight: 700;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    margin: 0;
}
.btn-submit {
    color: #fff !important;
    background: linear-gradient(135deg, var(--m-sky), var(--m-sky-dark)) !important;
    border: 2px solid var(--m-sky-dark) !important;
    box-shadow: 3px 3px 0 rgba(58, 109, 168, 0.35) !important;
}
.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 4px 5px 0 rgba(58, 109, 168, 0.4) !important;
}
.btn-reset {
    color: var(--m-sky-dark) !important;
    background: #F0F6FC !important;
    border: 2px solid var(--m-sky-deep) !important;
}
.btn-reset:hover {
    background: #dcebf8 !important;
    transform: translateY(-2px);
}

/* === MyOJ 次级卡片 === */
.myoj-card {
    margin-top: 18px;
    background: #fff;
    border: 2.5px solid var(--m-ink);
    border-radius: 20px;
    box-shadow: 4px 4px 0 var(--m-shadow);
    padding: 20px 24px 22px;
    animation: m-pop 0.45s ease 0.16s both;
}
.myoj-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}
.myoj-badge {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f7d98e, #e8a83c);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    flex: none;
}
.myoj-title {
    font-family: 'ZCOOL KuaiLe', sans-serif;
    font-weight: 400;
    font-size: 1.3rem;
    margin: 0;
}
.myoj-card .ui.button {
    border-radius: 999px !important;
    padding: 10px 26px !important;
    font-weight: 700;
    color: #fff !important;
    background: linear-gradient(135deg, var(--m-sky), var(--m-sky-dark)) !important;
    border: 2px solid var(--m-sky-dark) !important;
}
.myoj-card .ui.button:hover { transform: translateY(-2px); }

/* === 移动端适配 === */
@media (max-width: 640px) {
    .modify-page { padding: 16px 10px 26px; }
    .modify-banner { padding: 22px 14px 18px; border-radius: 16px; }
    .modify-banner-title { font-size: 1.65rem; }
    .modify-banner-sub { font-size: 0.8rem; }
    .modify-card { padding: 18px 15px 20px; border-radius: 16px; }
    .myoj-card { padding: 16px 15px 18px; border-radius: 16px; }
    .two.fields { flex-direction: column; gap: 0; }
    .two.fields .field:last-child { margin-bottom: 16px; }
    .form-actions { flex-direction: column-reverse; gap: 10px; }
    .modify-page input[type="text"],
    .modify-page input[type="password"],
    .modify-page input:not([type]),
    .modify-page select {
        font-size: 16px;       /* 防止 iOS 聚焦缩放 */
        min-height: 48px;      /* 保证大字号下文字完整显示不被裁切 */
        padding-top: 10px !important;
        padding-bottom: 10px !important;
    }
    /* 修复部分移动端内核（X5/旧 WebKit）按最长校名撑开 select 导致右侧超出屏幕 */
    .modify-page form,
    .modify-page .field { max-width: 100%; min-width: 0; }
    .modify-page select {
        display: block;
        width: 100%;
        max-width: 100% !important;
        min-width: 0 !important;
    }
}
</style>

<div class="modify-page">

    <div class="modify-banner">
        <h1 class="modify-banner-title"><?php echo $MSG_REG_INFO ?></h1>
        <p class="modify-banner-sub">完善个人资料，让老师和同学更容易找到你</p>
    </div>

    <div class="modify-card">
        <div class="ui error message" id="error" data-am-alert hidden>
            <p id="error_info"></p>
        </div>
        <form action="modify.php" method="post" role="form" class="ui form">
                <div class="field">
                    <label for="username"><?php echo $MSG_USER_ID?></label>
                    <input class="form-control" placeholder="<?php echo $MSG_Input.$MSG_USER_ID?>"  disabled="disabled" type="text" value="<?php echo $_SESSION[$OJ_NAME.'_'.'user_id']?>">
                </div>
                <?php require_once('./include/set_post_key.php');?>
                <div class="field">
                    <label for="username"><?php echo $MSG_NICK?> <span class="req">*</span></label>
                    <input name="nick" placeholder="<?php echo $MSG_Input.$MSG_NICK?>" type="text" value="<?php echo htmlentities($row['nick'],ENT_QUOTES,"UTF-8")?>">
                </div>
                <div class="field">
                    <label class="ui header">当前密码 <span class="req">*</span><span class="opt">（验证身份，请输入正在使用的密码）</span></label>
                      <input name="opassword" placeholder="请输入当前密码" type="password">
                    </div>
                <div class="two fields">
                    <div class="field">
                    <label class="ui header">新密码 <span class="opt">（留空则不修改密码）</span></label>
                      <input name="npassword" placeholder="请输入新密码，留空则不修改" type="password">
                    </div>
                    <div class="field">
                      <label class="ui header">确认新密码 <span class="opt">（再次输入新密码）</span></label>
                      <input name="rptpassword" placeholder="请再次输入新密码" type="password">
                    </div>
                </div>
                <div class="field">
                    <label for="school_id"><?php echo $MSG_SCHOOL?></label>
                    <?php if (!empty($school_list)): ?>
                    <select name="school_id" class="ui dropdown">
                        <option value=""><?php echo $MSG_SCHOOL?></option>
                        <?php foreach ($school_list as $school): ?>
                        <option value="<?php echo $school['id'] ?>" <?php if (isset($row['school_id']) && $row['school_id'] == $school['id']) echo 'selected'; ?>>
                            <?php echo htmlentities($school['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php else: ?>
                    <input name="school" placeholder="<?php echo $MSG_SCHOOL?>" type="text" value="<?php echo htmlentities($row['school'],ENT_QUOTES,"UTF-8")?>">
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="email"><?php echo $MSG_EMAIL?> <span class="req">*</span></label>
                    <input name="email" placeholder="<?php echo $MSG_EMAIL?>" type="text" value="<?php echo htmlentities($row['email'],ENT_QUOTES,"UTF-8")?>">
                </div>
                <?php if($OJ_VCODE){?>
                  <div class="field">
                    <label for="email"><?php echo $MSG_VCODE?> <span class="req">*</span></label>
                    <div class="vcode-row">
                        <input name="vcode" class="form-control" placeholder="<?php echo $MSG_VCODE?>" type="text" autocomplete=off >
                        <img class="vcode-img" alt="click to change" src="vcode.php" onclick="this.src='vcode.php?'+Math.random()" height="30px">
                    </div>
                  </div>
                <?php }?>
                <div class="form-actions">
                    <button name="submit" type="reset" class="ui button btn-reset"><?php echo $MSG_RESET; ?></button>
                    <button name="submit" type="submit" class="ui button btn-submit"><?php echo $MSG_SUBMIT; ?></button>
                </div>
            </form>
    </div>

<?php if ($OJ_SaaS_ENABLE && $domain==$DOMAIN){ ?>
    <div class="myoj-card">
        <a name='MyOJ'>&nbsp;</a>
        <div class="myoj-head">
            <span class="myoj-badge"><i class="globe icon"></i></span>
            <h2 class="myoj-title">My OJ</h2>
        </div>
        <form action="saasinit.php" method="post" role="form" class="ui form">
                <div class="field">
                    <label for="template"><?php echo $MSG_TEMPLATE ?></label>
                    <select name="template" class="form-control" >
                                <option>bs3</option>
                                <option>mdui</option>
                                <option>syzoj</option>
                                <option>sweet</option>
                                <option>bshark</option>
                                <option>sidebar</option>
                    </select>
                </div>

                <div class="field">
                    <label for="friendly"><?php echo $MSG_FRIENDLY_LEVEL ?></label>
                    <select name="friendly" class="form-control" >
                                <option value=0>0=<?php echo   $MSG_FRIENDLY_L0 ?></option>
                                <option value=1>1=0+<?php echo   $MSG_FRIENDLY_L1 ?></option>
                                <option value=2>2=1+<?php echo   $MSG_FRIENDLY_L2 ?></option>
                                <option value=3>3=2+<?php echo   $MSG_FRIENDLY_L3 ?></option>
                                <option value=4>4=3+<?php echo   $MSG_FRIENDLY_L4 ?></option>
                                <option value=5>5=4+<?php echo   $MSG_FRIENDLY_L5 ?></option>
                                <option value=6>6=5+<?php echo   $MSG_FRIENDLY_L6 ?></option>
                                <option value=7>7=6+<?php echo   $MSG_FRIENDLY_L7 ?></option>
                                <option value=8>8=7+<?php echo   $MSG_FRIENDLY_L8 ?></option>
                                <option value=9>9=8+<?php echo   $MSG_FRIENDLY_L9 ?></option>
                    </select>
                </div>
                <button name="submit" type="submit" class="ui button">重新初始化</button>
            </form>
    </div>
<?php } ?>

</div>

<?php include("template/$OJ_TEMPLATE/footer.php");?>
