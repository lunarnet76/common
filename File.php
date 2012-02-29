<?php
namespace Common {
    class File {
        public static function getExtension($file) {
            pre($file);
            return substr($file,strrpos($file,'.')+1);
        }
    }
}
