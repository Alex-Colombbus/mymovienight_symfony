<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;


class ProfilUserForm extends AbstractType
{
      public function buildForm(FormBuilderInterface $builder, array $options): void
      {
            $builder
                  ->add('email', EmailType::class)
                  ->add('username', TextType::class)
                  ->add('birthday', BirthdayType::class, [
                        'required' => false,
                        'widget' => 'single_text',
                        'input' => 'datetime_immutable',
                  ])
                  ->add('submit', SubmitType::class, [
                        'label' => 'Mettre à jour',
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
