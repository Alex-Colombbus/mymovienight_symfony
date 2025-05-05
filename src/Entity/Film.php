<?php

namespace App\Entity;

use App\Repository\FilmRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Unique;

#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    #[ORM\Id]
    #[ORM\Column(length: 15)]
    private ?string $tconst = null;

    #[ORM\Column(length: 30)]
    private ?string $titleType = null;

    #[ORM\Column(length: 499)]
    private ?string $primaryTitle = null;

    #[ORM\Column(length: 499)]
    private ?string $originalTitle = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isAdult = null;

    #[ORM\Column(nullable: true)]
    private ?int $startYear = null;

    #[ORM\Column(nullable: true)]
    private ?int $endYear = null;

    #[ORM\Column(nullable: true)]
    private ?int $runtimeMinutes = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $genres = null;



    public function getTconst(): ?string
    {
        return $this->tconst;
    }

    public function setTconst(string $tconst): static
    {
        $this->tconst = $tconst;

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

    public function isAdult(): ?bool
    {
        return $this->isAdult;
    }

    public function setIsAdult(?bool $isAdult): static
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

    public function getGenres(): ?array
    {
        return $this->genres;
    }

    public function setGenres(?array $genres): static
    {
        $this->genres = $genres;

        return $this;
    }
}
