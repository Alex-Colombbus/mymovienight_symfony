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



        // $filmList = $session->get('films');
        // // dd($filmList);


        // $films = [];



        // foreach ($filmList as $film) {

        //     // It's crucial to either clone the service or instantiate it if it's not shared
        //     // Since TmdbFilmInfo is likely a service, cloning is the correct approach if it's stateful.
        //     $currentFilmInfo = clone $tmdbFilmInfo; // Clone the service instance for each film
        //     $currentFilmInfo->setTconst($film['tconst']);
        //     $currentFilmInfo->setFilmInfos();


        //     if ($currentFilmInfo->getSynopsis() == null || $currentFilmInfo->getSynopsis() == '' || $currentFilmInfo->getActors() == [] || $currentFilmInfo->getActors() == null) {
        //         continue;
        //     } else {

        //         $jsonFilm = $currentFilmInfo->serialize();
        //         if ($currentFilmInfo->isValid()) {
        //             $films[] = $jsonFilm;
        //         }
        //     }




        // dd($jsonFilm);

        // You might want to only add valid films to the list

        {
            // Get the list of films from the session to display on the page
            $filmListFromSession = $session->get('films');

            $filmsForDisplay = []; // This array will hold the enriched film data to pass to Twig

            // Process the film list from the session to enrich data and validate
            if ($filmListFromSession) { // Check if the session list exists and is not empty
                foreach ($filmListFromSession as $filmData) {
                    // Ensure $filmData has 'tconst' before processing. Skip if not.
                    if (!isset($filmData['tconst'])) {
                        continue;
                    }

                    // It's crucial to either clone the service or ensure it's not stateful if reused.
                    // Cloning is safer if TmdbFilmInfo holds state related to the current film.
                    $currentFilmInfo = clone $tmdbFilmInfo;
                    $currentFilmInfo->setTconst($filmData['tconst']);
                    $currentFilmInfo->setFilmInfos(); // Assume this method fetches details from TMDB/elsewhere

                    // Check your validity criteria (synopsis and actors)
                    // Use strict comparison (=== null) and check if actors array is empty using empty()
                    if ($currentFilmInfo->getSynopsis() === null || $currentFilmInfo->getSynopsis() === '' || empty($currentFilmInfo->getActors())) {
                        continue; // Skip this film if criteria are not met
                    }

                    // Assuming serialize() returns an array or object suitable for the frontend,
                    // and includes properties like tconst, title, posterPath, etc.
                    $enrichedFilmData = $currentFilmInfo->serialize();

                    // Assuming isValid() checks if the fetching and processing were successful
                    if ($currentFilmInfo->isValid()) {
                        // Add the enriched film data to the list for display
                        $filmsForDisplay[] = $enrichedFilmData;
                    }
                }
            }

            // --- FETCH USER'S SAVED FILMS FROM THE DATABASE ---
            $userSavedFilmTconsts = []; // Initialize an empty array to store tconsts of saved films
            $user = $this->getUser(); // Get the current logged-in user (returns User object or null)

            // Check if a user is logged in
            if ($user instanceof UserInterface) { // Use instanceof for a more robust type check
                // Find the user's primary list (assuming one list per user for this feature)
                // Use the EntityManager to get the repository for the Liste entity
                $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user]); // Find by the User entity

                // If the user has a list
                if ($liste) {
                    // Find all ListFilm entries associated with this specific list
                    // Use the EntityManager to get the repository for the ListFilm entity
                    // Assuming ListFilm has a ManyToOne relation mappedBy 'listFilms' on Liste,
                    // and the property name on ListFilm pointing back to Liste is 'liste'.
                    $listFilms = $entityManager->getRepository(ListFilm::class)->findBy(['liste' => $liste]);

                    // Extract the tconst string from the associated FilmFiltre entity for each ListFilm
                    $userSavedFilmTconsts = array_map(function (ListFilm $listFilm) {
                        // Assuming ListFilm has a ManyToOne relation named 'tconst' that points to FilmFiltre
                        // And FilmFiltre has a method getTconst() that returns the string ID (e.g., "tt1234567")
                        return $listFilm->getTconst()->getTconst();
                    }, $listFilms);
                }
            }

            $filmsForDisplay2 = array_filter($filmsForDisplay, function ($film) use ($userSavedFilmTconsts) {
                // Pour chaque $movie dans $movies, cette fonction est appelée.
                // $movie est un tableau associatif représentant un film.

                // Vérifier si le 'tconst' de ce film est présent dans $tconstsToExclude
                $isExcluded = in_array($film['tconst'], $userSavedFilmTconsts, true); // Utilisez 'true' pour comparaison stricte

                // array_filter garde l'élément si la callback retourne TRUE.
                // Nous voulons garder les films qui NE SONT PAS exclus.
                return !$isExcluded;
            });

            $filmsForDisplay2 = array_values($filmsForDisplay2);




            // Render the Twig template
            return $this->render('film/index.html.twig', [
                // Pass the enriched list of films to display
                'films' => $filmsForDisplay2,


            ]);
        }
    }
}
