<?php
// Photo Gallery Application
// Load configuration
$config = require_once 'config.php';

// Get current view parameters
$album = $_GET['album'] ?? null;
$image = $_GET['image'] ?? null;

// Function to scan albums directory
function getAlbums() {
    $albums = [];
    $albumsDir = __DIR__ . '/albums';
    
    if (is_dir($albumsDir)) {
        $dirs = scandir($albumsDir);
        foreach ($dirs as $dir) {
            if ($dir !== '.' && $dir !== '..' && is_dir($albumsDir . '/' . $dir)) {
                $albums[] = $dir;
            }
        }
    }
    
    sort($albums);
    return $albums;
}

// Function to get images in an album
function getImagesInAlbum($albumName) {
    $images = [];
    $albumPath = __DIR__ . '/albums/' . $albumName;
    
    if (is_dir($albumPath)) {
        $files = scandir($albumPath);
        $imageExtensions = $GLOBALS['config']['supported_formats'];
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $imageExtensions)) {
                    $images[] = $file;
                }
            }
        }
    }
    
    sort($images);
    return $images;
}

// Function to get navigation for current image
function getImageNavigation($albumName, $currentImage) {
    $images = getImagesInAlbum($albumName);
    $currentIndex = array_search($currentImage, $images);
    
    $prev = null;
    $next = null;
    
    if ($currentIndex !== false) {
        if ($currentIndex > 0) {
            $prev = $images[$currentIndex - 1];
        }
        if ($currentIndex < count($images) - 1) {
            $next = $images[$currentIndex + 1];
        }
    }
    
    return ['prev' => $prev, 'next' => $next, 'current' => $currentIndex + 1, 'total' => count($images)];
}

$albums = getAlbums();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
        if ($image && $album) {
            echo htmlspecialchars($image) . ' - ' . htmlspecialchars($album);
        } elseif ($album) {
            echo htmlspecialchars($album) . ' Album';
        } else {
            echo htmlspecialchars($config['gallery_name']);
        }
    ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .thumbnail-hover {
            transition: all 0.3s ease;
        }
        .thumbnail-hover:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    
    <!-- Navigation Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-4">
            <nav class="flex items-center justify-between">
                <div class="flex items-center space-x-2 sm:space-x-4 min-w-0 flex-1">
                    <a href="/" class="text-xl sm:text-2xl font-bold text-gray-800 hover:text-blue-600 flex-shrink-0">📸 
                        <span class="hidden sm:inline"><?php echo htmlspecialchars($config['gallery_name']); ?></span>
                        <span class="sm:hidden">Gallery</span>
                    </a>
                    <?php if ($album): ?>
                        <span class="text-gray-400 hidden sm:inline">/</span>
                        <a href="/" class="text-gray-600 hover:text-blue-600 hidden sm:inline">Albums</a>
                        <span class="text-gray-400">/</span>
                        <span class="text-gray-800 font-medium truncate"><?php echo htmlspecialchars($album); ?></span>
                        <?php if ($image): ?>
                            <span class="text-gray-400 hidden md:inline">/</span>
                            <span class="text-gray-600 truncate max-w-[100px] md:max-w-xs hidden md:inline" title="<?php echo htmlspecialchars($image); ?>">
                                <?php echo htmlspecialchars(pathinfo($image, PATHINFO_FILENAME)); ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($image && $album): 
                    $nav = getImageNavigation($album, $image);
                ?>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                        <span><?php echo $nav['current']; ?> of <?php echo $nav['total']; ?></span>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        
        <?php if (!$album): ?>
            <!-- Albums Overview -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($config['gallery_name']); ?></h1>
                <p class="text-gray-600"><?php echo htmlspecialchars($config['gallery_description']); ?></p>
            </div>
            
            <?php if (empty($albums)): ?>
                <div class="text-center py-16">
                    <div class="text-6xl mb-4">📁</div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">No Albums Found</h2>
                    <p class="text-gray-500">Create some folders in the 'albums' directory and add your photos!</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <?php foreach ($albums as $albumName): 
                        $images = getImagesInAlbum($albumName);
                        $firstImage = !empty($images) ? $images[0] : null;
                    ?>
                        <a href="?album=<?php echo urlencode($albumName); ?>" 
                           class="group block bg-white rounded-lg shadow-md overflow-hidden thumbnail-hover">
                            <div class="aspect-square bg-gray-200 relative overflow-hidden">
                                <?php if ($firstImage): ?>
                                    <img src="thumb.php?path=<?php echo urlencode('albums/' . $albumName . '/' . $firstImage); ?>&size=<?php echo $config['album_thumb_size']; ?>" 
                                         alt="<?php echo htmlspecialchars($albumName); ?>"
                                         class="w-full h-full object-cover group-hover:opacity-90">
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <div class="text-center">
                                            <div class="text-4xl mb-2">📁</div>
                                            <div class="text-sm">Empty Album</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($albumName); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo count($images); ?> photos</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php elseif (!$image): ?>
            <!-- Album Contents -->
            <?php 
            $images = getImagesInAlbum($album);
            ?>
            
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($album); ?></h1>
                <p class="text-gray-600"><?php echo count($images); ?> photos in this album</p>
            </div>
            
            <?php if (empty($images)): ?>
                <div class="text-center py-16">
                    <div class="text-6xl mb-4">🖼️</div>
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">No Photos Found</h2>
                    <p class="text-gray-500">This album doesn't contain any images yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    <?php foreach ($images as $imageName): ?>
                        <a href="?album=<?php echo urlencode($album); ?>&image=<?php echo urlencode($imageName); ?>" 
                           class="group block bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md thumbnail-hover">
                            <div class="aspect-square bg-gray-200 overflow-hidden">
                                <img src="thumb.php?path=<?php echo urlencode('albums/' . $album . '/' . $imageName); ?>&size=<?php echo $config['default_thumb_size']; ?>" 
                                     alt="<?php echo htmlspecialchars($imageName); ?>"
                                     class="w-full h-full object-cover group-hover:opacity-90">
                            </div>
                            <div class="p-2">
                                <p class="text-xs text-gray-600 truncate" title="<?php echo htmlspecialchars($imageName); ?>">
                                    <?php echo htmlspecialchars(pathinfo($imageName, PATHINFO_FILENAME)); ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Single Image View - Lightbox Style -->
            <?php 
            $nav = getImageNavigation($album, $image);
            $imagePath = 'albums/' . $album . '/' . $image;
            ?>
            
            <!-- Lightbox Container -->
            <div class="fixed inset-0 bg-black bg-opacity-95 z-50" style="width: 100vw; height: 100vh; overflow: hidden;">
                <div class="h-full flex flex-col">
                    <!-- Header with Navigation -->
                    <div class="flex items-center justify-between p-4 bg-black bg-opacity-50 flex-shrink-0">
                        <a href="?album=<?php echo urlencode($album); ?>" 
                           class="inline-flex items-center px-3 py-2 bg-white bg-opacity-20 text-white rounded-lg hover:bg-opacity-30 transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            <span class="hidden sm:inline">Back to Album</span>
                            <span class="sm:hidden">Back</span>
                        </a>
                        
                        <div class="flex items-center space-x-2">
                            <?php if ($nav['prev']): ?>
                                <a href="?album=<?php echo urlencode($album); ?>&image=<?php echo urlencode($nav['prev']); ?>" 
                                   class="p-2 bg-white bg-opacity-20 text-white rounded-lg hover:bg-opacity-30 transition-all" 
                                   title="Previous image">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($nav['next']): ?>
                                <a href="?album=<?php echo urlencode($album); ?>&image=<?php echo urlencode($nav['next']); ?>" 
                                   class="p-2 bg-white bg-opacity-20 text-white rounded-lg hover:bg-opacity-30 transition-all" 
                                   title="Next image">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Main Image Container -->
                    <div class="flex-1 flex items-center justify-center p-4 min-h-0">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="<?php echo htmlspecialchars($image); ?>"
                             class="max-w-full max-h-full object-contain"
                             style="max-width: calc(100vw - 2rem); max-height: calc(100vh - 140px);">
                    </div>
                    
                    <!-- Image Info Footer -->
                    <div class="p-4 bg-black bg-opacity-50 text-white flex-shrink-0">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <h2 class="text-lg font-semibold truncate"><?php echo htmlspecialchars(pathinfo($image, PATHINFO_FILENAME)); ?></h2>
                            <div class="text-sm text-gray-300">
                                <span>Image <?php echo $nav['current']; ?> of <?php echo $nav['total']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Keyboard Navigation -->
            <script>
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowLeft' && <?php echo $nav['prev'] ? 'true' : 'false'; ?>) {
                        window.location.href = '?album=<?php echo urlencode($album); ?>&image=<?php echo urlencode($nav['prev'] ?? ''); ?>';
                    } else if (e.key === 'ArrowRight' && <?php echo $nav['next'] ? 'true' : 'false'; ?>) {
                        window.location.href = '?album=<?php echo urlencode($album); ?>&image=<?php echo urlencode($nav['next'] ?? ''); ?>';
                    } else if (e.key === 'Escape') {
                        window.location.href = '?album=<?php echo urlencode($album); ?>';
                    }
                });
            </script>
            
        <?php endif; ?>
        
    </main>
    
</body>
</html>
