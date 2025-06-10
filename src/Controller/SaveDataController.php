<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Entity\Genre;
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

    /**
     * Gère l'ajout d'un film à la liste des favoris de l'utilisateur.
     * Récupère les données du film via une requête POST JSON, valide le token CSRF,
     * met à jour les informations du film si nécessaire, et l'ajoute à la liste "Ma liste".
     * Crée les listes "Ma liste" et "Liste de refus" pour l'utilisateur si elles n'existent pas.
     * Vérifie si le film est déjà dans l'une des listes avant de l'ajouter.
     */
    #[Route('/save/data/favorite', name: 'app_save_data')]
    public function saveFavorite(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Décodage des données JSON de la requête
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();
        // Récupération du token CSRF depuis l'en-tête de la requête
        $submittedToken = $request->headers->get('X-CSRF-TOKEN');
        // Valider le token CSRF
        // L'identifiant 'save_favorite' doit correspondre à celui utilisé dans Twig
        if (!$this->isCsrfTokenValid('save_favorite', $submittedToken)) {
            return new JsonResponse(['status' => 'error', 'message' => 'CSRF token invalide.'], Response::HTTP_FORBIDDEN);
        }
        $content = $request->getContent(); // Récupérer le corps brut de la requête
        error_log('SaveDataController: Raw Content Received: ' . $content);

        // S'assurer que l'utilisateur est authentifié
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'Veuillez vous connnecter.'], Response::HTTP_UNAUTHORIZED);
        }

        // Validation de base : vérifier si les données requises (comme tconst) sont présentes
        if (!isset($data['tconst']) || empty($data['tconst'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Tconst est non valide ou manquant.'], Response::HTTP_BAD_REQUEST); // 400 Requête incorrecte
        }

        // --- Logique de sauvegarde ---
        try {

            // 1. Rechercher l'enregistrement du film dans la base de donnée.
            $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($data['tconst']);

            if (!$filmFiltre) {
                // Si le film n'existe pas dans la table FilmFiltre, il ne peut pas être ajouté à une liste.
                // Selon l'application, on pourrait le créer ici à partir de $data ou retourner une erreur.
                return new JsonResponse(['status' => 'error', 'message' => 'Film non trouvé dans la base de données.'], Response::HTTP_NOT_FOUND);
            }

            // Mise à jour des détails de FilmFiltre si nécessaire (gestion des vérifications null et conversions de type)
            // Ces blocs vérifient si une propriété du film est nulle dans la base de données
            // et si des données correspondantes sont fournies dans la requête, puis mettent à jour l'entité.
            if (isset($data['title']) && $filmFiltre->getTitle() === null) {
                // S'assurer que les données entrantes pour le titre sont une chaîne non vide
                if ($data['title'] !== '') { // Utiliser une vérification stricte de non-vide
                    $filmFiltre->setTitle($data['title']);
                } else {
                    return new JsonResponse(['status' => 'error', 'message' => 'Le titre du film est manquant ou invalide.'], Response::HTTP_BAD_REQUEST);
                }
            }

            // 2. Mettre à jour les détails de FilmFiltre si nécessaire (gérer les vérifications null et les conversions de type)
            if (isset($data['synopsis']) && $filmFiltre->getSynopsis() === null) {
                $filmFiltre->setSynopsis($data['synopsis']);
            }

            if (isset($data['posterPath']) && $filmFiltre->getPosterPath() === null) {
                $filmFiltre->setPosterPath($data['posterPath']);
            }

            // Gestion de la date de sortie (startYear)
            if (isset($data['releaseDate']) && $filmFiltre->getStartYear() === null) {
                // releaseDate est 'YYYY-MM-DD', extraire l'année et convertir
                try {
                    $year = (new \DateTimeImmutable($data['releaseDate']))->format('Y');
                    $filmFiltre->setStartYear((int) $year);
                } catch (\Exception $e) {
                    // En cas d'erreur de formatage, on peut choisir de ne pas mettre à jour ou de gérer l'erreur
                    return new JsonResponse(['status' => 'error', 'message' => 'Date de sortie invalide.'], Response::HTTP_BAD_REQUEST);
                }
            } else if (isset($data['startYear']) && $filmFiltre->getStartYear() === null) {
                // Si startYear est déjà un entier dans les données
                if (is_numeric($data['startYear'])) {
                    $filmFiltre->setStartYear((int) $data['startYear']);
                }
            }


            if (isset($data['imdbRating']) && $filmFiltre->getAverageRating() === null) {
                // S'assurer que le type de données correspond à la propriété DECIMAL(3,1) de l'entité
                $filmFiltre->setAverageRating((string) $data['imdbRating']); // Convertir en chaîne pour DECIMAL
            }

            if (isset($data['tmdbRating']) && $filmFiltre->getTmdbRating() === null) {
                if (is_numeric($data['tmdbRating'])) { // tmdbRating est float dans l'entité
                    $filmFiltre->setTmdbRating((float) $data['tmdbRating']);
                }
            }



            // Correction pour le type des acteurs : conversion d'un tableau en chaîne de caractères
            if (isset($data['actors']) && $filmFiltre->getActors() === null) {
                // Convertir un tableau de noms d'acteurs en chaîne
                if (is_array($data['actors'])) {
                    $filmFiltre->setActors(implode(', ', $data['actors']));
                } else if (is_string($data['actors'])) {
                    // Si par hasard c'est déjà une chaîne
                    $filmFiltre->setActors($data['actors']);
                }
            }

            // importantCrew est défini comme tableau dans l'entité
            if (isset($data['importantCrew']) && $filmFiltre->getImportantCrew() === null) {
                if (is_array($data['importantCrew'])) {
                    $filmFiltre->setImportantCrew($data['importantCrew']);
                }
            }


            // 3. Rechercher la liste de l'utilisateur ou la créer si elle n'existe pas
            // Recherche ou création de la "Liste de refus"
            $listeRefusal = $entityManager->getRepository(Liste::class)->findOneBy([
                'user' => $user,
                'name_liste' => 'Liste de refus'
            ]);

            // Recherche ou création de "Ma liste" (liste principale des favoris)
            $listeMain = $entityManager->getRepository(Liste::class)->findOneBy([
                'user' => $user,
                'name_liste' => 'Ma liste'
            ]);

            // Conditionnel : Créer la liste UNIQUEMENT si elle n'existe pas
            if (!$listeRefusal) {
                $liste = new Liste();
                $liste->setUser($user);
                $liste->setNameListe('Liste de refus');
                $entityManager->persist($liste);
                $entityManager->flush(); // Persister la nouvelle liste immédiatement pour la récupérer ensuite
                $listeRefusal = $entityManager->getRepository(Liste::class)->findOneBy([
                    'user' => $user,
                    'name_liste' => 'Liste de refus'
                ]);
            }

            if (!$listeMain) {
                $listeMain = new Liste();
                $listeMain->setUser($user);
                $listeMain->setNameListe('Ma liste');
                $entityManager->persist($listeMain);
                $entityManager->flush(); // Persister la nouvelle liste immédiatement
                $listeMain = $entityManager->getRepository(Liste::class)->findOneBy([
                    'user' => $user,
                    'name_liste' => 'Ma liste'
                ]);
            }


            // 4. Vérifier si le FilmFiltre est déjà lié à cette Liste dans ListFilm
            // Vérification pour "Ma liste"
            $existingListFilmMain = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'tconst' => $filmFiltre, // Passer l'OBJET ENTITÉ FilmFiltre
                'liste' => $listeMain,       // Passer l'OBJET ENTITÉ Liste
            ]);

            // Vérification pour "Liste de refus"
            $existingListFilmRefusal = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'tconst' => $filmFiltre, // Passer l'OBJET ENTITÉ FilmFiltre
                'liste' => $listeRefusal,       // Passer l'OBJET ENTITÉ Liste
            ]);


            // 5. Si le lien existe déjà, retourner une réponse de conflit
            if ($existingListFilmMain !== null) {
                return new JsonResponse(['status' => 'info', 'message' => 'Film est déjà dans votre liste de favoris!'], Response::HTTP_CONFLICT);
            }

            if ($existingListFilmRefusal !== null) {
                return new JsonResponse(['status' => 'info', 'message' => 'Film est dans votre liste de refus, retirez le si vous voulez l\'ajouter à vos favoris.'], Response::HTTP_CONFLICT);
            }

            // 6. Si le lien n'existe pas, créer une nouvelle entrée ListFilm pour "Ma liste"
            $listFilm = new ListFilm();
            $listFilm->setListFilmInfo($filmFiltre, $listeMain); // Utilise le setter personnalisé

            // 7. Persister la nouvelle entité ListFilm
            $entityManager->persist($listFilm);

            // 8. Flusher tous les changements en attente (mises à jour de FilmFiltre, nouvelle Liste si créée, nouveau ListFilm)
            $entityManager->flush();

            // 9. Retourner une réponse de succès
            return new JsonResponse(['status' => 'success', 'message' => 'Film ajouté dans votre liste de favoris!'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Enregistrement de l'erreur pour le débogage en développement
            error_log('Error in SaveDataController: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());

            // 10. Retourner une réponse d'erreur JSON
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la sauvegarde: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString() // ATTENTION: Ne pas exposer la trace en production
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Gère l'ajout d'un film à la liste de refus de l'utilisateur.
     * Similaire à la méthode `index`, mais ajoute le film à "Liste de refus".
     * Récupère les données du film via une requête POST JSON, valide le token CSRF,
     * met à jour les informations du film si nécessaire.
     * Crée les listes "Ma liste" et "Liste de refus" pour l'utilisateur si elles n'existent pas.
     * Vérifie si le film est déjà dans l'une des listes avant de l'ajouter.
     */
    #[Route('/save/data_refusal', name: 'app_save_data_refusal')]
    public function saveRefusal(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Décodage des données JSON de la requête
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser();

        $content = $request->getContent();

        // Récupérer le token CSRF depuis l'en-tête de la requête
        $submittedToken = $request->headers->get('X-CSRF-TOKEN');

        // Valider le token CSRF pour l'action 'save_refusal'
        if (!$this->isCsrfTokenValid('save_refusal', $submittedToken)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN);
        }

        // *** Ajouter un log ici pour voir le contenu brut ***
        error_log('SaveDataController: Raw Content Received: ' . $content);

        // S'assurer que l'utilisateur est authentifié
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'Veuillez vous connecter.'], Response::HTTP_UNAUTHORIZED);
        }

        // Validation de base : vérifier si 'tconst' est présent
        if (!isset($data['tconst']) || empty($data['tconst'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Tconst est non valide ou manquant.'], Response::HTTP_BAD_REQUEST);
        }

        // --- Logique de sauvegarde ---
        try {
            // Rechercher l'entité FilmFiltre
            $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($data['tconst']);

            if (!$filmFiltre) {
                return new JsonResponse(['status' => 'error', 'message' => 'Film non trouvé dans la base de données.'], Response::HTTP_NOT_FOUND);
            }

            // Mise à jour des détails de FilmFiltre si nécessaire (similaire à la méthode index)
            if (isset($data['title']) && $filmFiltre->getTitle() === null) {
                // S'assurer que les données entrantes pour le titre sont une chaîne non vide
                if ($data['title'] !== '') { // Utiliser une vérification stricte de non-vide
                    $filmFiltre->setTitle($data['title']);
                }
            }

            // 2. Mettre à jour les détails de FilmFiltre si nécessaire (gérer les vérifications null et les conversions de type)
            if (isset($data['synopsis']) && $filmFiltre->getSynopsis() === null) {
                $filmFiltre->setSynopsis($data['synopsis']);
            }

            if (isset($data['posterPath']) && $filmFiltre->getPosterPath() === null) {
                $filmFiltre->setPosterPath($data['posterPath']);
            }

            // Gestion de la date de sortie (startYear)
            if (isset($data['releaseDate']) && $filmFiltre->getStartYear() === null) {
                // releaseDate est 'YYYY-MM-DD', extraire l'année et convertir
                try {
                    $year = (new \DateTimeImmutable($data['releaseDate']))->format('Y');
                    $filmFiltre->setStartYear((int) $year);
                } catch (\Exception $e) {
                    // En cas d'erreur de formatage, on peut choisir de ne pas mettre à jour ou de gérer l'erreur
                    return new JsonResponse(['status' => 'error', 'message' => 'Date de sortie invalide.'], Response::HTTP_BAD_REQUEST);
                }
            } else if (isset($data['startYear']) && $filmFiltre->getStartYear() === null) {
                // Si startYear est déjà un entier dans les données
                if (is_numeric($data['startYear'])) {
                    $filmFiltre->setStartYear((int) $data['startYear']);
                }
            }


            if (isset($data['imdbRating']) && $filmFiltre->getAverageRating() === null) {
                // S'assurer que le type de données correspond à la propriété DECIMAL(3,1) de l'entité
                $filmFiltre->setAverageRating((string) $data['imdbRating']); // Convertir en chaîne pour DECIMAL
            }

            if (isset($data['tmdbRating']) && $filmFiltre->getTmdbRating() === null) {
                if (is_numeric($data['tmdbRating'])) { // tmdbRating est float dans l'entité
                    $filmFiltre->setTmdbRating((float) $data['tmdbRating']);
                }
            }


            // Correction pour le type des acteurs
            if (isset($data['actors']) && $filmFiltre->getActors() === null) {
                // Convertir un tableau de noms d'acteurs en chaîne
                if (is_array($data['actors'])) {
                    $filmFiltre->setActors(implode(', ', $data['actors']));
                } else if (is_string($data['actors'])) {
                    // Si par hasard c'est déjà une chaîne
                    $filmFiltre->setActors($data['actors']);
                }
            }

            // importantCrew est défini comme tableau dans l'entité
            if (isset($data['importantCrew']) && $filmFiltre->getImportantCrew() === null) {
                if (is_array($data['importantCrew'])) {
                    $filmFiltre->setImportantCrew($data['importantCrew']);
                }
            }

            // Rechercher ou créer les listes de l'utilisateur ("Liste de refus" et "Ma liste")
            $listeRefusal = $entityManager->getRepository(Liste::class)->findOneBy([
                'user' => $user,
                'name_liste' => 'Liste de refus'
            ]);
            $listeMain = $entityManager->getRepository(Liste::class)->findOneBy([
                'user' => $user,
                'name_liste' => 'Ma liste'
            ]);

            if (!$listeRefusal) {
                $liste = new Liste();
                $liste->setUser($user);
                $liste->setNameListe('Liste de refus');
                $entityManager->persist($liste);
                $entityManager->flush();
                $listeRefusal = $entityManager->getRepository(Liste::class)->findOneBy([
                    'user' => $user,
                    'name_liste' => 'Liste de refus'
                ]);
            }

            if (!$listeMain) {
                $listeMain = new Liste();
                $listeMain->setUser($user);
                $listeMain->setNameListe('Ma liste');
                $entityManager->persist($listeMain);
                $entityManager->flush();
                $listeMain = $entityManager->getRepository(Liste::class)->findOneBy([
                    'user' => $user,
                    'name_liste' => 'Ma liste'
                ]);
            }

            // Vérifier si le film est déjà lié à "Ma liste" ou "Liste de refus"
            $existingListFilmMain = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'tconst' => $filmFiltre,
                'liste' => $listeMain,
            ]);

            $existingListFilmRefusal = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'tconst' => $filmFiltre,
                'liste' => $listeRefusal,
            ]);

            // Si le lien existe déjà, retourner une réponse de conflit
            if ($existingListFilmMain !== null) {
                return new JsonResponse(['status' => 'info', 'message' => 'Film est dans votre liste de favoris, retirez le si vous voulez l\'ajouter à vos refus.'], Response::HTTP_CONFLICT);
            }

            if ($existingListFilmRefusal !== null) {
                return new JsonResponse(['status' => 'info', 'message' => 'Film est deja dans votre liste de refus!'], Response::HTTP_CONFLICT);
            }

            // Si le lien n'existe pas, créer une nouvelle entrée ListFilm pour "Liste de refus"
            $listFilm = new ListFilm();
            $listFilm->setListFilmInfo($filmFiltre, $listeRefusal);

            $entityManager->persist($listFilm);
            $entityManager->flush();

            // Retourner une réponse de succès
            return new JsonResponse(['status' => 'success', 'message' =>  "le film a bien été ajouté dans votre liste de refus"], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Error in SaveDataController: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la sauvegarde: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString() // ATTENTION: Ne pas exposer la trace en production
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Gère le déplacement d'un film de la liste des favoris vers l'historique.
     * Récupère le `tconst` du film depuis l'URL.
     * Crée la liste "Historique des films" si elle n'existe pas.
     * Supprime le film de "Ma liste" (favoris) s'il s'y trouve.
     * Ajoute le film à "Historique des films" s'il n'y est pas déjà.
     * Affiche des messages flash pour informer l'utilisateur et redirige vers la liste des favoris.
     */
    #[Route('/save/data_history/{tconst}', name: 'app_save_data_history', methods: ['POST'])]
    public function addToFilmListeHistory(string $tconst, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        // Validation du token CSRF
        $submittedToken = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('add_history_' . $tconst, $submittedToken)) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_liste_favorites');
        }

        // Recherche ou création de la liste "Historique des films"
        $listeHistory = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Historique des films']);
        // Recherche de la liste "Ma liste" (favoris)
        $listeFavorites = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Ma liste']);

        if (!$listeHistory) {
            // Si la liste "Historique des films" n'existe pas, on la crée
            $listeHistory = new Liste();
            $listeHistory->setUser($user);
            $listeHistory->setNameListe('Historique des films');
            $entityManager->persist($listeHistory);
            $entityManager->flush(); // Persister immédiatement pour pouvoir l'utiliser
            // Récupérer l'entité fraîchement créée pour s'assurer qu'elle est managée par l'EM
            $listeHistory = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Historique des films']);
        }

        // 1. Rechercher l'entité FilmFiltre en utilisant le tconst
        $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($tconst);

        if (!$filmFiltre) {
            $this->addFlash('error', 'Le film que vous essayez d\'ajouter n\'existe pas.');
            return $this->redirectToRoute('app_liste_history'); // Rediriger vers une page appropriée
        }

        // S'assurer que la liste des favoris existe avant de chercher un film dedans
        // Si $listeFavorites est null et qu'on essaie de l'utiliser dans findOneBy, cela causera une erreur.
        // Une gestion plus robuste pourrait être nécessaire si $listeFavorites peut légitimement être null.

        // 2. Rechercher l'entrée ListFilm dans la liste des favoris
        $listFilmFavorites = null;
        if ($listeFavorites) { // Vérifier que la liste des favoris existe
            $listFilmFavorites = $entityManager->getRepository(ListFilm::class)->findOneBy([
                'liste' => $listeFavorites,
                'tconst' => $filmFiltre // Utiliser l'instance de l'entité FilmFiltre ici
            ]);
        }

        // Rechercher l'entrée ListFilm dans l'historique
        $listFilmHistory = $entityManager->getRepository(ListFilm::class)->findOneBy([
            'liste' => $listeHistory,
            'tconst' => $filmFiltre // Utiliser l'instance de l'entité FilmFiltre ici
        ]);

        // Si le film est dans les favoris, le supprimer
        if ($listFilmFavorites) {
            $entityManager->remove($listFilmFavorites);
            // Le flush sera fait plus bas ou après l'ajout à l'historique pour regrouper les opérations DB
            $this->addFlash('success', sprintf('"%s" est maintenant dans votre historique', $filmFiltre->getTitle()));
        }

        // Si le film n'est pas déjà dans l'historique, l'ajouter
        if (!$listFilmHistory) {
            $listFilm = new ListFilm();
            $listFilm->setListFilmInfo($filmFiltre, $listeHistory);
            $entityManager->persist($listFilm);
        } else {
            // Si le film est déjà dans l'historique et n'a pas été supprimé des favoris (car il n'y était pas)
            if (!$listFilmFavorites) {
                $this->addFlash('info', 'Film déjà dans votre historique.');
            }
        }

        // Flusher toutes les modifications (suppression et/ou ajout)
        $entityManager->flush();

        return $this->redirectToRoute('app_liste_favorites');
    }
}
