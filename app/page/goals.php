<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: ./sign-in.php");
    exit();
}
include __DIR__ . '/../config/config.php';

$user_id = $_SESSION['id_user'];

// fetch goals
$stmt = $conn->prepare("SELECT * FROM goals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goals - ELGROW</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen">
<div class="max-w-4xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Goals</h1>
        <div class="space-x-2">
            <a href="dashboard.php" class="text-sm bg-gray-800 px-3 py-2 rounded">Back</a>
            <button onclick="openAddGoal()" class="text-sm bg-orange-500 px-3 py-2 rounded">Add Goal</button>
        </div>
    </div>

    <div class="space-y-4">
        <?php if (empty($goals)): ?>
            <div class="bg-gray-900 p-6 rounded border border-gray-800 text-center text-gray-400">No goals yet. Create one!</div>
        <?php else: ?>
            <?php foreach ($goals as $g): ?>
            <div class="bg-gray-900 p-4 rounded border border-gray-800 flex justify-between items-start">
                <div>
                    <h3 class="font-semibold <?php echo $g['is_complete'] ? 'line-through text-gray-400' : ''; ?>"><?php echo htmlspecialchars($g['title']); ?></h3>
                    <?php if (!empty($g['description'])): ?>
                        <p class="text-gray-300 text-sm mt-1"><?php echo nl2br(htmlspecialchars($g['description'])); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($g['target_date'])): ?>
                        <div class="text-gray-400 text-xs mt-2">Target: <?php echo htmlspecialchars($g['target_date']); ?></div>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col items-end space-y-2">
                    <div class="flex space-x-2">
                        <button onclick="toggleComplete(<?php echo $g['id']; ?>, <?php echo $g['is_complete'] ? '0' : '1'; ?>)" class="px-3 py-1 bg-gray-800 rounded text-sm">
                            <?php echo $g['is_complete'] ? 'Mark Open' : 'Mark Done'; ?>
                        </button>
                        <button onclick="editGoal(<?php echo $g['id']; ?>)" class="px-3 py-1 bg-gray-800 rounded text-sm">Edit</button>
                        <button onclick="deleteGoal(<?php echo $g['id']; ?>)" class="px-3 py-1 bg-red-600 rounded text-sm">Delete</button>
                    </div>
                    <div class="text-gray-400 text-xs">Created: <?php echo htmlspecialchars($g['created_at']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div id="goalModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-2xl p-6 max-w-lg w-full border border-gray-800">
        <div class="flex items-center justify-between mb-4">
            <h2 id="goalModalTitle" class="text-xl font-bold">Add Goal</h2>
            <button onclick="closeGoalModal()" class="text-gray-400">Close</button>
        </div>
        <form id="goalForm" class="space-y-3">
            <input type="hidden" id="goalId" value="">
            <div>
                <label class="text-sm text-gray-300">Title</label>
                <input id="goalTitle" required class="w-full px-3 py-2 bg-gray-800 rounded border border-gray-700" />
            </div>
            <div>
                <label class="text-sm text-gray-300">Description</label>
                <textarea id="goalDesc" rows="3" class="w-full px-3 py-2 bg-gray-800 rounded border border-gray-700"></textarea>
            </div>
            <div>
                <label class="text-sm text-gray-300">Target Date</label>
                <input id="goalDate" type="date" class="w-full px-3 py-2 bg-gray-800 rounded border border-gray-700" />
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-orange-500 px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddGoal(){
    document.getElementById('goalModalTitle').textContent = 'Add Goal';
    document.getElementById('goalId').value = '';
    document.getElementById('goalTitle').value = '';
    document.getElementById('goalDesc').value = '';
    document.getElementById('goalDate').value = '';
    document.getElementById('goalModal').classList.remove('hidden');
}
function closeGoalModal(){ document.getElementById('goalModal').classList.add('hidden'); }

document.getElementById('goalForm').addEventListener('submit', function(e){
    e.preventDefault();
    const id = document.getElementById('goalId').value || null;
    const payload = {
        action: id ? 'update' : 'create',
        goal_id: id,
        title: document.getElementById('goalTitle').value,
        description: document.getElementById('goalDesc').value,
        target_date: document.getElementById('goalDate').value
    };
    fetch('../actions/goals-action.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(payload)})
        .then(r=>r.json()).then(j=>{ if(j.success){ location.reload(); } else { alert(j.message||'Error'); } })
        .catch(()=>alert('Network error'));
});

function editGoal(id){
    // fetch goal data from server
    fetch('../actions/goals-action.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'get', goal_id:id})})
        .then(r=>r.json()).then(j=>{
            if(j.success){
                document.getElementById('goalModalTitle').textContent = 'Edit Goal';
                document.getElementById('goalId').value = j.goal.id;
                document.getElementById('goalTitle').value = j.goal.title;
                document.getElementById('goalDesc').value = j.goal.description;
                document.getElementById('goalDate').value = j.goal.target_date || '';
                document.getElementById('goalModal').classList.remove('hidden');
            } else alert(j.message||'Error');
        });
}

function deleteGoal(id){
    if(!confirm('Delete this goal?')) return;
    fetch('../actions/goals-action.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'delete', goal_id:id})})
        .then(r=>r.json()).then(j=>{ if(j.success) location.reload(); else alert(j.message||'Error'); });
}

function toggleComplete(id, val){
    fetch('../actions/goals-action.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'toggle', goal_id:id, is_complete: val})})
        .then(r=>r.json()).then(j=>{ if(j.success) location.reload(); else alert(j.message||'Error'); });
}
</script>
</body>
</html>