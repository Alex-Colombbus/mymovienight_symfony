<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController implements EventSubscriberInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email')->setLabel('E-mail'),
            TextField::new('username')->setLabel('Nom d\'utilisateur'),
            DateField::new('birthday')->setLabel('Date de naissance'),
            TextField::new('plainPassword', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->onlyOnForms()
                ->setHelp('Laissez vide pour ne pas changer le mot de passe lors de la modification.'),

            ChoiceField::new('roles')
                ->setLabel('Rôles')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderExpanded()
                ->setHelp('Sélectionnez les rôles de l\'utilisateur.'),
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => 'hashPassword',
            BeforeEntityUpdatedEvent::class => 'hashPassword',
        ];
    }

    public function hashPassword($event): void
    {
        $entity = $event->getEntityInstance();

        if (!$entity instanceof User) {
            return;
        }

        // Pour la création (BeforeEntityPersistedEvent)
        if ($event instanceof BeforeEntityPersistedEvent) {
            // Le plainPassword est obligatoire à la création
            if ($entity->getPlainPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $entity,
                    $entity->getPlainPassword()
                );
                $entity->setPassword($hashedPassword);
                $entity->eraseCredentials();
            } else {
                // Si pas de plainPassword lors de la création, on lève une exception
                throw new \InvalidArgumentException('Le mot de passe est obligatoire lors de la création d\'un utilisateur.');
            }
        }

        // Pour la modification (BeforeEntityUpdatedEvent)
        if ($event instanceof BeforeEntityUpdatedEvent) {
            // Seulement si un nouveau mot de passe est fourni
            if ($entity->getPlainPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $entity,
                    $entity->getPlainPassword()
                );
                $entity->setPassword($hashedPassword);
                $entity->eraseCredentials();
            }
        }
    }
}
