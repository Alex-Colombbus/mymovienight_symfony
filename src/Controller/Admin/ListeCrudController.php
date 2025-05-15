<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Liste;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;

class ListeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Liste::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name_liste')->setLabel('Nom de la liste'),
            AssociationField::new('user')->setLabel('Utilisateur') // Affiche le nom de l'utilisateur
                ->setCrudController(UserCrudController::class), // Optionnel : permet de naviguer vers le CRUD User
            DateField::new('created_at', 'Date de création')->hideOnForm(),
        ];
    }
}
