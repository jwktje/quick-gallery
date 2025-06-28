<?php
// Photo Gallery Configuration

return [
    // Gallery Settings
    'gallery_name' => 'Quick Gallery',
    'gallery_description' => 'Description of the gallery',
    
    // Thumbnail Settings
    'default_thumb_size' => 200,
    'album_thumb_size' => 300,
    'thumbnail_quality' => 85,
    
    // Display Settings
    'images_per_row' => [
        'xs' => 2,  // Mobile
        'sm' => 3,  // Small tablets
        'md' => 4,  // Medium screens
        'lg' => 5,  // Large screens
        'xl' => 6   // Extra large screens
    ],
    
    // Supported image formats
    'supported_formats' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    
    // Security
    'max_thumbnail_size' => 1000
]; 