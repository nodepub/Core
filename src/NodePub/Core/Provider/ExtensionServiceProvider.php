<?php

namespace NodePub\Core\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use NodePub\Core\Extension\ExtensionContainer;
use NodePub\Core\Extension\DomManipulator;
use NodePub\Core\Routing\ExtensionRouting;
use NodePub\Core\Controller\ExtensionController;
use NodePub\Core\Model\BlockProvider;

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
    }

    public function boot(Application $app)
    {
        $app->before(function() use ($app) {
            $app['np.extensions']['debug'] = $app['debug'];
            $app['np.extensions']['admin'] = $app['np.admin'];
            $app['np.extensions']->boot();
            
            // add template paths for all blocks
            $app['twig.loader.filesystem'] = $app->share($app->extend('twig.loader.filesystem', function($loader, $app) {
                foreach ($app['np.extensions']['block_types'] as $extensionName => $blockType) {
                    
                    $path = __DIR__ . '/../Extensions/' . $extensionName . '/Blocks/' . $blockType;
                    
                    if (is_dir($path) || is_link($path)) {
                        $loader->addPath($path, 'block_' . strtolower($blockType));
                    }
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

            $app['np.extensions']->prepareSnippets();

            $response->setContent(
                $app['np.extensions']['snippet_queue']->processAll($app, $response->getContent())
            );
        });
    }
}