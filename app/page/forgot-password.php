<?php
session_start();
include __DIR__ . '/../config/config.php';

$email_error = '';
$success_message = '';
$step = 1; // Step 1: Email, Step 2: Security Question, Step 3: New Password

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Verify Email
    if (isset($_POST['verify_email'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        
        $query = mysqli_query($conn, "SELECT id_user, username, email FROM users WHERE email='$email'");
        
        if ($query && mysqli_num_rows($query) > 0) {
            $user = mysqli_fetch_assoc($query);
            $_SESSION['reset_user_id'] = $user['id_user'];
            $_SESSION['reset_email'] = $user['email'];
            $_SESSION['reset_username'] = $user['username'];
            $step = 2;
        } else {
            $email_error = "Email tidak ditemukan!";
        }
    }
    
    // Step 2: Verify Security Answer (simplified - just confirm email)
    if (isset($_POST['verify_security'])) {
        if (isset($_SESSION['reset_user_id'])) {
            $step = 3;
        } else {
            header("Location: forgot-password.php");
            exit();
        }
    }
    
    // Step 3: Reset Password
    if (isset($_POST['reset_password'])) {
        if (!isset($_SESSION['reset_user_id'])) {
            header("Location: forgot-password.php");
            exit();
        }
        
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        $user_id = $_SESSION['reset_user_id'];
        
        $is_valid = true;
        $password_error = '';
        
        if (strlen($new_password) < 8) {
            $password_error = "Kata sandi minimal 8 karakter!";
            $is_valid = false;
        }
        
        if ($new_password !== $confirm_password) {
            $password_error = "Konfirmasi kata sandi tidak cocok!";
            $is_valid = false;
        }
        
        if ($is_valid) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id_user = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                // Clear session
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_username']);
                
                // Redirect to sign-in with success message
                header("Location: sign-in.php?reset=success");
                exit();
            } else {
                $password_error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
        
        $step = 3;
    }
}

// Check if we're in step 2 or 3
if (isset($_SESSION['reset_user_id']) && !isset($_POST['verify_email'])) {
    if (isset($_POST['verify_security'])) {
        $step = 3;
    } elseif (isset($_POST['reset_password'])) {
        $step = 3;
    } else {
        $step = 2;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - ELGROW</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .fade-in { animation: fadeIn 0.8s ease-out; }
        .slide-in { animation: slideIn 0.8s ease-out; }
        
        .glass-effect {
            background: rgba(17, 24, 39, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(234, 88, 12, 0.2);
        }

        .step-indicator {
            transition: all 0.3s ease;
        }

        .step-active {
            background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%);
            transform: scale(1.1);
        }

        .step-completed {
            background: #22c55e;
        }

        .step-inactive {
            background: #374151;
        }

        /* Mobile optimizations */
        @media (max-width: 640px) {
            .mobile-compact {
                padding: 1rem;
            }
            
            .mobile-icon {
                width: 2.5rem;
                height: 2.5rem;
            }
            
            .mobile-title {
                font-size: 1.5rem;
            }
            
            .step-indicator {
                width: 2rem;
                height: 2rem;
                font-size: 0.875rem;
            }
            
            .step-line {
                width: 2rem;
            }
        }
    </style>
</head>

<body class="bg-black min-h-screen flex items-center justify-center px-3 sm:px-4 relative overflow-hidden">
    <!-- Animated background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-64 sm:w-96 h-64 sm:h-96 bg-orange-500/10 rounded-full blur-3xl top-0 left-0 animate-pulse"></div>
        <div class="absolute w-64 sm:w-96 h-64 sm:h-96 bg-yellow-500/10 rounded-full blur-3xl bottom-0 right-0 animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10 py-6 sm:py-8">
        <!-- Header -->
        <div class="text-center mb-6 sm:mb-8 fade-in">
            <a href="sign-in.php" class="inline-block mb-4 sm:mb-6">
                <h1 class="text-3xl sm:text-4xl font-bold text-orange-500">ELGROW</h1>
            </a>
            <div class="inline-block p-3 sm:p-4 bg-orange-500 bg-opacity-20 rounded-full mb-3 sm:mb-4">
                <svg class="w-10 h-10 sm:w-12 sm:h-12 mobile-icon text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h2 class="text-2xl sm:text-3xl mobile-title font-bold text-white mb-2">Lupa Kata Sandi?</h2>
            <p class="text-sm sm:text-base text-gray-400 px-4">Jangan khawatir, kami akan membantu Anda reset</p>
        </div>

        <!-- Step Indicator -->
        <div class="flex justify-center mb-6 sm:mb-8 fade-in px-2" style="animation-delay: 0.1s;">
            <div class="flex items-center space-x-2 sm:space-x-4">
                <div class="flex flex-col items-center">
                    <div class="step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-white font-bold <?php echo $step >= 1 ? ($step > 1 ? 'step-completed' : 'step-active') : 'step-inactive'; ?>">
                        <?php if ($step > 1): ?>
                            <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        <?php else: ?>
                            1
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-gray-400 mt-1 sm:mt-2">Email</span>
                </div>
                
                <div class="w-8 sm:w-12 step-line h-1 bg-gray-700 <?php echo $step >= 2 ? 'bg-orange-500' : ''; ?>"></div>
                
                <div class="flex flex-col items-center">
                    <div class="step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-white font-bold <?php echo $step >= 2 ? ($step > 2 ? 'step-completed' : 'step-active') : 'step-inactive'; ?>">
                        <?php if ($step > 2): ?>
                            <svg class="w-4 h-4 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        <?php else: ?>
                            2
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-gray-400 mt-1 sm:mt-2">Verifikasi</span>
                </div>
                
                <div class="w-8 sm:w-12 step-line h-1 bg-gray-700 <?php echo $step >= 3 ? 'bg-orange-500' : ''; ?>"></div>
                
                <div class="flex flex-col items-center">
                    <div class="step-indicator w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center text-white font-bold <?php echo $step >= 3 ? 'step-active' : 'step-inactive'; ?>">
                        3
                    </div>
                    <span class="text-xs text-gray-400 mt-1 sm:mt-2">Reset</span>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="glass-effect rounded-2xl sm:rounded-3xl p-5 sm:p-8 mobile-compact shadow-2xl slide-in">
            <?php if ($step === 1): ?>
                <!-- Step 1: Email Verification -->
                <form method="POST" class="space-y-5 sm:space-y-6">
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input type="email" name="email" required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                placeholder="Masukkan email Anda"
                                class="w-full pl-10 sm:pl-12 pr-3 sm:pr-4 py-3 text-sm sm:text-base bg-gray-800 border <?php echo !empty($email_error) ? 'border-red-500' : 'border-gray-700'; ?> rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-orange-500 transition">
                        </div>
                        <?php if (!empty($email_error)): ?>
                        <p class="text-red-500 text-xs sm:text-sm mt-2 flex items-center">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <?php echo $email_error; ?>
                        </p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="verify_email"
                        class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white py-3 rounded-lg font-semibold text-sm sm:text-base hover:from-orange-600 hover:to-yellow-600 transition transform active:scale-95 sm:hover:scale-105">
                        Lanjutkan
                    </button>
                </form>

            <?php elseif ($step === 2): ?>
                <!-- Step 2: Security Verification -->
                <div class="space-y-5 sm:space-y-6">
                    <div class="bg-gray-800 rounded-lg p-3 sm:p-4 border border-gray-700">
                        <div class="flex items-center space-x-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                                <?php echo isset($_SESSION['reset_username']) ? strtoupper(substr($_SESSION['reset_username'], 0, 1)) : 'U'; ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-white font-semibold text-sm sm:text-base truncate"><?php echo htmlspecialchars($_SESSION['reset_username'] ?? 'User'); ?></p>
                                <p class="text-gray-400 text-xs sm:text-sm truncate"><?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?></p>
                            </div>
                        </div>
                        <p class="text-xs sm:text-sm text-gray-400">
                            Kami telah menemukan akun Anda. Klik tombol di bawah untuk melanjutkan reset kata sandi.
                        </p>
                    </div>

                    <form method="POST">
                        <button type="submit" name="verify_security"
                            class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white py-3 rounded-lg font-semibold text-sm sm:text-base hover:from-orange-600 hover:to-yellow-600 transition transform active:scale-95 sm:hover:scale-105">
                            Verifikasi & Lanjutkan
                        </button>
                    </form>
                </div>

            <?php elseif ($step === 3): ?>
                <!-- Step 3: Reset Password -->
                <form method="POST" class="space-y-5 sm:space-y-6">
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Kata Sandi Baru</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input type="password" name="new_password" id="new_password" required
                                oninput="checkPasswordStrength(this.value)"
                                placeholder="Min. 8 karakter"
                                class="w-full pl-10 sm:pl-12 pr-10 sm:pr-12 py-3 text-sm sm:text-base bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-orange-500 transition">
                            <button type="button" onclick="togglePassword('new_password')" 
                                class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center text-gray-400 hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Password Strength -->
                        <div class="mt-2">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-gray-400">Kekuatan:</span>
                                <span id="strengthText" class="text-xs font-medium text-gray-400">-</span>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-1">
                                <div id="strengthBar" class="h-full rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Konfirmasi Kata Sandi</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 sm:pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input type="password" name="confirm_password" id="confirm_password" required
                                oninput="checkPasswordMatch()"
                                placeholder="Ulangi kata sandi"
                                class="w-full pl-10 sm:pl-12 pr-10 sm:pr-12 py-3 text-sm sm:text-base bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-orange-500 transition">
                            <button type="button" onclick="togglePassword('confirm_password')" 
                                class="absolute inset-y-0 right-0 pr-3 sm:pr-4 flex items-center text-gray-400 hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <p id="matchMessage" class="text-xs sm:text-sm mt-2 hidden"></p>
                        <?php if (isset($password_error)): ?>
                        <p class="text-red-500 text-xs sm:text-sm mt-2"><?php echo $password_error; ?></p>
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="reset_password"
                        class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white py-3 rounded-lg font-semibold text-sm sm:text-base hover:from-orange-600 hover:to-yellow-600 transition transform active:scale-95 sm:hover:scale-105 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Reset Kata Sandi</span>
                    </button>
                </form>
            <?php endif; ?>

            <!-- Back to Sign In -->
            <div class="text-center mt-5 sm:mt-6">
                <a href="sign-in.php" class="text-gray-400 hover:text-orange-500 transition text-xs sm:text-sm inline-flex items-center space-x-2">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Kembali ke Sign In</span>
                </a>
            </div>
        </div>

        <!-- Info Box -->
        <?php if ($step === 1): ?>
        <div class="mt-4 sm:mt-6 bg-gray-900 border border-gray-800 rounded-xl p-3 sm:p-4 fade-in" style="animation-delay: 0.3s;">
            <div class="flex items-start space-x-2 sm:space-x-3">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-xs sm:text-sm text-gray-300">
                        Masukkan email yang terdaftar di akun ELGROW Anda. Kami akan membantu Anda mereset kata sandi.
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
        }

        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
            const texts = ['Sangat Lemah', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];
            const textColors = ['text-red-500', 'text-orange-500', 'text-yellow-500', 'text-blue-500', 'text-green-500'];
            
            const percentage = (strength / 5) * 100;
            strengthBar.style.width = percentage + '%';
            strengthBar.className = 'h-full rounded-full transition-all duration-300 ' + colors[strength - 1];
            strengthText.textContent = texts[strength - 1] || '-';
            strengthText.className = 'text-xs font-medium ' + (textColors[strength - 1] || 'text-gray-400');
        }

        function checkPasswordMatch() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchMessage = document.getElementById('matchMessage');
            
            if (confirmPassword.length > 0) {
                if (newPassword === confirmPassword) {
                    matchMessage.textContent = '✓ Kata sandi cocok';
                    matchMessage.className = 'text-green-500 text-xs sm:text-sm mt-2';
                    matchMessage.classList.remove('hidden');
                } else {
                    matchMessage.textContent = '✗ Kata sandi tidak cocok';
                    matchMessage.className = 'text-red-500 text-xs sm:text-sm mt-2';
                    matchMessage.classList.remove('hidden');
                }
            } else {
                matchMessage.classList.add('hidden');
            }
        }
    </script>
</body>
</html>