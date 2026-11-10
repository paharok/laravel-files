<?php
namespace Paharok\Laravelfiles\Helpers\Intervention;

/**
 * Thin wrapper over the parts of Intervention Image the package uses,
 * so ChangeImageIntervention works the same on v3 and v4.
 */
interface ImageAdapter
{
    /** Read an image from an absolute file path (EXIF orientation is auto-corrected). */
    public function read(string $path): object;

    /** Create a blank canvas filled with the given color. */
    public function canvas(int $width, int $height, string $color): object;

    /** Crop-to-fill to exactly $width x $height, keeping aspect ratio. */
    public function cover(object $image, int $width, int $height, string $position): object;

    /** Resize to fit inside $width x $height, keeping aspect ratio, never upsizing. */
    public function scaleDown(object $image, int $width, int $height): object;

    /** Place $image onto $canvas at the given position; returns the canvas. */
    public function place(object $canvas, object $image, string $position): object;

    /** Encode by file extension (jpg/png/webp/gif/bmp) and save to $path. */
    public function save(object $image, string $path, string $extension, int $quality): void;
}
