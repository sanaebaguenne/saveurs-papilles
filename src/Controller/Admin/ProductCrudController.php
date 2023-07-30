<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('content'),
            TextEditorField::new('description_short'),
            TextField::new('imageFile')->setFormType(VichImageType::class),
            ImageField::new('image')->setBasePath('/images/produits')->onlyOnIndex(),
            MoneyField::new('price')->setCurrency('EUR'),
            IntegerField::new('stock')->hideOnIndex(), // Champ stock (caché sur la liste)
            // ...
            DateField::new('createdAt'),
            ChoiceField::new('category', 'Catégorie')
              ->setChoices([
                'Chocolats' => 'Chocolats',
                'Macarons' => 'Macarons',
                'Confiseries' => 'Confiseries',
                'Patisseries' => 'Patisseries',
                'Gouters' => 'Gouters'
              ]),

        ];
    }
    
}
