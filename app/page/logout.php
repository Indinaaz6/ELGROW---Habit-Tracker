<?php
session_start();

// Jika user belum login, redirect ke sign-in
if (!isset($_SESSION['id_user'])) {
    header("Location: sign-in.php");
    exit();
}

// Proses logout jika konfirmasi diterima
if (isset($_POST['confirm_logout']) && $_POST['confirm_logout'] === 'yes') {
    // Hapus semua variabel session
    $_SESSION = array();
    
    // Hapus cookie session jika ada
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Hancurkan session
    session_destroy();
    
    // Redirect ke halaman sign-in dengan pesan sukses
    header("Location: sign-in.php?logout=success");
    exit();
}

// Get user info
$username = $_SESSION['username'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - ELGROW</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
        .slide-in { animation: slideIn 0.4s ease-out forwards; }
    </style>
</head>

<body class="bg-black min-h-screen flex items-center justify-center p-4">
    <div class="bg-gray-900 rounded-2xl p-8 max-w-md w-full border border-gray-800 slide-in">
        <!-- Icon -->
        <div class="text-center mb-6">
            <div class="inline-block p-4 bg-orange-500 bg-opacity-20 rounded-full mb-4">
                <svg class="w-16 h-16 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Keluar dari ELGROW?</h1>
            <p class="text-gray-400">Hai, <span class="text-orange-500 font-semibold"><?php echo htmlspecialchars($username); ?></span></p>
        </div>

        <!-- Message -->
        <div class="bg-gray-800 rounded-lg p-4 mb-6 border border-gray-700">
            <p class="text-gray-300 text-center">
                Apakah Anda yakin ingin keluar dari akun Anda? 
                <br><br>
                <span class="text-sm text-gray-400">Progress dan habit Anda akan tetap tersimpan dengan aman.</span>
            </p>
        </div>

        <!-- Buttons -->
        <form method="POST" class="space-y-3">
            <input type="hidden" name="confirm_logout" value="yes">
            
            <button type="submit" 
                class="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-lg font-semibold transition transform hover:scale-105 flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span>Ya, Keluar</span>
            </button>
        </form>
        
        <a href="dashboard.php" 
            class="block w-full text-center bg-gray-800 hover:bg-gray-700 text-white py-3 rounded-lg font-semibold transition mt-3">
            Batal, Tetap di Sini
        </a>

        <!-- Footer Info -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
                Anda dapat login kembali kapan saja dengan kredensial Anda
            </p>
        </div>
    </div>

    <script>
        // Prevent back button after logout
        if (window.history && window.history.pushState) {
            window.history.pushState('forward', null, './logout.php');
            window.onpopstate = function() {
                window.history.pushState('forward', null, './logout.php');
            };
        }
    </script>
</body>
</html>