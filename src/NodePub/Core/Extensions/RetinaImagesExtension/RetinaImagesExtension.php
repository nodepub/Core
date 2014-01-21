<?php

namespace NodePub\Core\Extensions\RetinaImagesExtension;

use NodePub\Core\Extension\Extension;
use NodePub\Core\Helper\ImageHelper;

class RetinaImagesExtension extends Extension
{
    /**
     * Adds script tag to end of head for setting device pixel density cookie
     */
    public function getSnippets()
    {
        $snippets = array();
        
        if (isset($this->app['np.image_helper']) && !$this->app['np.image_helper']->isRetinaChecked()) {
            $snippets[DomManipulator::END_HEAD] = function($app) {
                return $app['twig']->render('@RetinaImagesExtension/_retinaCheck.twig', array(
                    'redirect_to' => $_SERVER['PHP_SELF'],
                    'cookie_key' => ImageHelper::COOKIE_PIXEL_RATIO
                ));
            };
        }
        
        return $snippets;
    }
}