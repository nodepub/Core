<?php

namespace NodePub\Core\Routing;

use Silex\Application;
use Silex\ControllerProviderInterface;

class ExtensionRouting implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $extensionFinder = function($name) use($app) {
            if ($extension = $app['np.extensions']->getExtension($name)) {
                return $extension;
            } else {
                throw new \Exception("Extension not found", 404);
            }
        };

        $controllers->get('/', 'np.extensions.controller:getExtensionsAction')
            ->bind('admin_extensions');

        $controllers->get('/{extension}', 'np.extensions.controller:getExtensionAction')
            ->convert('extension', $extensionFinder)
            ->bind('admin_extension');

        $controllers->get('/{extension}/configure', 'np.extensions.controller:configureExtensionAction')
            ->convert('extension', $extensionFinder)
            ->bind('admin_configure_extension');

        $controllers->post('/{extension}/update', 'np.extensions.controller:updateAction')
            ->convert('extension', $extensionFinder)
            ->bind('admin_update_extension');

        $controllers->post('/{extension}/activate', 'np.extensions.controller:activateExtensionAction')
            ->convert('extension', $extensionFinder)
            ->bind('admin_activate_extension');

        $controllers->post('/{extension}/deactivate', 'np.extensions.controller:deactivateExtensionAction')
            ->convert('extension', $extensionFinder)
            ->bind('admin_deactivate_extension');

        return $controllers;
    }
}