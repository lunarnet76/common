<?php
namespace CRUDsader\MVC {
    class Image {
        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $folder = false;

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $originalFolder = false;

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $file = false;

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $originalFile = false;

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $title = false;

        /**
         * 	@access	protected
         * 	@var 	float
         */
        protected $width = false;

        /**
         * 	@access	protected
         * 	@var 	float
         */
        protected $height = false;

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $id = false;

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $css = '';

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $style = '';

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $align = '';

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $path = 'module';

        /**
         * 	@access	protected
         * 	@var 	string
         */
        protected $module = false;

        /**
         * 	@access	protected
         * 	@var 	bool
         */
        protected $zoom = false;

        /**
         * 	@access	protected
         * 	@var 	bool
         */
        protected $render = true;

        /**
         * 	@access	protected
         * 	@var 	bool
         */
        protected $rendered = false;

        /**
         * 	@access	protected
         * 	@var 	sttring
         */
        protected $cacheFolder = 'file/cache/image/';

        /**
         * 	@access	protected
         * 	@var 	sttring
         */
        protected $cache = false;

        /**
         * 	@access	protected
         * 	@var 	sttring
         */
        protected $baseRef = false;

        /**
         * 	@access	protected
         * 	@var 	sttring
         */
        protected $_cacheFile = false;

        /**
         * 	@access	protected
         * 	@var 	sttring
         */
        public $url = '';
        protected $_frontController;

        /**
         * 	@access	protected
         * 	@staticvar 	string
         */
        protected static $jsIncluded = false;

        /**
         * Constructor
         * 	@access	public
         * 	@param string $file
         * 	@param string $title
         * 	@param float $width
         * 	@param float $height
         * 	@param string $folder
         * 	@return void
         */
        public function __construct($params=array('file' => false, 'title' => false, 'width' => false, 'height' => false, 'folder' => false, 'path' => 'module', 'style' => false, 'cache' => false)) {
            if (is_array($params))
                foreach ($params as $key => $value)
                    $this->$key = $value;
            switch ($this->path) {
                case 'module':
                    $this->folder = 'file/app/' . $this->module . '/image/' . $this->folder;
                    break;
                default:
                    $this->folder = 'file/application/image/' . $this->folder;
            }
            $this->originalFolder = $this->folder;
            $this->originalFile = $this->file;
        }

        public function __get($var) {
            return $this->$var;
        }

        public function __toString() {
            if (!$this->rendered)
                return $this->render();
            return $this->rendered;
        }

        public function getUrl($cache=true) {
            $file = ($this->cache && $cache ? $this->cacheFolder : $this->folder) . ($this->cache && $cache ? $this->cacheFile : $this->file);
            return $this->baseRef . $file;
        }

        /**
         * Write HTMl img tag
         * 	@access	public
         * 	@return string
         */
        public function render() {
            if ($this->rendered)
                return $this->rendered;
            /*if ($this->width || $this->height) {
                if ($this->cache) {
                    $file = str_replace('/', '_', $this->folder) . $this->width . 'x' . $this->height . '_' . $this->file;
                    if (!file_exists($this->cacheFolder . $file)) {
                        $resize = self::resizeFile($this->file, $this->width, $this->height, false, $this->folder, $this->cacheFolder, $file);
                        if (is_array($resize)) {
                            $this->cacheFile = $resize[0];
                            $this->width = $resize[2];
                            $this->height = $resize[3];
                        } else {
                            if (DEV)
                                echo $resize;
                            $this->noImage();
                        }
                    }else {
                        $resize = self::resize($this->file, $this->width, $this->height, $this->folder);
                        $this->cacheFile = $file;
                        $this->width = $resize[2];
                        $this->height = $resize[3];
                    }
                } else {
                    $resize = self::resize($this->file, $this->width, $this->height, $this->folder);
                    $this->width = $resize[2];
                    $this->height = $resize[3];
                }
            }*/
            $this->url = $this->getUrl();
            $this->rendered = '<img src="' . $this->url . '" alt="' . $this->title . '" title="' . $this->title . '"  ' . ($this->width ? 'width="' . $this->width . '"' : '') . ($this->height ? ' height="' . $this->height . '"' : '') . ' class="' . $this->css . '" id="' . ($this->id ? $this->id : 'none') . '" ' . ($this->style ? 'style="' . $this->style . '"' : '') . ' ' . ($this->align ? 'align="' . $this->align . '"' : '') . '/>';

            if ($this->zoom)
                $this->rendered = '<a href="' . $this->getUrl(false) . '" class="nivoZoom center">' . $this->rendered . '<div class="nivoCaption">' . $this->title . '</div></a>';
            if ($this->render)
                return $this->rendered;
            else
                return '';
        }

        /**
         * Return the Resized width and height corresponding to the maximum resizing possible with gethe params without deforming the ima
         * 	@static
         * 	@access	public
         * 	@param string $file
         * 	@param float $maxWidth
         * 	@param float $maxHeight
         * 	@param string $file
         * 	@return void
         */
        public static function resize($file, $maxWidth=false, $maxHeight=false, $folder=false) {
            //I get height and width from the source image
            $informationsImageSize = getimagesize($folder . $file);
            $newWidth = $sourceWidth = $informationsImageSize[0];
            $newHeight = $sourceHeight = $informationsImageSize[1];

            //I calculate appropriated width and height for the new image
            if ($maxHeight && $newHeight > $maxHeight) {
                $factor = $newHeight / $maxHeight;
                $newHeight/=$factor;
                $newWidth/=$factor;
            }
            if ($maxWidth && $newWidth > $maxWidth) {
                $factor = $newWidth / $maxWidth;
                $newHeight/=$factor;
                $newWidth/=$factor;
            }
            return array($sourceWidth, $sourceHeight, $newWidth, $newHeight);
        }

        /**
         * Resize an image and send it to an other file, or the same file
         * 	@param string $folder folder where the image source is saved in
         * 	@param string $file	name of the image that will be resized
         * 	@param float $maxWidth  maximum with that the image will have when it will be resized
         * 	@param float $maxHeight maximum height that the image will have when it will be resized
         * 	@param boolean $delete   true if we want to delete the old file (unresized image), false otherwise
         * 	@param string $destinationFolder  path toward the folder where the resized image must be saved back
         * 	@param string|bool $newName the name of the final image
         * 	@return array|string wether an array with the filename and the folder or an error message
         */
        public static function resizeFile($file, $maxWidth=false, $maxHeight=false, $deleteOriginal=false, $folder=false, $destinationFolder=false, $newName=false) {

            if (file_exists($folder . $file)) {
                if (!$destinationFolder)
                    $destinationFolder = $folder;
                // get the information about the file (size, format...)
                $infofile = pathinfo($folder . $file);

                //  get the image extension (jpg, png, ...)
                $extension = strtolower($infofile['extension']);

                // check whether it is an image format:
                if ($extension == "jpg" || $extension == "png" || $extension == "jpeg" || $extension == "gif") {
                    // create a GD Image from the source image according to the MIME type
                    switch ($extension) {
                        case 'png':
                            $imageGDSource = imagecreatefrompng($folder . $file);
                            break;
                        case 'gif':
                            $imageGDSource = imagecreatefromgif($folder . $file);
                            break;
                        case 'jpeg':
                        case 'jpg':
                            $imageGDSource = imagecreatefromjpeg($folder . $file);
                            break;
                    }

                    // image resizing
                    $resize = self::resize($file, $maxWidth, $maxHeight, $folder);


                    // create a GD image with appropriated resized width and height
                    $imageGDDestination = imagecreatetruecolor($resize[2], $resize[3]);

                    // make the image white and transparent
                    $white = imagecolorallocate($imageGDDestination, 236, 237, 237);
                    imagefill($imageGDDestination, 0, 0, $white);
                    imagecolortransparent($imageGDDestination, $white);

                    // resize the source image by copying it into the GD image
                    imagecopyresampled($imageGDDestination, $imageGDSource, 0, 0, 0, 0, $resize[2], $resize[3], $resize[0], $resize[1]);

                    // if not specified,create a new name for the image with the new file extension
                    $imageNewName = $newName ? $newName : time() . microtime() . '.' . $extension;

                    //if we decided to delete the file, we do it
                    if ($deleteOriginal) {
                        if (file_exists($folder . $file))
                            unlink($folder . $file);
                    }

                    if (!is_writable($destinationFolder))
                        return ($destinationFolder . ' is not writable');

                    // save the resized image in the folder $destination
                    switch ($extension) {
                        case 'png':
                            imagepng($imageGDDestination, $destinationFolder . $imageNewName);
                            break;
                        case 'gif':
                            imagegif($imageGDDestination, $destinationFolder . $imageNewName);
                            break;
                        case 'jpeg':
                        case 'jpg':
                            imagejpeg($imageGDDestination, $destinationFolder . $imageNewName);
                            break;
                    }

                    return array($imageNewName, $destinationFolder, $resize[2], $resize[3]);
                } else {
                    //Wrong type MIME
                    return ($file . $folder . ' wrong mime type');
                }
            } else {
                //Image not found
                return ($file . $folder . ' was not found');
            }
        }
    }
}