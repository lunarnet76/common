<?php
namespace Common {
    class File {
        public static function getExtension($file) {
            return substr($file,strrpos($file,'.')+1);
        }
    }
}
