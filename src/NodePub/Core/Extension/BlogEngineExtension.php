<?php

namespace NodePub\Core\Extension;

use NodePub\Core\Extension\Extension;
use NodePub\Core\Model\ToolbarItem;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

class BlogEngineExtension extends Extension
{
    public function getName() {
        return 'NodePub Blog';
    }

    public function getResourceDirectory() {
        return '';
    }

    public function getToolbarItems() {
        return array(
            new ToolbarItem('Blog', 'blog_admin', 'comment'),
        );
    }
}