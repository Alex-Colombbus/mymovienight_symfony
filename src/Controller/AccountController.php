<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MailUserForm;
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
        }


        return $this->render('account/password.html.twig', [
            'modifyPwd' => $form->createView(),
        ]);
    }

    #[Route('compte/modifier-mail', name: 'app_account_modify_mail')]
    public function modifyMail(Request $request, EntityManagerInterface $entityManager): Response
    {


        $user = $this->getUser(); // Récupère l'utilisateur connecté


        $form = $this->createForm(MailUserForm::class, $user, [
            "data_class" => User::class,
            // On n'utilise pas l'option userPasswordHasher car on ne modifie pas le mot de passe
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
        }


        return $this->render('account/password.html.twig', [
            'modifyPwd' => $form->createView(),
        ]);
    }
}
