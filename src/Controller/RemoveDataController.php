<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Liste;
use App\Entity\ListFilm;
use App\Entity\FilmFiltre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


final class RemoveDataController extends AbstractController
{

    #[Route('/remove/data/', name: 'app_remove_data', methods: ['POST'])]
    public function removeFilm(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        $tconst = $data['tconst'];

        // Récupérer le token CSRF depuis l'en-tête de la requête
        $submittedToken = $request->headers->get('X-CSRF-TOKEN');

        // Valider le token CSRF
        // L'identifiant 'save_favorite' doit correspondre à celui utilisé dans Twig
        if (!$this->isCsrfTokenValid('remove_favorite', $submittedToken)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }



        // Ensure user is authenticated
        if (!$user instanceof UserInterface) {
            return new JsonResponse(['status' => 'error', 'message' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
        }

        // Basic validation for tconst (route parameter guarantees it exists, but check content)
        if (empty($tconst)) {
            // This shouldn't happen if the route is configured correctly, but defensive check
            error_log('SaveDataController REMOVE failed: tconst route parameter is empty.');
            return new JsonResponse(['status' => 'error', 'message' => 'Missing film identifier (tconst) for deletion.'], Response::HTTP_BAD_REQUEST); // 400 Bad Request
        }


        try {
            // 1. Find the user's list
            $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Ma liste']);



            // 2. Find the specific ListFilm entry to remove

            // Find the FilmFiltre entity first, as ListFilm's 'tconst' property is a relation to FilmFiltre
            $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

            if (!$filmFiltre) {
                // FilmFiltre entity not found for this tconst. Cannot remove if the master film doesn't exist.
                error_log('SaveDataController REMOVE info: FilmFiltre not found for tconst ' . $tconst . ' for user ' . $user->getId());
                return new JsonResponse(['status' => 'info', 'message' => 'Film not found in master database.'], Response::HTTP_NOT_FOUND); // 404 Not Found
            }

            // Now find the ListFilm linking this user's list and this specific film entity
            $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'liste' => $liste, // Use the Liste entity
                'tconst' => $filmFiltre, // Use the FilmFiltre entity
            ]);


            // 3. If the ListFilm entry is found, remove it
            if ($listFilm) {
                $entityManager->remove($listFilm); // Mark for removal
                $entityManager->flush(); // Execute the removal

                error_log('SaveDataController REMOVE success: Film removed for user ' . $user->getId() . ' tconst ' . $tconst);
                return new JsonResponse(['status' => 'success', 'message' => 'Film removed from list!'], Response::HTTP_OK); // 200 OK or 204 No Content

            } else {
                // The ListFilm entry was not found (film wasn't in the list)
                error_log('SaveDataController REMOVE info: ListFilm not found for user ' . $user->getId() . ' tconst ' . $tconst . ' (was not in list?)');
                return new JsonResponse(['status' => 'info', 'message' => 'Film was not found in your list.'], Response::HTTP_NOT_FOUND); // 404 Not Found
            }
        } catch (\Exception $e) {
            // Log unexpected errors
            error_log('SaveDataController REMOVE unexpected error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return new JsonResponse([
                'status' => 'error',
                'message' => 'An unexpected error occurred while trying to remove the film: ' . $e->getMessage(),
                // Remove trace in production
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500 Internal Server Error
        }
    }


    #[Route('/remove/data_refusal/', name: 'app_remove_data_refusal', methods: ['POST'])]
    public function removeFilmRefusal(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        $tconst = $data['tconst'];

        // Récupérer le token CSRF depuis l'en-tête de la requête
        $submittedToken = $request->headers->get('X-CSRF-TOKEN');

        // Valider le token CSRF
        // L'identifiant 'save_favorite' doit correspondre à celui utilisé dans Twig
        if (!$this->isCsrfTokenValid('remove_refusal', $submittedToken)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
        }



        // Ensure user is authenticated
        if (!$user instanceof UserInterface) {
            return new JsonResponse(['status' => 'error', 'message' => 'Authentication required.'], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
        }

        // Basic validation for tconst (route parameter guarantees it exists, but check content)
        if (empty($tconst)) {
            // This shouldn't happen if the route is configured correctly, but defensive check
            error_log('SaveDataController REMOVE failed: tconst route parameter is empty.');
            return new JsonResponse(['status' => 'error', 'message' => 'Missing film identifier (tconst) for deletion.'], Response::HTTP_BAD_REQUEST); // 400 Bad Request
        }


        try {
            // 1. Find the user's list
            $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Liste de refus']);



            // 2. Find the specific ListFilm entry to remove

            // Find the FilmFiltre entity first, as ListFilm's 'tconst' property is a relation to FilmFiltre
            $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

            if (!$filmFiltre) {
                // FilmFiltre entity not found for this tconst. Cannot remove if the master film doesn't exist.
                error_log('SaveDataController REMOVE info: FilmFiltre not found for tconst ' . $tconst . ' for user ' . $user->getId());
                return new JsonResponse(['status' => 'info', 'message' => 'Film not found in master database.'], Response::HTTP_NOT_FOUND); // 404 Not Found
            }

            // Now find the ListFilm linking this user's list and this specific film entity
            $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'liste' => $liste, // Use the Liste entity
                'tconst' => $filmFiltre, // Use the FilmFiltre entity
            ]);


            // 3. If the ListFilm entry is found, remove it
            if ($listFilm) {
                $entityManager->remove($listFilm); // Mark for removal
                $entityManager->flush(); // Execute the removal

                error_log('SaveDataController REMOVE success: Film removed for user ' . $user->getId() . ' tconst ' . $tconst);
                return new JsonResponse(['status' => 'success', 'message' => 'Film removed from list!'], Response::HTTP_OK); // 200 OK or 204 No Content

            } else {
                // The ListFilm entry was not found (film wasn't in the list)
                error_log('SaveDataController REMOVE info: ListFilm not found for user ' . $user->getId() . ' tconst ' . $tconst . ' (was not in list?)');
                return new JsonResponse(['status' => 'info', 'message' => 'Film was not found in your list.'], Response::HTTP_NOT_FOUND); // 404 Not Found
            }
        } catch (\Exception $e) {
            // Log unexpected errors
            error_log('SaveDataController REMOVE unexpected error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return new JsonResponse([
                'status' => 'error',
                'message' => 'An unexpected error occurred while trying to remove the film: ' . $e->getMessage(),
                // Remove trace in production
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500 Internal Server Error
        }
    }
    // --- END NEW ACTION ---

    #[Route('/remove/data/getRemove/{tconst}', name: 'app_remove_data_get_favorite', methods: ['GET'])]
    public function removeFromFilmListe(string $tconst, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // $user will be a UserInterface object due to IsGranted. If using specific User methods, type cast or check.

        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Ma liste']);

        if (!$liste) {
            $this->addFlash('error', 'Your list ("Ma liste") could not be found.');
            return $this->redirectToRoute('app_liste_favorites'); // Or a more appropriate route
        }

        // 1. Find the FilmFiltre entity using the tconst (which is its ID)
        $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

        if (!$filmFiltre) {
            $this->addFlash('error', 'The film you are trying to remove does not exist in the database.');
            return $this->redirectToRoute('app_liste_favorites');
        }

        // 2. Find the ListFilm entry using the Liste entity and the FilmFiltre entity
        $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
            'liste' => $liste,
            'tconst' => $filmFiltre // Use the FilmFiltre entity instance here
        ]);

        if ($listFilm) {
            $entityManager->remove($listFilm);
            $entityManager->flush();
            $this->addFlash('success', sprintf('Film "%s" removed from your list.', $filmFiltre->getTitle())); // Assuming FilmFiltre has getTitle()
        } else {
            $this->addFlash('info', 'This film was not found in your list or was already removed.');
        }

        return $this->redirectToRoute('app_liste_favorites');
    }



    #[Route('/remove/data/remove/data/getRemove_refusal/{tconst}', name: 'app_remove_data_get_refusal', methods: ['GET'])]
    public function removeFromFilmListeRefusal(string $tconst, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // $user will be a UserInterface object due to IsGranted. If using specific User methods, type cast or check.

        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Liste de refus']);

        if (!$liste) {
            $this->addFlash('error', 'Votre liste ("Liste de refus") n\'a pas pu être trouvée.');
            return $this->redirectToRoute('app_liste_refusals'); // Or a more appropriate route
        }

        // 1. Find the FilmFiltre entity using the tconst (which is its ID)
        $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

        if (!$filmFiltre) {
            $this->addFlash('error', 'The film you are trying to remove does not exist in the database.');
            return $this->redirectToRoute('app_liste_refusals');
        }

        // 2. Find the ListFilm entry using the Liste entity and the FilmFiltre entity
        $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
            'liste' => $liste,
            'tconst' => $filmFiltre // Use the FilmFiltre entity instance here
        ]);

        if ($listFilm) {
            $entityManager->remove($listFilm);
            $entityManager->flush();
            $this->addFlash('success', sprintf('Film "%s" removed from your list.', $filmFiltre->getTitle())); // Assuming FilmFiltre has getTitle()
        } else {
            $this->addFlash('info', 'This film was not found in your list or was already removed.');
        }

        return $this->redirectToRoute('app_liste_refusals');
    }


    #[Route('/remove/data/getRemove_history/{tconst}', name: 'app_remove_data_get_history', methods: ['GET'])]
    public function removeFromFilmListeHistory(string $tconst, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        // $user will be a UserInterface object due to IsGranted. If using specific User methods, type cast or check.

        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Historique des films']);

        if (!$liste) {
            return $this->redirectToRoute('app_liste_history');
        }

        // 1. Find the FilmFiltre entity using the tconst (which is its ID)
        $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

        if (!$filmFiltre) {
            $this->addFlash('error', 'Le film que vous essayez de supprimer n\'existe pas dans la base de données.');
            return $this->redirectToRoute('app_liste_history');
        }

        // 2. Find the ListFilm entry using the Liste entity and the FilmFiltre entity
        $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
            'liste' => $liste,
            'tconst' => $filmFiltre // Use the FilmFiltre entity instance here
        ]);

        if ($listFilm) {
            $entityManager->remove($listFilm);
            $entityManager->flush();
            $this->addFlash('success', sprintf('Le film "%s" à été supprimé de votre liste.', $filmFiltre->getTitle())); // Assuming FilmFiltre has getTitle()
        } else {
            $this->addFlash('info', 'Ce film n\'a pas été trouvé dans votre liste ou a déjà été supprimé.');
        }

        return $this->redirectToRoute('app_liste_history');
    }
}
