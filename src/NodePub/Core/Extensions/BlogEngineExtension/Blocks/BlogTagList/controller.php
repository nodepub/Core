<?php

class BlogTagListBlockController extends NodePub\Controller\BlockController
{
    /**
     * Initializes block
     */
    public function init()
    {
        // if (!$app['site']['has_blog']) {
        //     $this->postManager = $app['blog.post_manager'];
        // }
    }

    /**
     * Formats the data that gets passed to the template file (view.twig)
     */
    public function getTemplateParams()
    {
        $tags = $this->postManager->getTags();

        // sort tags in descending order of popularity
        uasort($tags, function($a, $b) {
            if ($a['count'] == $b['count']) {
                return 0;
            }
            return ($a['count'] > $b['count']) ? -1 : 1;
        });

        return array('tags' => $tags);
    }

    public function install() {}
    public function uninstall() {}

    public function create() {}
    public function find() {}
    public function update() {}
    public function delete() {}
}
