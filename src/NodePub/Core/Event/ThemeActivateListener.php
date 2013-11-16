<?php

namespace NodePub\Core\Event;

/**
 * Listens for theme activation and configures the relevant templates
 */
class ThemeActivateListener
{
    protected $app;
    
    function __construct($app)
    {
        $this->app = $app;
    }
    
    public function onThemeActivate($event)
    {

        // We may want some kind of registry or ThemeTemplateResolver object
        // that uses theme's configuration to map its templates to common page types,
        // otherwise all themes have to use exact template names,
        // and there's no way to share a template for different page types, or fallback on a parent theme

        $theme = $event->getTheme();
        $name = $theme->getNamespace();
        
        $this->app['np.blog.theme.options'] = $this->app->share(function($app) use ($theme) {
            $templates = $theme->getTemplates();
            return array('templates' => $templates['blog']);
        });

        $this->app['np.theme.templates.custom_css'] = '@'.$name.'/_styles.css.twig';

        // set active theme's parent
        if ($parentName = $theme->getParentNamespace()) {
            if ($parent = $this->app['np.theme.manager']->getTheme($parentName)) {
                $theme->setParent($parent);
            }
        }
        
        if ($themeOptions = $this->app['np.theme.configuration_provider']->get($name)) {
            $theme->customize($themeOptions);
        }
    }
}
