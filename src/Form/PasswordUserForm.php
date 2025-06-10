<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class PasswordUserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            // mot de passe actuel à comparer
            ->add('actualPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'label_attr' => [
                    'class' => 'form-label labelFormRegister fw-lighter',
                ], // Exemple d'attribut personnali
                'attr' => [
                    'placeholder' => 'Entrez votre mot de passe actuel',
                    'class' => 'form-control inputFormRegister'
                ],
                'mapped' => false,
            ])

            // nouveau mot de passe à ajouter à la base de données
            ->add('passwordPlain', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => [
                    'label' => 'Choissisez votre nouveau mot de passe',
                    'label_attr' => [
                        'class' => 'form-label labelFormRegister fw-lighter',
                    ], // Exemple d'attribut personnalisé

                    'hash_property_path' => 'password',
                    'attr' => [
                        'class' => 'form-control inputFormRegister',
                        'placeholder' => 'Entrez votre nouveau mot de passe',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer un mot de passe',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                            'max' => 40,
                        ]),
                        new Regex([
                            'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                            'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule et un chiffre.',
                        ])
                    ]

                ],
                'second_options' => [
                    'label' => 'Confirmez votre nouveau mot de passe',

                    'label_attr' => [
                        'class' => 'form-label labelFormRegister fw-lighter',
                    ],

                    'attr' => [
                        'class' => 'form-control inputFormRegister',
                        'placeholder' => 'Confirmez votre nouveau mot de passe',
                    ],
                ],
                'mapped' => false,
            ])

            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Modifier mon mot de passe',
                    'attr' => [
                        'class' => 'boutonSite btn colorJauneMoche rounded-2 w-100 mt-3',
                    ],
                ]
            )

            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();

                // Récupérer le mot de passe actuel
                $actualPassword = $form->get('actualPassword')->getData();
                //Récupérer l'instance de UserPasswordHasherInterface
                // pour hasher le mot de passe
                $passwordHasher = $form->getConfig()->getOptions()['userPasswordHasher'];
                //recupérer l'utilisateur connecté
                $user = $form->getConfig()->getOptions()['data'];


                $isValid = $passwordHasher->isPasswordValid(
                    $user,
                    $actualPassword
                );

                if (!$isValid) {
                    $form->get('actualPassword')->addError(new FormError('Le mot de passe actuel est incorrect'));
                }
                // Si le mot de passe actuel est valide, on continue
            })


        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'userPasswordHasher' => null,
        ]);
    }
}
