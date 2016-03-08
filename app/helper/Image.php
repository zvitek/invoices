<?php
namespace App\Helper;
use Nette\Utils;

class Image
{
	public static function image__save($image, $temp_dir, $destination_dir, $id = NULL, $sizes = [], $save_Original = TRUE) {
		if($image->isOk()) {
			$file_temp = $temp_dir . $image->getSanitizedName();
			$file_extension = strtolower(pathinfo($image->getSanitizedName(), PATHINFO_EXTENSION));
			$file_name = sprintf('%s_%s_%s.%s', is_null($id) ? '' : $id, Utils\Strings::webalize(str_replace('.' . $file_extension, '', $image->getSanitizedName())), rand(100, 999), $file_extension);
			if($image->move($file_temp)) {
				if($save_Original) {
					$image = Utils\Image::fromFile($file_temp);
					$image->save(sprintf('%s%s', $destination_dir, $file_name), 100);
				}
				if(count($sizes)) {
					foreach($sizes as $size) {
						$image = Utils\Image::fromFile($file_temp);
						$image->resize($size, $size, Utils\Image::FILL);
						$image->save(sprintf('%s%s_%s', $destination_dir, $size, $file_name), 100);
					}
				}
				return $file_name;
			}
		}
		return FALSE;
	}
}