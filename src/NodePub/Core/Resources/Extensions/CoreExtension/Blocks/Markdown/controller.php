<?php

class MarkdownBlockController extends NodePub\Controller\BlockController
{
    /**
     * Formats the data that gets passed to the template file (view.twig)
     */
    public function getTemplateParams()
    {
        return array('content' => $this->data['filtered_content']);
    }

    public function install()
    {
        $schema = new \Doctrine\DBAL\Schema\Schema();
        $table = $schema->createTable($this->getTableName());
        $table->addColumn("id", "integer", array("unsigned" => true, 'autoincrement' => true));
        $table->setPrimaryKey(array("id"));
        $table->addColumn('content', 'text');
        $table->addColumn('filtered_content', 'text');

        $queries = $schema->toSql($this->app['db']->getDatabasePlatform());
        $queries = implode("; ", $queries);
        $this->app['db']->query($queries);

        return true;
    }

    public function uninstall() {}

    public function create($content) {
        $filteredContent = $this->app['markdown']->render($content);

        $this->app['db']->insert($this->getTableName(), array(
            'content' => $content,
            'filtered_content' => $filteredContent
        ));
    }

    public function find() {}
    public function delete() {}
    
    public function update($id, $content)
    {   
        $filteredContent = $this->app['markdown']->render($content);
        
        $sql = "UPDATE ". $this->getTableName() ." SET content = ?, filtered_content = ? WHERE id = ?";
        
        return $this->app['db']->executeUpdate($sql, array($content, $filteredContent, (int) $id));
    }
}
