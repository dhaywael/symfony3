<?php
// src/OC/PlatformBundle/Form/AdvertEditType.php

namespace OC\PlatformBundle\Form\Advert;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PostEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('date')
            ->remove('published');
    }

    public function getParent()
    {
        return PostType::class;
    }
}
