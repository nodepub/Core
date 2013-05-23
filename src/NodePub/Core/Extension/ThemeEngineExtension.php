<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\Extension;
use NodePub\Core\Model\ToolbarItem;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class ThemeEngineExtension extends Extension
{
    public function getName() {
        return 'NodePub Theme';
    }

    public function getResourceManifest() {
        return array(
            'css/spectrum.css',
            'js/lib/spectrum.js',
            'js/np/themeEngine.js',
            'js/np/colorPicker.js'
        );
    }

    public function getToolbarItems() {
        return array(
            new ToolbarItem('Themes', 'get_themes'),
        );
    }

    public function getAdminContent()
    {
        $content = '';

        if ($theme = $this->app['session']->get('theme_preview')) {
            $content .= $this->getThemeSwitcher();
        }

        return $content;
    }

    protected function getThemeSwitcher()
    {
        $subRequest = Request::create($this->app['url_generator']->generate('theme_switcher', array('referer' => urlencode($this->app['request']->getPathInfo()))));
        $subResponse = $this->app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
        $themeSwitcher = "\n".str_replace("\n", '', $subResponse->getContent())."\n";

        return $themeSwitcher;
    }
}