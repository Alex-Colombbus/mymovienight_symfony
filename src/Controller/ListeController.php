<?php

namespace App\Controller;


use App\Entity\Liste;
use App\Entity\ListFilm;
use App\Entity\FilmFiltre;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


final class ListeController extends AbstractController
{
    #[Route('/liste/favorites', name: 'app_liste_favorites')]
    public function listFavorites(EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        $listToDisplay = [];


        // Récupérer l'entité Liste associée à l'utilisateur
        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Ma liste']);


        if (!$liste) {
            return new Response('Liste non trouvée pour cet utilisateur.', Response::HTTP_NOT_FOUND);
        }

        // Récupérer tous les ListFilm associés à cette liste
        $listFilms = $entityManager->getRepository(ListFilm::class)->findBy(['liste' => $liste]);

        foreach ($listFilms as $listFilm) {;
            $film = $entityManager->getRepository(FilmFiltre::class)->findOneBy(['tconst' => $listFilm->getTconst()]);

            if ($film) {
                $listToDisplay[] = [
                    'tconst' => $film->getTconst(),
                    'title' => $film->getTitle(),
                    'posterPath' => $film->getPosterPath(),
                    'synopsis' => $film->getSynopsis(),
                    'genres' => $film->getGenres(),
                    'runtimeMinutes' => $film->getRuntimeMinutes(),
                    'averageRating' => $film->getAverageRating(),
                    'addedAt' => $listFilm->getAddedAt()->format('d/m/Y'),
                ];
            }
        }




        // dd($listFilms, $liste, $listToDisplay);
        if (!$listToDisplay) {
            return $this->render('liste/emptyListe.html.twig', []);
        }

        return $this->render('liste/favorite.html.twig', [
            'listToDisplay' => $listToDisplay
        ]);
    }
    #[Route('/liste/refusal', name: 'app_liste_refusals')]
    public function listRefusals(EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        $listToDisplay = [];


        // Récupérer l'entité Liste associée à l'utilisateur
        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user, 'name_liste' => 'Liste de refus']);


        if (!$liste) {
            return new Response('Liste non trouvée pour cet utilisateur.', Response::HTTP_NOT_FOUND);
        }

        // Récupérer tous les ListFilm associés à cette liste
        $listFilms = $entityManager->getRepository(ListFilm::class)->findBy(['liste' => $liste]);

        foreach ($listFilms as $listFilm) {;
            $film = $entityManager->getRepository(FilmFiltre::class)->findOneBy(['tconst' => $listFilm->getTconst()]);

            if ($film) {
                $listToDisplay[] = [
                    'tconst' => $film->getTconst(),
                    'title' => $film->getTitle(),
                    'posterPath' => $film->getPosterPath(),
                    'synopsis' => $film->getSynopsis(),
                    'genres' => $film->getGenres(),
                    'runtimeMinutes' => $film->getRuntimeMinutes(),
                    'averageRating' => $film->getAverageRating(),
                    'addedAt' => $listFilm->getAddedAt()->format('d/m/Y'),
                ];
            }
        }




        // dd($listFilms, $liste, $listToDisplay);
        if (!$listToDisplay) {
            return $this->render('liste/emptyListe.html.twig', []);
        }

        return $this->render('liste/refusal.html.twig', [
            'listToDisplay' => $listToDisplay
        ]);
    }
}
