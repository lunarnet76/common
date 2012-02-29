<?php
namespace Common\Docx {
    class Document {
        protected $_file;
        protected $_folder;
        protected $_extracted = false;
        protected $_content = '';
        protected static $_callBackArgs = array();

        public function __construct($file, $folder) {
            $this->_file = $file;
            $this->_folder = $folder;
            if (!class_exists('XSLTProcessor'))
                throw new Exception('Dependency library missing : XSL');
            if (!class_exists('ZipArchive'))
                throw new Exception('Dependency library missing : ZipArchive');
        }

        public function extractXSLFile($toPath=false) {
            if ($this->_extracted)
                return $this->_extracted;
            $toPath = $toPath ? $toPath : $this->_folder . 'tmp/';

            $zipArchive = new \ZipArchive();

            $error = $zipArchive->open($this->_folder . $this->_file);
            if ($error !== true) {
                switch ($error) {
                    case ZIPARCHIVE::ER_EXISTS: $error = 'ER_EXISTS';
                        break;
                    case ZIPARCHIVE::ER_INCONS: $error = 'ER_INCONS';
                        break;
                    case ZIPARCHIVE::ER_INVAL:$error = 'ER_INVAL';
                        break;
                    case ZIPARCHIVE::ER_MEMORY:$error = 'ER_MEMORY';
                        break;
                    case ZIPARCHIVE::ER_NOENT:$error = 'ER_NOENT';
                        break;
                    case ZIPARCHIVE::ER_NOZIP:$error = 'ER_NOZIP';
                        break;
                    case ZIPARCHIVE::ER_OPEN:$error = 'ER_OPEN';
                        break;
                    case ZIPARCHIVE::ER_READ:$error = 'ER_READ';
                        break;
                    case ZIPARCHIVE::ER_SEEK:$error = 'ER_SEEK';
                        break;
                }
                throw new Exception('Zip: error opening "' . $this->_folder . $this->_file . '" :' . $error);
            }
            $zipArchive->extractTo($toPath, 'word/document.xml');
            $zipArchive->close();

            $this->_extracted = $toPath . $this->_file . '.xslt';

            if (file_exists($this->_extracted))
                unlink($this->_extracted);
            rename($toPath . 'word/document.xml', $this->_extracted);

            // transform into xslt
            ob_start();
            readfile($this->_extracted);
            $this->_content = $content = ob_get_clean();
            $fh = fopen($this->_extracted, 'w');
            $content = str_replace('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>', '<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:template match="/">', $content);
            fwrite($fh, $content . '</xsl:template></xsl:stylesheet>');
            fclose($fh);
            return $this->_extracted;
        }

        public function replace(array $array) {
            if (!$this->_extracted)
                $this->extractXSLFile();

            $xsltFile = $this->_extracted;
            $sourceTemplate = $this->_folder . $this->_file;
            $xmlDocument = new \DOMDocument();

            $xsltDocument = new \DOMDocument();
            $xsltDocument->load($xsltFile);


            $xsltProcessor = new \XSLTProcessor();
            $xsltProcessor->importStylesheet($xsltDocument);

            // After the transformation, $newContentNew contains
            //the XML data in the Open XML Wordprocessing format.
            $this->_content = $xsltProcessor->transformToXML($xmlDocument);
            self::$_callBackArgs = $array;
            // echo htmlentities($this->_content);

            /*
              <w:hyperlink r:id="rId11" w:history="1">
              <w:r w:rsidR="001636BD" w:rsidRPr="00593805">
              <w:rPr>
              <w:rStyle w:val="Hyperlink"/>
              <w:color w:val="000000" w:themeColor="text1"/>
              </w:rPr>
              <w:t>CITY
              </w:t>
              </w:r>
              </w:hyperlink> */

            $this->_content = preg_replace_callback('|<w:hyperlink[^\>]*><w:r[^\>]*><w:rPr[^\>]*>(<w[^\>]*>)*<\/w:rPr><w:t>([^<]*)|', array(__CLASS__, 'replaceCallBack'), $this->_content);
            //echo  $this->_content ;
        }

        public static function replaceCallBack($match) {
            $index = count($match) - 1;
            if (isset(self::$_callBackArgs[$match[$index]]))
                return str_replace(PHP_EOL, '</w:t><w:br/><w:t>', str_replace($match[$index], self::$_callBackArgs[$match[$index]], $match[0]));
            else
                return $match[0];
        }

        public function getContent() {
            return $this->_content;
        }

        public function save() {
            if (!$this->_extracted)
                $this->extractXSLFile();
            if (copy($this->_folder . $this->_file, $this->_folder . $this->_file . '.tmp')) {
                //Open XML files are packaged following the Open Packaging
                //Conventions and can be treated as zip files when
                //accessing their content.
                $zipArchive = new \ZipArchive();
                $zipArchive->open($this->_folder . $this->_file . '.tmp');

                //Replace the content with the new content created above.
                //In the Open XML Wordprocessing format content is stored
                //in the document.xml file located in the word directory.
                $zipArchive->addFromString("word/document.xml", $this->_content);
                $zipArchive->close();
            }else
                throw new Exception('cannot copy ' . $this->_folder . $this->_file);
            unlink($this->_folder . $this->_file);
            rename($this->_folder . $this->_file . '.tmp', $this->_folder . $this->_file);
        }
    }
}
