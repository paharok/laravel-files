<?php
namespace Paharok\Laravelfiles\Helpers\Intervention;

use Intervention\Image\Encoders\BmpEncoder;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;

/**
 * Logic shared by v3 and v4: cover(), scaleDown() and the encoder classes
 * have the same names and constructors in both versions.
 */
abstract class AbstractAdapter implements ImageAdapter
{
    public function cover(object $image, int $width, int $height, string $position): object
    {
        return $image->cover($width, $height, $position);
    }

    public function scaleDown(object $image, int $width, int $height): object
    {
        return $image->scaleDown($width, $height);
    }

    public function save(object $image, string $path, string $extension, int $quality): void
    {
        $image->encode($this->encoder($extension, $quality))->save($path);
    }

    protected function encoder(string $extension, int $quality): object
    {
        switch (strtolower($extension)) {
            case 'png':
                return new PngEncoder();
            case 'webp':
                return new WebpEncoder($quality);
            case 'gif':
                return new GifEncoder();
            case 'bmp':
                return new BmpEncoder();
            default:
                return new JpegEncoder($quality);
        }
    }
}
