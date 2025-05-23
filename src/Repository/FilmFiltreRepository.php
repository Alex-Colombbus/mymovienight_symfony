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
        // Construction de la requête de base
        $sql = 'SELECT f.tconst FROM film_filtre f WHERE 1=1'; // 1=1 est une astuce pour simplifier l'ajout de conditions

        // Gestion des genres: $genresArray est non vide à ce stade (garanti par HomeController)
        $genreConditions = [];
        foreach ($genresArray as $key => $genre) {

            $paramName = 'genre' . $key;
            // Ajoute une condition LIKE pour ce genre.
            // Utilisation de CONCAT(',', f.genres, ',') et '%,genre,%'
            // pour une correspondance exacte du mot/genre.
            // Les genres dans les films sont stockés sour forme : "Action,Aventure"
            //pour que la recherche fonctionne, on concatène une virgule avant et après.
            // ce qui donne : ",Action,Aventure," et non permet de chercher un genre en utilisant LIKE : "%,Action,%" alors qu'avant on aurait pas pu le trouver.
            $genreConditions[] = "CONCAT(',', f.genres, ',') LIKE :" . $paramName;
            $params[$paramName] = '%,' . $genre . ',%';
        }

        $sql .= ' AND (' . implode(' OR ', $genreConditions) . ')';

        // Ajout des autres filtres et de leurs paramètres
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
        // Permet de selectionner les films ayant plus de 10 000 votes
        $sql .= ' AND f.num_votes > 10000';

        // On tire 40 films au hasard
        $sql .= ' ORDER BY RAND() LIMIT 40';

        $stmt = $conn->executeQuery($sql, $params);
        // Récupération des résultats sous forme de tableau associatif
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
