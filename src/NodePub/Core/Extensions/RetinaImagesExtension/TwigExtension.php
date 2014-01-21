<?php

namespace NodePub\Core\Extensions\CoreExtension;

class TwigExtension extends \Twig_Extension
{
    const RETINA_FILE_SUFFIX = '@2x';
    
    protected $imageHelper;
    
    function __construct($imageHelper)
    {
        $this->imageHelper = $imageHelper;
    }

    public function getName()
    {
        return 'NodePubRetinaImages';
    }
    
    public function getGlobals()
    {
        return array(
            'is_retina' => $this->imageHelper->isRetina()
        );
    }
    
    public function getFilters()
    {
        $imageHelper = $this->imageHelper;
        
        return array(
            new \Twig_SimpleFilter('2x', function($input) use ($imageHelper) {
                // todo: check for file extension
                if ($imageHelper->isRetina()) {
                    $input .= TwigExtension::RETINA_FILE_SUFFIX;
                }
                
                return $input;
            }),
        );
    }
}
