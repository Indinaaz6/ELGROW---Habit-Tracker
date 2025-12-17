<?php
session_start();
include __DIR__ . '/../config/config.php';

$signup_errors = [];
$fullname = '';
$email = '';
$terms_checked = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = isset($_POST['fullname']) ? mysqli_real_escape_string($conn, $_POST['fullname']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($conn, $_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';
    $terms_checked = isset($_POST['terms']);

    if (empty($fullname) || empty($email) || empty($password) || empty($confirm)) {
        $signup_errors[] = 'Semua field harus diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signup_errors[] = 'Format email tidak valid.';
    } elseif ($password !== $confirm) {
        $signup_errors[] = 'Password dan konfirmasi tidak cocok.';
    } else {
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='" . mysqli_real_escape_string($conn, $email) . "'");
        if ($check && mysqli_num_rows($check) > 0) {
            $signup_errors[] = 'Email sudah terdaftar.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $hash = mysqli_real_escape_string($conn, $hash);
            $fullname_safe = mysqli_real_escape_string($conn, $fullname);
            
            $insert_sql = "INSERT INTO users (username, email, password) VALUES ('" . $fullname_safe . "', '" . mysqli_real_escape_string($conn, $email) . "', '" . $hash . "')";

            if (!mysqli_query($conn, $insert_sql)) {
                $err = mysqli_error($conn);
                $signup_errors[] = 'Gagal membuat akun: ' . $err;
                $log = "[" . date('Y-m-d H:i:s') . "] INSERT FAILED:\nQuery: " . $insert_sql . "\nError: " . $err . "\n\n";
                @file_put_contents(__DIR__ . '/signup-debug.log', $log, FILE_APPEND);
            }

            if (empty($signup_errors)) {
                header('Location: sign-in.php');
                exit();
            }
        }
    }
}

?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - ELGROW</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(234, 88, 12, 0.3); }
            50% { box-shadow: 0 0 40px rgba(234, 88, 12, 0.6); }
        }
        
        @keyframes progress {
            from { transform: scaleX(0); }
            to { transform: scaleX(1); }
        }
        
        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }
        
        .slide-in {
            animation: slideIn 0.8s ease-out;
        }
        
        .input-glow:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.3);
            border-color: #ea580c;
        }
        
        .btn-glow:hover {
            animation: glow 2s ease-in-out infinite;
        }
        
        .bg-pattern {
            background-image: 
                radial-gradient(circle at 80% 20%, rgba(234, 88, 12, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 20% 80%, rgba(251, 191, 36, 0.1) 0%, transparent 50%);
        }
        
        .glass-effect {
            background: rgba(17, 24, 39, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(234, 88, 12, 0.2);
        }
        
        .shine {
            position: relative;
            overflow: hidden;
        }
        
        .shine::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .shine:hover::before {
            left: 100%;
        }
        
        .strength-bar-bg {
            height: 6px;
            background: #374151;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            transform-origin: left;
        }
        
        .checkmark {
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s ease;
        }
        
        .checkmark.show {
            opacity: 1;
            transform: scale(1);
        }
    </style>
</head>

<body class="bg-black min-h-screen flex items-center justify-center px-4 py-8 bg-pattern relative overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-96 h-96 bg-yellow-500/10 rounded-full blur-3xl top-0 right-0 animate-pulse"></div>
        <div class="absolute w-96 h-96 bg-orange-500/10 rounded-full blur-3xl bottom-0 left-0 animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="w-full max-w-6xl flex items-center justify-center gap-12 relative z-10">
        <!-- Left Side - Sign Up Form -->
        <div class="w-full lg:w-auto lg:min-w-[520px] fade-in">
            <div class="glass-effect rounded-3xl p-10 shadow-2xl">

                <!-- Header -->
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2">Create Your Account</h2>
                    <p class="text-gray-400">Join thousands building better habits</p>
                </div>

                <?php if (!empty($signup_errors)): ?>
                    <div class="mb-4">
                        <ul class="text-sm text-red-400">
                            <?php foreach ($signup_errors as $err): ?>
                                <li>- <?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form id="signupForm" method="post" action="sign-up.php" class="space-y-5">
                    <!-- Full Name Input -->
                    <div class="relative">
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Full Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                id="fullname"
                                name="fullname"
                                placeholder="John Doe" 
                                required
                                value="<?php echo htmlspecialchars($fullname); ?>"
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 input-glow transition duration-300"
                            >
                        </div>
                    </div>

                    <!-- Email Input -->
                    <div class="relative">
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input 
                                type="email" 
                                id="email"
                                name="email"
                                placeholder="your.email@example.com" 
                                required
                                value="<?php echo htmlspecialchars($email); ?>"
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 input-glow transition duration-300"
                            >
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="relative">
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="password"
                                name="password"
                                placeholder="Create a strong password" 
                                required
                                minlength="8"
                                class="w-full pl-12 pr-12 py-3.5 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 input-glow transition duration-300"
                            >
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <svg class="w-5 h-5 text-gray-400 hover:text-gray-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="strength-bar-bg mt-3">
                            <div id="strengthBar" class="strength-bar" style="width: 0%; background: #ef4444;"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <p id="strengthText" class="text-xs text-gray-400"></p>
                            <div class="flex space-x-1">
                                <span id="req1" class="text-xs text-gray-500">8+ chars</span>
                                <span class="text-xs text-gray-600">•</span>
                                <span id="req2" class="text-xs text-gray-500">A-Z</span>
                                <span class="text-xs text-gray-600">•</span>
                                <span id="req3" class="text-xs text-gray-500">0-9</span>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="relative">
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <input 
                                type="password" 
                                id="confirmPassword"
                                name="confirmPassword"
                                placeholder="Confirm your password" 
                                required
                                class="w-full pl-12 pr-12 py-3.5 bg-gray-800 border border-gray-700 rounded-xl text-white placeholder-gray-500 input-glow transition duration-300"
                            >
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                                <svg id="matchIcon" class="w-5 h-5 text-green-500 checkmark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        <p id="matchText" class="text-xs mt-2 hidden"></p>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start space-x-3 pt-2">
                        <input 
                            type="checkbox" 
                            id="terms"
                            name="terms"
                            required
                            <?php echo $terms_checked ? 'checked' : ''; ?>
                            class="mt-1 w-5 h-5 text-orange-500 border-gray-600 rounded focus:ring-orange-500 focus:ring-2 bg-gray-800 cursor-pointer"
                        >
                        <label for="terms" class="text-sm text-gray-400 cursor-pointer">
                            I agree to the <a href="#" class="text-orange-500 hover:text-orange-400 font-medium">Terms & Conditions</a> and <a href="#" class="text-orange-500 hover:text-orange-400 font-medium">Privacy Policy</a>
                        </label>
                    </div>

                    <!-- Create Account Button -->
                    <button 
                        type="submit"
                        class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white py-4 rounded-xl font-semibold hover:from-orange-600 hover:to-yellow-600 transition duration-300 transform hover:scale-[1.02] shadow-lg btn-glow shine mt-6"
                    >
                        Create Account
                    </button>

                    <!-- Sign In Link -->
                    <div class="text-center pt-4">
                        <span class="text-gray-400">Already have an account? </span>
                        <a href="sign-in.php" class="text-orange-500 hover:text-orange-400 font-semibold transition duration-300">
                            Sign in
                        </a>
                    </div>
                </form>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-6">
                <a href="../../public/index.php" class="text-gray-400 hover:text-orange-500 transition duration-300 text-sm inline-flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Back to Home</span>
                </a>
            </div>
        </div>

        <!-- Right Side - Benefits -->
        <div class="hidden lg:block flex-1 slide-in">
            <div class="space-y-8">
                <div>
                    <h1 class="text-5xl font-extrabold mb-4 text-white">
                        Start Your Journey<br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 via-yellow-400 to-orange-600">
                            Today
                        </span>
                    </h1>
                    <p class="text-xl text-gray-400 leading-relaxed">
                        Join our community and unlock powerful features to transform your habits and achieve lasting success.
                    </p>
                </div>
                
                <div class="space-y-6">
                    <div class="flex items-start space-x-4 bg-gray-900/50 p-6 rounded-2xl border border-gray-800">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg mb-1">Daily Reminders</h3>
                            <p class="text-gray-400">Never miss a habit with smart notifications tailored to your schedule</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 bg-gray-900/50 p-6 rounded-2xl border border-gray-800">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg mb-1">Progress Analytics</h3>
                            <p class="text-gray-400">Detailed insights and visualizations to track your growth over time</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 bg-gray-900/50 p-6 rounded-2xl border border-gray-800">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg mb-1">Achievement Rewards</h3>
                            <p class="text-gray-400">Earn badges and unlock special features as you hit milestones</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-orange-500/20 to-yellow-500/20 p-8 rounded-2xl border border-orange-500/30">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="flex -space-x-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-red-500 border-2 border-gray-900"></div>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 border-2 border-gray-900"></div>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-cyan-500 border-2 border-gray-900"></div>
                        </div>
                        <span class="text-orange-400 font-bold text-lg">10,000+ Users</span>
                    </div>
                    <p class="text-white font-medium mb-2">"ELGROW helped me build consistent habits that transformed my life!"</p>
                    <p class="text-gray-400 text-sm">- Sarah K., Premium Member</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const matchText = document.getElementById('matchText');
        const matchIcon = document.getElementById('matchIcon');
        const req1 = document.getElementById('req1');
        const req2 = document.getElementById('req2');
        const req3 = document.getElementById('req3');
        const togglePassword = document.getElementById('togglePassword');

        // Toggle password visibility
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        });

        // Password strength checker
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            
            if (hasLength) {
                strength += 33;
                req1.classList.remove('text-gray-500');
                req1.classList.add('text-green-400');
            } else {
                req1.classList.remove('text-green-400');
                req1.classList.add('text-gray-500');
            }
            
            if (hasUpper) {
                strength += 33;
                req2.classList.remove('text-gray-500');
                req2.classList.add('text-green-400');
            } else {
                req2.classList.remove('text-green-400');
                req2.classList.add('text-gray-500');
            }
            
            if (hasNumber) {
                strength += 34;
                req3.classList.remove('text-gray-500');
                req3.classList.add('text-green-400');
            } else {
                req3.classList.remove('text-green-400');
                req3.classList.add('text-gray-500');
            }
            
            strengthBar.style.width = strength + '%';
            
            if (strength <= 33) {
                strengthBar.style.background = '#ef4444';
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-xs text-red-400';
            } else if (strength <= 66) {
                strengthBar.style.background = '#f59e0b';
                strengthText.textContent = 'Good';
                strengthText.className = 'text-xs text-yellow-400';
            } else {
                strengthBar.style.background = '#10b981';
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-xs text-green-400';
            }
        });

        // Password match checker
        confirmPasswordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const confirmPassword = this.value;
            
            if (confirmPassword === '') {
                matchText.classList.add('hidden');
                matchIcon.classList.remove('show');
            } else if (password === confirmPassword) {
                matchText.textContent = '✓ Passwords match';
                matchText.className = 'text-xs text-green-400 mt-2';
                matchIcon.classList.add('show');
            } else {
                matchText.textContent = '✗ Passwords do not match';
                matchText.className = 'text-xs text-red-400 mt-2';
                matchIcon.classList.remove('show');
            }
        });

        // Form submission: client-side validation, allow normal POST
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            // allow form to submit to server (POST)
            return true;
        });
    </script>
</body>

</html>