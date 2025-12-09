<?php
/**
 * User Profile Page
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
    
    // Get user stats
    $photoCountStmt = $pdo->prepare("SELECT COUNT(*) FROM gallery_foto WHERE UserID = ?");
    $photoCountStmt->execute([getCurrentUserId()]);
    $photoCount = $photoCountStmt->fetchColumn();
    
    $albumCountStmt = $pdo->prepare("SELECT COUNT(*) FROM gallery_album WHERE UserID = ?");
    $albumCountStmt->execute([getCurrentUserId()]);
    $albumCount = $albumCountStmt->fetchColumn();
    
    $likesReceivedStmt = $pdo->prepare("
        SELECT COUNT(*) FROM gallery_likefoto l
        JOIN gallery_foto f ON l.FotoID = f.FotoID
        WHERE f.UserID = ?
    ");
    $likesReceivedStmt->execute([getCurrentUserId()]);
    $likesReceived = $likesReceivedStmt->fetchColumn();
    
    $totalViewsStmt = $pdo->prepare("SELECT SUM(view_count) FROM gallery_foto WHERE UserID = ?");
    $totalViewsStmt->execute([getCurrentUserId()]);
    $totalViews = $totalViewsStmt->fetchColumn() ?? 0;
    
    // Get user's recent photos
    $recentPhotosStmt = $pdo->prepare("
        SELECT f.*, 
            (SELECT COUNT(*) FROM gallery_likefoto WHERE FotoID = f.FotoID) as like_count,
            (SELECT COUNT(*) FROM gallery_komentarfoto WHERE FotoID = f.FotoID) as comment_count
        FROM gallery_foto f 
        WHERE f.UserID = ? 
        ORDER BY f.TanggalUnggah DESC, f.FotoID DESC 
        LIMIT 8
    ");
    $recentPhotosStmt->execute([getCurrentUserId()]);
    $recentPhotos = $recentPhotosStmt->fetchAll();
    
    // Get full user info from database
    $userStmt = $pdo->prepare("SELECT * FROM gallery_user WHERE UserID = ?");
    $userStmt->execute([getCurrentUserId()]);
    $userInfo = $userStmt->fetch();
    
} catch (PDOException $e) {
    error_log("Profile error: " . $e->getMessage());
    $photoCount = 0;
    $albumCount = 0;
    $likesReceived = 0;
    $totalViews = 0;
    $recentPhotos = [];
    $userInfo = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - TugasGallery</title>
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
                    <a href="<?php echo baseUrl('pages/profile/index.php'); ?>" class="text-white px-3 py-2 rounded-lg bg-white/20">Profile</a>
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

    <!-- Profile Header -->
    <div class="gradient-bg py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <!-- Avatar -->
                <div class="w-32 h-32 bg-white/20 rounded-full flex items-center justify-center text-white text-4xl font-bold border-4 border-white/30">
                    <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
                </div>
                
                <!-- User Info -->
                <div class="text-center md:text-left flex-1">
                    <h1 class="text-3xl font-bold text-white mb-1"><?php echo e($user['nama_lengkap']); ?></h1>
                    <p class="text-white/80 mb-2">@<?php echo e($userInfo['Username'] ?? ''); ?></p>
                    <?php if (isAdmin()): ?>
                        <span class="inline-block px-3 py-1 bg-yellow-400 text-yellow-900 text-sm font-bold rounded-full mb-3">Admin</span>
                    <?php endif; ?>
                    <p class="text-white/70 text-sm">Member since <?php echo formatDate($userInfo['created_at'] ?? ''); ?></p>
                </div>
                
                <!-- Edit Profile Button -->
                <a href="<?php echo baseUrl('pages/profile/edit.php'); ?>" class="px-6 py-3 bg-white/20 hover:bg-white/30 text-white font-semibold rounded-xl transition-all border border-white/30">
                    Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                <div>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $photoCount; ?></p>
                    <p class="text-gray-500 text-sm">Photos</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-indigo-600"><?php echo $albumCount; ?></p>
                    <p class="text-gray-500 text-sm">Albums</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-pink-600"><?php echo $likesReceived; ?></p>
                    <p class="text-gray-500 text-sm">Likes Received</p>
                </div>
                <div>
                    <p class="text-3xl font-bold text-blue-600"><?php echo number_format($totalViews); ?></p>
                    <p class="text-gray-500 text-sm">Total Views</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="max-w-5xl mx-auto px-4 mt-4">
            <div class="p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Profile Info -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid md:grid-cols-3 gap-8">
            <!-- User Details -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Profile Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-sm text-gray-500">Email</label>
                        <p class="text-gray-800"><?php echo e($userInfo['Email'] ?? ''); ?></p>
                    </div>
                    <div>
                        <label class="text-sm text-gray-500">Full Name</label>
                        <p class="text-gray-800"><?php echo e($userInfo['NamaLengkap'] ?? ''); ?></p>
                    </div>
                    <?php if (!empty($userInfo['Alamat'])): ?>
                    <div>
                        <label class="text-sm text-gray-500">Address</label>
                        <p class="text-gray-800"><?php echo e($userInfo['Alamat']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Photos -->
            <div class="md:col-span-2">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-gray-800">My Recent Photos</h2>
                    <a href="<?php echo baseUrl('pages/gallery/upload.php'); ?>" class="text-purple-600 hover:text-purple-700 text-sm font-medium">Upload New</a>
                </div>
                
                <?php if (empty($recentPhotos)): ?>
                    <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-500 mb-4">You haven't uploaded any photos yet.</p>
                        <a href="<?php echo baseUrl('pages/gallery/upload.php'); ?>" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all">
                            Upload Your First Photo
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($recentPhotos as $photo): ?>
                            <a href="<?php echo baseUrl('pages/gallery/photo.php?id=' . $photo['FotoID']); ?>" class="group block bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-all">
                                <div class="aspect-square relative overflow-hidden">
                                    <img src="<?php echo uploadUrl($photo['LokasiFile']); ?>" alt="<?php echo e($photo['JudulFoto']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    <?php if (!$photo['is_public']): ?>
                                        <div class="absolute top-2 left-2 bg-gray-800/80 text-white text-xs px-2 py-1 rounded-lg">
                                            <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-3">
                                    <p class="text-sm text-gray-800 truncate font-medium"><?php echo e($photo['JudulFoto']); ?></p>
                                    <div class="flex items-center space-x-3 text-xs text-gray-400 mt-1">
                                        <span class="flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                            </svg>
                                            <?php echo $photo['like_count']; ?>
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <?php echo $photo['view_count']; ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
