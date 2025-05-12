<?php

namespace App\Entity;

use App\Repository\FilmRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
#[ORM\Table(name: 'film')]
class Film
{
    #[ORM\Id]
    #[ORM\Column(length: 15)]
    private ?string $tconst = null;

    #[ORM\Column(length: 499)]
    private ?string $primaryTitle = null;

    #[ORM\Column(length: 499)]
    private ?string $originalTitle = null;

    #[ORM\Column(nullable: true)]
    private ?int $endYear = null;

    #[ORM\Column(nullable: true)]
    private ?int $runtimeMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $numVotes = null;

    #[ORM\OneToOne(mappedBy: 'film', targetEntity: FilmFiltre::class, cascade: ['persist', 'remove'])]
    // 'film' est la propriété dans FilmFiltre qui référence cet objet Film
    private ?FilmFiltre $filmFiltre = null;



    public function getTconst(): ?string
    {
        return $this->tconst;
    }

    public function setTconst(string $tconst): static
    {
        $this->tconst = $tconst;

        return $this;
    }

    public function getPrimaryTitle(): ?string
    {
        return $this->primaryTitle;
    }

    public function setPrimaryTitle(string $primaryTitle): static
    {
        $this->primaryTitle = $primaryTitle;

        return $this;
    }

    public function getOriginalTitle(): ?string
    {
        return $this->originalTitle;
    }

    public function setOriginalTitle(string $originalTitle): static
    {
        $this->originalTitle = $originalTitle;

        return $this;
    }

    public function getEndYear(): ?int
    {
        return $this->endYear;
    }

    public function setEndYear(?int $endYear): static
    {
        $this->endYear = $endYear;

        return $this;
    }

    public function getRuntimeMinutes(): ?int
    {
        return $this->runtimeMinutes;
    }

    public function setRuntimeMinutes(int $runtimeMinutes): static
    {
        $this->runtimeMinutes = $runtimeMinutes;

        return $this;
    }

    public function getNumVotes(): ?int
    {
        return $this->numVotes;
    }

    public function setNumVotes(?int $numVotes): static
    {
        $this->numVotes = $numVotes;

        return $this;
    }

    public function getFilmFiltre(): ?FilmFiltre
    {
        return $this->filmFiltre;
    }
}
