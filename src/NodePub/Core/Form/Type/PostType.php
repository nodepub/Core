<?php

namespace NodePub\Core\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use NodePub\Core\Form\Type\TextTagsType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('slug', 'text')
            ->add('rawContent', 'textarea', array(
                'label' => 'Content'
            ))
            ->add('tags', new TextTagsType());
    }

    public function getName()
    {
        return 'post';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NodePub\BlogEngine\Post',
        ));
    }
}