<?php

namespace NodePub\Core\Extensions\CoreExtension;

class TwigExtension extends \Twig_Extension
{
    protected $twigEnvironment,
              $blockProvider,
              $slugHelper,
              $markdownHelper
              ;
    
    function __construct($blockProvider, $slugHelper, $markdownHelper)
    {
        $this->blockProvider = $blockProvider;
        $this->slugHelper = $slugHelper;
        $this->markdownHelper = $markdownHelper;
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
            'embed_image'     => new \Twig_Function_Method($this, 'embedImage'),
            'embed_slideshow' => new \Twig_Function_Method($this, 'embedSlideshow'),
            'embed_youtube'   => new \Twig_Function_Method($this, 'embedYouTube'),
            'split_button'    => new \Twig_Function_Method($this, 'splitButton'),
        );
    }
    
    public function getFilters()
    {
        $slugHelper = $this->slugHelper;
        $markdownHelper = $this->markdownHelper;
        
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
            
            new \Twig_SimpleFilter('markdown', function($input) use ($markdownHelper) {
                return $markdownHelper->transform($input);
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
    
    /**
     * Renders YouTube embed html for a YouTube block or raw video id
     */
    public function embedYouTube($blockOrVideoId)
    {
        $block = $this->blockProvider->get($blockOrVideoId);
        
        if ($block && isset($block['video'])) {
            $video = $block['video'];
        } else {
            $video = $blockOrVideoId;
        }
        
        return $this->twigEnvironment->render('@block_youtube/view.twig', array(
            'block_id' => $blockOrVideoId,
            'video' => $video,
        ));
    }
    
    public function splitButton($href, $label1, $label2 = '►', $class = '')
    {
        $class = 'btn-split ' . $class;
        $btn = "<a class=\"$class\" href=\"$href\">"
            . "<span>$label1</span>"
            . "<span>$label2</span>"
            . '</a>'
            ;
        
        return $btn;
    }
}
