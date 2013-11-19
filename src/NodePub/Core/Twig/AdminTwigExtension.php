<?php

namespace NodePub\Core\Twig;

class AdminTwigExtension extends \Twig_Extension
{
    protected $twigEnvironment;

    public function getName()
    {
        return 'NodePubAdmin';
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->twigEnvironment = $environment;
    }
    
    public function getFunctions()
    {
        return array(
            'panel_nav' => new \Twig_Function_Method($this, 'panelNav'),
        );
    }

    public function panelNav($navItems = array())
    {
        $breadcrumbs = array();
        $separator = '<i class="fa fa-chevron-right"></i>';
        
        foreach ($navItems as $navItem) {
            if (isset($navItem['uri'])) {
                $breadcrumbs[] = sprintf('<a href="%s">%s</a>', $navItem['uri'], $navItem['name']);
            } else {
                $breadcrumbs[] = $navItem['name'];
            }
        }

        return implode($separator, $breadcrumbs);
    }
}
