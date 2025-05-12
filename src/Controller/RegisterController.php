<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Liste;
use App\Form\RegisterUserForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

final class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function index(HttpFoundationRequest $request, EntityManagerInterface $entityManager): Response

    {
        // Création d'une nouvelle instance de l'entité User
        $user = new User();
        $liste = new Liste();

        // Création du formulaire en liant l'entité User au formulaire RegisterUserForm
        $form = $this->createForm(RegisterUserForm::class, $user);

        // Gestion de la requête HTTP pour pré-remplir ou traiter les données soumises dans le formulaire
        $form->handleRequest($request);

        // Vérifie si le formulaire a été soumis et si les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {

            // Définit le rôle par défaut de l'utilisateur comme "ROLE_USER"
            $user->setRoles(['ROLE_USER']);
            $liste->setUserId($user);
            $liste->setNameListe('Ma liste');

            // Prépare l'entité User pour être sauvegardée dans la base de données
            $entityManager->persist($user);

            // Exécute la requête SQL pour insérer l'utilisateur dans la base de données
            // Pas besoin de persist() car on insere pas une nouvelle entité

            $entityManager->flush();
            $entityManager->persist($liste);
            $entityManager->flush();

            //Message de succès de l'inscription
            $this->addFlash(
                'success',
                'Vous êtes inscrit avec succès !'
            );

            return $this->redirectToRoute('app_login');
        }




        return $this->render('register/index.html.twig', [
            'registerForm' => $form->createView()
        ]);
    }
}
