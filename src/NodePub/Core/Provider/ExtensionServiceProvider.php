<?php

namespace NodePub\Core\Provider;

use NodePub\Core\Controller\ExtensionController;
use NodePub\Core\Extension\DomManipulator;
use NodePub\Core\Extension\ExtensionContainer;
use NodePub\Core\Helper\ImageHelper;
use NodePub\Core\Model\BlockProvider;
use NodePub\Core\Routing\ExtensionRouting;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtensionServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['np.extensions'] = $app->share(function($app) {
            return new ExtensionContainer($app, array(
                'toolbar' => $app['np.admin.toolbar'],
            ));
        });
        
        $app['np.extensions.mount_point'] = '/extensions';

        $app['np.extensions.controller'] = $app->share(function($app) {
            return new ExtensionController($app, $app['np.extensions']);
        });
        
        $app['np.admin.controllers'] = $app->share($app->extend('np.admin.controllers', function($adminControllers, $app) {
            $extensionControllers = new ExtensionRouting();
            $extensionControllers = $extensionControllers->connect($app);
            $adminControllers->mount($app['np.extensions.mount_point'], $extensionControllers);
            
            return $adminControllers;
        }));
        
        $app['np.block_provider'] = $app->share(function($app) {
            return new BlockProvider();
        });
        
        $app['np.image_helper'] = $app->share(function($app) {
            $helper = new ImageHelper();
            $helper->publicSiteRootPath = '/assets/images/';
            return $helper;
        });
    }

    public function boot(Application $app)
    {
        $app->before(function() use ($app) {
            
            $app['np.extensions']['debug'] = $app['debug'];
            $app['np.extensions']['admin'] = $app['np.admin'];
            $app['np.extensions']->boot();
            
            // add template paths for all extensions and blocks
            $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem', function($loader, $app) {
                foreach ($app['np.extensions']->getAll() as $namespace => $extension) {
                    $loader->addPath($extension->getPath(), $namespace);
                }
                foreach ($app['np.extensions']['block_types'] as $blockType) {
                    $loader->addPath($blockType->getPath(), 'block_' . $app['np.slug_helper']->slugify($blockType->name));
                }
                
                return $loader;
            }));
            
            // load collected twig functions
            $app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
                foreach ($app['np.extensions']['twig_extensions'] as $extension) {
                    $twig->addExtension($extension);
                }
                return $twig;
            }));
        });

        $app->after(function(Request $request, Response $response) use ($app) {
            
            if ($response instanceof BinaryFileResponse || !$app['np.extensions']->booted) {
                return;
            }

            $app['np.extensions']->prepareSnippets();

            $response->setContent(
                $app['np.extensions']['snippet_queue']->processAll($app, $response->getContent())
            );
        });
    }
}