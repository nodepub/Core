<?php

namespace Slideshow;

function info() {

    $data = array(
        'name' =>"Slideshow",
        'description' => "Add a slideshow to your site, when using <code>{{ slideshow(name) }}</code> in your templates.",
        'author' => "Andrew Gruner",
        'link' => "http://nodepub.com",
        'version' => "0.1",
        'required_npub_version' => "0.7",
        'highest_npub_version' => "0.8",
        'type' => "Twig Function",
        'first_releasedate' => "2012-11-10",
        'latest_releasedate' => "2012-11-10",
        'dependancies' => "",
        'priority' => 10
    );

    return $data;
}

function init($app) {
    $app['twig']->addFunction('slideshow', new \Twig_Function_Function('HelloWorld\render'));
    $app['twig.loader']->addPath(__DIR__, 'extension');
}

function render($slug="") {

    $slideshows = $app['yaml']->load('slideshows');
    $slideshow = $slideshows->get($slug);

    return $app['twig']->render('@extension/Slideshow.twig', array(
        'slug' => $slug,
        'img_path' => $slideshow['img_path'],
        'slides' => $slideshow['slides']
    ));
}
