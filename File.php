<?php
namespace Common {
	class File {

		public static function getExtension($file)
		{
			return substr($file, strrpos($file, '.') + 1);
		}

		public static function getMimeType($path)
		{
			$finfo = \finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$mime = \finfo_file($finfo, $path);
			\finfo_close($finfo);
			return $mime;
		}
	}
}
