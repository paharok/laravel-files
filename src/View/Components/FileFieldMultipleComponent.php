<?php

namespace Paharok\Laravelfiles\View\Components;

use Illuminate\View\Component;
use Paharok\Laravelfiles\LaravelFiles AS LaravelFiles;

class FileFieldMultipleComponent extends Component
{
    /**
     * Field name
     *
     * @var string
     */
    public string $name;


    /**
     * Field values
     *
     * @var array
     */
    public array $values = [];

    /**
     * Field value
     *
     * @var string
     */
    public string $thumbnail = '/vendor/laravel-files/files/no-img.png';

    /**
     * Field value
     *
     * @var string
     */
    public string $fileName = "";

    /**
     * Field value
     *
     * @var string
     */
    public string $placeholder = '/vendor/laravel-files/files/no-img.png';

    /**
     * Field value
     *
     * @var string
     */
    public string $extension = '';

    /**
     * @var bool
     */
    public bool $needExtension = false;

    /**
     * Create a new component instance.
     *
     * @param LaravelFiles $laravelFiles
     * @param string $name
     * @param string $value
     * @return void
     */
    public function __construct(LaravelFiles $laravelFiles,string $name = '', array $values = [])
    {
        //
        $this->name = $name;

        foreach ($values as $value) {
            if(!empty($value) && file_exists(public_path() . '/' . $value)) {

                $fileInfo = pathinfo($value);
                $thumbnail = $laravelFiles->getThumbnailURI(public_path($value));
                $this->values[] = [
                    'value' => $value,
                    'thumbnail' => $thumbnail,
                    'fileName' => $fileInfo['basename'] ?? '',
                    'extension' => $fileInfo['extension'] ?? '',
                    'needExtension' => $laravelFiles->needExtension($thumbnail)
                ];
            }
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('laravelfiles::components.file-field-multiple-component');
    }
}
