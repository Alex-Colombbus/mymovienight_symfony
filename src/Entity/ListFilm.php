<?php

namespace App\Entity;

use App\Repository\ListFilmRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListFilmRepository::class)]
// La contrainte d'unicité garantit qu'une combinaison de 'tconst' (film) et 'liste_id' (liste) est unique.
// Les noms dans 'columns' doivent correspondre aux noms des colonnes de la base de données pour les clés étrangères.
#[ORM\Table(name: 'list_film')] // Il est bon de spécifier explicitement le nom de la table
#[ORM\UniqueConstraint(name: 'unique_film_in_list', columns: ['tconst_id', 'liste_id'])]
class ListFilm
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null; // Nouvelle clé primaire simple auto-incrémentée

    // #[ORM\Id] a été retiré d'ici
    #[ORM\ManyToOne(targetEntity: FilmFiltre::class, inversedBy: 'listFilms')]
    #[ORM\JoinColumn(name: 'tconst_id', referencedColumnName: 'tconst', nullable: false)] // Nom de colonne explicite 'tconst_id'
    private ?FilmFiltre $tconst = null;

    // #[ORM\Id] a été retiré d'ici
    #[ORM\ManyToOne(targetEntity: Liste::class, inversedBy: 'listFilms')]
    #[ORM\JoinColumn(name: 'liste_id', referencedColumnName: 'id', nullable: false)]
    private ?Liste $liste = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $added_at = null;

    public function __construct()
    {
        $this->added_at = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->username ?? 'Utilisateur sans nom'; // Retourne le nom d'utilisateur ou un texte par défaut
    }

    // Getter pour la nouvelle clé primaire
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setListFilmInfo(FilmFiltre $filmFiltre, Liste $liste): static
    {
        $this->tconst = $filmFiltre; // Associe le film
        $this->liste = $liste;       // Associe la liste

        return $this;
    }

    public function getTconst(): ?FilmFiltre
    {
        return $this->tconst;
    }

    public function setTconst(?FilmFiltre $tconst): static
    {
        $this->tconst = $tconst;

        return $this;
    }

    public function getListe(): ?Liste
    {
        return $this->liste;
    }

    public function setListe(?Liste $liste): static
    {
        $this->liste = $liste;

        return $this;
    }

    public function getAddedAt(): ?\DateTimeImmutable
    {
        return $this->added_at;
    }

    public function setAddedAt(\DateTimeImmutable $added_at): static
    {
        $this->added_at = $added_at;

        return $this;
    }

    public function getListeUser(): ?string
    {
        return $this->liste && $this->liste->getUser()
            ? $this->liste->getUser()->__toString()
            : 'Utilisateur inconnu';
    }
}
