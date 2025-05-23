<?php

namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use App\Repository\FilmFiltreRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Loader\Configurator\session;



final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, FilmFiltreRepository $filmFiltreRepository, SessionInterface $session): Response
    {
        $genresArray = [];
        $films = null;



        if ($request->isMethod('POST')) {

            $minRating = $request->request->get('minNote'); // 'nom_utilisateur' est la valeur de l'attribut 'name' de l'input
            $maxRating = $request->request->get('maxNote');

            $genresArray = $request->request->all('genres') ?? []; // Récupère les genres sous forme de tableau

            // Convertit le tableau en chaîne de caractères séparée par des virgules

            $minYear = $request->request->get('minAnnee');
            $maxYear = $request->request->get('maxAnnee');

            $session->set('minRating', $minRating);
            $session->set('maxRating', $maxRating);
            $session->set('minYear', $minYear);
            $session->set('maxYear', $maxYear);
            $session->set('genresArray', $genresArray);

            if (empty($genresArray)) {
                // dd("test");
                $films = $filmFiltreRepository->findMoviesAllGenres($minRating, $maxRating, $minYear, $maxYear);
                $session->set('films', $films);
                return $this->redirectToRoute('app_film');
            } else {
                // $genres = implode('%', $genresArray);
                // dd($genres);
                $films = $filmFiltreRepository->findMoviesWithGenres($genresArray, $minRating, $maxRating, $minYear, $maxYear);
                $session->set('films', $films);
                return $this->redirectToRoute('app_film');
            }
        }
        return $this->render('home/index.html.twig', []);
    }


    #[Route('/home/more', name: 'app_home_more')]
    public function moreFilm(FilmFiltreRepository $filmFiltreRepository, SessionInterface $session): Response
    {

        if (!$session->has('minRating') || !$session->has('maxRating') || !$session->has('minYear') || !$session->has('maxYear')) {
            return $this->redirectToRoute('app_home');
        }

        $genresArray = $session->get('genresArray');
        $minRating = $session->get('minRating');
        $maxRating = $session->get('maxRating');
        $minYear = $session->get('minYear');
        $maxYear = $session->get('maxYear');



        if (empty($genresArray)) {
            // dd("test");
            $films = $filmFiltreRepository->findMoviesAllGenres($minRating, $maxRating, $minYear, $maxYear);
            $session->set('films', $films);
            return $this->redirectToRoute('app_film');
        } else {
            // $genres = implode('%', $genresArray);
            // dd($genres);
            $films = $filmFiltreRepository->findMoviesWithGenres($genresArray, $minRating, $maxRating, $minYear, $maxYear);
            $session->set('films', $films);
            return $this->redirectToRoute('app_film');
        }
        return $this->render('film/index.html.twig', [
            'films' => $films,
        ]);
    }
}
