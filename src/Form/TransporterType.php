<?php

namespace App\Form;

use App\Entity\Transporter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransporterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('transporter', EntityType::class, [
                'class' => Transporter::class,
                'label' => false,
                'required' =>true, 
                'multiple' =>false,
            ])
            ->add('title')
            ->add('content')
            ->add('price')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transporter::class,
        ]);
    }
}
