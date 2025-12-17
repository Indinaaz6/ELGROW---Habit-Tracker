<?php
session_start();
include __DIR__ . '/../config/config.php';

$email_error = '';
$password_error = '';

if (isset($_POST['Sign-In'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if ($query && mysqli_num_rows($query) > 0) {
        $data = mysqli_fetch_assoc($query);
        if (isset($data['password']) && password_verify($password, $data['password'])) {
            $_SESSION['id_user'] = $data['id_user'];
            $_SESSION['email'] = $data['email'];
            $_SESSION['username'] = $data['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $password_error = "Password salah!";
        }
    } else {
        $email_error = "Email tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - ELGROW</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(234, 88, 12, 0.3);
            }

            50% {
                box-shadow: 0 0 40px rgba(234, 88, 12, 0.6);
            }
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
                radial-gradient(circle at 20% 50%, rgba(234, 88, 12, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(251, 191, 36, 0.1) 0%, transparent 50%);
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
    </style>
</head>

<body class="bg-black min-h-screen flex items-center justify-center px-4 bg-pattern relative overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute w-96 h-96 bg-orange-500/10 rounded-full blur-3xl top-0 left-0 animate-pulse"></div>
        <div class="absolute w-96 h-96 bg-yellow-500/10 rounded-full blur-3xl bottom-0 right-0 animate-pulse" style="animation-delay: 1s;"></div>
    </div>

    <div class="w-full max-w-6xl flex items-center justify-center gap-12 relative z-10">
        <!-- Left Side - Branding -->
        <div class="hidden lg:block flex-1 slide-in">
            <div class="space-y-8">
                <div>
                    <h1 class="text-6xl font-extrabold mb-4">
                        <span class="text-white">Welcome to</span><br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 via-yellow-400 to-orange-600">
                            ELGROW
                        </span>
                    </h1>
                    <p class="text-xl text-gray-400 leading-relaxed">
                        Transform your daily routine into powerful habits. Track progress, build streaks, and achieve your goals with our intuitive platform.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-yellow-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg">Track Your Progress</h3>
                            <p class="text-gray-400">Visualize your journey with beautiful charts and analytics</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg">Build Streaks</h3>
                            <p class="text-gray-400">Stay motivated with gamification and milestone rewards</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-600 to-yellow-400 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg">Join Community</h3>
                            <p class="text-gray-400">Connect with thousands of achievers worldwide</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Sign In Form -->
        <div class="w-full lg:w-auto lg:min-w-[480px] fade-in">
            <div class="glass-effect rounded-3xl p-10 shadow-2xl">
                <!-- Logo for mobile -->
                <div class="text-center mb-8 lg:hidden">
                    <h1 class="text-3xl font-bold">
                        Welcome to <span class="text-orange-500">ELGROW</span>
                    </h1>
                    <p class="text-gray-400 mt-2">Your journey to better habits starts here</p>
                </div>

                <!-- Desktop header -->
                <div class="hidden lg:block text-center mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2">Sign In</h2>
                    <p class="text-gray-400">Continue your habit-building journey</p>
                </div>

                <form id="signinForm" method="post" action="sign-in.php" class="space-y-6">
                    <!-- Email Input -->
                    <div class="relative">
                        <label for="email" class="text-sm font-medium text-gray-300 mb-2 block">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input
                                type="text"
                                name="email"
                                id="email"
                                required
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                placeholder="<?php echo $email_error ?: '     Masukkan Email'; ?>"
                                class="w-full px-4 py-2.5 bg-white/15 border <?php echo !empty($email_error) ? 'border-red-500 placeholder-red-500' : 'border-white/25'; ?> text-white rounded-lg focus:ring-2 focus:ring-white/40 focus:border-white outline-none placeholder-white/60 autofill:bg-white/15 autofill:text-white"
                                style="
                                    -webkit-text-fill-color: white;
                                    transition: background-color 5000s ease-in-out 0s;
                                " />
                            <?php if (!empty($email_error)): ?>
                                <div class="text-red-500 text-sm mt-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <?php echo $email_error; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="relative">
                        <label for="password" class="text-sm font-medium text-gray-300 mb-2 block">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                placeholder="<?php echo $password_error ?: '     Masukkan password'; ?>"
                                class="w-full px-4 py-2.5 bg-white/15 border <?php echo !empty($password_error) ? 'border-red-500 placeholder-red-500' : 'border-white/25'; ?> text-white rounded-lg focus:ring-2 focus:ring-white/40 focus:border-white outline-none placeholder-white/60 autofill:bg-white/15 autofill:text-white"
                                style="
                                    -webkit-text-fill-color: white;
                                    transition: background-color 5000s ease-in-out 0s;
                                " />
                            <?php if (!empty($password_error)): ?>
                                <div class="text-red-500 text-sm mt-2 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <?php echo $password_error; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 text-orange-500 border-gray-600 rounded focus:ring-orange-500 focus:ring-2 bg-gray-800">
                            <span class="text-sm text-gray-400">Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="text-sm text-orange-500 hover:text-orange-400 transition duration-300 font-medium">
                            Forgot password?
                        </a>
                    </div>

                    <!-- Sign In Button -->
                    <button
                        type="submit"
                        name="Sign-In"
                        class="w-full bg-gradient-to-r from-orange-500 to-yellow-500 text-white py-4 rounded-xl font-semibold hover:from-orange-600 hover:to-yellow-600 transition duration-300 transform hover:scale-[1.02] shadow-lg btn-glow shine">
                        Sign In
                    </button>

                    <!-- Sign Up Link -->
                    <div class="text-center pt-4">
                        <span class="text-gray-400">Don't have an account? </span>
                        <a href="sign-up.php" class="text-orange-500 hover:text-orange-400 font-semibold transition duration-300">
                            Sign up for free
                        </a>
                    </div>
                </form> <br>

                <!-- Success Message After Password Reset -->
<?php if (isset($_GET['reset']) && $_GET['reset'] === 'success'): ?>
<div class="bg-green-500 bg-opacity-20 border border-green-500 text-green-400 px-6 py-4 rounded-xl mb-6 fade-in flex items-center space-x-3">
    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <div>
        <p class="font-semibold">Kata Sandi Berhasil Direset!</p>
        <p class="text-sm">Silakan login dengan kata sandi baru Anda.</p>
    </div>
</div>
<?php endif; ?>

<!-- Logout Success Message -->
<?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
<div class="bg-blue-500 bg-opacity-20 border border-blue-500 text-blue-400 px-6 py-4 rounded-xl mb-6 fade-in flex items-center space-x-3">
    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
    </svg>
    <div>
        <p class="font-semibold">Berhasil Logout</p>
        <p class="text-sm">Anda telah keluar dari akun. Terima kasih!</p>
    </div>
</div>
<?php endif; ?>
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
    </div>

    <script>
        // Toggle password visibility (safe check)
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
            });
        }
    </script>
</body>

</html>