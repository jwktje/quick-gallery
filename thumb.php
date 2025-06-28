<?php
// Thumbnail Generator with Caching
// Usage: thumb.php?path=albums/albumname/image.jpg&size=300

// Load configuration
$config = require_once 'config.php';

// Get parameters
$imagePath = $_GET['path'] ?? '';
$size = (int)($_GET['size'] ?? $config['default_thumb_size']);

// Validate parameters
if (empty($imagePath) || $size <= 0 || $size > $config['max_thumbnail_size']) {
    http_response_code(400);
    exit('Invalid parameters');
}

// Security: Ensure path is within allowed directory
$realPath = realpath(__DIR__ . '/' . $imagePath);
$allowedPath = realpath(__DIR__ . '/albums');

if (!$realPath || !$allowedPath || strpos($realPath, $allowedPath) !== 0) {
    http_response_code(403);
    exit('Access denied');
}

// Check if source image exists
if (!file_exists($realPath)) {
    http_response_code(404);
    exit('Image not found');
}

// Create thumbs directory if it doesn't exist
$thumbsDir = __DIR__ . '/thumbs';
if (!is_dir($thumbsDir)) {
    mkdir($thumbsDir, 0755, true);
}

// Generate thumbnail path
$pathInfo = pathinfo($imagePath);
$thumbPath = $thumbsDir . '/' . md5($imagePath . '_' . $size) . '.jpg';

// Check if thumbnail already exists and is newer than source
if (file_exists($thumbPath) && filemtime($thumbPath) >= filemtime($realPath)) {
    // Serve existing thumbnail
    header('Content-Type: image/jpeg');
    header('Cache-Control: public, max-age=86400'); // Cache for 1 day
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($thumbPath)) . ' GMT');
    readfile($thumbPath);
    exit;
}

// Get image info
$imageInfo = getimagesize($realPath);
if (!$imageInfo) {
    http_response_code(400);
    exit('Invalid image');
}

$sourceWidth = $imageInfo[0];
$sourceHeight = $imageInfo[1];
$mimeType = $imageInfo['mime'];

// Calculate thumbnail dimensions (maintain aspect ratio)
if ($sourceWidth > $sourceHeight) {
    $thumbWidth = $size;
    $thumbHeight = (int)(($size * $sourceHeight) / $sourceWidth);
} else {
    $thumbHeight = $size;
    $thumbWidth = (int)(($size * $sourceWidth) / $sourceHeight);
}

// Create source image resource
$sourceImage = null;
switch ($mimeType) {
    case 'image/jpeg':
        $sourceImage = imagecreatefromjpeg($realPath);
        break;
    case 'image/png':
        $sourceImage = imagecreatefrompng($realPath);
        break;
    case 'image/gif':
        $sourceImage = imagecreatefromgif($realPath);
        break;
    case 'image/webp':
        if (function_exists('imagecreatefromwebp')) {
            $sourceImage = imagecreatefromwebp($realPath);
        }
        break;
}

if (!$sourceImage) {
    http_response_code(400);
    exit('Unsupported image format');
}

// Create thumbnail image
$thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

// Handle transparency for PNG and GIF
if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
    imagealphablending($thumbImage, false);
    imagesavealpha($thumbImage, true);
    $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
    imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
} else {
    // For JPEG, fill with white background
    $white = imagecolorallocate($thumbImage, 255, 255, 255);
    imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $white);
}

// Resize image
imagecopyresampled(
    $thumbImage, $sourceImage,
    0, 0, 0, 0,
    $thumbWidth, $thumbHeight,
    $sourceWidth, $sourceHeight
);

// Save thumbnail to cache
imagejpeg($thumbImage, $thumbPath, $config['thumbnail_quality']);

// Clean up memory
imagedestroy($sourceImage);
imagedestroy($thumbImage);

// Serve the thumbnail
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400'); // Cache for 1 day
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($thumbPath)) . ' GMT');
readfile($thumbPath);
?> 