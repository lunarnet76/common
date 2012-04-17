<?php
namespace Common {
	class File {

		public static function getExtension($file)
		{
			return strtolower(substr($file, strrpos($file, '.') + 1));
		}

		public static function getMimeType($path)
		{
			if(function_exists('finfo_open')){
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$mime = finfo_file($finfo, $path);
			finfo_close($finfo);
			}else if(function_exists('mime_content_type')){
				return mime_content_type($path);
			}else if($info = getimagesize($path)){
				return $info['mime'];
			}else
				throw new \Exception('your system does not support finfo nor mime_content_type');
			return $mime;
		}
	}
}
