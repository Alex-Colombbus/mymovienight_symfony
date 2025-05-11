<?php

namespace App\Entity;

use App\Repository\FilmFiltreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilmFiltreRepository::class)]
class FilmFiltre
{
    #[ORM\Id] // tconst (via la relation 'film') est la clé primaire
    #[ORM\OneToOne(targetEntity: Film::class, inversedBy: 'filmFiltre')]
    #[ORM\JoinColumn(name: 'tconst', referencedColumnName: 'tconst', nullable: false)]
    // name: 'tconst' -> nom de la colonne FK dans film_filtre
    // referencedColumnName: 'tconst' -> nom de la colonne PK dans film
    private ?Film $film = null; // Cette propriété représente la FK et la relation

    #[ORM\Column(length: 30)]
    private ?string $titleType = null;

    #[ORM\Column]
    private ?bool $isAdult = null;

    #[ORM\Column(nullable: true)]
    private ?int $startYear = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $genres = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    private ?string $averageRating = null;



    // public function getTconst(): ?string
    // {
    //     return $this->tconst;
    // }

    // public function setTconst(string $tconst): static
    // {
    //     $this->tconst = $tconst;

    //     return $this;
    // }

    public function getFilm(): ?Film
    {
        return $this->film;
    }

    public function setFilm(Film $film): self // Doit être un Film, pas null
    {
        $this->film = $film;
        // Si vous voulez maintenir la bidirectionnalité:
        // $film->setFilmFiltre($this); // Attention aux boucles infinies si mal géré
        // Il est souvent préférable de gérer cela du côté inverse
        // ou au moment de l'association des deux objets.
        return $this;
    }

    public function getTitleType(): ?string
    {
        return $this->titleType;
    }

    public function setTitleType(string $titleType): static
    {
        $this->titleType = $titleType;

        return $this;
    }

    public function isAdult(): ?bool
    {
        return $this->isAdult;
    }

    public function setIsAdult(bool $isAdult): static
    {
        $this->isAdult = $isAdult;

        return $this;
    }

    public function getStartYear(): ?int
    {
        return $this->startYear;
    }

    public function setStartYear(int $startYear): static
    {
        $this->startYear = $startYear;

        return $this;
    }

    public function getGenres(): ?string
    {
        return $this->genres;
    }

    public function setGenres(string $genres): static
    {
        $this->genres = $genres;

        return $this;
    }

    public function getAverageRating(): ?string
    {
        return $this->averageRating;
    }

    public function setAverageRating(?string $averageRating): static
    {
        $this->averageRating = $averageRating;

        return $this;
    }
}
