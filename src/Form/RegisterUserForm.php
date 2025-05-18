<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

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
            ])



            ->add('passwordPlain', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => [
                    'label' => 'Choissisez un mot de passe',
                    'label_attr' => [
                        'class' => 'form-label labelFormRegister fw-lighter',
                    ], // Exemple d'attribut personnalisé

                    'hash_property_path' => 'password',
                    'attr' => [
                        'class' => 'form-control inputFormRegister',
                        'placeholder' => 'Entrez votre mot de passe',
                    ],
                    'constraints' => []

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
