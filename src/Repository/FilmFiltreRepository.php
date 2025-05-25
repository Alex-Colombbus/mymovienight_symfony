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


        $conn = $this->getEntityManager()->getConnection();
        $params = []; // Pour stocker les paramètres de la requête

        // Construction de la requête de base avec JOIN
        $sql = 'SELECT DISTINCT f.tconst FROM film_filtre f 
                INNER JOIN film_genre fg ON f.tconst = fg.film_tconst 
                INNER JOIN genre g ON fg.genre_id = g.id 
                WHERE 1=1';

        // Gestion des genres
        if (!empty($genresArray)) {
            $genreConditions = [];
            foreach ($genresArray as $key => $genre) {
                $paramName = 'genre' . $key;
                $genreConditions[] = 'g.name = :' . $paramName;
                $params[$paramName] = $genre;
            }
            $sql .= ' AND (' . implode(' OR ', $genreConditions) . ')';
        }

        // Ajout des autres filtres
        if ($minRating !== null && $minRating !== '') {
            $sql .= ' AND f.average_rating >= :minRating';
            $params['minRating'] = (float)$minRating;
        }
        if ($maxRating !== null && $maxRating !== '') {
            $sql .= ' AND f.average_rating <= :maxRating';
            $params['maxRating'] = (float)$maxRating;
        }
        if ($minYear !== null && $minYear !== '') {
            $sql .= ' AND f.start_year >= :minYear';
            $params['minYear'] = (int)$minYear;
        }
        if ($maxYear !== null && $maxYear !== '') {
            $sql .= ' AND f.start_year <= :maxYear';
            $params['maxYear'] = (int)$maxYear;
        }
        // Filtre sur le nombre de votes pour sélectionner les films populaires
        $sql .= ' AND f.num_votes > 10000';
        // Ordre aléatoire et limite
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
