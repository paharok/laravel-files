<?php

namespace Paharok\Laravelfiles\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearThumbnailsCommand extends Command
{
    protected $signature = 'laravel-files:clear-thumbnails {path? : Шлях відносно public/ для пошуку папок мініатюр (за замовчуванням vendor/laravel-files/files)}';

    protected $description = 'Рекурсивно шукає папки __thumbnails__ у вказаному шляху (відносно public/) та видаляє їх разом із вмістом';

    public function handle()
    {
        $path = $this->argument('path') ?: 'vendor/laravel-files/files';

        if($this->hasParentTraversal($path)){
            $this->error('Шлях не може містити "..". Дозволені лише шляхи всередині public/.');
            return self::FAILURE;
        }

        $searchPath = public_path($path);

        if(!File::isDirectory($searchPath)){
            $this->error("Директорія не знайдена: {$searchPath}");
            return self::FAILURE;
        }

        $this->info("Пошук папок __thumbnails__ у: {$searchPath}");

        $visited = [];
        $thumbnailDirs = $this->findThumbnailDirs($searchPath, $visited);

        if(empty($thumbnailDirs)){
            $this->info('Папок з мініатюрами не знайдено.');
            return self::SUCCESS;
        }

        foreach($thumbnailDirs as $dir){
            File::deleteDirectory($dir);
            $this->line("Видалено: {$dir}");
        }

        $this->info('Готово. Видалено папок: ' . count($thumbnailDirs));

        return self::SUCCESS;
    }

    private function hasParentTraversal($path)
    {
        $normalized = str_replace('\\', '/', $path);
        $segments = explode('/', $normalized);
        return in_array('..', $segments, true);
    }

    private function findThumbnailDirs($dir, array &$visited)
    {
        $found = [];

        $realDir = realpath($dir);
        if($realDir === false || isset($visited[$realDir])){
            return $found;
        }
        $visited[$realDir] = true;

        $items = File::directories($dir);

        foreach($items as $item){
            if(basename($item) === '__thumbnails__'){
                $found[] = $item;
                continue;
            }
            $found = array_merge($found, $this->findThumbnailDirs($item, $visited));
        }

        return $found;
    }
}
