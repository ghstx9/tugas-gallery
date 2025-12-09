<?php
/**
 * Albums Listing Page
 * Shows all user's albums with management options
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
    
    // Get user's albums with photo count
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            (SELECT COUNT(*) FROM gallery_foto WHERE AlbumID = a.AlbumID) as photo_count,
            (SELECT LokasiFile FROM gallery_foto WHERE FotoID = a.cover_photo_id) as cover_image
        FROM gallery_album a
        WHERE a.UserID = ?
        ORDER BY a.TanggalDibuat DESC, a.AlbumID DESC
    ");
    $stmt->execute([getCurrentUserId()]);
    $albums = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Albums error: " . $e->getMessage());
    $albums = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Albums - TugasGallery</title>
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
                    <a href="<?php echo baseUrl('pages/gallery/upload.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">Upload</a>
                    <a href="<?php echo baseUrl('pages/album/index.php'); ?>" class="text-white px-3 py-2 rounded-lg bg-white/20">Albums</a>
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">My Albums</h1>
                    <p class="text-gray-500 mt-1">Organize your photos into collections</p>
                </div>
                <button onclick="openCreateModal()" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Album
                </button>
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

    <!-- Albums Grid -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if (empty($albums)): ?>
            <div class="text-center py-20">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No albums yet</h3>
                <p class="text-gray-500 mb-6">Create your first album to organize your photos!</p>
                <button onclick="openCreateModal()" class="inline-flex items-center px-6 py-3 bg-purple-600 text-white font-semibold rounded-xl hover:bg-purple-700 transition-all">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Your First Album
                </button>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($albums as $album): ?>
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 group">
                        <!-- Album Cover -->
                        <a href="<?php echo baseUrl('pages/album/view.php?id=' . $album['AlbumID']); ?>" class="block aspect-video relative overflow-hidden bg-gray-100">
                            <?php if ($album['cover_image']): ?>
                                <img src="<?php echo uploadUrl($album['cover_image']); ?>" alt="<?php echo e($album['NamaAlbum']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <!-- Overlay with photo count -->
                            <div class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded-lg">
                                <?php echo $album['photo_count']; ?> photo<?php echo $album['photo_count'] != 1 ? 's' : ''; ?>
                            </div>
                            <?php if (!$album['is_public']): ?>
                                <div class="absolute top-2 left-2 bg-gray-800/80 text-white text-xs px-2 py-1 rounded-lg flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Private
                                </div>
                            <?php endif; ?>
                        </a>
                        
                        <!-- Album Info -->
                        <div class="p-4">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 min-w-0">
                                    <a href="<?php echo baseUrl('pages/album/view.php?id=' . $album['AlbumID']); ?>" class="font-semibold text-gray-800 truncate block hover:text-purple-600 transition-colors">
                                        <?php echo e($album['NamaAlbum']); ?>
                                    </a>
                                    <p class="text-sm text-gray-500 mt-1"><?php echo formatDate($album['TanggalDibuat']); ?></p>
                                </div>
                                <!-- Actions Dropdown -->
                                <div class="relative">
                                    <button onclick="toggleDropdown(<?php echo $album['AlbumID']; ?>)" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </button>
                                    <div id="dropdown-<?php echo $album['AlbumID']; ?>" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border z-10">
                                        <a href="<?php echo baseUrl('pages/album/edit.php?id=' . $album['AlbumID']); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-xl">
                                            Edit Album
                                        </a>
                                        <button onclick="confirmDelete(<?php echo $album['AlbumID']; ?>, '<?php echo e(addslashes($album['NamaAlbum'])); ?>')" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-xl">
                                            Delete Album
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php if ($album['Deskripsi']): ?>
                                <p class="text-sm text-gray-500 mt-2 line-clamp-2"><?php echo e($album['Deskripsi']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Create Album Modal -->
    <div id="create-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Create New Album</h2>
                <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form action="<?php echo baseUrl('actions/album/create_action.php'); ?>" method="POST">
                <?php echo csrfField(); ?>
                
                <div class="mb-4">
                    <label for="nama_album" class="block text-sm font-medium text-gray-700 mb-2">Album Name</label>
                    <input type="text" id="nama_album" name="nama_album" required maxlength="255" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none" placeholder="My vacation photos">
                </div>
                
                <div class="mb-4">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Description <span class="text-gray-400">(Optional)</span></label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none" placeholder="A collection of photos from my trip..."></textarea>
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Visibility</label>
                    <div class="flex space-x-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="is_public" value="1" checked class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-gray-700">Public</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="is_public" value="0" class="w-4 h-4 text-purple-600 focus:ring-purple-500">
                            <span class="ml-2 text-gray-700">Private</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 py-3 px-4 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-all">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all">
                        Create Album
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('create-modal').classList.remove('hidden');
            document.getElementById('create-modal').classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
        
        function closeCreateModal() {
            document.getElementById('create-modal').classList.add('hidden');
            document.getElementById('create-modal').classList.remove('flex');
            document.body.style.overflow = 'auto';
        }
        
        function toggleDropdown(albumId) {
            // Close all other dropdowns
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
                if (el.id !== 'dropdown-' + albumId) {
                    el.classList.add('hidden');
                }
            });
            
            const dropdown = document.getElementById('dropdown-' + albumId);
            dropdown.classList.toggle('hidden');
        }
        
        function confirmDelete(albumId, albumName) {
            if (confirm('Are you sure you want to delete "' + albumName + '"? Photos in this album will NOT be deleted, but will become orphaned.')) {
                window.location.href = '<?php echo baseUrl('actions/album/delete_action.php'); ?>?id=' + albumId;
            }
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('[onclick^="toggleDropdown"]')) {
                document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
                    el.classList.add('hidden');
                });
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCreateModal();
            }
        });
        
        // Close modal when clicking outside
        document.getElementById('create-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCreateModal();
            }
        });
    </script>
</body>
</html>
