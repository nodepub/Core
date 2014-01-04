<?php

namespace NodePub\Core\Extensions\CoreExtension;

class TwigExtension extends \Twig_Extension
{
    protected $twigEnvironment,
              $blockProvider,
              $slugHelper
              ;
    
    function __construct($blockProvider, $slugHelper)
    {
        $this->blockProvider = $blockProvider;
        $this->slugHelper = $slugHelper;
    }

    public function getName()
    {
        return 'NodePubCore';
    }
    
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->twigEnvironment = $environment;
    }
    
    public function getFunctions()
    {
        return array(
            'embed_image' => new \Twig_Function_Method($this, 'embedImage'),
            'embed_slideshow' => new \Twig_Function_Method($this, 'embedSlideshow'),
            'embed_youtube' => new \Twig_Function_Method($this, 'embedYouTube'),
        );
    }
    
    public function getFilters()
    {
        $slugHelper = $this->slugHelper;
        
        return array(
            new \Twig_SimpleFilter('slugify', function($input) use ($slugHelper) {
                if (is_array($input)) {
                    foreach ($input as $key => $item) {
                        $input[$key] = $slugHelper->slugify($item);
                    }
                    return $input;
                } else {
                    return $slugHelper->slugify($input);
                }
            }),
        );
    }
    
    public function embedImage($blockId)
    {}
    
    public function embedSlideshow($blockId)
    {
        $block = $this->blockProvider->get($blockId);
        if ($block && isset($block['slides']) && isset($block['img_path'])) {
            return $this->twigEnvironment->render('@block_slideshow/view.twig', array(
                'block_id' => $blockId,
                'slides' => $block['slides'],
                'img_path' => $block['img_path']
            ));
        }
    }
    
    public function embedYouTube($blockId)
    {}
}
