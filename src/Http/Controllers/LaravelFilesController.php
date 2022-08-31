<?php

namespace Paharok\Laravelfiles\Http\Controllers;
use App\Http\Controllers\Controller;
use That0n3guy\Transliteration\Transliteration AS Transliteration;

use File;

use Illuminate\Http\Request;

class LaravelFilesController extends Controller
{
    private $imagesResize = ['jpg','jpeg','png','webp'];

    private $imagesorigin = ['svg'];

    private $exclude = [
        '.',
        '..',
        'no-img.jpg',
        'no-img.png',
    ];

    private $pathToFiles;
    //

    public function __construct(){
        $this->pathToFiles = public_path('vendor/laravel-files/images');
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
            }
        }

        return response()->json($request->all(),200);
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
                    'type' => is_dir($dir . '/' . $file)?'dir':'file',
                ];
                if($filesList[$key]['type'] == 'file'){
                    $path_parts = pathinfo($filesList[$key]['path']);
                    $filesList[$key]['extension'] = $path_parts['extension'] ?? '';
                    $filesList[$key]['filename'] = $path_parts['filename'] ?? '';
                    $filesList[$key]['extension'] = $path_parts['extension'] ?? '';
                    $filesList[$key]['date'] = filemtime(($filesList[$key]['path'])) ?? '';
                }
            }
        }
        return $filesList;
    }

    private function getFilesFromDir($dir){
        $files = scandir($dir);
        return $files;
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
