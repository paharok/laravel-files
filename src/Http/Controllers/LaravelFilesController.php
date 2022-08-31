<?php

namespace Paharok\Laravelfiles\Http\Controllers;
use App\Http\Controllers\Controller;
use That0n3guy\Transliteration\Transliteration AS Transliteration;
use Paharok\Laravelfiles\Helpers\ChangeImageIntervention as ChangeImage;

use File;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class LaravelFilesController extends Controller
{
    private $imagesResize = ['jpg','jpeg','png','webp'];

    private $imagesOrigin = ['svg'];

    private $exclude = [
        '.',
        '..',
        'no-img.jpg',
        'no-img.png',
        '__thumbnails__',
    ];

    private $pathToFiles;

    private $filesFolder = 'vendor/laravel-files/files';
    //

    public function __construct(){
        $this->pathToFiles = public_path($this->filesFolder);
    }

    public function index(Request $request){

         $path = $this->pathToFiles . $request->input('path');

         $files = $this->getFilesFromDir($path);
         $data['files'] = $this->formatedFiles($files,$path);

         $data['files'] = $this->sortFiles($data['files']);

         $data['currentFolder'] = $request->input('path');
         $data['breadcrumbs'] = $this->generateBreadcrumbs($request->input('path'));

         return view("laravelfiles::index",$data);
    }

    public function newFolder(Request $request){
        if(!$request->input('foldername')){
            return response()->json(['error'=>'Пустое поле'],200);
        }

        $currentFolder = $this->pathToFiles . $request->input('currentFolder');

        $folderName = $this->setName($request->input('foldername'),$currentFolder);

        File::makeDirectory($currentFolder . '/' . $folderName);

        return response()->json([$folderName],200);
    }


    public function newFile(Request $request){
        $currentFolder = $this->pathToFiles . $request->input('folder');
        for($i=0;$request->hasFile('file-'.$i);$i++){
            $file = $request->file('file-'.$i);
            if($file->isValid()){

                $fileName = $this->setName($file->getClientOriginalName(),$currentFolder);
                $file->move($currentFolder,$fileName);
                $this->makeThumbnails($currentFolder,$fileName);
            }
        }

        return response()->json($request->all(),200);
    }

    private function makeThumbnails($folder, $fileName){
        $this->checkThumbnailFolder($folder);
        try {
            $fileInfo = pathinfo($folder . '/' . $fileName);
            if(in_array(strtolower($fileInfo['extension']),$this->imagesResize)){
                $filePublicPath = str_replace(public_path(),'',($folder . '/' . $fileName));

                ChangeImage::changeImage($filePublicPath,100,100);
            }
        }catch (Exception $e){
            echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
        }


    }

    private function checkThumbnailFolder($folder){
        if(!is_dir($folder . '/__thumbnails__')){
            File::makeDirectory($folder . '/__thumbnails__');
        }
    }

    private function formatedFiles($files,$dir='/'){
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
                ];
                if($filesList[$key]['type'] == 'file'){
                    $path_parts = pathinfo($filesList[$key]['path']);
                    $filesList[$key]['extension'] = $path_parts['extension'] ?? '';
                    $filesList[$key]['filename'] = $path_parts['filename'] ?? '';
                    $filesList[$key]['extension'] = $path_parts['extension'] ?? '';
                    $filesList[$key]['date'] = filemtime(($filesList[$key]['path'])) ?? '';
                    $filesList[$key]['thumbnail'] = $this->getThumbnailURI($filesList[$key]['path']);
                }
            }
        }
        return $filesList;
    }

    private function getFilesFromDir($dir){
        $files = scandir($dir);
        return $files;
    }

    private function getThumbnailURI($file){
        $fileInfo = pathinfo($file);
        $thumbnailName = $fileInfo['filename'] . '100_100fit.' . $fileInfo['extension'];
        $prePath = str_replace($this->pathToFiles,'',$fileInfo['dirname']);
        if(in_array(strtolower($fileInfo['extension']),$this->imagesOrigin)){
            return env('APP_URL') . '/' . $this->filesFolder .  $prePath . '/' . $fileInfo['basename'];
        }else if(file_exists($fileInfo['dirname'] . '/__thumbnails__/'.$thumbnailName)){
           return env('APP_URL') . '/' . $this->filesFolder .  $prePath . '/__thumbnails__/'.$thumbnailName;
        }
    }


    private function sortFiles($files){
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

    private function setName($name,$dir){
        $transliteration = new Transliteration();
        $newName = $transliteration->clean_filename($name);

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


    private function generateBreadcrumbs($path){
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


}
