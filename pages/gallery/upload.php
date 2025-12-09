<?php
/**
 * Photo Upload Page
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

// Require authentication
requireAuth();

$user = getCurrentUser();
$flash = getFlash();

// Get user's albums for selection
try {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT AlbumID, NamaAlbum FROM gallery_album WHERE UserID = ? ORDER BY NamaAlbum ASC");
    $stmt->execute([getCurrentUserId()]);
    $albums = $stmt->fetchAll();
} catch (PDOException $e) {
    $albums = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Photo - TugasGallery</title>
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
        .upload-area {
            border: 2px dashed #d1d5db;
            transition: all 0.3s ease;
        }
        .upload-area:hover,
        .upload-area.dragover {
            border-color: #8b5cf6;
            background-color: #f5f3ff;
        }
        .upload-area.has-file {
            border-color: #10b981;
            background-color: #ecfdf5;
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
                    <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="text-white/80 hover:text-white px-3 py-2 rounded-lg hover:bg-white/10 transition-all">
                        Gallery
                    </a>
                    <a href="<?php echo baseUrl('pages/gallery/upload.php'); ?>" class="text-white px-3 py-2 rounded-lg bg-white/20">
                        Upload
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
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center space-x-4">
                <a href="<?php echo baseUrl('pages/gallery/index.php'); ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Upload Photo</h1>
                    <p class="text-gray-500 mt-1">Share a new photo with the community</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="max-w-3xl mx-auto px-4 mt-4">
            <div class="p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'; ?>">
                <?php echo e($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Upload Form -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form action="<?php echo baseUrl('actions/photo/upload_action.php'); ?>" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-lg p-8">
            <?php echo csrfField(); ?>
            
            <!-- File Upload Area -->
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-3">Photo</label>
                <div id="upload-area" class="upload-area rounded-2xl p-8 text-center cursor-pointer" onclick="document.getElementById('photo').click()">
                    <input 
                        type="file" 
                        id="photo" 
                        name="photo" 
                        accept="image/jpeg,image/png,image/gif,image/webp"
                        required
                        class="hidden"
                        onchange="handleFileSelect(this)"
                    >
                    
                    <!-- Default State -->
                    <div id="upload-placeholder">
                        <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-600 font-medium mb-1">Click to upload or drag and drop</p>
                        <p class="text-gray-400 text-sm">JPG, PNG, GIF or WebP (max 5MB)</p>
                    </div>
                    
                    <!-- Preview State -->
                    <div id="upload-preview" class="hidden">
                        <img id="preview-image" src="" alt="Preview" class="max-h-64 mx-auto rounded-xl mb-4">
                        <p id="file-name" class="text-gray-600 font-medium"></p>
                        <button type="button" onclick="clearFile(event)" class="mt-2 text-red-500 hover:text-red-700 text-sm">
                            Remove photo
                        </button>
                    </div>
                </div>
            </div>

            <!-- Photo Title -->
            <div class="mb-6">
                <label for="judul" class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input 
                    type="text" 
                    id="judul" 
                    name="judul" 
                    required
                    maxlength="255"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none"
                    placeholder="Give your photo a title"
                >
            </div>

            <!-- Photo Description -->
            <div class="mb-6">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Description <span class="text-gray-400">(Optional)</span></label>
                <textarea 
                    id="deskripsi" 
                    name="deskripsi" 
                    rows="3"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none resize-none"
                    placeholder="Add a description for your photo"
                ></textarea>
            </div>

            <!-- Album Selection -->
            <div class="mb-6">
                <label for="album" class="block text-sm font-medium text-gray-700 mb-2">Album <span class="text-gray-400">(Optional)</span></label>
                <select 
                    id="album" 
                    name="album_id"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all outline-none bg-white"
                >
                    <option value="">No album</option>
                    <?php foreach ($albums as $album): ?>
                        <option value="<?php echo $album['AlbumID']; ?>"><?php echo e($album['NamaAlbum']); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($albums)): ?>
                    <p class="text-gray-400 text-sm mt-1">You haven't created any albums yet.</p>
                <?php endif; ?>
            </div>

            <!-- Privacy -->
            <div class="mb-8">
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

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full py-4 px-6 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-purple-600 hover:to-indigo-700 focus:ring-4 focus:ring-purple-200 transition-all transform hover:scale-[1.02] active:scale-[0.98]"
            >
                Upload Photo
            </button>
        </form>
    </main>

    <script>
        const uploadArea = document.getElementById('upload-area');
        const placeholder = document.getElementById('upload-placeholder');
        const preview = document.getElementById('upload-preview');
        const previewImage = document.getElementById('preview-image');
        const fileName = document.getElementById('file-name');
        const fileInput = document.getElementById('photo');

        // Drag and drop handlers
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect(fileInput);
            }
        });

        function handleFileSelect(input) {
            const file = input.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, or WebP)');
                    input.value = '';
                    return;
                }

                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must not exceed 5MB');
                    input.value = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    fileName.textContent = file.name;
                    placeholder.classList.add('hidden');
                    preview.classList.remove('hidden');
                    uploadArea.classList.add('has-file');
                };
                reader.readAsDataURL(file);
            }
        }

        function clearFile(e) {
            e.stopPropagation();
            fileInput.value = '';
            placeholder.classList.remove('hidden');
            preview.classList.add('hidden');
            uploadArea.classList.remove('has-file');
        }
    </script>
</body>
</html>
