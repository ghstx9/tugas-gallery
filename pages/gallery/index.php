<?php
/**
 * Gallery Index Page
 * Displays all public photos in a grid layout
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

$user = getCurrentUser();
$flash = getFlash();

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

try {
    $pdo = db();
    
    // Get total count of public photos
    $countStmt = $pdo->query("SELECT COUNT(*) FROM gallery_foto WHERE is_public = TRUE");
    $totalPhotos = $countStmt->fetchColumn();
    $totalPages = ceil($totalPhotos / $perPage);
    
    // Get photos with user info and like count
    $stmt = $pdo->prepare("
        SELECT 
            f.FotoID,
            f.JudulFoto,
            f.DeskripsiFoto,
            f.TanggalUnggah,
            f.LokasiFile,
            f.view_count,
            f.UserID,
            u.Username,
            u.NamaLengkap,
            (SELECT COUNT(*) FROM gallery_likefoto WHERE FotoID = f.FotoID) as like_count,
            (SELECT COUNT(*) FROM gallery_komentarfoto WHERE FotoID = f.FotoID) as comment_count,
            (SELECT COUNT(*) FROM gallery_likefoto WHERE FotoID = f.FotoID AND UserID = ?) as user_liked
        FROM gallery_foto f
        JOIN gallery_user u ON f.UserID = u.UserID
        WHERE f.is_public = TRUE
        ORDER BY f.TanggalUnggah DESC, f.FotoID DESC
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute([getCurrentUserId(), $perPage, $offset]);
    $photos = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Gallery error: " . $e->getMessage());
    $photos = [];
    $totalPages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery - TugasGallery</title>
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
        .photo-card:hover .photo-overlay {
            opacity: 1;
        }
        .photo-card:hover img {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
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

                <!-- Navigation Links -->
                <div class="flex items-center space-x-4">
                    <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="text-white px-3 py-2 rounded-lg bg-white/20">
                        Gallery
                    </a>
                    <a href="<?php echo baseUrl('pages/album/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">
                        Albums
                    </a>
                    <a href="<?php echo baseUrl('pages/profile/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">
                        Profile
                    </a>
                    <div class="flex items-center space-x-3">
                        <span class="text-white/80">
                            <strong class="text-white"><?php echo e($user['nama_lengkap']); ?></strong>
                            <?php if (isAdmin()): ?>
                                <span class="ml-1 px-2 py-0.5 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">Admin</span>
                            <?php endif; ?>
                        </span>
                        <a href="<?php echo baseUrl('actions/auth/logout_action.php'); ?>" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl transition-all">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Photo Gallery</h1>
                    <p class="text-gray-500 mt-1">Browse beautiful photos from our community</p>
                </div>
                <a href="<?php echo baseUrl('pages/gallery/upload.php'); ?>" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Upload Photo
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Gallery Grid -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (empty($photos)): ?>
            <!-- Empty State -->
            <div class="text-center py-20">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No photos yet</h3>
                <p class="text-gray-500 mb-6">Be the first to share a photo with the community!</p>
                <a href="<?php echo baseUrl('pages/gallery/upload.php'); ?>" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white font-semibold rounded-xl hover:bg-purple-700 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Upload Your First Photo
                </a>
            </div>
        <?php else: ?>
            <!-- Photo Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($photos as $photo): ?>
                    <a href="<?php echo baseUrl('pages/gallery/photo.php?id=' . $photo['FotoID']); ?>" class="photo-card group block bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                        <!-- Photo Image -->
                        <div class="aspect-square relative overflow-hidden">
                            <img 
                                src="<?php echo uploadUrl($photo['LokasiFile']); ?>" 
                                alt="<?php echo e($photo['JudulFoto']); ?>"
                                class="w-full h-full object-cover transition-transform duration-300"
                                loading="lazy"
                            >
                            <!-- Overlay -->
                            <div class="photo-overlay absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 transition-opacity duration-300 flex items-end p-4">
                                <div class="text-white">
                                    <h3 class="font-semibold truncate"><?php echo e($photo['JudulFoto']); ?></h3>
                                    <p class="text-sm text-white/80">by <?php echo e($photo['Username']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Photo Info -->
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 truncate mb-1"><?php echo e($photo['JudulFoto']); ?></h3>
                            <p class="text-sm text-gray-500 mb-3">by <?php echo e($photo['Username']); ?></p>
                            
                            <!-- Stats -->
                            <div class="flex items-center space-x-4 text-sm text-gray-400">
                                <!-- Likes -->
                                <span class="flex items-center space-x-1 <?php echo $photo['user_liked'] ? 'text-red-500' : ''; ?>">
                                    <svg class="w-4 h-4 <?php echo $photo['user_liked'] ? 'fill-current' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    <span><?php echo $photo['like_count']; ?></span>
                                </span>
                                <!-- Comments -->
                                <span class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <span><?php echo $photo['comment_count']; ?></span>
                                </span>
                                <!-- Views -->
                                <span class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span><?php echo $photo['view_count']; ?></span>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center mt-12">
                    <nav class="flex items-center space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>" class="px-4 py-2 rounded-lg transition-colors <?php echo $i === $page ? 'bg-purple-600 text-white' : 'bg-white border border-gray-200 hover:bg-gray-50'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                Next
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-400 text-sm">
                &copy; <?php echo date('Y'); ?> TugasGallery. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>
