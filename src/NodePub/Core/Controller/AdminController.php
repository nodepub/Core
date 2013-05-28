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
        return $this->app->redirect($this->app['url_generator']->generate('admin_dashboard'));
    }

    /**
     * Toolbar
     */
    public function toolbarAction()
    {
        // if (true !== $this->app['security']->isGranted('ROLE_ADMIN')) {
        //     return new Response();
        // }

        //$token = $this->app['security']->getToken();
        return new Response($this->app['twig']->render('@np-admin/_toolbar.twig', array(
            'username' => 'Andrew', //$token->getUser()->getUsername(),
            'toolbar' => $this->app['np.admin.toolbar']->getActiveItems(),
            'js_modules' => array()
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
    
    /**
     * Dashboard
     */
    public function dashboardAction()
    {
        return new Response($this->app['twig']->render('@core/admin/dashboard.twig'));
    }

    /**
     * Dashboard
     */
    public function moreSettingsAction()
    {
        return new Response($this->app['twig']->render('@core/admin/more_settings.twig'), array(
            'toolbar' => $this->app['np.admin.toolbar']->getActiveItems()
        ));
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
        // TODO: schema tool will mess with all tables in the db,
        // don't use for final implementation

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->app['db.orm.em']);
        $classes = array(
            $this->app['db.orm.em']->getClassMetadata('NodePub\Model\Site'),
            $this->app['db.orm.em']->getClassMetadata('NodePub\Model\SiteAttribute'),
            $this->app['db.orm.em']->getClassMetadata('NodePub\Model\Node'),
            $this->app['db.orm.em']->getClassMetadata('NodePub\Model\NodeAttribute'),
            $this->app['db.orm.em']->getClassMetadata('NodePub\Model\PublishState'),
            $this->app['db.orm.em']->getClassMetadata('NodePub\Model\Block'),
            $this->app['db.orm.em']->getClassMetadata('NodePub\Model\BlockType')
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

        $this->app['db.orm.em']->persist($site);
        
        $siteDescription = new \NodePub\Model\SiteAttribute();
        $siteDescription
            ->setSite($site)
            ->setName('description')
            ->setValue('NodePub: A CMS that WON\'T kidnap and kill you');
            
        $this->app['db.orm.em']->persist($siteDescription);

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
            $this->app['db.orm.em']->persist($state);

            $publishStates[$name] = $state;
        }

        # ===================================================== #
        #    Create Core Block Types                            #
        # ===================================================== #

        $htmlType = $this->app['block_manager']->installBlock('HTML');
        $htmlType->setCore(true);

        $markdownBlockType = $this->app['block_manager']->installBlock('Markdown');
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
        $this->app['db.orm.em']->persist($node);

        $nodeDescription = new \NodePub\Model\NodeAttribute();
        $nodeDescription
            ->setNode($node)
            ->setName('description')
            ->setValue('This description overrides the Site description');
        $this->app['db.orm.em']->persist($nodeDescription);

        $nodeTitle = new \NodePub\Model\NodeAttribute();
        $nodeTitle
            ->setNode($node)
            ->setName('title')
            ->setValue('This title overrides the Site title');
        $this->app['db.orm.em']->persist($nodeTitle);
        
        $this->app['db']->insert('np_html_blocks', array('content' => '<p>Welcome to NodePub</p>'));
        $this->app['db']->insert('np_html_blocks', array('content' => '<p>This is an example of an editable block.</p>'));

        $mainBlock = new \NodePub\Model\Block();
        $mainBlock
            ->setAreaName('Main')
            ->setOrder(1)
            ->setBlockContentId(1)
            ->setBlockType($htmlType)
            ->setNode($node);

        $this->app['db.orm.em']->persist($mainBlock);

        $sidebarBlock = new \NodePub\Model\Block();
        $sidebarBlock
            ->setAreaName('Sidebar')
            ->setOrder(1)
            ->setBlockContentId(2)
            ->setBlockType($htmlType)
            ->setNode($node);

        $this->app['db.orm.em']->persist($sidebarBlock);


        $this->app['db.orm.em']->flush();

        return $this->app->json(array('success' => true));
    }

    public function testEmail(Request $request)
    {
        $emailMessageBody = $request->get('messageBody', 'This is an email test');

        try {
            $emailMessage = $this->app['error_message'];
            $emailMessage->setBody($emailMessageBody, 'text/plain');

            if ($status = $this->app['mailer']->send($emailMessage, $failures)) {
                $statusMessage = 'Message was successfully sent';
            } else {
                $statusMessage = 'Message could not sent to: '.implode(', ', $failures);
            }
        } catch (Exception $e) {
            $statusMessage = 'Error while attempting to send email';
            $this->app['monolog']->addError(array(
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