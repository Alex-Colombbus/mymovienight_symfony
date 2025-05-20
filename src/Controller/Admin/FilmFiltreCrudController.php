<?php

namespace App\Controller\Admin;

use App\Entity\FilmFiltre;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\JsonField;
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
            CodeEditorField::new('importantCrew', 'Personnes importantes de la réalisation (metiers: nom)')->hideOnIndex(),
            TextField::new('actors', '3 acteurs principaux (acteur,acteur,acteur)')->hideOnIndex(),
            TextField::new('posterPath', 'Chemin de l\'affiche imdb')->hideOnIndex(),
            BooleanField::new('isAdult', 'Film pour adultes'),
            NumberField::new('startYear', 'Année de début'),
            NumberField::new('runtimeMinutes', 'Durée (minutes)'),
            TextField::new('genres', 'Genres (genre,genre,genre)')->hideOnIndex(),
            NumberField::new('averageRating', 'Note moyenne'),
            NumberField::new('tmdbRating', 'Note TMDB')->hideOnIndex(),
            NumberField::new('numVotes', 'Nombre de votes')->hideOnIndex(),
            AssociationField::new('listFilms', 'Listes associées')->hideOnForm(), // Relation avec ListFilm
        ];
    }
}
