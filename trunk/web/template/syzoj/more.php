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

    <!-- 核心功能 -->
    <div class="section">
        <h2 class="section-title">核心功能</h2>
        <div class="cards-grid">
            <a href="problemset.php" class="card">
                <div class="card-icon">
                    <i class="book icon"></i>
                </div>
                <div class="card-title">题库练习</div>
                <div class="card-desc">海量编程题目，从入门到竞赛全涵盖</div>
            </a>

            <a href="status.php" class="card">
                <div class="card-icon">
                    <i class="list icon"></i>
                </div>
                <div class="card-title">评测状态</div>
                <div class="card-desc">实时查看提交记录与评测结果</div>
            </a>

            <a href="contest.php" class="card">
                <div class="card-icon">
                    <i class="trophy icon"></i>
                </div>
                <div class="card-title">竞赛活动</div>
                <div class="card-desc">参加各类编程竞赛，挑战自我</div>
            </a>

            <a href="ranklist.php" class="card">
                <div class="card-icon">
                    <i class="chart bar icon"></i>
                </div>
                <div class="card-title">排行榜</div>
                <div class="card-desc">查看用户排名与解题统计</div>
            </a>

            <a href="category.php" class="card">
                <div class="card-icon">
                    <i class="tags icon"></i>
                </div>
                <div class="card-title">题目分类</div>
                <div class="card-desc">按知识点分类浏览题目</div>
            </a>

            <a href="discuss.php" class="card">
                <div class="card-icon">
                    <i class="comments icon"></i>
                </div>
                <div class="card-title">讨论区</div>
                <div class="card-desc">与其他用户交流解题思路</div>
            </a>
        </div>
    </div>

    <!-- AI训练分类 -->
    <div class="section">
        <h2 class="section-title">AI训练</h2>
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
        </div>
    </div>

    <!-- AI体验 -->
    <div class="section">
        <h2 class="section-title">AI体验</h2>
        <div class="cards-grid">
            <a href="javascript:openAIExperience()" class="card">
                <div class="card-icon">
                    <i class="robot icon"></i>
                </div>
                <div class="card-title">AI进阶</div>
                <div class="card-desc">体验前沿大语言模型的强大能力</div>
            </a>
        </div>
    </div>

    <!-- 趣味工具 -->
    <div class="section">
        <h2 class="section-title">趣味工具</h2>
        <div class="cards-grid">
            <a href="keyboard_game.php" class="card">
                <div class="card-icon">
                    <i class="keyboard icon"></i>
                </div>
                <div class="card-title">打字游戏</div>
                <div class="card-desc">在游戏中提升打字速度与准确率</div>
            </a>

            <a href="scratch.php" class="card">
                <div class="card-icon">
                    <i class="code icon"></i>
                </div>
                <div class="card-title">Scratch案例</div>
                <div class="card-desc">丰富的Scratch编程案例学习</div>
            </a>

            <a href="https://turbowarp.org/editor" target="_blank" class="card">
                <div class="card-icon">
                    <i class="pencil alternate icon"></i>
                </div>
                <div class="card-title">Scratch在线编程</div>
                <div class="card-desc">在线Scratch编辑器，轻松创作作品</div>
            </a>
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