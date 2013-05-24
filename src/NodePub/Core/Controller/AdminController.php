<?php

namespace NodePub\Core\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController
{
    /**
     * Index - redirects to dashboard
     */
    public function indexAction(Application $app)
    {
        return $app->redirect($app['url_generator']->generate('admin_dashboard'));
    }
    
    /**
     * Dashboard
     */
    public function dashboardAction(Application $app)
    {
        return new Response($app['twig']->render('@core/admin/dashboard.twig'));
    }

    /**
     * Toolbar
     */
    public function toolbarAction(Application $app)
    {
        if ($app['security']->isGranted('ROLE_ADMIN')) {
            $token = $app['security']->getToken();
            return new Response($app['twig']->render('@core/admin/_toolbar.twig', array(
                'username' => $token->getUser()->getUsername(),
                'toolbar' => $app['np.admin.toolbar']->getActiveItems()
            )));
        } else {
            return new Response();
        }
    }
    
    public function sitemapAction(Request $request, Application $app)
    {
        $nodes = $app['db.orm.em']
            ->getRepository('NodePub\Model\Node')
            ->findAll();
        
        // if ($this->isApiRequest($request)) {
        //             return $app->json($nodes);
        //         }
        
        return new Response($app['twig']->render('@core/admin/sitemap.twig', array(
            'nodes' => $nodes,
            'node_types' => array(),
            'components' => array()
        )));
    }

    public function themesAction(Request $request, Application $app)
    {
        $themes = $app['theme.manager']->loadThemeConfigs();
        
        foreach ($themes as $config) {
            $config['layouts'] = $app['theme.manager']->getThemeLayouts($config['name']);
        }
        
        // if ($this->isApiRequest($request)) {
        //     return $app->json($themes);
        // }
        
        return new Response($app['twig']->render('@core/admin/themes.twig', array(
            'themes' => $themes
        )));
    }

    public function usersAction(Application $app)
    {
        return $app->json(array('Users!' => array()));
    }

    public function userAction(Application $app)
    {
        return $app->json(array('User!' => array()));
    }

    public function logAction(Application $app)
    {
        if (!file_exists($app['log_file'])) {
            return 'no file in '.$app['log_file'];
        }

        $logContents = file_get_contents($app['log_file']);
        $logContents = $logContents ? $logContents : 'Log could not be loaded.';

        return new Response($app['twig']->render('@core/admin/log.twig', array(
            'log' => $logContents
        )));
    }

    public function statsAction(Application $app)
    {
        if (!file_exists($app['log_file'])) {
            return;
        }

        $logLines = file($app['log_file']);
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

        return new Response($app['twig']->render('@core/admin/stats.twig', array(
            'events' => $events,
            'stats'  => $stats
        )));
    }

    public function blocksAction(Application $app)
    {
        $blocks = $app['block_manager']->getInfo();

        return $app['twig']->render('@core/admin/blocks.twig', array('blocks' => $blocks));
    }

    public function installBlockAction(Request $request, Application $app, $blockNamespace)
    {

        $blockType = $app['block_manager']->findBlockType($blockNamespace);

        // Block is already installed
        if ($blockType && $app['block_manager']->blockTypeTableExists($blockType->getTableName())) {
            $app['session']->setFlash('info', $app['translator']->trans('installed.exists', array('%name%' => $blockNamespace . ' block')));

            return $app->redirect($app['url_generator']->generate('admin_blocks'));
        }

        $blockType = $app['block_manager']->installBlock($blockNamespace);

        // enable the block type for the current site
        $app['site']->enableBlockType($blockType);

        $app['db.orm.em']->persist($app['site']);
        $app['db.orm.em']->flush();

        if ($blockType) {
            $level = 'success';
            $transKey = 'installed.success';
        } else {
            $level = 'error';
            $transKey = 'installed.error';

        }

        $app['session']->setFlash($level, $app['translator']->trans($transKey, array('%name%' => $blockNamespace . ' block')));

        return $app->redirect($app['url_generator']->generate('admin_blocks'));
    }

    public function blockTypesAction(Request $request, Application $app)
    {
        $blockTypes = $app['block_manager']->getEnabledBlockTypes();
    }

    public function clearCacheAction(Application $app, $all)
    {
        # delete the cache for all sites or just the current site
        # depending on the flag in the url
        $cacheDir = $all == 'all' ? $app['cache_dir'].'/..' : $app['cache_dir'];

        $files = Symfony\Component\Finder\Finder::create()
            ->files()
            ->name('*')
            ->in($cacheDir);
            
        foreach ($files as $file) {
            try {
                unlink($file->getRealPath());
            } catch (\Exception $e) {
                # @TODO error logging, more response info
                return $app->json(array('success' => false));
            }
        }

        return $app->json(array('success' => true));
    }

    public function installAction(Request $request, Application $app, $step = 1)
    {
        // TODO: schema tool will mess with all tables in the db,
        // don't use for final implementation

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($app['db.orm.em']);
        $classes = array(
            $app['db.orm.em']->getClassMetadata('NodePub\Model\Site'),
            $app['db.orm.em']->getClassMetadata('NodePub\Model\SiteAttribute'),
            $app['db.orm.em']->getClassMetadata('NodePub\Model\Node'),
            $app['db.orm.em']->getClassMetadata('NodePub\Model\NodeAttribute'),
            $app['db.orm.em']->getClassMetadata('NodePub\Model\PublishState'),
            $app['db.orm.em']->getClassMetadata('NodePub\Model\Block'),
            $app['db.orm.em']->getClassMetadata('NodePub\Model\BlockType')
        );

        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);

        # ===================================================== #
        #    Create Default Site                                #
        # ===================================================== #

        $site = new \NodePub\Model\Site();
        $site->setName('NodePub')
            ->setDomainName('nodepub.com')
            ->setTemplate('@default/layout.twig');

        $app['db.orm.em']->persist($site);
        
        $siteDescription = new \NodePub\Model\SiteAttribute();
        $siteDescription
            ->setSite($site)
            ->setName('description')
            ->setValue('NodePub: A CMS that WON\'T kidnap and kill you');
            
        $app['db.orm.em']->persist($siteDescription);

        # ===================================================== #
        #    Create Publish States                              #
        # ===================================================== #

        $publishStates = array();
        $stateDefinitions = array(
            'status.draft' => 'status.draft.desc',
            'status.published' => 'status.published.desc',
            //'status.scheduled' => 'Publically viewable on set date.',
            'status.archived' => 'status.archived.desc');

        foreach ($stateDefinitions as $name => $desc) {
            $state = new \NodePub\Model\PublishState();
            $state->setName($name)
                ->setDescription($desc);
            $app['db.orm.em']->persist($state);

            $publishStates[$name] = $state;
        }

        # ===================================================== #
        #    Create Core Block Types                            #
        # ===================================================== #

        $htmlType = $app['block_manager']->installBlock('HTML');
        $htmlType->setCore(true);

        $markdownBlockType = $app['block_manager']->installBlock('Markdown');
        $markdownBlockType->setCore(true);

        $site->enableBlockType($htmlType);
        $site->enableBlockType($markdownBlockType);

        # ===================================================== #
        #    Create Example Nodes                               #
        # ===================================================== #

        $node = new \NodePub\Model\Node();
        $node->setTitle('Welcome')
            ->setSlug('home')
            ->setPath('home')
            ->setTemplate('@default/layout.twig')
            ->setSite($site)
            ->setPublishState($publishStates['status.published'])
            ;
        $app['db.orm.em']->persist($node);

        $nodeDescription = new \NodePub\Model\NodeAttribute();
        $nodeDescription
            ->setNode($node)
            ->setName('description')
            ->setValue('This description overrides the Site description');
        $app['db.orm.em']->persist($nodeDescription);

        $nodeTitle = new \NodePub\Model\NodeAttribute();
        $nodeTitle
            ->setNode($node)
            ->setName('title')
            ->setValue('This title overrides the Site title');
        $app['db.orm.em']->persist($nodeTitle);
        
        $app['db']->insert('np_html_blocks', array('content' => '<p>Welcome to NodePub</p>'));
        $app['db']->insert('np_html_blocks', array('content' => '<p>This is an example of an editable block.</p>'));

        $mainBlock = new \NodePub\Model\Block();
        $mainBlock
            ->setAreaName('Main')
            ->setOrder(1)
            ->setBlockContentId(1)
            ->setBlockType($htmlType)
            ->setNode($node);

        $app['db.orm.em']->persist($mainBlock);

        $sidebarBlock = new \NodePub\Model\Block();
        $sidebarBlock
            ->setAreaName('Sidebar')
            ->setOrder(1)
            ->setBlockContentId(2)
            ->setBlockType($htmlType)
            ->setNode($node);

        $app['db.orm.em']->persist($sidebarBlock);


        $app['db.orm.em']->flush();

        return $app->json(array('success' => true));
    }

    public function testEmail(Request $request, Application $app)
    {
        $emailMessageBody = $request->get('messageBody', 'This is an email test');

        try {
            $emailMessage = $app['error_message'];
            $emailMessage->setBody($emailMessageBody, 'text/plain');

            if ($status = $app['mailer']->send($emailMessage, $failures)) {
                $statusMessage = 'Message was successfully sent';
            } else {
                $statusMessage = 'Message could not sent to: '.implode(', ', $failures);
            }
        } catch (Exception $e) {
            $statusMessage = 'Error while attempting to send email';
            $app['monolog']->addError(array(
                'message'    => $statusMessage,
                'error'      => $e->getMessage(),
            ));
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