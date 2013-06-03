<?php

namespace NodePub\Core\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

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
        return $this->app->redirect($this->app['url_generator']->generate('admin_dashboard'));
    }

    /**
     * Toolbar
     */
    public function toolbarAction()
    {
        //$token = $this->app['security']->getToken();
        return new Response($this->app['twig']->render('@np-admin/_toolbar.twig', array(
            'username' => 'Andrew', //$token->getUser()->getUsername(),
            'toolbar' => $this->app['np.admin.toolbar']->getActiveItems(),
            'js_modules' => array()
        )));
    }

    /**
     * Dashboard
     */
    public function dashboardAction()
    {
        $actionMap = array(
            array(
                'Sites' => array(
                    array('name' => 'Sites', 'route' => 'admin_sites')),
                'Content' => array(
                    array('name' => 'Sitemap', 'route' => 'admin_sitemap'),
                    array('name' => 'Node Types', 'route' => 'admin_node_types')),
                'Design' => array(
                    array('name' => 'Themes', 'route' => 'admin_themes'),
                    array('name' => 'Templates', 'route' => 'admin_templates'))
            ),
            array(
                'Components' => array(
                    array('name' => 'Blocks', 'route' => 'admin_blocks'),
                    array('name' => 'Extensions', 'route' => 'admin_extensions')),
                'Reports' => array(
                    array('name' => 'Statistics', 'route' => 'admin_stats'),
                    array('name' => 'Logs', 'route' => 'admin_logs'))
            )
        );

        // Remove inactive actions if no admin route exists
        foreach ($actionMap as $sectionKey => $section) {
            foreach ($section as $header => $actions) {
                foreach ($actions as $actionKey => $action) {
                    try {
                        $url = $this->app['url_generator']->generate($action['route']);
                        $actionMap[$sectionKey][$header][$actionKey]['url'] = $url;
                    } catch (RouteNotFoundException $e) {
                        unset($actionMap[$sectionKey][$header][$actionKey]);
                    }
                }
            }
        }

        return new Response($this->app['twig']->render('@np-admin/dashboard.twig', array(
            'dashboard_actions' => $actionMap
        )));
    }

    /**
     * Settings
     */
    public function settingsAction()
    {
        sleep(3);
        return new Response($this->app['twig']->render('@np-admin/settings.twig', array(
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
    
    public function sitemapAction(Request $request)
    {
        $nodes = $this->app['db.orm.em']
            ->getRepository('NodePub\Model\Node')
            ->findAll();
        
        return new Response($this->app['twig']->render('@core/admin/sitemap.twig', array(
            'nodes' => $nodes,
            'node_types' => array(),
            'components' => array()
        )));
    }

    public function usersAction()
    {
        return $this->app->json(array('Users!' => array()));
    }

    public function userAction()
    {
        return $this->app->json(array('User!' => array()));
    }

    public function logAction()
    {
        if (!file_exists($this->app['log_file'])) {
            return 'no file in '.$this->app['log_file'];
        }

        $logContents = file_get_contents($this->app['log_file']);
        $logContents = $logContents ? $logContents : 'Log could not be loaded.';

        return new Response($this->app['twig']->render('@core/admin/log.twig', array(
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

        return new Response($this->app['twig']->render('@core/admin/stats.twig', array(
            'events' => $events,
            'stats'  => $stats
        )));
    }

    public function blocksAction()
    {
        $blocks = $this->app['block_manager']->getInfo();

        return $this->app['twig']->render('@core/admin/blocks.twig', array('blocks' => $blocks));
    }

    public function installBlockAction(Request $request, $blockNamespace)
    {

        $blockType = $this->app['block_manager']->findBlockType($blockNamespace);

        // Block is already installed
        if ($blockType && $this->app['block_manager']->blockTypeTableExists($blockType->getTableName())) {
            $this->app['session']->setFlash('info', $this->app['translator']->trans('installed.exists', array('%name%' => $blockNamespace . ' block')));

            return $this->app->redirect($this->app['url_generator']->generate('admin_blocks'));
        }

        $blockType = $this->app['block_manager']->installBlock($blockNamespace);

        // enable the block type for the current site
        $this->app['site']->enableBlockType($blockType);

        $this->app['db.orm.em']->persist($this->app['site']);
        $this->app['db.orm.em']->flush();

        if ($blockType) {
            $level = 'success';
            $transKey = 'installed.success';
        } else {
            $level = 'error';
            $transKey = 'installed.error';

        }

        $this->app['session']->setFlash($level, $this->app['translator']->trans($transKey, array('%name%' => $blockNamespace . ' block')));

        return $this->app->redirect($this->app['url_generator']->generate('admin_blocks'));
    }

    public function blockTypesAction(Request $request)
    {
        $blockTypes = $this->app['block_manager']->getEnabledBlockTypes();
    }

    public function clearCacheAction($all)
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

    public function installAction(Request $request, $step = 1)
    {
        $installer = new \NodePub\Core\Installer($this->app);

        if ($installer->install()) {
            return $this->app->json(array('success' => true));
        }
    }
    
    /**
     * Checks if request is ajax or expecting json returned
     */
    protected function isApiRequest(Request $request)
    {
        return ($request->isXmlHttpRequest() || $request->getRequestFormat() == 'json');
    }
}