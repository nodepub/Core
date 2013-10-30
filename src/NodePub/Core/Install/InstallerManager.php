<?php

namespace NodePub\Core\Install;

use Doctrine\ORM\Tools\SchemaTool;
use NodePub\Core\Install\InstallerInterface;
use Silex\Application;

/**
 * Allows installers from other modules to register for installation,
 * and runs the installation of each.
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
        $this->entityClasses = array_merge($this->entityClasses, $installer->getEntityClasses());
    }
    
    protected function createSchema()
    {
        // $classes = array();
        // foreach (array_unique($this->entityClasses) as $className) {
        //     $classes[] = $this->app['orm.em']->getClassMetadata($className);
        // }
        
        $em = $this->app['orm.em'];
        
        $classes = array_map(function($className) use ($em) {
            return $em->getClassMetadata($className);
        }, array_unique($this->entityClasses));
        
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