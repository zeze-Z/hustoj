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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: #fff;
    font-size: 24px;
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

    <!-- AI训练 -->
    <div class="section">
        <h2 class="section-title">🤖 AI训练 <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="AI_training.php?type=image" class="card">
                <div class="card-icon">
                    <i class="image icon"></i>
                </div>
                <div class="card-title">图像分类</div>
                <div class="card-desc">训练AI模型识别不同类别的图像内容</div>
            </a>

            <a href="AI_training.php?type=handpose" class="card">
                <div class="card-icon">
                    <i class="hand paper icon"></i>
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
                <div class="card-icon">
                    <i class="robot icon"></i>
                </div>
                <div class="card-title">AI进阶</div>
                <div class="card-desc">体验前沿大语言模型的强大能力</div>
            </a>

            <a href="ai_drawing_game.php" class="card">
                <div class="card-icon">
                    <i class="paint brush icon"></i>
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
                <div class="card-icon">
                    <i class="palette icon"></i>
                </div>
                <div class="card-title">颜色匹配</div>
                <div class="card-desc">认识颜色，点击正确的颜色名称（适合1-2年级）</div>
            </a>

            <a href="clock_reading.php" class="card">
                <div class="card-icon">
                    <i class="clock icon"></i>
                </div>
                <div class="card-title">时钟认读</div>
                <div class="card-desc">学习看时钟，认识整点和半点（适合1-2年级）</div>
            </a>

            <a href="guess_number.php" class="card">
                <div class="card-icon">
                    <i class="question circle icon"></i>
                </div>
                <div class="card-title">猜数字</div>
                <div class="card-desc">动动脑筋，猜出隐藏的神秘数字（适合1-3年级）</div>
            </a>

            <a href="memory_game.php" class="card">
                <div class="card-icon">
                    <i class="th large icon"></i>
                </div>
                <div class="card-title">卡片配对</div>
                <div class="card-desc">翻开卡片，找出相同的图案配对（适合1-3年级）</div>
            </a>

            <a href="sequence_memory.php" class="card">
                <div class="card-icon">
                    <i class="brain icon"></i>
                </div>
                <div class="card-title">序列记忆</div>
                <div class="card-desc">记住颜色顺序并重复，挑战你的记忆力（适合1-4年级）</div>
            </a>

            <a href="math_game.php" class="card">
                <div class="card-icon">
                    <i class="calculator icon"></i>
                </div>
                <div class="card-title">数学闯关</div>
                <div class="card-desc">挑战数学题，闯过一关又一关（适合2-4年级）</div>
            </a>


        </div>
    </div>

    <!-- 高年级专区（4-6年级） -->
    <div class="section">
        <h2 class="section-title">📚 高年级专区（4-6年级） <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="snake.php" class="card">
                <div class="card-icon">
                    <i class="gamepad icon"></i>
                </div>
                <div class="card-title">贪吃蛇</div>
                <div class="card-desc">控制小蛇吃食物变长，不要撞墙（适合3-6年级）</div>
            </a>

            <a href="number_puzzle.php" class="card">
                <div class="card-icon">
                    <i class="sort numeric down icon"></i>
                </div>
                <div class="card-title">数字华容道</div>
                <div class="card-desc">滑动数字块，按1-15顺序排列（适合3-6年级）</div>
            </a>

            <a href="idiom_chain.php" class="card">
                <div class="card-icon">
                    <i class="book icon"></i>
                </div>
                <div class="card-title">成语接龙</div>
                <div class="card-desc">60秒限时挑战，成语知识大比拼（适合3-6年级）</div>
            </a>

            <a href="minesweeper.php" class="card">
                <div class="card-icon">
                    <i class="bomb icon"></i>
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
                <div class="card-icon">
                    <i class="keyboard icon"></i>
                </div>
                <div class="card-title">打字游戏</div>
                <div class="card-desc">在游戏中提升打字速度与准确率（适合3-6年级）</div>
            </a>

            <a href="balloon_typing.php" class="card">
                <div class="card-icon">
                    <i class="keyboard icon"></i>
                </div>
                <div class="card-title">气球打字</div>
                <div class="card-desc">打字击破上升的气球，练习键盘输入（适合3-6年级）</div>
            </a>
        </div>
    </div>

    <!-- 编程学习 -->
    <div class="section">
        <h2 class="section-title">💻 编程学习 <span class="auth-tag tag-private">需要登录</span></h2>
        <div class="cards-grid">
            <a href="coding_game.php" class="card">
                <div class="card-icon">
                    <i class="code icon"></i>
                </div>
                <div class="card-title">编程启蒙</div>
                <div class="card-desc">拖拽积木学习编程，控制小猫走迷宫（适合3-6年级）</div>
            </a>

            <a href="https://turbowarp.org/editor" target="_blank" class="card">
                <div class="card-icon">
                    <i class="pencil alternate icon"></i>
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