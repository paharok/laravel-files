<?php
namespace Paharok\Laravelfiles\Helpers\Intervention;

use Intervention\Image\ImageManager;

class AdapterFactory
{
    /** Pick the adapter matching the installed Intervention Image major version. */
    public static function make(): ImageAdapter
    {
        // decodePath() exists only in v4 (v3 uses read()).
        if (method_exists(ImageManager::class, 'decodePath')) {
            return new V4Adapter();
        }

        return new V3Adapter();
    }
}
