<?php
/**
 * 老师登录IP归属地统计 + 教师活跃度查询
 * 管理后台页面，展示老师登录IP的地理位置分布（柱状图）和活跃度统计（饼图）
 */
require("admin-header.php");
require_once("../include/set_get_key.php");
require_once("../include/school.php");

// 权限控制：仅管理员可访问
if (!isset($_SESSION[$OJ_NAME.'_'.'administrator'])) {
    echo "<a href='../loginpage.php'>Please Login First!</a>";
    exit(1);
}

if (isset($OJ_LANG)) {
    require_once("../lang/$OJ_LANG.php");
}

// 入参
$stat_type = isset($_GET['stat_type']) ? trim($_GET['stat_type']) : 'city';
$tab = isset($_GET['tab']) ? trim($_GET['tab']) : 'ip';
?>

<title>老师登录IP统计</title>
<hr>
<center><h3>老师统计</h3></center>

<!-- 标签页导航 -->
<ul class="nav nav-tabs" style="margin:10px 20px;">
    <li id="tabIp" class="<?php echo $tab === 'ip' ? 'active' : ''; ?>">
        <a href="teacher_ip_stats.php?tab=ip">IP归属地分布</a>
    </li>
    <li id="tabActivity" class="<?php echo $tab === 'activity' ? 'active' : ''; ?>">
        <a href="teacher_ip_stats.php?tab=activity">教师活跃度</a>
    </li>
</ul>

<div style="margin:10px 20px;">
    <!-- IP归属地分布 -->
    <div id="ipSection" style="<?php echo $tab !== 'ip' ? 'display:none;' : ''; ?>">
        <div class="form-inline" style="margin-bottom:10px;">
            统计维度：
            <select id="statType" class="form-control input-sm" style="width:120px;">
                <option value="city" <?php if ($stat_type === 'city') echo 'selected'; ?>>按城市</option>
                <option value="province" <?php if ($stat_type === 'province') echo 'selected'; ?>>按省份</option>
            </select>
            <button id="btnRefresh" type="button" class="btn btn-primary btn-sm">刷新统计</button>
            <span id="status" style="margin-left:10px;color:#999;"></span>
        </div>

        <div id="resultArea" style="display:none;">
            <div id="ipChart" style="width:100%;height:500px;"></div>
            <h4>详细数据</h4>
            <table class="table table-bordered table-condensed">
                <thead>
                    <tr>
                        <th>排名</th>
                        <th id="thLocation">城市</th>
                        <th>用户数</th>
                        <th>占比</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
            <div style="margin-top:10px;padding:8px 12px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;color:#856404;font-size:12px;">
                <i class="glyphicon glyphicon-info-sign"></i>
                <b>说明：</b>同一教师在不同地点（不同IP）登录会被分别统计。例如：某教师在北京登录3次、上海登录2次，则北京IP和上海IP各计1人，但该教师实际只有1人。
            </div>
        </div>
        <div id="emptyArea" style="text-align:center;color:#999;margin:20px;">
            点击"刷新统计"按钮查询老师登录IP归属地分布
        </div>
    </div>

    <!-- 教师活跃度 -->
    <div id="activitySection" style="<?php echo $tab !== 'activity' ? 'display:none;' : ''; ?>">
        <div class="form-inline" style="margin-bottom:10px;">
            <button class="btn btn-primary btn-sm activity-btn" data-days="1">当天</button>
            <button class="btn btn-default btn-sm activity-btn" data-days="7">近7天</button>
            <button class="btn btn-default btn-sm activity-btn" data-days="14">近14天</button>
            <button id="btnRefreshActivity" type="button" class="btn btn-success btn-sm" style="margin-left:10px;">刷新活跃度</button>
            <span id="activityStatus" style="margin-left:10px;color:#999;"></span>
        </div>

        <div id="activityResultArea" style="display:none;">
            <div style="display:flex;gap:20px;flex-wrap:wrap;">
                <div id="activityPieChart" style="width:50%;min-width:400px;height:400px;"></div>
                <div style="flex:1;min-width:300px;">
                    <h4 id="activityTitle">教师活跃度统计</h4>
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th>状态</th>
                                <th>人数</th>
                                <th>占比</th>
                            </tr>
                        </thead>
                        <tbody id="activityTableBody"></tbody>
                    </table>
                    <div id="activitySummary" style="margin-top:15px;padding:10px;background:#f5f5f5;border-radius:4px;"></div>
                </div>
            </div>
        </div>
        <div id="activityEmptyArea" style="text-align:center;color:#999;margin:20px;">
            选择时间段后点击"刷新活跃度"按钮查询
        </div>
    </div>
</div>

<script src="../template/<?php echo $OJ_TEMPLATE ?>/js/echarts.min.js"></script>
<script type="text/javascript">
var ipChart = null;
var activityChart = null;
var currentDays = 1;

// 切换标签页
$('.activity-btn').click(function() {
    $('.activity-btn').removeClass('btn-primary').addClass('btn-default');
    $(this).removeClass('btn-default').addClass('btn-primary');
    currentDays = $(this).data('days');
});

// IP归属地统计
$('#btnRefresh').click(function() {
    var statType = $('#statType').val();
    var btn = $(this);
    btn.prop('disabled', true).text('统计中...');
    $('#status').text('正在查询IP归属地，请稍候...');

    $.ajax({
        url: 'teacher_ip_stats_ajax.php',
        type: 'GET',
        data: { action: 'ip_stats', stat_type: statType },
        dataType: 'json',
        timeout: 300000,
        success: function(data) {
            btn.prop('disabled', false).text('刷新统计');
            if (data.error) {
                $('#status').text('错误: ' + data.error);
                return;
            }
            if (!data.chart_labels || data.chart_labels.length === 0) {
                $('#status').text('暂无数据');
                return;
            }
            try {
                renderIpChart(data);
                renderIpTable(data, statType);
                $('#status').text('统计完成，共 ' + data.count + ' 个IP，' + data.total + ' 位老师');
            } catch(e) {
                $('#status').text('渲染出错: ' + e.message);
            }
        },
        error: function(xhr, status, err) {
            btn.prop('disabled', false).text('刷新统计');
            $('#status').text('请求失败: ' + (err || status));
        }
    });
});

// 教师活跃度统计
$('#btnRefreshActivity').click(function() {
    var btn = $(this);
    btn.prop('disabled', true).text('查询中...');
    $('#activityStatus').text('正在查询教师活跃度...');

    $.ajax({
        url: 'teacher_ip_stats_ajax.php',
        type: 'GET',
        data: { action: 'activity', days: currentDays },
        dataType: 'json',
        timeout: 30000,
        success: function(data) {
            btn.prop('disabled', false).text('刷新活跃度');
            if (data.error) {
                $('#activityStatus').text('错误: ' + data.error);
                return;
            }
            try {
                renderActivityChart(data);
                renderActivityTable(data);
                $('#activityStatus').text('查询完成');
            } catch(e) {
                $('#activityStatus').text('渲染出错: ' + e.message);
            }
        },
        error: function(xhr, status, err) {
            btn.prop('disabled', false).text('刷新活跃度');
            $('#activityStatus').text('请求失败: ' + (err || status));
        }
    });
});

// 渲染IP归属地图表
function renderIpChart(data) {
    $('#resultArea').show();
    $('#emptyArea').hide();

    if (!ipChart) {
        ipChart = echarts.init($('#ipChart')[0]);
        $(window).resize(function() { ipChart.resize(); });
    }

    ipChart.setOption({
        title: {
            text: '老师登录IP归属地统计',
            subtext: '时间范围: ' + data.from + ' 至 ' + data.to,
            left: 'center'
        },
        tooltip: {
            trigger: 'axis',
            axisPointer: { type: 'shadow' },
            formatter: '{b}: {c} 人'
        },
        grid: {
            left: '3%', right: '4%', bottom: '15%', containLabel: true
        },
        xAxis: {
            type: 'category',
            data: data.chart_labels,
            axisLabel: { rotate: 45, interval: 0 }
        },
        yAxis: { type: 'value', name: '用户数' },
        series: [{
            name: '用户数', type: 'bar', data: data.chart_data,
            itemStyle: {
                color: function(params) {
                    var colors = ['#5470c6','#91cc75','#fac858','#ee6666','#73c0de','#3ba272','#fc8452','#9a60b4','#ea7ccc'];
                    return colors[params.dataIndex % colors.length];
                }
            },
            label: { show: true, position: 'top' }
        }]
    }, true);
}

function renderIpTable(data, statType) {
    $('#thLocation').text(statType === 'province' ? '省份' : '城市');
    var html = '';
    var rank = 1;
    for (var key in data.stats) {
        var count = data.stats[key];
        var percent = data.total > 0 ? (count / data.total * 100).toFixed(1) : 0;
        html += '<tr><td>' + rank++ + '</td><td>' + key + '</td><td>' + count + '</td><td>' + percent + '%</td></tr>';
    }
    $('#tableBody').html(html);
}

// 渲染活跃度饼图
function renderActivityChart(data) {
    $('#activityResultArea').show();
    $('#activityEmptyArea').hide();

    if (!activityChart) {
        activityChart = echarts.init($('#activityPieChart')[0]);
        $(window).resize(function() { activityChart.resize(); });
    }

    var pieData = [
        { value: data.active, name: '已登录' },
        { value: data.inactive, name: '未登录' }
    ];

    activityChart.setOption({
        title: {
            text: '教师活跃度',
            subtext: data.period,
            left: 'center'
        },
        tooltip: {
            trigger: 'item',
            formatter: '{b}: {c} 人 ({d}%)'
        },
        legend: {
            orient: 'vertical',
            left: 'left',
            top: 'middle'
        },
        series: [{
            name: '活跃度',
            type: 'pie',
            radius: ['40%', '70%'],
            center: ['55%', '50%'],
            avoidLabelOverlap: false,
            itemStyle: {
                borderRadius: 10,
                borderColor: '#fff',
                borderWidth: 2
            },
            label: {
                show: true,
                formatter: '{b}\n{c}人 ({d}%)'
            },
            emphasis: {
                label: { show: true, fontSize: '18', fontWeight: 'bold' }
            },
            data: pieData,
            color: ['#91cc75', '#ee6666']
        }]
    }, true);
}

function renderActivityTable(data) {
    var total = data.total;
    var activePercent = total > 0 ? (data.active / total * 100).toFixed(1) : 0;
    var inactivePercent = total > 0 ? (data.inactive / total * 100).toFixed(1) : 0;

    var html = '<tr><td><span style="color:#91cc75;">●</span> 已登录</td>'
        + '<td>' + data.active + ' 人</td><td>' + activePercent + '%</td></tr>'
        + '<tr><td><span style="color:#ee6666;">●</span> 未登录</td>'
        + '<td>' + data.inactive + ' 人</td><td>' + inactivePercent + '%</td></tr>'
        + '<tr><td><b>合计</b></td><td><b>' + total + ' 人</b></td><td>100%</td></tr>';
    $('#activityTableBody').html(html);

    $('#activityTitle').text(data.period + ' 教师活跃度统计');
    $('#activitySummary').html(
        '<b>活跃率：</b>' + activePercent + '% '
        + '(' + data.active + '/' + total + ')'
        + ' | <b>沉默率：</b>' + inactivePercent + '%'
    );
}
</script>

<?php require("admin-footer.php"); ?>
