<?php

namespace App\Form;

use App\Entity\Cabinet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CabinetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nomCabinet')
            ->add('nomContact')
            ->add('nomClient')
            ->add('tel')
            ->add('adresse')
            ->add('email')
            ->add('isActive')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cabinet::class,
        ]);
    }
}
