<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Regex;

class RegisterUserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'username',
                TextType::class,
                [
                    'label' => 'Nom d\'utilisateur',
                    'label_attr' => [
                        'class' => 'form-label labelFormRegister fw-lighter',
                    ],

                    'attr' => [
                        'class' => 'form-control inputFormRegister',
                        'placeholder' => 'Entrez votre nom d\'utilisateur',
                    ],
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Veuillez entrer un mot de passe',
                        ]),
                        new Length([
                            'min' => 3,
                            'minMessage' => 'Votre pseudo doit comporter au moins {{ limit }} caractères',
                            'max' => 30,
                        ]),
                    ]
                ]
            )

            ->add('email', EmailType::class, [
                'label' => 'Email',
                'label_attr' => [
                    'class' => 'form-label labelFormRegister fw-lighter',
                ],


                'attr' => [
                    'class' => 'form-control inputFormRegister',
                    'placeholder' => 'Entrez votre email',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un email',
                    ]),
                    new Email([
                        'message' => 'L\'adresse email "{{ value }}" n\'est pas une adresse email valide.'
                    ],)

                ]
            ])



            ->add('passwordPlain', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
                        'max' => 40,
                    ]),

                ],
                'first_options'  => [
                    'label' => 'Choissisez un mot de passe',
                    'label_attr' => [
                        'class' => 'form-label labelFormRegister fw-lighter',
                    ],

                    'hash_property_path' => 'password',
                    'attr' => [
                        'class' => 'form-control inputFormRegister',
                        'placeholder' => 'Entrez votre mot de passe',
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
                    'label' => 'Confirmez le mot de passe',

                    'label_attr' => [
                        'class' => 'form-label labelFormRegister fw-lighter',
                    ],

                    'attr' => [
                        'class' => 'form-control inputFormRegister',
                        'placeholder' => 'Confirmez votre mot de passe',
                    ],
                ],
                'mapped' => false,
            ])


            ->add('birthday', BirthdayType::class, [
                'label' => 'Date de naissance',
                'label_attr' => [
                    'class' => 'form-label labelFormRegister fw-lighter',
                ],
                'placeholder' => [
                    'year' => 'Année',
                    'month' => 'Mois',
                    'day' => 'Jour',
                ],
                'attr' => [
                    'class' => 'form-control inputFormRegister',
                ],
            ])

            ->add(
                'Inscription',
                SubmitType::class,
                [

                    'attr' => [
                        'class' => 'boutonSite w-100 mb-3 btn btn-primary',
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
