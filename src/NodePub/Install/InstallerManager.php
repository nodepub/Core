<?php

namespace NodePub\Install;

use Doctrine\ORM\Tools\SchemaTool;
use NodePub\Install\InstallerInterface;
use Silex\Application;

/**
 * 
 */
class InstallerManager
{
    protected $app,
        $subInstallers,
        $entityClasses;
    
    function __construct(Application $app)
    {
        $this->app = $app;
        $this->entityClasses = array();
        $this->subInstallers = array();
    }

    public function install()
    {
        $this->createSchema();
        $this->runInstallers();
        $this->app['orm.em']->flush();

        return true;
    }
    
    public function register(InstallerInterface $installer)
    {
        $this->subInstallers[] = $installer;
        array_merge($this->entityClasses, $installer->getEntityClasses());
    }
    
    protected function createSchema()
    {
        $classes = array();
        
        foreach ($this->entityClasses as $className) {
            $classes[] = $this->app['orm.em']->getClassMetadata($className);
        }
        
        $schemaTool = new SchemaTool($this->app['orm.em']);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
    }
    
    protected function runInstallers()
    {
        foreach ($this->subInstallers as $installer) {
            $installer->install();
        }
    }
}