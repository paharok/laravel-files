<?php

namespace Paharok\Laravelfiles\Http\Controllers;
use Paharok\Laravelfiles\Http\Controllers\Controller;
use Paharok\Laravelfiles\LaravelFiles AS LaravelFiles;
use Illuminate\Http\Request;

class LaravelFilesController extends Controller
{

    public function index(LaravelFiles $laravelFiles, Request $request){

         $path = $laravelFiles->getPathToFiles() . $request->input('path');

         $files = $laravelFiles->getFilesFromDir($path);
         $data['files'] = $laravelFiles->formatedFiles($files,$path);

         $data['files'] = $laravelFiles->sortFiles($data['files']);

         $data['currentFolder'] = $request->input('path');
         $data['breadcrumbs'] = $laravelFiles->generateBreadcrumbs($request->input('path'));

         return view("laravelfiles::index",$data);
    }

    public function newFolder(LaravelFiles $laravelFiles, Request $request){
        if(!$request->input('foldername')){
            return response()->json(['error'=>trans('laravelfiles::plf.errorEmptyField')],200);
        }

        $currentFolder = $laravelFiles->getPathToFiles() . $request->input('currentFolder');

        $folderName = $laravelFiles->setName($request->input('foldername'),$currentFolder);

        $laravelFiles->makeDirectory($currentFolder . '/' . $folderName);

        return response()->json([$folderName],200);
    }


    public function newFile(LaravelFiles $laravelFiles, Request $request){
        $currentFolder = $laravelFiles->getPathToFiles() . $request->input('folder');
        for($i=0;$request->hasFile('file-'.$i);$i++){
            $file = $request->file('file-'.$i);
            if($file->isValid()){

                $fileName = $laravelFiles->setName($file->getClientOriginalName(),$currentFolder);
                $file->move($currentFolder,$fileName);
                $laravelFiles->makeThumbnails($currentFolder,$fileName);
            }
        }

        return response()->json($request->all(),200);
    }


    public function removeFile(LaravelFiles $laravelFiles, Request $request){
        $filePath = $request->input('path');
        $pathInfo = pathinfo($filePath);

        if(file_exists($laravelFiles->getPathToFiles() . $filePath)){
            unlink($laravelFiles->getPathToFiles() . $filePath);
        }

        $thumbsDir = $laravelFiles->getPathToFiles() . $pathInfo['dirname'] . '/__thumbnails__';
        if(is_dir($thumbsDir)){
            $laravelFiles->removeThumbnails($thumbsDir,$pathInfo['filename']);
        }

        return response()->json(['success'=>'ok'],200);
    }

    public function removeDir(LaravelFiles $laravelFiles, Request $request){
        $dirPath = $request->input('path');
        if(is_dir($laravelFiles->getPathToFiles() . $dirPath) &&  $laravelFiles->deleteDirectory($laravelFiles->getPathToFiles() . $dirPath)){
            return response()->json(['success'=>'ok'],200);
        }
        return response()->json(['errors'=>['err1'=>trans('laravelfiles::plf.errorSomethingWrong')]],200);
    }

    public function search(LaravelFiles $laravelFiles, Request $request){
        $currentFolder = $laravelFiles->getPathToFiles() . $request->input('currentFolder');
        $s = $request->input('s');

        if(!$s){
            return response()->json(['errors'=>['err'=>'err']],200);
        }

        $files = $laravelFiles->searchFiles($currentFolder,$s);
        $data['files'] = $laravelFiles->formatedFiles($files,$currentFolder);

        $returnHTML = view('laravelfiles::partials.items',$data)->render();

        return response()->json(['success'=>'ok','html'=>$returnHTML],200);
    }




}
