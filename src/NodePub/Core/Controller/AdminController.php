<?php

namespace NodePub\Core\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Index - redirects to dashboard
     */
    public function indexAction()
    {
        return $this->app->redirect($this->app['url_generator']->generate('admin_toolbar'));
    }

    public function installAction(Request $request, $step = 1)
    {
        // TODO check if already installed
        
        if (isset($this->app['np.installer'])) {
            try {
                $this->app['np.installer']->install();
                return new Response('installed!');
            } catch (\Exception $e) {
                return new Response($e->getMessage(), 500);
            }
        } else {
            return new Response('No installer configured', 500);
        }
    }

    public function toolbarAction()
    {
        //$token = $this->app['security']->getToken();

        return new Response($this->app['twig']->render('@np-admin/_toolbar.twig', array(
            'username' => 'Andrew', //$token->getUser()->getUsername(),
            'toolbar' => $this->app['np.admin.toolbar']->getGroupedActiveItems($this->app['np.admin.toolbar.group_size']),
            'js_modules' => array()
        )));
    }

    public function settingsAction()
    {
        return new Response($this->app['twig']->render('@np-admin/panels/settings.twig', array(
            'settings' => $this->app['np.admin.toolbar']->getActiveItems()
        )));
    }

    /**
     * Dynamically compiles all require.js modules defined by each extension
     */
    public function javaScriptsAction()
    {
        $resources = $this->app['np.extension_manager']->collectMethodCalls('getResourceManifest');
        foreach ($resources as $resource) {
            if (0 === strpos($resource, '/js')) {
                # code...
                if (is_file($resource)) {
                    file_get_contents($filename);
                }
            }
        }

        $response = new Response($js);
        $response->headers->set('Content-Type', 'application/javascript');

        return $response;
    }
    
    public function sitemapAction()
    {
        return $this->app['twig']->render('@np-admin/panels/sitemap.twig',
            array(
                'sitemap' => $this->app['np.sitemap']->getTree(),
                'node_types' => $this->app['np.node_types']
            )
        );
    }
    
    public function sitemapUpdateAction(Request $request)
    {
        return $this->app['twig']->render('@np-admin/panels/sitemap.twig',
            array('sitemap' => $this->app['np.sitemap']->getTree())
        );
    }

    public function usersAction()
    {
        sleep(2);
        return $this->app['twig']->render('@np-admin/panels/users.twig');
    }

    public function userAction()
    {
        return $this->app['twig']->render('@np-admin/panels/user.twig');
    }

    public function logAction()
    {
        if (!file_exists($this->app['log_file'])) {
            return 'no file in '.$this->app['log_file'];
        }

        $logContents = file_get_contents($this->app['log_file']);
        $logContents = $logContents ? $logContents : 'Log could not be loaded.';

        return new Response($this->app['twig']->render('@np-admin/panels/log.twig', array(
            'log' => $logContents
        )));
    }

    public function statsAction()
    {
        if (!file_exists($this->app['log_file'])) {
            return;
        }

        $logLines = file($this->app['log_file']);
        $events = array();
        $stats = array();

        # todo: refactor into a helper to avoid processing for stats and errors

        foreach ($logLines as $line) {
            $event = json_decode($line);

            $genericEvent = clone($event);
            unset($genericEvent->ip_address);

            $raw = json_encode($genericEvent->context);
            $hash = crc32($raw);

            if (array_key_exists($hash, $events)) {
                $events[$hash]->count++;
                $events[$hash]->datetime = $event->datetime;
            } elseif (array_key_exists($hash, $stats)) {
                $stats[$hash]->count++;
                $stats[$hash]->datetime = $event->datetime;
            } else {
                $event->count = 1;
                if ($event->level_name == "ERROR") {
                    $event->raw = $raw;
                    $events[$hash] = $event;
                } elseif($event->message == 'request') {
                    $stats[$hash] = $event;
                }
            }
        }

        usort($events, function($a, $b) {
            return $b->count > $a->count ;
        });

        usort($stats, function($a, $b) {
            return $b->count > $a->count ;
        });

        return new Response($this->app['twig']->render(' @np-admin/panels/stats.twig', array(
            'events' => $events,
            'stats'  => $stats
        )));
    }

    // TODO: move to admin blocks controller

    // public function blocksAction()
    // {
    //     $blocks = $this->app['block_manager']->getInfo();

    //     return $this->app['twig']->render('@core/admin/blocks.twig', array('blocks' => $blocks));
    // }

    // public function installBlockAction(Request $request, $blockNamespace)
    // {

    //     $blockType = $this->app['block_manager']->findBlockType($blockNamespace);

    //     // Block is already installed
    //     if ($blockType && $this->app['block_manager']->blockTypeTableExists($blockType->getTableName())) {
    //         $this->app['session']->setFlash('info', $this->app['translator']->trans('installed.exists', array('%name%' => $blockNamespace . ' block')));

    //         return $this->app->redirect($this->app['url_generator']->generate('admin_blocks'));
    //     }

    //     $blockType = $this->app['block_manager']->installBlock($blockNamespace);

    //     // enable the block type for the current site
    //     $this->app['site']->enableBlockType($blockType);

    //     $this->app['db.orm.em']->persist($this->app['site']);
    //     $this->app['db.orm.em']->flush();

    //     if ($blockType) {
    //         $level = 'success';
    //         $transKey = 'installed.success';
    //     } else {
    //         $level = 'error';
    //         $transKey = 'installed.error';

    //     }

    //     $this->app['session']->setFlash($level, $this->app['translator']->trans($transKey, array('%name%' => $blockNamespace . ' block')));

    //     return $this->app->redirect($this->app['url_generator']->generate('admin_blocks'));
    // }

    // public function blockTypesAction(Request $request)
    // {
    //     $blockTypes = $this->app['block_manager']->getEnabledBlockTypes();
    // }

    public function cacheAction()
    {
        return $this->app['twig']->render('@np-admin/panels/cache.twig');
    }

    public function postClearCacheAction($all)
    {
        # delete the cache for all sites or just the current site
        # depending on the flag in the url
        $cacheDir = $all == 'all' ? $this->app['cache_dir'].'/..' : $this->app['cache_dir'];

        $files = Symfony\Component\Finder\Finder::create()
            ->files()
            ->name('*')
            ->in($cacheDir);
            
        foreach ($files as $file) {
            try {
                unlink($file->getRealPath());
            } catch (\Exception $e) {
                # @TODO error logging, more response info
                return $this->app->json(array('success' => false));
            }
        }

        return $this->app->json(array('success' => true));
    }
}