<?php

namespace App\Command;

use App\Entity\Genre;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrate-genres',
    description: 'Migrates genre strings from film_filtre to genre and film_genre tables.',
)]
class MigrateGenresCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    public function __construct(EntityManagerInterface $entityManager, Connection $connection)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->connection = $connection;
    }

    protected function configure(): void
    {
        // Configuration si nécessaire
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting Genre Migration');

        // Désactiver les logs SQL de Doctrine pour ne pas polluer la sortie
        $this->connection->getConfiguration()->setSQLLogger(null);

        // 1. Extraire les genres uniques et peupler la table `genre`
        $io->section('Populating `genre` table');
        $stmtOldGenres = $this->connection->executeQuery("SELECT DISTINCT genres FROM film_filtre WHERE genres IS NOT NULL AND genres != ''");
        $allGenreStrings = $stmtOldGenres->fetchAllAssociative();

        $uniqueGenreNames = [];
        foreach ($allGenreStrings as $row) {
            $genresFromRow = explode(',', $row['genres']);
            foreach ($genresFromRow as $genreName) {
                $trimmedGenre = trim($genreName);
                if (!empty($trimmedGenre) && !in_array($trimmedGenre, $uniqueGenreNames, true)) {
                    $uniqueGenreNames[strtolower($trimmedGenre)] = $trimmedGenre; // Garder la casse originale
                }
            }
        }
        ksort($uniqueGenreNames); // Optionnel, pour un ordre d'insertion cohérent

        $genreRepo = $this->entityManager->getRepository(Genre::class);
        $genreMap = []; // Pour mapper nom de genre -> objet Genre ou ID

        foreach ($uniqueGenreNames as $originalGenreName) {
            $existingGenre = $genreRepo->findOneBy(['nom' => $originalGenreName]);
            if (!$existingGenre) {
                $newGenre = new Genre();
                $newGenre->setNom($originalGenreName);
                $this->entityManager->persist($newGenre);
                $io->writeln(sprintf('Creating genre: %s', $originalGenreName));
            } else {
                $io->writeln(sprintf('Genre already exists: %s', $originalGenreName));
                $newGenre = $existingGenre; // Utiliser l'existant pour la map
            }
            // Il faut flusher pour obtenir les IDs si on ne les a pas déjà
        }
        $this->entityManager->flush(); // Flush pour que les nouveaux genres aient des IDs

        // Re-fetch tous les genres pour avoir une map nom -> id propre
        $allPersistedGenres = $genreRepo->findAll();
        foreach ($allPersistedGenres as $persistedGenre) {
            $genreMap[strtolower(trim($persistedGenre->getNom()))] = $persistedGenre->getId();
        }
        $io->success('`genre` table populated/checked.');


        // 2. Peupler la table de liaison `film_genre`
        $io->section('Populating `film_genre` table');
        $batchSize = 100; // Pour gérer la mémoire avec beaucoup de films
        $i = 0;
        // Utiliser un QueryBuilder pour itérer sur les films peut être plus efficace en mémoire
        // Mais pour la simplicité, on utilise une requête directe pour l'instant
        $stmtFilms = $this->connection->executeQuery("SELECT tconst, genres FROM film_filtre WHERE genres IS NOT NULL AND genres != ''");

        // Utiliser des requêtes préparées pour l'insertion dans film_genre
        $insertFilmGenreSql = "INSERT IGNORE INTO film_genre (film_tconst, genre_id) VALUES (?, ?)";
        $insertStmt = $this->connection->prepare($insertFilmGenreSql);

        while ($film = $stmtFilms->fetchAssociative()) {
            $tconst = $film['tconst'];
            $genresInFilmString = $film['genres'];
            $genresNamesInFilm = explode(',', $genresInFilmString);

            foreach ($genresNamesInFilm as $genreName) {
                $trimmedGenreName = trim($genreName);
                if (!empty($trimmedGenreName)) {
                    $lookupKey = strtolower($trimmedGenreName);
                    if (isset($genreMap[$lookupKey])) {
                        $genreId = $genreMap[$lookupKey];
                        try {
                            $insertStmt->executeStatement([$tconst, $genreId]);
                            // $io->writeln(sprintf('Linking film %s to genre ID %s (%s)', $tconst, $genreId, $trimmedGenreName));
                        } catch (\Exception $e) {
                            $io->warning(sprintf('Could not link film %s to genre %s (ID %s): %s', $tconst, $trimmedGenreName, $genreId, $e->getMessage()));
                        }
                    } else {
                        $io->warning(sprintf('Genre name "%s" from film %s not found in genre map.', $trimmedGenreName, $tconst));
                    }
                }
            }
            if (($i++ % $batchSize) === 0) {
                $io->writeln(sprintf('Processed %d films for film_genre links...', $i));
            }
        }
        $io->success('`film_genre` table populated.');
        $io->success('Genre migration command finished.');
        return Command::SUCCESS;
    }
}
