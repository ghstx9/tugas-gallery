<?php
/**
 * Single Photo View Page
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
    
    // Get photo with user info
    $stmt = $pdo->prepare("
        SELECT 
            f.*,
            u.Username,
            u.NamaLengkap,
            a.NamaAlbum,
            a.AlbumID
        FROM gallery_foto f
        JOIN gallery_user u ON f.UserID = u.UserID
        LEFT JOIN gallery_album a ON f.AlbumID = a.AlbumID
        WHERE f.FotoID = ?
    ");
    $stmt->execute([$photoId]);
    $photo = $stmt->fetch();
    
    if (!$photo) {
        setFlash('error', 'Photo not found.');
        redirect(baseUrl('pages/gallery/index.php'));
    }
    
    // Check if user can view this photo (public or owner)
    if (!$photo['is_public'] && $photo['UserID'] != getCurrentUserId() && !isAdmin()) {
        setFlash('error', 'You do not have permission to view this photo.');
        redirect(baseUrl('pages/gallery/index.php'));
    }
    
    // Increment view count
    $updateStmt = $pdo->prepare("UPDATE gallery_foto SET view_count = view_count + 1 WHERE FotoID = ?");
    $updateStmt->execute([$photoId]);
    $photo['view_count']++; // Update local copy
    
    // Get like count and check if current user liked
    $likeStmt = $pdo->prepare("SELECT COUNT(*) FROM gallery_likefoto WHERE FotoID = ?");
    $likeStmt->execute([$photoId]);
    $likeCount = $likeStmt->fetchColumn();
    
    $userLikedStmt = $pdo->prepare("SELECT LikeID FROM gallery_likefoto WHERE FotoID = ? AND UserID = ?");
    $userLikedStmt->execute([$photoId, getCurrentUserId()]);
    $userLiked = $userLikedStmt->fetch() !== false;
    
    // Get comments
    $commentStmt = $pdo->prepare("
        SELECT 
            k.*,
            u.Username,
            u.NamaLengkap
        FROM gallery_komentarfoto k
        JOIN gallery_user u ON k.UserID = u.UserID
        WHERE k.FotoID = ?
        ORDER BY k.TanggalKomentar DESC, k.KomentarID DESC
    ");
    $commentStmt->execute([$photoId]);
    $comments = $commentStmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Photo view error: " . $e->getMessage());
    setFlash('error', 'An error occurred.');
    redirect(baseUrl('pages/gallery/index.php'));
}

$isOwner = $photo['UserID'] == getCurrentUserId();
$canDelete = isAdmin();
$canEdit = $isOwner || isAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($photo['JudulFoto']); ?> - TugasGallery</title>
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
        .like-btn.liked svg {
            fill: currentColor;
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

    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="max-w-5xl mx-auto px-4 mt-4">
            <div class="p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Photo Detail -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Photo -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <img 
                        src="<?php echo uploadUrl($photo['LokasiFile']); ?>" 
                        alt="<?php echo e($photo['JudulFoto']); ?>"
                        class="w-full h-auto"
                    >
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center justify-between mt-4">
                    <div class="flex items-center space-x-4">
                        <!-- Like Button -->
                        <button 
                            onclick="toggleLike(<?php echo $photoId; ?>)"
                            id="like-btn"
                            class="like-btn flex items-center space-x-2 px-4 py-2 rounded-xl transition-all <?php echo $userLiked ? 'liked bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600'; ?>"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span id="like-count"><?php echo $likeCount; ?></span>
                        </button>
                    </div>
                    
                    <?php if ($canEdit || $canDelete): ?>
                    <div class="flex items-center space-x-2">
                        <?php if ($canEdit): ?>
                        <a href="<?php echo baseUrl('pages/gallery/edit.php?id=' . $photoId); ?>" class="px-4 py-2 bg-blue-100 text-blue-600 rounded-xl hover:bg-blue-200 transition-all">
                            Edit
                        </a>
                        <?php endif; ?>
                        <?php if ($canDelete): ?>
                        <button onclick="confirmDelete(<?php echo $photoId; ?>)" class="px-4 py-2 bg-red-100 text-red-600 rounded-xl hover:bg-red-200 transition-all">
                            Delete
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info & Comments -->
            <div class="space-y-6">
                <!-- Photo Info -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h1 class="text-2xl font-bold text-gray-800 mb-2"><?php echo e($photo['JudulFoto']); ?></h1>
                    
                    <?php if ($photo['DeskripsiFoto']): ?>
                        <p class="text-gray-600 mb-4"><?php echo nl2br(e($photo['DeskripsiFoto'])); ?></p>
                    <?php endif; ?>
                    
                    <div class="border-t pt-4 space-y-3">
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <span>by <strong><?php echo e($photo['NamaLengkap']); ?></strong> (@<?php echo e($photo['Username']); ?>)</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span><?php echo formatDate($photo['TanggalUnggah']); ?></span>
                        </div>
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span><?php echo $photo['view_count']; ?> views</span>
                        </div>
                        <?php if ($photo['NamaAlbum']): ?>
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            <span>Album: <?php echo e($photo['NamaAlbum']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="font-bold text-gray-800 mb-4">Comments (<?php echo count($comments); ?>)</h3>
                    
                    <!-- Comment Form -->
                    <form action="<?php echo baseUrl('actions/photo/comment_action.php'); ?>" method="POST" class="mb-6">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="photo_id" value="<?php echo $photoId; ?>">
                        <textarea 
                            name="comment" 
                            rows="2" 
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none mb-2"
                            placeholder="Add a comment..."
                        ></textarea>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-all">
                            Post Comment
                        </button>
                    </form>
                    
                    <!-- Comments List -->
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        <?php if (empty($comments)): ?>
                            <p class="text-gray-500 text-center py-4">No comments yet. Be the first!</p>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="border-b border-gray-100 pb-4 last:border-0">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="font-semibold text-gray-800"><?php echo e($comment['NamaLengkap']); ?></span>
                                            <span class="text-gray-400 text-sm ml-2">@<?php echo e($comment['Username']); ?></span>
                                        </div>
                                        <span class="text-gray-400 text-xs"><?php echo formatDate($comment['TanggalKomentar']); ?></span>
                                    </div>
                                    <p class="text-gray-600 mt-1"><?php echo nl2br(e($comment['IsiKomentar'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="mt-8">
            <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="inline-flex items-center text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Gallery
            </a>
        </div>
    </main>

    <script>
        function toggleLike(photoId) {
            fetch('<?php echo baseUrl('actions/photo/like_action.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'photo_id=' + photoId + '&csrf_token=<?php echo e(generateCsrfToken()); ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeBtn = document.getElementById('like-btn');
                    const likeCount = document.getElementById('like-count');
                    
                    likeCount.textContent = data.like_count;
                    
                    if (data.liked) {
                        likeBtn.classList.add('liked', 'bg-red-100', 'text-red-600');
                        likeBtn.classList.remove('bg-gray-100', 'text-gray-600');
                    } else {
                        likeBtn.classList.remove('liked', 'bg-red-100', 'text-red-600');
                        likeBtn.classList.add('bg-gray-100', 'text-gray-600');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        function confirmDelete(photoId) {
            if (confirm('Are you sure you want to delete this photo? This action cannot be undone.')) {
                window.location.href = '<?php echo baseUrl('actions/photo/delete_action.php'); ?>?id=' + photoId;
            }
        }
    </script>
</body>
</html>
