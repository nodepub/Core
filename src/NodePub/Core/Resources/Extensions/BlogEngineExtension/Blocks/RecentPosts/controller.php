<?php

class RecentPostsBlockController extends NodePub\Controller\BlockController
{
    /**
     * Initializes block
     */
    public function init()
    {
        // if (!$app['site_config']['has_blog']) {
        //     $this->postManager = $app['blog.post_manager'];
        // }
    }

    /**
     * Formats the data that gets passed to the template file (view.twig)
     */
    public function getTemplateParams()
    {
        $postLimit = $this->model->postLimit;
        return array('posts' => $this->postManager->findRecentPosts($postLimit, 1, false));
    }

    public function install() {}
    public function uninstall() {}

    public function create() {}
    public function find() {}
    public function update() {}
    public function delete() {}
}
