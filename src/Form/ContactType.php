<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType as TypeTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname',TypeTextType::class,[
                'attr' => [
                    'class' => 'form-control', 
                    'minlenght' => '2',
                    'maxlenght' => '50',
                ],

                'label' => 'Nom', 
                'label_attr' => [
                    'class' => 'form-label  mt-4'
                ]
                
            ])
            ->add('lastname',TextType::class,[
                'attr' => [
                    'class' => 'form-control', 
                    'minlenght' => '2',
                    'maxlenght' => '50',
                ],

                'label' => 'Prénom', 
                'label_attr' => [
                    'class' => 'form-label  mt-4'
                ]
                
            ])
            ->add('email',EmailType::class,[
                'attr' => [
                    'class' => 'form-control', 
                    'minlenght' => '2',
                    'maxlenght' => '180',
                ],

                'label' => 'Adresse email', 
                'label_attr' => [
                    'class' => 'form-label  mt-4'
                ]
                
            ])
            ->add('subject',TextType::class,[
                'attr' => [
                    'class' => 'form-control', 
                    'minlenght' => '2',
                    'maxlenght' => '100',
                ],

                'label' => 'Sujet', 
                'label_attr' => [
                    'class' => 'form-label  mt-4'
                ]
                
            ])
            ->add('message', TextareaType::class,[
                'attr' => [
                    'class' => 'form-control', 
                    'minlenght' => '10',
                   
                ],

                'label' => 'Message', 
                'label_attr' => [
                    'class' => 'form-label  mt-4'
                ]
                
            ])
            ->add('submit', SubmitType::class,[
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ],
                'label' => 'Soumettre ma demande'
            ])
            // ->add('createdAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
