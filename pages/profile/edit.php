<?php
/**
 * Edit Profile Page
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

$user = getCurrentUser();
$flash = getFlash();

try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM gallery_user WHERE UserID = ?");
    $stmt->execute([getCurrentUserId()]);
    $userInfo = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Profile error: " . $e->getMessage());
    $userInfo = [];
}

// Get old input on validation failure
$oldInput = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - TugasGallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?php echo baseUrl('index.php'); ?>" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <span class="text-white font-bold text-xl">TugasGallery</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">Gallery</a>
                    <a href="<?php echo baseUrl('pages/album/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">Albums</a>
                    <a href="<?php echo baseUrl('pages/profile/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">Profile</a>
                    <div class="flex items-center space-x-3">
                        <span class="text-white/80">
                            <strong class="text-white"><?php echo e($user['nama_lengkap']); ?></strong>
                            <?php if (isAdmin()): ?>
                                <span class="ml-1 px-2 py-0.5 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">Admin</span>
                            <?php endif; ?>
                        </span>
                        <a href="<?php echo baseUrl('actions/auth/logout_action.php'); ?>" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl transition-all">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-white border-b">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center space-x-4">
                <a href="<?php echo baseUrl('pages/profile/index.php'); ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Edit Profile</h1>
                    <p class="text-gray-500 mt-1">Update your account information</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="max-w-3xl mx-auto px-4 mt-4">
            <div class="p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="<?php echo baseUrl('actions/profile/update_action.php'); ?>" method="POST" class="bg-white rounded-2xl shadow-lg p-8">
            <?php echo csrfField(); ?>
            
            <div class="mb-6">
                <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required maxlength="255" value="<?php echo e($oldInput['nama_lengkap'] ?? $userInfo['NamaLengkap'] ?? ''); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
            </div>
            
            <div class="mb-6">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" required maxlength="255" value="<?php echo e($oldInput['email'] ?? $userInfo['Email'] ?? ''); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
            </div>
            
            <div class="mb-6">
                <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">Address <span class="text-gray-400">(Optional)</span></label>
                <textarea id="alamat" name="alamat" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none"><?php echo e($oldInput['alamat'] ?? $userInfo['Alamat'] ?? ''); ?></textarea>
            </div>
            
            <hr class="my-8">
            
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Change Password</h3>
            <p class="text-sm text-gray-500 mb-4">Leave blank to keep your current password.</p>
            
            <div class="mb-6">
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                <input type="password" id="current_password" name="current_password" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
            </div>
            
            <div class="mb-6">
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <input type="password" id="new_password" name="new_password" minlength="6" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
            </div>
            
            <div class="mb-8">
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 py-3 px-6 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all">
                    Save Changes
                </button>
                <a href="<?php echo baseUrl('pages/profile/index.php'); ?>" class="py-3 px-6 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all text-center">
                    Cancel
                </a>
            </div>
        </form>
    </main>
</body>
</html>
