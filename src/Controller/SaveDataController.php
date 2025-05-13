<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Entity\ListFilm;
use App\Entity\FilmFiltre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SaveDataController extends AbstractController
{

    #[Route('/save/data', name: 'app_save_data')]
    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        $content = $request->getContent(); // Get the raw request body

        // *** Add logging here to see the raw content ***
        error_log('SaveDataController: Raw Content Received: ' . $content);

        // Ensure the user is authenticated
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED);
        }

        // Basic validation: check if required data (like tconst) is present
        if (!isset($data['tconst']) || empty($data['tconst'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid or missing "tconst" in request data.'], Response::HTTP_BAD_REQUEST);
        }

        // --- Logique de sauvegarde ---
        try {
            // 1. Find the FilmFiltre entity. Handle if not found.
            $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($data['tconst']);

            if (!$filmFiltre) {
                // If the film doesn't exist in your FilmFiltre table, you can't add it to a list.
                // Depending on your app, you might create it here based on $data
                // or return an error. Let's return an error for now.
                return new JsonResponse(['status' => 'error', 'message' => 'Film not found in the database to add to list.'], Response::HTTP_NOT_FOUND);
            }

            if (isset($data['title']) && $filmFiltre->getTitle() === null) {
                // Ensure the incoming data for title is a non-empty string
                if ($data['title'] !== '') { // Use strict non-empty check
                    $filmFiltre->setTitle($data['title']);
                }
            }

            // 2. Update FilmFiltre details if necessary (handle null checks and type conversions)
            if (isset($data['synopsis']) && $filmFiltre->getSynopsis() === null) {
                $filmFiltre->setSynopsis($data['synopsis']);
            }

            if (isset($data['posterPath']) && $filmFiltre->getPosterPath() === null) {
                $filmFiltre->setPosterPath($data['posterPath']);
            }

            // Assuming 'startYear' in entity and 'releaseDate' or 'startYear' in data.
            // Your entity mapping for startYear is INT, but frontend sends string like "2005".
            // Need to cast to int.
            if (isset($data['releaseDate']) && $filmFiltre->getStartYear() === null) {
                // Assuming releaseDate is 'YYYY-MM-DD', extract year and cast
                try {
                    $year = (new \DateTimeImmutable($data['releaseDate']))->format('Y');
                    $filmFiltre->setStartYear((int) $year);
                } catch (\Exception $e) {
                    // Handle potential date parsing errors if format isn't reliable
                    // Log or ignore, depending on strictness
                }
            } else if (isset($data['startYear']) && $filmFiltre->getStartYear() === null) {
                // If startYear is already an integer in data
                if (is_numeric($data['startYear'])) {
                    $filmFiltre->setStartYear((int) $data['startYear']);
                }
            }


            if (isset($data['imdbRating']) && $filmFiltre->getAverageRating() === null) {
                // Ensure the data type matches the entity's DECIMAL(3,1) property
                $filmFiltre->setAverageRating((string) $data['imdbRating']); // Cast to string for DECIMAL
            }

            if (isset($data['tmdbRating']) && $filmFiltre->getTmdbRating() === null) {
                if (is_numeric($data['tmdbRating'])) { // tmdbRating is float in entity
                    $filmFiltre->setTmdbRating((float) $data['tmdbRating']);
                }
            }

            if (isset($data['genres']) && $filmFiltre->getGenres() === null) {
                if (is_string($data['genres'])) { // Genres is string in entity
                    $filmFiltre->setGenres($data['genres']);
                }
            }

            // *** FIX FOR ACTORS TYPE ERROR ***
            if (isset($data['actors']) && $filmFiltre->getActors() === null) {
                // Convert array of actor names to a string
                if (is_array($data['actors'])) {
                    $filmFiltre->setActors(implode(', ', $data['actors']));
                } else if (is_string($data['actors'])) {
                    // If by chance it's already a string
                    $filmFiltre->setActors($data['actors']);
                }
            }
            // *** END FIX ***

            // importantCrew is defined as array in entity and is array in data - This is OK
            if (isset($data['importantCrew']) && $filmFiltre->getImportantCrew() === null) {
                if (is_array($data['importantCrew'])) {
                    $filmFiltre->setImportantCrew($data['importantCrew']);
                }
            }

            // No need to persist FilmFiltre here, Doctrine manages it after find()

            // 3. Find the user's list or create it if it doesn't exist
            $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user]); // Use the $user entity

            // CONDITIONAL: Create the list ONLY if it doesn't exist
            if (!$liste) {
                $liste = new Liste();
                $liste->setUser($user); // Use the correct setter name
                $liste->setNameListe('My Watchlist'); // Set a default name
                $entityManager->persist($liste); // Persist the new list
                // No flush yet
            }

            // 4. Check if the FilmFiltre is already linked to this Liste in ListFilm
            // *** FIX FOR findOneBy CRITERIA ***
            $existingListFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'tconst' => $filmFiltre, // Pass the FilmFiltre ENTITY OBJECT
                'liste' => $liste,       // Pass the Liste ENTITY OBJECT
            ]);
            // *** END FIX ***

            // 5. If the link already exists, return a conflict response
            if ($existingListFilm) {
                return new JsonResponse(['status' => 'info', 'message' => 'Film is already in your list!'], Response::HTTP_CONFLICT); // 409 Conflict
            }

            // 6. If the link doesn't exist, create a new ListFilm entry
            $listFilm = new ListFilm();
            // Use the custom setter which expects entity objects
            $listFilm->setListFilmInfo($filmFiltre, $liste);

            // Or use standard setters if you prefer:
            // $listFilm->setTconst($filmFiltre); // setTconst expects FilmFiltre entity
            // $listFilm->setListeId($liste);     // setListeId expects Liste entity


            // 7. Persist the new ListFilm entity
            $entityManager->persist($listFilm);

            // 8. Flush all pending changes (FilmFiltre updates, new Liste if created, new ListFilm)
            $entityManager->flush();

            // 9. Return success response
            return new JsonResponse(['status' => 'success', 'message' => 'Film added to list!'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Log the error for debugging in development
            // In production, consider logging to a file or service and returning a generic error message
            error_log('Error in SaveDataController: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            // 10. Return a JSON error response
            return new JsonResponse([
                'status' => 'error',
                // Provide the exception message for debugging in development.
                // In production, return a more generic message like 'An internal error occurred.'
                'message' => 'Une erreur est survenue lors de la sauvegarde: ' . $e->getMessage(),
                // WARNING: Exposing the full trace in a production API response is a security risk.
                // Remove 'trace' => $e->getTraceAsString() for production.
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
