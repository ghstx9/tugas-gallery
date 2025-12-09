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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TugasGallery - Share Your Moments</title>
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
                        <span class="text-white font-bold text-xl">TugasGallery</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-4">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">
                            Gallery
                        </a>
                        <div class="flex items-center space-x-3">
                            <span class="text-white/80">
                                Hello, <strong class="text-white"><?php echo e($user['nama_lengkap']); ?></strong>
                                <?php if (isAdmin()): ?>
                                    <span class="ml-1 px-2 py-0.5 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">Admin</span>
                                <?php endif; ?>
                            </span>
                            <a href="<?php echo baseUrl('actions/auth/logout_action.php'); ?>" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl transition-all">
                                Logout
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo baseUrl('pages/auth/login.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">
                            Login
                        </a>
                        <a href="<?php echo baseUrl('pages/auth/register.php'); ?>" class="px-4 py-2 bg-white text-purple-600 font-semibold rounded-xl hover:bg-gray-100 transition-all">
                            Sign Up
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
                        Share Your <br>
                        <span class="text-yellow-300">Beautiful Moments</span>
                    </h1>
                    <p class="text-xl text-white/80 mb-8 leading-relaxed">
                        TugasGallery is your personal space to upload, organize, and share your favorite photos with the world. Create albums, get likes, and connect through images.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <?php if ($isLoggedIn): ?>
                            <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="px-8 py-4 bg-white text-purple-600 font-bold rounded-2xl hover:bg-gray-100 transition-all transform hover:scale-105 shadow-xl">
                                Browse Gallery
                            </a>
                            <a href="<?php echo baseUrl('pages/gallery/upload.php'); ?>" class="px-8 py-4 bg-white/20 text-white font-semibold rounded-2xl hover:bg-white/30 transition-all border-2 border-white/30">
                                Upload Photo
                            </a>
                        <?php else: ?>
                            <a href="<?php echo baseUrl('pages/auth/register.php'); ?>" class="px-8 py-4 bg-white text-purple-600 font-bold rounded-2xl hover:bg-gray-100 transition-all transform hover:scale-105 shadow-xl">
                                Get Started Free
                            </a>
                            <a href="<?php echo baseUrl('pages/auth/login.php'); ?>" class="px-8 py-4 bg-white/20 text-white font-semibold rounded-2xl hover:bg-white/30 transition-all border-2 border-white/30">
                                Sign In
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

    <!-- Features Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-4">Why Choose TugasGallery?</h2>
                <p class="text-gray-500 text-lg max-w-2xl mx-auto">Everything you need to store, organize, and share your precious memories in one place.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Easy Upload</h3>
                    <p class="text-gray-500">Upload your photos with just a few clicks. Support for JPG, PNG, GIF, and WebP formats.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Organize in Albums</h3>
                    <p class="text-gray-500">Create custom albums to organize your photos. Keep your memories neatly categorized.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow">
                    <div class="w-14 h-14 bg-pink-100 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Likes & Comments</h3>
                    <p class="text-gray-500">Engage with the community. Like and comment on photos you love.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <?php if (!$isLoggedIn): ?>
    <section class="gradient-bg py-20">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">Ready to Start Sharing?</h2>
            <p class="text-white/80 text-lg mb-8">Join TugasGallery today and start uploading your favorite photos.</p>
            <a href="<?php echo baseUrl('pages/auth/register.php'); ?>" class="inline-block px-8 py-4 bg-white text-purple-600 font-bold rounded-2xl hover:bg-gray-100 transition-all transform hover:scale-105 shadow-xl">
                Create Free Account
            </a>
        </div>
    </section>
    <?php endif; ?>

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
                    <span class="font-bold text-xl">TugasGallery</span>
                </div>
                <p class="text-gray-400 text-sm">
                    &copy; <?php echo date('Y'); ?> TugasGallery. All rights reserved.
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
