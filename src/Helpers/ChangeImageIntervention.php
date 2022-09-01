<?php
namespace Paharok\Laravelfiles\Helpers;

use Paharok\Laravelfiles\Helpers\Contracts\ChangeImage;

use Intervention\Image\Facades\Image AS Image;

class ChangeImageIntervention implements ChangeImage{


	public static function changeImage($filePath,$width=0,$height=0,$crop='fit'){

		if(!$filePath || !file_exists(public_path() . $filePath)){

			$filePath = '/no-img.png';
		}
		$image_size = getimagesize(public_path() . $filePath);


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


		$path_parts = pathinfo($filePath);

		$mainMime = mime_content_type(public_path() . $filePath);
		$cache_dir = $path_parts['dirname'] . '/__thumbnails__/';
		$newFileName = $path_parts['filename'] . $width . '_'.$height . $crop . '.'. $path_parts['extension'];
		$newFileNameWebp = $path_parts['filename'] . $width . '_'.$height . $crop . '.webp';

		if(!file_exists(public_path() . '/' . $cache_dir . $newFileName)){



			$image = Image::make(public_path() . $filePath);




			if(!file_exists(public_path() . '/' . $cache_dir)){
				mkdir(public_path() . '/' . $cache_dir,0775,true);
			}

			if($crop=='fit'){
				$image->fit($width, $height, function ($constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
				});
			}elseif($crop=='resize'){
				$image->resize($width, $height, function ($constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
				});
			}elseif($crop=='resizebg'){
				$canvas = Image::canvas($width, $height, '#ffffff');

				$image->resize($width, $height, function ($constraint) {
					$constraint->aspectRatio();
					$constraint->upsize();
				});

				$image = $canvas->insert($image,'center');

			}



			$image->save(public_path() . '/' . $cache_dir . $newFileName);
			$image->save(public_path() . '/' . $cache_dir . $newFileNameWebp);
		}

	$cache_dir = str_replace(' ','%20',$cache_dir);

		return [
				'main_uri'=> '/'. $cache_dir . str_replace(' ','%20',$newFileName),
				'original_uri'=> str_replace(' ','%20',$filePath),
				'sources'=>[
								'image/webp'=>'/'. $cache_dir . str_replace(' ','%20',$newFileNameWebp),
								$mainMime=>'/'. $cache_dir . str_replace(' ','%20',$newFileName)

							]

				];

	}

}

  ?>