<?php
namespace Paharok\Laravelfiles\Helpers\Intervention;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

/**
 * Intervention Image 4.x
 * Renamed vs v3: read() -> decodePath(), create() -> createImage(), place() -> insert().
 */
class V4Adapter extends AbstractAdapter
{
    private $manager;

    public function __construct()
    {
        $this->manager = ImageManager::usingDriver(GdDriver::class);
    }

    public function read(string $path): object
    {
        return $this->manager->decodePath($path);
    }

    public function canvas(int $width, int $height, string $color): object
    {
        return $this->manager->createImage($width, $height)->fill($color);
    }

    public function place(object $canvas, object $image, string $position): object
    {
        return $canvas->insert($image, 0, 0, $position);
    }
}
