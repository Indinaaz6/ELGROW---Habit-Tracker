<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELGROW - Build Better Habits</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .fade-in {
            animation: fadeIn 1s ease-out;
        }
        
        .slide-up {
            animation: fadeIn 1.2s ease-out;
        }
        
        .bounce-in {
            animation: fadeIn 1.4s ease-out;
        }
        
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(234, 88, 12, 0.3);
        }
        
        .bg-grid {
            background-image: 
                linear-gradient(rgba(234, 88, 12, 0.1) 1px, transparent 1px),
                linear-gradient(90deg, rgba(234, 88, 12, 0.1) 1px, transparent 1px);
            background-size: 50px 50px;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #ea580c 0%, #fbbf24 50%, #ea580c 100%);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient 3s ease infinite;
        }
        
        @keyframes gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        
        .mobile-menu {
            display: none;
        }
        
        .mobile-menu.active {
            display: block;
        }
    </style>
</head>

<body class="text-gray-900 font-sans overflow-x-hidden" style="background-color:rgb(22, 22, 22);">
    <!-- Navbar -->
    <nav class="bg-black shadow-lg border-b border-gray-800 sticky top-0 z-50 backdrop-blur-md bg-opacity-90">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="#" class="text-2xl font-bold text-orange-500 hover:text-orange-400 transition duration-300">ELGROW</a>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="../app/page/sign-in.php" class="text-gray-300 hover:text-orange-500 px-3 py-2 text-sm font-medium transition duration-300">Sign In</a>
                    <a href="../app/page/sign-up.php" class="bg-gradient-to-r from-orange-600 to-yellow-500 text-white px-6 py-2 rounded-full hover:from-orange-700 hover:to-yellow-600 transition duration-300 shadow-lg transform hover:scale-105">Get Started</a>
                </div>
                <button id="mobile-menu-btn" class="md:hidden text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
            <div id="mobile-menu" class="mobile-menu md:hidden pb-4">
                <a href="#features" class="block text-gray-300 hover:text-orange-500 px-3 py-2 text-sm font-medium">Features</a>
                <a href="#about" class="block text-gray-300 hover:text-orange-500 px-3 py-2 text-sm font-medium">About</a>
                <a href="#contact" class="block text-gray-300 hover:text-orange-500 px-3 py-2 text-sm font-medium">Contact</a>
                <a href="sign-in.php" class="block text-gray-300 hover:text-orange-500 px-3 py-2 text-sm font-medium">Sign In</a>
            </div>
        </div>
    </nav>  

    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center relative overflow-hidden bg-grid">
        <div class="absolute inset-0 bg-gradient-to-b from-orange-900/20 to-transparent"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 py-12">
            <h1 class="text-5xl md:text-7xl font-extrabold gradient-text mb-6 fade-in">
                Grow Better Habits,<br><span class="text-white">One Day at a Time</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-300 mb-8 max-w-3xl mx-auto slide-up leading-relaxed">
                Track your habits, build unstoppable streaks, and achieve your goals with our intuitive, gamified habit tracker. Join a community of achievers transforming their lives.
            </p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4 bounce-in">
                <a href="../app/page/sign-up.php" class="bg-gradient-to-r from-orange-600 to-yellow-500 text-white px-8 py-4 rounded-full text-lg font-semibold hover:from-orange-700 hover:to-yellow-600 transition duration-300 shadow-xl transform hover:scale-105">Try Now </a>
            </div>
        </div>
    </section>

    <!-- Demo Section -->
    <section class="py-24 bg-gray-900 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-t from-gray-950 to-transparent"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <div class="bg-gradient-to-r from-orange-600/20 to-yellow-600/20 rounded-2xl p-8 backdrop-blur-sm border border-orange-500/30">
                <video autoplay loop muted class="rounded-lg shadow-2xl w-full mb-4">
                    <source src="./video/demo.mp4" type="video/mp4">
                </video>
                <p class="text-gray-400 mt-6 text-sm">Live Demo Dashboard</p>
            </div>
            <h2 class="text-4xl md:text-5xl font-bold text-white mt-12 mb-6 fade-in">Ready to Transform Your Habits?</h2>
            <p class="text-xl text-gray-300 mb-8 slide-up">Join thousands of successful users. Start your free trial today and see the difference.</p>
            <a href="../app/page/sign-up.php" class="inline-block bg-gradient-to-r from-orange-600 to-yellow-500 text-white px-8 py-4 rounded-full text-lg font-semibold hover:from-orange-700 hover:to-yellow-600 transition duration-300 shadow-xl transform hover:scale-105">Start Your Journey</a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-gray-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-white mb-4 fade-in">
                    Why Choose <span class="gradient-text">ELGROW</span>?
                </h2>
                <p class="text-xl text-gray-300 slide-up">Powerful, user-friendly features designed to make habit-building fun and effective.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 p-8 rounded-2xl shadow-lg hover-lift text-center border border-gray-700">
                    <div class="w-20 h-20 bg-gradient-to-r from-orange-600 to-yellow-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4 text-white">Effortless Tracking</h3>
                    <p class="text-gray-300 leading-relaxed">Log habits with one tap. Visualize progress with stunning charts and heatmaps that motivate you to keep going.</p>
                </div>
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 p-8 rounded-2xl shadow-lg hover-lift text-center border border-gray-700">
                    <div class="w-20 h-20 bg-gradient-to-r from-yellow-500 to-yellow-300 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <svg class="w-10 h-10 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4 text-white">Streak Mastery</h3>
                    <p class="text-gray-300 leading-relaxed">Build epic streaks with gamification. Get reminders, celebrate milestones, and never lose momentum.</p>
                </div>
                <div class="bg-gradient-to-br from-gray-900 to-gray-800 p-8 rounded-2xl shadow-lg hover-lift text-center border border-gray-700">
                    <div class="w-20 h-20 bg-gradient-to-r from-yellow-300 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4 text-white">Fully Customizable</h3>
                    <p class="text-gray-300 leading-relaxed">Tailor habits to your life. Set personalized goals, categories, and get AI-powered insights for better results.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-black text-white py-16 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4 text-orange-500">ELGROW</h3>
                    <p class="text-gray-400 leading-relaxed">Aplikasi pelacakan kebiasaan yang membantu Anda membangun rutinitas positif, melacak progres harian, dan mencapai tujuan hidup dengan cara yang menyenangkan dan terstruktur.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Tautan Cepat</h4>
                    <ul class="space-y-2">
                        <li><a href="#about" class="text-gray-400 hover:text-orange-500 transition">Tentang Kami</a></li>
                        <li><a href="#features" class="text-gray-400 hover:text-orange-500 transition">Layanan</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact</h4>
                    <ul class="space-y-2">
                        <li class="text-gray-400 flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>support@elgrow.com</span>
                        </li>
                        <li class="text-gray-400 flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span>+62 812-3456-7890</span>
                        </li>   
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Follow Us</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-orange-500 transition transform hover:scale-110">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-orange-500 transition transform hover:scale-110">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-orange-500 transition transform hover:scale-110">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400">&copy; 2024 ELGROW. All rights reserved. Made with ❤️ for habit builders.</p>
            </div>
        </div>
    </footer>
    
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    mobileMenu.classList.remove('active');
                }
            });
        });
    </script>
</body>

</html>