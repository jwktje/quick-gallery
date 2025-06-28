# PHP Photo Gallery

A lightweight, responsive photo gallery application built with PHP and Tailwind CSS.

## Setup

1. **Deployment**: Upload all files to a public directory on your web server (Apache recommended for `.htaccess` support)

2. **Configuration**: Edit `config.php` to customize your gallery name and settings

3. **Place your photos**: Create subdirectories in the `albums/` folder and add your images
   ```
   albums/
   ├── vacation-2023/
   │   ├── beach.jpg
   │   └── sunset.png
   └── family-photos/
       ├── birthday.jpg
       └── wedding.gif
   ```

4. **Requirements**: PHP with GD extension enabled for thumbnail generation

### Thumbnail System
The `thumb.php` script automatically generates and caches thumbnails:
- Thumbnails are created on-demand when first requested
- Cached in the `thumbs/` directory to improve performance
- Maintains aspect ratio and handles transparency (PNG/GIF)
- Security: Only allows access to images within the `albums/` directory
- **Cache busting**: Delete the `thumbs/` directory to regenerate all thumbnails

## Configuration

Edit `config.php` to customize your gallery.