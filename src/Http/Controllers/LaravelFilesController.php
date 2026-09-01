<?php

namespace Paharok\Laravelfiles\Http\Controllers;
use Paharok\Laravelfiles\Http\Controllers\Controller;
use Paharok\Laravelfiles\LaravelFiles AS LaravelFiles;
use Illuminate\Http\Request;

class LaravelFilesController extends Controller
{

    public function index(LaravelFiles $laravelFiles, Request $request){
         if(!$request->ajax()){ abort(404); }

         $path = $laravelFiles->resolveSafePath($request->input('path'));
         if($path === null || !is_dir($path)){
             abort(404);
         }

         $files = $laravelFiles->getFilesFromDir($path);
         $data['files'] = $laravelFiles->formatedFiles($files,$path);

         $data['files'] = $laravelFiles->sortFiles($data['files']);

         $data['currentFolder'] = $request->input('path');
         $data['breadcrumbs'] = $laravelFiles->generateBreadcrumbs($request->input('path'));

         return view("laravelfiles::index",$data);
    }

    public function newFolder(LaravelFiles $laravelFiles, Request $request){
        if(!$request->ajax()){ abort(404); }

        if(!$request->input('foldername')){
            return response()->json(['error'=>trans('laravelfiles::plf.errorEmptyField')],200);
        }

        $currentFolder = $laravelFiles->resolveSafePath($request->input('currentFolder'));
        if($currentFolder === null || !is_dir($currentFolder)){
            return response()->json(['error'=>trans('laravelfiles::plf.errorInvalidPath')],422);
        }

        $folderName = $laravelFiles->setName($request->input('foldername'),$currentFolder,false);

        $laravelFiles->makeDirectory($currentFolder . '/' . $folderName);

        return response()->json([$folderName],200);
    }


    public function newFile(LaravelFiles $laravelFiles, Request $request){
        if(!$request->ajax()){ abort(404); }

        $currentFolder = $laravelFiles->resolveSafePath($request->input('folder'));
        if($currentFolder === null || !is_dir($currentFolder)){
            return response()->json(['error'=>trans('laravelfiles::plf.errorInvalidPath')],422);
        }

        $rejected = [];
        for($i=0;$request->hasFile('file-'.$i);$i++){
            $file = $request->file('file-'.$i);
            if(!$file->isValid()){
                continue;
            }

            if(!$laravelFiles->isUploadAllowed($file->getClientOriginalName())){
                $rejected[] = $file->getClientOriginalName();
                continue;
            }

            $fileName = $laravelFiles->setName($file->getClientOriginalName(),$currentFolder);
            $file->move($currentFolder,$fileName);
            $laravelFiles->makeThumbnails($currentFolder,$fileName);
        }

        return response()->json(['success'=>'ok','rejected'=>$rejected],200);
    }


    public function removeFile(LaravelFiles $laravelFiles, Request $request){
        if(!$request->ajax()){ abort(404); }

        $filePath = $laravelFiles->resolveSafePath($request->input('path'));
        if($filePath === null){
            return response()->json(['errors'=>['path'=>trans('laravelfiles::plf.errorInvalidPath')]],422);
        }
        $pathInfo = pathinfo($filePath);

        if(file_exists($filePath)){
            unlink($filePath);
        }

        $thumbsDir = $pathInfo['dirname'] . '/__thumbnails__';
        if(is_dir($thumbsDir)){
            $laravelFiles->removeThumbnails($thumbsDir,$pathInfo['filename']);
        }

        return response()->json(['success'=>'ok'],200);
    }

    public function removeDir(LaravelFiles $laravelFiles, Request $request){
        if(!$request->ajax()){ abort(404); }

        $dirPath = $laravelFiles->resolveSafePath($request->input('path'));
        if($dirPath === null){
            return response()->json(['errors'=>['path'=>trans('laravelfiles::plf.errorInvalidPath')]],422);
        }
        if(is_dir($dirPath) &&  $laravelFiles->deleteDirectory($dirPath)){
            return response()->json(['success'=>'ok'],200);
        }
        return response()->json(['errors'=>['err1'=>trans('laravelfiles::plf.errorSomethingWrong')]],200);
    }

    public function search(LaravelFiles $laravelFiles, Request $request){
        if(!$request->ajax()){ abort(404); }

        $currentFolder = $laravelFiles->resolveSafePath($request->input('currentFolder'));
        $s = $request->input('s');

        if(!$s || $currentFolder === null || !is_dir($currentFolder)){
            return response()->json(['errors'=>['err'=>'err']],200);
        }

        $files = $laravelFiles->searchFiles($currentFolder,$s);
        $data['files'] = $laravelFiles->formatedFiles($files,$currentFolder);
        $data['files'] = $laravelFiles->sortFiles($data['files']);

        $returnHTML = view('laravelfiles::partials.items',$data)->render();

        return response()->json(['success'=>'ok','html'=>$returnHTML],200);
    }


    public function rename(LaravelFiles $laravelFiles,Request $request)
    {
        if(!$request->ajax()){ abort(404); }

        $path = $request->input('path');
        $newName = $request->input('newName');

        if($laravelFiles->resolveSafePath($path) === null){
            return response()->json(['errors'=>['path'=>trans('laravelfiles::plf.errorInvalidPath')]],422,[],JSON_UNESCAPED_UNICODE);
        }

        $result = $laravelFiles->renameItem($path,$newName);

        if(!empty($result['errors'])){
            return response()->json($result,422,[],JSON_UNESCAPED_UNICODE);
        }
        return response()->json($result,200,[],JSON_UNESCAPED_UNICODE);
    }



    public function groupRemove(Request $request, LaravelFiles $laravelFiles)
    {
        if(!$request->ajax()){ abort(404); }

        $items = $request->input('items');
        if($laravelFiles->resolveSafePaths($items) === null){
            return response()->json(['errors'=>['items'=>trans('laravelfiles::plf.errorInvalidPath')]],422);
        }

        $result = $laravelFiles->groupRemove($items);

        return response()->json($result,200);
    }

    public function groupCopy(Request $request, LaravelFiles $laravelFiles)
    {
        if(!$request->ajax()){ abort(404); }

        $items = $request->input('items');
        $path = $request->input('path');
        if($laravelFiles->resolveSafePaths($items) === null || $laravelFiles->resolveSafePath($path) === null){
            return response()->json(['errors'=>['items'=>trans('laravelfiles::plf.errorInvalidPath')]],422);
        }

        $result = $laravelFiles->groupCopy($items,$path);

        return response()->json($result,200);
    }

    public function groupMove(Request $request, LaravelFiles $laravelFiles)
    {
        if(!$request->ajax()){ abort(404); }

        $items = $request->input('items');
        $path = $request->input('path');
        if($laravelFiles->resolveSafePaths($items) === null || $laravelFiles->resolveSafePath($path) === null){
            return response()->json(['errors'=>['items'=>trans('laravelfiles::plf.errorInvalidPath')]],422);
        }

        $result = $laravelFiles->groupCopy($items,$path,true);

        return response()->json($result,200);
    }
}
