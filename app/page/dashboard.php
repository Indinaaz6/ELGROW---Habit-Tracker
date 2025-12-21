<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ./sign-in.php");
    exit();
}
include __DIR__ . '/../config/config.php';

date_default_timezone_set('Asia/Jakarta');

$user_id = $_SESSION['id_user'];
$today = date('Y-m-d');
$selected_date = isset($_GET['date']) ? $_GET['date'] : $today;

function getFrequencyLabel($frequency, $daily_days, $weekly_count, $end_date) {
    if ($frequency === 'daily') {
        return 'Daily';
    } elseif ($frequency === 'weekly' && $weekly_count > 0) {
        return 'Weekly (' . $weekly_count . ' days/week)';
    } elseif ($frequency === 'custom' && $end_date) {
        $dateObj = DateTime::createFromFormat('Y-m-d', $end_date);
        return 'Until ' . ($dateObj ? $dateObj->format('M d, Y') : $end_date);
    }
    return ucfirst($frequency);
}

$stmt = $conn->prepare("SELECT username FROM users WHERE id_user = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    $user = ['username' => $_SESSION['username'] ?? 'User'];
}

$stmt = $conn->prepare("SELECT * FROM habits WHERE user_id = ? AND (end_date IS NULL OR end_date >= ?) ORDER BY created_at DESC");
$stmt->bind_param("is", $user_id, $selected_date);
$stmt->execute();
$all_habits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$habits = array_filter($all_habits, function($h) use ($selected_date) {
    $freq = $h['frequency'] ?? 'daily';
    $daily_days = $h['daily_days'] ?? '';
    $weekly_count = (int)($h['weekly_count'] ?? 0);
    $dow = (int)date('w', strtotime($selected_date));

    if ($freq === 'daily') {
        return true;
    }

    if ($freq === 'weekly') {
        if (trim($daily_days) !== '') {
            $days = array_map('trim', explode(',', $daily_days));
            return in_array((string)$dow, $days) || in_array($dow, $days, true);
        }
        return true;
    }

    if ($freq === 'custom') {
        return true;
    }

    return true;
});

$stmt = $conn->prepare("SELECT * FROM habit_completions WHERE user_id = ? AND completion_date = ?");
$stmt->bind_param("is", $user_id, $selected_date);
$stmt->execute();
$completions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$completed_habits = array_column($completions, 'habit_id');

$stmt = $conn->prepare("
    SELECT 
        COUNT(DISTINCT CASE WHEN hc.completion_date >= DATE_SUB(?, INTERVAL 7 DAY) THEN h.id END) * 100.0 / 
        NULLIF(COUNT(DISTINCT h.id) * 7, 0) as completion_rate,
        MAX(h.streak) as current_streak
    FROM habits h
    LEFT JOIN habit_completions hc ON h.id = hc.habit_id AND hc.user_id = ?
    WHERE h.user_id = ? AND h.is_active = 1
");
$stmt->bind_param("sii", $selected_date, $user_id, $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">         
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ELGROW</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes checkmark {
            0% { transform: scale(0) rotate(0deg); }
            50% { transform: scale(1.2) rotate(180deg); }
            100% { transform: scale(1) rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
        .slide-in { animation: slideIn 0.5s ease-out forwards; }
        .pulse-animation { animation: pulse 2s ease-in-out infinite; }
        
        .habit-card {
            transition: all 0.3s ease;
        }
        
        .habit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(234, 88, 12, 0.3);
        }
        
        .habit-completed {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.2) 0%, rgba(16, 185, 129, 0.2) 100%);
            border-color: rgba(34, 197, 94, 0.5);
        }
        
        .check-animation { animation: checkmark 0.5s ease-out; }
        .progress-ring { transform: rotate(-90deg); }
        .progress-ring-circle { transition: stroke-dashoffset 0.5s ease; }
        
        .calendar-day {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .calendar-day:hover { background: rgba(249, 115, 22, 0.2); }
        .calendar-day.selected { background: #f97316; color: white; }
        .calendar-day.has-completion { background: rgba(34, 197, 94, 0.3); }
        
        .modal-backdrop {
            backdrop-filter: blur(8px);
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>

<body class="bg-black min-h-screen">
    <!-- Navbar -->
    <nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50 backdrop-blur-md bg-opacity-90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-8">
                    <a href="#" class="text-2xl font-bold text-orange-500">ELGROW</a>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="bg-orange-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-orange-600 transition">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </button>
                    <a href="logout.php" class="text-gray-400 hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Date Header with Calendar -->
                <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800 slide-in">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-2" id="currentDay">
                                <?php echo date('l', strtotime($selected_date)); ?>
                            </h1>
                            <div class="flex items-center space-x-3 text-gray-400">
                                <span id="currentDate"><?php echo date('d F Y', strtotime($selected_date)); ?></span>
                                <button onclick="changeDate(-1)" class="hover:text-orange-500 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                <button onclick="changeDate(1)" class="hover:text-orange-500 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                <button onclick="goToToday()" class="text-xs bg-gray-800 px-3 py-1 rounded hover:bg-orange-500 transition">
                                    Today
                                </button>
                                <button onclick="toggleCalendar()" class="hover:text-orange-500 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <button id="addHabitBtn" class="bg-orange-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-orange-600 transition flex items-center space-x-2 shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>Add Habit</span>
                        </button>
                    </div>
                    
                    <!-- Mini Calendar -->
                    <div id="miniCalendar" class="hidden mt-4 bg-gray-800 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-4">
                            <button onclick="changeMonth(-1)" class="text-gray-400 hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <span class="text-white font-semibold" id="calendarMonth"></span>
                            <button onclick="changeMonth(1)" class="text-gray-400 hover:text-white">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-7 gap-2 text-center text-sm">
                            <div class="text-gray-500 font-medium py-2">Su</div>
                            <div class="text-gray-500 font-medium py-2">Mo</div>
                            <div class="text-gray-500 font-medium py-2">Tu</div>
                            <div class="text-gray-500 font-medium py-2">We</div>
                            <div class="text-gray-500 font-medium py-2">Th</div>
                            <div class="text-gray-500 font-medium py-2">Fr</div>
                            <div class="text-gray-500 font-medium py-2">Sa</div>
                        </div>
                        <div id="calendarDays" class="grid grid-cols-7 gap-2 text-center text-sm mt-2 min-h-[180px]"></div>
                    </div>
                </div>

                <!-- Habits List -->
                <div id="habitsContainer" class="space-y-4">
                    <?php foreach ($habits as $habit): ?>
                    <div class="habit-card bg-gray-900 rounded-2xl p-6 border border-gray-800 fade-in <?php echo in_array($habit['id'], $completed_habits) ? 'habit-completed' : ''; ?>" 
                         data-habit-id="<?php echo $habit['id']; ?>"
                         data-habit-name="<?php echo htmlspecialchars($habit['name']); ?>">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 flex-1">
                                <button class="habit-checkbox w-12 h-12 rounded-lg border-2 transition flex items-center justify-center
                                    <?php echo in_array($habit['id'], $completed_habits) ? 'border-green-500 bg-green-500' : 'border-gray-700 bg-gray-800 hover:border-orange-500'; ?>"
                                    onclick="toggleHabit(this, <?php echo $habit['id']; ?>)">
                                    <svg class="w-6 h-6 text-white <?php echo !in_array($habit['id'], $completed_habits) ? 'hidden' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                                <div class="flex-1">
                                    <h3 class="text-xl font-semibold text-white mb-2 <?php echo in_array($habit['id'], $completed_habits) ? 'line-through opacity-75' : ''; ?>">
                                        <?php echo htmlspecialchars($habit['name']); ?>
                                    </h3>
                                    <div class="flex items-center space-x-2 flex-wrap gap-2">
                                        <span class="bg-orange-500 text-white text-xs px-3 py-1 rounded-full font-medium">
                                            <?php echo htmlspecialchars(getFrequencyLabel($habit['frequency'], $habit['daily_days'], $habit['weekly_count'], $habit['end_date'])); ?>
                                        </span>
                                        <?php if ($habit['category']): ?>
                                        <span class="bg-gray-700 text-gray-300 text-xs px-3 py-1 rounded-full">
                                            <?php echo htmlspecialchars($habit['category']); ?>
                                        </span>
                                        <?php endif; ?>
                                        <span class="text-gray-400 text-sm">🔥 <?php echo $habit['streak']; ?> day streak</span>
                                    </div>
                                    <!-- Frequency Details List -->
                                    <div class="mt-2 text-xs text-gray-400">
                                        <?php if ($habit['frequency'] === 'daily' && $habit['daily_days']): ?>
                                            <div class="flex flex-wrap gap-1">
                                                <?php 
                                                    $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                                    $days = array_filter(array_map('trim', explode(',', $habit['daily_days'])));
                                                    foreach ($days as $d):
                                                        $dayName = isset($dayNames[(int)$d]) ? $dayNames[(int)$d] : '';
                                                        if ($dayName):
                                                ?>
                                                <span class="bg-gray-800 px-2 py-1 rounded">📅 <?php echo $dayName; ?></span>
                                                <?php endif; endforeach; ?>
                                            </div>
                                        <?php elseif ($habit['frequency'] === 'weekly'): ?>
                                            <?php if (!empty($habit['daily_days'])): ?>
                                                <div class="flex flex-wrap gap-1">
                                                    <?php 
                                                        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                                                        $days = array_filter(array_map('trim', explode(',', $habit['daily_days'])));
                                                        foreach ($days as $d):
                                                            $dayName = isset($dayNames[(int)$d]) ? $dayNames[(int)$d] : '';
                                                            if ($dayName):
                                                    ?>
                                                    <span class="bg-gray-800 px-2 py-1 rounded">📅 <?php echo $dayName; ?></span>
                                                    <?php endif; endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <div>📊 <?php echo $habit['weekly_count'] ?? 0; ?> days per week</div>
                                            <?php endif; ?>
                                        <?php elseif ($habit['frequency'] === 'custom' && $habit['end_date']): ?>
                                            <div>📆 Until <?php echo date('M d, Y', strtotime($habit['end_date'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (in_array($habit['id'], $completed_habits)): 
                                        $note = array_filter($completions, function($c) use ($habit) { 
                                            return $c['habit_id'] == $habit['id']; 
                                        });
                                        $note = reset($note);
                                    ?>
                                        <?php if (!empty($note['notes'])): ?>
                                        <p class="text-gray-400 text-sm mt-2 italic">📝 <?php echo htmlspecialchars($note['notes']); ?></p>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="editHabit(<?php echo $habit['id']; ?>)" class="text-gray-400 hover:text-blue-500 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteHabit(<?php echo $habit['id']; ?>)" class="text-gray-400 hover:text-red-500 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($habits)): ?>
                    <div class="bg-gray-900 rounded-2xl p-12 border border-gray-800 text-center fade-in">
                        <div class="text-6xl mb-4">🎯</div>
                        <h3 class="text-xl font-semibold text-white mb-2">No Habits Yet</h3>
                        <p class="text-gray-400 mb-6">Start building better habits today!</p>
                        <button onclick="document.getElementById('addHabitBtn').click()" 
                            class="bg-orange-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-orange-600 transition">
                            Create Your First Habit
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar Stats -->
            <div class="space-y-6">
                <!-- Progress Card -->
                <div class="bg-gradient-to-br from-orange-500 to-yellow-500 rounded-2xl p-6 text-white fade-in">
                    <p class="text-sm opacity-90 mb-2">Progress Today</p>
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-5xl font-bold mb-1" id="progressPercentage">
                                <?php 
                                $total_habits = count($habits);
                                $completed_today = count($completed_habits);
                                $percentage = $total_habits > 0 ? round(($completed_today / $total_habits) * 100) : 0;
                                echo $percentage;
                                ?>%
                            </h2>
                            <p class="text-sm opacity-90" id="progressText">
                                <?php echo $completed_today; ?> of <?php echo $total_habits; ?> completed
                            </p>
                        </div>
                        <svg class="w-24 h-24" viewBox="0 0 100 100">
                            <circle class="progress-ring" cx="50" cy="50" r="40" stroke="rgba(255,255,255,0.3)" stroke-width="8" fill="none"/>
                            <circle class="progress-ring progress-ring-circle" cx="50" cy="50" r="40" stroke="white" stroke-width="8" fill="none" 
                                stroke-dasharray="251.2" stroke-dashoffset="<?php echo 251.2 - (($percentage / 100) * 251.2); ?>" stroke-linecap="round"/>
                        </svg>
                    </div>
                </div>

                <!-- Weekly Stats -->
                <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800 fade-in" style="animation-delay: 0.1s;">
                    <h3 class="text-lg font-semibold text-white mb-4">Weekly Overview</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-400">Completion Rate</span>
                                <span class="text-white font-semibold"><?php echo round($stats['completion_rate'] ?? 0); ?>%</span>
                            </div>
                            <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-orange-500 to-yellow-500 rounded-full" 
                                     style="width: <?php echo round($stats['completion_rate'] ?? 0); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-400">Current Streak</span>
                                <span class="text-white font-semibold"><?php echo $stats['current_streak'] ?? 0; ?> days</span>
                            </div>
                            <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full" 
                                     style="width: <?php echo min(100, ($stats['current_streak'] ?? 0) * 10); ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-400">Total Habits</span>
                                <span class="text-white font-semibold"><?php echo count($habits); ?> habits</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-gray-900 rounded-2xl p-6 border border-gray-800 fade-in" style="animation-delay: 0.2s;">
                    <h3 class="text-lg font-semibold text-white mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button onclick="viewHistory()" class="w-full bg-gray-800 hover:bg-gray-700 text-white px-4 py-3 rounded-lg transition flex items-center justify-between">
                            <span>📊 View History</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        <button onclick="viewGoals()" class="w-full bg-gray-800 hover:bg-gray-700 text-white px-4 py-3 rounded-lg transition flex items-center justify-between">
                            <span>🎯 Goals</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"></path>
                            </svg>
                        </button>
                        <button onclick="showStats()" class="w-full bg-gray-800 hover:bg-gray-700 text-white px-4 py-3 rounded-lg transition flex items-center justify-between">
                            <span>📈 Analytics</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Habit Modal -->
    <div id="habitModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4 modal-backdrop">
        <div class="bg-gray-900 rounded-2xl p-8 max-w-md w-full border border-gray-800 fade-in">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white" id="modalTitle">Add New Habit</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="habitForm" class="space-y-4">
                <input type="hidden" id="habitId" value="">
                
                <div>
                    <label class="text-sm font-medium text-gray-300 mb-2 block">Habit Name</label>
                    <input type="text" id="habitName" placeholder="e.g., Morning Run" required
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-orange-500 transition">
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-300 mb-2 block">Frequency</label>
                    <select id="habitFrequency" 
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:border-orange-500 transition">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>

                    <div id="frequencyOptions" class="space-y-3">

                    <div id="weeklyOptions" class="hidden space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-300 mb-2 block">Select days</label>
                            <div class="grid grid-cols-7 gap-2 text-center">
                                <label class="calendar-day flex flex-col items-center justify-center cursor-pointer" title="Sunday">
                                    <input type="checkbox" class="weekly-weekday-checkbox h-4 w-4 mb-1 accent-orange-500" value="0">
                                    <span class="block text-gray-300">Su</span>
                                </label>
                                <label class="calendar-day flex flex-col items-center justify-center cursor-pointer" title="Monday">
                                    <input type="checkbox" class="weekly-weekday-checkbox h-4 w-4 mb-1 accent-orange-500" value="1">
                                    <span class="block text-gray-300">Mo</span>
                                </label>
                                <label class="calendar-day flex flex-col items-center justify-center cursor-pointer" title="Tuesday">
                                    <input type="checkbox" class="weekly-weekday-checkbox h-4 w-4 mb-1 accent-orange-500" value="2">
                                    <span class="block text-gray-300">Tu</span>
                                </label>
                                <label class="calendar-day flex flex-col items-center justify-center cursor-pointer" title="Wednesday">
                                    <input type="checkbox" class="weekly-weekday-checkbox h-4 w-4 mb-1 accent-orange-500" value="3">
                                    <span class="block text-gray-300">We</span>
                                </label>
                                <label class="calendar-day flex flex-col items-center justify-center cursor-pointer" title="Thursday">
                                    <input type="checkbox" class="weekly-weekday-checkbox h-4 w-4 mb-1 accent-orange-500" value="4">
                                    <span class="block text-gray-300">Th</span>
                                </label>
                                <label class="calendar-day flex flex-col items-center justify-center cursor-pointer" title="Friday">
                                    <input type="checkbox" class="weekly-weekday-checkbox h-4 w-4 mb-1 accent-orange-500" value="5">
                                    <span class="block text-gray-300">Fr</span>
                                </label>
                                <label class="calendar-day flex flex-col items-center justify-center cursor-pointer" title="Saturday">
                                    <input type="checkbox" class="weekly-weekday-checkbox h-4 w-4 mb-1 accent-orange-500" value="6">
                                    <span class="block text-gray-300">Sa</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-300 mb-2 block">Days per week (info)</label>
                            <input id="weeklyCount" type="number" min="1" max="7" placeholder="e.g., 3"
                                class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:border-orange-500 transition" />
                        </div>
                    </div>

                    
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-300 mb-2 block">Category</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" data-category="health" onclick="selectCategory(this, 'health')" 
                            class="category-btn px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white hover:border-orange-500 transition text-sm">
                            💪 Health
                        </button>
                        <button type="button" data-category="mind" onclick="selectCategory(this, 'mind')" 
                            class="category-btn px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white hover:border-orange-500 transition text-sm">
                            🧠 Mind
                        </button>
                        <button type="button" data-category="work" onclick="selectCategory(this, 'work')" 
                            class="category-btn px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white hover:border-orange-500 transition text-sm">
                            💼 Work
                        </button>
                        <button type="button" data-category="social" onclick="selectCategory(this, 'social')" 
                            class="category-btn px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white hover:border-orange-500 transition text-sm">
                            👥 Social
                        </button>
                        <button type="button" data-category="hobby" onclick="selectCategory(this, 'hobby')" 
                            class="category-btn px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white hover:border-orange-500 transition text-sm">
                            🎨 Hobby
                        </button>
                        <button type="button" data-category="finance" onclick="selectCategory(this, 'finance')" 
                            class="category-btn px-3 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white hover:border-orange-500 transition text-sm">
                            💰 Finance
                        </button>
                    </div>
                </div>
                
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white py-3 rounded-lg font-semibold hover:from-orange-600 hover:to-yellow-600 transition">
                    <span id="submitBtnText">Create Habit</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Note Modal -->
    <div id="noteModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4 modal-backdrop">
        <div class="bg-gray-900 rounded-2xl p-8 max-w-md w-full border border-gray-800 fade-in">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-white">Add Note</h2>
                <button onclick="closeNoteModal()" class="text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="noteForm" class="space-y-4">
                <input type="hidden" id="noteHabitId" value="">
                
                <div>
                    <label class="text-sm font-medium text-gray-300 mb-2 block">Your Note</label>
                    <textarea id="habitNote" rows="4" placeholder="Write your note here..."
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-orange-500 transition"></textarea>
                </div>
                
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white py-3 rounded-lg font-semibold hover:from-orange-600 hover:to-yellow-600 transition">
                    Save Note
                </button>
            </form>
        </div>
    </div>

<script>
// State management
let selectedCategory = null;
let editingHabitId = null;

// Helper to parse local date (avoiding timezone issues)
function parseLocalDate(dateStr) {
    const parts = dateStr.split('-');
    return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
}
// Global variable to track current calendar month
let calendarCurrentMonth = null;

// Helper to parse date string correctly (avoiding timezone issues)
function parseLocalDate(dateStr) {
    const parts = dateStr.split('-');
    return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
}

    document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('habitForm').addEventListener('submit', handleHabitSubmit);
    document.getElementById('noteForm').addEventListener('submit', handleNoteSubmit);
    document.getElementById('addHabitBtn').addEventListener('click', openAddHabitModal);
    const freq = document.getElementById('habitFrequency');
    if (freq) {
        freq.addEventListener('change', updateFrequencyOptions);
        updateFrequencyOptions();
    }

    // Initialize calendar with current month
    calendarCurrentMonth = parseLocalDate('<?php echo $selected_date; ?>');
    renderCalendar(calendarCurrentMonth);

    // checkbox change behaviour for Weekly
    document.querySelectorAll('.weekly-weekday-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const label = this.closest('.calendar-day');
            if (!label) return;
            if (this.checked) {
                label.classList.add('bg-orange-500', 'text-white');
            } else {
                label.classList.remove('bg-orange-500', 'text-white');
            }
        });
    });
});

// Modal functions
function openAddHabitModal() {
    editingHabitId = null;
    selectedCategory = null;
    document.getElementById('modalTitle').textContent = 'Add New Habit';
    document.getElementById('submitBtnText').textContent = 'Create Habit';
    document.getElementById('habitForm').reset();
    document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('border-orange-500', 'bg-orange-500'));
    // reset frequency option UI
    document.querySelectorAll('#weeklyOptions .calendar-day').forEach(el => el.classList.remove('bg-orange-500', 'text-white'));
    document.querySelectorAll('.weekly-weekday-checkbox').forEach(cb => cb.checked = false);
    if (document.getElementById('weeklyCount')) document.getElementById('weeklyCount').value = '';
    updateFrequencyOptions();
    document.getElementById('habitModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('habitModal').classList.add('hidden');
}

function selectCategory(btn, category) {
    document.querySelectorAll('.category-btn').forEach(b => {
        b.classList.remove('border-orange-500', 'bg-orange-500');
    });
    btn.classList.add('border-orange-500', 'bg-orange-500');
    selectedCategory = category;
}

function updateFrequencyOptions() {
    const val = document.getElementById('habitFrequency').value;
    const daily = document.getElementById('dailyOptions');
    const weekly = document.getElementById('weeklyOptions');
    const custom = document.getElementById('customOptions');
    if (daily) daily.classList.toggle('hidden', val !== 'daily');
    if (weekly) weekly.classList.toggle('hidden', val !== 'weekly');
    if (custom) custom.classList.toggle('hidden', val !== 'custom');
}

function closeNoteModal() {
    document.getElementById('noteModal').classList.add('hidden');
}

// Date navigation
function changeDate(days) {
    const date = new Date('<?php echo $selected_date; ?>');
    date.setDate(date.getDate() + days);
    const dateStr = date.toISOString().split('T')[0];
    window.location.href = '?date=' + dateStr;
}

function goToToday() {
    window.location.href = '?date=' + new Date().toISOString().split('T')[0];
}

function toggleCalendar() {
    document.getElementById('miniCalendar').classList.toggle('hidden');
    if (!document.getElementById('miniCalendar').classList.contains('hidden')) {
        renderCalendar(calendarCurrentMonth);
    }
}

function changeMonth(direction) {
    // Update calendarCurrentMonth and re-render
    calendarCurrentMonth.setMonth(calendarCurrentMonth.getMonth() + direction);
    renderCalendar(calendarCurrentMonth);
}

function renderCalendar(date) {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
    
    document.getElementById('calendarMonth').textContent = monthNames[date.getMonth()] + ' ' + date.getFullYear();
    
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1).getDay();
    const daysInMonth = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    
    let html = '';
    
    // empty cells before month starts
    for (let i = 0; i < firstDay; i++) {
        html += '<div class="text-gray-600"></div>';
    }
    
    // days of month
    for (let day = 1; day <= daysInMonth; day++) {
        // Create date using local time (not UTC)
        const fullDate = new Date(date.getFullYear(), date.getMonth(), day);
        const year = fullDate.getFullYear();
        const month = String(fullDate.getMonth() + 1).padStart(2, '0');
        const dayStr = String(day).padStart(2, '0');
        const dateStr = year + '-' + month + '-' + dayStr;
        
        const isSelected = dateStr === '<?php echo $selected_date; ?>';
        const isToday = dateStr === '<?php echo $today; ?>';
        
        html += `<button onclick="window.location.href='?date=${dateStr}'" 
                    class="calendar-day py-2 rounded hover:bg-orange-500 hover:text-white transition ${isSelected ? 'bg-orange-500 text-white font-bold' : isToday ? 'border-2 border-orange-500 text-orange-500' : 'text-gray-300'}">
                    ${day}
                </button>`;
    }
    
    document.getElementById('calendarDays').innerHTML = html;
}

// Habit functions
function toggleHabit(btn, habitId) {
    const isCompleting = !btn.classList.contains('bg-green-500');
    const action = isCompleting ? 'complete' : 'uncomplete';
    
    fetch('../actions/habit-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: action,
            habit_id: habitId,
            date: '<?php echo $selected_date; ?>'
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(err => console.error('Error:', err));
}

function editHabit(habitId) {
    editingHabitId = habitId;
    document.getElementById('modalTitle').textContent = 'Edit Habit';
    document.getElementById('submitBtnText').textContent = 'Save Changes';

    // fetch habit details and prefill form
    fetch('../actions/habit-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get', habit_id: habitId })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success || !data.habit) return;
        const h = data.habit;
        document.getElementById('habitName').value = h.name || '';
        document.getElementById('habitFrequency').value = h.frequency || 'daily';
        // set category
        selectedCategory = h.category || null;
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('border-orange-500', 'bg-orange-500');
            if (btn.getAttribute('data-category') === selectedCategory) {
                btn.classList.add('border-orange-500', 'bg-orange-500');
            }
        });

        updateFrequencyOptions();

        // set weekly weekday checkboxes (if present)
        document.querySelectorAll('.weekly-weekday-checkbox').forEach(cb => {
            cb.checked = false;
            const label = cb.closest('.calendar-day');
            if (label) label.classList.remove('bg-orange-500', 'text-white');
        });
        if (h.daily_days) {
            h.daily_days.split(',').map(s => s.trim()).filter(Boolean).forEach(v => {
                const cb = Array.from(document.querySelectorAll('.weekly-weekday-checkbox')).find(x => x.value === v);
                if (cb) {
                    cb.checked = true;
                    const label = cb.closest('.calendar-day');
                    if (label) label.classList.add('bg-orange-500', 'text-white');
                }
            });
        }

        // weekly count
        if (document.getElementById('weeklyCount')) document.getElementById('weeklyCount').value = h.weekly_count || '';
        // end_date removed (custom option disabled)

        document.getElementById('habitModal').classList.remove('hidden');
    })
    .catch(err => console.error('Error fetching habit:', err));
}

function deleteHabit(habitId) {
    if (!confirm('Are you sure you want to delete this habit?')) return;
    
    fetch('../actions/habit-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'delete',
            habit_id: habitId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(err => console.error('Error:', err));
}

function handleHabitSubmit(e) {
    e.preventDefault();
    
    const habitData = {
        action: editingHabitId ? 'update' : 'create',
        habit_id: editingHabitId,
        name: document.getElementById('habitName').value,
        frequency: document.getElementById('habitFrequency').value,
        category: selectedCategory
    };
    // collect frequency-specific details
    const frequency = habitData.frequency;
    if (frequency === 'daily') {
        // daily now repeats every day; no weekday selection
        habitData.daily_days = '';
        habitData.weekly_count = 0;
    } else if (frequency === 'weekly') {
        const weeklyDaysArr = Array.from(document.querySelectorAll('.weekly-weekday-checkbox'))
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        let weeklyCountVal = parseInt(document.getElementById('weeklyCount').value);
        if (!weeklyCountVal || isNaN(weeklyCountVal)) {
            weeklyCountVal = weeklyDaysArr.length;
        }
        // If user selected more days than the numeric 'days per week', trim extras and notify
        if (weeklyCountVal > 0 && weeklyDaysArr.length > weeklyCountVal) {
            // trim to first N selected
            const trimmed = weeklyDaysArr.slice(0, weeklyCountVal);
            habitData.daily_days = trimmed.join(',');
            habitData.weekly_count = weeklyCountVal;
            alert('You selected ' + weeklyDaysArr.length + ' weekdays but Days per week is ' + weeklyCountVal + '. Extra selections were removed.');
        } else {
            habitData.daily_days = weeklyDaysArr.join(',');
            habitData.weekly_count = weeklyCountVal || weeklyDaysArr.length;
        }
        habitData.end_date = '';
    } else {
        // default: ensure fields empty
        habitData.daily_days = '';
        habitData.weekly_count = 0;
    }
    
    fetch('../actions/habit-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(habitData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeModal();
            location.reload();
        }
    })
    .catch(err => console.error('Error:', err));
}

function handleNoteSubmit(e) {
    e.preventDefault();
    
    const noteData = {
        action: 'add_note',
        habit_id: document.getElementById('noteHabitId').value,
        notes: document.getElementById('habitNote').value,
        date: '<?php echo $selected_date; ?>'
    };
    
    fetch('../actions/habit-action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(noteData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeNoteModal();
            location.reload();
        }
    })
    .catch(err => console.error('Error:', err));
}

function viewHistory() {
    window.location.href = 'history.php';
}

function viewGoals() {
    window.location.href = 'goals.php';
}

function exportData() {
    // legacy: redirect to export (kept for backward compat) -> now go to goals
    window.location.href = 'goals.php';
}

function showStats() {
    window.location.href = 'analytics.php';
}
</script>
</body>
</html>