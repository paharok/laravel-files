<?php

namespace Paharok\Laravelfiles;

use Illuminate\Support\Str;
use Paharok\Laravelfiles\Helpers\ChangeImageIntervention as ChangeImage;

use File;

class LaravelFiles
{
    private $imagesResize = ['jpg','jpeg','png','webp'];

    private $fileIco = '__file_ico__.svg';

    private $imagesOrigin = ['svg'];

    private $exclude = [
        '.',
        '..',
        'no-img.jpg',
        'no-img.png',
        '__thumbnails__',
        '__file_ico__.svg',
    ];

    private $pathToFiles;

    private $filesFolder = 'vendor/laravel-files/files';

    public function __construct(){
        $this->pathToFiles = public_path($this->filesFolder);
    }

    public function getPathToFiles(){
        return $this->pathToFiles;
    }

    public function searchFiles($currentFolder,$s){
        $files = $this->getFilesFromDir($currentFolder);
        foreach ($files as $key=>$file){
            if(in_array($file,$this->exclude) || !stristr($file,$s)){
                unset($files[$key]);
            }
        }
        return $files;
    }

    public function removeThumbnails($thumbsDir,$filename){
        $files = $this->getFilesFromDir($thumbsDir);
        foreach ($files as $file){
            if(in_array($file,$this->exclude)){
                continue;
            }
            $fileInfo = pathinfo($file);
            if(!strstr($fileInfo['filename'],$filename)){
                continue;
            }
            $withoutMainName = str_replace(
                [$filename,'fit','resize','resizebg'],
                ['','','',''],
                $fileInfo['filename']
            );

            preg_match("/^[0-9]{1,}[_][0-9]{1,}$/",$withoutMainName,$matches);
            if(!empty($matches)){
                unlink($thumbsDir . '/' . $file);
            }
        }
    }

    public function makeThumbnails($folder, $fileName){
        $this->checkThumbnailFolder($folder);
        $fileInfo = pathinfo($folder . '/' . $fileName);
        if(in_array(strtolower($fileInfo['extension']),$this->imagesResize)){
            $filePublicPath = str_replace(public_path(),'',($folder . '/' . $fileName));

            ChangeImage::changeImage($filePublicPath,100,100);
        }
    }

    private function checkThumbnailFolder($folder){
        if(!is_dir($folder . '/__thumbnails__')){
            File::makeDirectory($folder . '/__thumbnails__');
        }
    }

    public function formatedFiles($files,$dir='/'){
        $prePath = str_replace($this->pathToFiles,'',$dir);


        $filesList = [];
        foreach($files as $key=>$file){
            if(!in_array($file,$this->exclude)){
                $filesList[$key] = [
                    'name' => $file,
                    'path' => $dir . '/' . $file,
                    'minPath' =>  $prePath . '/' .  $file,
                    'pulicPath' =>  $this->filesFolder . $prePath .'/'. $file,
                    'type' => is_dir($dir . '/' . $file)?'dir':'file',
                    'url' => env('APP_URL') . '/' . $this->filesFolder . $prePath .'/'. $file,
                    'needExtension'=>false,
                ];
                if($filesList[$key]['type'] == 'file'){
                    $path_parts = pathinfo($filesList[$key]['path']);
                    $filesList[$key]['extension'] = $path_parts['extension'] ?? '';
                    $filesList[$key]['filename'] = $path_parts['filename'] ?? '';
                    $filesList[$key]['extension'] = $path_parts['extension'] ?? '';
                    $filesList[$key]['date'] = filemtime(($filesList[$key]['path'])) ?? '';
                    $filesList[$key]['thumbnail'] = $this->getThumbnailURI($filesList[$key]['path']);
                    $filesList[$key]['needExtension'] = $this->needExtension($filesList[$key]['thumbnail']);
                }
            }
        }
        return $filesList;
    }

    public function needExtension($file){
        $fileIco = '/' . $this->fileIco;
        if(strstr($file,$fileIco)){
            return true;
        }
        return false;
    }

    public function getFilesFromDir($dir){
        $files = scandir($dir);
        return $files;
    }


    public function getThumbnailURI($file){
        $fileInfo = pathinfo($file);
        $thumbnailName = $fileInfo['filename'] . '100_100fit.' . $fileInfo['extension'];
        $prePath = str_replace($this->pathToFiles,'',$fileInfo['dirname']);
        if(in_array(strtolower($fileInfo['extension']),$this->imagesOrigin)){
            return env('APP_URL') . '/' . $this->filesFolder .  $prePath . '/' . $fileInfo['basename'];
        }else if(file_exists($fileInfo['dirname'] . '/__thumbnails__/'.$thumbnailName)){
            return env('APP_URL') . '/' . $this->filesFolder .  $prePath . '/__thumbnails__/'.$thumbnailName;
        }else{
            return env('APP_URL') . '/' . $this->filesFolder .  '/' . $this->fileIco;
        }
    }


    public function sortFiles($files){
        $dirsArray = [];
        $filesArray = [];
        foreach($files as $file){
            if($file['type']=='dir'){
                $dirsArray[] = $file;
            }else{
                $filesArray[] = $file;
            }
        }
        usort($filesArray, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_merge($dirsArray,$filesArray);
    }

    public function setName($name,$dir){

        $newName = Str::slug($name);

        $files = $this->getFilesFromDir($dir);

        $newName = $this->uniqName($newName,$files);

        return $newName;
    }

    private function uniqName($name,$files){
        if(in_array($name,$files)){
            $name = '1_' . $name;
            $name = $this->uniqName($name,$files);
        }
        return $name;
    }


    public function generateBreadcrumbs($path){
        $breadCrumbs = [['title'=>'Home','path'=>'']];
        $toPath = '';
        $breadcrumbsItems = explode('/',$path);
        if($breadcrumbsItems){
            foreach ($breadcrumbsItems as $bcItem) {
                if ($bcItem){
                    $toPath .= '/' . $bcItem;
                    $breadCrumbs[] = ['title' => $bcItem, 'path' => $toPath];
                }
            }
        }
        return $breadCrumbs;
    }

    public function makeDirectory($dir){
        return File::makeDirectory($dir);
    }
    public function deleteDirectory($dir){
        return File::deleteDirectory($dir);
    }

}
