<?php

namespace App\Entity;

use App\Repository\ListFilmRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListFilmRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_tconst_liste', columns: ['tconst', 'liste_id'])] //Permet de faire une clé composite
class ListFilm
{


    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: FilmFiltre::class, inversedBy: 'listFilms')]
    #[ORM\JoinColumn(name: 'tconst', referencedColumnName: 'tconst', nullable: false)]
    private ?FilmFiltre $tconst = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Liste::class, inversedBy: 'listFilms')]
    #[ORM\JoinColumn(name: 'liste_id', referencedColumnName: 'id', nullable: false)]
    private ?Liste $liste = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $added_at = null;

    public function __construct()
    {
        $this->added_at = new \DateTimeImmutable();
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
}
