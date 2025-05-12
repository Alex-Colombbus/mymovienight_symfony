<?php

namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use App\Service\TmdbFilmInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class FilmController extends AbstractController
{
    #[Route('/film', name: 'app_film')]
    public function index(SessionInterface $session, TmdbFilmInfo $tmdbFilmInfo, FilmRepository $filmRepository): Response
    {



        $filmList = $session->get('films');
        // dd($filmList);


        $films = [];

        foreach ($filmList as $film) {

            // It's crucial to either clone the service or instantiate it if it's not shared
            // Since TmdbFilmInfo is likely a service, cloning is the correct approach if it's stateful.
            $currentFilmInfo = clone $tmdbFilmInfo; // Clone the service instance for each film
            $currentFilmInfo->setTconst($film['tconst']);
            $currentFilmInfo->setFilmInfos();


            if ($currentFilmInfo->getSynopsis() == null || $currentFilmInfo->getSynopsis() == '' || $currentFilmInfo->getActors() == [] || $currentFilmInfo->getActors() == null) {
                continue;
            } else {

                $jsonFilm = $currentFilmInfo->serialize();
                if ($currentFilmInfo->isValid()) {
                    $films[] = $jsonFilm;
                }
            }




            // dd($jsonFilm);

            // You might want to only add valid films to the list

        }


        // dd($films);


        //transforme $films en json pour permettre so utilisation côté front avec javascript









        return $this->render('film/index.html.twig', [
            'films' => $films,
        ]);
    }
}
