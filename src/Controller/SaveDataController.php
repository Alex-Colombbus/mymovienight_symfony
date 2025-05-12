<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Entity\ListFilm;
use App\Entity\FilmFiltre;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SaveDataController extends AbstractController
{
    #[Route('/save/data', name: 'app_save_data')]
    public function index(Request $request, EntityManagerInterface $entityManager, User $user): JsonResponse
    {

        $data = json_decode($request->getContent(), true);


        if (!$data || !isset($data)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Données manquantes ou invalides'], Response::HTTP_BAD_REQUEST);
        }

        // --- Logique de sauvegarde ---
        try {
            return new JsonResponse(['status' => 'success', 'message' => 'test'], Response::HTTP_OK);
            
            $filmFiltre = $entityManager->getRepository(FilmFiltre::class)->find($data['tconst']);


            if (isset($data['synopsis'])) {
                $filmFiltre->setSynopsis($data['synopsis']);
            }


            if (isset($data['posterPath'])) {
                $filmFiltre->setPosterPath($data['posterPath']);
            }

            if (isset($data['startYear']) && $filmFiltre->getStartYear() == null) {
                $filmFiltre->setStartYear($data['startYear']);
            }

            if (isset($data['averageRating'])) {
                $filmFiltre->setAverageRating($data['averageRating']);
            }

            if (isset($data['tmdbRating'])) {
                $filmFiltre->setTmdbRating($data['tmdbRating']);
            }

            if (isset($data['actors']) && $filmFiltre->getActors() == null) {
                $filmFiltre->setActors($data['actors']);
            }

            if (isset($data['importantCrew']) && $filmFiltre->getImportantCrew() == null) {
                $filmFiltre->setImportantCrew($data['importantCrew']);
            }

            // dd($filmFiltre);

            $entityManager->persist($filmFiltre);
            $entityManager->flush();

            $liste = $entityManager->getRepository(Liste::class)->findOneBy(['user' => $user]);
            $listFilm = new ListFilm();
            $listFilm->setListFilmInfo($data['tconst'], $liste);

            // Ajoutez d'autres setteurs selon les données reçues

            $entityManager->persist($listFilm);
            $entityManager->flush();

            // 3. Retourner une réponse JSON de succès
            return new JsonResponse(['status' => 'success', 'message' => 'Données sauvegardées avec succès!'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Loggez l'erreur $e->getMessage() si besoin
            // 4. Retourner une réponse JSON d'erreur en cas de problème
            return new JsonResponse(['status' => 'error', 'message' => 'Erreur lors de la sauvegarde.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
