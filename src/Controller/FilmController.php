<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Entity\ListFilm;
use App\Service\TmdbFilmInfo;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class FilmController extends AbstractController
{
    #[Route('/film', name: 'app_film')]
    public function index(SessionInterface $session, TmdbFilmInfo $tmdbFilmInfo, EntityManagerInterface $entityManager): Response
    {
        // Récupère la liste de 40 films depuis la session
        $filmListFromSession = $session->get('films');
        $user = $this->getUser();
        // Tableau pour stocker les données des films une fois enrichies pour les afficher
        $filmsForDisplay = [];
        // Initialisation des tableaux pour les identifiants (tconsts) des films
        $tconstsFromFav = [];     // tconsts des films favoris de l'utilisateur
        $tconstsFromRefusal = []; // tconsts des films refusés par l'utilisateur

        // Vérifie si un utilisateur est connecté pour récupérer ses listes
        if ($user instanceof UserInterface) {
            // Récupère la liste de favoris de l'utilisateur
            $userSavedFilmFav  = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Ma liste']);
            // Récupère la liste de refus de l'utilisateur
            $userSavedFilmRefusal  = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Liste de refus']);

            // Si la liste de favoris existe
            if ($userSavedFilmFav) {
                // Récupère tous les films associés à cette liste de favoris
                $listFilms = $entityManager->getRepository(ListFilm::class)->findBy(['liste' => $userSavedFilmFav]);
                // Extrait les tconsts de ces films
                $tconstsFromFav = array_map(function (ListFilm $listFilm) {
                    return $listFilm->getTconst()->getTconst();
                }, $listFilms);
            }

            // Si la liste de refus existe
            if ($userSavedFilmRefusal) {
                // Récupère tous les films associés à cette liste de refus
                $listFilms = $entityManager->getRepository(ListFilm::class)->findBy(['liste' => $userSavedFilmRefusal]);
                // Extrait les tconsts de ces films
                $tconstsFromRefusal = array_map(function (ListFilm $listFilm) {
                    return $listFilm->getTconst()->getTconst();
                }, $listFilms);
            }

            // Fusionne les tconsts des favoris et des refus pour avoir une liste complète d'exclusion
            $userSavedFilmTconsts = array_merge($tconstsFromFav, $tconstsFromRefusal);
        } else {
            // Si aucun utilisateur n'est connecté, la liste des tconsts à exclure est vide
            $userSavedFilmTconsts = [];
        }


        // Filtre la liste de films de la session pour exclure ceux déjà présents dans les listes de l'utilisateur
        $filteredFilmList = array_filter((array)$filmListFromSession, function ($film) use ($userSavedFilmTconsts) {
            // Vérifie si le film est dans la liste d'exclusion
            $isExcluded = in_array($film['tconst'], $userSavedFilmTconsts, true);
            // Garde le film s'il n'est PAS exclu
            return !$isExcluded;
        });

        // Ré-indexe le tableau filtré (pour que les clés soient 0, 1, 2...)
        $filteredFilmList = array_values($filteredFilmList);
        // Ne garde que les 20 premiers films de la liste filtrée
        $filteredFilmList = array_slice($filteredFilmList, 0, 20);

        // Traite la liste filtrée pour enrichir les données des films
        if ($filteredFilmList) { // Vérifie si la liste filtrée n'est pas vide
            foreach ($filteredFilmList as $filmData) {
                // S'assure que $filmData contient 'tconst' avant de continuer
                if (!isset($filmData['tconst'])) {
                    continue; // Passe au film suivant
                }

                // Clone le service TmdbFilmInfo pour éviter les états partagés entre les itérations
                $currentFilmInfo = clone $tmdbFilmInfo;
                // Définit le tconst pour le film actuel
                $currentFilmInfo->setTconst($filmData['tconst']);
                // Récupère les informations détaillées du film (via TMDB ou autre source)
                $currentFilmInfo->setFilmInfos();

                // Vérifie les critères de validité (synopsis et acteurs)
                if ($currentFilmInfo->getSynopsis() === null || $currentFilmInfo->getSynopsis() === '' || empty($currentFilmInfo->getActors())) {
                    continue; // Passe au film suivant si les critères ne sont pas remplis
                }

                // Sérialise les données du film pour le template
                $enrichedFilmData = $currentFilmInfo->serialize();

                // Vérifie si la récupération et le traitement des informations ont réussi
                if ($currentFilmInfo->isValid()) {
                    // Ajoute les données enrichies du film à la liste pour affichage
                    $filmsForDisplay[] = $enrichedFilmData;
                }
            }
        }


        // Rend le template Twig 'film/index.html.twig'
        return $this->render('film/index.html.twig', [
            // Passe la liste des films enrichis et validés au template
            'films' => $filmsForDisplay,
        ]);
    }
}
