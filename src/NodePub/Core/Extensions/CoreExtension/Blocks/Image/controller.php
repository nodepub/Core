<?php

class ImageBlockController extends NodePub\Controller\BlockController
{
    /**
     * Formats the data that gets passed to the template file (view.twig)
     */
    public function getTemplateParams()
    {
        return array('image' => $this->model);
    }

    public function install() {}
    public function uninstall() {}
    public function create() {}
    public function find() {}
    public function update() {}
    public function delete() {}
}
