<?php
namespace Paharok\Laravelfiles\Helpers\Intervention;

use Intervention\Image\ImageManager;

/** Intervention Image 3.x */
class V3Adapter extends AbstractAdapter
{
    private $manager;

    public function __construct()
    {
        $this->manager = ImageManager::gd();
    }

    public function read(string $path): object
    {
        return $this->manager->read($path);
    }

    public function canvas(int $width, int $height, string $color): object
    {
        return $this->manager->create($width, $height)->fill($color);
    }

    public function place(object $canvas, object $image, string $position): object
    {
        return $canvas->place($image, $position);
    }
}
