<?php
/**
 * View Album Page
 * Shows all photos in an album
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

$user = getCurrentUser();
$flash = getFlash();

// Get album ID
$albumId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($albumId <= 0) {
    setFlash('error', 'Album not found.');
    redirect(baseUrl('pages/album/index.php'));
}

try {
    $pdo = db();
    
    // Get album info
    $stmt = $pdo->prepare("
        SELECT a.*, u.Username, u.NamaLengkap
        FROM gallery_album a
        JOIN gallery_user u ON a.UserID = u.UserID
        WHERE a.AlbumID = ?
    ");
    $stmt->execute([$albumId]);
    $album = $stmt->fetch();
    
    if (!$album) {
        setFlash('error', 'Album not found.');
        redirect(baseUrl('pages/album/index.php'));
    }
    
    // Check if user can view this album (public or owner)
    $isOwner = $album['UserID'] == getCurrentUserId();
    if (!$album['is_public'] && !$isOwner && !isAdmin()) {
        setFlash('error', 'You do not have permission to view this album.');
        redirect(baseUrl('pages/album/index.php'));
    }
    
    // Get photos in this album
    $photoStmt = $pdo->prepare("
        SELECT 
            f.*,
            (SELECT COUNT(*) FROM gallery_likefoto WHERE FotoID = f.FotoID) as like_count,
            (SELECT COUNT(*) FROM gallery_komentarfoto WHERE FotoID = f.FotoID) as comment_count
        FROM gallery_foto f
        WHERE f.AlbumID = ?
        ORDER BY f.TanggalUnggah DESC, f.FotoID DESC
    ");
    $photoStmt->execute([$albumId]);
    $photos = $photoStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("View album error: " . $e->getMessage());
    setFlash('error', 'An error occurred.');
    redirect(baseUrl('pages/album/index.php'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($album['NamaAlbum']); ?> - TugasGallery</title>
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
                        <span class="text-white font-bold text-xl">Aplikasi Gallery</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">Galeri</a>
                    <a href="<?php echo baseUrl('pages/album/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">Album</a>
                    <a href="<?php echo baseUrl('pages/profile/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">Profil</a>
                    <div class="flex items-center space-x-3">
                        <span class="text-white/80">
                            <strong class="text-white"><?php echo $user['nama_lengkap']; ?></strong>
                            <?php if (isAdmin()): ?>
                                <span class="ml-1 px-2 py-0.5 bg-yellow-400 text-yellow-900 text-xs font-bold rounded-full">Admin</span>
                            <?php endif; ?>
                        </span>
                        <a href="<?php echo baseUrl('actions/auth/logout_action.php'); ?>" class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl transition-all">Keluar</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center space-x-4 mb-4">
                <a href="<?php echo baseUrl('pages/album/index.php'); ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <h1 class="text-3xl font-bold text-gray-800"><?php echo $album['NamaAlbum']; ?></h1>
                        <?php if (!$album['is_public']): ?>
                            <span class="px-2 py-1 bg-gray-200 text-gray-600 text-xs font-medium rounded-lg flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Private
                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="text-gray-500 mt-1">
                        oleh <?php echo $album['NamaLengkap']; ?> • Dibuat <?php echo formatDate($album['TanggalDibuat']); ?> • <?php echo count($photos); ?> foto
                    </p>
                    <?php if ($album['Deskripsi']): ?>
                        <p class="text-gray-600 mt-2"><?php echo nl2br($album['Deskripsi']); ?></p>
                    <?php endif; ?>
                </div>
                <?php if ($isOwner): ?>
                    <div class="flex items-center space-x-2">
                        <a href="<?php echo baseUrl('pages/album/edit.php?id=' . $albumId); ?>" class="px-4 py-2 bg-blue-100 text-blue-600 rounded-xl hover:bg-blue-200 transition-all">Edit</a>
                        <button onclick="confirmDelete(<?php echo $albumId; ?>, '<?php echo addslashes($album['NamaAlbum']); ?>')" class="px-4 py-2 bg-red-100 text-red-600 rounded-xl hover:bg-red-200 transition-all">Hapus</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Photos Grid -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (empty($photos)): ?>
            <div class="text-center py-20">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">Tidak ada photo di album ini.</h3>
                <p class="text-gray-500 mb-6">Tambahkan photo ke album ini untuk melihatnya di sini.</p>
                <?php if ($isOwner): ?>
                    <a href="<?php echo baseUrl('pages/gallery/upload.php'); ?>" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white font-semibold rounded-xl hover:bg-purple-700 transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Upload Foto
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($photos as $photo): ?>
                    <a href="<?php echo baseUrl('pages/gallery/photo.php?id=' . $photo['FotoID']); ?>" class="group block bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="aspect-square relative overflow-hidden">
                            <img src="<?php echo uploadUrl($photo['LokasiFile']); ?>" alt="<?php echo e($photo['JudulFoto']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-4">
                                <div class="text-white">
                                    <h3 class="font-semibold truncate"><?php echo e($photo['JudulFoto']); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-800 truncate mb-2"><?php echo e($photo['JudulFoto']); ?></h3>
                            <div class="flex items-center space-x-4 text-sm text-gray-400">
                                <span class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                    <span><?php echo $photo['like_count']; ?></span>
                                </span>
                                <span class="flex items-center space-x-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    <span><?php echo $photo['comment_count']; ?></span>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
    
    <script>
        function confirmDelete(albumId, albumName) {
            if (confirm('Apakah kamu yakin untuk menghapus album "' + albumName + '"? Foto di album ini akan tetap ada, tetapi akan menjadi foto yang tidak terhubung.')) {
                window.location.href = '<?php echo baseUrl('actions/album/delete_action.php'); ?>?id=' + albumId;
            }
        }
    </script>
</body>
</html>
