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
        $searchTerm = str_ireplace([' ', '-', '_','"', "'"], '', $s);
        foreach ($files as $key=>$file){
            $normalizedFile = str_ireplace([' ', '-', '_', '"', "'"], '', $file);
            if(in_array($file,$this->exclude) || !stristr($normalizedFile, $searchTerm)){
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
                [$filename,'fit','resizebg','resize'],
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
                    'publicPath' =>  $this->filesFolder . $prePath .'/'. $file,
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

    public function setName($name,$dir,$is_file = true){

        if($is_file){
            $dotPosition = mb_strripos($name,'.');
            if($dotPosition>=0){
                $ext = mb_substr($name,$dotPosition);
                $nameWithoutExt = str_replace($ext,'',$name);
                $newName = Str::slug($nameWithoutExt) . $ext;
            }else{
                $newName = Str::slug($name);
            }
        }else{
            $newName = Str::slug($name);
        }



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


    public function renameItem($path,$newName)
    {
        $fullPath = $this->getPathToFiles() . $path;

        if(!file_exists($fullPath)){
            return ['errors'=>['no file'=>__('laravelfiles::plf.No such file or directory')]];
        }

        $pathInfo = pathinfo($fullPath);

        $data = [
            'success'=>true,
        ];

        if(is_dir($fullPath)){
            if($pathInfo['basename'] === $newName){
                return ['errors'=>['no change name'=>__("laravelfiles::plf.It's same name!")]];
            }
            $newNameUniq = $this->setName($newName,$pathInfo['dirname'],false);

            File::move($fullPath, $pathInfo['dirname'] . '/' . $newNameUniq);

            if($newNameUniq !== $newName){
                $data['info'] = __('laravelfiles::plf.rename_exists', [
                    'newname' => $newName,
                    'newnameuniq' => $newNameUniq
                ]);
            }

        }else{
            if($pathInfo['filename'] === $newName){
                return ['errors'=>['no change name'=>__("laravelfiles::plf.It's same name!")]];
            }

            $thumbsDir =  $pathInfo['dirname'] . '/__thumbnails__';

            if(is_dir($thumbsDir)){
                $this->removeThumbnails($thumbsDir,$pathInfo['filename']);
            }

            $newNameUniq = $this->setName($newName . '.' . $pathInfo['extension'],$pathInfo['dirname']);

            File::move($fullPath, $pathInfo['dirname'] . '/' . $newNameUniq);

            $this->makeThumbnails($pathInfo['dirname'],$newNameUniq);

            if($newNameUniq !== ($newName . '.' . $pathInfo['extension'])){
                $data['info'] = __('laravelfiles::plf.rename_exists', [
                    'newname' => $newName . '.' . $pathInfo['extension'],
                    'newnameuniq' => $newNameUniq
                ]);
            }
        }

        return $data;

    }


    public function groupRemove($items)
    {
        foreach ($items as $item) {
            $fullPath = $this->getPathToFiles() . $item;
            if(!file_exists($fullPath)){
                continue;
            }
            if(is_dir($fullPath)){
                $this->deleteDirectory($fullPath);
            }else{
                $pathInfo = pathinfo($fullPath);

                unlink($fullPath);

                $thumbsDir = $pathInfo['dirname'] . '/__thumbnails__';
                if(is_dir($thumbsDir)){
                    $this->removeThumbnails($thumbsDir,$pathInfo['filename']);
                }
            }
        }
        return ['success'=>true];
    }

    public function groupCopy($items, $path, $move = false)
    {
        $fullPathDesctination = $this->getPathToFiles() . $path;
        foreach ($items as $item) {
            $fullPath = $this->getPathToFiles() . $item;
            if(!file_exists($fullPath)){
                continue;
            }
            $fileNameWithExtension = basename($fullPath);

            $newNameUniq = $this->setName($fileNameWithExtension,$fullPathDesctination);



            if(is_dir($fullPath)){
                if (strpos(realpath($fullPathDesctination), realpath($fullPath)) === 0) {
                    continue;
                }

                $newDirectoryPath = $fullPathDesctination . '/' . $newNameUniq;

                File::makeDirectory($newDirectoryPath,0755,true);

                File::copyDirectory($fullPath, $newDirectoryPath);


                if($move){
                    File::deleteDirectory($fullPath);
                }

            }else{
                $newFilePath = $fullPathDesctination . '/' . $newNameUniq;
                File::copy($fullPath, $newFilePath);

                $this->makeThumbnails($fullPathDesctination,$newNameUniq);

                if($move){
                    $pathInfo = pathinfo($fullPath);

                    unlink($fullPath);

                    $thumbsDir = $pathInfo['dirname'] . '/__thumbnails__';
                    if(is_dir($thumbsDir)){
                        $this->removeThumbnails($thumbsDir,$pathInfo['filename']);
                    }
                }

            }
        }
        return ['success'=>true];
    }

}
