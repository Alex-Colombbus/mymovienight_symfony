<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {

        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
        ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->setDisabled()->setFormTypeOption('disabled', true)->hideOnForm(),
            EmailField::new('email')->onlyOnIndex()->setLabel('E-mail'),
            TextField::new('username')->setLabel('Nom d\'utilisateur'),
            ChoiceField::new('roles')
                ->setLabel('Rôles')
                ->setChoices([
                    // Customize these roles according to your application
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    // Add other roles here, e.g., 'Modérateur' => 'ROLE_MODERATOR'
                ])
                ->allowMultipleChoices() // Allows selecting multiple roles
                ->renderExpanded() // Renders as checkboxes
                ->setHelp('Sélectionnez les rôles de l\'utilisateur.'),
        ];
    }
}
