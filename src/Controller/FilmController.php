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


        // $response = $client->request('GET', 'https://api.themoviedb.org/3/find/tt0111161?external_source=imdb_id&language=fr&append_to_response=credits', [
        //     'headers' => [
        //         'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJmYTA4ZmQ1NzcwZjc0YjdhYzU5NzFkNTRhMzYwMWU1MSIsIm5iZiI6MS43MzQ5NDM3OTIyMTUwMDAyZSs5LCJzdWIiOiI2NzY5MjQzMDQ2YTVhNDM4NzkwYjA5MTUiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.1I5wYX02rjaW8Zc97hry7LBs5AL23ZnUL5BOD5zt95I',
        //         'accept' => 'application/json',
        //     ],
        // ]);
        // // Permet de recupérer les films de la session
        // $mainFilmInfo = $response->toArray()['movie_results'][0];
        // $id = $response->toArray()['movie_results'][0]['id'];




        // $response = $client->request('GET', 'https://api.themoviedb.org/3/movie/' . $id . '/credits?language=fr-FR', [
        //     'headers' => [
        //         'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJmYTA4ZmQ1NzcwZjc0YjdhYzU5NzFkNTRhMzYwMWU1MSIsIm5iZiI6MS43MzQ5NDM3OTIyMTUwMDAyZSs5LCJzdWIiOiI2NzY5MjQzMDQ2YTVhNDM4NzkwYjA5MTUiLCJzY29wZXMiOlsiYXBpX3JlYWQiXSwidmVyc2lvbiI6MX0.1I5wYX02rjaW8Zc97hry7LBs5AL23ZnUL5BOD5zt95I',
        //         'accept' => 'application/json',
        //     ],
        // ]);

        // $creditsFilmInfoCast = $response->toArray()['cast'];
        // $creditsFilmInfoCast = array_slice($creditsFilmInfoCast, 0, 3);
        // $creditsFilmInfoCrew = $response->toArray()['crew'];
        // $importantCrew = [];
        // // On filtre les personnes ayant un job important
        // $importantCrew = array_filter($creditsFilmInfoCrew, function ($value) {
        //     return in_array($value['job'], ['Director', 'Screenplay', 'Novel', 'Story']);
        // });

        // // On regroupe les jobs par personne
        // // On initialise un tableau associatif
        // $jobsByName = [];
        // // On parcourt le tableau importantCrew

        // foreach ($importantCrew as $crewMember) {
        //     // On extrait le nom et le job
        //     $name = $crewMember['name'];
        //     // On ajoute le job au tableau associatif
        //     $job = $crewMember['job'];
        //     // Si le nom n'est pas encore dans le tableau, on l'ajoute
        //     if (!isset($jobsByName[$name])) {
        //         $jobsByName[$name] = [];
        //     }
        //     // On ajoute le job au tableau associatif au nom correspondant
        //     $jobsByName[$name][] = $job;
        // }



        // dd($mainFilmInfo, $creditsFilmInfoCast, $creditsFilmInfoCrew, $importantCrew, $jobsByName);
        $filmList = $session->get('films');
        // dd($filmList);


        $films = [];
        // for ($i = 0; $i < count($filmList); $i++) {
        // $tmdbFilmInfo->setTconst($filmList[0]['tconst']);
        // $tmdbFilmInfo->setFilmInfos();
        // $films[] = clone $tmdbFilmInfo; // Clone l'objet pour éviter d'écraser les données
        // }
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
