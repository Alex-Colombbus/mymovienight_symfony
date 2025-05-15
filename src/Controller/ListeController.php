<?php

namespace App\Controller;


use App\Entity\Liste;
use App\Entity\ListFilm;
use App\Entity\FilmFiltre;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Stmt\Return_;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Config\Framework\HttpClient\DefaultOptions\RetryFailedConfig;

final class ListeController extends AbstractController
{
    #[Route('/liste', name: 'app_liste')]
    public function index(EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();
        $listToDisplay = [];

        // Récupérer l'entité Liste associée à l'utilisateur
        $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user]);

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

        // dd($listToDisplay);


        // dd($listFilms, $liste, $listToDisplay);
        if (!$listToDisplay) {
            return $this->render('liste/emptyListe.html.twig', []);
        }

        return $this->render('liste/index.html.twig', [
            'listToDisplay' => $listToDisplay
        ]);
    }
}
