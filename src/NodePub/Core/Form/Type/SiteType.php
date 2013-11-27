<?php

namespace NodePub\Core\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('hostName', 'text')
            ->add('url', 'text')
            ->add('title', 'text')
            ->add('tagline', 'text')
            ->add('description', 'textarea')
            ;
    }

    public function getName()
    {
        return 'site';
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'NodePub\Core\Model\Site',
        ));
    }
}