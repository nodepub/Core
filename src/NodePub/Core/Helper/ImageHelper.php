<?php

namespace NodePub\Core\Helper;

use NodePub\Core\Helper\HtmlHelper;

class ImageHelper extends HtmlHelper
{
    const EXT_JPG = 'jpg';
    const EXT_PNG = 'png';
    const EXT_GIF = 'gif';
    const ATTR_WIDTH = 'width';
    const ATTR_HEIGHT = 'height';
    
    /**
     * The filesystem directory where we check for image files
     */
    public $baseDir;

    /**
     * The site-relative image path
     * e.g. '/assets/images/'
     */
    public $publicSiteRootPath;
    
    /**
     * The file extensions that will be checked if given path is not a file
     */
    public $imageExtensions = array(self::EXT_JPG, self::EXT_PNG, self::EXT_GIF);
    
    /**
     * Checks that the given file exists in the configured directory.
     * If file isn't found it searches for a matching file with other image file extensions.
     * When an existing file is found it gets the width and height of the image and creates an
     * image tag populated with those properties
     *
     * @return string
     */
    public function placeImage($fileName, $attrs = array(), $absolutePath = false)
    {
        $imgSrc = $this->publicSiteRootPath . $fileName;
        
        $filesystemPath = is_null($this->baseDir)
            ? $fileName
            : $this->addTrailingSlash($this->baseDir) . $fileName;

        if (file_exists($filesystemPath)) {
            list($attrs[self::ATTR_WIDTH], $attrs[self::ATTR_HEIGHT]) = getimagesize($filesystemPath);
        } else {
            # look for missing extensions
            foreach($this->imageExtensions as $ext) {
                $ext = '.' . $ext;
                $tryFile = $filesystemPath . $ext;
                if(file_exists($tryFile)) {
                    list($attrs[self::ATTR_WIDTH], $attrs[self::ATTR_HEIGHT]) = getimagesize($tryFile);
                    $imgSrc = $this->publicSiteRootPath . $fileName . $ext;
                    break;
                }
            }
        }
        
        return $this->imageTag($imgSrc, $attrs);
    }
    
    /**
     * Returns a rendered image tag
     * @return string
     */
    public function imageTag($src, $attrs = array())
    {
        $attrs['src'] = $src;
        
        return $this->renderTag('img', $attrs);
    }
    
    /**
     * Adds a trailing slash to the path if it's not already there
     */
    protected function addTrailingSlash($path)
    {
        if (substr($path, -1) !== DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }
        
        return $path;
    }
}
