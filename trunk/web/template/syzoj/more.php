<?php require("header.php"); ?>

<style>
/* 更多功能页面样式 */
.more-page {
    padding: 40px 0;
    max-width: 1200px;
    margin: 0 auto;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
    text-align: center;
}

.page-subtitle {
    font-size: 1.1rem;
    color: #666;
    text-align: center;
    margin-bottom: 50px;
}

.section {
    margin-bottom: 50px;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 10px;
    border-bottom: 2px solid #667eea;
    display: flex;
    align-items: center;
    gap: 10px;
}

.auth-tag {
    font-size: 0.85rem;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: normal;
}

.tag-public {
    background-color: #dcfce7;
    color: #166534;
}

.tag-private {
    background-color: #fef3c7;
    color: #92400e;
}

.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
}

.card {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid #e8e8e8;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
    border-color: #667eea;
    text-decoration: none;
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
}

.card-icon svg {
    width: 34px;
    height: 34px;
    display: block;
}

.card-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.card-desc {
    font-size: 0.95rem;
    color: #666;
    line-height: 1.5;
}

/* 响应式调整 */
@media (max-width: 768px) {
    .more-page {
        padding: 20px 15px;
    }

    .page-title {
        font-size: 2rem;
    }

    .cards-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
}
</style>

<div class="more-page">
    <h1 class="page-title">更多功能</h1>
    <p class="page-subtitle">探索平台提供的丰富功能与工具</p>

    <!-- 教师服务 -->
    <div class="section">
        <h2 class="section-title">👨‍🏫 教师服务 <span class="auth-tag tag-public">无需登录</span></h2>
        <div class="cards-grid">
            <a href="teacher_guide.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #6366f1, #8b5cf6);">
                    <svg viewBox="0 0 64 64">
                        <path d="M32 10 L8 22 L32 34 L56 22 Z" fill="#fff"/>
                        <rect x="12" y="33" width="40" height="5" rx="2.5" fill="#fff"/>
                        <circle cx="32" cy="40" r="6" fill="#fff"/>
                        <path d="M50 22 v10 a5 5 0 0 1 -5 5" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round"/>
                        <circle cx="43" cy="39" r="2.5" fill="#fff"/>
                    </svg>
                </div>
                <div class="card-title">学生账号批量开通</div>
                <div class="card-desc">教师专属通道：联系客服QQ，一次开通全班学生账号（适合班级/年级统一使用）</div>
            </a>
        </div>
    </div>

    <!-- AI训练 -->
    <div class="section">
        <h2 class="section-title">🤖 AI训练 <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="AI_training.php?type=image" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #06b6d4, #3b82f6);">
                    <svg viewBox="0 0 64 64">
                        <rect x="12" y="12" width="40" height="40" rx="7" fill="#fff"/>
                        <circle cx="22" cy="24" r="4" fill="rgba(0,0,0,0.28)"/>
                        <path d="M16 44 l12 -12 7 7 6 -6 9 11 z" fill="rgba(0,0,0,0.28)"/>
                    </svg>
                </div>
                <div class="card-title">图像分类</div>
                <div class="card-desc">训练AI模型识别不同类别的图像内容</div>
            </a>

            <a href="AI_training.php?type=handpose" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f97316, #f43f5e);">
                    <svg viewBox="0 0 64 64">
                        <rect x="14" y="20" width="8" height="24" rx="4" fill="#fff"/>
                        <rect x="24" y="14" width="8" height="30" rx="4" fill="#fff"/>
                        <rect x="34" y="12" width="8" height="32" rx="4" fill="#fff"/>
                        <rect x="44" y="20" width="8" height="24" rx="4" fill="#fff"/>
                        <path d="M14 44 h38 v2 a9 9 0 0 1 -9 9 h-20 a9 9 0 0 1 -9 -9 z" fill="#fff"/>
                        <ellipse cx="10" cy="38" rx="4" ry="9" fill="#fff" transform="rotate(-25 10 38)"/>
                    </svg>
                </div>
                <div class="card-title">手势分类</div>
                <div class="card-desc">训练AI模型识别各种手势动作</div>
            </a>

            <!-- 三方API已下线，暂时隐藏：语音分类、图像识别、手势识别 -->
            <!--
            <a href="AI_training.php?type=audio" class="card">
                <div class="card-icon">
                    <i class="microphone icon"></i>
                </div>
                <div class="card-title">语音分类</div>
                <div class="card-desc">训练AI模型识别不同的语音特征</div>
            </a>

            <a href="AI_training.php?type=recognition" class="card">
                <div class="card-icon">
                    <i class="eye icon"></i>
                </div>
                <div class="card-title">图像识别</div>
                <div class="card-desc">体验先进的图像识别与分析技术</div>
            </a>

            <a href="AI_training.php?type=gesture" class="card">
                <div class="card-icon">
                    <i class="hand rock icon"></i>
                </div>
                <div class="card-title">手势识别</div>
                <div class="card-desc">实时识别手势动作，体验交互乐趣</div>
            </a>
            -->
        </div>
    </div>

    <!-- AI体验 -->
    <div class="section">
        <h2 class="section-title">✨ AI体验 <span class="auth-tag tag-public">无需登录</span></h2>
        <div class="cards-grid">
            <a href="javascript:openAIExperience()" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #8b5cf6, #d946ef);">
                    <svg viewBox="0 0 64 64">
                        <rect x="30" y="6" width="4" height="12" fill="#fff"/>
                        <circle cx="32" cy="4" r="3" fill="#fff"/>
                        <rect x="13" y="18" width="38" height="32" rx="10" fill="#fff"/>
                        <circle cx="25" cy="32" r="4.5" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="39" cy="32" r="4.5" fill="rgba(0,0,0,0.35)"/>
                        <rect x="25" y="43" width="14" height="3.5" rx="1.75" fill="rgba(0,0,0,0.35)"/>
                    </svg>
                </div>
                <div class="card-title">AI进阶</div>
                <div class="card-desc">体验前沿大语言模型的强大能力</div>
            </a>

            <a href="ai_drawing_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #ec4899, #f43f5e);">
                    <svg viewBox="0 0 64 64">
                        <rect x="26" y="14" width="12" height="30" rx="5" fill="#fff"/>
                        <path d="M26 16 l-4 -4 a10 10 0 0 1 12 -12 l4 4 a8 8 0 0 1 -4 8 q-4 4 -8 4 z" fill="#fff"/>
                        <path d="M50 8 l2.5 5 5 2.5 -5 2.5 -2.5 5 -2.5 -5 -5 -2.5 5 -2.5 z" fill="#fff"/>
                    </svg>
                </div>
                <div class="card-title">AI猜猜画</div>
                <div class="card-desc">画图让AI识别，认识人工智能（适合1-6年级）</div>
            </a>
        </div>
    </div>

    <!-- 低年级专区（1-3年级） -->
    <div class="section">
        <h2 class="section-title">🎒 低年级专区（1-3年级） <span class="auth-tag tag-public">无需登录</span></h2>
        <div class="cards-grid">
            <a href="color_match.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f59e0b, #ef4444);">
                    <svg viewBox="0 0 64 64">
                        <path d="M32 8 a24 24 0 1 0 24 24 l-8 0 a8 8 0 0 1 -8 -8 a8 8 0 0 0 -8 -8 h-2 a10 10 0 0 0 2 -8 z" fill="#fff"/>
                        <circle cx="20" cy="20" r="3" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="34" cy="14" r="3" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="44" cy="26" r="3" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="18" cy="36" r="3" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="40" cy="44" r="4" fill="rgba(255,255,255,0.5)"/>
                    </svg>
                </div>
                <div class="card-title">颜色匹配</div>
                <div class="card-desc">认识颜色，点击正确的颜色名称（适合1-2年级）</div>
            </a>

            <a href="clock_reading.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                    <svg viewBox="0 0 64 64">
                        <circle cx="32" cy="32" r="23" fill="#fff"/>
                        <circle cx="32" cy="32" r="18" fill="none" stroke="rgba(0,0,0,0.12)" stroke-width="2"/>
                        <path d="M32 14 v18 l11 8" stroke="rgba(0,0,0,0.5)" stroke-width="4.5" stroke-linecap="round" fill="none"/>
                        <circle cx="32" cy="32" r="3.5" fill="rgba(0,0,0,0.5)"/>
                    </svg>
                </div>
                <div class="card-title">时钟认读</div>
                <div class="card-desc">学习看时钟，认识整点和半点（适合1-2年级）</div>
            </a>

            <a href="guess_number.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <svg viewBox="0 0 64 64">
                        <circle cx="30" cy="28" r="19" fill="#fff"/>
                        <text x="30" y="38" font-family="Arial, sans-serif" font-size="27" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.5)">?</text>
                        <rect x="42" y="42" width="16" height="16" rx="3" fill="rgba(0,0,0,0.25)"/>
                        <circle cx="46" cy="46" r="1.8" fill="#fff"/>
                        <circle cx="54" cy="54" r="1.8" fill="#fff"/>
                        <circle cx="54" cy="46" r="1.8" fill="#fff"/>
                        <circle cx="46" cy="54" r="1.8" fill="#fff"/>
                    </svg>
                </div>
                <div class="card-title">猜数字</div>
                <div class="card-desc">动动脑筋，猜出隐藏的神秘数字（适合1-3年级）</div>
            </a>

            <a href="memory_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #6366f1, #06b6d4);">
                    <svg viewBox="0 0 64 64">
                        <rect x="10" y="16" width="24" height="32" rx="5" fill="#fff" transform="rotate(-8 22 32)"/>
                        <rect x="32" y="20" width="24" height="32" rx="5" fill="#fff" transform="rotate(7 44 36)"/>
                        <path d="M39 34 l3 4 5 1 -4 4 1 5 -5 -3 -5 3 1 -5 -4 -4 5 -1 z" fill="rgba(0,0,0,0.3)"/>
                    </svg>
                </div>
                <div class="card-title">卡片配对</div>
                <div class="card-desc">翻开卡片，找出相同的图案配对（适合1-3年级）</div>
            </a>

            <a href="sequence_memory.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #a855f7, #ec4899);">
                    <svg viewBox="0 0 64 64">
                        <path d="M8 44 h44" stroke="#fff" stroke-width="4" stroke-linecap="round"/>
                        <circle cx="16" cy="44" r="5" fill="#fff"/>
                        <circle cx="32" cy="24" r="5" fill="#fff"/>
                        <circle cx="48" cy="44" r="5" fill="#fff"/>
                        <path d="M32 29 v15" stroke="#fff" stroke-width="3"/>
                    </svg>
                </div>
                <div class="card-title">序列记忆</div>
                <div class="card-desc">记住颜色顺序并重复，挑战你的记忆力（适合1-4年级）</div>
            </a>

            <a href="math_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f97316, #dc2626);">
                    <svg viewBox="0 0 64 64">
                        <rect x="15" y="8" width="34" height="48" rx="7" fill="#fff"/>
                        <rect x="19" y="13" width="26" height="11" rx="2.5" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="23" cy="34" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="32" cy="34" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="41" cy="34" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="23" cy="45" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="32" cy="45" r="3" fill="rgba(0,0,0,0.35)"/>
                        <circle cx="41" cy="45" r="3" fill="rgba(0,0,0,0.35)"/>
                    </svg>
                </div>
                <div class="card-title">数学闯关</div>
                <div class="card-desc">挑战数学题，闯过一关又一关（适合2-4年级）</div>
            </a>

            <a href="puzzle_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #eab308, #f97316);">
                    <svg viewBox="0 0 64 64">
                        <rect x="10" y="10" width="26" height="26" rx="6" fill="#fff"/>
                        <circle cx="36" cy="23" r="6.5" fill="#fff"/>
                        <rect x="28" y="28" width="26" height="26" rx="6" fill="#fff"/>
                        <circle cx="36" cy="41" r="5.5" fill="rgba(0,0,0,0.3)"/>
                    </svg>
                </div>
                <div class="card-title">拼图游戏</div>
                <div class="card-desc">拖动拼图碎片还原图片，训练观察力与空间思维（适合1-3年级）</div>
            </a>

        </div>
    </div>

    <!-- 高年级专区（4-6年级） -->
    <div class="section">
        <h2 class="section-title">📚 高年级专区（4-6年级） <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="snake.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                    <svg viewBox="0 0 64 64">
                        <path d="M12 46 q10 -16 20 -4 q10 12 20 -6" stroke="#fff" stroke-width="7" fill="none" stroke-linecap="round"/>
                        <circle cx="52" cy="36" r="6.5" fill="#fff"/>
                        <circle cx="49.5" cy="34" r="2.2" fill="rgba(0,0,0,0.45)"/>
                        <path d="M58 33 l5 -4" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="18" cy="22" r="5.5" fill="#fff"/>
                        <path d="M15 17 q2 -4 6 -2" stroke="#fff" stroke-width="2" fill="none" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="card-title">贪吃蛇</div>
                <div class="card-desc">控制小蛇吃食物变长，不要撞墙（适合3-6年级）</div>
            </a>

            <a href="number_puzzle.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #14b8a6, #0891b2);">
                    <svg viewBox="0 0 64 64">
                        <rect x="10" y="10" width="44" height="44" rx="7" fill="#fff"/>
                        <path d="M10 24.7 h44 M10 39.3 h44 M24.7 10 v44 M39.3 10 v44" stroke="rgba(0,0,0,0.12)" stroke-width="2"/>
                        <text x="17.3" y="20.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">1</text>
                        <text x="32" y="20.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">2</text>
                        <text x="46.7" y="20.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">3</text>
                        <text x="17.3" y="35.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">4</text>
                        <text x="32" y="35.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">5</text>
                        <text x="46.7" y="35.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">6</text>
                        <text x="17.3" y="50.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">7</text>
                        <text x="32" y="50.5" font-family="Arial, sans-serif" font-size="11" font-weight="700" text-anchor="middle" fill="rgba(0,0,0,0.55)">8</text>
                    </svg>
                </div>
                <div class="card-title">数字华容道</div>
                <div class="card-desc">滑动数字块，按1-15顺序排列（适合3-6年级）</div>
            </a>

            <a href="idiom_chain.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #d97706, #b45309);">
                    <svg viewBox="0 0 64 64">
                        <path d="M32 16 L10 12 v36 l22 4 z" fill="#fff"/>
                        <path d="M32 16 L54 12 v36 l-22 4 z" fill="#fff"/>
                        <path d="M32 16 v36" stroke="rgba(0,0,0,0.18)" stroke-width="1.5"/>
                        <path d="M14 18 h12 M14 24 h12 M14 30 h10" stroke="rgba(0,0,0,0.3)" stroke-width="2" stroke-linecap="round"/>
                        <path d="M38 18 h12 M38 24 h12 M40 30 h10" stroke="rgba(0,0,0,0.3)" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="card-title">成语接龙</div>
                <div class="card-desc">60秒限时挑战，成语知识大比拼（适合3-6年级）</div>
            </a>

            <a href="minesweeper.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #6b7280, #374151);">
                    <svg viewBox="0 0 64 64">
                        <g stroke="#fff" stroke-width="4" stroke-linecap="round">
                            <line x1="32" y1="8" x2="32" y2="17"/>
                            <line x1="32" y1="47" x2="32" y2="56"/>
                            <line x1="8" y1="32" x2="17" y2="32"/>
                            <line x1="47" y1="32" x2="56" y2="32"/>
                            <line x1="15" y1="15" x2="22" y2="22"/>
                            <line x1="42" y1="42" x2="49" y2="49"/>
                            <line x1="15" y1="49" x2="22" y2="42"/>
                            <line x1="42" y1="22" x2="49" y2="15"/>
                        </g>
                        <circle cx="32" cy="32" r="12" fill="#fff"/>
                        <circle cx="32" cy="32" r="4.5" fill="rgba(0,0,0,0.5)"/>
                    </svg>
                </div>
                <div class="card-title">扫雷</div>
                <div class="card-desc">经典扫雷，找出所有隐藏的地雷（适合4-6年级）</div>
            </a>
        </div>
    </div>

    <!-- 打字练习 -->
    <div class="section">
        <h2 class="section-title">⌨️ 打字练习 <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="keyboard_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    <svg viewBox="0 0 64 64">
                        <rect x="8" y="16" width="48" height="33" rx="6" fill="#fff"/>
                        <g fill="rgba(0,0,0,0.3)">
                            <rect x="12" y="20" width="8" height="6" rx="1.5"/>
                            <rect x="23" y="20" width="8" height="6" rx="1.5"/>
                            <rect x="34" y="20" width="8" height="6" rx="1.5"/>
                            <rect x="45" y="20" width="8" height="6" rx="1.5"/>
                            <rect x="12" y="29" width="8" height="6" rx="1.5"/>
                            <rect x="23" y="29" width="8" height="6" rx="1.5"/>
                            <rect x="34" y="29" width="8" height="6" rx="1.5"/>
                            <rect x="45" y="29" width="8" height="6" rx="1.5"/>
                            <rect x="12" y="38" width="20" height="6" rx="1.5"/>
                            <rect x="35" y="38" width="18" height="6" rx="1.5"/>
                        </g>
                    </svg>
                </div>
                <div class="card-title">打字游戏</div>
                <div class="card-desc">在游戏中提升打字速度与准确率（适合3-6年级）</div>
            </a>

            <a href="balloon_typing.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f43f5e, #db2777);">
                    <svg viewBox="0 0 64 64">
                        <ellipse cx="32" cy="27" rx="16" ry="19" fill="#fff"/>
                        <path d="M32 45 l-3.5 4.5 h7 z" fill="#fff"/>
                        <path d="M32 50 q-3 12 -9 14" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <path d="M27 13 q3 -5 6 0" stroke="rgba(0,0,0,0.3)" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        <ellipse cx="26" cy="21" rx="4" ry="7" fill="rgba(255,255,255,0.6)" transform="rotate(18 26 21)"/>
                    </svg>
                </div>
                <div class="card-title">气球打字</div>
                <div class="card-desc">打字击破上升的气球，练习键盘输入（适合3-6年级）</div>
            </a>

            <a href="frog_typing.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #22c55e, #15803d);">
                    <svg viewBox="0 0 64 64">
                        <ellipse cx="15" cy="46" rx="8" ry="6" fill="#fff"/>
                        <ellipse cx="49" cy="46" rx="8" ry="6" fill="#fff"/>
                        <ellipse cx="32" cy="39" rx="17" ry="14" fill="#fff"/>
                        <ellipse cx="32" cy="43" rx="10" ry="8" fill="rgba(0,0,0,0.15)"/>
                        <circle cx="23" cy="24" r="8" fill="#fff"/>
                        <circle cx="41" cy="24" r="8" fill="#fff"/>
                        <circle cx="24" cy="25" r="3" fill="rgba(0,0,0,0.5)"/>
                        <circle cx="42" cy="25" r="3" fill="rgba(0,0,0,0.5)"/>
                        <path d="M26 44 q6 5 12 0" stroke="rgba(0,0,0,0.35)" stroke-width="2" fill="none" stroke-linecap="round"/>
                        <circle cx="18" cy="42" r="2.5" fill="rgba(0,0,0,0.2)"/>
                        <circle cx="46" cy="42" r="2.5" fill="rgba(0,0,0,0.2)"/>
                    </svg>
                </div>
                <div class="card-title">青蛙过河</div>
                <div class="card-desc">打对石头上的词让青蛙过河，闯关收集星星解锁关卡（适合3-6年级）</div>
            </a>
        </div>
    </div>

    <!-- 编程学习 -->
    <div class="section">
        <h2 class="section-title">💻 编程学习 <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="coding_game.php" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                    <svg viewBox="0 0 64 64">
                        <path d="M19 16 L7 32 L19 48" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M45 16 L57 32 L45 48" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M37 13 L27 51" stroke="#fff" stroke-width="5" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="card-title">编程启蒙</div>
                <div class="card-desc">拖拽积木学习编程，控制小猫走迷宫（适合3-6年级）</div>
            </a>

            <a href="https://turbowarp.org/editor" target="_blank" class="card">
                <div class="card-icon" style="background: linear-gradient(135deg, #f97316, #ea580c);">
                    <svg viewBox="0 0 64 64">
                        <circle cx="27" cy="34" r="15" fill="#fff"/>
                        <path d="M15 26 L8 12 l14 6 z" fill="#fff"/>
                        <path d="M39 26 l7 -14 -14 6 z" fill="#fff"/>
                        <circle cx="22" cy="33" r="4" fill="rgba(0,0,0,0.45)"/>
                        <circle cx="32" cy="33" r="4" fill="rgba(0,0,0,0.45)"/>
                        <path d="M27 42 q5 5 10 0" stroke="rgba(0,0,0,0.35)" stroke-width="2" fill="none" stroke-linecap="round"/>
                        <circle cx="24" cy="38" r="1.6" fill="rgba(0,0,0,0.3)"/>
                        <circle cx="30" cy="38" r="1.6" fill="rgba(0,0,0,0.3)"/>
                        <path d="M12 48 l-5 3 M42 48 l5 3" stroke="rgba(0,0,0,0.3)" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="card-title">Scratch在线编程</div>
                <div class="card-desc">在线Scratch编辑器，轻松创作作品（适合3-6年级）</div>
            </a>

            <!-- Scratch案例暂未上线，暂时隐藏 -->
            <!--
            <a href="scratch.php" class="card">
                <div class="card-icon">
                    <i class="code icon"></i>
                </div>
                <div class="card-title">Scratch案例</div>
                <div class="card-desc">丰富的Scratch编程案例学习</div>
            </a>
            -->
        </div>
    </div>
</div>

<script>
// 复现header中的openAIExperience函数
function openAIExperience() {
    window.open('https://yiyan.baidu.com/', '_blank');
}
</script>

<?php require("footer.php"); ?>