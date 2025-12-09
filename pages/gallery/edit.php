<?php
/**
 * Edit Photo Page
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

$user = getCurrentUser();
$flash = getFlash();

// Get photo ID
$photoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($photoId <= 0) {
    setFlash('error', 'Photo not found.');
    redirect(baseUrl('pages/gallery/index.php'));
}

try {
    $pdo = db();
    
    // Get photo info
    $stmt = $pdo->prepare("SELECT * FROM gallery_foto WHERE FotoID = ?");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();
    
    if (!$photo) {
        setFlash('error', 'Photo not found.');
        redirect(baseUrl('pages/gallery/index.php'));
    }
    
    // Check if user can edit this photo (owner or admin)
    if ($photo['UserID'] != getCurrentUserId() && !isAdmin()) {
        setFlash('error', 'You do not have permission to edit this photo.');
        redirect(baseUrl('pages/gallery/photo.php?id=' . $photoId));
    }
    
    // Get user's albums for selection
    $albumStmt = $pdo->prepare("SELECT AlbumID, NamaAlbum FROM gallery_album WHERE UserID = ? ORDER BY NamaAlbum ASC");
    $albumStmt->execute([$photo['UserID']]); // Use photo owner's albums
    $albums = $albumStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Edit photo error: " . $e->getMessage());
    setFlash('error', 'An error occurred.');
    redirect(baseUrl('pages/gallery/index.php'));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Foto - Aplikasi Gallery</title>
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
                            <strong class="text-white"><?php echo e($user['nama_lengkap']); ?></strong>
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
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center space-x-4">
                <a href="<?php echo baseUrl('pages/gallery/photo.php?id=' . $photoId); ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Edit Foto</h1>
                    <p class="text-gray-500 mt-1">Perbarui detail foto</p>
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
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Photo Preview -->
            <div>
                <img 
                    src="<?php echo uploadUrl($photo['LokasiFile']); ?>" 
                    alt="<?php echo e($photo['JudulFoto']); ?>"
                    class="w-full rounded-2xl shadow-lg"
                >
            </div>
            
            <!-- Edit Form -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <form action="<?php echo baseUrl('actions/photo/edit_action.php'); ?>" method="POST">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="photo_id" value="<?php echo $photoId; ?>">
                    
                    <!-- Photo Title -->
                    <div class="mb-6">
                        <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">Judul</label>
                        <input 
                            type="text" 
                            id="judul" 
                            name="judul" 
                            required
                            maxlength="255"
                            value="<?php echo e($photo['JudulFoto']); ?>"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none"
                        >
                    </div>

                    <!-- Photo Description -->
                    <div class="mb-6">
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea 
                            id="deskripsi" 
                            name="deskripsi" 
                            rows="3"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none"
                        ><?php echo e($photo['DeskripsiFoto'] ?? ''); ?></textarea>
                    </div>

                    <!-- Album Selection -->
                    <div class="mb-6">
                        <label for="album" class="block text-sm font-medium text-gray-700 mb-2">Album</label>
                        <select 
                            id="album" 
                            name="album_id"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none bg-white"
                        >
                            <option value="">Tanpa album</option>
                            <?php foreach ($albums as $album): ?>
                                <option value="<?php echo $album['AlbumID']; ?>" <?php echo $photo['AlbumID'] == $album['AlbumID'] ? 'selected' : ''; ?>>
                                    <?php echo e($album['NamaAlbum']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Privacy -->
                    <div class="mb-8">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Visibilitas</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="is_public" value="1" <?php echo $photo['is_public'] ? 'checked' : ''; ?> class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-gray-700">Publik</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="is_public" value="0" <?php echo !$photo['is_public'] ? 'checked' : ''; ?> class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-gray-700">Privat</span>
                            </label>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex space-x-4">
                        <button 
                            type="submit" 
                            class="flex-1 py-3 px-6 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all"
                        >
                            Simpan Perubahan
                        </button>
                        <a 
                            href="<?php echo baseUrl('pages/gallery/photo.php?id=' . $photoId); ?>" 
                            class="py-3 px-6 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all"
                        >
                            Batal
                        </a>
                    </div>
                </form>
                
                <!-- Delete Photo (Admin Only) -->
                <?php if (isAdmin()): ?>
                <div class="mt-6 pt-6 border-t">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Zona Bahaya</h3>
                    <p class="text-sm text-gray-500 mb-3">Setelah foto dihapus, tidak dapat dikembalikan. Harap pastikan.</p>
                    <button onclick="confirmDelete(<?php echo $photoId; ?>)" class="w-full px-4 py-2 bg-red-100 text-red-600 rounded-xl hover:bg-red-200 transition-all font-medium">
                        Hapus Foto
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script>
        function confirmDelete(photoId) {
            if (confirm('Apakah Anda yakin ingin menghapus foto ini? Tindakan ini tidak dapat dibatalkan.')) {
                window.location.href = '<?php echo baseUrl('actions/photo/delete_action.php'); ?>?id=' + photoId;
            }
        }
    </script>
</body>
</html>
