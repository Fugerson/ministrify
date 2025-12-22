<?php
/**
 * Generate PWA icons
 * Run: php generate-icons.php
 */

$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

foreach ($sizes as $size) {
    $image = imagecreatetruecolor($size, $size);

    // Background color #3b82f6
    $bg = imagecolorallocate($image, 59, 130, 246);
    imagefill($image, 0, 0, $bg);

    // White color for cross
    $white = imagecolorallocate($image, 255, 255, 255);

    // Draw a simple cross
    $crossWidth = $size * 0.15;
    $crossHeight = $size * 0.6;
    $centerX = $size / 2;
    $centerY = $size / 2;

    // Vertical bar
    imagefilledrectangle(
        $image,
        $centerX - $crossWidth / 2,
        $centerY - $crossHeight / 2,
        $centerX + $crossWidth / 2,
        $centerY + $crossHeight / 2,
        $white
    );

    // Horizontal bar
    $hBarWidth = $size * 0.4;
    $hBarTop = $centerY - $crossHeight / 2 + $crossHeight * 0.25;
    imagefilledrectangle(
        $image,
        $centerX - $hBarWidth / 2,
        $hBarTop - $crossWidth / 2,
        $centerX + $hBarWidth / 2,
        $hBarTop + $crossWidth / 2,
        $white
    );

    // Save
    $filename = __DIR__ . "/icon-{$size}x{$size}.png";
    imagepng($image, $filename);
    imagedestroy($image);

    echo "Created: $filename\n";
}

echo "Done!\n";
