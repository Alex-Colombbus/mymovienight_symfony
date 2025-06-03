<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class MailUserForm extends AbstractType
{
      public function buildForm(FormBuilderInterface $builder, array $options): void
      {
            $builder
                  //mail à changer
                  ->add('email', EmailType::class, [
                        'label' => 'Adresse e-mail à changer',
                        'constraints' => [
                              new NotBlank([
                                    'message' => 'Veuillez saisir votre adresse e-mail.',
                              ]),
                        ],
                  ])
                  ->add('submit', SubmitType::class, [
                        'label' => 'Modifier mon adresse e-mail',
                        'attr' => [
                              'class' => 'btn btn-primary mt-3',
                        ],
                  ]);
      }

      public function configureOptions(OptionsResolver $resolver): void
      {
            $resolver->setDefaults([
                  'data_class' => User::class,

            ]);
      }
}
