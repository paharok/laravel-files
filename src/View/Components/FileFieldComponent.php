<?php

namespace Paharok\Laravelfiles\View\Components;

use Illuminate\View\Component;
use Paharok\Laravelfiles\LaravelFiles AS LaravelFiles;

class FileFieldComponent extends Component
{
    /**
     * Field name
     *
     * @var string
     */
    public string $name;

    /**
     * Field value
     *
     * @var string
     */
    public string $value;

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
    public function __construct(LaravelFiles $laravelFiles,string $name = '',string $value = '')
    {
        //
        $this->name = $name;
        $this->thumbnail = $this->placeholder;
        $this->value = $value;

        if(!empty($value) && file_exists(public_path() . '/' . $value)){

            $this->thumbnail = $laravelFiles->getThumbnailURI(public_path($value));
            $fileInfo = pathinfo($value);
            if(!empty($fileInfo['basename'])){
                $this->fileName = $fileInfo['basename'];
            }
            $this->extension = $fileInfo['extension']?:'';
            $this->needExtension = $laravelFiles->needExtension($this->thumbnail);
        }else{
            $this->value ='';
        }

    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('laravelfiles::components.file-field-component');
    }
}
