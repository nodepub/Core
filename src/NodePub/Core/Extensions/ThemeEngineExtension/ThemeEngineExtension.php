<?php

namespace NodePub\Core\Extensions\ThemeEngineExtension;

use NodePub\Core\Extension\Extension;
use NodePub\Core\Model\ToolbarItem;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class ThemeEngineExtension extends Extension
{
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