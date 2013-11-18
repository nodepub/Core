<?php

namespace NodePub\Core\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use NodePub\Core\Form\DataTransformer\ArrayToStringTransformer;

/**
 * Creates a field type for entering an array of tags as text input
 */
class TextTagsType extends AbstractType
{
    protected $tagDelimiter;
    
    public function __construct($tagDelimiter = ',')
    {
        $this->tagDelimiter = $tagDelimiter;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ArrayToStringTransformer($this->tagDelimiter);
        $builder->addModelTransformer($transformer);
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array());
    }
    
    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'text_tags';
    }
}