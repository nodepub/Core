<?php

namespace NodePub\Core\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('slug', 'text');
        $builder->add('rawContent', 'textarea', array(
            'label' => 'Content'
        ));
        
        //$builder->add('tags', 'text_tags');
    }

    public function getName()
    {
        return 'post';
    }
    
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'NodePub\BlogEngine\Post'
        );
    }
}