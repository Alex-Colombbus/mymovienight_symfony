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
            ->setSearchFields(['title', 'tconst']) // Propriétés sur lesquelles chercher
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des films') // Optionnel: change le titre de la page d'index
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un film') // Change le titre de la page de création
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le film') // Optionnel: change le titre de la page d'édition
            ->setEntityLabelInSingular('Film') // Utilisé pour les boutons comme "Créer Film"
            ->setEntityLabelInPlural('Films')
            ->setHelp(Crud::PAGE_INDEX, 'Utilisez la barre de recherche pour trouver des films par titre ou identifiant Imdb.');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('tconst', 'Identifiant Imdb'), // Champ ID unique
            TextField::new('title', 'Titre'),
            TextField::new('titleType', 'Type de titre'),
            TextareaField::new('synopsis', 'Synopsis'),
            CodeEditorField::new('importantCrew', 'Équipe technique (Nom: Rôle(s))')
                ->setLanguage('javascript') // Use 'javascript' for JSON-like highlighting

                ->setHelp('Éditez le JSON directement. Exemple: {"Nom_Prenom": "Role1, Role2", "Autre_Nom": "Role"}')
                ->setFormType(JsonCodeEditorType::class)
                ->formatValue(function ($value, $entity) {
                    if (is_array($value)) {
                        // Convert the array to a JSON string for display
                        // JSON_UNESCAPED_SLASHES and JSON_UNESCAPED_UNICODE are good for readability
                        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    }
                    // If it's already a string (e.g. null or actual JSON string), return as is
                    return $value;
                }),
            TextField::new('actors', '3 acteurs principaux (acteur,acteur,acteur)'),
            TextField::new('posterPath', 'Chemin de l\'affiche imdb'),
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
            NumberField::new('tmdbRating', 'Note TMDB'),
            NumberField::new('numVotes', 'Nombre de votes'),
            AssociationField::new('listFilms', 'Listes associées')->hideOnForm(), // Relation avec ListFilm
        ];
    }
}
