<?php
namespace Common\Docx {
    class Template {
        public static $templateDir ='file/documentTemplate/';
        public static $dir ='file/docx/';

        public static function docx($replacements, $templateFile, $outputFile) {
            
            // copy file
            if (file_exists(self::$dir . $outputFile . '.docx'))
                unlink(self::$dir . $outputFile . '.docx');
            copy(self::$templateDir.$templateFile, self::$dir . $outputFile . '.docx');

            // traduction des variables
            $replacement = array();
            $translate = \CRUDsader\Instancer::getInstance()->i18n;
            foreach ($replacements as $k => $v) {
                $replacement[strtoupper($translate->translate('docx.var.' . $k))] = ucfirst($v);
            }
            // extract XSLT
            $doc = new Document($outputFile . '.docx', self::$dir);
            $doc->replace($replacement);
            $doc->save();
            return self::$dir . $outputFile;
        }

        public static function output($file, $name) {
            if (!file_exists($file . '.docx'))
                throw new Exception('file "' . $file . '.docx" does not exist');
            header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); //application/octet-stream
            // header('Content-Disposition: attachment; filename="' . $name . '.docx"');
            readfile($file . '.docx');
        }
    }
}