<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => ' Titre'
            ])
            ->add('content', TextType::class, [
                'label' => '💬 Description'
            ])
            ->add('descriptionshort', TextType::class, [
                'label' => '💬 Description court'
            ])
            -> add('ingredient', TextType::class, [
                'label' => '💬 Ingredients et allergènes'
            ])
           
           
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Chocolats' => 'Chocolats',
                    'Macarons' => 'Macarons',
                    'Confiseries' => 'Confiseries',
                    'Patisseries' => 'Patisseries',
                    'Gouters' => 'Gouters'
                    
                ]
            ])

            ->add('imageFile', FileType::class, [
                'required' => false,
                'label' => '📸 Photo du produit'
            ])
            ->add('price', TextType::class, [
                'label' => '🛒 Prix'
            ])
            ->add('stock', TextType::class, [
                'label' => '🏭'
            ])
            // ->add('createdAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
