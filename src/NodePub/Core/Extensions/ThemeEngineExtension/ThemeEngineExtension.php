<?php

namespace NodePub\Core\Extensions\ThemeEngineExtension;

use NodePub\Core\Extension\DomManipulator;
use NodePub\Core\Extension\Extension;
use NodePub\Core\Model\ToolbarItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ThemeEngineExtension extends Extension
{
    public function isCore()
    {
        return true;
    }
    
    public function isInstalled()
    {
        return true;
    }
    
    public function isEnabled()
    {
        return true;
    }
    
    /**
     * If theme_preview is in the session, adds the theme switcher form.
     */
    public function getAdminContent()
    {
        $content = '';

        if ($this->app['session']->get('theme_preview')) {
            $content .= $this->getThemeSwitcher();
        }
        
        return $content;
    }
    
    /**
     * Adds TypeKit script tags to end of head.
     */
    public function getSnippets()
    {
        $snippets = array();
        
        if (isset($this->app['np.typekit_id'])) {
            $snippets[DomManipulator::END_HEAD] = function($app) {
                return $app['twig']->render('@ThemeEngineExtension/_typeKit.twig', array('typekit_id' => $app['np.typekit_id']));
            };
        }
        
        return $snippets;
    }

    /**
     * Fetches the rendered theme switcher form through a subrequest.
     *
     * @return string
     */
    protected function getThemeSwitcher()
    {
        $subRequest = Request::create($this->app['url_generator']->generate('theme_switcher', array('referer' => urlencode($this->app['request']->getPathInfo()))));
        $subResponse = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
        $themeSwitcher = "\n".str_replace("\n", '', $subResponse->getContent())."\n";

        return $themeSwitcher;
    }
}