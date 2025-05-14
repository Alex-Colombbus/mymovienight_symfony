<?php

namespace App\Controller\Admin;

use App\Entity\ListFilm;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ListFilmCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ListFilm::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // ... autres configurations
            ->setSearchFields(['Utilisateur', 'tconst']); // Propriétés sur lesquelles chercher
        // 'actors.name' suppose une relation 'actors' avec une propriété 'name'
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(), // Cache l'ID dans le formulaire
            AssociationField::new('tconst', 'Film') // Relation avec FilmFiltre
                ->autocomplete() // Active le champ de recherche pour éviter une liste déroulante trop longue
                ->setCrudController(FilmFiltreCrudController::class), // Optionnel : navigation vers le CRUD FilmFiltre
            AssociationField::new('liste', 'Liste') // Relation avec Liste
                ->autocomplete(), // Active également le champ de recherche pour Liste
            TextField::new('listeUser', 'Utilisateur') // Champ calculé pour afficher l'utilisateur
                ->formatValue(function ($value, $entity) {
                    return $entity->getListeUser(); // Utilise la méthode ajoutée dans ListFilm
                })
                ->hideOnForm(), // Lecture seule dans le formulaire
            DateField::new('added_at', 'Date d\'ajout')->hideOnForm(), // Affiche la date d'ajout en lecture seule
        ];
    }
}
