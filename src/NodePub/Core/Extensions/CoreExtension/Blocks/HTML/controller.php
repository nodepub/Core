<?php

class HTMLBlockController extends NodePub\Controller\BlockController
{

    public function install()
    {
        $schema = new \Doctrine\DBAL\Schema\Schema();
        $table = $schema->createTable($this->getTableName());
        $table->addColumn("id", "integer", array("unsigned" => true, 'autoincrement' => true));
        $table->setPrimaryKey(array("id"));
        $table->addColumn('content', 'text');

        $queries = $schema->toSql($this->app['db']->getDatabasePlatform());
        $queries = implode("; ", $queries);
        $this->app['db']->query($queries);

        return true;
    }

    public function uninstall()
    {
    }
    
    /**
     * Formats the data that gets passed to the template file (view.twig)
     */
    public function getTemplateParams()
    {
        return array('content' => $this->data['content']);
    }

    public function getForm($data)
    {
        $form = $this->app['form.factory']->createBuilder('form', $data)
            ->add('content', 'textarea')
            ->getForm();

        return $form;
    }
    
    public function create($content)
    {
        $this->app['db']->insert($this->getTableName(), array('content' => $content));
    }
    
    public function find($id)
    {   
        $sql = "SELECT * FROM ". $this->getTableName() ." WHERE id = ?";
        
        return $this->app['db']->fetchAssoc($sql, array((int) $id));
    }

    public function update($id, $content)
    {   
        $sql = "UPDATE ". $this->getTableName() ." SET content = ? WHERE id = ?";
        
        return $this->app['db']->executeUpdate($sql, array($content, (int) $id));
    }
    
    public function delete($id) {}
}
