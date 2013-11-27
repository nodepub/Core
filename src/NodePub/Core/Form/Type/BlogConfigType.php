<?php

namespace NodePub\Core\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BlogConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('path', 'text')
            ->add('permalink_format', 'text')
            ->add('post_limit', 'text')
            //->add('description', 'textarea')
            //->add('site_id', 'hidden')
            ;
    }

    public function getName()
    {
        return 'blog_config';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // $resolver->setDefaults(array(
        //     'data_class' => 'NodePub\Core\Model\---',
        // ));
    }
}