<?php
/**
 * Registration Page
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Redirect if already logged in
redirectIfLoggedIn();

$flash = getFlash();

// Get old form data if validation failed
$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Aplikasi Gallery</title>
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
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl w-full max-w-md p-8 my-8">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-2xl mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Buat Akun</h1>
            <p class="text-gray-500 mt-2">untuk mengakses aplikasi ini</p>
        </div>

        <!-- Flash Messages -->
        <?php if ($flash): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form action="<?php echo baseUrl('actions/auth/register_action.php'); ?>" method="POST" class="space-y-5">
            <?php echo csrfField(); ?>
            
            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    required
                    minlength="3"
                    maxlength="50"
                    pattern="[a-zA-Z0-9_]+"
                    value="<?php echo e($old['username'] ?? ''); ?>"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none"
                    placeholder="Isi nama anda"
                >
                <p class="text-xs text-gray-400 mt-1">Hanya huruf, angka, dan garis bawah</p>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required
                    value="<?php echo e($old['email'] ?? ''); ?>"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none"
                    placeholder="Masukkan email anda"
                >
            </div>

            <!-- Full Name -->
            <div>
                <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                <input 
                    type="text" 
                    id="nama_lengkap" 
                    name="nama_lengkap" 
                    required
                    value="<?php echo e($old['nama_lengkap'] ?? ''); ?>"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none"
                    placeholder="Masukkan nama anda"
                >
            </div>

            <!-- Address (Optional) -->
            <div>
                <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">Alamat <span class="text-gray-400">(Optional)</span></label>
                <textarea 
                    id="alamat" 
                    name="alamat" 
                    rows="2"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none"
                    placeholder="Masukkan alamat anda"
                ><?php echo e($old['alamat'] ?? ''); ?></textarea>
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Tipe Akun</label>
                <div class="flex space-x-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="role" value="User" checked class="w-4 h-4 text-purple-600 focus:ring-purple-500" onchange="toggleAdminPassword()">
                        <span class="ml-2 text-gray-700">Pengguna</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="role" value="Admin" class="w-4 h-4 text-purple-600 focus:ring-purple-500" onchange="toggleAdminPassword()">
                        <span class="ml-2 text-gray-700">Admin</span>
                    </label>
                </div>
            </div>

            <!-- Admin Password (conditional) -->
            <div id="adminPasswordField" class="hidden">
                <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-2">Password Admin</label>
                <input 
                    type="password" 
                    id="admin_password" 
                    name="admin_password" 
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none"
                    placeholder="Masukkan password khusus admin"
                >
                <p class="text-xs text-gray-500 mt-1">Hubungi administrator untuk mendapatkan password admin</p>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none pr-12"
                        placeholder="Masukkan password anda"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword('password')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">Minimal 6 karakter</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password_confirm" 
                        name="password_confirm" 
                        required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none pr-12"
                        placeholder="Masukkan konfirmasi password anda"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword('password_confirm')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                    >
                        <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full py-3 px-4 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-indigo-700 focus:ring-4 focus:ring-purple-200 transition-all transform hover:scale-[1.02] active:scale-[0.98]"
            >
                Buat Akun
            </button>
        </form>

        <!-- Login Link -->
        <div class="mt-8 text-center">
            <p class="text-gray-500">
                Sudah punya akun? 
                <a href="<?php echo baseUrl('pages/auth/login.php'); ?>" class="text-purple-600 font-semibold hover:text-purple-700 transition-colors">
                    Login
                </a>
            </p>
        </div>

        <!-- Back to Home -->
        <div class="mt-4 text-center">
            <a href="<?php echo baseUrl('index.php'); ?>" class="text-gray-400 hover:text-gray-600 text-sm transition-colors">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = passwordInput.parentElement.querySelector('.eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            }
        }
        
        function toggleAdminPassword() {
            const adminRadio = document.querySelector('input[name="role"][value="Admin"]');
            const adminPasswordField = document.getElementById('adminPasswordField');
            const adminPasswordInput = document.getElementById('admin_password');
            
            if (adminRadio.checked) {
                // Show admin password field with animation
                adminPasswordField.classList.remove('hidden');
                adminPasswordInput.required = true;
            } else {
                // Hide admin password field
                adminPasswordField.classList.add('hidden');
                adminPasswordInput.required = false;
                adminPasswordInput.value = ''; // Clear value when hidden
            }
        }
    </script>
</body>
</html>
