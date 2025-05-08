<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {


        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // Vérifie si l'utilisateur est connecté
        if ($user) {
            // L'utilisateur est connecté

            $username = $user->getUsername();
        } else {
            // L'utilisateur n'est pas connecté
            $username = null;
        }


        return $this->render('home/index.html.twig', [
            'userName' => $username,

        ]);
    }
}
