<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\Extension;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class ThemeEngineExtension extends Extension
{
    protected $name = 'Theme Extension';

    public function registerAdminJsModules() {
        return array('np/themeEngine', 'np/colorPicker');
    }

    public function registerAdminContent()
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