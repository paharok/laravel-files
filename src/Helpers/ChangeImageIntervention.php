<?php
namespace Paharok\Laravelfiles\Helpers;

use Paharok\Laravelfiles\Helpers\Contracts\ChangeImage;

use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Encoders\GifEncoder;
use Intervention\Image\Encoders\BmpEncoder;

class ChangeImageIntervention implements ChangeImage{
    public static $mimes = ['image/jpg','image/jpeg','image/png','image/webp','image/bmp','image/gif'];

    private static function manager(){
        static $manager;
        if(!$manager){
            $manager = ImageManager::gd();
        }
        return $manager;
    }

    private static function encoderForExtension($extension, $quality = 100){
        switch(strtolower($extension)){
            case 'png':
                return new PngEncoder();
            case 'webp':
                return new WebpEncoder($quality);
            case 'gif':
                return new GifEncoder();
            case 'bmp':
                return new BmpEncoder();
            default:
                return new JpegEncoder($quality);
        }
    }

    public static function changeImage($filePath,$width=0,$height=0,$crop='fit',$position='center',$quality=100){

        if(mb_substr($filePath,0,1) != '/'){
            $filePath = '/' . $filePath;
        }


        $path_parts = pathinfo($filePath);

        if($filePath==='/' || !$filePath || !file_exists(public_path() . $filePath) || empty($path_parts['extension'])){
            $filePath = '/vendor/laravel-files/files/no-img.png';
            $path_parts = pathinfo($filePath);
        }

        $checkSVG = SELF::checkSVG($filePath);
        if($checkSVG){
            return $checkSVG;
        }

        $mainMime = mime_content_type(public_path() . $filePath);
        if(!in_array($mainMime,SELF::$mimes)){
            $filePath = '/vendor/laravel-files/files/no-img.png';
        }


        $image_size = getimagesize(public_path() . $filePath);

        $checkGif = SELF::checkGif($filePath,$image_size);
        if($checkGif){
            return $checkGif;
        }

        if($width==0 && $height==0){
            $width = $image_size[0];
            $height = $image_size[1];
        }

        if($width==0){
            $width = round(($image_size[0] / $image_size[1]) * $height);
        }

        if($height==0){
            $height = round($width / ($image_size[0] / $image_size[1]));
        }





        $cache_dir = $path_parts['dirname'] . '/__thumbnails__/';
        $newFileName = $path_parts['filename'] . $width . '_'.$height . $crop  . '.'. $path_parts['extension'];
        $newFileNameWebp = $path_parts['filename'] . $width . '_'.$height . $crop  . '.webp';

        if(!file_exists(public_path() . '/' . $cache_dir . $newFileName)){



            // v3's read() auto-corrects EXIF orientation during decoding.
            $image = self::manager()->read(public_path() . $filePath);

            if(!file_exists(public_path() . $cache_dir)){
                mkdir(public_path() . $cache_dir,0775,true);
            }

            if($crop=='fit'){
                // v3 renamed fit() to cover() (crop-to-fill, preserving aspect ratio)
                $image->cover($width, $height, $position);
            }elseif($crop=='resize'){
                // scaleDown() preserves aspect ratio and never upsizes, matching the
                // old resize()+aspectRatio()+upsize() combo.
                $image->scaleDown($width, $height);
            }elseif($crop=='resizebg'){
                $canvas = self::manager()->create($width, $height)->fill('ffffff');

                $image->scaleDown($width, $height);

                $canvas->place($image, 'center');
                $image = $canvas;
            }

            $image->encode(self::encoderForExtension($path_parts['extension'],$quality))
                ->save(public_path() . $cache_dir . $newFileName);
            $image->encode(self::encoderForExtension('webp',$quality))
                ->save(public_path() . $cache_dir . $newFileNameWebp);
        }

        $cache_dir = str_replace(' ','%20',$cache_dir);

        return [
            'main_uri'=> $cache_dir . str_replace(' ','%20',$newFileName),
            'original_uri'=> str_replace(' ','%20',$filePath),
            'originalSizes'=>[$image_size[0],$image_size[1]],
            'newSizes'=>[$width,$height],
            'sources'=>[
                'image/webp'=> $cache_dir . str_replace(' ','%20',$newFileNameWebp),
                $mainMime=> $cache_dir . str_replace(' ','%20',$newFileName)

            ]

        ];

    }


    private static function checkSVG($filePath){
        $path_parts = pathinfo($filePath);
        if($path_parts['extension'] == 'svg'){
            return [
                'main_uri'=> str_replace(' ','%20',$filePath),
                'original_uri'=> str_replace(' ','%20',$filePath),
                'sources'=>[
                    'image/svg+xml'=> str_replace(' ','%20',$filePath)
                ]

            ];
        }
    }


    private static function checkGif($filePath,$image_size = [0=>0,1=>0]){
        $path_parts = pathinfo($filePath);
        if($path_parts['extension'] == 'gif'){
            return [
                'main_uri'=> str_replace(' ','%20',$filePath),
                'original_uri'=> str_replace(' ','%20',$filePath),
                'originalSizes'=>[$image_size[0],$image_size[1]],
                'newSizes'=>[$image_size[0],$image_size[1]],
                'sources'=>[
                    'image/gif'=> str_replace(' ','%20',$filePath)
                ]

            ];
        }
    }


    public static function isSupportWebp(){
        if(!empty($_SERVER['HTTP_ACCEPT']) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false){
            return true;
        }
    }

}

?>
