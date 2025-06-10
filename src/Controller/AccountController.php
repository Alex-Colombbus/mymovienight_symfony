<?php

namespace App\Controller;

use App\Form\ProfilUserForm;
use App\Form\PasswordUserForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


final class AccountController extends AbstractController
{
    #[Route('/compte', name: 'app_account')]
    public function index(): Response
    {

        $user = $this->getUser();

        return $this->render('account/index.html.twig', [
            'pageTitle' => 'Mon compte',
            'user' => $user,

        ]);
    }



    #[Route('compte/modifier-mdp', name: 'app_account_modify_pwd')]
    public function modifyPassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {


        $user = $this->getUser(); // Récupère l'utilisateur connecté
        // $passwordTest = $user->getPassword(); // Récupère l'utilisateur connecté

        // Envois une option userPasswordHasher dans le formulaire
        $form = $this->createForm(PasswordUserForm::class, $user, [
            "userPasswordHasher" => $userPasswordHasher,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            // Pas besoin de persist() car on insere pas une nouvelle entité
            // Exécute la requête SQL pour insérer l'utilisateur dans la base de données
            $entityManager->flush();

            // Envoie un message flash de succès
            $this->addFlash(
                'success',
                'Votre mot de passe a été modifié avec succès !'
            );

            return $this->redirectToRoute('app_account_modify_pwd');
        }


        return $this->render('account/password.html.twig', [
            'modifyPwd' => $form->createView(),
        ]);
    }

    #[Route('compte/modifier-profil', name: 'app_account_modify_profil')]
    public function modifyProfil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Utiliser seulement MailUserForm
        $form = $this->createForm(ProfilUserForm::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();


            $this->addFlash(
                'success',
                'Votre profil a été modifié avec succès !'
            );


            return $this->redirectToRoute('app_account_modify_profil');
        }

        // Utiliser le bon template
        return $this->render('account/modify-profil.html.twig', [
            'modifyProfil' => $form->createView(),
        ]);
    }
}
