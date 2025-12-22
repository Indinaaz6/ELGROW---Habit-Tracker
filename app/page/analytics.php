<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ./sign-in.php");
    exit();
}
include __DIR__ . '/../config/config.php';

$user_id = $_SESSION['id_user'];

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM habit_completions WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM habit_completions WHERE user_id = ? AND completion_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$last7 = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM habit_completions WHERE user_id = ? AND completion_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$last30 = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;

$stmt = $conn->prepare("SELECT completion_date FROM habit_completions WHERE user_id = ? GROUP BY completion_date ORDER BY completion_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$dates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$streak = 0;
$checkDate = date('Y-m-d');
foreach ($dates as $d) {
    if ($d['completion_date'] == $checkDate) {
        $streak++;
        $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
    } else {
        break;
    }
}

$stmt = $conn->prepare("SELECT h.name, COUNT(hc.id) AS cnt FROM habits h LEFT JOIN habit_completions hc ON h.id = hc.habit_id AND hc.user_id = ? WHERE h.user_id = ? GROUP BY h.id ORDER BY cnt DESC LIMIT 10");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$top = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT completion_date, COUNT(*) AS cnt FROM habit_completions WHERE user_id = ? AND completion_date >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY completion_date ORDER BY completion_date ASC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$daily = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$dailyMap = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $dailyMap[$d] = 0;
}
foreach ($daily as $d) {
    $dailyMap[$d['completion_date']] = (int)$d['cnt'];
}

$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM habit_completions WHERE user_id = ? AND completion_date >= DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND completion_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$prev7 = $stmt->get_result()->fetch_assoc()['cnt'] ?? 0;
$change7 = $prev7 > 0 ? round((($last7 - $prev7) / $prev7) * 100) : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - ELGROW</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.3);
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        @media (max-width: 768px) {
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white min-h-screen">

<div class="sticky top-0 z-10 backdrop-blur-lg bg-gray-900/80 border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center">
                    <span class="text-xl">📊</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold">Analytics</h1>
            </div>
            <a href="dashboard.php" class="bg-gray-800 hover:bg-gray-700 px-4 py-2 rounded-lg transition-colors duration-200 text-sm sm:text-base">
                ← Back
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-2xl border border-gray-700 card-hover">
            <div class="flex items-start justify-between mb-4">
                <div class="stat-icon bg-blue-500/20 text-blue-400">
                    🎯
                </div>
                <div class="text-xs font-medium text-gray-400 bg-gray-700/50 px-2 py-1 rounded">All Time</div>
            </div>
            <div class="text-3xl sm:text-4xl font-bold mb-1"><?php echo number_format($total); ?></div>
            <div class="text-sm text-gray-400">Total Completions</div>
        </div>

        <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-2xl border border-gray-700 card-hover">
            <div class="flex items-start justify-between mb-4">
                <div class="stat-icon bg-green-500/20 text-green-400">
                    📈
                </div>
                <?php if ($change7 != 0): ?>
                <div class="text-xs font-medium <?php echo $change7 > 0 ? 'text-green-400' : 'text-red-400'; ?> bg-gray-700/50 px-2 py-1 rounded">
                    <?php echo $change7 > 0 ? '↑' : '↓'; ?> <?php echo abs($change7); ?>%
                </div>
                <?php endif; ?>
            </div>
            <div class="text-3xl sm:text-4xl font-bold mb-1"><?php echo $last7; ?></div>
            <div class="text-sm text-gray-400">Last 7 Days</div>
        </div>

        <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-2xl border border-gray-700 card-hover">
            <div class="flex items-start justify-between mb-4">
                <div class="stat-icon bg-purple-500/20 text-purple-400">
                    📅
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-bold mb-1"><?php echo $last30; ?></div>
            <div class="text-sm text-gray-400">Last 30 Days</div>
        </div>

        <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-6 rounded-2xl border border-gray-700 card-hover">
            <div class="flex items-start justify-between mb-4">
                <div class="stat-icon bg-orange-500/20 text-orange-400">
                    🔥
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-bold mb-1"><?php echo $streak; ?></div>
            <div class="text-sm text-gray-400">Day Streak</div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6 sm:mb-8">
        
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-4 sm:p-6 rounded-2xl border border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg sm:text-xl font-semibold">Activity Trend</h3>
                <span class="text-xs text-gray-400">Last 14 days</span>
            </div>
            <div class="chart-container">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-4 sm:p-6 rounded-2xl border border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg sm:text-xl font-semibold">Top Performers</h3>
                <span class="text-xs text-gray-400">Top 5</span>
            </div>
            <div class="chart-container">
                <canvas id="topChart"></canvas>
            </div>
        </div>

    </div>

    <div class="bg-gradient-to-br from-gray-800 to-gray-900 p-4 sm:p-6 rounded-2xl border border-gray-700">
        <h3 class="text-lg sm:text-xl font-semibold mb-4 flex items-center">
            <span class="mr-2">🏆</span>
            All Habits Ranking
        </h3>
        <?php if (empty($top)): ?>
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📊</div>
                <div class="text-gray-400">No habits tracked yet.</div>
                <div class="text-sm text-gray-500 mt-2">Start completing habits to see your analytics!</div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="text-left py-3 px-2 text-xs sm:text-sm font-medium text-gray-400">Rank</th>
                            <th class="text-left py-3 px-2 text-xs sm:text-sm font-medium text-gray-400">Habit</th>
                            <th class="text-right py-3 px-2 text-xs sm:text-sm font-medium text-gray-400">Completions</th>
                            <th class="text-right py-3 px-2 text-xs sm:text-sm font-medium text-gray-400 hidden sm:table-cell">Progress</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $maxCount = $top[0]['cnt'] ?? 1;
                        foreach ($top as $idx => $t): 
                            $percentage = ($t['cnt'] / $maxCount) * 100;
                        ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
                            <td class="py-4 px-2">
                                <div class="flex items-center justify-center w-8 h-8 rounded-lg <?php 
                                    echo $idx === 0 ? 'bg-yellow-500/20 text-yellow-400' : 
                                        ($idx === 1 ? 'bg-gray-400/20 text-gray-300' : 
                                        ($idx === 2 ? 'bg-orange-600/20 text-orange-400' : 'bg-gray-700/50 text-gray-400'));
                                ?>">
                                    <span class="font-bold text-sm"><?php echo $idx + 1; ?></span>
                                </div>
                            </td>
                            <td class="py-4 px-2">
                                <div class="font-medium text-sm sm:text-base"><?php echo htmlspecialchars($t['name']); ?></div>
                            </td>
                            <td class="py-4 px-2 text-right">
                                <span class="font-semibold text-lg"><?php echo $t['cnt']; ?></span>
                            </td>
                            <td class="py-4 px-2 hidden sm:table-cell">
                                <div class="w-full bg-gray-700 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full transition-all duration-300" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Prepare data from PHP
const dailyLabels = <?php echo json_encode(array_map(function($d){ return date('M d', strtotime($d)); }, array_keys($dailyMap))); ?>;
const dailyData = <?php echo json_encode(array_values($dailyMap)); ?>;

const topLabels = <?php echo json_encode(array_map(function($t){ return $t['name']; }, array_slice($top, 0, 5))); ?>;
const topData = <?php echo json_encode(array_map(function($t){ return (int)$t['cnt']; }, array_slice($top, 0, 5))); ?>;

// Chart defaults
Chart.defaults.color = '#9CA3AF';
Chart.defaults.font.family = "'Inter', sans-serif";

// Activity (line) chart
const ctxA = document.getElementById('activityChart').getContext('2d');
const gradientA = ctxA.createLinearGradient(0, 0, 0, 300);
gradientA.addColorStop(0, 'rgba(102, 126, 234, 0.4)');
gradientA.addColorStop(1, 'rgba(118, 75, 162, 0.1)');

new Chart(ctxA, {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Completions',
            data: dailyData,
            backgroundColor: gradientA,
            borderColor: '#667eea',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#667eea',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                padding: 12,
                borderColor: '#374151',
                borderWidth: 1,
                titleFont: { size: 14, weight: '600' },
                bodyFont: { size: 13 }
            }
        },
        scales: {
            y: { 
                beginAtZero: true, 
                ticks: { precision: 0 },
                grid: { color: 'rgba(255,255,255,0.05)' }
            },
            x: {
                grid: { display: false },
                ticks: { maxRotation: 45, minRotation: 0 }
            }
        }
    }
});

// Top habits (bar) chart
const ctxT = document.getElementById('topChart').getContext('2d');
new Chart(ctxT, {
    type: 'bar',
    data: {
        labels: topLabels,
        datasets: [{
            label: 'Completions',
            data: topData,
            backgroundColor: [
                'rgba(34, 197, 94, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(168, 85, 247, 0.8)',
                'rgba(249, 115, 22, 0.8)',
                'rgba(236, 72, 153, 0.8)'
            ],
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(17, 24, 39, 0.9)',
                padding: 12,
                borderColor: '#374151',
                borderWidth: 1,
                titleFont: { size: 14, weight: '600' },
                bodyFont: { size: 13 }
            }
        },
        scales: { 
            x: { 
                beginAtZero: true, 
                ticks: { precision: 0 },
                grid: { color: 'rgba(255,255,255,0.05)' }
            },
            y: {
                grid: { display: false }
            }
        }
    }
});
</script>

</body>
</html>