<?php
/**
 * Homepage / Landing Page
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$flash = getFlash();
$isLoggedIn = isLoggedIn();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-pattern {
            background-color: #667eea;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .slide-up {
            animation: slideUp 0.6s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <span class="text-white font-bold text-xl">Aplikasi Gallery</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-4">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">
                            Galeri
                        </a>
                        <a href="<?php echo baseUrl('pages/album/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">
                            Album
                        </a>
                        <a href="<?php echo baseUrl('pages/profile/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">
                            Profil
                        </a>
                        <div class="flex items-center space-x-3">
                            <span class="text-white/80">
                                <strong class="text-white"><?php echo e($user['nama_lengkap']); ?></strong>
                                <?php if (isAdmin()): ?>
                                    <span class="ml-1 px-2 py-0.5 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">Admin</span>
                                <?php endif; ?>
                            </span>
                            <a href="<?php echo baseUrl('actions/auth/logout_action.php'); ?>" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl transition-all">
                                Keluar
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo baseUrl('pages/auth/login.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">
                            Masuk
                        </a>
                        <a href="<?php echo baseUrl('pages/auth/register.php'); ?>" class="px-4 py-2 bg-white text-purple-600 font-semibold rounded-xl hover:bg-gray-100 transition-all">
                            Daftar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-pattern gradient-bg text-white py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Text Content -->
                <div class="slide-up">
                    <h1 class="text-4xl lg:text-6xl font-bold leading-tight mb-6">
                        Selamat Datang di <br>
                        <span class="text-yellow-300">Aplikasi Gallery</span>
                    </h1>
                    <p class="text-xl text-white/80 mb-8 leading-relaxed">
                        Aplikasi ini berfungsi mirip seperti Pinterest dimana anda dapat menambahkan foto favorit anda dan menambahkan album untuk foto favorit anda.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <?php if ($isLoggedIn): ?>
                            <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="px-8 py-4 bg-white text-purple-600 font-bold rounded-2xl hover:bg-gray-100 transition-all transform hover:scale-105 shadow-xl">
                                Lihat Galeri
                            </a>
                            <a href="<?php echo baseUrl('pages/gallery/upload.php'); ?>" class="px-8 py-4 bg-white/20 text-white font-semibold rounded-2xl hover:bg-white/30 transition-all border-2 border-white/30">
                                Upload Foto
                            </a>
                        <?php else: ?>
                            <a href="<?php echo baseUrl('pages/auth/register.php'); ?>" class="px-8 py-4 bg-white text-purple-600 font-bold rounded-2xl hover:bg-gray-100 transition-all transform hover:scale-105 shadow-xl">
                                Daftar
                            </a>
                            <a href="<?php echo baseUrl('pages/auth/login.php'); ?>" class="px-8 py-4 bg-white/20 text-white font-semibold rounded-2xl hover:bg-white/30 transition-all border-2 border-white/30">
                                Masuk
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Image/Illustration -->
                <div class="hidden lg:block">
                    <div class="relative floating">
                        <div class="absolute -top-4 -left-4 w-72 h-72 bg-purple-400/30 rounded-3xl transform rotate-6"></div>
                        <div class="absolute -bottom-4 -right-4 w-72 h-72 bg-indigo-400/30 rounded-3xl transform -rotate-6"></div>
                        <div class="relative bg-white/10 backdrop-blur-sm rounded-3xl p-6 border border-white/20">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="aspect-square bg-gradient-to-br from-pink-400 to-purple-500 rounded-2xl"></div>
                                <div class="aspect-square bg-gradient-to-br from-blue-400 to-indigo-500 rounded-2xl"></div>
                                <div class="aspect-square bg-gradient-to-br from-green-400 to-teal-500 rounded-2xl"></div>
                                <div class="aspect-square bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-3 mb-4 md:mb-0">
                    <div class="w-10 h-10 bg-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span class="font-bold text-xl">Aplikasi Gallery</span>
                </div>
                <p class="text-gray-400 text-sm">
                    &copy; <?php echo date('Y'); ?> Aplikasi Gallery. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
