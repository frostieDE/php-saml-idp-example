<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class ServiceProviderType extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('entityId', TextType::class)
            ->add('name', TextType::class)
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('certificate', TextareaType::class)
            ->add('callbackUrl', UrlType::class);
    }
}