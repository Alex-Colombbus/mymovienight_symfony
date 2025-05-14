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

    #[Route('/remove/data/getRemove{tconst}', name: 'app_remove_data_get', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Ensures user is authenticated
    public function removeFromFilmListe(string $tconst, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // $user will be a UserInterface object due to IsGranted. If using specific User methods, type cast or check.

        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Ma liste']);

        if (!$liste) {
            $this->addFlash('error', 'Your list ("Ma liste") could not be found.');
            return $this->redirectToRoute('app_liste'); // Or a more appropriate route
        }

        // 1. Find the FilmFiltre entity using the tconst (which is its ID)
        $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

        if (!$filmFiltre) {
            $this->addFlash('error', 'The film you are trying to remove does not exist in the database.');
            return $this->redirectToRoute('app_liste');
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

        return $this->redirectToRoute('app_liste');
    }
}
