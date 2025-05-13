<?php

namespace App\Controller;


use App\Entity\Liste;
use App\Entity\ListFilm;
use App\Entity\FilmFiltre;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
            $Film = $entityManager->getRepository(FilmFiltre::class)->findOneBy(['tconst' => $listFilm->getTconst()]);
            $listToDisplay[] = $Film;
        }

        // dd($listFilms, $liste, $listToDisplay);

        return $this->render('liste/index.html.twig', [
            'listToDisplay' => $listToDisplay
        ]);
    }
}
