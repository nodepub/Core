<?php

namespace NodePub\Core;

/**
 * 
 */
class Installer
{
    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function install()
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

        return true;
    }
}