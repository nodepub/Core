<?php

namespace NodePub\Core\Form\Type;

use NodePub\Core\Form\DataTransformer\ArrayToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * Creates a field type for entering an array of tags as text input
 */
class TextTagsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $transformer = new ArrayToStringTransformer();
        $builder->appendClientTransformer($transformer);
    }

    public function getName()
    {
        return 'text_tags';
    }
    
    public function getParent(array $options)
    {
        return 'text';
    }
    
    public function getDefaultOptions(array $options)
    {
        return array();
    }
}