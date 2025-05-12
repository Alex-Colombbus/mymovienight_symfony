<?php

namespace App\Entity;

use App\Repository\ListFilmRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListFilmRepository::class)]
class ListFilm
{


    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: FilmFiltre::class, inversedBy: 'listFilms')]
    #[ORM\JoinColumn(name: 'tconst', referencedColumnName: 'tconst', nullable: false)]
    private ?FilmFiltre $tconst = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Liste::class, inversedBy: 'listFilms')]
    #[ORM\JoinColumn(nullable: false)]
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

    public function getListeId(): ?Liste
    {
        return $this->liste;
    }

    public function setListeId(?Liste $list_id): static
    {
        $this->liste = $list_id;

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
