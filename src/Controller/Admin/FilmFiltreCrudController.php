<?php

namespace App\Controller\Admin;

use App\Entity\FilmFiltre;
use App\Form\JsonCodeEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FilmFiltreCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FilmFiltre::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // ... autres configurations
            ->setSearchFields(['title', 'tconst']); // Propriétés sur lesquelles chercher
        // 'actors.name' suppose une relation 'actors' avec une propriété 'name'
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('tconst', 'Identifiant Imdb'), // Champ ID unique
            TextField::new('title', 'Titre'),
            TextField::new('titleType', 'Type de titre'),
            TextareaField::new('synopsis', 'Synopsis')->hideOnIndex(),
            CodeEditorField::new('importantCrew', 'Équipe technique (Nom: Rôle(s))')
                ->setLanguage('javascript') // Use 'javascript' for JSON-like highlighting
                ->hideOnIndex()
                ->setHelp('Éditez le JSON directement. Exemple: {"Nom_Prenom": "Role1, Role2", "Autre_Nom": "Role"}')
                ->setFormType(JsonCodeEditorType::class),
            TextField::new('actors', '3 acteurs principaux (acteur,acteur,acteur)')->hideOnIndex(),
            TextField::new('posterPath', 'Chemin de l\'affiche imdb')->hideOnIndex(),
            BooleanField::new('isAdult', 'Film pour adultes'),
            NumberField::new('startYear', 'Année de début'),
            NumberField::new('runtimeMinutes', 'Durée (minutes)'),
            AssociationField::new('genresCollection')
                ->setLabel('Genres')
                ->setFormTypeOptions([
                    'multiple' => true,
                    'by_reference' => false,
                ]),
            NumberField::new('averageRating', 'Note moyenne'),
            NumberField::new('tmdbRating', 'Note TMDB')->hideOnIndex(),
            NumberField::new('numVotes', 'Nombre de votes')->hideOnIndex(),
            AssociationField::new('listFilms', 'Listes associées')->hideOnForm(), // Relation avec ListFilm
        ];
    }
}
