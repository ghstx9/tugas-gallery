<?php
/**
 * Edit Album Page
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
    $stmt = $pdo->prepare("SELECT * FROM gallery_album WHERE AlbumID = ? AND UserID = ?");
    $stmt->execute([$albumId, getCurrentUserId()]);
    $album = $stmt->fetch();
    
    if (!$album) {
        setFlash('error', 'Album not found or you do not have permission to edit it.');
        redirect(baseUrl('pages/album/index.php'));
    }
    
    // Get photos in this album for cover selection
    $photoStmt = $pdo->prepare("SELECT FotoID, JudulFoto, LokasiFile FROM gallery_foto WHERE AlbumID = ? ORDER BY TanggalUnggah DESC");
    $photoStmt->execute([$albumId]);
    $photos = $photoStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Edit album error: " . $e->getMessage());
    setFlash('error', 'An error occurred.');
    redirect(baseUrl('pages/album/index.php'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Album - TugasGallery</title>
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
                    <a href="<?php echo baseUrl('pages/album/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">Albums</a>
                    <div class="flex items-center space-x-3">
                        <span class="text-white/80"><strong class="text-white"><?php echo e($user['nama_lengkap']); ?></strong></span>
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
                <a href="<?php echo baseUrl('pages/album/view.php?id=' . $albumId); ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Edit Album</h1>
                    <p class="text-gray-500 mt-1">Update album details</p>
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
        <form action="<?php echo baseUrl('actions/album/edit_action.php'); ?>" method="POST" class="bg-white rounded-2xl shadow-lg p-8">
            <?php echo csrfField(); ?>
            <input type="hidden" name="album_id" value="<?php echo $albumId; ?>">
            
            <div class="mb-6">
                <label for="nama_album" class="block text-sm font-medium text-gray-700 mb-2">Album Name</label>
                <input type="text" id="nama_album" name="nama_album" required maxlength="255" value="<?php echo e($album['NamaAlbum']); ?>" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none">
            </div>
            
            <div class="mb-6">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none"><?php echo e($album['Deskripsi'] ?? ''); ?></textarea>
            </div>
            
            <?php if (!empty($photos)): ?>
            <div class="mb-6">
                <label for="cover_photo" class="block text-sm font-medium text-gray-700 mb-2">Cover Photo</label>
                <select id="cover_photo" name="cover_photo_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none bg-white">
                    <option value="">No cover photo</option>
                    <?php foreach ($photos as $photo): ?>
                        <option value="<?php echo $photo['FotoID']; ?>" <?php echo $album['cover_photo_id'] == $photo['FotoID'] ? 'selected' : ''; ?>>
                            <?php echo e($photo['JudulFoto']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-3">Visibility</label>
                <div class="flex space-x-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="is_public" value="1" <?php echo $album['is_public'] ? 'checked' : ''; ?> class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-gray-700">Public</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="is_public" value="0" <?php echo !$album['is_public'] ? 'checked' : ''; ?> class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                        <span class="ml-2 text-gray-700">Private</span>
                    </label>
                </div>
            </div>
            
            <div class="flex space-x-4">
                <button type="submit" class="flex-1 py-3 px-6 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all">
                    Save Changes
                </button>
                <a href="<?php echo baseUrl('pages/album/view.php?id=' . $albumId); ?>" class="py-3 px-6 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all text-center">
                    Cancel
                </a>
            </div>
        </form>
    </main>
</body>
</html>
