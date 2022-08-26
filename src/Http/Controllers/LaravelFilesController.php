<?php

namespace Paharok\Laravelfiles\Http\Controllers;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class LaravelFilesController extends Controller
{
    //
    public function index(){
        $value = config('laravelfiles.variable');


        dump($value);

        return view("laravelfiles::index");
    }
}
