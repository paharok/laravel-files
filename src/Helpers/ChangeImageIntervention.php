<?php
namespace Paharok\Laravelfiles\Helpers;

use Paharok\Laravelfiles\Helpers\Contracts\ChangeImage;

use Paharok\Laravelfiles\Helpers\Intervention\AdapterFactory;
use Paharok\Laravelfiles\Helpers\Intervention\ImageAdapter;

class ChangeImageIntervention implements ChangeImage{
    public static $mimes = ['image/jpg','image/jpeg','image/png','image/webp','image/bmp','image/gif'];

    /** Works with Intervention Image v3 and v4 (see Helpers/Intervention). */
    private static function adapter(): ImageAdapter{
        static $adapter;
        if(!$adapter){
            $adapter = AdapterFactory::make();
        }
        return $adapter;
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



            $adapter = self::adapter();
            $w = (int) $width;
            $h = (int) $height;
            $quality = (int) $quality;

            // read() auto-corrects EXIF orientation during decoding (v3 and v4).
            $image = $adapter->read(public_path() . $filePath);

            if(!file_exists(public_path() . $cache_dir)){
                mkdir(public_path() . $cache_dir,0775,true);
            }

            if($crop=='fit'){
                // crop-to-fill, preserving aspect ratio
                $image = $adapter->cover($image, $w, $h, $position);
            }elseif($crop=='resize'){
                // preserves aspect ratio and never upsizes
                $image = $adapter->scaleDown($image, $w, $h);
            }elseif($crop=='resizebg'){
                $canvas = $adapter->canvas($w, $h, '#ffffff');
                $image = $adapter->scaleDown($image, $w, $h);
                $image = $adapter->place($canvas, $image, 'center');
            }

            $adapter->save($image, public_path() . $cache_dir . $newFileName, $path_parts['extension'], $quality);
            $adapter->save($image, public_path() . $cache_dir . $newFileNameWebp, 'webp', $quality);
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
