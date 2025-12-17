<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ./sign-in.php");
    exit();
}
include __DIR__ . '/../config/config.php';

$user_id = $_SESSION['id_user'];
$start = isset($_GET['start']) ? $_GET['start'] : null;
$end = isset($_GET['end']) ? $_GET['end'] : null;

if ($start && $end) {
    $stmt = $conn->prepare("SELECT hc.*, h.name AS habit_name FROM habit_completions hc JOIN habits h ON hc.habit_id = h.id WHERE hc.user_id = ? AND hc.completion_date BETWEEN ? AND ? ORDER BY hc.completion_date DESC, hc.created_at DESC");
    $stmt->bind_param("iss", $user_id, $start, $end);
} else {
    $stmt = $conn->prepare("SELECT hc.*, h.name AS habit_name FROM habit_completions hc JOIN habits h ON hc.habit_id = h.id WHERE hc.user_id = ? ORDER BY hc.completion_date DESC, hc.created_at DESC");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - ELGROW</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-border {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            padding: 1px;
            border-radius: 0.75rem;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.2);
        }
        @media (max-width: 640px) {
            .mobile-card {
                background: #1f2937;
                border-radius: 0.75rem;
                padding: 1rem;
                margin-bottom: 0.75rem;
                border: 1px solid #374151;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-950 via-black to-gray-950 min-h-screen text-white">

<div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">
    <!-- Header Section -->
    <div class="mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-orange-500 to-orange-600 bg-clip-text text-transparent mb-2">
                    History
                </h1>
                <p class="text-gray-400 text-sm sm:text-base">Track your habit completion journey</p>
            </div>
            <div class="flex gap-2 sm:gap-3">
                <a href="dashboard.php" class="flex-1 sm:flex-none text-center bg-gray-800 hover:bg-gray-700 px-4 py-2.5 rounded-lg transition-all font-medium text-sm">
                    <span class="hidden sm:inline">← Back</span>
                    <span class="sm:hidden">Back</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="gradient-border mb-6 sm:mb-8">
        <div class="bg-gray-900 rounded-xl p-4 sm:p-6">
            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="text-xl">🔍</span>
                Filter by Date Range
            </h2>
            <form method="get" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <label class="block text-xs text-gray-400 mb-1.5">Start Date</label>
                    <input type="date" name="start" value="<?php echo htmlspecialchars($start); ?>" class="w-full bg-gray-800 border border-gray-700 px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all" />
                </div>
                <div class="flex-1">
                    <label class="block text-xs text-gray-400 mb-1.5">End Date</label>
                    <input type="date" name="end" value="<?php echo htmlspecialchars($end); ?>"class="w-full bg-gray-800 border border-gray-700 px-4 py-2.5 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all" />
                </div>
                <div class="flex gap-2 sm:gap-3 sm:items-end">
                    <button class="flex-1 sm:flex-none gradient-bg hover:opacity-90 px-6 py-2.5 rounded-lg transition-all font-medium shadow-lg shadow-orange-500/30">
                        Apply
                    </button>
                    <a href="history.php" class="flex-1 sm:flex-none text-center bg-gray-800 hover:bg-gray-700 px-6 py-2.5 rounded-lg transition-all font-medium">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="mb-4 flex items-center justify-between text-sm text-gray-400">
        <span><?php echo count($results); ?> record<?php echo count($results) != 1 ? 's' : ''; ?> found</span>
        <?php if ($start && $end): ?>
        <span class="text-orange-500"><?php echo date('M d, Y', strtotime($start)); ?> - <?php echo date('M d, Y', strtotime($end)); ?></span>
        <?php endif; ?>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden sm:block bg-gray-900 rounded-xl overflow-hidden border border-gray-800 shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Habit</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Notes</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">Recorded At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php if (empty($results)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <span class="text-5xl">📭</span>
                                <p class="text-gray-400 text-lg">No history found</p>
                                <p class="text-gray-500 text-sm">Start completing habits to see your progress here</p>
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($results as $r): ?>
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-2 bg-orange-500/10 text-orange-400 px-3 py-1 rounded-full text-sm font-medium">
                                    📅 <?php echo date('M d, Y', strtotime($r['completion_date'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-white"><?php echo htmlspecialchars($r['habit_name']); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-300 text-sm max-w-md">
                                    <?php echo $r['notes'] ? nl2br(htmlspecialchars($r['notes'])) : '<span class="text-gray-500 italic">No notes</span>'; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-400 text-sm">
                                <?php echo date('M d, Y H:i', strtotime($r['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="sm:hidden">
        <?php if (empty($results)): ?>
        <div class="bg-gray-900 rounded-xl p-8 text-center border border-gray-800">
            <span class="text-5xl block mb-3">📭</span>
            <p class="text-gray-400 text-lg mb-2">No history found</p>
            <p class="text-gray-500 text-sm">Start completing habits to see your progress here</p>
        </div>
        <?php else: ?>
            <?php foreach ($results as $r): ?>
            <div class="mobile-card card-hover">
                <div class="flex items-start justify-between mb-3">
                    <span class="inline-flex items-center gap-1.5 bg-orange-500/10 text-orange-400 px-2.5 py-1 rounded-full text-xs font-medium">
                        📅 <?php echo date('M d, Y', strtotime($r['completion_date'])); ?>
                    </span>
                    <span class="text-xs text-gray-500"><?php echo date('H:i', strtotime($r['created_at'])); ?></span>
                </div>
                
                <h3 class="font-semibold text-white text-lg mb-2">
                    <?php echo htmlspecialchars($r['habit_name']); ?>
                </h3>
                
                <?php if ($r['notes']): ?>
                <div class="bg-gray-800/50 rounded-lg p-3 text-sm text-gray-300">
                    <?php echo nl2br(htmlspecialchars($r['notes'])); ?>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-sm italic">No notes added</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>