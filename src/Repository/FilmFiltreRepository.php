<?php

namespace App\Repository;

use App\Entity\FilmFiltre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FilmFiltre>
 */
class FilmFiltreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FilmFiltre::class);
    }

    //    /*
    //     * @return FilmFiltre[] Returns an array of FilmFiltre objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FilmFiltre
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function findMoviesWithGenres(array $genresArray, ?float $minRating, ?float $maxRating, ?int $minYear, ?int $maxYear): array
    {

        // return $stmt->fetchAllAssociative();
        $conn = $this->getEntityManager()->getConnection();
        $params = []; // Pour stocker les paramètres de la requête

        // Construction de la requête de base
        $sql = 'SELECT f.* FROM film_filtre f WHERE 1=1'; // 1=1 est une astuce pour simplifier l'ajout de conditions

        // Gestion des genres: $genresArray est non vide à ce stade (garanti par HomeController)
        $genreConditions = [];
        foreach ($genresArray as $key => $genre) {


            // Crée un nom de paramètre unique pour chaque genre (ex: :genre0, :genre1)
            $paramName = 'genre' . $key;

            // Ajoute une condition LIKE pour ce genre.
            // Utilisation de CONCAT(',', f.genres, ',') et '%,genre,%'
            // pour une correspondance exacte du mot/genre.
            $genreConditions[] = "CONCAT(',', f.genres, ',') LIKE :" . $paramName;
            $params[$paramName] = '%,' . $genre . ',%';
        }

        // S'il y a des conditions de genre valides (après avoir ignoré les genres vides du tableau)
        // on les ajoute à la requête.
        if (!empty($genreConditions)) {
            // Combine toutes les conditions de genre avec OR
            // ex: AND ((CONCAT(',', f.genres, ',') LIKE :genre0) OR (CONCAT(',', f.genres, ',') LIKE :genre1))
            $sql .= ' AND (' . implode(' OR ', $genreConditions) . ')';
        }
        // Note: Si $genresArray contenait uniquement des chaînes vides (ex: ['', '  ']),
        // alors $genreConditions serait vide, et aucun filtre de genre ne serait appliqué.
        // Si la logique métier exigeait qu'au moins un genre VALIDE soit présent
        // si $genresArray est fourni, il faudrait ajouter une gestion d'erreur ici (ex: retourner []).
        // Pour l'instant, on suppose que ne pas filtrer est acceptable si aucun genre valide n'est trouvé.

        // Ajout des autres filtres et de leurs paramètres
        if ($minRating !== null && $minRating !== '') { // Ajout d'une vérification pour chaîne vide
            $sql .= ' AND f.average_rating >= :minRating';
            $params['minRating'] = (float)$minRating;
        }
        if ($maxRating !== null && $maxRating !== '') { // Ajout d'une vérification pour chaîne vide
            $sql .= ' AND f.average_rating <= :maxRating';
            $params['maxRating'] = (float)$maxRating;
        }
        if ($minYear !== null && $minYear !== '') { // Ajout d'une vérification pour chaîne vide
            $sql .= ' AND f.start_year >= :minYear';
            $params['minYear'] = (int)$minYear;
        }
        if ($maxYear !== null && $maxYear !== '') { // Ajout d'une vérification pour chaîne vide
            $sql .= ' AND f.start_year <= :maxYear';
            $params['maxYear'] = (int)$maxYear;
        }

        $sql .= ' AND f.num_votes > 10000';

        // Attention: ORDER BY RAND() peut être très lent sur de grandes tables.
        $sql .= ' ORDER BY RAND() LIMIT 40';

        $stmt = $conn->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }

    public function findMoviesAllGenres(?float $minRating, ?float $maxRating, ?int $minYear, ?int $maxYear): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $params = []; // Pour stocker les paramètres de la requête

        // Construction de la requête de base
        $sql = 'SELECT f.tconst FROM film_filtre f WHERE 1=1'; // 1=1 est une astuce pour simplifier l'ajout de conditions


        if ($minRating !== null && $minRating !== '') { // Ajout d'une vérification pour chaîne vide
            $sql .= ' AND f.average_rating >= :minRating';
            $params['minRating'] = (float)$minRating;
        }
        if ($maxRating !== null && $maxRating !== '') { // Ajout d'une vérification pour chaîne vide
            $sql .= ' AND f.average_rating <= :maxRating';
            $params['maxRating'] = (float)$maxRating;
        }
        if ($minYear !== null && $minYear !== '') { // Ajout d'une vérification pour chaîne vide
            $sql .= ' AND f.start_year >= :minYear';
            $params['minYear'] = (int)$minYear;
        }
        if ($maxYear !== null && $maxYear !== '') { // Ajout d'une vérification pour chaîne vide
            $sql .= ' AND f.start_year <= :maxYear';
            $params['maxYear'] = (int)$maxYear;
        }

        $sql .= ' AND f.num_votes > 10000';


        $sql .= ' ORDER BY RAND() LIMIT 40';

        $stmt = $conn->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }
}
