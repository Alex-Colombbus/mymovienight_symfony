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
            return new JsonResponse(['status' => 'error', 'message' => 'CSRF token invalide.'], Response::HTTP_FORBIDDEN);
        }



        // S'assurer que l'utilisateur est authentifié
        if (!$user instanceof UserInterface) {
            return new JsonResponse(['status' => 'error', 'message' => 'Veuillez vous authentifier.'], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
        }

        // Validation de base pour tconst (le paramètre de route garantit qu'il existe, mais vérifier le contenu)
        if (empty($tconst)) {
            // Cela ne devrait pas arriver si la route est configurée correctement, mais vérification défensive
            error_log('SaveDataController REMOVE failed: tconst route parameter is empty.');
            return new JsonResponse(['status' => 'error', 'message' => 'Tconst est non valide ou manquant.'], Response::HTTP_BAD_REQUEST); // 400 Bad Request
        }


        try {
            // 1. Trouver la liste de l'utilisateur
            $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Ma liste']);



            // 2. Trouver l'entrée ListFilm spécifique à supprimer

            // Trouver d'abord l'entité FilmFiltre, car la propriété 'tconst' de ListFilm est une relation vers FilmFiltre
            $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

            if (!$filmFiltre) {
                // Entité FilmFiltre non trouvée pour ce tconst. Impossible de supprimer si le film principal n'existe pas.
                error_log('SaveDataController REMOVE info: FilmFiltre non trouvé pour ' . $tconst . '.');
                return new JsonResponse(['status' => 'info', 'message' => 'Film non trouvé dans la base de données.'], Response::HTTP_NOT_FOUND); // 404 Not Found
            }

            // Maintenant trouver le ListFilm qui lie la liste de cet utilisateur et cette entité film spécifique
            $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'liste' => $liste, // Utiliser l'entité Liste
                'tconst' => $filmFiltre, // Utiliser l'entité FilmFiltre
            ]);


            // 3. Si l'entrée ListFilm est trouvée, la supprimer
            if ($listFilm) {
                $entityManager->remove($listFilm); // Marquer pour suppression
                $entityManager->flush(); // Exécuter la suppression

                error_log('SaveDataController REMOVE success: Film retiré' . $tconst .  '.');
                return new JsonResponse(['status' => 'success', 'message' => 'Film retiré de votre liste de favoris.'], Response::HTTP_OK); // 200 OK or 204 No Content

            } else {
                // L'entrée ListFilm n'a pas été trouvée (le film n'était pas dans la liste)
                error_log('SaveDataController REMOVE info: ListFilm non trouvé ' . $tconst . '.');
                return new JsonResponse(['status' => 'info', 'message' => 'Film non trouvé dans votre liste.'], Response::HTTP_NOT_FOUND); // 404 Not Found
            }
        } catch (\Exception $e) {
            // Enregistrer les erreurs inattendues
            error_log('SaveDataController REMOVE unexpected error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Une erreur inattendue s\'est produite lors de la suppression du film : ' . $e->getMessage(),
                // Supprimer la trace en production
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
            return new JsonResponse(['status' => 'error', 'message' => 'Token CSRF invalide.'], Response::HTTP_FORBIDDEN);
        }



        // S'assurer que l'utilisateur est authentifié
        if (!$user instanceof UserInterface) {
            return new JsonResponse(['status' => 'error', 'message' => 'Veuillez vous connnecter.'], Response::HTTP_UNAUTHORIZED); // 401 Unauthorized
        }

        // Validation de base pour tconst (le paramètre de route garantit qu'il existe, mais vérifier le contenu)
        if (empty($tconst)) {
            // Cela ne devrait pas arriver si la route est configurée correctement, mais vérification défensive
            error_log('SaveDataController REMOVE failed: tconst route parameter is empty.');
            return new JsonResponse(['status' => 'error', 'message' => 'Tconst est non valide ou manquant.'], Response::HTTP_BAD_REQUEST); // 400 Bad Request
        }


        try {
            // 1. Trouver la liste de l'utilisateur
            $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Liste de refus']);



            // 2. Trouver l'entrée ListFilm spécifique à supprimer

            // Trouver d'abord l'entité FilmFiltre, car la propriété 'tconst' de ListFilm est une relation vers FilmFiltre
            $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

            if (!$filmFiltre) {
                // Entité FilmFiltre non trouvée pour ce tconst. Impossible de supprimer si le film principal n'existe pas.
                error_log('SaveDataController REMOVE info: FilmFiltre not found for tconst ' . $tconst . '.');
                return new JsonResponse(['status' => 'info', 'message' => 'Film non trouvé dans la base de données.'], Response::HTTP_NOT_FOUND); // 404 Not Found
            }

            // Maintenant trouver le ListFilm qui lie la liste de cet utilisateur et cette entité film spécifique
            $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'liste' => $liste, // Utiliser l'entité Liste
                'tconst' => $filmFiltre, // Utiliser l'entité FilmFiltre
            ]);


            // 3. Si l'entrée ListFilm est trouvée, la supprimer
            if ($listFilm) {
                $entityManager->remove($listFilm); // Marquer pour suppression
                $entityManager->flush(); // Exécuter la suppression

                error_log('SaveDataController REMOVE success: Film removed ' . $tconst . '.');
                return new JsonResponse(['status' => 'success', 'message' => 'Film retiré de votre liste de refus.'], Response::HTTP_OK); // 200 OK or 204 No Content

            } else {
                // L'entrée ListFilm n'a pas été trouvée (le film n'était pas dans la liste)
                error_log('SaveDataController REMOVE info: ListFilm not found for user ' . $tconst . '.');
                return new JsonResponse(['status' => 'info', 'message' => 'Le film n\'est pas dans votre liste de refus.'], Response::HTTP_NOT_FOUND); // 404 Not Found
            }
        } catch (\Exception $e) {
            // Enregistrer les erreurs inattendues
            error_log('SaveDataController REMOVE unexpected error: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Une erreur inattendue s\'est produite lors de la suppression du film : ' . $e->getMessage(),
                // Supprimer la trace en production
                'trace' => $e->getTraceAsString()
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500 Internal Server Error
        }
    }


    #[Route('/remove/data/getRemove/{tconst}', name: 'app_remove_data_get_favorite', methods: ['POST'])]
    public function removeFromFilmListe(string $tconst, EntityManagerInterface $entityManager, Request $request): Response
    {
        //Vérification du token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_favorite_' . $tconst, $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_liste_favorites');
        }
        $user = $this->getUser();
        // $user sera un objet UserInterface grâce à IsGranted. Si vous utilisez des méthodes User spécifiques, faites un cast ou vérifiez.

        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Ma liste']);

        if (!$liste) {
            $this->addFlash('error', 'Votre liste ("Ma liste") n\'a pas pu être trouvée.');
            return $this->redirectToRoute('app_liste_favorites'); // Ou une route plus appropriée
        }

        // 1. Trouver l'entité FilmFiltre en utilisant le tconst (qui est son ID)
        $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

        if (!$filmFiltre) {
            $this->addFlash('error', 'Le film que vous essayez de supprimer n\'existe pas dans la base de données.');
            return $this->redirectToRoute('app_liste_favorites');
        }

        // 2. Trouver l'entrée ListFilm en utilisant l'entité Liste et l'entité FilmFiltre
        $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
            'liste' => $liste,
            'tconst' => $filmFiltre // Utiliser l'instance de l'entité FilmFiltre ici
        ]);

        if ($listFilm) {
            $entityManager->remove($listFilm);
            $entityManager->flush();
            $this->addFlash('success', sprintf('Le film "%s" a été supprimé de votre liste.', $filmFiltre->getTitle()));
        } else {
            $this->addFlash('info', 'Ce film n\'a pas été trouvé dans votre liste ou a déjà été supprimé.');
        }

        return $this->redirectToRoute('app_liste_favorites');
    }



    #[Route('/remove/data/remove/data/getRemove_refusal/{tconst}', name: 'app_remove_data_get_refusal', methods: ['POST'])]
    public function removeFromFilmListeRefusal(string $tconst, EntityManagerInterface $entityManager, Request $request): Response
    {

        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_refusal_' . $tconst, $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_liste_refusals');
        }
        $user = $this->getUser();
        // $user sera un objet UserInterface grâce à IsGranted. Si vous utilisez des méthodes User spécifiques, faites un cast ou vérifiez.

        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Liste de refus']);

        if (!$liste) {
            $this->addFlash('error', 'Votre liste ("Liste de refus") n\'a pas pu être trouvée.');
            return $this->redirectToRoute('app_liste_refusals'); // Ou une route plus appropriée
        }

        // 1. Trouver l'entité FilmFiltre en utilisant le tconst (qui est son ID)
        $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

        if (!$filmFiltre) {
            $this->addFlash('error', 'Le film que vous essayez de supprimer n\'existe pas dans la base de données.');
            return $this->redirectToRoute('app_liste_refusals');
        }

        // 2. Trouver l'entrée ListFilm en utilisant l'entité Liste et l'entité FilmFiltre
        $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
            'liste' => $liste,
            'tconst' => $filmFiltre // Utiliser l'instance de l'entité FilmFiltre ici
        ]);

        if ($listFilm) {
            $entityManager->remove($listFilm);
            $entityManager->flush();
            $this->addFlash('success', sprintf('Le film "%s" à été supprimé de votre liste.', $filmFiltre->getTitle())); // En supposant que FilmFiltre a getTitle()
        } else {
            $this->addFlash('info', 'Ce film n\'a pas été trouvé dans votre liste ou a déjà été supprimé.');
        }

        return $this->redirectToRoute('app_liste_refusals');
    }


    #[Route('/remove/data/getRemove_history/{tconst}', name: 'app_remove_data_get_history', methods: ['POST'])]
    public function removeFromFilmListeHistory(string $tconst, EntityManagerInterface $entityManager, Request $request): Response
    {
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete_history_' . $tconst, $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_liste_history');
        }

        $user = $this->getUser();
        // $user sera un objet UserInterface grâce à IsGranted. Si vous utilisez des méthodes User spécifiques, faites un cast ou vérifiez.

        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Historique des films']);

        if (!$liste) {
            return $this->redirectToRoute('app_liste_history');
        }

        // 1. Trouver l'entité FilmFiltre en utilisant le tconst (qui est son ID)
        $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

        if (!$filmFiltre) {
            $this->addFlash('error', 'Le film que vous essayez de supprimer n\'existe pas dans la base de données.');
            return $this->redirectToRoute('app_liste_history');
        }

        // 2. Trouver l'entrée ListFilm en utilisant l'entité Liste et l'entité FilmFiltre
        $listFilm = $entityManager->getRepository(ListFilm::class)->findOneBy([
            'liste' => $liste,
            'tconst' => $filmFiltre // Utiliser l'instance de l'entité FilmFiltre ici
        ]);

        if ($listFilm) {
            $entityManager->remove($listFilm);
            $entityManager->flush();
            $this->addFlash('success', sprintf('Le film "%s" à été supprimé de votre liste.', $filmFiltre->getTitle())); // En supposant que FilmFiltre a getTitle()
        } else {
            $this->addFlash('info', 'Ce film n\'a pas été trouvé dans votre liste ou a déjà été supprimé.');
        }

        return $this->redirectToRoute('app_liste_history');
    }
}
